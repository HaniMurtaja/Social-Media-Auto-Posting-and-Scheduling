<?php

namespace FSPoster\App\Pages\Base\Controllers;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

class Action
{
	public function get_post_meta_box ( $post_id )
	{
		$share = Request::get( 'share', '0', 'string' );

		function get_nodes ( $node_list = NULL )
		{
			$accounts     = [];
			$active_nodes = [];

			if ( $node_list === NULL )
			{
				$accounts = DB::DB()->get_results( DB::DB()->prepare( "
SELECT tb2.*, tb1.filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM " . DB::WPtable( 'terms', TRUE ) . " WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name,'account' AS node_type
FROM " . DB::table( 'account_status' ) . " tb1
INNER JOIN " . DB::table( 'accounts' ) . " tb2 ON tb2.id=tb1.account_id
WHERE tb1.user_id=%d AND tb2.blog_id=%d
ORDER BY name", [ get_current_user_id(), Helper::getBlogId() ] ), ARRAY_A );

				$active_nodes = DB::DB()->get_results( DB::DB()->prepare( "
SELECT tb2.*, tb1.filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM " . DB::WPtable( 'terms', TRUE ) . " WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name FROM " . DB::table( 'account_node_status' ) . " tb1
LEFT JOIN " . DB::table( 'account_nodes' ) . " tb2 ON tb2.id=tb1.node_id
WHERE tb1.user_id=%d AND tb2.blog_id=%d
ORDER BY (CASE node_type WHEN 'ownpage' THEN 1 WHEN 'group' THEN 2 WHEN 'page' THEN 3 END), name", [
					get_current_user_id(),
					Helper::getBlogId()
				] ), ARRAY_A );
			}
			else
			{
				$node_list = is_array( $node_list ) ? $node_list : [];

				foreach ( $node_list as $node_info01 )
				{
					$node_info01 = explode( ':', $node_info01 );

					if ( count( $node_info01 ) < 3 )
					{
						continue;
					}

					if ( $node_info01[ 1 ] === 'account' )
					{
						$accounts_list[] = (int) $node_info01[ 2 ];
					}
					else
					{
						$nodes_list[] = (int) $node_info01[ 2 ];
					}
				}

				if ( ! empty( $accounts_list ) )
				{
					$accounts_list = "'" . implode( "','", $accounts_list ) . "'";

					$accounts = DB::DB()->get_results( DB::DB()->prepare( "SELECT tb2.*, IFNULL(tb1.filter_type, 'no') AS filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM " . DB::WPtable( 'terms', TRUE ) . " WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name,'account' AS node_type
FROM " . DB::table( 'accounts' ) . " tb2
LEFT JOIN " . DB::table( 'account_status' ) . " tb1 ON tb2.id=tb1.account_id AND tb1.user_id=%d
WHERE tb2.blog_id=%d AND tb2.id IN ({$accounts_list}) AND (tb2.user_id=%d OR tb2.is_public=1)
ORDER BY name", [ get_current_user_id(), Helper::getBlogId(), get_current_user_id() ] ), ARRAY_A );
				}

				if ( ! empty( $nodes_list ) )
				{
					$nodes_list = "'" . implode( "','", $nodes_list ) . "'";

					$active_nodes = DB::DB()->get_results( DB::DB()->prepare( "
SELECT tb2.*, IFNULL(tb1.filter_type, 'no') AS filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM " . DB::WPtable( 'terms', TRUE ) . " WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name
FROM " . DB::table( 'account_nodes' ) . " tb2
LEFT JOIN " . DB::table( 'account_node_status' ) . " tb1 ON tb2.id=tb1.node_id AND tb1.user_id=%d
WHERE tb2.blog_id=%d AND tb2.id IN ({$nodes_list}) AND (tb2.user_id=%d OR tb2.is_public=1)
ORDER BY node_type, name", [
						get_current_user_id(),
						Helper::getBlogId(),
						get_current_user_id()
					] ),
						ARRAY_A );
				}
			}

			return [
				$accounts,
				$active_nodes
			];
		}

		$cm_fs_post_text_message_fb          = Helper::getOption( 'post_text_message_fb', '{title}' );
		$cm_fs_post_text_message_fb_h        = Helper::getOption( 'post_text_message_fb_h', '{title}' );
		$cm_fs_post_text_message_twitter     = Helper::getOption( 'post_text_message_twitter', '{title}' );
		$cm_fs_post_text_message_instagram   = Helper::getOption( 'post_text_message_instagram', '{title}' );
		$cm_fs_post_text_message_instagram_h = Helper::getOption( 'post_text_message_instagram_h', '{title}' );
		$cm_fs_post_text_message_linkedin    = Helper::getOption( 'post_text_message_linkedin', '{title}' );
		$cm_fs_post_text_message_vk          = Helper::getOption( 'post_text_message_vk', '{title}' );
		$cm_fs_post_text_message_pinterest   = Helper::getOption( 'post_text_message_pinterest', "{content_short_500}" );
		$cm_fs_post_text_message_reddit      = Helper::getOption( 'post_text_message_reddit', '{title}' );
		$cm_fs_post_text_message_tumblr      = Helper::getOption( 'post_text_message_tumblr', "<img src='{featured_image_url}'>\n\n{content_full}" );
		$cm_fs_post_text_message_ok          = Helper::getOption( 'post_text_message_ok', '{title}' );
		$cm_fs_post_text_message_google_b    = Helper::getOption( 'post_text_message_google_b', '{title}' );
		$cm_fs_post_text_message_blogger     = Helper::getOption( 'post_text_message_blogger', "<img src='{featured_image_url}'>\n\n{content_full} \n\n<a href='{link}'>{link}</a>" );
		$cm_fs_post_text_message_telegram    = Helper::getOption( 'post_text_message_telegram', '{title}' );
		$cm_fs_post_text_message_medium      = Helper::getOption( 'post_text_message_medium', "{title}\n\n<img src='{featured_image_url}'>\n\n{content_full}\n\n<a href='{link}'>{link}</a>" );
		$cm_fs_post_text_message_wordpress   = Helper::getOption( 'post_text_message_wordpress', '{content_full}' );
		$cm_fs_post_text_message_plurk       = Helper::getOption( 'post_text_message_plurk', "{title}\n\n{featured_image_url}\n\n{content_short_200}" );

		if ( ! defined( 'NOT_CHECK_SP' ) && $share === '1' )
		{
			$check_not_sended_feeds = DB::DB()->get_row( DB::DB()->prepare( "SELECT count(0) AS cc FROM " . DB::table( 'feeds' ) . " WHERE post_id=%d AND is_sended=0 AND blog_id=%d", [
				(int) $post_id,
				Helper::getBlogId()
			] ), ARRAY_A );
		}

		if ( isset( $post_id ) && $post_id > 0 && ( get_post_status() === 'draft' || get_post_status() === 'pending' ) && metadata_exists( 'post', $post_id, '_fs_is_manual_action' ) )
		{
			$share_checkbox = get_post_meta( $post_id, '_fs_poster_share', TRUE );

			$cm_fs_post_text_message_fb          = get_post_meta( $post_id, '_fs_poster_cm_fb', TRUE );
			$cm_fs_post_text_message_fb_h        = get_post_meta( $post_id, '_fs_poster_cm_fb_h', TRUE );
			$cm_fs_post_text_message_twitter     = get_post_meta( $post_id, '_fs_poster_cm_twitter', TRUE );
			$cm_fs_post_text_message_instagram   = get_post_meta( $post_id, '_fs_poster_cm_instagram', TRUE );
			$cm_fs_post_text_message_instagram_h = get_post_meta( $post_id, '_fs_poster_cm_instagram_h', TRUE );
			$cm_fs_post_text_message_linkedin    = get_post_meta( $post_id, '_fs_poster_cm_linkedin', TRUE );
			$cm_fs_post_text_message_vk          = get_post_meta( $post_id, '_fs_poster_cm_vk', TRUE );
			$cm_fs_post_text_message_pinterest   = get_post_meta( $post_id, '_fs_poster_cm_pinterest', TRUE );
			$cm_fs_post_text_message_reddit      = get_post_meta( $post_id, '_fs_poster_cm_reddit', TRUE );
			$cm_fs_post_text_message_tumblr      = get_post_meta( $post_id, '_fs_poster_cm_tumblr', TRUE );
			$cm_fs_post_text_message_ok          = get_post_meta( $post_id, '_fs_poster_cm_ok', TRUE );
			$cm_fs_post_text_message_google_b    = get_post_meta( $post_id, '_fs_poster_cm_google_b', TRUE );
			$cm_fs_post_text_message_blogger     = get_post_meta( $post_id, '_fs_poster_cm_blogger', TRUE );
			$cm_fs_post_text_message_telegram    = get_post_meta( $post_id, '_fs_poster_cm_telegram', TRUE );
			$cm_fs_post_text_message_medium      = get_post_meta( $post_id, '_fs_poster_cm_medium', TRUE );
			$cm_fs_post_text_message_wordpress   = get_post_meta( $post_id, '_fs_poster_cm_wordpress', TRUE );
			$cm_fs_post_text_message_plurk       = get_post_meta( $post_id, '_fs_poster_cm_plurk', TRUE );

			$node_list = get_post_meta( $post_id, '_fs_poster_node_list', TRUE );

			list( $accounts, $nodes ) = get_nodes( $node_list );

		}
		else if ( get_post_type( $post_id ) === 'fs_post' )
		{
			$share_checkbox = Helper::getOption( 'auto_share_new_posts', '1' ) || Request::get( 'page' ) == 'fs-poster-share' || Request::post( 'post_id' ) !== NULL ? 'on' : 'off';

			$node_list = get_post_meta( $post_id, '_fs_poster_node_list', TRUE ) ? get_post_meta( $post_id, '_fs_poster_node_list', TRUE ) : NULL;

			list( $accounts, $nodes ) = get_nodes( $node_list );
		}
		else
		{
			$share_checkbox = Helper::getOption( 'auto_share_new_posts', '1' ) || Request::get( 'page' ) == 'fs-poster-share' || Request::post( 'post_id' ) !== NULL ? 'on' : 'off';

			list( $accounts, $nodes ) = get_nodes();
		}

		$active_nodes = array_merge( $accounts, $nodes );

		return [
			'active_nodes'                        => $active_nodes,
			'share_checkbox'                      => $share_checkbox,
			'cm_fs_post_text_message_fb'          => $cm_fs_post_text_message_fb,
			'cm_fs_post_text_message_fb_h'        => $cm_fs_post_text_message_fb_h,
			'cm_fs_post_text_message_twitter'     => $cm_fs_post_text_message_twitter,
			'cm_fs_post_text_message_telegram'    => $cm_fs_post_text_message_telegram,
			'cm_fs_post_text_message_tumblr'      => $cm_fs_post_text_message_tumblr,
			'cm_fs_post_text_message_instagram'   => $cm_fs_post_text_message_instagram,
			'cm_fs_post_text_message_ok'          => $cm_fs_post_text_message_ok,
			'cm_fs_post_text_message_vk'          => $cm_fs_post_text_message_vk,
			'cm_fs_post_text_message_linkedin'    => $cm_fs_post_text_message_linkedin,
			'cm_fs_post_text_message_pinterest'   => $cm_fs_post_text_message_pinterest,
			'cm_fs_post_text_message_reddit'      => $cm_fs_post_text_message_reddit,
			'cm_fs_post_text_message_medium'      => $cm_fs_post_text_message_medium,
			'cm_fs_post_text_message_wordpress'   => $cm_fs_post_text_message_wordpress,
			'cm_fs_post_text_message_google_b'    => $cm_fs_post_text_message_google_b,
			'cm_fs_post_text_message_blogger'     => $cm_fs_post_text_message_blogger,
			'cm_fs_post_text_message_instagram_h' => $cm_fs_post_text_message_instagram_h,
			'cm_fs_post_text_message_plurk'       => $cm_fs_post_text_message_plurk,
			'check_not_sended_feeds'              => isset( $check_not_sended_feeds ) ? $check_not_sended_feeds : NULL,
			'post_id'                             => $post_id,
			'tabs'                                => [
				'fb'        => [
					'icon' => 'fab fa-facebook-f',
					'long' => fsp__( 'Facebook' )
				],
				'twitter'   => [
					'icon' => 'fab fa-twitter',
					'long' => fsp__( 'Twitter' )
				],
				'instagram' => [
					'icon' => 'fab fa-instagram',
					'long' => fsp__( 'Instagram' )
				],
				'linkedin'  => [
					'icon' => 'fab fa-linkedin-in',
					'long' => fsp__( 'Linkedin' )
				],
				'vk'        => [
					'icon' => 'fab fa-vk',
					'long' => fsp__( 'VKontakte' )
				],
				'pinterest' => [
					'icon' => 'fab fa-pinterest-p',
					'long' => fsp__( 'Pinterest' )
				],
				'reddit'    => [
					'icon' => 'fab fa-reddit-alien',
					'long' => fsp__( 'Reddit' )
				],
				'tumblr'    => [
					'icon' => 'fab fa-tumblr',
					'long' => fsp__( 'Tumblr' )
				],
				'ok'        => [
					'icon' => 'fab fa-odnoklassniki',
					'long' => fsp__( 'Odnoklassniki' )
				],
				'plurk'     => [
					'icon' => 'fas fa-parking',
					'long' => fsp__( 'Plurk' )
				],
				'google_b'  => [
					'icon' => 'fab fa-google',
					'long' => fsp__( 'GMB' )
				],
				'blogger'   => [
					'icon' => 'fab fa-blogger',
					'long' => fsp__( 'Blogger' )
				],
				'telegram'  => [
					'icon' => 'fab fa-telegram-plane',
					'long' => fsp__( 'Telegram' )
				],
				'medium'    => [
					'icon' => 'fab fa-medium-m',
					'long' => fsp__( 'Medium' )
				],
				'wordpress' => [
					'icon' => 'fab fa-wordpress-simple',
					'long' => fsp__( 'WordPress' )
				],
			]
		];
	}

	public function get_post_meta_box_edit ( $data )
	{
		$share = Request::get( 'share', '0', 'string' );

		if ( $share === '1' )
		{
			$background = Request::get( 'background', '', 'string' );

			if ( ! empty( $background ) )
			{
				?>
				<script>
					jQuery( document ).ready( function () {
						FSPoster.toast( "<?php echo fsp__( 'The post will be shared in the background!' ); ?>", 'info' );

						window.history.pushState( {}, '', window.location.href.replace( /&share=1&background=([0-9]+)/, '' ) );
					} );
				</script>
				<?php
			}
			else
			{
				$checkNotSendedFeeds = DB::DB()->get_row( DB::DB()->prepare( "SELECT count(0) AS cc FROM " . DB::table( 'feeds' ) . " WHERE post_id=%d AND is_sended=0 AND `blog_id`=%d", [
					(int) $data[ 'post' ]->ID,
					Helper::getBlogId()
				] ), ARRAY_A );
			}
		}

		$feeds = DB::fetchAll( 'feeds', [
			'blog_id' => Helper::getBlogId(),
			'post_id' => $data[ 'post' ]->ID
		] );

		return [
			'parameters'             => [
				'post' => $data[ 'post' ]
			],
			'feeds'                  => $feeds,
			'check_not_sended_feeds' => isset( $checkNotSendedFeeds ) ? $checkNotSendedFeeds : [ 'cc' => 0 ]
		];
	}

	public function get_nodes ()
	{
		$accounts_list = DB::DB()->get_results( DB::DB()->prepare( "
					SELECT 
						tb2.*, IFNULL(tb1.filter_type, 'no') AS filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM " . DB::WPtable( 'terms', TRUE ) . " WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name,'account' AS node_type 
					FROM " . DB::table( 'accounts' ) . " tb2
					LEFT JOIN " . DB::table( 'account_status' ) . " tb1 ON tb2.id=tb1.account_id AND tb1.user_id=%d
					WHERE (tb2.user_id=%d OR tb2.is_public=1) AND tb2.blog_id=%d
					ORDER BY name", [ get_current_user_id(), get_current_user_id(), Helper::getBlogId() ] ), ARRAY_A );

		$pagesList = DB::DB()->get_results( DB::DB()->prepare( "
				SELECT 
					tb2.*, IFNULL(tb1.filter_type, 'no') AS filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM " . DB::WPtable( 'terms', TRUE ) . " WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name 
				FROM " . DB::table( 'account_nodes' ) . " tb2
				LEFT JOIN " . DB::table( 'account_node_status' ) . " tb1 ON tb2.id=tb1.node_id AND tb1.user_id=%d
				WHERE (tb2.user_id=%d OR tb2.is_public=1) AND tb2.blog_id=%d
				ORDER BY (CASE node_type WHEN 'ownpage' THEN 1 WHEN 'group' THEN 2 WHEN 'page' THEN 3 END), name", [
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		$fspGroups = DB::fetchAll( 'account_groups', [
			'user_id' => get_current_user_id(),
			'blog_id' => Helper::getBlogId()
		] );

		$nodes_all = array_merge( $accounts_list, $pagesList );

		$nodes_allByKey  = [];
		$nodes_allSorted = [ '-' => [] ];

		foreach ( $nodes_all as $node_info )
		{
			$nodes_allByKey[ $node_info[ 'node_type' ] . ':' . (int) $node_info[ 'id' ] ] = $node_info;
		}

		foreach ( $nodes_all as $node_info2 )
		{
			if ( isset( $node_info2[ 'account_id' ] ) && isset( $nodes_allByKey[ 'account:' . $node_info2[ 'account_id' ] ] ) )
			{
				$nodes_allSorted[ 'account:' . $node_info2[ 'account_id' ] ][] = $node_info2[ 'node_type' ] . ':' . (int) $node_info2[ 'id' ];
			}
			else
			{
				$nodes_allSorted[ '-' ][] = $node_info2[ 'node_type' ] . ':' . (int) $node_info2[ 'id' ];
			}
		}

		function printNodeCart ( $node, $isSub = FALSE )
		{
			$val = esc_html( $node[ 'driver' ] . ':' . $node[ 'node_type' ] ) . ':' . (int) $node[ 'id' ] . ':' . htmlspecialchars( $node[ 'filter_type' ] ) . ':' . htmlspecialchars( $node[ 'categories' ] );

			if ( in_array( $val, Request::post( 'dont_show', [], 'array' ) ) )
			{
				return '';
			}

			$isSub         = $isSub ? ' fsp-is-sub' : '';
			$isNonSharable = ( in_array( $node[ 'driver' ], [
					'pinterest',
					'tumblr',
					'google_b',
					'blogger',
					'telegram'
				] ) || ( $node[ 'driver' ] === 'fb' && empty( $node[ 'options' ] ) )  || ( $node[ 'driver' ] === 'instagram' && $node['password'] == '#####' ) ) && $node[ 'node_type' ] === 'account' ? ' fsp-is-disabled' : '';
			$sn_names      = [
				'fb'        => fsp__( 'FB' ),
				'twitter'   => fsp__( 'Twitter' ),
				'instagram' => fsp__( 'Instagram' ),
				'linkedin'  => fsp__( 'Linkedin' ),
				'vk'        => fsp__( 'VK' ),
				'pinterest' => fsp__( 'Pinterest' ),
				'reddit'    => fsp__( 'Reddit' ),
				'tumblr'    => fsp__( 'Tumblr' ),
				'ok'        => fsp__( 'OK' ),
				'plurk'     => fsp__( 'Plurk' ),
				'google_b'  => fsp__( 'GMB' ),
				'blogger'   => fsp__( 'Blogger' ),
				'telegram'  => fsp__( 'Telegram' ),
				'medium'    => fsp__( 'Medium' ),
				'wordpress' => fsp__( 'WordPress' )
			];
			$driver        = $sn_names[ $node[ 'driver' ] ];

			return '<div class="fsp-metabox-account' . $isNonSharable . $isSub . '" data-id="' . $val . '">
				<div class="fsp-metabox-account-image">
					<img src="' . Helper::profilePic( $node ) . '" onerror="FSPoster.no_photo( this );">
				</div>
				<div class="fsp-metabox-account-label">
					<a href="' . Helper::profileLink( $node ) . '" target="_blank" class="fsp-metabox-account-text">
						' . esc_html( $node[ 'name' ] ) . '
					</a>
					<div class="fsp-metabox-account-subtext">
						' . $driver . ' > ' . esc_html( $node[ 'node_type' ] ) . '
					</div>
				</div>
			</div>';
		}

		$metabox_accounts = '';

		foreach ( $nodes_allSorted[ '-' ] as $nodeKey )
		{
			$node = isset( $nodes_allByKey[ $nodeKey ] ) ? $nodes_allByKey[ $nodeKey ] : [];

			if ( empty( $node ) )
			{
				continue;
			}

			$metabox_accounts .= printNodeCart( $node );

			if ( isset( $nodes_allSorted[ $nodeKey ] ) )
			{
				foreach ( $nodes_allSorted[ $nodeKey ] as $nodeSubKey )
				{
					$subNode = isset( $nodes_allByKey[ $nodeSubKey ] ) ? $nodes_allByKey[ $nodeSubKey ] : [];

					if ( empty( $subNode ) )
					{
						continue;
					}

					$metabox_accounts .= printNodeCart( $subNode, TRUE );
				}
			}
		}

		$metabox_groups = '';

		foreach ( $fspGroups as $mb_group )
		{
			$val = 'fsp:group:' . $mb_group[ 'id' ] . ':no:';

			if ( in_array( $val, Request::post( 'dont_show', [], 'array' ) ) )
			{
				continue;
			}

			$metabox_groups .= '
				<div class="fsp-metabox-account" data-id="' . $val . '">
					<span class="fsp-metabox-account-badge" style="background-color: ' . $mb_group[ 'color' ] . ';"></span>
					<div class="fsp-metabox-account-label">
						<a target="_blank" href="admin.php?page=fs-poster-accounts&view=groups&group=' . $mb_group[ 'id' ] . '" target="_blank" class="fsp-metabox-account-text">' . $mb_group[ 'name' ] . '</a>
						<div class="fsp-metabox-account-subtext">' . fsp__( 'FSP > Group' ) . '</div>
					</div>
				</div>
			';
		}

		return [
			'metabox_accounts' => $metabox_accounts,
			'metabox_groups'   => $metabox_groups
		];
	}

	public function groups_to_nodes ( $params )
	{
		$node_list = $params['node_list'];
		$nodes = [];

		$get_group_nodes = function ( $group_id )
		{
			$accounts_list = DB::DB()->get_results( DB::DB()->prepare( "
					SELECT 
						tb2.*, IFNULL(tb1.filter_type, 'no') AS filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM " . DB::WPtable( 'terms', TRUE ) . " WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name,'account' AS node_type 
					FROM " . DB::table( 'accounts' ) . " tb2
					LEFT JOIN " . DB::table( 'account_status' ) . " tb1 ON tb2.id=tb1.account_id AND tb1.user_id=%d
					WHERE (tb2.user_id=%d OR tb2.is_public=1) AND tb2.blog_id=%d AND tb2.id IN (SELECT tb3.node_id FROM " . DB::table( 'account_groups_data' ) . " tb3 WHERE tb3.group_id=%d AND tb3.node_type='account')
					ORDER BY name", [
				get_current_user_id(),
				get_current_user_id(),
				Helper::getBlogId(),
				$group_id
			] ), ARRAY_A );

			$pagesList = DB::DB()->get_results( DB::DB()->prepare( "
				SELECT 
					tb2.*, IFNULL(tb1.filter_type, 'no') AS filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM " . DB::WPtable( 'terms', TRUE ) . " WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name 
				FROM " . DB::table( 'account_nodes' ) . " tb2
				LEFT JOIN " . DB::table( 'account_node_status' ) . " tb1 ON tb2.id=tb1.node_id AND tb1.user_id=%d
				WHERE (tb2.user_id=%d OR tb2.is_public=1) AND tb2.blog_id=%d AND tb2.id IN (SELECT tb3.node_id FROM " . DB::table( 'account_groups_data' ) . " tb3 WHERE tb3.group_id=%d AND tb3.node_type='node')
				ORDER BY (CASE node_type WHEN 'ownpage' THEN 1 WHEN 'group' THEN 2 WHEN 'page' THEN 3 END), name", [
				get_current_user_id(),
				get_current_user_id(),
				Helper::getBlogId(),
				$group_id
			] ), ARRAY_A );

			$node_list = [];

			foreach ( $accounts_list as $node )
			{
				$node_list[] = esc_html( $node[ 'driver' ] . ':' . $node[ 'node_type' ] ) . ':' . (int) $node[ 'id' ] . ':' . htmlspecialchars( $node[ 'filter_type' ] ) . ':' . htmlspecialchars( $node[ 'categories' ] );
			}

			foreach ( $pagesList as $node )
			{
				$node_list[] = esc_html( $node[ 'driver' ] . ':' . $node[ 'node_type' ] ) . ':' . (int) $node[ 'id' ] . ':' . htmlspecialchars( $node[ 'filter_type' ] ) . ':' . htmlspecialchars( $node[ 'categories' ] );
			}

			return $node_list;
		};

		foreach ( $node_list as $node )
		{
			$node_info = explode( ':', $node );

			if ( $node_info[ 0 ] === 'fsp' )
			{
				if ( ! empty( $node_info[ 2 ] ) )
				{
					$group_nodes = $get_group_nodes( $node_info[ 2 ] );
					$nodes       = array_merge( $nodes, $group_nodes );
				}
			}
			else
			{
				$nodes[] = $node;
			}
		}

		return array_unique($nodes);
	}
}
