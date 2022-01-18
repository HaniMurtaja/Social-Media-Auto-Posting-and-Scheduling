<?php

namespace FSPoster\App\Providers;

use DateTime;
use Exception;
use DateTimeZone;
use Abraham\TwitterOAuth\TwitterOAuth;
use FSPoster\App\Libraries\reddit\Reddit;
use FSPoster\App\Libraries\medium\Medium;
use FSPoster\App\Libraries\blogger\Blogger;
use FSPoster\App\Libraries\ok\OdnoKlassniki;
use FSPoster\App\Libraries\linkedin\Linkedin;
use FSPoster\App\Libraries\pinterest\Pinterest;
use FSPoster\App\Libraries\google\GoogleMyBusinessAPI;

class Helper
{
	use WPHelper, URLHelper;

	public static function pluginDisabled ()
	{
		return Helper::getOption( 'plugin_disabled', '0', TRUE ) > 0;
	}

	public static function response ( $status, $arr = [] )
	{
		$arr = is_array( $arr ) ? $arr : ( is_string( $arr ) ? [ 'error_msg' => $arr ] : [] );

		if ( $status )
		{
			$arr[ 'status' ] = 'ok';
		}
		else
		{
			$arr[ 'status' ] = 'error';
			if ( ! isset( $arr[ 'error_msg' ] ) )
			{
				$arr[ 'error_msg' ] = 'Error!';
			}
		}

		echo json_encode( $arr );
		exit();
	}

	public static function spintax ( $text )
	{
		$text = is_string( $text ) ? (string) $text : '';

		return preg_replace_callback( '/\{(((?>[^\{\}]+)|(?R))*)\}/x', function ( $text ) {
			$text  = Helper::spintax( $text[ 1 ] );
			$parts = explode( '|', $text );

			return $parts[ array_rand( $parts ) ];
		}, $text );
	}

	public static function cutText ( $text, $n = 35 )
	{
		return mb_strlen( $text, 'UTF-8' ) > $n ? mb_substr( $text, 0, $n, 'UTF-8' ) . '...' : $text;
	}

	public static function getVersion ()
	{
		$plugin_data = get_file_data( FS_ROOT_DIR . '/init.php', [ 'Version' => 'Version' ], FALSE );

		return isset( $plugin_data[ 'Version' ] ) ? $plugin_data[ 'Version' ] : '1.0.0';
	}

	public static function getInstalledVersion ()
	{
		$ver = Helper::getOption( 'poster_plugin_installed', '0', TRUE );

		return ( $ver === '1' || empty( $ver ) ) ? '1.0.0' : $ver;
	}

	public static function debug ()
	{
		error_reporting( E_ALL );
		ini_set( 'display_errors', 'on' );
	}

	public static function disableDebug ()
	{
		error_reporting( 0 );
		ini_set( 'display_errors', 'off' );
	}

	public static function fetchStatisticOptions ()
	{
		$getOptions = Curl::getURL( FS_API_URL . 'api.php?act=statistic_option' );
		$getOptions = json_decode( $getOptions, TRUE );

		$options = '<option selected disabled>Please select</option>';
		foreach ( $getOptions as $optionName => $optionValue )
		{
			$options .= '<option value="' . htmlspecialchars( $optionName ) . '">' . htmlspecialchars( $optionValue ) . '</option>';
		}

		return $options;
	}

	public static function hexToRgb ( $hex )
	{
		if ( strpos( '#', $hex ) === 0 )
		{
			$hex = substr( $hex, 1 );
		}

		return sscanf( $hex, "%02x%02x%02x" );
	}

	public static function downloadRemoteFile ( $destination, $sourceURL )
	{
		file_put_contents( $destination, Curl::getURL( $sourceURL ) );
	}

	private static $_options_cache = [];

	public static function getOption ( $optionName, $default = NULL, $network_option = FALSE )
	{
		if ( ! isset( self::$_options_cache[ $optionName ] ) )
		{
			$network_option = ! is_multisite() && $network_option == TRUE ? FALSE : $network_option;
			$fnName         = $network_option ? 'get_site_option' : 'get_option';

			self::$_options_cache[ $optionName ] = $fnName( 'fs_' . $optionName, $default );
		}

		return self::$_options_cache[ $optionName ];
	}

	public static function setOption ( $optionName, $optionValue, $network_option = FALSE, $autoLoad = NULL )
	{
		$network_option = ! is_multisite() && $network_option == TRUE ? FALSE : $network_option;
		$fnName         = $network_option ? 'update_site_option' : 'update_option';

		self::$_options_cache[ $optionName ] = $optionValue;

		$arguments = [ 'fs_' . $optionName, $optionValue ];

		if ( ! is_null( $autoLoad ) && ! $network_option )
		{
			$arguments[] = $autoLoad;
		}

		return call_user_func_array( $fnName, $arguments );
	}

	public static function deleteOption ( $optionName, $network_option = FALSE )
	{
		$network_option = ! is_multisite() && $network_option == TRUE ? FALSE : $network_option;
		$fnName         = $network_option ? 'delete_site_option' : 'delete_option';

		if ( isset( self::$_options_cache[ $optionName ] ) )
		{
			unset( self::$_options_cache[ $optionName ] );
		}

		return $fnName( 'fs_' . $optionName );
	}

	public static function setCustomSetting ( $setting_key, $setting_val, $node_type, $node_id )
	{
		if ( isset( $node_id, $node_type ) )
		{
			$node_type   = $node_type === 'account' ? 'account' : 'node';
			$setting_key = 'setting' . ':' . $node_type . ':' . $node_id . ':' . $setting_key;
			self::setOption( $setting_key, $setting_val );
		}
	}

	public static function getCustomSetting ( $setting_key, $default = NULL, $node_type = NULL, $node_id = NULL )
	{
		if ( isset( $node_id, $node_type ) )
		{
			$node_type    = $node_type === 'account' ? 'account' : 'node';
			$setting_key1 = 'setting' . ':' . $node_type . ':' . $node_id . ':' . $setting_key;
			$setting      = self::getOption( $setting_key1 );
		}

		if ( ! isset( $setting ) || $setting === FALSE )
		{
			return self::getOption( $setting_key, $default );
		}
		else
		{
			return $setting;
		}
	}

	public static function deleteCustomSettings ( $node_type, $node_id )
	{
		$like  = 'fs_setting' . ':' . $node_type . ':' . $node_id . ':' . '%';
		$table = DB::DB()->options;
		DB::DB()->query( DB::DB()->prepare( "DELETE FROM `$table` WHERE option_name LIKE %s", [ $like ] ) );
	}

	public static function removePlugin ()
	{
		$fsPurchaseKey        = Helper::getOption( 'poster_plugin_purchase_key', '', TRUE );
		$checkPurchaseCodeURL = FS_API_URL . "api.php?act=delete&purchase_code=" . urlencode( $fsPurchaseKey ) . "&domain=" . network_site_url();

		Curl::getURL( $checkPurchaseCodeURL );

		$fsTables = [
			'account_access_tokens',
			'account_node_status',
			'account_nodes',
			'account_sessions',
			'account_status',
			'grouped_accounts',
			'accounts',
			'apps',
			'feeds',
			'schedules'
		];

		foreach ( $fsTables as $tableName )
		{
			DB::DB()->query( "DROP TABLE IF EXISTS `" . DB::table( $tableName ) . "`" );
		}

		DB::DB()->query( 'DELETE FROM `' . DB::DB()->base_prefix . 'options` WHERE `option_name` LIKE "fs_%"' );
		DB::DB()->query( 'DELETE FROM `' . DB::DB()->base_prefix . 'sitemeta` WHERE `meta_key` LIKE "fs_%"' );
		DB::DB()->query( "DELETE FROM " . DB::WPtable( 'posts', TRUE ) . " WHERE post_type='fs_post_tmp' OR post_type='fs_post'" );
	}

	public static function socialIcon ( $driver )
	{
		switch ( $driver )
		{
			case 'fb':
				return "fab fa-facebook-f";
			case 'twitter':
			case 'wordpress':
			case 'medium':
			case 'reddit':
			case 'telegram':
			case 'pinterest':
			case 'linkedin':
			case 'vk':
			case 'instagram':
			case 'tumblr':
			case 'blogger':
				return "fab fa-{$driver}";
			case 'ok':
				return "fab fa-odnoklassniki";
			case 'google_b':
				return "fab fa-google";
		}
	}

	public static function standartAppRedirectURL ( $social_network )
	{
		$fsPurchaseKey = Helper::getOption( 'poster_plugin_purchase_key', '', TRUE );

		return FS_API_URL . '?purchase_code=' . $fsPurchaseKey . '&domain=' . network_site_url() . '&sn=' . $social_network . '&r_url=' . urlencode( site_url() . '/?fs_app_redirect=1&sn=' . $social_network );
	}

	public static function profilePic ( $info, $w = 40, $h = 40 )
	{
		if ( ! isset( $info[ 'driver' ] ) )
		{
			return '';
		}

		if ( empty( $info ) )
		{
			return Pages::asset( 'Base', 'img/no-photo.png' );
		}

		if ( is_array( $info ) && key_exists( 'cover', $info ) ) // nodes
		{
			if ( ! empty( $info[ 'cover' ] ) )
			{
				return $info[ 'cover' ];
			}
			else
			{
				if ( $info[ 'driver' ] === 'fb' )
				{
					return "https://graph.facebook.com/" . esc_html( $info[ 'node_id' ] ) . "/picture?redirect=1&height={$h}&width={$w}&type=normal";
				}
				else if ( $info[ 'driver' ] === 'instagram' )
				{
					return Pages::asset( 'Base', 'img/no-photo.png' );
				}
				else if ( $info[ 'driver' ] === 'tumblr' )
				{
					return "https://api.tumblr.com/v2/blog/" . esc_html( $info[ 'node_id' ] ) . "/avatar/" . ( $w > $h ? $w : $h );
				}
				else if ( $info[ 'driver' ] === 'reddit' )
				{
					return "https://www.redditstatic.com/avatars/avatar_default_10_25B79F.png";
				}
				else if ( $info[ 'driver' ] === 'google_b' )
				{
					return "https://ssl.gstatic.com/images/branding/product/2x/google_my_business_32dp.png";
				}
				else if ( $info[ 'driver' ] === 'blogger' )
				{
					return 'https://www.blogger.com/img/logo_blogger_40px.png';
				}
				else if ( $info[ 'driver' ] === 'telegram' )
				{
					return Pages::asset( 'Base', 'img/telegram.svg' );
				}
				else if ( $info[ 'driver' ] === 'linkedin' )
				{
					return Pages::asset( 'Base', 'img/no-photo.png' );
				}
			}
		}
		else
		{
			if ( $info[ 'driver' ] === 'fb' )
			{
				return "https://graph.facebook.com/" . esc_html( $info[ 'profile_id' ] ) . "/picture?redirect=1&height={$h}&width={$w}&type=normal";
			}
			else if ( $info[ 'driver' ] === 'twitter' )
			{
				static $twitter_appInfo;

				if ( is_null( $twitter_appInfo ) )
				{
					$twitter_appInfo = DB::fetch( 'apps', [ 'driver' => 'twitter' ] );
				}

				$connection = new TwitterOAuth( $twitter_appInfo[ 'app_key' ], $twitter_appInfo[ 'app_secret' ] );
				$user       = $connection->get( "users/show", [ 'screen_name' => $info[ 'username' ] ] );

				return str_replace( 'http://', 'https://', $user->profile_image_url );
			}
			else if ( $info[ 'driver' ] === 'instagram' )
			{
				if ( $info[ 'password' ] == '#####' )
				{
					return "https://graph.facebook.com/" . esc_html( $info[ 'profile_id' ] ) . "/picture?redirect=1&height={$h}&width={$w}&type=normal";
				}

				return $info[ 'profile_pic' ];
			}
			else if ( $info[ 'driver' ] === 'linkedin' )
			{
				return $info[ 'profile_pic' ];
			}
			else if ( $info[ 'driver' ] === 'vk' )
			{
				return $info[ 'profile_pic' ];
			}
			else if ( $info[ 'driver' ] === 'pinterest' )
			{
				return $info[ 'profile_pic' ];
			}
			else if ( $info[ 'driver' ] === 'reddit' )
			{
				return $info[ 'profile_pic' ];
			}
			else if ( $info[ 'driver' ] === 'tumblr' )
			{
				return "https://api.tumblr.com/v2/blog/" . esc_html( $info[ 'username' ] ) . "/avatar/" . ( $w > $h ? $w : $h );
			}
			else if ( $info[ 'driver' ] === 'ok' )
			{
				return $info[ 'profile_pic' ];
			}
			else if ( $info[ 'driver' ] === 'google_b' )
			{
				return $info[ 'profile_pic' ];
			}
			else if ( $info[ 'driver' ] === 'blogger' )
			{
				return empty( $info[ 'profile_pic' ] ) ? 'https://www.blogger.com/img/avatar_blue_m_96.png' : $info[ 'profile_pic' ];
			}
			else if ( $info[ 'driver' ] === 'telegram' )
			{
				return Pages::asset( 'Base', 'img/telegram.svg' );
			}
			else if ( $info[ 'driver' ] === 'medium' )
			{
				return $info[ 'profile_pic' ];
			}
			else if ( $info[ 'driver' ] === 'wordpress' )
			{
				return $info[ 'profile_pic' ];
			}
			else if ( $info[ 'driver' ] === 'plurk' )
			{
				return $info[ 'profile_pic' ];
			}
		}
	}

	public static function profileLink ( $info )
	{
		if ( ! isset( $info[ 'driver' ] ) )
		{
			return '';
		}

		// IF NODE
		if ( is_array( $info ) && key_exists( 'cover', $info ) ) // nodes
		{
			if ( $info[ 'driver' ] === 'fb' )
			{
				return "https://fb.com/" . esc_html( $info[ 'node_id' ] );
			}
			else if ( $info[ 'driver' ] === 'instagram' )
			{
				return "https://instagram.com/" . esc_html( $info[ 'screen_name' ] );
			}
			else if ( $info[ 'driver' ] === 'vk' )
			{
				return "https://vk.com/" . esc_html( $info[ 'screen_name' ] );
			}
			else if ( $info[ 'driver' ] === 'tumblr' )
			{
				return "https://" . esc_html( $info[ 'screen_name' ] ) . ".tumblr.com";
			}
			else if ( $info[ 'driver' ] === 'linkedin' )
			{
				return "https://www.linkedin.com/company/" . esc_html( $info[ 'node_id' ] );
			}
			else if ( $info[ 'driver' ] === 'ok' )
			{
				return "https://ok.ru/group/" . esc_html( $info[ 'node_id' ] );
			}
			else if ( $info[ 'driver' ] === 'reddit' )
			{
				return "https://www.reddit.com/r/" . esc_html( $info[ 'screen_name' ] );
			}
			else if ( $info[ 'driver' ] === 'google_b' )
			{
				return "https://business.google.com/posts/l/" . esc_html( $info[ 'node_id' ] );
			}
			else if ( $info[ 'driver' ] === 'blogger' )
			{
				return $info[ 'screen_name' ];
			}
			else if ( $info[ 'driver' ] === 'telegram' )
			{
				return "http://t.me/" . esc_html( $info[ 'screen_name' ] );
			}
			else if ( $info[ 'driver' ] === 'pinterest' )
			{
				return "https://www.pinterest.com/" . esc_html( $info[ 'screen_name' ] );
			}
			else if ( $info[ 'driver' ] === 'medium' )
			{
				return "https://medium.com/" . esc_html( $info[ 'screen_name' ] );
			}

			return '';
		}

		if ( $info[ 'driver' ] === 'fb' )
		{
			if ( empty( $info[ 'options' ] ) )
			{
				$info[ 'profile_id' ] = 'me';
			}

			return "https://fb.com/" . esc_html( $info[ 'profile_id' ] );
		}
		else if ( $info[ 'driver' ] === 'plurk' )
		{
			return "https://plurk.com/" . esc_html( $info[ 'username' ] );
		}
		else if ( $info[ 'driver' ] === 'twitter' )
		{
			return "https://twitter.com/" . esc_html( $info[ 'username' ] );
		}
		else if ( $info[ 'driver' ] === 'instagram' )
		{
			if ( $info[ 'password' ] == '#####' )
			{
				return 'https://fb.com/me';
			}

			return "https://instagram.com/" . esc_html( $info[ 'username' ] );
		}
		else if ( $info[ 'driver' ] === 'linkedin' )
		{
			return "https://www.linkedin.com/in/" . esc_html( str_replace( [
					'https://www.linkedin.com/in/',
					'http://www.linkedin.com/in/'
				], '', $info[ 'username' ] ) );
		}
		else if ( $info[ 'driver' ] === 'vk' )
		{
			return "https://vk.com/id" . esc_html( $info[ 'profile_id' ] );
		}
		else if ( $info[ 'driver' ] === 'pinterest' )
		{
			return "https://www.pinterest.com/" . esc_html( $info[ 'username' ] );
		}
		else if ( $info[ 'driver' ] === 'reddit' )
		{
			return "https://www.reddit.com/u/" . esc_html( $info[ 'username' ] );
		}
		else if ( $info[ 'driver' ] === 'tumblr' )
		{
			return "https://" . esc_html( $info[ 'username' ] ) . ".tumblr.com";
		}
		else if ( $info[ 'driver' ] === 'ok' )
		{
			return 'https://ok.ru/profile/' . urlencode( $info[ 'profile_id' ] );
		}
		else if ( $info[ 'driver' ] === 'google_b' )
		{
			return 'https://business.google.com/locations';
		}
		else if ( $info[ 'driver' ] === 'blogger' )
		{
			return 'https://www.blogger.com/profile/' . $info[ 'profile_id' ];
		}
		else if ( $info[ 'driver' ] === 'telegram' )
		{
			return "https://t.me/" . esc_html( $info[ 'username' ] );
		}
		else if ( $info[ 'driver' ] === 'medium' )
		{
			return "https://medium.com/@" . esc_html( $info[ 'username' ] );
		}
		else if ( $info[ 'driver' ] === 'wordpress' )
		{
			return $info[ 'options' ];
		}
	}

	public static function appIcon ( $appInfo )
	{
		if ( $appInfo[ 'driver' ] === 'fb' )
		{
			return "https://graph.facebook.com/" . esc_html( $appInfo[ 'app_id' ] ) . "/picture?redirect=1&height=40&width=40&type=small";
		}
		else
		{
			return Pages::asset( 'Base', 'img/app_icon.svg' );
		}
	}

	public static function replacePartOfTags ( $message, $postInf )
	{
		$postInf[ 'post_content' ] = Helper::removePageBuilderShortcodes( $postInf[ 'post_content' ] );

		$message = preg_replace_callback( '/\{content_short_?([0-9]+)?\}/', function ( $n ) use ( $postInf ) {
			if ( isset( $n[ 1 ] ) && is_numeric( $n[ 1 ] ) )
			{
				$cut = $n[ 1 ];
			}
			else
			{
				$cut = 40;
			}

			return Helper::cutText( strip_tags( $postInf[ 'post_content' ] ), $cut );
		}, $message );

		$message = preg_replace_callback( '/\{cf_(.+)\}/iU', function ( $n ) use ( $postInf ) {
			$customField = isset( $n[ 1 ] ) ? $n[ 1 ] : '';

			return get_post_meta( $postInf[ 'ID' ], $customField, TRUE );
		}, $message );

		return $message;
	}

	public static function replaceTags ( $message, $postInf, $link, $shortLink )
	{
		$message  = self::replacePartOfTags( $message, $postInf );
		$getPrice = Helper::getProductPrice( $postInf );

		$productRegularPrice = $getPrice[ 'regular' ];
		$productSalePrice    = $getPrice[ 'sale' ];

		$productCurrentPrice = ( isset( $productSalePrice ) && ! empty( $productSalePrice ) ) ? $productSalePrice : $productRegularPrice;

		$mediaId = get_post_thumbnail_id( $postInf[ 'ID' ] );

		if ( empty( $mediaId ) )
		{
			$media   = get_attached_media( 'image', $postInf[ 'ID' ] );
			$first   = reset( $media );
			$mediaId = isset( $first->ID ) ? $first->ID : 0;
		}

		$featuredImage = $mediaId > 0 ? wp_get_attachment_url( $mediaId ) : '';

		return str_replace( [
			'{id}',
			'{title}',
			'{title_ucfirst}',
			'{content_full}',
			'{link}',
			'{short_link}',
			'{product_regular_price}',
			'{product_sale_price}',
			'{product_current_price}',
			'{uniq_id}',
			'{tags}',
			'{categories}',
			'{excerpt}',
			'{author}',
			'{featured_image_url}',
			'{terms}',
		], [
			$postInf[ 'ID' ],
			$postInf[ 'post_title' ],
			ucfirst( mb_strtolower( $postInf[ 'post_title' ] ) ),
			$postInf[ 'post_content' ],
			$link,
			$shortLink,
			$productRegularPrice,
			$productSalePrice,
			$productCurrentPrice,
			uniqid(),
			Helper::getPostTerms( $postInf, 'post_tag', TRUE, FALSE ),
			Helper::getPostTerms( $postInf, 'category', TRUE, FALSE ),
			$postInf[ 'post_excerpt' ],
			get_the_author_meta( 'display_name', $postInf[ 'post_author' ] ),
			$featuredImage,
			Helper::getPostTerms( $postInf, NULL, TRUE, FALSE ),
		], $message );
	}

	public static function replaceAltTextTags ( $message, $postInf )
	{
		$message = self::replacePartOfTags( $message, $postInf );
		$mediaId = get_post_thumbnail_id( $postInf[ 'ID' ] );
		$altText = get_post( $mediaId );

		return str_replace( [
			'{site_name}',
			'{media_caption}',
			'{media_description}',
			'{media_name}',
			'{title}',
			'{tags}',
			'{excerpt}'
		], [
			get_bloginfo( 'name' ),
			$altText->post_excerpt,
			$altText->post_content,
			$altText->post_name,
			$postInf[ 'post_title' ],
			Helper::getPostTerms( $postInf, 'post_tag', TRUE, FALSE ),
			$postInf[ 'post_excerpt' ],
		], $message );
	}

	public static function scheduleFilters ( $schedule_info )
	{
		$scheduleId = $schedule_info[ 'id' ];

		/* Post type filter */
		$_postTypeFilter = $schedule_info[ 'post_type_filter' ];

		$allowedPostTypes = explode( '|', Helper::getOption( 'allowed_post_types', 'post|page|attachment|product' ) );

		if ( ! in_array( $_postTypeFilter, $allowedPostTypes ) )
		{
			$_postTypeFilter = '';
		}

		$_postTypeFilter = esc_sql( $_postTypeFilter );

		if ( ! empty( $_postTypeFilter ) )
		{
			$postTypeFilter = "AND post_type='" . $_postTypeFilter . "'";

			if ( $_postTypeFilter === 'product' && isset( $schedule_info[ 'dont_post_out_of_stock_products' ] ) && $schedule_info[ 'dont_post_out_of_stock_products' ] == 1 && function_exists( 'wc_get_product' ) )
			{
				$postTypeFilter .= ' AND IFNULL((SELECT `meta_value` FROM `' . DB::WPtable( 'postmeta', TRUE ) . '` WHERE `post_id`=tb1.id AND `meta_key`=\'_stock_status\'), \'\')<>\'outofstock\'';
			}
		}
		else
		{
			$post_types = "'" . implode( "','", array_map( 'esc_sql', $allowedPostTypes ) ) . "'";

			$postTypeFilter = "AND `post_type` IN ({$post_types})";
		}
		/* /End of post type filer */

		/* Categories filter */
		$categories_arr    = explode( '|', $schedule_info[ 'category_filter' ] );
		$categories_arrNew = [];

		foreach ( $categories_arr as $categ )
		{
			if ( is_numeric( $categ ) && $categ > 0 )
			{
				$categInf = get_term( (int) $categ );

				if ( ! $categInf )
				{
					continue;
				}

				$categories_arrNew[] = (int) $categ;

				// get sub categories
				$child_cats = get_categories( [
					'taxonomy'   => $categInf->taxonomy,
					'child_of'   => (int) $categ,
					'hide_empty' => FALSE
				] );

				foreach ( $child_cats as $child_cat )
				{
					$categories_arrNew[] = (int) $child_cat->term_id;
				}
			}
		}

		$categories_arr = $categories_arrNew;

		unset( $categories_arrNew );

		if ( empty( $categories_arr ) )
		{
			$categoriesFilter = '';
		}
		else
		{
			$get_taxs = DB::DB()->get_col( DB::DB()->prepare( "SELECT `term_taxonomy_id` FROM `" . DB::WPtable( 'term_taxonomy', TRUE ) . "` WHERE `term_id` IN ('" . implode( "' , '", $categories_arr ) . "')" ), 0 );

			$categoriesFilter = " AND `id` IN ( SELECT object_id FROM `" . DB::WPtable( 'term_relationships', TRUE ) . "` WHERE term_taxonomy_id IN ('" . implode( "' , '", $get_taxs ) . "') ) ";
		}
		/* / End of Categories filter */

		/* post_date_filter */
		switch ( $schedule_info[ 'post_date_filter' ] )
		{
			case "this_week":
				$week = Date::format( 'w' );
				$week = $week == 0 ? 7 : $week;

				$startDateFilter = Date::format( 'Y-m-d 00:00', '-' . ( $week - 1 ) . ' day' );
				$endDateFilter   = Date::format( 'Y-m-d 23:59' );
				break;
			case "previously_week":
				$week = Date::format( 'w' );
				$week = $week == 0 ? 7 : $week;
				$week += 7;

				$startDateFilter = Date::format( 'Y-m-d 00:00', '-' . ( $week - 1 ) . ' day' );
				$endDateFilter   = Date::format( 'Y-m-d 23:59', '-' . ( $week - 7 ) . ' day' );
				break;
			case "this_month":
				$startDateFilter = Date::format( 'Y-m-01 00:00' );
				$endDateFilter   = Date::format( 'Y-m-t 23:59' );
				break;
			case "previously_month":
				$startDateFilter = Date::format( 'Y-m-01 00:00', '-1 month' );
				$endDateFilter   = Date::format( 'Y-m-t 23:59', '-1 month' );
				break;
			case "this_year":
				$startDateFilter = Date::format( 'Y-01-01 00:00' );
				$endDateFilter   = Date::format( 'Y-12-31 23:59' );
				break;
			case "last_30_days":
				$startDateFilter = Date::format( 'Y-m-d 00:00', '-30 day' );
				$endDateFilter   = Date::format( 'Y-m-d 23:59' );
				break;
			case "last_60_days":
				$startDateFilter = Date::format( 'Y-m-d 00:00', '-60 day' );
				$endDateFilter   = Date::format( 'Y-m-d 23:59' );
				break;
			case "custom":
				$startDateFilter = Date::format( 'Y-m-d 00:00', $schedule_info[ 'filter_posts_date_range_from' ] );
				$endDateFilter   = Date::format( 'Y-m-d 23:59', $schedule_info[ 'filter_posts_date_range_to' ] );
				break;
		}

		$dateFilter = "";

		if ( isset( $startDateFilter ) && isset( $endDateFilter ) )
		{
			$dateFilter = " AND post_date BETWEEN '{$startDateFilter}' AND '{$endDateFilter}'";
		}
		/* End of post_date_filter */

		/* Filter by id */
		$postIDs      = explode( ',', $schedule_info[ 'post_ids' ] );
		$postIDFilter = [];

		foreach ( $postIDs as $post_id1 )
		{
			if ( is_numeric( $post_id1 ) && $post_id1 > 0 )
			{
				$postIDFilter[] = (int) $post_id1;
			}
		}

		if ( empty( $postIDFilter ) )
		{
			$postIDFilter = '';
		}
		else
		{
			$postIDFilter   = " AND id IN ('" . implode( "','", $postIDFilter ) . "') ";
			$postTypeFilter = '';
		}

		/* End ofid filter */

		/* post_sort */
		$sortQuery = '';

		if ( $scheduleId > 0 )
		{
			switch ( $schedule_info[ 'post_sort' ] )
			{
				case "random":
					$sortQuery .= 'ORDER BY RAND()';
					break;
				case "random2":
					$sortQuery .= ' AND id NOT IN (SELECT post_id FROM `' . DB::table( 'feeds' ) . "` WHERE schedule_id='" . (int) $scheduleId . "') ORDER BY RAND()";
					break;
				case "old_first":
					$last_shared_post_id = DB::DB()->get_row( 'SELECT `post_id` FROM `' . DB::table( 'feeds' ) . '` WHERE `schedule_id` = "' . ( int ) $scheduleId . '" ORDER BY `id` DESC LIMIT 1', ARRAY_A );

					if ( ! empty( $last_shared_post_id[ 'post_id' ] ) )
					{
						$last_post_date = Date::dateTimeSQL( get_the_date( 'Y-m-d H:i:s', $last_shared_post_id[ 'post_id' ] ) );

						if ( $last_post_date )
						{
							$sortQuery .= " AND ((post_date = '" . $last_post_date . "' AND ID > " . $last_shared_post_id[ 'post_id' ] . " ) OR post_date > '" . $last_post_date . "') ";
						}
					}

					$sortQuery .= 'ORDER BY post_date, ID ASC';
					break;
				case "new_first":
					$last_shared_post_id = DB::DB()->get_row( 'SELECT `post_id` FROM `' . DB::table( 'feeds' ) . '` WHERE `schedule_id` = "' . ( int ) $scheduleId . '" ORDER BY `id` DESC LIMIT 1', ARRAY_A );

					if ( ! empty( $last_shared_post_id[ 'post_id' ] ) )
					{
						$last_post_date = Date::dateTimeSQL( get_the_date( 'Y-m-d H:i:s', $last_shared_post_id[ 'post_id' ] ) );

						if ( $last_post_date )
						{
							$sortQuery .= " AND ( (post_date = '" . $last_post_date . "' AND ID < " . $last_shared_post_id[ 'post_id' ] . " ) OR post_date < '" . $last_post_date . "') ";
						}
					}

					$sortQuery .= 'ORDER BY post_date, ID DESC';
					break;
			}
		}

		return "{$postIDFilter} {$postTypeFilter} {$categoriesFilter} {$dateFilter} {$sortQuery}";
	}

	public static function getAccessToken ( $nodeType, $nodeId )
	{
		if ( $nodeType === 'account' )
		{
			$node_info     = DB::fetch( 'accounts', $nodeId );
			$nodeProfileId = $node_info[ 'profile_id' ];
			$n_accountId   = $nodeProfileId;

			$accessTokenGet    = DB::fetch( 'account_access_tokens', [ 'account_id' => $nodeId ] );
			$accessToken       = isset( $accessTokenGet ) && array_key_exists( 'access_token', $accessTokenGet ) ? $accessTokenGet[ 'access_token' ] : '';
			$accessTokenSecret = isset( $accessTokenGet ) && array_key_exists( 'access_token_secret', $accessTokenGet ) ? $accessTokenGet[ 'access_token_secret' ] : '';
			$appId             = isset( $accessTokenGet ) && array_key_exists( 'app_id', $accessTokenGet ) ? $accessTokenGet[ 'app_id' ] : '';
			$driver            = $node_info[ 'driver' ];
			$username          = $node_info[ 'username' ];
			$email             = $node_info[ 'email' ];
			$password          = $node_info[ 'password' ];
			$name              = $node_info[ 'name' ];
			$proxy             = $node_info[ 'proxy' ];
			$options           = $node_info[ 'options' ];
			$poster_id         = NULL;

			if ( $driver === 'reddit' )
			{
				$accessToken = Reddit::accessToken( $accessTokenGet );
			}
			else if ( $driver === 'ok' )
			{
				$accessToken = OdnoKlassniki::accessToken( $accessTokenGet );
			}
			else if ( $driver === 'medium' )
			{
				$accessToken = Medium::accessToken( $accessTokenGet );
			}
			else if ( $driver === 'google_b' && empty( $options ) )
			{
				$accessToken = GoogleMyBusinessAPI::accessToken( $accessTokenGet );
			}
			else if ( $driver === 'blogger' )
			{
				$accessToken = Blogger::accessToken( $accessTokenGet );
			}
			else if ( $driver === 'pinterest' && empty( $options ) )
			{
				$accessToken = Pinterest::accessToken( $accessTokenGet );
			}
			else if ( $driver === 'linkedin' )
			{
				$accessToken = Linkedin::accessToken( $nodeId, $accessTokenGet );
			}
		}
		else
		{
			$node_info    = DB::fetch( 'account_nodes', $nodeId );
			$account_info = DB::fetch( 'accounts', $node_info[ 'account_id' ] );

			if ( $node_info )
			{
				$node_info[ 'proxy' ] = $account_info[ 'proxy' ];
			}

			$name        = $account_info[ 'name' ];
			$username    = $account_info[ 'username' ];
			$email       = $account_info[ 'email' ];
			$password    = $account_info[ 'password' ];
			$proxy       = $account_info[ 'proxy' ];
			$options     = $account_info[ 'options' ];
			$n_accountId = $account_info[ 'profile_id' ];
			$poster_id   = NULL;

			$nodeProfileId     = $node_info[ 'node_id' ];
			$driver            = $node_info[ 'driver' ];
			$appId             = 0;
			$accessTokenSecret = '';

			if ( $driver === 'fb' && $node_info[ 'node_type' ] === 'ownpage' )
			{
				$accessToken = $node_info[ 'access_token' ];
			}
			else
			{
				$accessTokenGet    = DB::fetch( 'account_access_tokens', [ 'account_id' => $node_info[ 'account_id' ] ] );
				$accessToken       = isset( $accessTokenGet ) && array_key_exists( 'access_token', $accessTokenGet ) ? $accessTokenGet[ 'access_token' ] : '';
				$accessTokenSecret = isset( $accessTokenGet ) && array_key_exists( 'access_token_secret', $accessTokenGet ) ? $accessTokenGet[ 'access_token_secret' ] : '';
				$appId             = isset( $accessTokenGet ) && array_key_exists( 'app_id', $accessTokenGet ) ? $accessTokenGet[ 'app_id' ] : '';

				if ( $driver === 'reddit' )
				{
					$accessToken = Reddit::accessToken( $accessTokenGet );
				}
				else if ( $driver === 'ok' )
				{
					$accessToken = OdnoKlassniki::accessToken( $accessTokenGet );
				}
				else if ( $driver === 'medium' )
				{
					$accessToken = Medium::accessToken( $accessTokenGet );
				}
				else if ( $driver === 'google_b' && empty( $options ) )
				{
					$accessToken = GoogleMyBusinessAPI::accessToken( $accessTokenGet );
				}
				else if ( $driver === 'blogger' )
				{
					$accessToken = Blogger::accessToken( $accessTokenGet );
				}
				else if ( $driver === 'pinterest' && empty( $options ) )
				{
					$accessToken = Pinterest::accessToken( $accessTokenGet );
				}
				else if ( $driver === 'linkedin' )
				{
					$accessToken = Linkedin::accessToken( $node_info[ 'account_id' ], $accessTokenGet );
				}
				else if ( $driver === 'fb' && $nodeType === 'group' && isset( $node_info[ 'poster_id' ] ) && $node_info[ 'poster_id' ] > 0 )
				{
					$poster_id = $node_info[ 'poster_id' ];
				}
			}

			if ( $driver === 'vk' )
			{
				$nodeProfileId = '-' . $nodeProfileId;
			}
		}

		$node_info[ 'node_type' ] = empty( $node_info[ 'node_type' ] ) ? 'account' : $node_info[ 'node_type' ];

		return [
			'id'                  => $nodeId,
			'node_id'             => $nodeProfileId,
			'node_type'           => $nodeType,
			'access_token'        => $accessToken,
			'access_token_secret' => $accessTokenSecret,
			'app_id'              => $appId,
			'driver'              => $driver,
			'info'                => $node_info,
			'username'            => $username,
			'email'               => $email,
			'password'            => $password,
			'proxy'               => $proxy,
			'options'             => $options,
			'account_id'          => $n_accountId,
			'poster_id'           => $poster_id,
			'name'                => $name
		];
	}

	public static function isHiddenUser ()
	{
		$hideFSPosterForRoles = explode( '|', Helper::getOption( 'hide_menu_for', '' ) );

		$userInf   = wp_get_current_user();
		$userRoles = (array) $userInf->roles;

		if ( ! in_array( 'administrator', $userRoles ) )
		{
			foreach ( $userRoles as $roleId )
			{
				if ( in_array( $roleId, $hideFSPosterForRoles ) )
				{
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	public static function removePageBuilderShortcodes ( $message )
	{
		$message = str_replace( [
			'[[',
			']]'
		], [
			'&#91;&#91;',
			'&#93;&#93;'
		], $message );

		$message = preg_replace( [ '/\[(.+)]/', '/<!--(.*?)-->/' ], '', $message );

		$message = str_replace( [
			'&#91;&#91;',
			'&#93;&#93;'
		], [
			'[[',
			']]'
		], $message );

		return $message;
	}

	/**
	 * Check the time if is between two times
	 *
	 * @param $time int time to check
	 * @param $start int start time to compare
	 * @param $end int end time to compare
	 *
	 * @return bool if given time is between two dates, then true, otherwise false
	 */
	public static function isBetweenDates ( $time, $start, $end )
	{
		if ( $start < $end )
		{
			return $time >= $start && $time <= $end;
		}
		else
		{
			return $time <= $end || $time >= $start;
		}
	}
}
