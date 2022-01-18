<?php

namespace FSPoster\App\Providers;

use FSPoster\App\Libraries\fb\Facebook;
use FSPoster\App\Libraries\medium\Medium;
use FSPoster\App\Libraries\reddit\Reddit;
use FSPoster\App\Libraries\tumblr\Tumblr;
use FSPoster\App\Libraries\twitter\Twitter;
use FSPoster\App\Libraries\blogger\Blogger;
use FSPoster\App\Libraries\ok\OdnoKlassniki;
use FSPoster\App\Libraries\linkedin\Linkedin;
use FSPoster\App\Libraries\pinterest\Pinterest;
use FSPoster\App\Libraries\google\GoogleMyBusinessAPI;
use FSPoster\App\Libraries\instagram\InstagramAppMethod;

class FrontEnd
{
	public function __construct ()
	{
		if ( ! Helper::pluginDisabled() )
		{
			add_action( 'wp', [ $this, 'boot' ] );
		}
	}

	public function boot ()
	{
		$this->checkVisits();

		$this->fetchAccessToken();

		$this->fbRedirect();
		$this->instagramRedirect();
		$this->twitterRedirect();
		$this->linkedinRedirect();
		$this->pinterestRedirect();
		$this->redditRedirect();
		$this->tumblrRedirect();
		$this->okRedirect();
		$this->mediumRedirect();
		$this->gmbRedirect();
		$this->bloggerRedirect();

		$this->standartFSApp();
	}

	public function checkVisits ()
	{
		if ( is_single() || is_page() )
		{
			$feed_id = Request::get( 'feed_id', '0', 'int' );

			if ( ! isset( $_COOKIE[ 'fsp_last_visited_' . $feed_id ] ) && isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) && ! preg_match( '/abacho|accona|AddThis|AdsBot|ahoy|AhrefsBot|AISearchBot|alexa|altavista|anthill|appie|applebot|arale|araneo|AraybOt|ariadne|arks|aspseek|ATN_Worldwide|Atomz|baiduspider|baidu|bbot|bingbot|bing|Bjaaland|BlackWidow|BotLink|bot|boxseabot|bspider|calif|CCBot|ChinaClaw|christcrawler|CMC\/0\.01|combine|confuzzledbot|contaxe|CoolBot|cosmos|crawler|crawlpaper|crawl|curl|cusco|cyberspyder|cydralspider|dataprovider|digger|DIIbot|DotBot|downloadexpress|DragonBot|DuckDuckBot|dwcp|EasouSpider|ebiness|ecollector|elfinbot|esculapio|ESI|esther|eStyle|Ezooms|facebookexternalhit|facebook|facebot|fastcrawler|FatBot|FDSE|FELIX IDE|fetch|fido|find|Firefly|fouineur|Freecrawl|froogle|gammaSpider|gazz|gcreep|geona|Getterrobo-Plus|get|girafabot|golem|googlebot|\-google|grabber|GrabNet|griffon|Gromit|gulliver|gulper|hambot|havIndex|hotwired|htdig|HTTrack|ia_archiver|iajabot|IDBot|Informant|InfoSeek|InfoSpiders|INGRID\/0\.1|inktomi|inspectorwww|Internet Cruiser Robot|irobot|Iron33|JBot|jcrawler|Jeeves|jobo|KDD\-Explorer|KIT\-Fireball|ko_yappo_robot|label\-grabber|larbin|legs|libwww-perl|linkedin|Linkidator|linkwalker|Lockon|logo_gif_crawler|Lycos|m2e|majesticsEO|marvin|mattie|mediafox|mediapartners|MerzScope|MindCrawler|MJ12bot|mod_pagespeed|moget|Motor|msnbot|muncher|muninn|MuscatFerret|MwdSearch|NationalDirectory|naverbot|NEC\-MeshExplorer|NetcraftSurveyAgent|NetScoop|NetSeer|newscan\-online|nil|none|Nutch|ObjectsSearch|Occam|openstat.ru\/Bot|packrat|pageboy|ParaSite|patric|pegasus|perlcrawler|phpdig|piltdownman|Pimptrain|pingdom|pinterest|pjspider|PlumtreeWebAccessor|PortalBSpider|psbot|rambler|Raven|RHCS|RixBot|roadrunner|Robbie|robi|RoboCrawl|robofox|Scooter|Scrubby|Search\-AU|searchprocess|search|SemrushBot|Senrigan|seznambot|Shagseeker|sharp\-info\-agent|sift|SimBot|Site Valet|SiteSucker|skymob|SLCrawler\/2\.0|slurp|snooper|solbot|speedy|spider_monkey|SpiderBot\/1\.0|spiderline|spider|suke|tach_bw|TechBOT|TechnoratiSnoop|templeton|teoma|titin|topiclink|twitterbot|twitter|UdmSearch|Ukonline|UnwindFetchor|URL_Spider_SQL|urlck|urlresolver|Valkyrie libwww\-perl|verticrawl|Victoria|void\-bot|Voyager|VWbot_K|wapspider|WebBandit\/1\.0|webcatcher|WebCopier|WebFindBot|WebLeacher|WebMechanic|WebMoose|webquest|webreaper|webspider|webs|WebWalker|WebZip|wget|whowhere|winona|wlm|WOLP|woriobot|WWWC|XGET|xing|yahoo|YandexBot|YandexMobileBot|yandex|yeti|Zeus/i', $_SERVER[ 'HTTP_USER_AGENT' ] ) && ! empty( $_SERVER[ 'HTTP_REFERER' ] ) )
			{
				global $post;

				if ( isset( $post->ID ) && $feed_id > 0 )
				{
					$post_id = $post->ID;

					DB::DB()->query( DB::DB()->prepare( "UPDATE " . DB::table( 'feeds' ) . " SET visit_count=visit_count+1 WHERE id=%d AND post_id=%d AND status = 'ok'", [
						$feed_id,
						$post_id
					] ) );

					setcookie( 'fsp_last_visited_' . $feed_id, '1', Date::epoch( 'now', '+30 seconds' ), COOKIEPATH, COOKIE_DOMAIN );
				}
			}
		}
	}

	public function fetchAccessToken ()
	{
		if ( Request::get( 'fb_callback', '0', 'int' ) === 1 )
		{
			Facebook::getAccessToken();
		}
		if ( Request::get( 'instagram_callback', '0', 'int' ) === 1 )
		{
			InstagramAppMethod::getAccessToken();
		}
		else if ( Request::get( 'twitter_callback', '0', 'int' ) === 1 )
		{
			Twitter::getAccessToken();
		}
		else if ( Request::get( 'linkedin_callback', '0', 'int' ) === 1 )
		{
			Linkedin::getAccessToken();
		}
		else if ( Request::get( 'pinterest_callback', '0', 'int' ) === 1 || Request::get( 'state', '', 'str' ) === 'pinterest_callback' )
		{
			Pinterest::getAccessToken();
		}
		else if ( Request::get( 'reddit_callback', '0', 'int' ) === 1 )
		{
			Reddit::getAccessToken();
		}
		else if ( Request::get( 'tumblr_callback', '0', 'int' ) === 1 )
		{
			Tumblr::getAccessToken();
		}
		else if ( Request::get( 'ok_callback', '0', 'int' ) === 1 )
		{
			Odnoklassniki::getAccessToken();
		}
		else if ( Request::get( 'medium_callback', '0', 'int' ) === 1 )
		{
			Medium::getAccessToken();
		}
		else if ( Request::get( 'google_b_callback', '0', 'int' ) === 1 )
		{
			GoogleMyBusinessAPI::getAccessToken();
		}
		else if ( Request::get( 'blogger_callback', '0', 'int' ) === 1 )
		{
			Blogger::getAccessToken();
		}
	}

	public function fbRedirect ()
	{
		$appId = Request::get( 'fb_app_redirect', '0', 'int' );

		if ( $appId > 0 )
		{
			header( 'Location: ' . Facebook::getLoginURL( $appId ) );
			exit();
		}

	}

	public function instagramRedirect ()
	{
		$appId = Request::get( 'instagram_app_redirect', '0', 'int' );

		if ( $appId > 0 )
		{
			header( 'Location: ' . InstagramAppMethod::getLoginURL( $appId ) );
			exit();
		}

	}

	public function twitterRedirect ()
	{
		$appId = Request::get( 'twitter_app_redirect', '0', 'int' );

		if ( $appId > 0 )
		{
			header( 'Location:' . Twitter::getLoginURL( $appId ) );
			exit();
		}
	}

	public function linkedinRedirect ()
	{
		$appId = Request::get( 'linkedin_app_redirect', '0', 'int' );

		if ( $appId > 0 )
		{
			header( 'Location: ' . Linkedin::getLoginURL( $appId ) );
			exit();
		}
	}

	public function pinterestRedirect ()
	{
		$appId = Request::get( 'pinterest_app_redirect', '0', 'int' );

		if ( $appId > 0 )
		{
			header( 'Location: ' . Pinterest::getLoginURL( $appId ) );
			exit();
		}
	}

	public function redditRedirect ()
	{
		$appId = Request::get( 'reddit_app_redirect', '0', 'int' );

		if ( $appId > 0 )
		{
			header( 'Location: ' . Reddit::getLoginURL( $appId ) );
			exit();
		}
	}

	public function tumblrRedirect ()
	{
		$appId = Request::get( 'tumblr_app_redirect', '0', 'int' );

		if ( $appId > 0 )
		{
			header( 'Location: ' . Tumblr::getLoginURL( $appId ) );
			exit();
		}
	}

	public function okRedirect ()
	{
		$appId = Request::get( 'ok_app_redirect', '0', 'int' );

		if ( $appId > 0 )
		{
			header( 'Location: ' . Odnoklassniki::getLoginURL( $appId ) );
			exit();
		}
	}

	public function mediumRedirect ()
	{
		$appId = Request::get( 'medium_app_redirect', '0', 'int' );

		if ( $appId > 0 )
		{
			header( 'Location: ' . Medium::getLoginURL( $appId ) );
			exit();
		}
	}

	public function gmbRedirect ()
	{
		$appId = Request::get( 'google_b_app_redirect', '0', 'int' );

		if ( $appId > 0 )
		{
			header( 'Location: ' . GoogleMyBusinessAPI::getLoginURL( $appId ) );
			exit();
		}
	}

	public function bloggerRedirect ()
	{
		$appId = Request::get( 'blogger_app_redirect', '0', 'int' );

		if ( $appId > 0 )
		{
			header( 'Location: ' . Blogger::getLoginURL( $appId ) );
			exit();
		}
	}

	public function standartFSApp ()
	{
		$supportedFSApps = [
			'fb',
			'instagram',
			'twitter',
			'linkedin',
			'pinterest',
			'reddit',
			'tumblr',
			'ok',
			'medium',
			'google_b',
			'blogger'
		];

		$sn       = Request::get( 'sn', '', 'string', $supportedFSApps );
		$callback = Request::get( 'fs_app_redirect', '0', 'num', [ '1' ] );
		$proxy    = Request::get( 'proxy', '', 'string' );

		if ( ! empty( $proxy ) )
		{
			$proxy = strrev( $proxy );
		}

		if ( ! $callback || empty( $sn ) )
		{
			return;
		}

		$appInf = DB::fetch( 'apps', [
			'driver'      => $sn,
			'is_standart' => 1
		] );

		if ( $sn === 'fb' )
		{
			$access_token = Request::get( 'access_token', '', 'string' );

			if ( empty( $access_token ) )
			{
				return;
			}

			Facebook::authorize( $appInf[ 'id' ], $access_token, $proxy );
		}
		else if ( $sn === 'instagram' )
		{
			$access_token = Request::get( 'access_token', '', 'string' );

			if ( empty( $access_token ) )
			{
				return;
			}

			InstagramAppMethod::authorize( $appInf[ 'id' ], $access_token, $proxy );
		}
		else if ( $sn === 'twitter' )
		{
			$oauth_token        = Request::get( 'oauth_token', '', 'string' );
			$oauth_token_secret = Request::get( 'oauth_token_secret', '', 'string' );

			if ( empty( $oauth_token ) || empty( $oauth_token_secret ) )
			{
				return;
			}

			Twitter::authorize( $appInf, $oauth_token, $oauth_token_secret, $proxy );
		}
		else if ( $sn === 'linkedin' )
		{
			$access_token  = Request::get( 'access_token', '', 'string' );
			$expire_in     = Request::get( 'expire_in', '', 'string' );
			$refresh_token = Request::get( 'refresh_token', '', 'string' );

			if ( empty( $access_token ) || empty( $expire_in ) )
			{
				return;
			}

			Linkedin::authorize( $appInf[ 'id' ], $access_token, $expire_in, $refresh_token, $proxy );
		}
		else if ( $sn === 'pinterest' )
		{
			$accessToken = Request::get( 'access_token', '', 'string' );
			$refreshToken = Request::get( 'refresh_token', '', 'string' );
			$expiresIn = Request::get( 'expires_in', '', 'string' );

			if ( empty( $accessToken ) || empty( $refreshToken ) )
			{
				return;
			}

			Pinterest::authorize( $appInf[ 'id' ], $accessToken, $refreshToken, $expiresIn, $proxy );
		}
		else if ( $sn === 'reddit' )
		{
			$access_token = Request::get( 'access_token', '', 'string' );
			$refreshToken = Request::get( 'refresh_token', '', 'string' );
			$expiresIn    = Request::get( 'expires_in', '', 'string' );

			if ( empty( $access_token ) || empty( $refreshToken ) || empty( $expiresIn ) )
			{
				return;
			}

			Reddit::authorize( $appInf[ 'id' ], $access_token, $refreshToken, $expiresIn, $proxy );
		}
		else if ( $sn === 'tumblr' )
		{
			$access_token        = Request::get( 'access_token', '', 'string' );
			$access_token_secret = Request::get( 'access_token_secret', '', 'string' );

			if ( empty( $access_token ) || empty( $access_token_secret ) )
			{
				return;
			}

			date_default_timezone_set( 'Asia/Baku' );

			Tumblr::authorize( $appInf[ 'id' ], $appInf[ 'app_key' ], $appInf[ 'app_secret' ], $access_token, $access_token_secret, $proxy );
		}
		else if ( $sn === 'ok' )
		{
			$access_token = Request::get( 'access_token', '', 'string' );
			$refreshToken = Request::get( 'refresh_token', '', 'string' );
			$expiresIn    = Request::get( 'expires_in', '', 'string' );

			if ( empty( $access_token ) || empty( $refreshToken ) || empty( $expiresIn ) )
			{
				return;
			}

			OdnoKlassniki::authorize( $appInf[ 'id' ], $appInf[ 'app_key' ], $appInf[ 'app_secret' ], $access_token, $refreshToken, $expiresIn, $proxy );
		}
		else if ( $sn === 'medium' )
		{
			$access_token = Request::get( 'access_token', '', 'string' );
			$refreshToken = Request::get( 'refresh_token', '', 'string' );
			$expiresIn    = Request::get( 'expires_in', '', 'string' );

			if ( empty( $access_token ) || empty( $refreshToken ) || empty( $expiresIn ) )
			{
				return;
			}

			Medium::authorizeMediumUser( $appInf[ 'id' ], $access_token, $refreshToken, $expiresIn, $proxy );
		}
		else if ( $sn === 'google_b' )
		{
			$access_token = Request::get( 'access_token', '', 'string' );
			$refreshToken = Request::get( 'refresh_token', '', 'string' );
			$expiresIn    = Request::get( 'expires_in', '', 'string' );

			if ( empty( $access_token ) || empty( $refreshToken ) || empty( $expiresIn ) )
			{
				return;
			}

			GoogleMyBusinessAPI::authorize( $appInf, $access_token, $refreshToken, $proxy );
		}
		else if ( $sn === 'blogger' )
		{
			$access_token = Request::get( 'access_token', '', 'string' );
			$refreshToken = Request::get( 'refresh_token', '', 'string' );
			$expiresIn    = Request::get( 'expires_in', '', 'string' );

			if ( empty( $access_token ) || empty( $refreshToken ) || empty( $expiresIn ) )
			{
				return;
			}

			Blogger::authorize( $appInf, $access_token, $proxy, $refreshToken, $expiresIn );
		}
	}
}
