<?php

namespace FSPoster\App\Libraries\google;

use Exception;
use Google_Client;
use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\DB;
use Google_Service_MyBusiness;
use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\Session;
use Google_Service_MyBusiness_MediaItem;
use Google_Service_MyBusiness_LocalPost;
use FSPoster\App\Providers\SocialNetwork;
use Google_Service_MyBusiness_CallToAction;

require_once 'MyBusiness.php';

class GoogleMyBusinessAPI extends SocialNetwork
{
	private static $_client;

	public static function sendPost ( $app_id, $profile_id, $type, $message, $link, $images, $video, $access_token, $proxy )
	{
		$app_info = DB::fetch( 'apps', [ 'id' => $app_id, 'driver' => 'google_b' ] );

		if ( ! $app_info )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Error! There isn\'t a Google My Business App!' )
			];
		}

		if ( Helper::getOption( 'gmb_autocut', '1' ) == 1 && mb_strlen( $message ) > 1500 )
		{
			$message = mb_substr( $message, 0, 1497 ) . '...';
		}

		try
		{
			$client = self::getClient( $app_info, $proxy );
			$client->setAccessToken( $access_token );
			$client->setApiFormatV2( TRUE );

			$gmb  = new Google_Service_MyBusiness( $client );
			$post = new Google_Service_MyBusiness_LocalPost();
			$post->setSummary( $message );
			$post->setTopicType( 'STANDARD' );

			if ( $type === 'image' && ! empty( $images ) && is_array( $images ) )
			{
				foreach ( $images as $image )
				{
					$media = new Google_Service_MyBusiness_MediaItem();
					$media->setMediaFormat( 'PHOTO' );
					$media->setSourceUrl( $image );
					$post->setMedia( $media );
				}
			}
			else if ( $type === 'video' && ! empty( $video ) && is_string( $video ) )
			{
				$media = new Google_Service_MyBusiness_MediaItem();
				$media->setMediaFormat( 'VIDEO' );
				$media->setSourceUrl( $video );
				$post->setMedia( $media );
			}
			else if ( $type === 'link' )
			{
				$call_to_action = new Google_Service_MyBusiness_CallToAction();
				$call_to_action->setActionType( Helper::getOption( 'google_b_button_type', 'LEARN_MORE' ) );
				$call_to_action->setUrl( $link );
				$post->setCallToAction( $call_to_action );

				if ( ! empty( $images ) && is_array( $images ) )
				{
					$media = new Google_Service_MyBusiness_MediaItem();
					$media->setMediaFormat( 'PHOTO' );
					$media->setSourceUrl( reset( $images ) );
					$post->setMedia( $media );
				}
			}

			$posted = $gmb->accounts_locations_localPosts->create( $profile_id, $post );

			if ( $posted->getState() === 'REJECTED' )
			{
				return [
					'status'    => 'error',
					'error_msg' => fsp__( 'Error! The post rejected by Google My Business!' )
				];
			}

			$post_link   = $posted->getSearchUrl();
			$parsed_link = parse_url( $post_link );
			parse_str( $parsed_link[ 'query' ], $params );

			return [
				'status' => 'ok',
				'id'     => $params[ 'lpsid' ] . '&id=' . $params[ 'id' ]
			];
		}
		catch ( Exception $e )
		{
			$error = json_decode( $e->getMessage() );

			if ( $error->error->status === 'PERMISSION_DENIED' )
			{
				$error->error->message = fsp__( 'You need to verify your locations to share posts on it' );
			}

			if ( isset( $error->error->details[ 0 ]->errorDetails[ 0 ]->message ) )
			{
				$err_msg = fsp__( 'Error! %s', [ esc_html( $error->error->details[ 0 ]->errorDetails[ 0 ]->message ) ] );
			}
			else
			{
				$err_msg = fsp__( 'Error! %s', [ esc_html( $error->error->message ) ] );
			}

			return [
				'status'    => 'error',
				'error_msg' => $err_msg
			];
		}
	}

	public static function getLoginURL ( $app_id )
	{
		$proxy = Request::get( 'proxy', '', 'string' );

		Session::set( 'app_id', $app_id );
		Session::set( 'proxy', $proxy );

		$app_info = DB::fetch( 'apps', [ 'id' => $app_id, 'driver' => 'google_b' ] );

		try
		{
			$client = self::getClient( $app_info, $proxy );
			$client->setPrompt( 'consent' );
			$url = $client->createAuthUrl();
		}
		catch ( Exception $e )
		{
			self::error( $e->getMessage() );
		}

		return $url;
	}

	public static function callbackURL ()
	{
		return site_url() . '/?google_b_callback=1';
	}

	public static function getAccessToken ()
	{
		$app_id = Session::get( 'app_id' );
		$proxy  = Session::get( 'proxy' );
		$code   = Request::get( 'code', '', 'str' );

		if ( empty( $app_id ) || empty( $code ) )
		{
			return FALSE;
		}

		$app_info = DB::fetch( 'apps', [ 'id' => $app_id, 'driver' => 'google_b' ] );

		Session::remove( 'app_id' );
		Session::remove( 'proxy' );

		try
		{
			$client = self::getClient( $app_info, $proxy );
			$client->fetchAccessTokenWithAuthCode( $code );
			$access_token = $client->getAccessToken();

			if ( ! ( isset( $access_token[ 'access_token' ] ) && isset( $access_token[ 'refresh_token' ] ) ) )
			{
				throw new Exception( json_encode( ( object ) [ 'error' => ( object ) [ 'message' => 'Failed to get access token!' ] ] ) );
			}
		}
		catch ( Exception $e )
		{
			self::error();
		}

		self::authorize( $app_info, $access_token[ 'access_token' ], $access_token[ 'refresh_token' ], $proxy );
	}

	public static function authorize ( $app_info, $access_token, $refresh_token, $proxy )
	{
		try
		{
			$client = self::getClient( $app_info, $proxy );
			$client->setAccessToken( $access_token );

			$gmb      = new Google_Service_MyBusiness( $client );
			$accounts = $gmb->accounts->listAccounts()->getAccounts();

			foreach ( $accounts as $account )
			{
				$id          = $account->name;
				$name        = isset( $account->accountName ) && ! empty( $account->accountName ) ? esc_html( $account->accountName ) : '-';
				$profile_pic = isset( $account->profilePhotoUrl ) && ! empty( $account->profilePhotoUrl ) ? esc_html( $account->profilePhotoUrl ) : '';

				$checkUserExist = DB::fetch( 'accounts', [
					'blog_id'    => Helper::getBlogId(),
					'user_id'    => get_current_user_id(),
					'driver'     => 'google_b',
					'profile_id' => $id
				] );

				if ( ! get_current_user_id() > 0 )
				{
					Helper::response( FALSE, fsp__( 'The current WordPress user ID is not available. Please, check if your security plugins prevent user authorization.' ) );
				}

				$dataSQL = [
					'blog_id'     => Helper::getBlogId(),
					'user_id'     => get_current_user_id(),
					'driver'      => 'google_b',
					'name'        => $name,
					'profile_id'  => $id,
					'email'       => '',
					'username'    => $name,
					'profile_pic' => $profile_pic,
					'proxy'       => $proxy
				];

				if ( $checkUserExist )
				{
					$accId = $checkUserExist[ 'id' ];

					DB::DB()->update( DB::table( 'accounts' ), $dataSQL, [ 'id' => $accId ] );
					DB::DB()->delete( DB::table( 'account_access_tokens' ), [ 'account_id' => $accId ] );
				}
				else
				{
					DB::DB()->insert( DB::table( 'accounts' ), $dataSQL );
					$accId = DB::DB()->insert_id;
				}

				DB::DB()->insert( DB::table( 'account_access_tokens' ), [
					'account_id'    => $accId,
					'app_id'        => $app_info[ 'id' ],
					'access_token'  => $access_token,
					'refresh_token' => $refresh_token,
					'expires_on'    => Date::dateTimeSQL( 'now', '+55 minutes' )
				] );

				self::refetch_account($app_info, $access_token, $accId, $id, $proxy);
			}
		}
		catch ( Exception $e )
		{
			self::error( $e->getMessage() );
		}

		self::closeWindow();
	}

	public static function getStats ( $post_id, $accessToken, $accessTokenSecret, $appId, $proxy )
	{
		return [
			'comments' => 0,
			'like'     => 0,
			'shares'   => 0,
			'details'  => ''
		];
	}

	public static function checkAccount ( $app_id, $account, $access_token, $proxy )
	{
		$result   = [
			'error'     => TRUE,
			'error_msg' => NULL
		];
		$app_info = DB::fetch( 'apps', [ 'id' => $app_id, 'driver' => 'google_b' ] );

		try
		{
			$client = self::getClient( $app_info, $proxy );
			$client->setAccessToken( $access_token );

			$gmb = new Google_Service_MyBusiness( $client );
			$gmb->accounts->get( $account );

			$result[ 'error' ] = FALSE;
		}
		catch ( Exception $e )
		{
			$error = json_decode( $e->getMessage() );

			$result[ 'error' ]     = TRUE;
			$result[ 'error_msg' ] = fsp__( 'Error! %s', [ esc_html( $error->error->message ) ] );
		}

		return $result;
	}

	public static function refetch_account ( $app_info, $access_token, $account_id, $profile_id, $proxy )
	{
		try
		{
			$client = self::getClient( $app_info, $proxy );
			$client->setAccessToken( $access_token );

			$gmb = new Google_Service_MyBusiness( $client );

			$locations = self::getAllLocations( $gmb, $profile_id );
			$get_nodes = DB::DB()->get_results( DB::DB()->prepare( 'SELECT id, node_id FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id = %d', [ $account_id ] ), ARRAY_A );
			$my_nodes  = [];

			foreach ( $get_nodes as $node )
			{
				$my_nodes[ $node[ 'id' ] ] = $node[ 'node_id' ];
			}

			foreach ( $locations as $location )
			{
				if ( ! in_array( $location[ 'name' ], $my_nodes ) )
				{
					DB::DB()->insert( DB::table( 'account_nodes' ), [
						'blog_id'    => Helper::getBlogId(),
						'user_id'    => get_current_user_id(),
						'driver'     => 'google_b',
						'account_id' => $account_id,
						'node_type'  => 'location',
						'node_id'    => $location[ 'name' ],
						'name'       => $location[ 'locationName' ],
						'category'   => $location[ 'category' ]
					] );
				}
				else
				{
					DB::DB()->update( DB::table( 'account_nodes' ), [
						'name' => $location[ 'locationName' ]
					], [
						'account_id' => $account_id,
						'node_id'    => $location[ 'name' ]
					] );
				}

				unset( $my_nodes[ array_search( $location[ 'name' ], $my_nodes ) ] );
			}

			if ( ! empty( $my_nodes ) )
			{
				DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
				DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
			}
		}
		catch ( Exception $e )
		{
			return [];
		}

		return [ 'status' => TRUE ];
	}

	public static function accessToken ( $token_info )
	{
		if ( ( Date::epoch() + 30 ) > Date::epoch( $token_info[ 'expires_on' ] ) )
		{
			return self::refreshToken( $token_info );
		}

		return $token_info[ 'access_token' ];
	}

	private static function getClient ( $app_info, $proxy = NULL )
	{
		if ( is_null( self::$_client ) )
		{
			$client = new Google_Client();

			if ( $proxy )
			{
				$http_client = new Client( [
					'proxy'  => $proxy,
					'verify' => FALSE
				] );

				$client->setHttpClient( $http_client );
			}

			$client->setRedirectUri( self::callbackURL() );
			$client->setClientId( $app_info[ 'app_id' ] );
			$client->setClientSecret( $app_info[ 'app_secret' ] );
			$client->setAccessType( 'offline' );
			$client->addScope( [
				'https://www.googleapis.com/auth/business.manage',
				'https://www.googleapis.com/auth/userinfo.profile',
				'email',
				'profile'
			] );

			self::$_client = $client;
		}

		return self::$_client;
	}

	private static function refreshToken ( $token_info )
	{
		$app_id = $token_info[ 'app_id' ];

		$account_info = DB::fetch( 'accounts', $token_info[ 'account_id' ] );
		$proxy        = $account_info[ 'proxy' ];

		$app_info      = DB::fetch( 'apps', $app_id );
		$refresh_token = $token_info[ 'refresh_token' ];

		try
		{
			$client          = self::getClient( $app_info, $proxy );
			$refreshed_token = $client->refreshToken( $refresh_token );
		}
		catch ( Exception $e )
		{
			return '';
		}

		$access_token = $refreshed_token[ 'access_token' ];

		DB::DB()->update( DB::table( 'account_access_tokens' ), [
			'access_token' => $access_token,
			'expires_on'   => Date::dateTimeSQL( 'now', '+55 minutes' )
		], [ 'id' => $token_info[ 'id' ] ] );

		return $access_token;
	}

	private static function getAllLocations ( $gmb, $account )
	{
		$all_locations   = [];
		$next_page_token = NULL;

		while ( TRUE )
		{
			$locations = $gmb->accounts_locations->listAccountsLocations( $account, [
				'pageToken' => $next_page_token
			] );

			foreach ( $locations->getLocations() as $location )
			{
				if ( isset( $location->address->addressLines ) )
				{
					$category = implode( ', ', $location->address->addressLines );
				}
				else if ( isset( $location->address->locality ) )
				{
					$category = $location->address->regionCode . ', ' . $location->address->locality;
				}

				$all_locations[] = [
					'category'     => $category ? esc_html( $category ) : '',
					'name'         => $location->name,
					'locationName' => $location->locationName
				];
			}

			if ( ! empty( $locations->getNextPageToken() ) )
			{
				$next_page_token = $locations->getNextPageToken();
			}
			else
			{
				break;
			}
		}

		return $all_locations;
	}
}
