<?php

namespace FSPoster\App\Providers;

use stdClass;
use WP_Error;
use Exception;

class FSCodeUpdater
{
	private $is_forced_update = FALSE;
	private $expiration       = 43200; // seconds
	private $plugin_slug;
	private $update_url;
	private $plugin_base;
	private $purchase_code;
	private $transient; // temporary cache for multiple calls

	public function __construct ( $plugin, $updateURL, $purchase_code )
	{
		$this->plugin_slug   = $plugin;
		$this->update_url    = $updateURL;
		$this->plugin_base   = $plugin . '/init.php';
		$this->purchase_code = $purchase_code;

		$this->check_if_forced_for_update();

		add_filter( 'plugins_api', [ $this, 'plugin_info' ], 20, 3 );
		add_filter( 'site_transient_update_plugins', [ $this, 'push_update' ] );
		add_action( 'upgrader_process_complete', [ $this, 'after_update' ], 10, 2 );
		add_filter( 'plugin_row_meta', [ $this, 'check_for_update' ], 10, 2 );
		add_filter( 'upgrader_pre_download', [ $this, 'block_expired_updates' ], 10, 3 );

		add_action( 'in_plugin_update_message-' . $this->plugin_slug . '/init.php', [
			$this,
			'plugin_update_message'
		], 10, 2 );
	}

	public function plugin_info ( $res, $action, $args )
	{
		if ( $action !== 'plugin_information' )
		{
			return FALSE;
		}

		if ( $args->slug !== $this->plugin_slug )
		{
			return FALSE;
		}

		$remote = $this->get_transient();

		if ( $remote )
		{
			$res = new stdClass();

			$res->name         = $remote->name;
			$res->slug         = $this->plugin_slug;
			$res->tested       = $remote->tested;
			$res->version      = $remote->version;
			$res->last_updated = $remote->last_updated;

			$res->author         = '<a href="https://www.fs-code.com">FS Code</a>';
			$res->author_profile = 'https://www.fs-code.com';

			$res->sections = [
				'description' => $remote->sections->description,
				'changelog'   => $remote->sections->changelog
			];

			return $res;
		}

		return FALSE;
	}

	public function push_update ( $transient )
	{
		if ( empty( $transient->checked ) )
		{
			return $transient;
		}

		$remote = $this->get_transient();

		if ( $remote && version_compare( Helper::getVersion(), $remote->version, '<' ) )
		{
			$res = new stdClass();

			$res->slug          = $this->plugin_slug;
			$res->plugin        = $this->plugin_base;
			$res->new_version   = $remote->version;
			$res->tested        = $remote->tested;
			$res->package       = isset( $remote->download_url ) ? $remote->download_url : '';
			$res->update_notice = isset( $remote->update_notice ) ? $remote->update_notice : '';
			$res->compatibility = new stdClass();

			$transient->response[ $res->plugin ] = $res;
		}

		return $transient;
	}

	public function after_update ( $upgrader_object, $options )
	{
		if ( $options[ 'action' ] === 'update' && $options[ 'type' ] === 'plugin' )
		{
			delete_transient( 'fscode_upgrade_' . $this->plugin_slug );
		}
	}

	public function check_for_update ( $links, $file )
	{
		if ( strpos( $file, $this->plugin_base ) !== FALSE )
		{
			$new_links = [
				'check_for_update' => '<a href="plugins.php?fscode_check_for_update=1&plugin=' . urlencode( $this->plugin_slug ) . '&_wpnonce=' . wp_create_nonce( 'fscode_check_for_update_' . $this->plugin_slug ) . '">Check for update</a>'
			];

			$links = array_merge( $links, $new_links );
		}

		return $links;
	}

	public function block_expired_updates ( $reply, $package, $extra_data )
	{
		if ( $reply !== FALSE )
		{
			return $reply;
		}

		if ( ! isset( $extra_data, $extra_data->skin, $extra_data->skin->plugin_info ) )
		{
			return FALSE;
		}

		if ( $extra_data->skin->plugin_info[ 'TextDomain' ] !== $this->plugin_slug )
		{
			return FALSE;
		}

		$remote = $this->get_transient();

		if ( ! $remote || empty( $remote->update_notice ) )
		{
			return FALSE;
		}

		$update_notice = '<div class="fsp-plugin-blocked-notice">' . $remote->update_notice . '</div>';

		return new WP_Error( $this->plugin_slug . '_subscription_expired', $update_notice );
	}

	public function plugin_update_message ( $plugin_data, $extra_data )
	{
		if ( empty( $extra_data->package ) && ! empty( $extra_data->update_notice ) )
		{
			echo '<div class="fsp-plugin-update-notice">' . $extra_data->update_notice . '</div>';
		}
	}

	/*
	 * First check if temporary cache is available, if it is, use it
	 * Second check long-live cache, and if it is in the expiration timeframe, use it
	 * If neither cache is available, then request to remote server and cache it
	 */
	private function get_transient ()
	{
		if ( isset( $this->transient ) )
		{
			return $this->transient;
		}

		try
		{
			$transient_cache = json_decode( Helper::getOption( 'transient_cache_' . $this->plugin_slug, FALSE, TRUE ), FALSE );

			if ( empty( $transient_cache ) )
			{
				throw new Exception();
			}

			$transient = $transient_cache->transient;
			$time      = $transient_cache->time;
		}
		catch ( Exception $e )
		{
			$transient = FALSE;
		}

		if ( ! $transient || Date::epoch() - $time > $this->expiration )
		{
			$remote = wp_remote_get( $this->update_url . '?act=check_update&domain=' . network_site_url() . '&purchase_code=' . $this->purchase_code . '&version=' . Helper::getVersion() . '&is_manual_check=' . strval( $this->is_forced_update ) );

			if ( ! is_wp_error( $remote ) && isset( $remote[ 'response' ][ 'code' ] ) && $remote[ 'response' ][ 'code' ] == 200 && ! empty( $remote[ 'body' ] ) )
			{
				$transient = json_decode( $remote[ 'body' ] );

				// long-live cache
				Helper::setOption( 'transient_cache_' . $this->plugin_slug, json_encode( [
					'time'      => Date::epoch(),
					'transient' => $transient
				] ), TRUE );
			}
			else
			{
				Helper::setOption( 'transient_cache', json_encode( [
					'time'      => Date::epoch(),
					'transient' => 1
				] ), TRUE );
			}
		}

		$this->transient = $transient;

		return $transient;
	}

	/*
	 * Set expiration limit to 1 minute if update check is forced
	 * There should be at lease 1 minute difference between two requests
	 */
	private function check_if_forced_for_update ()
	{
		$check_update = Request::get( 'fscode_check_for_update', '', 'string' );
		$plugin       = Request::get( 'plugin', '', 'string' );
		$_wpnonce     = Request::get( '_wpnonce', '', 'string' );

		if ( $check_update === '1' && $plugin === $this->plugin_slug && wp_verify_nonce( $_wpnonce, 'fscode_check_for_update_' . $this->plugin_slug ) )
		{
			$this->is_forced_update = TRUE;
			$this->expiration       = 60; // if forced set time limit 60 seconds
		}
	}
}
