<?php

namespace FSPoster\App\Providers;

use Exception;
use FSPoster\App\Pages\Accounts\Controllers\Action;

class BackEnd
{
	use PluginMenu;

	private $active_custom_post_types;

	public function __construct ()
	{
		new Ajax();

		if ( ! Helper::pluginDisabled() )
		{
			new Popups();
		}

		$this->initMenu();

		$this->enqueueAssets();
		$this->updateService();
		$this->registerMetaBox();
		$this->addNewsWidget();

		$this->registerActions();
		$this->registerBulkAction();
		$this->registerNotifications();
		$this->cleanFSPoster();
	}

	private function registerMetaBox ()
	{
		add_action( 'add_meta_boxes', function () {
			if ( Helper::isHiddenUser() )
			{
				return;
			}

			add_meta_box( 'fs_poster_meta_box', 'FS Poster', [
				$this,
				'publish_meta_box'
			], $this->getActiveCustomPostTypes(), 'side', 'high' );
		} );
	}

	public function publish_meta_box ( $post )
	{
		if ( in_array( $post->post_status, [ 'new', 'auto-draft', 'draft', 'pending' ] ) )
		{
			Pages::controller( 'Base', 'MetaBox', 'post_metabox_v2', [
				'post_id' => $post->ID
			] );
		}
		else
		{
			Pages::controller( 'Base', 'MetaBox', 'post_meta_box_edit', [
				'post' => $post
			] );
		}
	}

	private function addNewsWidget ()
	{
		add_action( 'wp_dashboard_setup', function () {
			wp_add_dashboard_widget( 'fsp-news', 'FS Poster', function () {
				$dataURL  = 'https://www.fs-poster.com/api/news/';
				$expTime  = 43200; // In seconds

				try
				{
				    $cachedData     = json_decode( Helper::getOption( 'news_cache', FALSE, TRUE ) );
				    $now            = Date::epoch();

				    if ( empty( $cachedData ) || $now - $cachedData->time >= $expTime )
                    {
                        $data = Curl::getContents( $dataURL );

                        Helper::setOption( 'news_cache', json_encode( [
                            'time'  => $now,
                            'data'  => $data
                        ] ), TRUE );
                    }
				    else
                    {
                        $data = $cachedData->data;
                    }
				}
				catch ( Exception $e )
				{
					$data = '';
				}

                echo $data;
			} );
		} );
	}

	private function enqueueAssets ()
	{
		add_action( 'admin_enqueue_scripts', function () {
			wp_register_script( 'fsp-select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js' );
			wp_enqueue_script( 'fsp-select2' );
			wp_register_script( 'fsp', Pages::asset( 'Base', 'js/fsp.js' ), [ 'jquery' ], NULL );
			wp_enqueue_script( 'fsp' );
			wp_localize_script( 'fsp', 'fspConfig', [
				'pagesURL' => plugins_url( 'Pages/', dirname( __FILE__ ) ),
				'siteURL'  => site_url()
			] );
			wp_localize_script( 'fsp', 'FSPObject', [
				'modals' => []
			] );
			$this->bulkScheduleAction();

			wp_enqueue_style( 'fsp-fonts', '//fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;1,400;1,600&display=swap' );
			wp_enqueue_style( 'fsp-fontawesome', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/all.min.css' );
			wp_enqueue_style( 'fsp-select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css' );
			wp_enqueue_style( 'fsp-ui', Pages::asset( 'Base', 'css/fsp-ui.css' ), [], NULL );
			wp_enqueue_style( 'fsp-base', Pages::asset( 'Base', 'css/fsp-base.min.css' ), [], NULL );
			wp_enqueue_style( 'fsp-select2-custom', Pages::asset( 'Base', 'css/fsp-select2.css' ), [
				'fsp-select2',
				'fsp-ui',
				'fsp-base'
			], NULL );
		} );
	}

	private function updateService ()
	{
		$activationKey = Helper::getOption( 'poster_plugin_purchase_key', '', TRUE );

		if ( ! empty( $activationKey ) )
		{
			add_action( 'init', function () use ( $activationKey ) {
				$updater = new FSCodeUpdater( 'fs-poster', FS_API_URL . 'api.php', $activationKey );
			} );
		}
	}

	private function registerActions ()
	{
		$page                = Request::get( 'page', '', 'string' );
		$is_download_request = Request::get( 'download', '', 'string' );
		$exported_json       = Helper::getOption( 'exported_json_' . $is_download_request, '' );

		if ( $page === 'fs-poster-settings' && ! empty( $is_download_request ) && ! empty( $exported_json ) )
		{
			Helper::deleteOption( 'exported_json_' . $is_download_request );

			add_action( 'admin_init', function () use ( $exported_json, $is_download_request ) {
				header( 'Content-disposition: attachment; filename=fs_poster_' . $is_download_request . '.json' );
				header( 'Content-type: application/json' );

				exit( $exported_json );
			} );
		}

		if ( Helper::getOption( 'show_fs_poster_column', '1' ) )
		{
			$usedColumnsSave = [];

			foreach ( $this->getActiveCustomPostTypes() as $postType )
			{
				$postType = preg_replace( '/[^a-zA-Z0-9\-\_]/', '', $postType );

				switch ( $postType )
				{
					case 'post':
						$typeName = 'posts';
						break;
					case 'page':
						$typeName = 'pages';
						break;
					case 'attachment':
						$typeName = 'media';
						break;
					default:
						$typeName = $postType . '_posts';
				}

				add_action( 'manage_' . $typeName . '_custom_column', function ( $column_name, $post_id ) use ( &$usedColumnsSave ) {
					if ( ! Helper::isHiddenUser() && $column_name === 'fsp-share-column' && ! isset( $usedColumnsSave[ $post_id ] ) )
					{
						if ( get_post_status( $post_id ) === 'publish' )
						{
							$shareCount = DB::DB()->get_row( 'SELECT COUNT(0) AS c FROM ' . DB::table( 'feeds' ) . ' WHERE post_id=\'' . $post_id . '\' AND status=\'ok\'', ARRAY_A );

							$community_text = $shareCount[ 'c' ] == 1 ? fsp__('community') : fsp__('communities');
							echo '<i class="fas fa-rocket fsp-tooltip" data-title="' . fsp__( 'Share' ) . '" data-load-modal="share_saved_post" data-parameter-post_id="' . $post_id . '"></i><i class="fas fa-history fsp-tooltip" data-title="' . fsp__( 'Schedule' ) . '" data-load-modal="add_schedule" data-parameter-post_ids="' . $post_id . '"></i><i class="fas fa-bars fsp-tooltip" data-title="' . fsp__( 'This post is shared %d %s by FS Poster', [ $shareCount[ 'c' ], $community_text ] ) . '"></i>';
						}
						else
						{
							echo '<i class="fas fa-exclamation-triangle fsp-tooltip" data-title="' . fsp__( 'Only published posts can be shared or scheduled.' ) . '"></i>';
						}

						$usedColumnsSave[ $post_id ] = TRUE;
					}
				}, 10, 2 );

				add_filter( 'manage_' . $typeName . '_columns', function ( $columns ) {
					if ( ! Helper::isHiddenUser() && is_array( $columns ) && ! isset( $columns[ 'fsp-share-column' ] ) )
					{
						$columns[ 'fsp-share-column' ] = 'FS Poster';
					}

					return $columns;
				} );

			}

			$taxonomy = Request::get( 'taxonomy', '', 'string' );

			if ( ! empty( $taxonomy ) )
			{
				$taxonomy = $_REQUEST[ 'taxonomy' ];

				add_filter( "manage_edit-{$taxonomy}_columns", function ( $columns ) {
					return array_merge( $columns, [ 'fsp-share-column' => 'FS Poster' ] );
				} );

				add_action( "manage_{$taxonomy}_custom_column", function ( $content, $column_name, $term_id ) {

					if ( $column_name === 'fsp-share-column' )
					{
						$content = '<i class="fas fa-history fsp-tooltip" data-title="' . fsp__( 'Schedule' ) . '" data-load-modal="add_schedule" data-parameter-term_id="' . $term_id . '"></i>';
					}

					echo $content;
				}, 10, 3 );
			}
		}

		add_action( 'deleted_user', function ( $user_id ) {
			$this->cleanFSPoster( TRUE );
		} );

        add_action( 'user_register', function ( $user_id ) {
            $userData = get_userdata( $user_id );

            if ( ! in_array( $userData->roles, explode( '|', Helper::getOption( 'hide_menu_for', '' ) ) ) )
            {
                $activeAccountsForAll = DB::DB()->get_results( 'SELECT `acc`.*, `st`.`categories`, `st`.`filter_type` FROM '. DB::table( 'accounts' ) .' AS `acc` LEFT JOIN '. DB::table( 'account_status' ) .' AS `st` ON `st`.`account_id` = `acc`.`id`  WHERE `acc`.`for_all` = 1' );

                foreach ( $activeAccountsForAll as $acc )
                {
                    Action::activate_deactivate_account( $user_id, $acc->id, 1, $acc->filter_type, $acc->categories );
                }

                $activeNodesForAll = DB::DB()->get_results( 'SELECT `acc`.*, `st`.`categories`, `st`.`filter_type` FROM '. DB::table( 'account_nodes' ) .' AS `acc` LEFT JOIN '. DB::table( 'account_node_status' ) .' AS `st` ON `st`.`node_id` = `acc`.`id`  WHERE `acc`.`for_all` = 1' );

                foreach ( $activeNodesForAll as $node )
                {
                    Action::activate_deactivate_node( $user_id, $node->id, 1, $node->filter_type, $node->categories );
                }
            }
        } );
	}

	private function registerBulkAction ()
	{
		foreach ( $this->getActiveCustomPostTypes() as $postType )
		{
			if ( $postType === 'attachment' )
			{
				$postType = 'upload';
			}
			else
			{
				$postType = 'edit-' . $postType;
			}

			add_filter( 'bulk_actions-' . $postType, function ( $bulk_actions ) {
				if ( ! Helper::isHiddenUser() )
				{
					$bulk_actions[ 'fs_schedule' ] = fsp__( 'FS Poster: Schedule' );
				}

				return $bulk_actions;
			} );

			add_filter( 'handle_bulk_actions-' . $postType, function ( $redirect_to, $doaction, $post_ids ) {
				if ( $doaction !== 'fs_schedule' )
				{
					return $redirect_to;
				}

				return add_query_arg( 'fs_schedule_posts', implode( ',', $post_ids ), $redirect_to );
			}, 20, 3 );
		}
	}

	private function getActiveCustomPostTypes ()
	{
		if ( is_null( $this->active_custom_post_types ) )
		{
			$this->active_custom_post_types = explode( '|', Helper::getOption( 'allowed_post_types', 'post|page|attachment|product' ) );
		}

		return $this->active_custom_post_types;
	}

	private function bulkScheduleAction ()
	{
		$posts = Request::get( 'fs_schedule_posts', '', 'string' );

		$posts    = explode( ',', $posts );
		$post_ids = [];
		foreach ( $posts as $post_id )
		{
			if ( is_numeric( $post_id ) && $post_id > 0 )
			{
				$post_ids[] = (int) $post_id;
			}
		}

		if ( empty( $post_ids ) )
		{
			return;
		}

		wp_add_inline_script( 'fsp', 'jQuery(document).ready(function(){ FSPoster.loadModal("add_schedule" , {"post_ids": "' . implode( ',', $post_ids ) . '"}) });' );
	}

	private function registerNotifications ()
	{
		add_action( 'init', function () {

			if ( Helper::isHiddenUser() )
			{
				return;
			}

			$plgnVer = Helper::getOption( 'poster_plugin_installed', '0', TRUE );

			if ( ! $plgnVer )
			{
				return;
			}

			if ( Helper::getOption( 'hide_notifications', '0' ) != 1 )
			{
				$failed_accounts = DB::DB()->get_row( DB::DB()->prepare( 'SELECT COUNT(id) AS total FROM ' . DB::table( 'accounts' ) . ' WHERE status = \'error\' AND ( is_public = 1 OR user_id = %d ) AND blog_id=%d', [
					get_current_user_id(),
					Helper::getBlogId()
				] ), ARRAY_A );

				if ( $failed_accounts && $failed_accounts[ 'total' ] > 0 )
				{
					add_action( 'admin_notices', function () use ( $failed_accounts ) {
						$verb = (int) $failed_accounts[ 'total' ] >= 2 ? 'are' : 'is';

						echo '<div class="fsp-notification-container"><div class="fsp-notification"><div class="fsp-notification-info"><div class="fsp-notification-icon fsp-is-warning"></div><div class="fsp-notification-text"><div class="fsp-notification-status">' . 'FS Poster' . '</div><div class="fsp-notification-message">' . fsp__( 'There %s <b>%s</b> failed account(s).', [
								$verb,
								$failed_accounts[ 'total' ]
							], FALSE ) . '</div></div></div><div class="fsp-notification-buttons"><a class="fsp-button" href="' . admin_url() . 'admin.php?page=fs-poster-accounts">' . fsp__( 'REVIEW ACCOUNTS' ) . '</a><button class="fsp-button fsp-is-gray fsp-close-notification">' . fsp__( 'HIDE' ) . '</button></div></div></div>';
					} );
				}

				$failed_feeds = DB::DB()->get_row( DB::DB()->prepare( 'SELECT COUNT(id) AS total FROM ' . DB::table( 'feeds' ) . ' tb1 WHERE blog_id=' . Helper::getBlogId() . ' AND is_sended = 1 AND ( ( node_type=\'account\' AND ( SELECT COUNT(0) FROM ' . DB::table( 'accounts' ) . ' tb2 WHERE tb2.id = tb1.node_id AND ( tb2.user_id = %d OR tb2.is_public=1) ) > 0 ) OR ( node_type <> \'account\' AND (SELECT COUNT(0) FROM ' . DB::table( 'account_nodes' ) . ' tb2 WHERE tb2.id = tb1.node_id AND ( tb2.user_id = %d ) > 0 OR tb2.is_public = 1 ) ) ) AND status = \'error\' AND is_seen = 0', get_current_user_id(), get_current_user_id() ), ARRAY_A );

				if ( $failed_feeds && $failed_feeds[ 'total' ] > 0 )
				{
					add_action( 'admin_notices', function () use ( $failed_feeds ) {
						$verb = (int) $failed_feeds[ 'total' ] >= 2 ? 'are' : 'is';

						echo '<div class="fsp-notification-container"><div class="fsp-notification"><div class="fsp-notification-info"><div class="fsp-notification-icon fsp-is-warning"></div><div class="fsp-notification-text"><div class="fsp-notification-status">' . 'FS Poster' . '</div><div class="fsp-notification-message">' . fsp__( 'There %s <b>%s</b> failed post(s).', [
								$verb,
								$failed_feeds[ 'total' ]
							], FALSE ) . '</div></div></div><div class="fsp-notification-buttons"><a class="fsp-button" href="' . admin_url() . 'admin.php?page=fs-poster-logs&filter_by=error">' . fsp__( 'GO TO THE LOGS' ) . '</a><button class="fsp-button fsp-is-gray fsp-close-notification" data-hide="true">' . fsp__( 'HIDE' ) . '</button></div></div></div>';
					} );
				}
			}

			$not_sended_feeds = DB::DB()->get_row( DB::DB()->prepare( 'SELECT COUNT(id) AS total FROM ' . DB::table( 'feeds' ) . ' tb1 WHERE blog_id=' . Helper::getBlogId() . ' AND is_sended = 0 AND ( ( node_type=\'account\' AND ( SELECT COUNT(0) FROM ' . DB::table( 'accounts' ) . ' tb2 WHERE tb2.id = tb1.node_id AND ( tb2.user_id = %d OR tb2.is_public=1) ) > 0 ) OR ( node_type <> \'account\' AND (SELECT COUNT(0) FROM ' . DB::table( 'account_nodes' ) . ' tb2 WHERE tb2.id = tb1.node_id AND ( tb2.user_id = %d ) > 0 OR tb2.is_public = 1 ) ) ) AND status IS NULL AND share_on_background = 0 AND send_time <= %s', get_current_user_id(), get_current_user_id(), Date::dateTimeSQL( 'now', '-1 minutes' ) ), ARRAY_A );

			if ( $not_sended_feeds && $not_sended_feeds[ 'total' ] > 0 )
			{
				add_action( 'admin_notices', function () use ( $not_sended_feeds ) {
					$verb = (int) $not_sended_feeds[ 'total' ] >= 2 ? 'are' : 'is';

					echo '<div class="fsp-notification-container"><div class="fsp-notification"><div class="fsp-notification-info"><div class="fsp-notification-icon fsp-is-warning"></div><div class="fsp-notification-text"><div class="fsp-notification-status">' . 'FS Poster' . '</div><div class="fsp-notification-message">' . fsp__( 'There' ) . ' ' . $verb . ' <b>' . $not_sended_feeds[ 'total' ] . '</b> ' . fsp__( 'feed(s) that require action.' ) . ' <i class="far fa-question-circle fsp-tooltip" data-title="' . fsp__( 'If you didn\'t select to share posts in the background when sharing posts, for various reasons like refreshing the page might interrupt the sharing process. That will cause some posts to remain unshared. You can share them in the background or with pop-up.' ) . '"></i></div></div></div><div class="fsp-notification-buttons"><button id="fspNotificationShareWithPopup" class="fsp-button">' . fsp__( 'SHARE WITH POP-UP' ) . '</button><button id="fspNotificationShareOnBackground" class="fsp-button">' . fsp__( 'SHARE IN THE BACKGROUND' ) . '</button><button id="fspNotificationDoNotShare" class="fsp-button fsp-is-gray fsp-close-notification" data-hide="true">' . fsp__( 'DON\'T SHARE' ) . '</button></div></div></div>';
				} );
			}
		} );
	}

	private function cleanFSPoster ( $now = FALSE )
	{
		if ( Helper::getOption( 'clean_fsposter', 0 ) == 1 && Helper::getOption( 'last_clean_time', '0' ) <= Date::epoch( 'now', '-1 week' ) || $now )
		{
			$users = get_users( [
				'blog_id'      => Helper::getBlogId(),
				'fields'       => 'ID',
				'role__not_in' => explode( '|', Helper::getOption( 'hide_menu_for', '' ) )
			] );

			if ( ! empty( $users ) )
			{
				$user_ids = implode( ',', $users );

				DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_access_tokens' ) . ' WHERE account_id IN ( SELECT id FROM ' . DB::table( 'accounts' ) . ' WHERE user_id NOT IN ( ' . $user_ids . ' ) )' );
				DB::DB()->query( 'DELETE FROM ' . DB::table( 'feeds' ) . ' WHERE node_id IN ( SELECT id FROM ' . DB::table( 'accounts' ) . ' WHERE user_id NOT IN ( ' . $user_ids . ' ) ) AND node_type = "account"' );
				DB::DB()->query( 'DELETE FROM ' . DB::table( 'feeds' ) . ' WHERE node_id IN ( SELECT id FROM ' . DB::table( 'account_nodes' ) . ' WHERE user_id NOT IN ( ' . $user_ids . ' ) ) AND node_type != "account"' );
				DB::DB()->query( 'DELETE FROM ' . DB::table( 'apps' ) . ' WHERE user_id NOT IN ( ' . $user_ids . ' ) AND user_id IS NOT NULL' );

				$tables = [
					'accounts',
					'account_nodes',
					'account_node_status',
					'account_status',
					'grouped_accounts',
					'schedules'
				];

				foreach ( $tables as $table )
				{
					DB::DB()->query( 'DELETE FROM ' . DB::table( $table ) . ' WHERE user_id NOT IN ( ' . $user_ids . ' ) ' );
				}
			}

			Helper::setOption( 'last_clean_time', Date::epoch() );
		}
	}
}
