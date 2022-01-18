<?php

namespace FSPoster\App\Pages\Settings\Controllers;

use Exception;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Curl;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

trait Ajax
{
	private function isAdmin ()
	{
		if ( ! ( current_user_can( 'administrator' ) || defined( 'FS_POSTER_IS_DEMO' ) ) )
		{
			exit();
		}
	}

	public function settings_general_save ()
	{
		$this->isAdmin();

		$fsp_license_status = Request::post( 'fsp_license_status', '1', 'string', [ '0' ] ) === '1';

		if ( ! $fsp_license_status )
		{
			if ( defined( 'FS_POSTER_IS_DEMO' ) )
			{
				Helper::response( FALSE, fsp__( 'The feature is disabled on the demo to prevent disabling the license from here. It will be available on your website.' ) );
			}

			Curl::getURL( FS_API_URL . 'api.php?act=delete&purchase_code=' . urlencode( Helper::getOption( 'poster_plugin_purchase_key', '' ) ) . '&domain=' . network_site_url() );

			Helper::setOption( 'plugin_disabled', '1', TRUE );
			Helper::setOption( 'plugin_alert', fsp__( 'manually disabled by user.' ), TRUE );
			Helper::deleteOption( 'poster_plugin_purchase_key', TRUE );

			Helper::response( TRUE, [ 'redirect' => 'admin.php?page=fs-poster' ] );
		}

		$fs_hide_notifications        = Request::post( 'fs_hide_notifications', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_show_fs_poster_column     = Request::post( 'fs_show_fs_poster_column', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_check_accounts            = Request::post( 'fs_check_accounts', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_check_accounts_disable    = Request::post( 'fs_check_accounts_disable', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_allowed_post_types        = Request::post( 'fs_allowed_post_types', [
			'post',
			'attachment',
			'page',
			'product'
		], 'array' );
		$fs_collect_statistics        = Request::post( 'fs_collect_statistics', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_clean_accounts            = Request::post( 'fs_clean_accounts', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_virtual_cron_job_disabled = Request::post( 'fs_virtual_cron_job_disabled', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;

		$new_arrPostTypes = [];
		$allTypes         = get_post_types();
		foreach ( $fs_allowed_post_types as $fs_aPT )
		{
			if ( is_string( $fs_aPT ) && in_array( $fs_aPT, $allTypes ) )
			{
				$new_arrPostTypes[] = $fs_aPT;
			}
		}
		$new_arrPostTypes = implode( '|', $new_arrPostTypes );

		$fs_hide_for_roles   = Request::post( 'fs_hide_for_roles', [], 'array' );
		$new_arrHideForRoles = [];
		$allRoles            = get_editable_roles();
		foreach ( $fs_hide_for_roles as $fs_aPT )
		{
			if ( $fs_aPT != 'administrator' && is_string( $fs_aPT ) && isset( $allRoles[ $fs_aPT ] ) )
			{
				$new_arrHideForRoles[] = $fs_aPT;
			}
		}
		$new_arrHideForRoles = implode( '|', $new_arrHideForRoles );

		Helper::setOption( 'hide_notifications', (string) $fs_hide_notifications );
		Helper::setOption( 'show_fs_poster_column', (string) $fs_show_fs_poster_column );
		Helper::setOption( 'clean_accounts', (string) $fs_clean_accounts );
		Helper::setOption( 'check_accounts', (string) $fs_check_accounts );
		Helper::setOption( 'check_accounts_disable', (string) $fs_check_accounts_disable );
		Helper::setOption( 'allowed_post_types', $new_arrPostTypes );
		Helper::setOption( 'hide_menu_for', $new_arrHideForRoles );
		Helper::setOption( 'collect_statistics', (string) $fs_collect_statistics );
		Helper::setOption( 'virtual_cron_job_disabled', (string) $fs_virtual_cron_job_disabled );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_share_save ()
	{
		$this->isAdmin();

		$fs_auto_share_new_posts                = Request::post( 'fs_auto_share_new_posts', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_share_on_background                 = Request::post( 'fs_share_on_background', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_share_timer                         = Request::post( 'fs_share_timer', '0', 'integer' );
		$fs_keep_logs                           = Request::post( 'fs_keep_logs', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_post_interval                       = Request::post( 'fs_post_interval', '0', 'integer' );
		$fs_post_interval_type                  = Request::post( 'fs_post_interval_type', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_replace_whitespaces_with_underscore = Request::post( 'fs_replace_whitespaces_with_underscore', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_multiple_newlines_to_single         = Request::post( 'fs_multiple_newlines_to_single', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_replace_wp_shortcodes               = Request::post( 'fs_replace_wp_shortcodes', 'off', 'string', [
			'off',
			'on',
			'del'
		] );
		$fs_hashtag_taxonomies                  = Request::post( 'fs_hashtag_taxonomies', [], 'array' );

		Helper::setOption( 'auto_share_new_posts', (string) $fs_auto_share_new_posts );
		Helper::setOption( 'share_on_background', (string) $fs_share_on_background );
		Helper::setOption( 'share_timer', $fs_share_timer );
		Helper::setOption( 'keep_logs', (string) $fs_keep_logs );
		Helper::setOption( 'post_interval', (string) $fs_post_interval );
		Helper::setOption( 'post_interval_type', (string) $fs_post_interval_type );
		Helper::setOption( 'replace_whitespaces_with_underscore', (string) $fs_replace_whitespaces_with_underscore );
		Helper::setOption( 'multiple_newlines_to_single', (string) $fs_multiple_newlines_to_single );
		Helper::setOption( 'replace_wp_shortcodes', (string) $fs_replace_wp_shortcodes );
		Helper::setOption( 'hashtag_taxonomies', implode( '|', $fs_hashtag_taxonomies ) );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_url_save ()
	{
		$this->isAdmin();

		$fs_unique_link                  = Request::post( 'fs_unique_link', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_url_shortener                = Request::post( 'fs_url_shortener', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_shortener_service            = Request::post( 'fs_shortener_service', 0, 'string', [ 'tinyurl', 'bitly' ] );
		$fs_url_short_access_token_bitly = Request::post( 'fs_url_short_access_token_bitly', '', 'string' );
		$fs_url_additional               = Request::post( 'fs_url_additional', '', 'string' );
		$fs_share_custom_url             = Request::post( 'fs_share_custom_url', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_custom_url_to_share          = Request::post( 'fs_custom_url_to_share', '', 'string' );

		$fs_url_additional      = str_replace( ' ', '', $fs_url_additional );
		$fs_custom_url_to_share = str_replace( ' ', '', $fs_custom_url_to_share );

		Helper::setOption( 'unique_link', (string) $fs_unique_link );
		Helper::setOption( 'url_shortener', (string) $fs_url_shortener );
		Helper::setOption( 'shortener_service', $fs_shortener_service );
		Helper::setOption( 'url_short_access_token_bitly', $fs_url_short_access_token_bitly );
		Helper::setOption( 'url_additional', str_replace( ' ', '', $fs_url_additional ) );
		Helper::setOption( 'share_custom_url', (string) $fs_share_custom_url );
		Helper::setOption( 'custom_url_to_share', str_replace( ' ', '', $fs_custom_url_to_share ) );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_facebook_save ()
	{
		$this->isAdmin();

		$fs_facebook_post_in_type            = Request::post( 'fs_fb_post_in_type', 1, 'int', [ 1, 2, 3 ] );
		$fs_load_own_pages                   = (string) Request::post( 'fs_load_own_pages', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_load_groups                      = (string) Request::post( 'fs_load_groups', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_post_text_message_fb             = (string) Request::post( 'fs_post_text_message_fb', '', 'string' );
		$fs_facebook_story_custom_font_reset = Request::post( 'fs_facebook_story_custom_font_reset', 0, 'int' );
		$fs_facebook_posting_type            = (string) Request::post( 'fs_facebook_posting_type', '1', 'num', [
			'1',
			'2',
			'3',
			'4'
		] );

		$fs_post_text_message_fb_h                  = Request::post( 'fs_post_text_message_fb_h', '', 'string' );
		$fs_facebook_story_background               = Request::post( 'fs_facebook_story_background', '', 'string' );
		$fs_facebook_story_title_background         = Request::post( 'fs_facebook_story_title_background', '', 'string' );
		$fs_facebook_story_title_background_opacity = Request::post( 'fs_facebook_story_title_background_opacity', '', 'int' );
		$fs_facebook_story_title_color              = Request::post( 'fs_facebook_story_title_color', '', 'string' );
		$fs_facebook_story_title_top                = Request::post( 'fs_facebook_story_title_top', '', 'string' );
		$fs_facebook_story_title_left               = Request::post( 'fs_facebook_story_title_left', '', 'string' );
		$fs_facebook_story_title_width              = Request::post( 'fs_facebook_story_title_width', '', 'string' );
		$fs_facebook_story_title_font_size          = Request::post( 'fs_facebook_story_title_font_size', '', 'string' );
		$fs_facebook_story_title_rtl                = Request::post( 'fs_facebook_story_title_rtl', 'off', 'string', [
			'on',
			'off'
		] );

		$fs_facebook_story_custom_font = $_FILES[ 'fs_facebook_story_custom_font' ];

		if ( ! empty( $fs_facebook_story_custom_font[ 'name' ] ) )
		{
			if ( pathinfo( $fs_facebook_story_custom_font[ 'name' ], PATHINFO_EXTENSION ) === 'ttf' )
			{
				$custom_font_file_path = wp_upload_dir()[ 'basedir' ] . '/fs-poster-fonts/FS-Poster-fb-font.ttf';

				if ( file_exists( $custom_font_file_path ) )
				{
					unlink( $custom_font_file_path );
				}

				$_filter = TRUE;

				add_filter( 'upload_mimes', function ( $mimes ) use ( &$_filter ) {
					if ( $_filter )
					{
						$mimes[ 'ttf' ] = 'font/ttf';
					}

					return $mimes;
				} );

				add_filter( 'upload_dir', function ( $arr ) use ( &$_filter ) {
					if ( $_filter )
					{
						$arr[ 'path' ] = $arr[ 'basedir' ] . '/fs-poster-fonts';
					}

					return $arr;
				} );

				wp_upload_bits( 'FS-Poster-fb-font.ttf', NULL, file_get_contents( $fs_facebook_story_custom_font[ 'tmp_name' ] ) );

				$_filter = FALSE;

				Helper::setOption( 'facebook_story_custom_font', $custom_font_file_path );
			}
		}

		if ( $fs_facebook_story_custom_font_reset )
		{
			unlink( wp_upload_dir()[ 'basedir' ] . '/fs-poster-fonts/FS-Poster-fb-font.ttf' );

			Helper::setOption( 'facebook_story_custom_font', '' );
		}

		Helper::setOption( 'post_text_message_fb', $fs_post_text_message_fb );
		Helper::setOption( 'load_own_pages', $fs_load_own_pages );
		Helper::setOption( 'load_groups', $fs_load_groups );
		Helper::setOption( 'facebook_posting_type', $fs_facebook_posting_type );

		Helper::setOption( 'post_text_message_fb_h', $fs_post_text_message_fb_h );
		Helper::setOption( 'fb_post_in_type', $fs_facebook_post_in_type );
		Helper::setOption( 'facebook_story_background', $fs_facebook_story_background );
		Helper::setOption( 'facebook_story_title_background', $fs_facebook_story_title_background );
		Helper::setOption( 'facebook_story_title_background_opacity', ( $fs_facebook_story_title_background_opacity > 100 || $fs_facebook_story_title_background_opacity < 0 ? 30 : $fs_facebook_story_title_background_opacity ) );
		Helper::setOption( 'facebook_story_title_color', $fs_facebook_story_title_color );
		Helper::setOption( 'facebook_story_title_top', $fs_facebook_story_title_top );
		Helper::setOption( 'facebook_story_title_left', $fs_facebook_story_title_left );
		Helper::setOption( 'facebook_story_title_width', $fs_facebook_story_title_width );
		Helper::setOption( 'facebook_story_title_font_size', $fs_facebook_story_title_font_size );
		Helper::setOption( 'facebook_story_title_rtl', $fs_facebook_story_title_rtl );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_plurk_save ()
	{
		$this->isAdmin();

		$fs_post_text_message_plurk = (string) Request::post( 'fs_post_text_message_plurk', '', 'string' );
		$fs_plurk_auto_cut_plurks   = (string) Request::post( 'fs_plurk_auto_cut_plurks', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_plurk_posting_type      = (string) Request::post( 'fs_plurk_posting_type', '2', 'num', [
			'1',
			'2',
			'3',
			'4'
		] );

		$qualifier_r        = [
			':',
			'shares',
			'plays',
			'buys',
			'sells',
			'loves',
			'likes',
			'hates',
			'wants',
			'wishes',
			'needs',
			'has',
			'will',
			'hopes',
			'asks',
			'wonders',
			'feels',
			'thinks',
			'draws',
			'is',
			'says',
			'eats',
			'writes',
			'whispers'
		];
		$fs_plurk_qualifier = (string) Request::post( 'fs_plurk_qualifier', ':', 'str', $qualifier_r );

		Helper::setOption( 'post_text_message_plurk', $fs_post_text_message_plurk );
		Helper::setOption( 'fs_plurk_auto_cut_plurks', $fs_plurk_auto_cut_plurks );
		Helper::setOption( 'plurk_posting_type', $fs_plurk_posting_type );
		Helper::setOption( 'plurk_qualifier', $fs_plurk_qualifier );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_instagram_save ()
	{
		$this->isAdmin();

		$fs_instagram_autocut_text            = Request::post( 'fs_instagram_autocut_text', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_instagram_post_in_type            = Request::post( 'fs_instagram_post_in_type', 0, 'int', [ 1, 2, 3 ] );
		$fs_instagram_story_link              = Request::post( 'fs_instagram_story_link', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_instagram_story_hashtag           = Request::post( 'fs_instagram_story_hashtag', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_instagram_story_hashtag_name      = Request::post( 'fs_instagram_story_hashtag_name', '', 'string' );
		$fs_instagram_story_custom_font_reset = Request::post( 'fs_instagram_story_custom_font_reset', 0, 'int' );
		$fs_instagram_story_hashtag_position  = Request::post( 'fs_instagram_story_hashtag_position', 'top', 'string', [
			'top',
			'bottom'
		] );

		if ( $fs_instagram_story_hashtag && empty( $fs_instagram_story_hashtag_name ) )
		{
			Helper::response( FALSE, fsp__( 'Plase type the hashtag' ) );
		}

		$fs_post_text_message_instagram              = Request::post( 'fs_post_text_message_instagram', '', 'string' );
		$fs_post_text_message_instagram_h            = Request::post( 'fs_post_text_message_instagram_h', '', 'string' );
		$fs_instagram_story_background               = Request::post( 'fs_instagram_story_background', '', 'string' );
		$fs_instagram_story_title_background         = Request::post( 'fs_instagram_story_title_background', '', 'string' );
		$fs_instagram_story_title_background_opacity = Request::post( 'fs_instagram_story_title_background_opacity', '', 'int' );
		$fs_instagram_story_title_color              = Request::post( 'fs_instagram_story_title_color', '', 'string' );
		$fs_instagram_story_title_top                = Request::post( 'fs_instagram_story_title_top', '', 'string' );
		$fs_instagram_story_title_left               = Request::post( 'fs_instagram_story_title_left', '', 'string' );
		$fs_instagram_story_title_width              = Request::post( 'fs_instagram_story_title_width', '', 'string' );
		$fs_instagram_story_title_font_size          = Request::post( 'fs_instagram_story_title_font_size', '', 'string' );
		$fs_instagram_story_title_rtl                = Request::post( 'fs_instagram_story_title_rtl', 'off', 'string', [
			'on',
			'off'
		] );

		$fs_instagram_story_custom_font = $_FILES[ 'fs_instagram_story_custom_font' ];

		if ( ! empty( $fs_instagram_story_custom_font[ 'name' ] ) )
		{
			if ( pathinfo( $fs_instagram_story_custom_font[ 'name' ], PATHINFO_EXTENSION ) === 'ttf' )
			{
				$custom_font_file_path = wp_upload_dir()[ 'basedir' ] . '/fs-poster-fonts/FS-Poster-ig-font.ttf';

				if ( file_exists( $custom_font_file_path ) )
				{
					unlink( $custom_font_file_path );
				}

				$_filter = TRUE;

				add_filter( 'upload_mimes', function ( $mimes ) use ( &$_filter ) {
					if ( $_filter )
					{
						$mimes[ 'ttf' ] = 'font/ttf';
					}

					return $mimes;
				} );

				add_filter( 'upload_dir', function ( $arr ) use ( &$_filter ) {
					if ( $_filter )
					{
						$arr[ 'path' ] = $arr[ 'basedir' ] . '/fs-poster-fonts';
					}

					return $arr;
				} );

				wp_upload_bits( 'FS-Poster-ig-font.ttf', NULL, file_get_contents( $fs_instagram_story_custom_font[ 'tmp_name' ] ) );

				$_filter = FALSE;

				Helper::setOption( 'instagram_story_custom_font', $custom_font_file_path );
			}
		}

		if ( $fs_instagram_story_custom_font_reset )
		{
			unlink( wp_upload_dir()[ 'basedir' ] . '/fs-poster-fonts/FS-Poster-ig-font.ttf' );

			Helper::setOption( 'instagram_story_custom_font', '' );
		}

		Helper::setOption( 'instagram_autocut_text', $fs_instagram_autocut_text );
		Helper::setOption( 'post_text_message_instagram', $fs_post_text_message_instagram );
		Helper::setOption( 'post_text_message_instagram_h', $fs_post_text_message_instagram_h );
		Helper::setOption( 'instagram_post_in_type', $fs_instagram_post_in_type );
		Helper::setOption( 'instagram_story_link', (string) $fs_instagram_story_link );
		Helper::setOption( 'instagram_story_hashtag', (string) $fs_instagram_story_hashtag );
		Helper::setOption( 'instagram_story_hashtag_name', $fs_instagram_story_hashtag ? $fs_instagram_story_hashtag_name : '' );
		Helper::setOption( 'instagram_story_hashtag_position', $fs_instagram_story_hashtag ? $fs_instagram_story_hashtag_position : '' );
		Helper::setOption( 'instagram_story_background', $fs_instagram_story_background );
		Helper::setOption( 'instagram_story_title_background', $fs_instagram_story_title_background );
		Helper::setOption( 'instagram_story_title_background_opacity', ( $fs_instagram_story_title_background_opacity > 100 || $fs_instagram_story_title_background_opacity < 0 ? 30 : $fs_instagram_story_title_background_opacity ) );
		Helper::setOption( 'instagram_story_title_color', $fs_instagram_story_title_color );
		Helper::setOption( 'instagram_story_title_top', $fs_instagram_story_title_top );
		Helper::setOption( 'instagram_story_title_left', $fs_instagram_story_title_left );
		Helper::setOption( 'instagram_story_title_width', $fs_instagram_story_title_width );
		Helper::setOption( 'instagram_story_title_font_size', $fs_instagram_story_title_font_size );
		Helper::setOption( 'instagram_story_title_rtl', $fs_instagram_story_title_rtl );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_vk_save ()
	{
		$this->isAdmin();

		$fs_vk_load_admin_communities   = Request::post( 'fs_vk_load_admin_communities', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_vk_load_members_communities = Request::post( 'fs_vk_load_members_communities', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_vk_upload_image             = Request::post( 'fs_vk_upload_image', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_vk_max_communities_limit    = Request::post( 'fs_vk_max_communities_limit', '50', 'num' );
		$fs_vk_posting_type             = (string) Request::post( 'fs_vk_posting_type', '1', 'num', [
			'1',
			'2',
			'3',
			'4'
		] );

		if ( $fs_vk_max_communities_limit > 1000 )
		{
			$fs_vk_max_communities_limit = 1000;
		}

		$fs_post_text_message_vk = Request::post( 'fs_post_text_message_vk', '', 'string' );

		Helper::setOption( 'post_text_message_vk', $fs_post_text_message_vk );
		Helper::setOption( 'vk_load_admin_communities', (string) $fs_vk_load_admin_communities );
		Helper::setOption( 'vk_load_members_communities', (string) $fs_vk_load_members_communities );
		Helper::setOption( 'vk_max_communities_limit', $fs_vk_max_communities_limit );
		Helper::setOption( 'vk_upload_image', $fs_vk_upload_image );
		Helper::setOption( 'vk_posting_type', $fs_vk_posting_type );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_twitter_save ()
	{
		$this->isAdmin();

		$fs_post_text_message_twitter = (string) Request::post( 'fs_post_text_message_twitter', '', 'string' );
		$fs_twitter_auto_cut_tweets   = (string) Request::post( 'fs_twitter_auto_cut_tweets', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_twitter_posting_type      = (string) Request::post( 'fs_twitter_posting_type', '1', 'num', [
			'1',
			'2',
			'3',
			'4'
		] );

		Helper::setOption( 'post_text_message_twitter', $fs_post_text_message_twitter );
		Helper::setOption( 'twitter_auto_cut_tweets', $fs_twitter_auto_cut_tweets );
		Helper::setOption( 'twitter_posting_type', $fs_twitter_posting_type );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_linkedin_save ()
	{
		$this->isAdmin();

		$fs_linkedin_autocut_text      = (string) Request::post( 'fs_linkedin_autocut_text', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_post_text_message_linkedin = (string) Request::post( 'fs_post_text_message_linkedin', '', 'string' );
		$fs_linkedin_posting_type      = (string) Request::post( 'fs_linkedin_posting_type', '1', 'num', [
			'1',
			'2',
			'3',
			'4'
		] );

		Helper::setOption( 'linkedin_autocut_text', $fs_linkedin_autocut_text );
		Helper::setOption( 'post_text_message_linkedin', $fs_post_text_message_linkedin );
		Helper::setOption( 'linkedin_posting_type', $fs_linkedin_posting_type );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_pinterest_save ()
	{
		$this->isAdmin();

		$fs_pinterest_autocut_title     = Request::post( 'fs_pinterest_autocut_title', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_post_text_message_pinterest = Request::post( 'fs_post_text_message_pinterest', '', 'string' );
		$fs_alt_text_pinterest          = Request::post( 'fs_alt_text_pinterest', '', 'string' );

		Helper::setOption( 'pinterest_autocut_title', $fs_pinterest_autocut_title );
		Helper::setOption( 'post_text_message_pinterest', $fs_post_text_message_pinterest );
		Helper::setOption( 'alt_text_pinterest', $fs_alt_text_pinterest );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_google_b_save ()
	{
		$this->isAdmin();

		$fs_gmb_autocut                = (string) Request::post( 'fs_gmb_autocut', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_post_text_message_google_b = Request::post( 'fs_post_text_message_google_b', '', 'string' );
		$fs_google_b_share_as_product  = Request::post( 'fs_google_b_share_as_product', 0, 'string', [ 'on' ] ) === 'on' && function_exists( 'wc_get_product' ) ? 1 : 0;
		$fs_google_b_button_type       = Request::post( 'fs_google_b_button_type', 'LEARN_MORE', 'string', [
			'BOOK',
			'ORDER',
			'SHOP',
			'SIGN_UP',
			'WATCH_VIDEO',
			'RESERVE',
			'GET_OFFER',
			'CALL'
		] );
		$fs_google_b_posting_type      = (string) Request::post( 'fs_google_b_posting_type', '1', 'num', [
			'1',
			'2',
			'3',
			'4'
		] );

		Helper::setOption( 'gmb_autocut', $fs_gmb_autocut );
		Helper::setOption( 'post_text_message_google_b', $fs_post_text_message_google_b );
		Helper::setOption( 'google_b_share_as_product', $fs_google_b_share_as_product );
		Helper::setOption( 'google_b_button_type', $fs_google_b_button_type );
		Helper::setOption( 'google_b_posting_type', $fs_google_b_posting_type );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_blogger_save ()
	{
		$this->isAdmin();

		$fs_blogger_posting_type      = Request::post( 'fs_blogger_posting_type', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_post_title_blogger        = Request::post( 'fs_post_title_blogger', '', 'string' );
		$fs_post_text_message_blogger = Request::post( 'fs_post_text_message_blogger', '', 'string' );
		$fs_blogger_post_with_terms   = Request::post( 'fs_blogger_post_with_terms', 0, 'string', [ 'on' ] ) !== 'on' ? 0 : 1;
		$fs_blogger_post_status       = Request::post( 'fs_blogger_post_status', 'publish', 'string', [
			'publish',
			'draft'
		] );

		Helper::setOption( 'post_title_blogger', ( string ) $fs_post_title_blogger );
		Helper::setOption( 'post_text_message_blogger', ( string ) $fs_post_text_message_blogger );
		Helper::setOption( 'blogger_post_with_terms', ( string ) $fs_blogger_post_with_terms );
		Helper::setOption( 'blogger_posting_type', ( string ) $fs_blogger_posting_type );
		Helper::setOption( 'blogger_post_status', ( string ) $fs_blogger_post_status );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_tumblr_save ()
	{
		$this->isAdmin();

		$fs_tumblr_send_tags         = Request::post( 'fs_tumblr_send_tags', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_post_text_message_tumblr = Request::post( 'fs_post_text_message_tumblr', '', 'string' );
		$fs_post_title_tumblr        = Request::post( 'fs_post_title_tumblr', '', 'string' );
		$fs_tumblr_posting_type      = Request::post( 'fs_tumblr_posting_type', '1', 'num', [
			'1',
			'2',
			'3',
			'4',
			'5'
		] );

		Helper::setOption( 'tumblr_send_tags', $fs_tumblr_send_tags );
		Helper::setOption( 'post_text_message_tumblr', $fs_post_text_message_tumblr );
		Helper::setOption( 'tumblr_posting_type', $fs_tumblr_posting_type );
		Helper::setOption( 'post_title_tumblr', $fs_post_title_tumblr );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_reddit_save ()
	{
		$this->isAdmin();

		$fs_reddit_autocut_text      = Request::post( 'fs_reddit_autocut_title', 1, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_post_text_message_reddit = Request::post( 'fs_post_text_message_reddit', '', 'string' );
		$fs_reddit_posting_type      = Request::post( 'fs_reddit_posting_type', '1', 'num', [ '1', '2', '3' ] );

		Helper::setOption( 'reddit_autocut_title', $fs_reddit_autocut_text );
		Helper::setOption( 'post_text_message_reddit', $fs_post_text_message_reddit );
		Helper::setOption( 'reddit_posting_type', $fs_reddit_posting_type );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_ok_save ()
	{
		$this->isAdmin();

		$fs_post_text_message_ok = (string) Request::post( 'fs_post_text_message_ok', '', 'string' );
		$fs_ok_posting_type      = (string) Request::post( 'fs_ok_posting_type', '1', 'num', [ '1', '2', '3', '4' ] );

		Helper::setOption( 'post_text_message_ok', $fs_post_text_message_ok );
		Helper::setOption( 'ok_posting_type', $fs_ok_posting_type );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_telegram_save ()
	{
		$this->isAdmin();

		$fs_telegram_autocut_text      = Request::post( 'fs_telegram_autocut_text', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_post_text_message_telegram = Request::post( 'fs_post_text_message_telegram', '', 'string' );
		$fs_telegram_type_of_sharing   = Request::post( 'fs_telegram_type_of_sharing', '1', 'int', [
			'1',
			'2',
			'3',
			'4'
		] );

		Helper::setOption( 'telegram_autocut_text', $fs_telegram_autocut_text );
		Helper::setOption( 'post_text_message_telegram', $fs_post_text_message_telegram );
		Helper::setOption( 'telegram_type_of_sharing', $fs_telegram_type_of_sharing );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_medium_save ()
	{
		$this->isAdmin();

		$fs_medium_send_tags         = Request::post( 'fs_medium_send_tags', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_post_text_message_medium = Request::post( 'fs_post_text_message_medium', '', 'string' );

		Helper::setOption( 'medium_send_tags', $fs_medium_send_tags );
		Helper::setOption( 'post_text_message_medium', $fs_post_text_message_medium );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_wordpress_save ()
	{
		$this->isAdmin();

		$fs_wordpress_posting_type         = Request::post( 'fs_wordpress_posting_type', 1, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_post_title_wordpress           = Request::post( 'fs_post_title_wordpress', '', 'string' );
		$fs_post_text_message_wordpress    = Request::post( 'fs_post_text_message_wordpress', '', 'string' );
		$fs_post_excerpt_wordpress         = Request::post( 'fs_post_excerpt_wordpress', '', 'string' );
		$fs_wordpress_post_with_categories = Request::post( 'fs_wordpress_post_with_categories', 0, 'string', [ 'on' ] ) !== 'on' ? 0 : 1;
		$fs_wordpress_post_with_tags       = Request::post( 'fs_wordpress_post_with_tags', 0, 'string', [ 'on' ] ) !== 'on' ? 0 : 1;
		$fs_wordpress_post_status          = Request::post( 'fs_wordpress_post_status', 'publish', 'string', [
			'publish',
			'private',
			'draft',
			'pending'
		] );

		Helper::setOption( 'post_title_wordpress', ( string ) $fs_post_title_wordpress );
		Helper::setOption( 'post_text_message_wordpress', ( string ) $fs_post_text_message_wordpress );
		Helper::setOption( 'post_excerpt_wordpress', ( string ) $fs_post_excerpt_wordpress );
		Helper::setOption( 'wordpress_post_with_categories', ( string ) $fs_wordpress_post_with_categories );
		Helper::setOption( 'wordpress_post_with_tags', ( string ) $fs_wordpress_post_with_tags );
		Helper::setOption( 'wordpress_posting_type', ( string ) $fs_wordpress_posting_type );
		Helper::setOption( 'wordpress_post_status', ( string ) $fs_wordpress_post_status );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function settings_export_save ()
	{
		$this->isAdmin();

		if ( defined( 'FS_POSTER_IS_DEMO' ) )
		{
			Helper::response( FALSE, fsp__( 'The feature is disabled on the demo to prevent exporting accounts from here. It will be available on your website.' ) );
		}

		$fs_export_multisite         = Request::post( 'fs_export_multisite', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_export_accounts          = Request::post( 'fs_export_accounts', 1, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_export_failed_accounts   = Request::post( 'fs_export_failed_accounts', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_export_accounts_statuses = Request::post( 'fs_export_accounts_statuses', 1, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_export_apps              = Request::post( 'fs_export_apps', 1, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_export_logs              = Request::post( 'fs_export_logs', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_export_schedules         = Request::post( 'fs_export_schedules', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_export_settings          = Request::post( 'fs_export_settings', 1, 'string', [ 'on' ] ) === 'on' ? 1 : 0;

		Helper::setOption( 'export_multisite', (string) $fs_export_multisite );
		Helper::setOption( 'export_accounts', (string) $fs_export_accounts );
		Helper::setOption( 'export_failed_accounts', (string) $fs_export_failed_accounts );
		Helper::setOption( 'export_accounts_statuses', (string) $fs_export_accounts_statuses );
		Helper::setOption( 'export_apps', (string) $fs_export_apps );
		Helper::setOption( 'export_logs', (string) $fs_export_logs );
		Helper::setOption( 'export_schedules', (string) $fs_export_schedules );
		Helper::setOption( 'export_settings', (string) $fs_export_settings );

		$settings         = [];
		$export_multisite = '';

		if ( ! $fs_export_multisite )
		{
			$export_multisite = 'AND `blog_id` = ' . Helper::getBlogId();
		}

		if ( $fs_export_accounts )
		{
			$settings[ 'accounts' ] = DB::DB()->get_results( 'SELECT * FROM `' . DB::table( 'accounts' ) . '` WHERE 1 = 1 ' . $export_multisite . ' ' . ( $fs_export_failed_accounts ? '' : 'AND ( `status` IS NULL OR `status` != "error" )' ), ARRAY_A );

			$account_ids                         = array_map( function ( $acc ) {
				return $acc[ 'id' ];
			}, $settings[ 'accounts' ] );
			$settings[ 'account_access_tokens' ] = count( $account_ids ) > 0 ? DB::DB()->get_results( 'SELECT * FROM `' . DB::table( 'account_access_tokens' ) . '` WHERE `account_id` IN (' . implode( ',', $account_ids ) . ')', ARRAY_A ) : [];
			$settings[ 'account_nodes' ]         = count( $account_ids ) > 0 ? DB::DB()->get_results( 'SELECT * FROM `' . DB::table( 'account_nodes' ) . '` WHERE `account_id` IN (' . implode( ',', $account_ids ) . ')', ARRAY_A ) : [];

			if ( $fs_export_accounts_statuses )
			{
				$settings[ 'account_status' ] = count( $account_ids ) > 0 ? DB::DB()->get_results( 'SELECT * FROM `' . DB::table( 'account_status' ) . '` WHERE `account_id` IN (' . implode( ',', $account_ids ) . ')', ARRAY_A ) : [];

				$node_ids = array_map( function ( $acc ) {
					return $acc[ 'id' ];
				}, $settings[ 'account_nodes' ] );

				$settings[ 'account_node_status' ] = count( $node_ids ) > 0 ? DB::DB()->get_results( 'SELECT * FROM `' . DB::table( 'account_node_status' ) . '` WHERE `node_id` IN (' . implode( ',', $node_ids ) . ')', ARRAY_A ) : [];
			}
		}

		if ( $fs_export_apps )
		{
			$settings[ 'apps' ] = DB::DB()->get_results( 'SELECT * FROM `' . DB::table( 'apps' ) . '`', ARRAY_A );
		}

		if ( $fs_export_logs )
		{
			$settings[ 'feeds' ] = DB::DB()->get_results( 'SELECT * FROM `' . DB::table( 'feeds' ) . '` WHERE 1 = 1 ' . $export_multisite, ARRAY_A );

			if ( $fs_export_schedules )
			{
				$settings[ 'schedules' ] = DB::DB()->get_results( 'SELECT * FROM `' . DB::table( 'schedules' ) . '` WHERE 1 = 1 ' . $export_multisite, ARRAY_A );
			}
		}

		if ( $fs_export_settings )
		{
			$settings[ 'options' ] = DB::DB()->get_results( 'SELECT `option_name`, `option_value`, `autoload` FROM `' . DB::DB()->base_prefix . 'options` WHERE `option_name` LIKE "fs_%" AND `option_name` NOT IN ( "fs_poster_plugin_purchase_key", "fs_poster_plugin_installed" )', ARRAY_A );
		}

		$file_id = wp_generate_password( 8, FALSE );

		Helper::setOption( 'exported_json_' . $file_id, json_encode( $settings ) );
		Helper::response( TRUE, [
			'file_id' => $file_id,
			'msg'     => fsp__( 'Export is successful. The download process is starting...' )
		] );
	}

	public function settings_import_save ()
	{
		$this->isAdmin();

		if ( ! ( isset( $_FILES[ 'fsp_import_file' ] ) && is_string( $_FILES[ 'fsp_import_file' ][ 'name' ] ) && $_FILES[ 'fsp_import_file' ][ 'size' ] > 0 && $_FILES[ 'fsp_import_file' ][ 'type' ] === 'application/json' ) )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'No valid import file is selected!' ) ] );
		}

		try
		{
			$json         = file_get_contents( $_FILES[ 'fsp_import_file' ][ 'tmp_name' ] );
			$json_array   = json_decode( $json, TRUE );
			$allowed_keys = [
				'account_access_tokens',
				'account_node_status',
				'account_nodes',
				'account_sessions',
				'account_status',
				'accounts',
				'apps',
				'feeds',
				'schedules',
				'grouped_accounts',
			];

			DB::DB()->query( 'SET FOREIGN_KEY_CHECKS = 0;' );

			foreach ( $json_array as $table => $rows )
			{
				if ( in_array( $table, $allowed_keys ) && ! empty( $rows ) && is_array( $rows ) )
				{
					DB::DB()->query( 'TRUNCATE TABLE `' . DB::table( $table ) . '`' );

					foreach ( $rows as $row )
					{
						if ( ! is_array( $row ) || empty( $row ) )
						{
							continue;
						}

						DB::DB()->insert( DB::table( $table ), $row );
					}
				}
				else
				{
				}
			}

			if ( isset( $json_array[ 'options' ] ) && is_array( $json_array[ 'options' ] ) && ! empty( $json_array[ 'options' ] ) )
			{
				DB::DB()->query( 'DELETE FROM `' . DB::DB()->base_prefix . 'options` WHERE `option_name` LIKE "fs_%" AND `option_name` NOT IN ( "fs_poster_plugin_purchase_key", "fs_poster_plugin_installed" )' );

				foreach ( $json_array[ 'options' ] as $option )
				{
					if ( ! is_array( $option ) || empty( $option ) || in_array( $option[ 'option_name' ], [
							'fs_poster_plugin_purchase_key',
							'fs_poster_plugin_installed'
						] ) )
					{
						continue;
					}

					DB::DB()->insert( DB::DB()->base_prefix . 'options', $option );
				}
			}

			DB::DB()->query( "SET FOREIGN_KEY_CHECKS = 1;" );
		}
		catch ( Exception $e )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'Error occurred while importing!' ) ] );
		}

		Helper::response( TRUE, [ 'msg' => fsp__( 'Successfully restored!' ) ] );
	}
}
