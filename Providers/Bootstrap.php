<?php

namespace {

	function fsp__ ( $text = '', $binds = [], $esc_html = TRUE )
	{
		if ( ! empty( $binds ) && is_array( $binds ) )
		{
			$text = vsprintf( $text, $binds );
		}

		return $esc_html ? esc_html__( $text, 'fs-poster' ) : __( $text, 'fs-poster' );
	}
}

namespace FSPoster\App\Providers {

	/**
	 * Class Bootstrap
	 * @package FSPoster\App\Providers
	 */
	class Bootstrap
	{
		/**
		 * Bootstrap constructor.
		 */
		public function __construct ()
		{
			CronJob::init();

			$this->registerDefines();

			$this->loadPluginTextdomaion();
			$this->loadPluginLinks();
			$this->createCustomPostTypes();
			$this->createPostSaveEvent();

			if ( is_admin() )
			{
				new BackEnd();
			}
			else
			{
				new FrontEnd();
			}
		}

		private function registerDefines ()
		{
			define( 'FS_ROOT_DIR', dirname( dirname( __DIR__ ) ) );
			define( 'FS_API_URL', 'https://www.fs-poster.com/api/' );
			//			define( 'FS_POSTER_IS_DEMO', true ); // enable on demo
		}

		private function loadPluginLinks ()
		{
			add_filter( 'plugin_action_links_fs-poster/init.php', function ( $links ) {
				$newLinks = [
					'<a href="https://support.fs-code.com" target="_blank">' . fsp__( 'Support' ) . '</a>',
					'<a href="https://www.fs-poster.com/documentation/" target="_blank">' . fsp__( 'Documentation' ) . '</a>'
				];

				return array_merge( $newLinks, $links );
			} );
		}

		private function loadPluginTextdomaion ()
		{
			add_action( 'init', function () {
				load_plugin_textdomain( 'fs-poster', FALSE, 'fs-poster/languages' );
			} );
		}

		private function createCustomPostTypes ()
		{
			add_action( 'init', function () {
				register_post_type( 'fs_post', [
					'labels'      => [
						'name'          => fsp__( 'FS Posts' ),
						'singular_name' => fsp__( 'FS Post' )
					],
					'public'      => FALSE,
					'has_archive' => TRUE
				] );

				register_post_type( 'fs_post_tmp', [
					'labels'      => [
						'name'          => fsp__( 'FS Posts' ),
						'singular_name' => fsp__( 'FS Post' )
					],
					'public'      => FALSE,
					'has_archive' => TRUE
				] );
			} );
		}

		private function createPostSaveEvent ()
		{
			add_action( 'transition_post_status', [ 'FSPoster\App\Providers\ShareService', 'postSaveEvent' ], 10, 3 );
			add_action( 'delete_post', [ 'FSPoster\App\Providers\ShareService', 'deletePostFeeds' ], 10 );
		}
	}
}
