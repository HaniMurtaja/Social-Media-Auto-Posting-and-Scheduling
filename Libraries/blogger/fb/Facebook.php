<?php

namespace FSPoster\App\Libraries\fb;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Curl;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\Session;
use FSPoster\App\Providers\SocialNetwork;

class Facebook extends SocialNetwork
{
	/**
	 * Check APP credentials...
	 *
	 * @param string $appId
	 * @param string $appSecret
	 *
	 * @return mixed
	 */
	public static function checkApp ( $appId, $appSecret, $version )
	{
		$getInfo = json_decode( Curl::getContents( 'https://graph.facebook.com/' . $appId . '?fields=permissions{permission},roles,name,link,category&access_token=' . $appId . '|' . $appSecret ), TRUE );

		$appInfo = is_array( $getInfo ) && ! isset( $getInfo[ 'error' ] ) && isset( $getInfo[ 'name' ] ) ? $getInfo : FALSE;

		if ( ! $appInfo )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'The App ID or the App Secret is invalid!' ) ] );
		}

		return $appInfo[ 'name' ];
	}

	public static function sendPost ( $nodeFbId, $type, $message, $preset_id, $link, $images, $video, $accessToken, $proxy, $poster = NULL )
	{
		$sendData = [
			'message' => $message
		];

		$endPoint = 'feed';

		if ( $type === 'image' )
		{
			$sendData[ 'attached_media' ] = [];
			$images                       = is_array( $images ) ? $images : [ $images ];
			$poster                       = $poster ? $poster : $nodeFbId;

			foreach ( $images as $imageURL )
			{
				$sendData2 = [
					'url'       => $imageURL . '?_r=' . uniqid(),
					'published' => 'false',
					'caption'   => ''
				];

				$imageUpload = self::cmd( '/' . $poster . '/photos', 'POST', $accessToken, $sendData2, $proxy );

				if ( isset( $imageUpload[ 'error' ] ) )
				{
					if ( isset( $imageUpload[ 'error' ][ 'message' ] ) )
					{
						$error_msg = $imageUpload[ 'error' ][ 'message' ];
					}
					else if ( isset( $imageUpload[ 'error' ][ 'error_user_msg' ] ) )
					{
						$error_msg = $imageUpload[ 'error' ][ 'error_user_msg' ];
					}
					else
					{
						$error_msg = 'The post can\'t be shared';
					}

					return [
						'status'    => 'error',
						'error_msg' => fsp__( 'Error! %s', [ esc_html( $error_msg ) ] )
					];
				}

				if ( isset( $imageUpload[ 'id' ] ) )
				{
					$sendData[ 'attached_media' ][] = json_encode( [ 'media_fbid' => $imageUpload[ 'id' ] ] );
				}
			}
		}
		else if ( $type === 'video' )
		{
			$endPoint = 'videos';
			$sendData = [
				'file_url'    => $video,
				'description' => $message
			];
		}
		else if ( $type === 'link' )
		{
			$sendData[ 'link' ] = $link;
		}

		$result = self::cmd( '/' . $nodeFbId . '/' . $endPoint, 'POST', $accessToken, $sendData, $proxy );

		if ( isset( $result[ 'error' ] ) )
		{
			$result2 = [
				'status'    => 'error',
				'error_msg' => isset( $result[ 'error' ][ 'message' ] ) ? $result[ 'error' ][ 'message' ] : 'Error!'
			];
		}
		else
		{
			if ( isset( $result[ 'id' ] ) )
			{
				$stsId = explode( '_', $result[ 'id' ] );
				$stsId = end( $stsId );
			}
			else
			{
				$stsId = 0;
			}

			$result2 = [
				'status' => 'ok',
				'id'     => $stsId
			];
		}

		return $result2;
	}

	public static function cmd ( $cmd, $method, $accessToken, array $data = [], $proxy = '' )
	{
		$data[ 'access_token' ] = $accessToken;
		$url                    = 'https://graph.facebook.com' . $cmd; //. '?' . http_build_query( $data );
		$method                 = $method === 'POST' ? 'POST' : ( $method === 'DELETE' ? 'DELETE' : 'GET' );
		$data1                  = Curl::getContents( $url, $method, $data, [], $proxy, TRUE, FALSE );
		$data                   = json_decode( $data1, TRUE );

		if ( ! is_array( $data ) )
		{
			$data = [
				'error' => [ 'message' => 'Error data! (' . $data1 . ')' ]
			];
		}

		if ( isset( $data[ 'error' ][ 'message' ] ) && strpos( $data[ 'error' ][ 'message' ], '(#200)' ) !== FALSE )
		{
			$data[ 'error' ][ 'message' ] = fsp__( 'You need to add the Facebook App to your group settings. <a href=\'https://www.fs-poster.com/documentation/commonly-encountered-issues#issue5\' target=\'_blank\'>How to?</a>', [], FALSE );
		}

		return $data;
	}

	/**
	 * Fetch login URL...
	 *
	 * @param integer $appId
	 *
	 * @return string
	 */
	public static function getLoginURL ( $appId )
	{
		Session::set( 'app_id', $appId );
		Session::set( 'proxy', Request::get( 'proxy', '', 'string' ) );

		$appInf = DB::fetch( 'apps', [ 'id' => $appId, 'driver' => 'fb' ] );
		$appId  = $appInf[ 'app_id' ];

		if ( $appInf[ 'version' ] >= 70 )
		{
			$permissions = [ 'public_profile', 'email', 'pages_manage_posts', 'publish_to_groups' ];
		}
		else
		{
			$permissions = [ 'public_profile', 'email', 'manage_pages', 'publish_pages', 'publish_to_groups' ];
		}

		$permissions = implode( ',', array_map( 'urlencode', $permissions ) );

		$callbackUrl = self::callbackUrl();

		return "https://www.facebook.com/dialog/oauth?redirect_uri={$callbackUrl}&scope={$permissions}&response_type=code&client_id={$appId}";
	}

	/**
	 * Callback URL
	 *
	 * @return string
	 */
	public static function callbackURL ()
	{
		return site_url() . '/?fb_callback=1';
	}

	/**
	 * Fetch Access token...
	 *
	 * @return string|bool
	 */
	public static function getAccessToken ()
	{
		$appId = (int) Session::get( 'app_id' );

		if ( empty( $appId ) )
		{
			return FALSE;
		}

		$code = Request::get( 'code', '', 'string' );

		if ( empty( $code ) )
		{
			$errorMsg = Request::get( 'error_message', '', 'str' );

			self::error( $errorMsg );
		}

		$proxy = Session::get( 'proxy' );

		Session::remove( 'app_id' );
		Session::remove( 'proxy' );

		$appInf    = DB::fetch( 'apps', [ 'id' => $appId, 'driver' => 'fb' ] );
		$appSecret = $appInf[ 'app_key' ];
		$appId     = $appInf[ 'app_id' ];

		$token_url = "https://graph.facebook.com/oauth/access_token?" . "client_id=" . $appId . "&redirect_uri=" . urlencode( self::callbackUrl() ) . "&client_secret=" . $appSecret . "&code=" . $code;

		$response = Curl::getURL( $token_url, $proxy );

		$params = json_decode( $response, TRUE );

		if ( isset( $params[ 'error' ][ 'message' ] ) )
		{
			self::error( $params[ 'error' ][ 'message' ] );
		}

		$accessToken = esc_html( $params[ 'access_token' ] );

		self::authorize( $appId, $accessToken, $proxy );
	}

	/**
	 * Authorize account...
	 *
	 * @param $appId
	 * @param $accessToken
	 * @param $proxy
	 */
	public static function authorize ( $appId, $accessToken, $proxy )
	{
		$me = self::cmd( '/me', 'GET', $accessToken, [ 'fields' => 'id,name,email' ], $proxy );

		if ( isset( $me[ 'error' ] ) )
		{
			Helper::response( FALSE, isset( $me[ 'error' ][ 'message' ] ) ? $me[ 'error' ][ 'message' ] : 'Error!' );
		}

		if ( ! isset( $me[ 'id' ] ) )
		{
			$me[ 'id' ] = 0;
		}

		if ( ! isset( $me[ 'name' ] ) )
		{
			$me[ 'name' ] = '?';
		}

		if ( ! isset( $me[ 'email' ] ) )
		{
			$me[ 'email' ] = '?';
		}

		$meId = isset( $me[ 'id' ] ) ? $me[ 'id' ] : 0;

		if ( ! get_current_user_id() > 0 )
		{
			Helper::response( FALSE, fsp__( 'The current WordPress user ID is not available. Please, check if your security plugins prevent user authorization.' ) );
		}

		$checkLoginRegistered = DB::fetch( 'accounts', [
			'blog_id'    => Helper::getBlogId(),
			'user_id'    => get_current_user_id(),
			'driver'     => 'fb',
			'profile_id' => $meId
		] );

		$dataSQL = [
			'blog_id'    => Helper::getBlogId(),
			'user_id'    => get_current_user_id(),
			'name'       => $me[ 'name' ],
			'driver'     => 'fb',
			'profile_id' => $meId,
			'email'      => $me[ 'email' ],
			'proxy'      => $proxy
		];

		if ( ! $checkLoginRegistered )
		{
			DB::DB()->insert( DB::table( 'accounts' ), $dataSQL );

			$fb_accId = DB::DB()->insert_id;
		}
		else
		{
			$fb_accId = $checkLoginRegistered[ 'id' ];

			DB::DB()->update( DB::table( 'accounts' ), $dataSQL, [ 'id' => $fb_accId ] );

			DB::DB()->delete( DB::table( 'account_access_tokens' ), [ 'account_id' => $fb_accId, 'app_id' => $appId ] );
		}

		$expiresOn = self::getAccessTokenExpiresDate( $accessToken, $proxy );

		// acccess token
		DB::DB()->insert( DB::table( 'account_access_tokens' ), [
			'account_id'   => $fb_accId,
			'app_id'       => $appId,
			'expires_on'   => $expiresOn,
			'access_token' => $accessToken
		] );

		self::refetch_account( $fb_accId, $accessToken, $proxy );

		self::closeWindow();
	}

	/**
	 * Get access token expiration date...
	 *
	 * @param string $accessToken
	 * @param string $proxy
	 *
	 * @return null|string
	 */
	public static function getAccessTokenExpiresDate ( $accessToken, $proxy )
	{
		$url = 'https://graph.facebook.com/oauth/access_token_info?fields=id,category,company,name&access_token=' . $accessToken;

		$data = json_decode( Curl::getContents( $url, 'GET', [], [], $proxy ), TRUE );

		return is_array( $data ) && isset( $data[ 'expires_in' ] ) && $data[ 'expires_in' ] > 0 ? Date::dateTimeSQL( 'now', '+' . (int) $data[ 'expires_in' ] . ' seconds' ) : NULL;
	}

	/**
	 * Fetch all pages list...
	 *
	 * @param $accessToken string
	 * @param $proxy string
	 *
	 * @return array
	 */
	public static function fetchPages ( $accessToken, $proxy = '' )
	{
		$pages = [];

		$accounts_list = self::cmd( '/me/accounts', 'GET', $accessToken, [
			'fields' => 'access_token,category,name,id',
			'limit'  => 100
		], $proxy );

		// If Facebook Developer APP doesn't approved for Business use... ( set limit 3 )
		if ( isset( $accounts_list[ 'error' ][ 'code' ] ) && $accounts_list[ 'error' ][ 'code' ] === '4' && isset( $accounts_list[ 'error' ][ 'error_subcode' ] ) && $accounts_list[ 'error' ][ 'error_subcode' ] === '1349193' )
		{
			$accounts_list = self::cmd( '/me/accounts', 'GET', $accessToken, [
				'fields' => 'access_token,category,name,id',
				'limit'  => '3'
			], $proxy );

			if ( isset( $accounts_list[ 'data' ] ) && is_array( $accounts_list[ 'data' ] ) )
			{
				$pages = $accounts_list[ 'data' ];
			}

			return $pages;
		}

		if ( isset( $accounts_list[ 'data' ] ) )
		{
			$pages = array_merge( $pages, $accounts_list[ 'data' ] );
		}

		// paginaeting...
		while ( isset( $accounts_list[ 'paging' ][ 'cursors' ][ 'after' ] ) )
		{
			$after = $accounts_list[ 'paging' ][ 'cursors' ][ 'after' ];

			$accounts_list = self::cmd( '/me/accounts', 'GET', $accessToken, [
				'fields' => 'access_token,category,name,id',
				'limit'  => 100,
				'after'  => $accounts_list[ 'paging' ][ 'cursors' ][ 'after' ]
			], $proxy );

			if ( isset( $accounts_list[ 'data' ] ) )
			{
				$pages = array_merge( $pages, $accounts_list[ 'data' ] );
			}
		}

		return $pages;
	}

	/**
	 * Fetch all groups...
	 *
	 * @param $accessToken string
	 * @param $proxy string
	 *
	 * @return array
	 */
	public static function fetchGroups ( $accessToken, $proxy = '' )
	{
		$groups = [];

		$groupsList = self::cmd( '/me/groups', 'GET', $accessToken, [
			'fields'     => 'name,privacy,id,icon,cover{source},administrator',
			'limit'      => 100,
			'admin_only' => 'true'
		], $proxy );

		// If Facebook Developer APP doesn't approved for Business use... ( set limit 3 )
		if ( isset( $groupsList[ 'error' ][ 'code' ] ) && $groupsList[ 'error' ][ 'code' ] === '4' && isset( $groupsList[ 'error' ][ 'error_subcode' ] ) && $groupsList[ 'error' ][ 'error_subcode' ] === '1349193' )
		{
			$groupsList = self::cmd( '/me/groups', 'GET', $accessToken, [
				'fields'     => 'name,privacy,id,icon,cover{source},administrator',
				'limit'      => 3,
				'admin_only' => 'true'
			], $proxy );

			if ( isset( $groupsList[ 'data' ] ) && is_array( $groupsList[ 'data' ] ) )
			{
				$groups = $groupsList[ 'data' ];
			}

			return $groups;
		}

		if ( isset( $groupsList[ 'data' ] ) )
		{
			$groups = array_merge( $groups, $groupsList[ 'data' ] );
		}

		// paginaeting...
		while ( isset( $groupsList[ 'paging' ][ 'cursors' ][ 'after' ] ) )
		{
			$after = $groupsList[ 'paging' ][ 'cursors' ][ 'after' ];

			$groupsList = self::cmd( '/me/groups', 'GET', $accessToken, [
				'fields'     => 'name,privacy,id,icon,cover{source},administrator',
				'limit'      => 100,
				'admin_only' => 'true',
				'after'      => $after
			], $proxy );

			if ( isset( $groupsList[ 'data' ] ) )
			{
				$groups = array_merge( $groups, $groupsList[ 'data' ] );
			}
		}

		return $groups;
	}

	/**
	 * Get post statistics (e.g. likes, comments, shares, etc.)
	 *
	 * @param integer $post_id
	 * @param string $accessToken
	 * @param string $proxy
	 *
	 * @return array
	 */
	public static function getStats ( $post_id, $accessToken, $proxy )
	{
		$insights = self::cmd( '/' . $post_id, 'GET', $accessToken, [
			'fields' => 'reactions.type(LIKE).limit(0).summary(total_count).as(like),reactions.type(LOVE).summary(total_count).limit(0).as(love),reactions.type(WOW).summary(total_count).limit(0).as(wow),reactions.type(HAHA).summary(total_count).limit(0).as(haha),reactions.type(SAD).summary(total_count).limit(0).as(sad),reactions.type(ANGRY).summary(total_count).limit(0).as(angry),comments.limit(0).summary(true),sharedposts.limit(5000).summary(true)'
		], $proxy );

		$insights = [
			'like'  => isset( $insights[ 'like' ][ 'summary' ][ 'total_count' ] ) ? $insights[ 'like' ][ 'summary' ][ 'total_count' ] : 0,
			'love'  => isset( $insights[ 'love' ][ 'summary' ][ 'total_count' ] ) ? $insights[ 'love' ][ 'summary' ][ 'total_count' ] : 0,
			'wow'   => isset( $insights[ 'wow' ][ 'summary' ][ 'total_count' ] ) ? $insights[ 'wow' ][ 'summary' ][ 'total_count' ] : 0,
			'haha'  => isset( $insights[ 'haha' ][ 'summary' ][ 'total_count' ] ) ? $insights[ 'haha' ][ 'summary' ][ 'total_count' ] : 0,
			'sad'   => isset( $insights[ 'sad' ][ 'summary' ][ 'total_count' ] ) ? $insights[ 'sad' ][ 'summary' ][ 'total_count' ] : 0,
			'angry' => isset( $insights[ 'angry' ][ 'summary' ][ 'total_count' ] ) ? $insights[ 'angry' ][ 'summary' ][ 'total_count' ] : 0
		];

		$details = fsp__( 'Like: ' ) . $insights[ 'like' ] . "\n";
		$details .= fsp__( 'Love: ' ) . $insights[ 'love' ] . "\n";
		$details .= fsp__( 'Wow: ' ) . $insights[ 'wow' ] . "\n";
		$details .= fsp__( 'Haha: ' ) . $insights[ 'haha' ] . "\n";
		$details .= fsp__( 'Sad: ' ) . $insights[ 'sad' ] . "\n";
		$details .= fsp__( 'Angry: ' ) . $insights[ 'angry' ];

		$likesSum = $insights[ 'like' ] + $insights[ 'love' ] + $insights[ 'wow' ] + $insights[ 'haha' ] + $insights[ 'sad' ] + $insights[ 'angry' ];

		return [
			'like'     => $likesSum,
			'comments' => isset( $insights[ 'comments' ][ 'count' ] ) ? $insights[ 'comments' ][ 'count' ] : 0,
			'shares'   => isset( $insights[ 'sharedposts' ][ 'count' ] ) ? $insights[ 'sharedposts' ][ 'count' ] : 0,
			'details'  => $details
		];
	}

	/**
	 * @param string $accessToken
	 * @param string $proxy
	 *
	 * @return array
	 */
	public static function checkAccount ( $accessToken, $proxy )
	{
		$result = [
			'error'     => TRUE,
			'error_msg' => NULL
		];
		$me     = self::cmd( '/me', 'GET', $accessToken, [ 'fields' => 'id,name,email' ], $proxy );

		if ( isset( $me[ 'error' ] ) && isset( $me[ 'error' ][ 'message' ] ) )
		{
			$result[ 'error_msg' ] = $me[ 'error' ][ 'message' ];
		}
		else if ( ! isset( $me[ 'error' ] ) )
		{
			$result[ 'error' ] = FALSE;
		}

		return $result;
	}

	public static function refetch_account ( $account_id, $access_token, $proxy )
	{
		$get_nodes = DB::DB()->get_results( DB::DB()->prepare( 'SELECT id, node_id FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id = %d', [ $account_id ] ), ARRAY_A );
		$my_nodes  = [];

		foreach ( $get_nodes as $node )
		{
			$my_nodes[ $node[ 'id' ] ] = $node[ 'node_id' ];
		}

		if ( Helper::getOption( 'load_own_pages', 1 ) == 1 )
		{
			$accounts_list = self::fetchPages( $access_token, $proxy );

			foreach ( $accounts_list as $accountInfo )
			{
				if ( ! in_array( $accountInfo[ 'id' ], $my_nodes ) )
				{
					DB::DB()->insert( DB::table( 'account_nodes' ), [
						'blog_id'      => Helper::getBlogId(),
						'user_id'      => get_current_user_id(),
						'driver'       => 'fb',
						'account_id'   => $account_id,
						'node_type'    => 'ownpage',
						'node_id'      => $accountInfo[ 'id' ],
						'name'         => $accountInfo[ 'name' ],
						'access_token' => $accountInfo[ 'access_token' ],
						'category'     => $accountInfo[ 'category' ]
					] );
				}
				else
				{
					DB::DB()->update( DB::table( 'account_nodes' ), [
						'name' => $accountInfo[ 'name' ],
						'access_token' => $accountInfo[ 'access_token' ]
					],
						[
							'account_id' => $account_id,
							'node_type'  => 'ownpage',
							'node_id'    => $accountInfo[ 'id' ]
						] );
				}

				unset( $my_nodes[ array_search( $accountInfo[ 'id' ], $my_nodes ) ] );
			}
		}

		if ( Helper::getOption( 'load_groups', 1 ) == 1 )
		{
			$groupsList = self::fetchGroups( $access_token, $proxy );

			foreach ( $groupsList as $groupInf )
			{
				$cover = '';

				if ( isset( $groupInf[ 'cover' ][ 'source' ] ) )
				{
					$cover = $groupInf[ 'cover' ][ 'source' ];
				}
				else if ( isset( $groupInf[ 'icon' ] ) )
				{
					$cover = $groupInf[ 'icon' ];
				}

				if ( ! in_array( $groupInf[ 'id' ], $my_nodes ) )
				{
					DB::DB()->insert( DB::table( 'account_nodes' ), [
						'blog_id'    => Helper::getBlogId(),
						'user_id'    => get_current_user_id(),
						'driver'     => 'fb',
						'account_id' => $account_id,
						'node_type'  => 'group',
						'node_id'    => $groupInf[ 'id' ],
						'name'       => $groupInf[ 'name' ],
						'category'   => isset( $groupInf[ 'privacy' ] ) ? $groupInf[ 'privacy' ] : 'group',
						'cover'      => $cover
					] );
				}
				else
				{
					DB::DB()->update( DB::table( 'account_nodes' ), [
						'name'  => $groupInf[ 'name' ],
						'cover' => $cover
					], [
						'account_id' => $account_id,
						'node_type'  => 'group',
						'node_id'    => $groupInf[ 'id' ]
					] );
				}

				unset( $my_nodes[ array_search( $groupInf[ 'id' ], $my_nodes ) ] );
			}
		}

		if ( ! empty( $my_nodes ) )
		{
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
		}

		return [ 'status' => TRUE ];
	}
}
