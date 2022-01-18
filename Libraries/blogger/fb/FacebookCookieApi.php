<?php

namespace FSPoster\App\Libraries\fb;

use Exception;
use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Curl;
use FSPoster\App\Providers\Helper;
use FSP_GuzzleHttp\Cookie\CookieJar;
use FSPoster\App\Providers\AccountService;
use FSP_GuzzleHttp\Exception\GuzzleException;
use FSPoster\App\Libraries\PHPImage\PHPImage;
use FSPoster\App\Providers\PersianStringDecorator;

class FacebookCookieApi
{
	private $client;
	private $fb_dtsg;
	private $fbUserId;
	private $fbSess;
	private $proxy;

	public function __construct ( $fbUserId, $fbSess, $proxy = NULL )
	{
		$this->fbUserId = $fbUserId;
		$this->fbSess   = $fbSess;
		$this->proxy    = $proxy;

		$cookies = [
			[
				"Name"     => "c_user",
				"Value"    => $fbUserId,
				"Domain"   => ".facebook.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => FALSE,
				"Discard"  => FALSE,
				"HttpOnly" => FALSE,
				"Priority" => "HIGH"
			],
			[
				"Name"     => "xs",
				"Value"    => $fbSess,
				"Domain"   => ".facebook.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => FALSE,
				"Discard"  => FALSE,
				"HttpOnly" => TRUE,
				"Priority" => "HIGH"
			]
		];

		$cookieJar = new CookieJar( FALSE, $cookies );

		$this->client = new Client( [
			'cookies'         => $cookieJar,
			'allow_redirects' => [ 'max' => 5 ],
			'proxy'           => empty( $proxy ) ? NULL : $proxy,
			'verify'          => FALSE,
			'http_errors'     => FALSE,
			'headers'         => [ 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:66.0) Gecko/20100101 Firefox/66.0' ]
		] );

		$this->fb_dtsg();
	}

	public function updateFbCookie ( $id )
	{

		$myInfo = $this->myInfo();

		if ( $this->fbUserId !== $myInfo[ 'id' ] )
		{
			return FALSE;
		}

		$dataSQL = [
			'proxy'     => $this->proxy,
			'options'   => $this->fbSess,
			'status'    => NULL,
			'error_msg' => NULL
		];

		DB::DB()->update( DB::table( 'accounts' ), $dataSQL, [ 'id' => $id ] );

		return TRUE;
	}

	public function authorizeFbUser ()
	{
		$myInfo = $this->myInfo();

		if ( $this->fbUserId !== $myInfo[ 'id' ] )
		{
			return FALSE;
		}

		if ( ! get_current_user_id() > 0 )
		{
			Helper::response( FALSE, fsp__( 'The current WordPress user ID is not available. Please, check if your security plugins prevent user authorization.' ) );
		}

		$checkLoginRegistered = DB::fetch( 'accounts', [
			'blog_id'    => Helper::getBlogId(),
			'user_id'    => get_current_user_id(),
			'driver'     => 'fb',
			'profile_id' => $myInfo[ 'id' ]
		] );

		$dataSQL = [
			'blog_id'    => Helper::getBlogId(),
			'user_id'    => get_current_user_id(),
			'name'       => $myInfo[ 'name' ],
			'driver'     => 'fb',
			'profile_id' => $myInfo[ 'id' ],
			'proxy'      => $this->proxy,
			'options'    => $this->fbSess
		];

		if ( ! $checkLoginRegistered )
		{
			DB::DB()->insert( DB::table( 'accounts' ), $dataSQL );

			$fbAccId = DB::DB()->insert_id;
		}
		else
		{
			$fbAccId = $checkLoginRegistered[ 'id' ];

			DB::DB()->update( DB::table( 'accounts' ), $dataSQL, [ 'id' => $fbAccId ] );
			DB::DB()->delete( DB::table( 'account_access_tokens' ), [ 'account_id' => $fbAccId ] );
		}

		$this->refetch_account( $fbAccId );

		return TRUE;
	}

	public function myInfo ()
	{
		try
		{
			$req = $this->client->request( 'GET', 'https://touch.facebook.com/', [
				'allow_redirects' => [ 'max' => 0 ]
			] );

			$location = $req->getHeader( 'Location' );

			if ( ! empty( $location ) && strpos( $location[ 0 ], '/checkpoint/' ) > -1 )
			{
				Helper::response( FALSE, fsp__( 'Your account seems to be blocked by Facebook. You need to unblock it before adding the account.' ) );
			}

			$getInfo = (string) $req->getBody();
		}
		catch ( Exception $e )
		{
			Helper::response( FALSE, $e->getMessage() );
		}

		preg_match( '/\"USER_ID\"\:\"([0-9]+)\"/i', $getInfo, $accountId );
		$accountId = isset( $accountId[ 1 ] ) ? $accountId[ 1 ] : '?';

		preg_match( '/\"NAME\"\:\"([^\"]+)\"/i', $getInfo, $name );
		$name = json_decode( '"' . ( isset( $name[ 1 ] ) ? $name[ 1 ] : '?' ) . '"' );

		return [
			'id'   => $accountId,
			'name' => $name
		];
	}

	public function getMyPages ()
	{
		$myPagesArr = [];

		try
		{
			$result = (string) $this->client->request( 'GET', 'https://m.facebook.com/pages/?viewallpywo=1', [
				'headers' => [ 'User-Agent' => 'Mozilla/5.0 (PlayBook; U; RIM Tablet OS 2.1.0; en-US) AppleWebKit/536.2+ (KHTML, like Gecko) Version/7.2.1.0 Safari/536.2+' ]
			] )->getBody();
		}
		catch ( Exception $e )
		{
			$result = '';
		}

		preg_match( '/class="b\w b\w"><div>(.*?)class="b\w b\w"><div>/i', $result, $pages );

		if ( isset( $pages ) && ! empty( $pages ) )
		{
			$result = $pages[ 1 ];
		}
		else
		{
			$result = '';
		}

		preg_match_all( '/page_suggestion_([0-9]+)/i', $result, $ids );
		preg_match_all( '/<span>(.*?)<\/span>/i', $result, $names );

		$ids   = $ids[ 1 ];
		$names = $names[ 1 ];

		if ( count( $ids ) === count( $names ) )
		{
			for ( $i = 0; $i < count( $ids ); $i++ )
			{
				$myPagesArr[] = [
					'id'    => $ids[ $i ],
					'name'  => $names[ $i ],
					'cover' => 'https://graph.facebook.com/' . $ids[ $i ] . '/picture'
				];
			}
		}

		return $myPagesArr;
	}

	public function getGroups ()
	{
		try
		{
			$result = (string) $this->client->request( 'GET', 'https://m.facebook.com/groups/?seemore', [
				'headers' => [ 'User-Agent' => 'Mozilla/5.0 (PlayBook; U; RIM Tablet OS 2.1.0; en-US) AppleWebKit/536.2+ (KHTML, like Gecko) Version/7.2.1.0 Safari/536.2+' ]
			] )->getBody();
		}
		catch ( Exception $e )
		{
			$result = '';
		}

		preg_match_all( '/a href="(?:https:\/\/m\.facebook\.com\/|\/)groups\/(?!create)([0-9a-zA-Z._-]+)\/.+">(.+)<\/a/Ui', $result, $groups );

		if ( ! isset( $groups[ 1 ] ) )
		{
			return [];
		}

		$groupsArr = [];

		foreach ( $groups[ 1 ] as $key => $group )
		{
			if ( ! is_numeric( $group ) )
			{
				try
				{
					$result = (string) $this->client->request( 'GET', 'https://m.facebook.com/groups/' . $group, [
						'headers' => [ 'User-Agent' => 'Mozilla/5.0 (PlayBook; U; RIM Tablet OS 2.1.0; en-US) AppleWebKit/536.2+ (KHTML, like Gecko) Version/7.2.1.0 Safari/536.2+' ]
					] )->getBody();

					preg_match( '/group_id=([0-9]+)/', $result, $group_id );

					$group = $group_id[ 1 ];
				}
				catch ( Exception $e )
				{
					continue;
				}
			}

			$groupsArr[] = [
				'id'   => $group,
				'name' => isset( $groups[ 2 ][ $key ] ) ? $groups[ 2 ][ $key ] : '???'
			];
		}

		return $groupsArr;
	}

	public function getStats ( $postId )
	{
		try
		{
			$result = (string) $this->client->request( 'GET', 'https://touch.facebook.com/' . $postId )->getBody();
		}
		catch ( Exception $e )
		{
			$result = '';
		}

		preg_match( '/\,comment_count\:([0-9]+)\,/i', $result, $comments );
		preg_match( '/\,share_count\:([0-9]+)\,/i', $result, $shares );
		preg_match( '/\,reactioncount\:([0-9]+)\,/i', $result, $likes );

		return [
			'like'     => isset( $likes[ 1 ] ) ? $likes[ 1 ] : 0,
			'comments' => isset( $comments[ 1 ] ) ? $comments[ 1 ] : 0,
			'shares'   => isset( $shares[ 1 ] ) ? $shares[ 1 ] : 0,
			'details'  => ''
		];
	}

	public function sendPost ( $nodeFbId, $node_id, $nodeType, $type, $message, $preset_id, $link, $images, $videos )
	{
		$sendData = [
			'fb_dtsg'  => $this->fb_dtsg(),
			'__ajax__' => 'true',
			'__a'      => '1'
		];

		if ( empty( $sendData[ 'fb_dtsg' ] ) )
		{
			if ( $nodeType === 'account' )
			{
				$accID = $node_id;
			}
			else
			{
				$node_info = DB::fetch( 'account_nodes', [ 'id' => $node_id ] );
				$accID     = $node_info[ 'account_id' ];
			}

			AccountService::disable_account( $accID, fsp__( 'The account is disconnected from the FS Poster plugin. Please add your account to the plugin without deleting the account from the plugin; as a result, account settings will remain as it is.' ) );

			return [
				'status'    => 'error',
				'error_msg' => fsp__( ' The account is disconnected from the FS Poster plugin. Please update your account cookie to connect it to the plugin again. <a href=\'https://www.fs-poster.com/documentation/commonly-encountered-issues#issue8\' target=\'_blank\'>How to?</a>.', [], FALSE )
			];
		}

		if ( $preset_id > 0 && $type === 'status' )
		{
			$sendData[ 'text_format_preset_id' ] = $preset_id;
		}
		else if ( $type === 'link' )
		{
			$sendData[ 'linkUrl' ] = $link;
		}

		$postType = 'form_params';

		if ( $type === 'image' )
		{
			$sendData[ 'photo_ids' ] = [];
			$images                  = is_array( $images ) ? $images : [ $images ];

			foreach ( $images as $imageURL )
			{
				$photoId = $this->uploadPhoto( $imageURL, $nodeFbId, $nodeType );

				if ( $photoId > 0 )
				{
					$sendData[ 'photo_ids' ][ $photoId ] = $photoId;
				}
			}

			if ( $nodeType === 'group' )
			{
				$endpoint = "https://touch.facebook.com/_mupload_/composer/?target=" . $nodeFbId;

				$sendData[ 'message' ] = $message;
			}
			else if ( $nodeType === 'ownpage' )
			{
				$endpoint = 'https://upload.facebook.com/_mupload_/composer/?target=' . $nodeFbId . '&av=' . $nodeFbId;

				$sendData[ 'status' ]           = $message;
				$sendData[ 'waterfall_id' ]     = $this->waterfallId();
				$sendData[ 'waterfall_source' ] = 'composer_pages_feed';

				$postType = 'multipart';
			}
			else
			{
				$endpoint = "https://touch.facebook.com/_mupload_/composer/?target=" . $nodeFbId;

				$sendData[ 'status' ]           = $message;
				$sendData[ 'waterfall_id' ]     = $this->waterfallId();
				$sendData[ 'waterfall_source' ] = 'composer_pages_feed';
				$sendData[ 'privacyx' ]         = $this->getPrivacyX();
			}

		}
		else if ( $type === 'video' )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Error! Facebook cookie method doesn\'t allow sharing videos!' )
			];
		}
		else
		{
			if ( $nodeType === 'group' )
			{
				$endpoint              = 'https://touch.facebook.com/a/group/post/add/?gid=' . $nodeFbId;
				$sendData[ 'message' ] = $message;
			}
			else if ( $nodeType === 'ownpage' )
			{
				$endpoint             = 'https://touch.facebook.com/a/home.php?av=' . $nodeFbId;
				$sendData[ 'status' ] = $message;
			}
			else
			{
				$endpoint = 'https://touch.facebook.com/a/home.php';

				$sendData[ 'status' ]   = $message;
				$sendData[ 'target' ]   = $nodeFbId;
				$sendData[ 'privacyx' ] = $this->getPrivacyX();
			}
		}

		if ( $postType === 'multipart' )
		{
			$sendData = $this->conertToMultipartArray( $sendData );
		}

		try
		{
			$post = (string) $this->client->request( 'POST', $endpoint, [
				$postType => $sendData,
				'headers' => [ 'Referer' => 'https://touch.facebook.com/' ]
			] )->getBody();
		}
		catch ( Exception $e )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Error! %s', [ $e->getMessage() ] )
			];
		}

		$hasError = $this->parsePostRepsonse( $post );

		if ( ! $hasError[ 0 ] )
		{
			return [
				'status'    => 'error',
				'error_msg' => esc_html( $hasError[ 1 ] )
			];
		}

		if ( preg_match( '/errcode=([0-9]+)/', $post ) && preg_match( '/upload_error=([0-9]+)/', $post ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Error! Failed to upload the image!' )
			];
		}

		if ( $nodeType === 'account' && ( $type === 'link' || $type === 'custom_message' ) )
		{
			preg_match( '/top_level_post_id\.([0-9]+)/i', $post, $postId );

			$postId = isset( $postId[ 1 ] ) ? $postId[ 1 ] : 0;
		}
		else if ( $nodeType === 'account' && $type === 'image' )
		{
			try
			{
				$get_account = (string) $this->client->request( 'GET', 'https://m.facebook.com/me', [
					'headers' => [ 'User-Agent' => 'Mozilla/5.0 (PlayBook; U; RIM Tablet OS 2.1.0; en-US) AppleWebKit/536.2+ (KHTML, like Gecko) Version/7.2.1.0 Safari/536.2+' ]
				] )->getBody();
			}
			catch ( Exception $e )
			{
				$get_account = '';
			}

			preg_match( '/name="privacy\[([0-9]+)\]/i', $get_account, $postId );

			$postId = isset( $postId[ 1 ] ) ? $postId[ 1 ] : 0;
		}
		else if ( $nodeType === 'ownpage' && ( $type === 'link' || $type === 'custom_message' ) )
		{
			preg_match( '/top_level_post_id\.([0-9]+)/i', $post, $postId );

			$postId = isset( $postId[ 1 ] ) ? $postId[ 1 ] : 0;
		}
		else if ( $nodeType === 'ownpage' && $type === 'image' )
		{
			try
			{
				$get_last_post = (string) $this->client->request( 'GET', 'https://m.facebook.com/' . $nodeFbId . '/', [
					'headers' => [ 'User-Agent' => 'Mozilla/5.0 (PlayBook; U; RIM Tablet OS 2.1.0; en-US) AppleWebKit/536.2+ (KHTML, like Gecko) Version/7.2.1.0 Safari/536.2+' ]
				] )->getBody();
			}
			catch ( Exception $e )
			{
				$get_last_post = '';
			}

			preg_match( '/id\=\"like_([0-9]+)\"/i', $get_last_post, $postId );

			$postId = isset( $postId[ 1 ] ) ? $postId[ 1 ] : 0;
		}
		else if ( $nodeType === 'group' && ( $type === 'link' || $type === 'custom_message' ) )
		{
			preg_match( '/class=\\\"_5xu4\\\">\\\u003Ca href=\\\".*?id=([0-9]+)\\\"/i', $post, $postId );

			$postId = isset( $postId[ 1 ] ) ? $postId[ 1 ] : 0;
		}
		else if ( $nodeType === 'group' && $type === 'image' )
		{
			preg_match( '/id=([0-9]+)/i', $post, $postId );

			$postId = isset( $postId[ 1 ] ) ? $postId[ 1 ] : 0;
		}

		if ( ! ( $postId > 0 ) && strpos( $post, 'help protect the community from spam' ) > -1 )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'You may want to slow down or stop to avoid a restriction on your account. We limit how often you can post, comment or do other things in a given amount of time to help protect the community from spam. <a href=\'https://www.facebook.com/help/177066345680802\' target=\'_blank\'>Learn more.</a>', [], FALSE )
			];
		}
		else if ( strpos( $post, 'is pending approval' ) > -1 )
		{
			$postId = 'groups/' . $nodeFbId . '/pending';
		}

		if ( empty( $postId ) || ( is_numeric( $postId ) && intval( $postId ) === 0 ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'An error occured while sharing the post.' )
			];
		}

		return [
			'status' => 'ok',
			'id'     => $postId
		];
	}

	private function fb_dtsg ()
	{
		if ( is_null( $this->fb_dtsg ) )
		{
			try
			{
				$getFbDtsg = $this->client->request( 'GET', 'https://m.facebook.com/', [
					'headers' => [ 'User-Agent' => 'Mozilla/5.0 (PlayBook; U; RIM Tablet OS 2.1.0; en-US) AppleWebKit/536.2+ (KHTML, like Gecko) Version/7.2.1.0 Safari/536.2+' ]
				] )->getBody();
			}
			catch ( Exception $e )
			{
				$getFbDtsg = '';
			}

			preg_match( '/name\=\"fb_dtsg\" value\=\"(.+)\"/Ui', $getFbDtsg, $fb_dtsg );

			if ( ! isset( $fb_dtsg[ 1 ] ) )
			{
				$this->fb_dtsg = '';
			}
			else
			{
				$this->fb_dtsg = $fb_dtsg[ 1 ];
			}

			if ( strpos( $getFbDtsg, 'cookie/consent' ) > -1 )
			{
				try
				{
					$this->client->request( 'POST', 'https://www.facebook.com/cookie/consent/', [
						'form_params' => [
							'fb_dtsg'        => $this->fb_dtsg(),
							'__a'            => '1',
							'__user'         => $this->fbUserId,
							'accept_consent' => 'true',
							'__ccg'          => 'GOOD'
						]
					] );
				}
				catch ( Exception $e )
				{
					Helper::response( FALSE, $e->getMessage() );
				}
			}
		}

		return $this->fb_dtsg;
	}

	private function uploadPhoto ( $photo, $target, $targetType )
	{
		$postData = [
			[
				'name'     => 'file1',
				'contents' => Curl::getURL( $photo, $this->proxy ),
				'filename' => basename( $photo )
			]
		];

		$endpoint = 'https://upload.facebook.com/_mupload_/photo/x/saveunpublished/?thumbnail_width=80&thumbnail_height=80&waterfall_id=' . $this->waterfallId() . '&waterfall_app_name=web_m_touch&waterfall_source=composer_pages_feed&target_id=' . urlencode( $target ) . '&fb_dtsg=' . urlencode( $this->fb_dtsg() ) . '&__ajax__=true&__a=true';

		if ( $targetType === 'ownpage' )
		{
			$endpoint .= '&av=' . urlencode( $target );
		}

		try
		{
			$post = (string) $this->client->request( 'POST', $endpoint, [
				'multipart' => $postData,
				'headers'   => [ 'Referer' => 'https://touch.facebook.com/' ]
			] )->getBody();
		}
		catch ( Exception $e )
		{
			$post = '';
		}

		preg_match( '/\"fbid\"\:\"([0-9]+)/i', $post, $photoId );

		return isset( $photoId[ 1 ] ) ? $photoId[ 1 ] : 0;
	}

	private function waterfallId ()
	{
		return md5( uniqid() . rand( 0, 99999999 ) . uniqid() );
	}

	private function getPrivacyX ()
	{
		$url = 'https://touch.facebook.com/privacy/timeline/saved_custom_audience_selector_dialog/?fb_dtsg=' . $this->fb_dtsg();

		try
		{
			$getData = (string) $this->client->request( 'GET', $url )->getBody();
		}
		catch ( Exception $e )
		{
			$getData = '';
		}

		preg_match( '/\:\"([0-9]+)\"/i', htmlspecialchars_decode( $getData ), $firstPrivacyX );

		return isset( $firstPrivacyX[ 1 ] ) ? $firstPrivacyX[ 1 ] : '0';
	}

	private function conertToMultipartArray ( $arr )
	{
		$newArr = [];

		foreach ( $arr as $name => $value )
		{
			if ( is_array( $value ) )
			{
				foreach ( $value as $name2 => $value2 )
				{
					$newArr[] = [
						'name'     => $name . '[' . $name2 . ']',
						'contents' => $value2
					];
				}
			}
			else
			{
				$newArr[] = [
					'name'     => $name,
					'contents' => $value
				];
			}
		}

		return $newArr;
	}

	private function parsePostRepsonse ( $response )
	{
		if ( empty( $response ) )
		{
			return [ FALSE, fsp__( 'Error! Response is empty!' ) ];
		}

		$hasError = preg_match( '/\,\"error\"\:([0-9]+)\,/iU', $response, $errCode );

		if ( $hasError && (int) $errCode[ 1 ] > 0 )
		{
			$errCode = (int) $errCode[ 1 ];

			preg_match( '/\,\"errorDescription\"\:\"(.+)\"\,/iU', $response, $errMsg );
			$errMsg = isset( $errMsg[ 1 ] ) ? $errMsg[ 1 ] : fsp__( 'Error!' );

			return [ FALSE, fsp__( '%s ( error code: %s )', [ $errMsg, $errCode ] ) ];
		}

		return [ TRUE ];
	}

	/**
	 * @return array
	 */
	public function checkAccount ()
	{
		$result = [
			'error'     => TRUE,
			'error_msg' => NULL
		];
		$myInfo = $this->myInfo();

		if ( $this->fbUserId === $myInfo[ 'id' ] )
		{
			$result[ 'error' ] = FALSE;
		}

		return $result;
	}

	public function refetch_account ( $account_id )
	{
		$get_nodes = DB::DB()->get_results( DB::DB()->prepare( 'SELECT id, node_id FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id = %d', [ $account_id ] ), ARRAY_A );
		$my_nodes  = [];

		foreach ( $get_nodes as $node )
		{
			$my_nodes[ $node[ 'id' ] ] = $node[ 'node_id' ];
		}

		if ( Helper::getOption( 'load_own_pages', 1 ) == 1 )
		{
			$accounts_list = $this->getMyPages();

			foreach ( $accounts_list as $account_info )
			{
				if ( ! in_array( $account_info[ 'id' ], $my_nodes ) )
				{
					DB::DB()->insert( DB::table( 'account_nodes' ), [
						'blog_id'      => Helper::getBlogId(),
						'user_id'      => get_current_user_id(),
						'driver'       => 'fb',
						'account_id'   => $account_id,
						'node_type'    => 'ownpage',
						'node_id'      => $account_info[ 'id' ],
						'name'         => $account_info[ 'name' ],
						'access_token' => NULL,
						'cover'        => $account_info[ 'cover' ],
						'category'     => ''
					] );
				}
				else
				{
					DB::DB()->update( DB::table( 'account_nodes' ), [
						'name' => $account_info[ 'name' ]
					],
						[
							'account_id' => $account_id,
							'node_type'  => 'ownpage',
							'node_id'    => $account_info[ 'id' ]
						] );
				}

				unset( $my_nodes[ array_search( $account_info[ 'id' ], $my_nodes ) ] );
			}
		}

		if ( Helper::getOption( 'load_groups', 1 ) == 1 )
		{
			$accounts_list = $this->getGroups();

			foreach ( $accounts_list as $account_info )
			{
				if ( ! in_array( $account_info[ 'id' ], $my_nodes ) )
				{
					$cover = 'https://static.xx.fbcdn.net/rsrc.php/v3/yF/r/MzwrKZOhtIS.png';

					DB::DB()->insert( DB::table( 'account_nodes' ), [
						'blog_id'      => Helper::getBlogId(),
						'user_id'      => get_current_user_id(),
						'driver'       => 'fb',
						'account_id'   => $account_id,
						'node_type'    => 'group',
						'node_id'      => $account_info[ 'id' ],
						'name'         => $account_info[ 'name' ],
						'access_token' => NULL,
						'category'     => NULL,
						'cover'        => $cover
					] );
				}
				else
				{
					DB::DB()->update( DB::table( 'account_nodes' ), [
						'name' => $account_info[ 'name' ]
					], [
						'account_id' => $account_id,
						'node_type'  => 'group',
						'node_id'    => $account_info[ 'id' ]
					] );
				}

				unset( $my_nodes[ array_search( $account_info[ 'id' ], $my_nodes ) ] );
			}
		}

		if ( ! empty( $my_nodes ) )
		{
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
		}

		return [ 'status' => TRUE ];
	}

	public function sendStory ( $accId, $message, $image )
	{
		$sendData = [
			'fb_dtsg' => $this->fb_dtsg(),
			'__user'  => $this->fbUserId,
			'av'      => $this->fbUserId
		];

		if ( empty( $sendData[ 'fb_dtsg' ] ) )
		{
			AccountService::disable_account( $accId, fsp__( 'The account is disconnected from the FS Poster plugin. Please add your account to the plugin without deleting the account from the plugin; as a result, account settings will remain as it is.' ) );

			return [
				'status'    => 'error',
				'error_msg' => fsp__( ' The account is disconnected from the FS Poster plugin. Please update your account cookie to connect it to the plugin again. <a href=\'https://www.fs-poster.com/documentation/commonly-encountered-issues#issue8\' target=\'_blank\'>How to?</a>.', [], FALSE )
			];
		}

		$sendData[ 'fb_api_req_friendly_name' ] = 'StoriesCreateMutation';
		$sendData[ 'fb_api_caller_class' ]      = 'RelayModern';

		$image_path = self::imageForStory( $image, $message )[ 'path' ];

		try
		{
			$post = (string) $this->client->request( 'POST', 'https://m.facebook.com/stories/mobile/create/', [
				'query'     => $sendData,
				'multipart' => [
					[
						'name'     => 'photo',
						'contents' => file_get_contents( $image_path ),
						'filename' => 'blob',
						'headers'  => [ 'Content-Type' => 'image/png' ]
					]
				]
			] )->getBody();

			unlink( $image_path );

			preg_match( '/\"photo_fbid\":\"([0-9]*)\"/', $post, $matches );

			if ( empty( $matches ) || empty( $matches[ 1 ] ) )
			{
				return [
					'status'    => 'error',
					'error_msg' => 'Unknown error'
				];
			}

			return [
				'status' => 'ok',
				'id'     => $matches[ 1 ]
			];
		}
		catch ( GuzzleException $e )
		{
			unlink( $image_path );

			return [
				'status'    => 'error',
				'error_msg' => $e->getMessage()
			];
		}
	}

	private static function imageForStory ( $photo_path, $title )
	{
		$storyBackground    = Helper::getOption( 'facebook_story_background', '636e72' );
		$titleBackground    = Helper::getOption( 'facebook_story_title_background', '000000' );
		$titleBackgroundOpc = Helper::getOption( 'facebook_story_title_background_opacity', '30' );
		$titleColor         = Helper::getOption( 'facebook_story_title_color', 'FFFFFF' );
		$titleTop           = (int) Helper::getOption( 'facebook_story_title_top', '125' );
		$titleLeft          = (int) Helper::getOption( 'facebook_story_title_left', '30' );
		$titleWidth         = (int) Helper::getOption( 'facebook_story_title_width', '660' );
		$titleFontSize      = (int) Helper::getOption( 'facebook_story_title_font_size', '30' );
		$titleRtl           = Helper::getOption( 'facebook_story_title_rtl', 'off' ) == 'on';

		if ( $titleRtl )
		{
			$p_a   = new PersianStringDecorator();
			$title = $p_a->decorate( $title, FALSE, TRUE );
		}

		$titleBackgroundOpc = $titleBackgroundOpc > 100 || $titleBackgroundOpc < 0 ? 0.3 : $titleBackgroundOpc / 100;

		$storyBackground   = Helper::hexToRgb( $storyBackground );
		$storyBackground[] = 0;// opacity

		$storyW = 1080 / 1.5;
		$storyH = 1920 / 1.5;

		$imageInf    = new PHPImage( $photo_path );
		$imageWidth  = $imageInf->getWidth();
		$imageHeight = $imageInf->getHeight();

		if ( $imageWidth * $imageHeight > 3400 * 3400 ) // large file
		{
			return NULL;
		}

		$imageInf->cleanup();
		unset( $imageInf );

		$w1 = $storyW;
		$h1 = ( $w1 / $imageWidth ) * $imageHeight;

		if ( $h1 > $storyH )
		{
			$w1 = ( $storyH / $h1 ) * $w1;
			$h1 = $storyH;
		}

		$image = new PHPImage();
		$image->initialiseCanvas( $storyW, $storyH, 'img', $storyBackground );

		$image->draw( $photo_path, '50%', '50%', $w1, $h1 );

		$titleLength  = mb_strlen( $title, 'UTF-8' );
		$titlePercent = $titleLength - 40;
		if ( $titlePercent < 0 )
		{
			$titlePercent = 0;
		}
		else if ( $titlePercent > 100 )
		{
			$titlePercent = 100;
		}

		// write title
		if ( ! empty( $title ) )
		{
			$textPadding = 10;
			$textWidth   = $titleWidth;
			$textHeight  = 100 + $titlePercent;
			$iX          = $titleLeft;
			$iY          = $titleTop;

			$fontDir = Helper::getOption( 'facebook_story_custom_font', '' );
			$fontDir = ! empty( $fontDir ) && file_exists( $fontDir ) ? $fontDir : __DIR__ . '/../PHPImage/font/arial.ttf';

			$image->setFont( $fontDir );

			$image->rectangle( $iX, $iY, $textWidth + $textPadding, $textHeight - $textPadding, Helper::hexToRgb( $titleBackground ), $titleBackgroundOpc );

			$image->textBox( $title, [
				'fontSize'        => $titleFontSize,
				'fontColor'       => Helper::hexToRgb( $titleColor ),
				'x'               => $iX,
				'y'               => $iY,
				'strokeWidth'     => 1,
				'strokeColor'     => [ 99, 110, 114 ],
				'width'           => $textWidth,
				'height'          => $textHeight,
				'alignHorizontal' => 'center',
				'alignVertical'   => 'center'
			] );
		}

		$newFileName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid( 'fs_' );
		//static::moveToTrash( $newFileName );

		$image->setOutput( 'jpg' )->save( $newFileName );

		return [
			'width'  => $storyW,
			'height' => $storyH,
			'path'   => $newFileName
		];
	}
}
