<?php

namespace FSPoster\App\Pages\Accounts\Controllers;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

class Action
{
	public function get_fb_accounts ()
	{
		$accounts_list  = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " WHERE account_id=tb1.id AND node_type='ownpage' AND (user_id=%d OR is_public=1)) ownpages,
		(SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " WHERE account_id=tb1.id AND node_type='group' AND (user_id=%d OR is_public=1)) `groups`,
		(SELECT filter_type FROM " . DB::table( 'account_status' ) . " WHERE account_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'account' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'accounts' ) . " tb1
	WHERE (user_id=%d OR is_public=1) AND driver='fb' AND blog_id=%d", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );
		$my_accounts_id = [ -1 ];

		foreach ( $accounts_list as $i => $account_info )
		{
			$my_accounts_id[] = (int) $account_info[ 'id' ];

			$accounts_list[ $i ][ 'node_list' ] = DB::DB()->get_results( DB::DB()->prepare( "
			SELECT 
				*,
				(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
				(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`,
				(SELECT name FROM " . DB::table( 'account_nodes' ) . " WHERE account_id=tb1.account_id AND node_id=tb1.poster_id AND user_id=%d) poster_name
			FROM " . DB::table( 'account_nodes' ) . " tb1
			WHERE (user_id=%d OR is_public=1) AND account_id=%d AND blog_id=%d", [
				get_current_user_id(),
				get_current_user_id(),
				get_current_user_id(),
				get_current_user_id(),
				$account_info[ 'id' ],
				Helper::getBlogId()
			] ), ARRAY_A );
		}

		$public_communities = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'account_nodes' ) . " tb1
	WHERE driver='fb' AND (user_id=%d OR is_public=1) AND blog_id=%d AND account_id NOT IN ('" . implode( "','", $my_accounts_id ) . "')", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		return [
			'accounts_list'      => $accounts_list,
			'public_communities' => $public_communities
		];
	}

	public function get_fb_pages ( $params = [] )
	{
		if ( ! ( isset( $params[ 'account_id' ] ) && ! empty( $params[ 'account_id' ] && isset( $params[ 'group_id' ] ) && ! empty( $params[ 'group_id' ] ) ) ) )
		{
			return [];
		}

		$group_id = $params[ 'group_id' ];

		$get_group = DB::DB()->get_row( DB::DB()->prepare( "SELECT * FROM " . DB::table( 'account_nodes' ) . " WHERE id = %d AND node_type = 'group' AND ( user_id = %d OR is_public = 1 ) AND blog_id = %d", [
			$group_id,
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		if ( ! $get_group )
		{
			return [];
		}

		$poster_id = isset( $get_group[ 'poster_id' ] ) && $get_group[ 'poster_id' ] > 0 ? $get_group[ 'poster_id' ] : NULL;

		$account_id = $params[ 'account_id' ];
		$pages      = [];

		$get_account = DB::DB()->get_row( DB::DB()->prepare( 'SELECT * FROM ' . DB::table( 'accounts' ) . ' WHERE id = %d', [ $account_id ] ), ARRAY_A );

		$pages[] = [
			'id'       => '',
			'name'     => $get_account[ 'name' ],
			'selected' => is_null( $poster_id )
		];

		$get_pages = DB::DB()->get_results( DB::DB()->prepare( "SELECT * FROM " . DB::table( 'account_nodes' ) . " WHERE account_id = %d AND node_type = 'ownpage' AND ( user_id = %d OR is_public = 1 ) AND blog_id = %d", [
			$account_id,
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		foreach ( $get_pages as $page )
		{
			$pages[] = [
				'id'       => $page[ 'id' ],
				'name'     => $page[ 'name' ],
				'selected' => ! is_null( $poster_id ) && $page[ 'node_id' ] == $poster_id
			];
		}

		return $pages;
	}

	public function get_google_b_accounts ()
	{
		$accounts_list = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " WHERE account_id=tb1.id) locations,
		(SELECT filter_type FROM " . DB::table( 'account_status' ) . " WHERE account_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'account' AND user_id = %d) `is_hidden` 
	FROM " . DB::table( 'accounts' ) . " tb1 
	WHERE (user_id=%d OR is_public=1) AND driver='google_b' AND blog_id=%d", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		$my_accounts_id = [ -1 ];
		foreach ( $accounts_list as $i => $account_info )
		{
			$my_accounts_id[] = (int) $account_info[ 'id' ];

			$accounts_list[ $i ][ 'node_list' ] = DB::DB()->get_results( DB::DB()->prepare( "
			SELECT 
				*,
				(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
			FROM " . DB::table( 'account_nodes' ) . " tb1
			WHERE (user_id=%d OR is_public=1) AND account_id=%d AND blog_id=%d", [
				get_current_user_id(),
				get_current_user_id(),
				get_current_user_id(),
				$account_info[ 'id' ],
				Helper::getBlogId()
			] ), ARRAY_A );
		}

		$public_communities = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'account_nodes' ) . " tb1
	WHERE driver='google_b' AND (user_id=%d OR is_public=1) AND blog_id=%d AND account_id NOT IN ('" . implode( "','", $my_accounts_id ) . "')", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		return [
			'accounts_list'      => $accounts_list,
			'public_communities' => $public_communities
		];
	}

	public function get_blogger_accounts ()
	{
		$accounts_list = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
	 	*,
		(SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " WHERE account_id=tb1.id AND (user_id=%d OR is_public=1)) AS `blogs`,
		(SELECT filter_type FROM " . DB::table( 'account_status' ) . " WHERE account_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'account' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'accounts' ) . " tb1
	WHERE (user_id=%d OR is_public=1) AND driver='blogger' AND blog_id=%d", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		$my_accounts_id = [ -1 ];
		foreach ( $accounts_list as $i => $account_info )
		{
			$my_accounts_id[] = (int) $account_info[ 'id' ];

			$accounts_list[ $i ][ 'node_list' ] = DB::DB()->get_results( DB::DB()->prepare( "
			SELECT 
				*,
				(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
			FROM " . DB::table( 'account_nodes' ) . " tb1
			WHERE (user_id=%d OR is_public=1) AND blog_id=%d AND account_id=%d", [
				get_current_user_id(),
				get_current_user_id(),
				get_current_user_id(),
				Helper::getBlogId(),
				$account_info[ 'id' ]
			] ), ARRAY_A );
		}

		$public_communities = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'account_nodes' ) . " tb1
	WHERE driver='blogger' AND (user_id=%d OR is_public=1) AND blog_id=%d AND account_id NOT IN ('" . implode( "','", $my_accounts_id ) . "')", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		return [
			'accounts_list'      => $accounts_list,
			'public_communities' => $public_communities
		];
	}

	public function get_instagram_accounts ()
	{
		$accounts_list = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT filter_type FROM " . DB::table( 'account_status' ) . " WHERE account_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'account' AND user_id = %d) `is_hidden` 
	FROM " . DB::table( 'accounts' ) . " tb1 
	WHERE (user_id=%d OR is_public=1) AND driver='instagram' AND blog_id=%d", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		$accounts_with_cookie = DB::DB()->get_results( "SELECT * FROM " . DB::table( 'account_sessions' ) . " WHERE driver='instagram'", 'ARRAY_A' );
		$cookie_users         = [];

		foreach ( $accounts_with_cookie as $record )
		{
			$cookie_users[] = $record[ 'username' ];
		}

		foreach ( $accounts_list as $i => $account )
		{
			$accounts_list[ $i ][ 'has_cookie' ] = in_array( $account[ 'username' ], $cookie_users ) ? 1 : 0;
		}

		if ( version_compare( PHP_VERSION, '5.6.0' ) < 0 )
		{
			echo '<div >
				<div ><i class="fa fa-warning fa-exclamation-triangle fa-5x" ></i> </div>
				<div >For using instagram account, please update your PHP version 5.6 or higher!</div>
				<div>Your current PHP version is: ' . PHP_VERSION . '</div>
			</div>';

			return [];
		}

		$my_accounts_id = [-1];

		foreach ( $accounts_list as $i => $account_info )
		{
			$my_accounts_id[] = (int) $account_info[ 'id' ];

			$accounts_list[ $i ][ 'node_list' ] = DB::DB()->get_results( DB::DB()->prepare( "
			SELECT 
				*,
				(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
				(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`,
				(SELECT name FROM " . DB::table( 'account_nodes' ) . " WHERE account_id=tb1.account_id AND node_id=tb1.poster_id AND user_id=%d) poster_name
			FROM " . DB::table( 'account_nodes' ) . " tb1
			WHERE (user_id=%d OR is_public=1) AND account_id=%d AND blog_id=%d", [
				get_current_user_id(),
				get_current_user_id(),
				get_current_user_id(),
				get_current_user_id(),
				$account_info[ 'id' ],
				Helper::getBlogId()
			] ), ARRAY_A );
		}

		$public_communities = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'account_nodes' ) . " tb1
	WHERE driver='instagram' AND (user_id=%d OR is_public=1) AND blog_id=%d AND account_id NOT IN ('" . implode( "','", $my_accounts_id ) . "')", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		return [
			'accounts_list'      => $accounts_list,
			'public_communities' => $public_communities
		];
	}

	public function get_linkedin_accounts ()
	{
		$accounts_list = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
	 	*,
		(SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " WHERE account_id=tb1.id AND (user_id=%d OR is_public=1)) AS companies,
		(SELECT filter_type FROM " . DB::table( 'account_status' ) . " WHERE account_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'account' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'accounts' ) . " tb1
	WHERE (user_id=%d OR is_public=1) AND driver='linkedin' AND blog_id=%d", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		$my_accounts_id = [ -1 ];
		foreach ( $accounts_list as $i => $account_info )
		{
			$my_accounts_id[] = (int) $account_info[ 'id' ];

			$accounts_list[ $i ][ 'node_list' ] = DB::DB()->get_results( DB::DB()->prepare( "
			SELECT 
				*,
				(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
			FROM " . DB::table( 'account_nodes' ) . " tb1
			WHERE (user_id=%d OR is_public=1) AND account_id=%d AND blog_id=%d", [
				get_current_user_id(),
				get_current_user_id(),
				get_current_user_id(),
				$account_info[ 'id' ],
				Helper::getBlogId()
			] ), ARRAY_A );
		}

		$public_communities = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'account_nodes' ) . " tb1
	WHERE driver='linkedin' AND (user_id=%d OR is_public=1) AND blog_id=%d AND account_id NOT IN ('" . implode( "','", $my_accounts_id ) . "')", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		return [
			'accounts_list'      => $accounts_list,
			'public_communities' => $public_communities
		];
	}

	public function get_medium_accounts ()
	{
		$accounts_list = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " WHERE account_id=tb1.id AND (user_id=%d OR is_public=1)) publications,
		(SELECT filter_type FROM " . DB::table( 'account_status' ) . " WHERE account_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'account' AND user_id = %d) `is_hidden` 
	FROM " . DB::table( 'accounts' ) . " tb1 
	WHERE (user_id=%d OR is_public=1) AND driver='medium' AND blog_id=%d", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		$my_accounts_id = [ -1 ];
		foreach ( $accounts_list as $i => $account_info )
		{
			$my_accounts_id[] = (int) $account_info[ 'id' ];

			$accounts_list[ $i ][ 'node_list' ] = DB::DB()->get_results( DB::DB()->prepare( "
			SELECT 
				*,
				(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
			FROM " . DB::table( 'account_nodes' ) . " tb1
			WHERE (user_id=%d OR is_public=1) AND blog_id=%d AND account_id=%d", [
				get_current_user_id(),
				get_current_user_id(),
				get_current_user_id(),
				Helper::getBlogId(),
				$account_info[ 'id' ]
			] ), ARRAY_A );
		}

		$public_communities = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'account_nodes' ) . " tb1
	WHERE driver='medium' AND (user_id=%d OR is_public=1) AND blog_id=%d AND account_id NOT IN ('" . implode( "','", $my_accounts_id ) . "')", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		return [
			'accounts_list'      => $accounts_list,
			'public_communities' => $public_communities
		];
	}

	public function get_ok_accounts ()
	{
		$accounts_list = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
	 	*,
		(SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " WHERE account_id=tb1.id AND (user_id=%d OR is_public=1)) AS `groups`,
		(SELECT filter_type FROM " . DB::table( 'account_status' ) . " WHERE account_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'account' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'accounts' ) . " tb1
	WHERE (user_id=%d OR is_public=1) AND driver='ok' AND blog_id=%d", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		$my_accounts_id = [ -1 ];
		foreach ( $accounts_list as $i => $account_info )
		{
			$my_accounts_id[] = (int) $account_info[ 'id' ];

			$accounts_list[ $i ][ 'node_list' ] = DB::DB()->get_results( DB::DB()->prepare( "
			SELECT 
				*,
				(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
			FROM " . DB::table( 'account_nodes' ) . " tb1
			WHERE (user_id=%d OR is_public=1) AND blog_id=%d AND account_id=%d", [
				get_current_user_id(),
				get_current_user_id(),
				get_current_user_id(),
				Helper::getBlogId(),
				$account_info[ 'id' ]
			] ), ARRAY_A );
		}

		$public_communities = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'account_nodes' ) . " tb1
	WHERE driver='ok' AND (user_id=%d OR is_public=1) AND blog_id=%d AND account_id NOT IN ('" . implode( "','", $my_accounts_id ) . "')", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		return [
			'accounts_list'      => $accounts_list,
			'public_communities' => $public_communities
		];
	}

	public function get_pinterest_accounts ()
	{
		$accountsList = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " WHERE account_id=tb1.id AND (user_id=%d OR is_public=1)) boards,
		(SELECT filter_type FROM " . DB::table( 'account_status' ) . " WHERE account_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'account' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'accounts' ) . " tb1 
	WHERE (user_id=%d OR is_public=1) AND driver='pinterest' AND blog_id=%d", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		$accounts_with_cookie = DB::DB()->get_results( "SELECT * FROM " . DB::table( 'account_sessions' ) . " WHERE driver='pinterest'", 'ARRAY_A' );
		$cookie_users         = [];

		foreach ( $accounts_with_cookie as $record )
		{
			$cookie_users[] = $record[ 'username' ];
		}

		foreach ( $accountsList as $i => $account )
		{
			$accountsList[ $i ][ 'has_cookie' ] = in_array( $account[ 'username' ], $cookie_users ) ? 1 : 0;
		}

		$collectMyAccountIDs = [ -1 ];
		foreach ( $accountsList as $i => $accountInf1 )
		{
			$collectMyAccountIDs[]             = (int) $accountInf1[ 'id' ];
			$accountsList[ $i ][ 'node_list' ] = DB::DB()->get_results( DB::DB()->prepare( "
			SELECT 
				*,
				(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
			FROM " . DB::table( 'account_nodes' ) . " tb1
			WHERE (user_id=%d OR is_public=1) AND account_id=%d AND blog_id=%d", [
				get_current_user_id(),
				get_current_user_id(),
				get_current_user_id(),
				$accountInf1[ 'id' ],
				Helper::getBlogId()
			] ), ARRAY_A );
		}

		$publicCommunities = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'account_nodes' ) . " tb1
	WHERE driver='pinterest' AND (user_id=%d OR is_public=1) AND blog_id=%d AND account_id NOT IN ('" . implode( "','", $collectMyAccountIDs ) . "')", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		return [
			'accounts_list'      => $accountsList,
			'public_communities' => $publicCommunities
		];
	}

	public function get_reddit_accounts ()
	{
		$accounts_list = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " WHERE account_id=tb1.id AND (user_id=%d OR is_public=1)) subreddits,
		(SELECT filter_type FROM " . DB::table( 'account_status' ) . " WHERE account_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'account' AND user_id = %d) `is_hidden` 
	FROM " . DB::table( 'accounts' ) . " tb1 
	WHERE (user_id=%d OR is_public=1) AND driver='reddit' AND blog_id=%d", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		$my_accounts_id = [ -1 ];
		foreach ( $accounts_list as $i => $account_info )
		{
			$my_accounts_id[] = (int) $account_info[ 'id' ];

			$accounts_list[ $i ][ 'node_list' ] = DB::DB()->get_results( DB::DB()->prepare( "
			SELECT 
				*,
				(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
			FROM " . DB::table( 'account_nodes' ) . " tb1
			WHERE (user_id=%d OR is_public=1) AND account_id=%d AND blog_id=%d", [
				get_current_user_id(),
				get_current_user_id(),
				get_current_user_id(),
				$account_info[ 'id' ],
				Helper::getBlogId()
			] ), ARRAY_A );
		}

		$public_communities = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'account_nodes' ) . " tb1
	WHERE driver='reddit' AND (user_id=%d OR is_public=1) AND blog_id=%d AND account_id NOT IN ('" . implode( "','", $my_accounts_id ) . "')", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		return [
			'accounts_list'      => $accounts_list,
			'public_communities' => $public_communities
		];
	}

	public function get_telegram_accounts ()
	{
		$accounts_list = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " WHERE account_id=tb1.id) chats,
		(SELECT filter_type FROM " . DB::table( 'account_status' ) . " WHERE account_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'account' AND user_id = %d) `is_hidden` 
	FROM " . DB::table( 'accounts' ) . " tb1 
	WHERE (user_id=%d OR is_public=1) AND driver='telegram' AND blog_id=%d", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		$my_accounts_id = [ -1 ];
		foreach ( $accounts_list as $i => $account_info )
		{
			$my_accounts_id[] = (int) $account_info[ 'id' ];

			$accounts_list[ $i ][ 'node_list' ] = DB::DB()->get_results( DB::DB()->prepare( "
			SELECT 
				*,
				(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
			FROM " . DB::table( 'account_nodes' ) . " tb1
			WHERE (user_id=%d OR is_public=1) AND account_id=%d AND blog_id=%d", [
				get_current_user_id(),
				get_current_user_id(),
				get_current_user_id(),
				$account_info[ 'id' ],
				Helper::getBlogId()
			] ), ARRAY_A );
		}

		$public_communities = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'account_nodes' ) . " tb1
	WHERE driver='telegram' AND (user_id=%d OR is_public=1) AND blog_id=%d AND account_id NOT IN ('" . implode( "','", $my_accounts_id ) . "')", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		return [
			'accounts_list'      => $accounts_list,
			'public_communities' => $public_communities
		];
	}

	public function get_tumblr_accounts ()
	{
		$accounts_list = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " WHERE account_id=tb1.id) AS blogs,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'account' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'accounts' ) . " tb1 
	WHERE (user_id=%d OR is_public=1) AND driver='tumblr' AND blog_id=%d", [
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		$my_accounts_id = [ -1 ];
		foreach ( $accounts_list as $i => $account_info )
		{
			$my_accounts_id[] = (int) $account_info[ 'id' ];

			$accounts_list[ $i ][ 'node_list' ] = DB::DB()->get_results( DB::DB()->prepare( "
			SELECT 
				*,
				(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
			FROM " . DB::table( 'account_nodes' ) . " tb1
			WHERE (user_id=%d OR is_public=1) AND account_id=%d AND blog_id=%d", [
				get_current_user_id(),
				get_current_user_id(),
				get_current_user_id(),
				$account_info[ 'id' ],
				Helper::getBlogId()
			] ), ARRAY_A );
		}

		$public_communities = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'account_nodes' ) . " tb1
	WHERE driver='tumblr' AND (user_id=%d OR is_public=1) AND blog_id=%d AND account_id NOT IN ('" . implode( "','", $my_accounts_id ) . "')", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		return [
			'accounts_list'      => $accounts_list,
			'public_communities' => $public_communities
		];
	}

	public function get_twitter_accounts ()
	{
		$accounts_list = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT filter_type FROM " . DB::table( 'account_status' ) . " WHERE account_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'account' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'accounts' ) . " tb1 
	WHERE (user_id=%d OR is_public=1) AND driver='twitter' AND blog_id=%d", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		return [
			'accounts_list' => $accounts_list
		];
	}

	public function get_plurk_accounts ()
	{
		$accounts_list = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT filter_type FROM " . DB::table( 'account_status' ) . " WHERE account_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'account' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'accounts' ) . " tb1 
	WHERE (user_id=%d OR is_public=1) AND driver='plurk' AND blog_id=%d", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		return [
			'accounts_list' => $accounts_list
		];
	}

	public function get_vk_accounts ()
	{
		$accounts_list = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " WHERE account_id=tb1.id AND (user_id=%d OR is_public=1)) communities,
		(SELECT filter_type FROM " . DB::table( 'account_status' ) . " WHERE account_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'account' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'accounts' ) . " tb1 
	WHERE (user_id=%d OR is_public=1) AND driver='vk' AND `blog_id`=%d", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		$my_accounts_id = [ -1 ];
		foreach ( $accounts_list as $i => $account_info )
		{
			$my_accounts_id[] = (int) $account_info[ 'id' ];

			$accounts_list[ $i ][ 'node_list' ] = DB::DB()->get_results( DB::DB()->prepare( "
			SELECT 
				*,
				(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
			FROM " . DB::table( 'account_nodes' ) . " tb1
			WHERE (user_id=%d OR is_public=1) AND account_id=%d AND blog_id=%d", [
				get_current_user_id(),
				get_current_user_id(),
				get_current_user_id(),
				$account_info[ 'id' ],
				Helper::getBlogId()
			] ), ARRAY_A );
		}

		$public_communities = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT filter_type FROM " . DB::table( 'account_node_status' ) . " WHERE node_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'node' AND user_id = %d) `is_hidden`
	FROM " . DB::table( 'account_nodes' ) . " tb1
	WHERE driver='vk' AND (user_id=%d OR is_public=1) AND blog_id=%d AND account_id NOT IN ('" . implode( "','", $my_accounts_id ) . "')", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		return [
			'accounts_list'      => $accounts_list,
			'public_communities' => $public_communities
		];
	}

	public function get_wordpress_accounts ()
	{
		$accounts_list = DB::DB()->get_results( DB::DB()->prepare( "
	SELECT 
		*,
		(SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " WHERE account_id=tb1.id AND (user_id=%d OR is_public=1)) publications,
		(SELECT filter_type FROM " . DB::table( 'account_status' ) . " WHERE account_id=tb1.id AND user_id=%d) is_active,
		(SELECT COUNT(0) FROM " . DB::table( 'grouped_accounts' ) . " WHERE account_id = tb1.id AND account_type = 'account' AND user_id = %d) `is_hidden` 
	FROM " . DB::table( 'accounts' ) . " tb1 
	WHERE (user_id=%d OR is_public=1) AND driver='wordpress' AND blog_id=%d", [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		return [
			'accounts_list' => $accounts_list
		];
	}

	public function get_fb_apps ()
	{
		return [
			'applications' => DB::fetchAll( 'apps', [ 'driver' => 'fb' ] )
		];
	}

	public function get_instagram_apps ()
	{
		return [
			'applications' => DB::fetchAll( 'apps', [ 'driver' => 'instagram' ] )
		];
	}

	public function get_twitter_apps ()
	{
		return [
			'applications' => DB::fetchAll( 'apps', [ 'driver' => 'twitter' ] )
		];
	}

	public function get_plurk_apps ()
	{
		return [
			'applications' => DB::fetchAll( 'apps', [ 'driver' => 'plurk' ] )
		];
	}

	public function get_linkedin_apps ()
	{
		return [
			'applications' => DB::fetchAll( 'apps', [ 'driver' => 'linkedin' ] )
		];
	}

	public function get_ok_apps ()
	{
		return [
			'applications' => DB::fetchAll( 'apps', [ 'driver' => 'ok' ] )
		];
	}

	public function get_pinterest_apps ()
	{
		return [
			'applications' => DB::fetchAll( 'apps', [ 'driver' => 'pinterest' ] )
		];
	}

	public function get_reddit_apps ()
	{
		return [
			'applications' => DB::fetchAll( 'apps', [ 'driver' => 'reddit' ] )
		];
	}

	public function get_tumblr_apps ()
	{
		return [
			'applications' => DB::fetchAll( 'apps', [ 'driver' => 'tumblr' ] )
		];
	}

	public function get_vk_apps ()
	{
		return [
			'applications' => DB::fetchAll( 'apps', [ 'driver' => 'vk' ] )
		];
	}

	public function get_google_b_apps ()
	{
		return [
			'applications' => DB::fetchAll( 'apps', [ 'driver' => 'google_b' ] )
		];
	}

	public function get_blogger_apps ()
	{
		return [
			'applications' => DB::fetchAll( 'apps', [ 'driver' => 'blogger' ] )
		];
	}

	public function get_medium_apps ()
	{
		return [
			'applications' => DB::fetchAll( 'apps', [ 'driver' => 'medium' ] )
		];
	}

	public function get_subreddit_info ()
	{
		$accountId  = (int) Request::post( 'account_id', '0', 'num' );
		$userId     = get_current_user_id();
		$accountInf = DB::DB()->get_row( "SELECT * FROM " . DB::table( 'accounts' ) . " WHERE id='{$accountId}' AND driver='reddit' AND (user_id='{$userId}' OR is_public=1) AND blog_id='" . Helper::getBlogId() . "' ", ARRAY_A );

		return [
			'accountId'  => $accountId,
			'userId'     => $userId,
			'accountInf' => ! $accountInf ? '' : $accountInf
		];
	}

	public function get_counts ()
	{
		DB::DB()->query( 'DELETE FROM `' . DB::table( 'account_status' ) . '` WHERE (SELECT count(0) FROM `' . DB::table( 'accounts' ) . '` WHERE id=account_id)=0' );
		DB::DB()->query( 'DELETE FROM `' . DB::table( 'account_node_status' ) . '` WHERE (SELECT count(0) FROM `' . DB::table( 'account_nodes' ) . '` WHERE id=`' . DB::table( 'account_node_status' ) . '`.node_id)=0' );

		$accounts_list = DB::DB()->get_results( DB::DB()->prepare( "SELECT driver, COUNT(0) AS _count FROM " . DB::table( 'accounts' ) . " WHERE (user_id=%d OR is_public=1) AND blog_id=%d GROUP BY driver", [
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );
		$nodes_list    = DB::DB()->get_results( DB::DB()->prepare( 'SELECT driver, 1 AS _count FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id NOT IN ( SELECT id FROM ' . DB::table( 'accounts' ) . ' WHERE user_id = %d AND blog_id = %d ) AND is_public = 1 AND blog_id = %d GROUP BY driver', [
			get_current_user_id(),
			Helper::getBlogId(),
			Helper::getBlogId()
		] ), ARRAY_A );

		$fsp_accounts_count = [
			'total'     => 0,
			'fb'        => [
				'total'  => 0,
				'failed' => 0,
				'active' => 0
			],
			'twitter'   => [
				'total'  => 0,
				'failed' => 0,
				'active' => 0
			],
			'instagram' => [
				'total'  => 0,
				'failed' => 0,
				'active' => 0
			],
			'linkedin'  => [
				'total'  => 0,
				'failed' => 0,
				'active' => 0
			],
			'vk'        => [
				'total'  => 0,
				'failed' => 0,
				'active' => 0
			],
			'pinterest' => [
				'total'  => 0,
				'failed' => 0,
				'active' => 0
			],
			'reddit'    => [
				'total'  => 0,
				'failed' => 0,
				'active' => 0
			],
			'tumblr'    => [
				'total'  => 0,
				'failed' => 0,
				'active' => 0
			],
			'google_b'  => [
				'total'  => 0,
				'failed' => 0,
				'active' => 0
			],
			'blogger'   => [
				'total'  => 0,
				'failed' => 0,
				'active' => 0
			],
			'ok'        => [
				'total'  => 0,
				'failed' => 0,
				'active' => 0
			],
			'plurk'     => [
				'total'  => 0,
				'failed' => 0,
				'active' => 0
			],
			'telegram'  => [
				'total'  => 0,
				'failed' => 0,
				'active' => 0
			],
			'medium'    => [
				'total'  => 0,
				'failed' => 0,
				'active' => 0
			],
			'wordpress' => [
				'total'  => 0,
				'failed' => 0,
				'active' => 0
			]
		];

		foreach ( $accounts_list as $a_info )
		{
			if ( isset( $fsp_accounts_count[ $a_info[ 'driver' ] ] ) )
			{
				$fsp_accounts_count[ $a_info[ 'driver' ] ][ 'total' ] = $a_info[ '_count' ];
				$fsp_accounts_count[ 'total' ]                        += $a_info[ '_count' ];
			}
		}

		foreach ( $nodes_list as $node_info )
		{
			if ( isset( $fsp_accounts_count[ $node_info[ 'driver' ] ] ) )
			{
				$fsp_accounts_count[ $node_info[ 'driver' ] ][ 'total' ] += $node_info[ '_count' ];
				$fsp_accounts_count[ 'total' ]                           += $node_info[ '_count' ];
			}
		}

		$failed_accounts_list = DB::DB()->get_results( DB::DB()->prepare( "SELECT driver, COUNT(0) AS _count FROM " . DB::table( 'accounts' ) . " WHERE status = 'error' AND (user_id=%d OR is_public=1) AND blog_id=%d GROUP BY driver", [
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		foreach ( $failed_accounts_list as $a_info )
		{
			if ( isset( $fsp_accounts_count[ $a_info[ 'driver' ] ] ) )
			{
				$fsp_accounts_count[ $a_info[ 'driver' ] ][ 'failed' ] = $a_info[ '_count' ];
			}
		}

		$active_accounts = DB::DB()->get_results( DB::DB()->prepare( "SELECT `driver` FROM " . DB::table( 'accounts' ) . " WHERE ( `id` IN ( SELECT `account_id` FROM " . DB::table( 'account_status' ) . ") OR `id` IN ( SELECT `account_id` FROM " . DB::table( 'account_nodes' ) . " WHERE `id` IN ( SELECT `node_id` FROM " . DB::table( 'account_node_status' ) . " ) ) ) AND `user_id` = %d AND `blog_id` = %d GROUP BY `driver`", [
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		foreach ( $active_accounts as $a_info )
		{
			if ( isset( $fsp_accounts_count[ $a_info[ 'driver' ] ] ) )
			{
				$fsp_accounts_count[ $a_info[ 'driver' ] ][ 'active' ] = 1;
			}
		}

		return $fsp_accounts_count;
	}

	public function get_groups ()
	{
		DB::DB()->query( 'DELETE FROM `' . DB::table( 'account_status' ) . '` WHERE (SELECT count(0) FROM `' . DB::table( 'accounts' ) . '` WHERE id=account_id)=0' );
		DB::DB()->query( 'DELETE FROM `' . DB::table( 'account_node_status' ) . '` WHERE (SELECT count(0) FROM `' . DB::table( 'account_nodes' ) . '` WHERE id=`' . DB::table( 'account_node_status' ) . '`.node_id)=0' );

		$accounts_table       = DB::table( 'accounts' );
		$account_status_table = DB::table( 'account_status' );
		$nodes_table          = DB::table( 'account_nodes' );
		$node_status_table    = DB::table( 'account_node_status' );
		$groups_table         = DB::table( 'account_groups' );
		$groups_data_table    = DB::table( 'account_groups_data' );

		$sql = "
			SELECT 
			       gt.*,
			       (SELECT COUNT(0) FROM `$groups_data_table` gdt WHERE gdt.group_id=gt.id) AS total,
			       (SELECT COUNT(0) FROM `$accounts_table` acct WHERE acct.id IN (SELECT gdt.node_id FROM `$groups_data_table` gdt WHERE gdt.group_id=gt.id AND gdt.node_type='account') AND acct.status='error') AS failed,
			       (SELECT COUNT(0) FROM `$account_status_table` ast WHERE ast.user_id=gt.user_id AND ast.account_id IN (SELECT gdt.node_id FROM `$groups_data_table` gdt WHERE gdt.group_id=gt.id AND gdt.node_type='account')) active_a,
			       (SELECT COUNT(0) FROM `$node_status_table` nst WHERE nst.user_id=gt.user_id AND nst.node_id IN (SELECT gdt.node_id FROM `$groups_data_table` gdt WHERE gdt.group_id=gt.id AND gdt.node_type='node')) active_n
			FROM `$groups_table` gt
			WHERE gt.user_id=%d AND gt.blog_id=%d 
			ORDER BY gt.name
		";

		$groups = DB::DB()->get_results( DB::DB()->prepare( $sql, [
			get_current_user_id(),
			Helper::getBlogId()
		] ), 'ARRAY_A' );

		if ( $groups )
		{
			return $groups;
		}
		else
		{
			return [];
		}
	}

	public function get_node_groups ()
	{
		$node_id           = Request::post( 'node_id', '', 'num' );
		$node_type         = Request::post( 'node_type', 'account', 'string', [ 'account', 'node' ] );
		$groups_table      = DB::table( 'account_groups' );
		$groups_data_table = DB::table( 'account_groups_data' );

		$groups = DB::DB()->get_results(
			DB::DB()->prepare(
				"
				SELECT gt.id, gt.name FROM `$groups_table` gt 
				WHERE gt.id IN (SELECT gdt.group_id FROM `$groups_data_table` gdt WHERE gdt.node_type=%s AND gdt.node_id=%d)
				 AND gt.blog_id=%d AND gt.user_id=%d
				", [
					$node_type,
					$node_id,
					Helper::getBlogId(),
					get_current_user_id()
				]
			),
			'ARRAY_A'
		);

		return [
			'id'        => $node_id,
			'node_type' => $node_type,
			'groups'    => isset( $groups ) ? $groups : []
		];
	}

	public function get_nodes ( $groupId )
	{
		$accountsList = DB::DB()->get_results( DB::DB()->prepare( '
					SELECT tb2.*, \'account\' AS node_type FROM ' . DB::table( 'accounts' ) . ' tb2
					WHERE (tb2.user_id=%d OR tb2.is_public=1) AND tb2.blog_id=%d 
					AND tb2.id NOT IN (SELECT node_id FROM ' . DB::table( 'account_groups_data' ) . ' WHERE node_type=\'account\' AND group_id=%d)
					ORDER BY name', [
			get_current_user_id(),
			Helper::getBlogId(),
			$groupId
		] ), ARRAY_A );

		$pagesList = DB::DB()->get_results( DB::DB()->prepare( '
				SELECT tb2.* FROM ' . DB::table( 'account_nodes' ) . ' tb2
				WHERE (tb2.user_id=%d OR tb2.is_public=1) AND tb2.blog_id=%d 
				AND tb2.id NOT IN (SELECT node_id FROM ' . DB::table( 'account_groups_data' ) . ' WHERE node_type=\'node\' AND group_id=%d)
				ORDER BY (CASE node_type WHEN \'ownpage\' THEN 1 WHEN \'group\' THEN 2 WHEN \'page\' THEN 3 END), name', [
			get_current_user_id(),
			Helper::getBlogId(),
			$groupId
		] ), ARRAY_A );

		$nodesAll = array_merge( $accountsList, $pagesList );

		$nodesAllByKey  = [];
		$nodesAllSorted = [ '-' => [] ];

		foreach ( $nodesAll as $nodeInfo )
		{
			$nodesAllByKey[ $nodeInfo[ 'node_type' ] . ':' . (int) $nodeInfo[ 'id' ] ] = $nodeInfo;
		}

		foreach ( $nodesAll as $nodeInfo2 )
		{
			if ( isset( $nodeInfo2[ 'account_id' ] ) && isset( $nodesAllByKey[ 'account:' . $nodeInfo2[ 'account_id' ] ] ) )
			{
				$nodesAllSorted[ 'account:' . $nodeInfo2[ 'account_id' ] ][] = $nodeInfo2[ 'node_type' ] . ':' . (int) $nodeInfo2[ 'id' ];
			}
			else
			{
				$nodesAllSorted[ '-' ][] = $nodeInfo2[ 'node_type' ] . ':' . (int) $nodeInfo2[ 'id' ];
			}
		}

		function printNodeCart ( $node, $isSub = FALSE )
		{
			$val = esc_html( $node[ 'driver' ] . ':' . $node[ 'node_type' ] ) . ':' . (int) $node[ 'id' ];

			if ( in_array( $val, Request::post( 'dont_show', [], 'array' ) ) )
			{
				return '';
			}

			$isSub   = $isSub ? ' fsp-is-sub' : '';
			$snNames = [
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
			$driver  = $snNames[ $node[ 'driver' ] ];

			return '<div class="fsp-metabox-account' . $isSub . '" data-id="' . $val . '">
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

		$metaboxAccounts = '';

		foreach ( $nodesAllSorted[ '-' ] as $nodeKey )
		{
			$node = isset( $nodesAllByKey[ $nodeKey ] ) ? $nodesAllByKey[ $nodeKey ] : [];

			if ( empty( $node ) )
			{
				continue;
			}

			$metaboxAccounts .= printNodeCart( $node );

			if ( isset( $nodesAllSorted[ $nodeKey ] ) )
			{
				foreach ( $nodesAllSorted[ $nodeKey ] as $nodeSubKey )
				{
					$subNode = isset( $nodesAllByKey[ $nodeSubKey ] ) ? $nodesAllByKey[ $nodeSubKey ] : [];

					if ( empty( $subNode ) )
					{
						continue;
					}

					$metaboxAccounts .= printNodeCart( $subNode, TRUE );
				}
			}
		}

		return $metaboxAccounts;
	}

	public static function get_group_nodes ( $group_id )
	{
		$accounts_table         = DB::table( 'accounts' );
		$account_status_table   = DB::table( 'account_status' );
		$grouped_accounts_table = DB::table( 'grouped_accounts' );
		$nodes_table            = DB::table( 'account_nodes' );
		$node_status_table      = DB::table( 'account_node_status' );
		$groups_table           = DB::table( 'account_groups' );
		$groups_data_table      = DB::table( 'account_groups_data' );

		$sql_accounts = "
			SELECT acct.*,
			       (SELECT ast.filter_type FROM `$account_status_table` ast WHERE ast.account_id=acct.id AND ast.user_id=%d) is_active,
				   (SELECT COUNT(0) FROM `$grouped_accounts_table` gat WHERE gat.account_id = acct.id AND gat.account_type = 'account' AND gat.user_id = %d) is_hidden
			FROM `$accounts_table` acct 
			INNER JOIN `$groups_data_table` gdt 
			    ON acct.id=gdt.node_id
			WHERE gdt.node_type='account' 
			  AND (acct.user_id=%d OR acct.is_public=1)
			  AND gdt.group_id IN (SELECT gt.id FROM `$groups_table` gt WHERE gt.id=%d AND gt.user_id=%d AND gt.blog_id=%d)
			ORDER BY acct.id	
		";

		$accounts = DB::DB()->get_results( DB::DB()->prepare( $sql_accounts, [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			$group_id,
			get_current_user_id(),
			Helper::getBlogId()
		] ), 'ARRAY_A' );

		$sql_nodes = "
			SELECT nt.*,
			       (SELECT filter_type FROM `$node_status_table` nst WHERE nst.node_id=nt.id AND nst.user_id=%d) is_active,
				   (SELECT COUNT(0) FROM `$grouped_accounts_table` gat WHERE gat.account_id = nt.id AND gat.account_type = 'node' AND gat.user_id = %d) is_hidden
			FROM `$nodes_table` nt 
			INNER JOIN `$groups_data_table` gdt 
			    ON nt.id=gdt.node_id
			WHERE gdt.node_type='node' 
			  AND (nt.user_id=%d OR nt.is_public=1)
			  AND gdt.group_id IN (SELECT gt.id FROM `$groups_table` gt WHERE gt.id=%d AND gt.user_id=%d AND gt.blog_id=%d)
			ORDER BY nt.account_id, (CASE nt.node_type WHEN 'ownpage' THEN 1 WHEN 'group' THEN 2 WHEN 'page' THEN 3 END), nt.name
		";

		$nodes = DB::DB()->get_results( DB::DB()->prepare( $sql_nodes, [
			get_current_user_id(),
			get_current_user_id(),
			get_current_user_id(),
			$group_id,
			get_current_user_id(),
			Helper::getBlogId()
		] ), 'ARRAY_A' );

		$sn_names = [
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

		$i           = 0;
		$nodes_count = count( $nodes );

		$public_communities = [];

		foreach ( $accounts as &$account )
		{
			$account[ 'sn_name' ] = $sn_names[ $account[ 'driver' ] ];

			while ( $i < $nodes_count )
			{
				$nodes[ $i ][ 'sn_name' ] = $sn_names[ $nodes[ $i ][ 'driver' ] ];

				if ( $nodes[ $i ][ 'account_id' ] === $account[ 'id' ] )
				{
					$account[ 'node_list' ][] = $nodes[ $i ];
					$i++;
				}
				else if ( $nodes[ $i ][ 'account_id' ] < $account[ 'id' ] )
				{
					$public_communities[] = $nodes[ $i ];
					$i++;
				}
				else
				{
					break;
				}
			}
		}

		while ( $i < $nodes_count )
		{
			$nodes[ $i ][ 'sn_name' ] = $sn_names[ $nodes[ $i ][ 'driver' ] ];
			$public_communities[]     = $nodes[ $i ];
			$i++;
		}

		return [
			'accounts_list'      => $accounts,
			'public_communities' => $public_communities,
			'count'              => count( $accounts ) + $nodes_count
		];
	}

	public static function bulk_action_public ( $ids )
	{
		$account_ids = [];
		$node_ids    = [];

		foreach ( $ids as $id )
		{
			if ( $id[ 'type' ] === 'account' )
			{
				$account_ids[] = $id[ 'id' ];
			}
			else
			{
				$node_ids[] = $id[ 'id' ];
			}
		}

		foreach ( $account_ids as $account_id )
		{
			self::public_private_account( $account_id, 1 );
		}

		foreach ( $node_ids as $node_id )
		{
			self::public_private_node( $node_id, 1 );
		}

		return [ 'status' => TRUE ];
	}

	public function bulk_action_private ( $ids )
	{
		$account_ids = [];
		$node_ids    = [];

		foreach ( $ids as $id )
		{
			if ( $id[ 'type' ] === 'account' )
			{
				$account_ids[] = $id[ 'id' ];
			}
			else
			{
				$node_ids[] = $id[ 'id' ];
			}
		}

		foreach ( $account_ids as $account_id )
		{
			self::public_private_account( $account_id, 0 );
		}

		foreach ( $node_ids as $node_id )
		{
			self::public_private_node( $node_id, 0 );
		}

		return [ 'status' => TRUE ];
	}

	public function bulk_action_activate ( $ids, $for_all = FALSE )
	{
		$account_ids = [];
		$node_ids    = [];

		foreach ( $ids as $id )
		{
			if ( $id[ 'type' ] === 'account' )
			{
				$account_ids[] = $id[ 'id' ];
			}
			else
			{
				$node_ids[] = $id[ 'id' ];
			}
		}

		foreach ( $account_ids as $account_id )
		{
			self::activate_deactivate_account( get_current_user_id(), $account_id, 1, 'no', NULL, $for_all );
		}

		foreach ( $node_ids as $node_id )
		{
			self::activate_deactivate_node( get_current_user_id(), $node_id, 1, 'no', NULL, $for_all );
		}

		return [ 'status' => TRUE ];
	}

	public function bulk_action_activate_all ( $ids )
	{
		self::bulk_action_public( $ids );

		return $this->bulk_action_activate( $ids, TRUE );
	}

	public static function bulk_action_activate_condition ( $ids, $filter_type, $categories_arr, $for_all = FALSE )
	{
		$account_ids = [];
		$node_ids    = [];

		foreach ( $ids as $id )
		{
			if ( $id[ 'type' ] === 'account' )
			{
				$account_ids[] = $id[ 'id' ];
			}
			else
			{
				$node_ids[] = $id[ 'id' ];
			}
		}

		foreach ( $account_ids as $account_id )
		{
			self::activate_deactivate_account( get_current_user_id(), $account_id, 1, $filter_type, $categories_arr, $for_all );
		}

		foreach ( $node_ids as $node_id )
		{
			self::activate_deactivate_node( get_current_user_id(), $node_id, 1, $filter_type, $categories_arr, $for_all );
		}

		if ( $for_all )
		{
			self::bulk_action_public( $ids );
		}

		return [ 'status' => TRUE ];
	}

	public function bulk_action_deactivate ( $ids, $for_all = FALSE )
	{
		$account_ids = [];
		$node_ids    = [];

		foreach ( $ids as $id )
		{
			if ( $id[ 'type' ] === 'account' )
			{
				$account_ids[] = $id[ 'id' ];
			}
			else
			{
				$node_ids[] = $id[ 'id' ];
			}
		}

		foreach ( $account_ids as $account_id )
		{
			self::activate_deactivate_account( get_current_user_id(), $account_id, 0, 'no', NULL, $for_all );
		}

		foreach ( $node_ids as $node_id )
		{
			self::activate_deactivate_node( get_current_user_id(), $node_id, 0, 'no', NULL, $for_all );
		}

		return [ 'status' => TRUE ];
	}

	public function bulk_action_deactivate_all ( $ids )
	{
		$this->bulk_action_private( $ids );

		return $this->bulk_action_deactivate( $ids, TRUE );
	}

	public function bulk_action_delete ( $ids )
	{
		$account_ids = [];
		$node_ids    = [];

		foreach ( $ids as $id )
		{
			if ( $id[ 'type' ] === 'account' )
			{
				$account_ids[] = $id[ 'id' ];
			}
			else
			{
				$node_ids[] = $id[ 'id' ];
			}
		}

		foreach ( $account_ids as $account_id )
		{
			self::delete_account( $account_id );
		}

		foreach ( $node_ids as $node_id )
		{
			self::delete_node( $node_id );
		}

		return [ 'status' => TRUE ];
	}

	public function bulk_action_hide ( $ids )
	{
		$account_ids = [];
		$node_ids    = [];

		foreach ( $ids as $id )
		{
			if ( $id[ 'type' ] === 'account' )
			{
				$account_ids[] = $id[ 'id' ];
			}
			else
			{
				$node_ids[] = $id[ 'id' ];
			}
		}

		foreach ( $account_ids as $account_id )
		{
			self::hide_unhide_account( $account_id, 1 );
		}

		foreach ( $node_ids as $node_id )
		{
			self::hide_unhide_node( $node_id, 1 );
		}

		return [ 'status' => TRUE ];
	}

	public function bulk_action_unhide ( $ids )
	{
		$account_ids = [];
		$node_ids    = [];

		foreach ( $ids as $id )
		{
			if ( $id[ 'type' ] === 'account' )
			{
				$account_ids[] = $id[ 'id' ];
			}
			else
			{
				$node_ids[] = $id[ 'id' ];
			}
		}

		foreach ( $account_ids as $account_id )
		{
			self::hide_unhide_account( $account_id, 0 );
		}

		foreach ( $node_ids as $node_id )
		{
			self::hide_unhide_node( $node_id, 0 );
		}

		return [ 'status' => TRUE ];
	}

	public static function delete_account ( $account_id )
	{
		$check_account = DB::fetch( 'accounts', $account_id );

		if ( ! $check_account )
		{
			return [ 'status' => FALSE, 'error_msg' => fsp__( 'The account isn\'t found!' ) ];
		}
		else if ( $check_account[ 'user_id' ] != get_current_user_id() )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'You don\'t have permission to remove the account! Only the account owner can remove it.' )
			];
		}

		DB::DB()->delete( DB::table( 'accounts' ), [ 'id' => $account_id ] );
		DB::DB()->delete( DB::table( 'account_status' ), [ 'account_id' => $account_id ] );
		DB::DB()->delete( DB::table( 'account_access_tokens' ), [ 'account_id' => $account_id ] );

		DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN ( SELECT id FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id = "' . $account_id . '")' );

		DB::DB()->delete( DB::table( 'account_nodes' ), [ 'account_id' => $account_id ] );

		DB::DB()->delete( DB::table( 'account_groups_data' ), [ 'node_type' => 'account', 'node_id' => $account_id ] );

		Helper::deleteCustomSettings( 'account', $account_id );

		if ( $check_account[ 'driver' ] === 'instagram' )
		{
			$checkIfUsernameExist = DB::fetch( 'accounts', [
				'blog_id'  => Helper::getBlogId(),
				'username' => $check_account[ 'username' ],
				'driver'   => $check_account[ 'driver' ]
			] );

			if ( ! $checkIfUsernameExist )
			{
				DB::DB()->delete( DB::table( 'account_sessions' ), [
					'driver'   => $check_account[ 'driver' ],
					'username' => $check_account[ 'username' ]
				] );
			}
		}
		else if ( $check_account[ 'driver' ] === 'pinterest' )
		{
			DB::DB()->delete( DB::table( 'account_sessions' ), [
				'driver'   => 'pinterest',
				'username' => $check_account[ 'username' ]
			] );
		}

		return [ 'status' => TRUE ];
	}

	public static function delete_node ( $node_id )
	{
		$check_account = DB::fetch( 'account_nodes', $node_id );

		if ( ! $check_account )
		{
			return [ 'status' => FALSE, 'error_msg' => fsp__( 'The account isn\'t found!' ) ];
		}

		if ( $check_account[ 'user_id' ] != get_current_user_id() )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'You don\'t have permission to remove the account! Only the account owner can remove it.' )
			];
		}

		DB::DB()->delete( DB::table( 'account_nodes' ), [ 'id' => $node_id ] );
		DB::DB()->delete( DB::table( 'account_node_status' ), [ 'node_id' => $node_id ] );
		DB::DB()->delete( DB::table( 'account_groups_data' ), [ 'node_type' => 'node', 'node_id' => $node_id ] );

		Helper::deleteCustomSettings( 'node', $node_id );

		return [ 'status' => TRUE ];
	}

	public static function public_private_account ( $account_id, $checked )
	{
		$check_account = DB::fetch( 'accounts', $account_id );

		if ( ! $check_account )
		{
			return [ 'status' => FALSE, 'error_msg' => fsp__( 'The account isn\'t found!' ) ];
		}

		if ( $check_account[ 'user_id' ] != get_current_user_id() )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'Only the account owner can make it public or private!' )
			];
		}

		if ( $check_account[ 'status' ] === 'error' && $checked )
		{
			return [ 'status' => FALSE, 'error_msg' => fsp__( 'Failed accounts can\'t be public!' ) ];
		}

		DB::DB()->update( DB::table( 'accounts' ), [
			'is_public' => $checked
		], [
			'id'      => $account_id,
			'user_id' => get_current_user_id()
		] );

		return [ 'status' => TRUE ];
	}

	public static function public_private_node ( $node_id, $checked )
	{
		$check_node = DB::fetch( 'account_nodes', $node_id );

		if ( ! $check_node )
		{
			return [ 'status' => FALSE, 'error_msg' => fsp__( 'The account isn\'t found!' ) ];
		}

		if ( $check_node[ 'user_id' ] != get_current_user_id() )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'Only the account owner can make it public or private!' )
			];
		}

		DB::DB()->update( DB::table( 'account_nodes' ), [ 'is_public' => $checked ], [ 'id' => $node_id ] );

		return [ 'status' => TRUE ];
	}

	public static function activate_deactivate_account ( $user_id, $account_id, $checked, $filter_type = 'no', $categories_arr = NULL, $for_all = FALSE )
	{
		$check_account = DB::fetch( 'accounts', $account_id );

		if ( ! $check_account )
		{
			return [ 'status' => FALSE, 'error_msg' => fsp__( 'The account isn\'t found!' ) ];
		}

		if ( $check_account[ 'user_id' ] != $user_id && $check_account[ 'is_public' ] != 1 )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'You haven\'t sufficient permissions!' )
			];
		}

		if ( $checked )
		{
			if ( $check_account[ 'status' ] === 'error' )
			{
				return [ 'status' => FALSE, 'error_msg' => fsp__( 'Failed accounts can\'t be activated!' ) ];
			}

			if ( $for_all )
			{
				DB::DB()->delete( DB::table( 'account_status' ), [ 'account_id' => $account_id ] );

                DB::DB()->update( DB::table( 'accounts' ), [
                    'for_all' => 1,
                    'is_public' => 1
                ], [
                    'id' => $account_id
                ] );

				$offset = 0;
				$number = 400;

				while ( $users = get_users( [
					'role__not_in' => explode( '|', Helper::getOption( 'hide_menu_for', '' ) ),
					'fields'       => 'ID',
					'offset'       => $offset,
					'number'       => $number
				] ) )
				{
					$rows = [];

					while ( $uid = array_splice( $users, 0, 1 ) )
					{
						$rows[] = [
							'account_id'  => $account_id,
							'user_id'     => $uid[ 0 ],
							'filter_type' => $filter_type,
							'categories'  => $categories_arr
						];
					}

					DB::insertAll( 'account_status', [ 'account_id', 'user_id', 'filter_type', 'categories' ], $rows );

					$offset += $number;
				}
			}
			else
			{
				$check_is_active = DB::fetch( 'account_status', [
					'account_id' => $account_id,
					'user_id'    => $user_id,
				] );

				if ( ! $check_is_active )
				{
					DB::DB()->insert( DB::table( 'account_status' ), [
						'account_id'  => $account_id,
						'user_id'     => $user_id,
						'filter_type' => $filter_type,
						'categories'  => $categories_arr
					] );
				}
				else
				{
					DB::DB()->update( DB::table( 'account_status' ), [
						'filter_type' => $filter_type,
						'categories'  => $categories_arr
					], [ 'id' => $check_is_active[ 'id' ] ] );
				}
			}
		}
		else
		{
			if ( $for_all )
			{
                DB::DB()->update( DB::table( 'accounts' ), [
                    'for_all' => 0,
                    'is_public' => 0
                ], [
                    'id' => $account_id
                ] );

				$sql = [
					'account_id' => $account_id
				];
			}
			else
			{
				$sql = [
					'account_id' => $account_id,
					'user_id'    => $user_id
				];
			}

			DB::DB()->delete( DB::table( 'account_status' ), $sql );
		}

		return [ 'status' => TRUE ];
	}

	public static function activate_deactivate_node ( $user_id, $node_id, $checked, $filter_type = 'no', $categories_arr = NULL, $for_all = FALSE )
	{
		$check_account = DB::fetch( 'account_nodes', $node_id );

		if ( ! $check_account )
		{
			return [ 'status' => FALSE, 'error_msg' => fsp__( 'The account isn\'t found!' ) ];
		}

		if ( $check_account[ 'user_id' ] != $user_id && $check_account[ 'is_public' ] != 1 )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'You haven\'t sufficient permissions!' )
			];
		}

		if ( $checked )
		{
			$check_account_parent = DB::fetch( 'accounts', $check_account[ 'account_id' ] );

			if ( $check_account_parent[ 'status' ] === 'error' )
			{
				return [
					'status'    => FALSE,
					'error_msg' => fsp__( 'Failed accounts and their communities can\'t be activated!' )
				];
			}

			if ( $for_all )
			{
				DB::DB()->delete( DB::table( 'account_node_status' ), [ 'node_id' => $node_id ] );

				$offset = 0;
				$number = 400;

                DB::DB()->update( DB::table( 'account_nodes' ), [
                    'for_all' => 1,
                    'is_public' => 1
                ], [
                    'id' => $node_id
                ] );

				while ( $users = get_users( [
					'role__not_in' => explode( '|', Helper::getOption( 'hide_menu_for', '' ) ),
					'fields'       => 'ID',
					'offset'       => $offset,
					'number'       => $number
				] ) )
				{
					$rows = [];

					while ( $uid = array_splice( $users, 0, 1 ) )
					{
						$rows[] = [
							'node_id'     => $node_id,
							'user_id'     => $uid[ 0 ],
							'filter_type' => $filter_type,
							'categories'  => $categories_arr
						];
					}

					DB::insertAll( 'account_node_status', [
						'node_id',
						'user_id',
						'filter_type',
						'categories'
					], $rows );

					$offset += $number;
				}
			}
			else
			{
				$check_is_active = DB::fetch( 'account_node_status', [
					'node_id' => $node_id,
					'user_id' => $user_id
				] );

				if ( ! $check_is_active )
				{
					DB::DB()->insert( DB::table( 'account_node_status' ), [
						'node_id'     => $node_id,
						'user_id'     => $user_id,
						'filter_type' => $filter_type,
						'categories'  => $categories_arr
					] );
				}
				else
				{
					DB::DB()->update( DB::table( 'account_node_status' ), [
						'filter_type' => $filter_type,
						'categories'  => $categories_arr
					], [ 'id' => $check_is_active[ 'id' ] ] );
				}
			}
		}
		else
		{
			if ( $for_all )
			{
				$sql = [
					'node_id' => $node_id
				];

                DB::DB()->update( DB::table( 'account_nodes' ), [
                    'for_all' => 0,
                    'is_public' => 0
                ], [
                    'id' => $node_id
                ] );
			}
			else
			{
				$sql = [
					'node_id' => $node_id,
					'user_id' => $user_id
				];
			}

			DB::DB()->delete( DB::table( 'account_node_status' ), $sql );
		}

		return [ 'status' => TRUE ];
	}

	public static function hide_unhide_account ( $account_id, $checked )
	{
		$check_account = DB::fetch( 'accounts', $account_id );

		if ( ! $check_account )
		{
			return [ 'status' => FALSE, 'error_msg' => fsp__( 'The account isn\'t found!' ) ];
		}

		if ( $check_account[ 'user_id' ] != get_current_user_id() && $check_account[ 'is_public' ] != 1 )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'You haven\'t sufficient permissions!' )
			];
		}

		$get_visibility = DB::fetch( 'grouped_accounts', [
			'account_id'   => $account_id,
			'account_type' => 'account',
			'user_id'      => get_current_user_id()
		] );

		if ( ! $get_visibility && $checked )
		{
			DB::DB()->insert( DB::table( 'grouped_accounts' ), [
				'account_id'   => $account_id,
				'account_type' => 'account',
				'user_id'      => get_current_user_id()
			] );
		}
		else if ( $get_visibility && ! $checked )
		{
			DB::DB()->delete( DB::table( 'grouped_accounts' ), [
				'account_id'   => $account_id,
				'account_type' => 'account',
				'user_id'      => get_current_user_id()
			] );
		}

		return [ 'status' => TRUE ];
	}

	public static function hide_unhide_node ( $node_id, $checked )
	{
		$check_account = DB::fetch( 'account_nodes', $node_id );

		if ( ! $check_account )
		{
			return [ 'status' => FALSE, 'error_msg' => fsp__( 'The account isn\'t found!' ) ];
		}

		if ( $check_account[ 'user_id' ] != get_current_user_id() && $check_account[ 'is_public' ] != 1 )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'You haven\'t sufficient permissions!' )
			];
		}

		$get_visibility = DB::fetch( 'grouped_accounts', [
			'account_id'   => $node_id,
			'account_type' => 'node',
			'user_id'      => get_current_user_id()
		] );

		if ( ! $get_visibility && $checked )
		{
			DB::DB()->insert( DB::table( 'grouped_accounts' ), [
				'account_id'   => $node_id,
				'account_type' => 'node',
				'user_id'      => get_current_user_id()
			] );
		}
		else if ( $get_visibility && ! $checked )
		{
			DB::DB()->delete( DB::table( 'grouped_accounts' ), [
				'account_id'   => $node_id,
				'account_type' => 'node',
				'user_id'      => get_current_user_id()
			] );
		}

		return [ 'status' => TRUE ];
	}

	public static function getNodeCustomPostingTypeSettings ( $nodeDriver )
	{
		if ( empty( $nodeDriver ) )
		{
			return FALSE;
		}

		$title     = fsp__( 'Posting type' );
		$options   = [
			'1' => fsp__( 'Link card view' ),
			'4' => fsp__( 'Only custom message' ),
			'2' => fsp__( 'Featured image' ),
			'3' => fsp__( 'All post images' )
		];
		$nodesData = [
			'fb'        => [
				'title'     => $title,
				'text'    => fsp__( 'Link card view – shares the post link and the custom message. Facebook fetches the post as a link card view. <a href=\'https://www.fs-poster.com/documentation/commonly-encountered-issues#issue9\' target=\'_blank\'>Debug your website</a>;<br>Only custom message – shares the custom message without any image or link;<br>Featured image - Shares the featured image and the custom message;<br>All post images - Shares all post images as an album and the custom message.', [], FALSE ),
				'options' => $options
			],
			'twitter'   => [
				'title'   => $title,
				'text'    => fsp__( 'Link card view – shares the post link and the custom message. Twitter fetches the post as a link card view. <a href=\'https://www.fs-poster.com/documentation/commonly-encountered-issues#issue9\' target=\'_blank\'>Debug your website</a>;<br>Only custom message – shares the custom message without any image or link;<br>Featured image - Shares the featured image and the custom message;<br>All post images - Shares all post images as an album and the custom message.', [], FALSE ),
				'options' => $options
			],
			'linkedin'  => [
				'title'   => $title,
				'text'    => fsp__( 'Link card view – shares the post link and the custom message. Linkedin fetches the post as a link card view. <a href=\'https://www.fs-poster.com/documentation/commonly-encountered-issues#issue9\' target=\'_blank\'>Debug your website</a>;<br>Only custom message – shares the custom message without any image or link;<br>Featured image - Shares the featured image and the custom message;<br>All post images - Shares all post images as an album and the custom message.', [], FALSE ),
				'options' => $options
			],
			'vk'        => [
				'title'   => $title,
				'text'    => fsp__( 'Link card view – shares the post link and the custom message. Vk fetches the post as a link card view;<br>Only custom message – shares the custom message without any image or link;<br>Featured image - Shares the featured image and the custom message;<br>All post images - Shares all post images as an album and the custom message.', [], FALSE ),
				'options' => $options
			],
			'tumblr'    => [
				'title'   => $title,
				'text'    => fsp__( 'Link card view – shares the post link and the custom message. Tumblr fetches the post as a link card view;<br>Only custom message – shares the custom message without any image or link;<br>Featured image - Shares the featured image and the custom message;<br>All post images - Shares all post images as an album and the custom message;<br>Quote - Shares the custom message as a quote. Note that post images will not be shared inside the quote.', [], FALSE ),
				'options' => [
					'1' => fsp__( 'Link card view' ),
					'4' => fsp__( 'Only custom message' ),
					'2' => fsp__( 'Featured image' ),
					'3' => fsp__( 'All post images' ),
					'5' => fsp__( 'Quote' )
				]
			],
			'reddit'    => [
				'title'   => $title,
				'text'    => fsp__( 'Link card view – share the post link with preview and up to 300 characters custom message as title;<br>Title + text - share the post title and the custom message as the text of the Reddit post;<br>Custom message + featured image - share up to 300 characters custom message and the image.', [], FALSE ),
				'options' => [
					'1' => fsp__( 'Link card view' ),
					'2' => fsp__( 'Title + text' ),
					'3' => fsp__( 'Custom message + image' )
				]
			],
			'ok'        => [
				'title'   => $title,
				'text'    => fsp__( 'Link card view – shares the post link and the custom message. Odnoklassniki fetches the post as a link card view;<br>Only custom message – shares the custom message without any image or link;<br>Featured image - Shares the featured image and the custom message;<br>All post images - Shares all post images as an album and the custom message.', [], FALSE ),
				'options' => $options
			],
			'plurk'     => [
				'title'   => $title,
				'text'    => fsp__( 'Only custom message – shares only the custom message;<br>Custom message and link – shares the custom message and the link;<br>Custom message and featured image - Shares the custom message and the featured image link. Plurk fetches the featured image from the link;<br>Custom message and all images - Shares the custom message and all post image links. Plurk fetches the post images from the links.', [], FALSE ),
				'options' => [
					'1' => fsp__( 'Only custom message' ),
					'2' => fsp__( 'Custom message and link' ),
					'3' => fsp__( 'Custom message and featured image' ),
					'4' => fsp__( 'Custom message and all images' ),
				]
			],
			'google_b'  => [
				'title'   => fsp__( 'Posting type(only the app method)' ),
				'text'    => fsp__( 'Link card view – shares the post link and the custom message;<br>Only custom message – shares the custom message without any image or link;<br>Featured image - Shares the featured image and the custom message;<br>All post images - Shares all post images as an album and the custom message.', [], FALSE ),
				'options' => $options
			],
			'telegram'  => [
				'title'   => fsp__( 'Select what to share on Telegram' ),
				'text'    => fsp__( 'Define what you need to share on Telegram as a message.' ),
				'options' => [
					'1' => fsp__( 'Custom message + Post Link' ),
					'2' => fsp__( 'Custom message' ),
					'3' => fsp__( 'Featured image + Custom message' ),
					'4' => fsp__( 'Featured image + Custom message + Post Link' )
				]
			],
			'blogger'   => [
				'text'    => fsp__( 'Share WP pages as Blogger pages' ),
				'subtext' => fsp__( 'WordPress pages will be shared as Blogger pages' )
			],
			'wordpress' => [
				'text'    => fsp__( 'The post type availability' ),
				'subtext' => fsp__( 'The post type will be shared as it is on the remote website. In the case, the post type is not available on the remote website, if the option is enabled, the post type will be "Post". If the option is disabled, the post will fail.' )
			]
		];

		if ( empty( $nodesData[ $nodeDriver ] ) )
		{
			return FALSE;
		}
		else
		{
			$nodesData[ $nodeDriver ][ 'driver' ] = $nodeDriver;
			$nodesData[ $nodeDriver ][ 'posting_type' ] = $nodeDriver === 'telegram' ? 'telegram_type_of_sharing' : ( $nodeDriver === 'fb' ? 'facebook_posting_type' : $nodeDriver . '_posting_type' );

			return $nodesData[ $nodeDriver ];
		}
	}

}
