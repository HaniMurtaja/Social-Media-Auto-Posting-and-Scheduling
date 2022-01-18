<?php

namespace FSPoster\App\Providers;

use FSPoster\App\Libraries\vk\Vk;
use FSPoster\App\Libraries\fb\Facebook;
use FSPoster\App\Libraries\medium\Medium;
use FSPoster\App\Libraries\reddit\Reddit;
use FSPoster\App\Libraries\tumblr\Tumblr;
use FSPoster\App\Libraries\twitter\Twitter;
use FSPoster\App\Libraries\ok\OdnoKlassniki;
use FSPoster\App\Libraries\linkedin\Linkedin;
use FSPoster\App\Libraries\telegram\Telegram;
use FSPoster\App\Libraries\pinterest\Pinterest;
use FSPoster\App\Libraries\wordpress\Wordpress;
use FSPoster\App\Libraries\fb\FacebookCookieApi;
use FSPoster\App\Libraries\instagram\InstagramApi;
use FSPoster\App\Libraries\twitter\TwitterPrivateAPI;
use FSPoster\App\Libraries\google\GoogleMyBusinessAPI;
use FSPoster\App\Libraries\pinterest\PinterestCookieApi;
use FSPoster\App\Libraries\tumblr\TumblrLoginPassMethod;

class AccountService
{
	public static function checkAccounts ()
	{
		$all_accountsSQL = DB::DB()->prepare( 'SELECT * FROM ' . DB::table( 'accounts' ) . ' WHERE ((id IN (SELECT account_id FROM ' . DB::table( 'account_status' ) . ')) OR (id IN (SELECT account_id FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (SELECT node_id FROM ' . DB::table( 'account_node_status' ) . ')))) AND `blog_id` = %d', [
			Helper::getBlogId()
		] );
		$all_accounts    = DB::DB()->get_results( $all_accountsSQL, ARRAY_A );

		foreach ( $all_accounts as $account )
		{
			$node_info = Helper::getAccessToken( 'account', $account[ 'id' ] );

			$appId             = $node_info[ 'app_id' ];
			$driver            = $node_info[ 'driver' ];
			$accessToken       = $node_info[ 'access_token' ];
			$accessTokenSecret = $node_info[ 'access_token_secret' ];
			$proxy             = $node_info[ 'info' ][ 'proxy' ];
			$options           = $node_info[ 'options' ];
			$accountId         = $node_info[ 'account_id' ];

			if ( $driver === 'fb' )
			{
				if ( empty( $options ) ) // app method
				{
					$result = Facebook::checkAccount( $accessToken, $proxy );
				}
				else // cookie method
				{
					$fbDriver = new FacebookCookieApi( $accountId, $options, $proxy );
					$result   = $fbDriver->checkAccount();
				}
			}
			else if ( $driver === 'twitter' )
			{
				if ( empty( $options ) )
				{
					$result = Twitter::checkAccount( $appId, $accessToken, $accessTokenSecret, $proxy );
				}
				else
				{
					$tp     = new TwitterPrivateAPI( $options, $proxy );
					$result = $tp->checkAccount();
				}
			}
			else if ( $driver === 'instagram' )
			{
				$result = InstagramApi::checkAccount( $node_info[ 'info' ] );
			}
			else if ( $driver === 'linkedin' )
			{
				$result = Linkedin::checkAccount( $accessToken, $proxy );
			}
			else if ( $driver === 'vk' )
			{
				$result = Vk::checkAccount( $accessToken, $proxy );
			}
			else if ( $driver === 'pinterest' )
			{
				if ( empty( $options ) ) // app method
				{
					$result = Pinterest::checkAccount( $accessToken, $proxy );
				}
				else // cookie method
				{
					$getCookie = DB::fetch( 'account_sessions', [
						'driver'   => 'pinterest',
						'username' => $node_info[ 'username' ]
					] );

					$pinterest = new PinterestCookieApi( $getCookie[ 'cookies' ], $proxy );
					$result    = $pinterest->checkAccount();
				}
			}
			else if ( $driver === 'reddit' )
			{
				$result = Reddit::checkAccount( $accessToken, $proxy );
			}
			else if ( $driver === 'tumblr' )
			{
				if ( empty( $account[ 'password' ] ) )
				{
					$result = Tumblr::checkAccount( $accessToken, $accessTokenSecret, $appId, $proxy );
				}
				else
				{
					$tm     = new TumblrLoginPassMethod( $account[ 'email' ], $account[ 'password' ], $proxy );
					$result = $tm->checkAccount();
				}
			}
			else if ( $driver === 'ok' )
			{
				$appInf = DB::fetch( 'apps', [ 'id' => $appId ] );

				$result = OdnoKlassniki::checkAccount( $accessToken, $appInf[ 'app_key' ], $appInf[ 'app_secret' ], $proxy );
			}
			else if ( $driver === 'google_b' )
			{
				$result = GoogleMyBusinessAPI::checkAccount( $appId, $account[ 'profile_id' ], $accessToken, $proxy );
			}
			else if ( $driver === 'telegram' )
			{
				$telegram = new Telegram( $options, $proxy );
				$result   = $telegram->checkAccount();
			}
			else if ( $driver === 'medium' )
			{
				$result = Medium::checkAccount( $accessToken, $proxy );
			}
			else if ( $driver === 'wordpress' )
			{
				$node_info[ 'password' ] = substr( $node_info[ 'password' ], 0, 9 ) === '(-F-S-P-)' ? explode( '(-F-S-P-)', base64_decode( str_rot13( substr( $node_info[ 'password' ], 9 ) ) ) )[ 0 ] : $node_info[ 'password' ];

				$wordpress = new Wordpress( $options, $node_info[ 'username' ], $node_info[ 'password' ], $proxy );
				$result    = $wordpress->checkAccount();
			}

			if ( $result[ 'error' ] )
			{
				$error_msg = isset( $result[ 'error_msg' ] ) ? substr( $result[ 'error_msg' ], 0, 300 ) : fsp__( 'The account is disconnected from the FS Poster plugin. Please add your account to the plugin without deleting the account from the plugin; as a result, account settings will remain as it is.' );

				self::disable_account( $account[ 'id' ], $error_msg );
			}
			else
			{
				DB::DB()->update( DB::table( 'accounts' ), [
					'status'    => NULL,
					'error_msg' => NULL
				], [
					'id' => $account[ 'id' ]
				] );
			}

			Helper::setOption( 'check_accounts_last', Date::epoch() );
		}
	}

	public static function disable_account ( $account_id, $error_msg )
	{
		DB::DB()->update( DB::table( 'accounts' ), [
			'status'    => 'error',
			'error_msg' => $error_msg
		], [ 'id' => $account_id ] );

		if ( Helper::getOption( 'check_accounts_disable', 0 ) )
		{
			DB::DB()->delete( DB::table( 'account_status' ), [
				'account_id' => $account_id
			] );

			DB::DB()->query( DB::DB()->prepare( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN (SELECT id FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id = %d)', [ $account_id ] ) );
		}
	}
}