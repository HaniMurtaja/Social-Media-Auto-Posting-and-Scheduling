<?php

namespace FSPoster\App\Pages\Schedules\Controllers;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

trait Ajax
{
	public function schedule_save ()
	{
		$id               = Request::post( 'id', '0', 'int' );
		$title            = Request::post( 'title', '', 'string' );
		$start_date       = Request::post( 'start_date', '', 'string' );
		$start_time       = Request::post( 'start_time', '', 'string' );
		$interval         = Request::post( 'interval', '0', 'num' );
		$share_time       = Request::post( 'share_time', '', 'string' );
		$post_type_filter = Request::post( 'post_type_filter', '', 'string' );

		$filter_posts_date_range_from = Request::post( 'filter_posts_date_range_from', '1000-00-00', 'string' );
		$filter_posts_date_range_to   = Request::post( 'filter_posts_date_range_to', '9999-12-31', 'string' );

		$filter_posts_date_range_from = ! empty( $filter_posts_date_range_from ) ? $filter_posts_date_range_from : '1000-00-00';
		$filter_posts_date_range_to   = ! empty( $filter_posts_date_range_to ) ? $filter_posts_date_range_to : '9999-12-31';

		$dont_post_out_of_stock_products = Request::post( 'dont_post_out_of_stock_products', '0', 'string', [
			'0',
			'1'
		] );
		$category_filter                 = Request::post( 'category_filter', '0', 'int' );
		$post_sort                       = Request::post( 'post_sort', 'random', 'string', [
			'random',
			'random2',
			'old_first',
			'new_first'
		] );
		$post_freq                       = Request::post( 'post_freq', 'once', 'string', [
			'once',
			'repeat'
		] );
		$post_ids_p                      = Request::post( 'post_ids', '', 'string' );
		$custom_messages                 = Request::post( 'custom_messages', '', 'string' );
		$accounts_list                   = Request::post( 'accounts_list', '', 'string' );
		$sleep_time_start                = Request::post( 'sleep_time_start', '', 'string' );
		$sleep_time_end                  = Request::post( 'sleep_time_end', '', 'string' );

		if ( ! ( $interval > 0 && $interval <= 1440000 ) )
		{
			Helper::response( FALSE, fsp__( 'Interval is not correct!' ) );
		}

		if ( $id > 0 )
		{
			$schedule_info = DB::fetch( 'schedules', $id );

			if ( ! $schedule_info )
			{
				Helper::response( FALSE );
			}
		}

		$post_ids   = [];
		$post_ids_p = explode( ',', str_replace( ' ', '', $post_ids_p ) );

		foreach ( $post_ids_p as $post_id )
		{
			if ( is_numeric( $post_id ) && $post_id > 0 )
			{
				$post_ids[] = (int) $post_id;
			}
		}

		$post_ids_count = count( $post_ids );

		if ( $post_ids_count > 200 )
		{
			Helper::response( FALSE, fsp__( 'Too many posts are selected! You can select maximum 200 posts!' ) );
		}

		if ( $post_ids_count > 1 && $post_freq !== 'once' )
		{
			Helper::response( FALSE, fsp__( 'If you want to share repeatedly, you should schedule only a post!' ) );
		}

		if ( $post_freq === 'repeat' )
		{
			$post_sort = 'random';
		}

		$post_ids = implode( ',', $post_ids );
		$post_ids = empty( $post_ids ) ? NULL : $post_ids;

		if ( empty( $sleep_time_start ) || empty( $sleep_time_end ) )
		{
			$sleep_time_start = NULL;
			$sleep_time_end   = NULL;
		}
		else
		{
			$sleep_time_start = Date::timeSQL( $sleep_time_start );
			$sleep_time_end   = Date::timeSQL( $sleep_time_end );
		}

		$_custom_messages = [];

		if ( ! empty( $custom_messages ) )
		{
			$custom_messages = json_decode( $custom_messages, TRUE );
			$custom_messages = is_array( $custom_messages ) ? $custom_messages : [];

			foreach ( $custom_messages as $socialNetwork => $message1 )
			{
				if ( in_array( $socialNetwork, [
						'fb',
						'fb_h',
						'instagram',
						'instagram_h',
						'linkedin',
						'twitter',
						'pinterest',
						'vk',
						'ok',
						'tumblr',
						'reddit',
						'google_b',
						'blogger',
						'telegram',
						'medium',
						'wordpress',
						'plurk'
					] ) && is_string( $message1 ) )
				{
					$_custom_messages[ $socialNetwork ] = $message1;
				}
			}
		}

		$_custom_messages = empty( $_custom_messages ) ? NULL : json_encode( $_custom_messages );

		$_accounts_list = [];

		if ( ! empty( $accounts_list ) )
		{
			$accounts_list = json_decode( $accounts_list, TRUE );
			$accounts_list = Pages::action( 'Base', 'groups_to_nodes', [ 'node_list' => $accounts_list ] );
			$accounts_list = is_array( $accounts_list ) ? $accounts_list : [];

			foreach ( $accounts_list as $social_account )
			{
				if ( is_string( $social_account ) )
				{
					$social_account = explode( ':', $social_account );

					if ( count( $social_account ) !== 5 )
					{
						continue;
					}

					$_accounts_list[] = ( $social_account[ 1 ] === 'account' ? 'account' : 'node' ) . ':' . $social_account[ 2 ];
				}
			}
		}

		if ( empty( $_accounts_list ) )
		{
			Helper::response( FALSE, fsp__( 'No account or community is selected.' ) );
		}

		if ( strtotime( $filter_posts_date_range_from ) > strtotime( $filter_posts_date_range_to ) )
		{
			Helper::response( FALSE, fsp__( 'The date range is not correct.' ) );
		}

		$_accounts_list = implode( ',', $_accounts_list );

		$allowedPostTypes = explode( '|', Helper::getOption( 'allowed_post_types', '' ) );

		if ( ! in_array( $post_type_filter, $allowedPostTypes ) )
		{
			$post_type_filter = '';
		}

		if ( empty( $title ) )
		{
			Helper::response( FALSE, fsp__( 'The name can\'t be empty!' ) );
		}

		if ( empty( $start_date ) || empty( $start_time ) )
		{
			Helper::response( FALSE, fsp__( 'Please select the start date and time!' ) );
		}

		if ( ! is_numeric( $interval ) | $interval <= 0 )
		{
			Helper::response( FALSE, fsp__( 'Please type the interval!' ) );
		}

		$start_date = Date::dateSQL( $start_date );
		$start_time = Date::timeSQL( $start_time );

		$cronStartTime = $start_date . ' ' . $start_time;

		if ( ! empty( $schedule_info[ 'id' ] ) )
		{
			$old_start_datetime = $schedule_info[ 'start_date' ] . ' ' . $schedule_info[ 'share_time' ];

			if ( $old_start_datetime === $cronStartTime || Date::epoch( $cronStartTime ) < Date::epoch( $schedule_info[ 'next_execute_time' ] ) )
			{
				$expected_last_share  = Date::epoch( $schedule_info[ 'next_execute_time' ] ) - $schedule_info[ 'interval' ] * 60;
				$last_share_timestamp = $expected_last_share >= Date::epoch( $old_start_datetime ) ? $expected_last_share : Date::epoch( $old_start_datetime );

				if ( $old_start_datetime !== $schedule_info[ 'next_execute_time' ] )
				{
					$next_execute_timestamp = $last_share_timestamp + $interval * 60;
					$cronStartTime          = Date::dateTimeSQL( $next_execute_timestamp );
				}

				if ( Date::epoch( $cronStartTime ) < Date::epoch( Date::dateTimeSQL() ) )
				{
					$cronStartTime = $schedule_info[ 'next_execute_time' ];
				}
			}
		}

		$sql_arr = [
			'blog_id'                         => Helper::getBlogId(),
			'title'                           => $title,
			'start_date'                      => $start_date,
			'interval'                        => $interval,
			'status'                          => 'active',
			'insert_date'                     => Date::dateTimeSQL(),
			'user_id'                         => get_current_user_id(),
			'share_time'                      => $start_time,
			'next_execute_time'               => $cronStartTime,
			'post_ids'                        => $post_ids,
			'save_post_ids'                   => $post_ids,
			'post_type_filter'                => $post_type_filter,
			'dont_post_out_of_stock_products' => $dont_post_out_of_stock_products,
			'category_filter'                 => $category_filter > 0 ? $category_filter : NULL,
			'post_sort'                       => $post_sort,
			'post_freq'                       => $post_ids_count > 1 ? 'once' : $post_freq,
			'post_date_filter'                => 'custom',

			'filter_posts_date_range_from' => $filter_posts_date_range_from,
			'filter_posts_date_range_to'   => $filter_posts_date_range_to,

			'custom_post_message' => $_custom_messages,
			'share_on_accounts'   => $_accounts_list,
			'sleep_time_start'    => $sleep_time_start,
			'sleep_time_end'      => $sleep_time_end
		];

		if ( $id > 0 && $schedule_info[ 'status' ] != 'finished' )
		{
			unset( $sql_arr[ 'status' ] );

			DB::DB()->update( DB::table( 'schedules' ), $sql_arr, [ 'id' => $id ] );
		}
		else
		{
			DB::DB()->insert( DB::table( 'schedules' ), $sql_arr );
		}

		Helper::response( TRUE );
	}

	public function wp_native_schedule_save ()
	{
		$post_id         = 0;
		$info            = Request::post( 'info', '', 'string' );
		$custom_messages = Request::post( 'custom_messages', '', 'string' );
		$accounts_list   = Request::post( 'accounts_list', '', 'string' );

		if ( ! empty( $info ) )
		{
			$info    = json_decode( $info, TRUE );
			$info    = is_array( $info ) ? $info : [];
			$post_id = $info[ 'post_id' ];
		}

		if ( ! ( $post_id > 0 ) )
		{
			Helper::response( FALSE );
		}

		$_custom_messages = [];

		if ( ! empty( $custom_messages ) )
		{
			$custom_messages = json_decode( $custom_messages, TRUE );
			$custom_messages = is_array( $custom_messages ) ? $custom_messages : [];

			foreach ( $custom_messages as $socialNetwork => $message1 )
			{
				if ( in_array( $socialNetwork, [
						'fb',
						'fb_h',
						'instagram',
						'instagram_h',
						'linkedin',
						'twitter',
						'pinterest',
						'vk',
						'ok',
						'tumblr',
						'reddit',
						'google_b',
						'blogger',
						'telegram',
						'medium',
						'wordpress',
						'plurk'
					] ) && is_string( $message1 ) )
				{
					$_custom_messages[ $socialNetwork ] = $message1;
				}
			}
		}

		$_accounts_list = [
			'accounts' => [],
			'nodes'    => []
		];

		if ( ! empty( $accounts_list ) )
		{
			$accounts_list = json_decode( $accounts_list, TRUE );
			$accounts_list = Pages::action( 'Base', 'groups_to_nodes', [ 'node_list' => $accounts_list ] );
			$accounts_list = is_array( $accounts_list ) ? $accounts_list : [];

			foreach ( $accounts_list as $social_account )
			{
				if ( is_string( $social_account ) )
				{
					$social_account = explode( ':', $social_account );

					if ( ! is_numeric( $social_account[ 2 ] ) )
					{
						continue;
					}

					if ( $social_account[ 1 ] === 'account' )
					{
						$_accounts_list[ 'accounts' ][] = $social_account[ 2 ];
					}
					else
					{
						$_accounts_list[ 'nodes' ][] = $social_account[ 2 ];
					}
				}
			}
		}

		$account_ids = implode( ',', $_accounts_list[ 'accounts' ] );
		$node_ids    = implode( ',', $_accounts_list[ 'nodes' ] );

		if ( ! empty( $account_ids ) )
		{
			$accounts = DB::DB()->get_results( DB::DB()->prepare( "
				SELECT 
					tb2.*, IFNULL(tb1.filter_type, 'no') AS filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM " . DB::WPtable( 'terms', TRUE ) . " WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name,'account' AS node_type 
				FROM " . DB::table( 'accounts' ) . " tb2
				LEFT JOIN " . DB::table( 'account_status' ) . " tb1 ON tb2.id=tb1.account_id AND tb1.user_id=%d
				WHERE (tb2.user_id=%d OR is_public=1) AND tb2.blog_id=%d AND tb2.id IN ({$account_ids})
				ORDER BY name", [ get_current_user_id(), get_current_user_id(), Helper::getBlogId() ] ), ARRAY_A );
		}
		else
		{
			$accounts = [];
		}

		if ( ! empty( $node_ids ) )
		{
			$active_nodes = DB::DB()->get_results( DB::DB()->prepare( "
				SELECT 
					tb2.*, IFNULL(tb1.filter_type, 'no') AS filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM " . DB::WPtable( 'terms', TRUE ) . " WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name 
				FROM " . DB::table( 'account_nodes' ) . " tb2
				LEFT JOIN " . DB::table( 'account_node_status' ) . " tb1 ON tb2.id=tb1.node_id AND tb1.user_id=%d
				WHERE (tb2.user_id=%d OR tb2.is_public=1) AND tb2.blog_id=%d AND tb2.id IN ({$node_ids})
				ORDER BY (CASE node_type WHEN 'ownpage' THEN 1 WHEN 'group' THEN 2 WHEN 'page' THEN 3 END), name", [
				get_current_user_id(),
				get_current_user_id(),
				Helper::getBlogId()
			] ), ARRAY_A );
		}
		else
		{
			$active_nodes = [];
		}

		$active_nodes = array_merge( $accounts, $active_nodes );

		if ( empty( $active_nodes ) )
		{
			Helper::response( FALSE, fsp__( 'No account or community is selected.' ) );
		}

		DB::DB()->query( DB::DB()->prepare( 'DELETE FROM ' . DB::table( 'feeds' ) . ' WHERE post_id = %d AND is_sended = 0 AND blog_id = %d', [
			$post_id,
			Helper::getBlogId()
		] ) );

		$new_feeds = [];

		foreach ( $active_nodes as $node )
		{
			$new_feeds[] = '(' . intval( $post_id ) . ', ' . intval( $node[ 'id' ] ) . ', "' . esc_sql( $node[ 'node_type' ] ) . '", "' . esc_sql( $node[ 'driver' ] ) . '", "' . esc_sql( $info[ 'send_time' ] ) . '", 0, 0, "' . mb_substr( esc_sql( $_custom_messages[ $node[ 'driver' ] ] ), 0, 2000 ) . '", 1, ' . Helper::getBlogId() . ', 0)';
		}

		$new_feeds_sql = 'INSERT INTO ' . DB::table( 'feeds' ) . ' (`post_id`, `node_id`, `node_type`, `driver`, `send_time`, `interval`, `driver_post_id`, `custom_post_message`, `share_on_background`, `blog_id`, `is_seen`) VALUES ' . implode( ',', $new_feeds ) . '';
		$inserted_rows = DB::DB()->query( $new_feeds_sql );

		if ( ! $inserted_rows )
		{
			Helper::response( TRUE );
		}

		Helper::response( TRUE );
	}

	public function wp_native_schedule_delete ()
	{
		$info = Request::post( 'info', '', 'string' );

		if ( empty( $info ) )
		{
			Helper::response( FALSE );
		}

		$info    = json_decode( $info, TRUE );
		$info    = is_array( $info ) ? $info : [];
		$post_id = $info[ 'post_id' ];

		if ( ! ( $post_id > 0 ) )
		{
			Helper::response( FALSE );
		}

		DB::DB()->query( DB::DB()->prepare( 'DELETE FROM ' . DB::table( 'feeds' ) . ' WHERE post_id = %d AND is_sended = 0 AND blog_id = %d', [
			$post_id,
			Helper::getBlogId()
		] ) );

		Helper::response( TRUE );
	}

	public function schedule_posts ()
	{
		$plan_date  = Request::post( 'plan_date', '', 'string' );
		$post_ids_p = Request::post( 'post_ids', [], 'array' );
		$interval   = Request::post( 'interval', '0', 'num' );

		if ( ! ( $interval > 0 ) )
		{
			Helper::response( FALSE, fsp__( 'Validation error' ) );
		}

		if ( empty( $plan_date ) )
		{
			Helper::response( FALSE, fsp__( 'Schedule date is empty!' ) );
		}
		else if ( Date::epoch( $plan_date, '-1 year' ) > Date::epoch() )
		{
			Helper::response( FALSE, fsp__( 'Plan date or time is not valid!' ) );
		}
		else if ( Date::epoch( $plan_date, '+5 minutes' ) < Date::epoch() )
		{
			Helper::response( FALSE, fsp__( 'Plan date or time is not valid!<br>Please select Schedule date/time according to your server time. <br>Your server time is: %s', [ Date::dateTime() ], FALSE ) );
		}

		$plan_date = Date::dateTimeSQL( $plan_date );

		$post_ids = [];

		foreach ( $post_ids_p as $post_id )
		{
			if ( is_numeric( $post_id ) && $post_id > 0 )
			{
				$post_ids[] = (int) $post_id;
			}
		}

		if ( empty( $post_ids ) )
		{
			Helper::response( FALSE, fsp__( 'Please select at least one post.' ) );
		}
		else if ( count( $post_ids ) > 200 )
		{
			Helper::response( FALSE, fsp__( 'Too many post selected! You can select maximum 200 posts!' ) );
		}

		$custom_messages = Request::post( 'custom_messages', '', 'string' );
		$accounts_list   = Request::post( 'accounts_list', '', 'string' );

		$_custom_messages = [];

		if ( ! empty( $custom_messages ) )
		{
			$custom_messages = json_decode( $custom_messages, TRUE );
			$custom_messages = is_array( $custom_messages ) ? $custom_messages : [];

			foreach ( $custom_messages as $socialNetwork => $message1 )
			{
				if ( in_array( $socialNetwork, [
						'fb',
						'fb_h',
						'instagram',
						'instagram_h',
						'linkedin',
						'twitter',
						'pinterest',
						'vk',
						'ok',
						'tumblr',
						'reddit',
						'google_b',
						'blogger',
						'telegram',
						'medium',
						'wordpress',
						'plurk'
					] ) && is_string( $message1 ) )
				{
					$_custom_messages[ $socialNetwork ] = $message1;
				}
			}
		}

		$_custom_messages = empty( $_custom_messages ) ? NULL : json_encode( $_custom_messages );
		$_accounts_list   = [];

		if ( ! empty( $accounts_list ) )
		{
			$accounts_list = json_decode( $accounts_list, TRUE );
			$accounts_list = Pages::action( 'Base', 'groups_to_nodes', [ 'node_list' => $accounts_list ] );
			$accounts_list = is_array( $accounts_list ) ? $accounts_list : [];

			foreach ( $accounts_list as $social_account )
			{
				if ( is_string( $social_account ) )
				{
					$social_account = explode( ':', $social_account );
					if ( ! ( count( $social_account ) == 2 && is_numeric( $social_account[ 1 ] ) ) )
					{
						continue;
					}

					$_accounts_list[] = ( $social_account[ 0 ] === 'account' ? 'account' : 'node' ) . ':' . $social_account[ 1 ];
				}
			}
		}

		$_accounts_list = empty( $_accounts_list ) ? NULL : implode( ',', $_accounts_list );
		$postsCount     = count( $post_ids );

		if ( $postsCount == 1 )
		{
			$onePostId  = reset( $post_ids );
			$onePostInf = get_post( $onePostId, ARRAY_A );

			$title = ! empty( $onePostInf[ 'post_title' ] ) ? 'Scheduled post: "' . Helper::cutText( $onePostInf[ 'post_title' ] ) . '"' : '';
		}
		else
		{
			$title = 'Schedule ( ' . $postsCount . ' posts )';
		}

		$post_ids = implode( ',', $post_ids );

		$start_date = Date::dateSQL( $plan_date );
		$end_date   = Date::dateSQL( $plan_date, '+' . ( ( $postsCount - 1 ) * $interval ) . ' minutes' );
		$share_time = Date::timeSQL( $plan_date );

		$post_type_filter = '';
		$category_filter  = '';
		$post_sort        = $postsCount == 1 ? 'new_first' : Request::post( 'post_sort', 'old_first', 'string', [
			'old_first',
			'random',
			'new_first'
		] );
		$post_date_filter = 'all';

		DB::DB()->insert( DB::table( 'schedules' ), [
			'blog_id'     => Helper::getBlogId(),
			'title'       => $title,
			'start_date'  => $start_date,
			'end_date'    => $end_date,
			'interval'    => $interval,
			'status'      => 'active',
			'insert_date' => Date::dateTimeSQL(),
			'user_id'     => get_current_user_id(),
			'share_time'  => $share_time,

			'post_type_filter' => $post_type_filter,
			'category_filter'  => $category_filter,
			'post_sort'        => $post_sort,
			'post_date_filter' => $post_date_filter,

			'post_ids'          => $post_ids,
			'save_post_ids'     => $post_ids,
			'next_execute_time' => $plan_date,

			'custom_post_message' => $_custom_messages,
			'share_on_accounts'   => $_accounts_list
		] );

		Helper::response( TRUE );
	}

	public function delete_schedule ()
	{
		$id = Request::post( 'id', 0, 'num' );
		if ( $id <= 0 )
		{
			Helper::response( FALSE );
		}

		$checkSchedule = DB::fetch( 'schedules', $id );
		if ( ! $checkSchedule )
		{
			Helper::response( FALSE, fsp__( 'There isn\'t a schedule.' ) );
		}
		else
		{
			if ( $checkSchedule[ 'user_id' ] != get_current_user_id() )
			{
				Helper::response( FALSE, fsp__( 'You don\'t have permission to delete the schedule!' ) );
			}
		}

		DB::DB()->delete( DB::table( 'schedules' ), [ 'id' => $id ] );

		Helper::response( TRUE );
	}

	public function delete_schedules ()
	{
		$ids = Request::post( 'ids', [], 'array' );
		if ( count( $ids ) == 0 )
		{
			Helper::response( FALSE, fsp__( 'No schedule selected!' ) );
		}

		foreach ( $ids as $id )
		{
			if ( is_numeric( $id ) && $id > 0 )
			{
				$checkSchedule = DB::fetch( 'schedules', $id );
				if ( ! $checkSchedule )
				{
					Helper::response( FALSE, fsp__( 'There isn\'t a schedule.' ) );
				}

				else
				{
					if ( $checkSchedule[ 'user_id' ] != get_current_user_id() )
					{
						Helper::response( FALSE, fsp__( 'You don\'t have permission to delete the schedule!' ) );
					}
				}

				DB::DB()->delete( DB::table( 'schedules' ), [ 'id' => $id ] );
			}
		}

		Helper::response( TRUE );
	}

	public function schedule_change_status ()
	{
		$id = Request::post( 'id', 0, 'num' );

		if ( $id <= 0 )
		{
			Helper::response( FALSE );
		}

		$checkSchedule = DB::fetch( 'schedules', $id );
		if ( ! $checkSchedule )
		{
			Helper::response( FALSE, fsp__( 'There isn\'t a schedule.' ) );
		}
		else
		{
			if ( $checkSchedule[ 'user_id' ] != get_current_user_id() )
			{
				Helper::response( FALSE, fsp__( 'You don\'t have permission to Pause/Play the schedule!' ) );
			}
		}

		if ( $checkSchedule[ 'status' ] != 'paused' && $checkSchedule[ 'status' ] != 'active' )
		{
			Helper::response( FALSE, fsp__( 'This schedule has finished!' ) );
		}

		$newStatus = $checkSchedule[ 'status' ] === 'active' ? 'paused' : 'active';

		$update_arr = [ 'status' => $newStatus ];

		if ( $newStatus != 'paused' )
		{
			$locTime         = Date::epoch();
			$scheduleStarted = Date::epoch( $checkSchedule[ 'start_date' ] . ' ' . $checkSchedule[ 'share_time' ] );

			$dif = $locTime - $scheduleStarted;

			$interval = $checkSchedule[ 'interval' ] * 60;

			$nextExecTime = ( $dif % $interval ) === 0 ? $locTime : $locTime + $interval - ( $dif % $interval );

			$update_arr[ 'next_execute_time' ] = Date::dateTimeSQL( $nextExecTime );
		}

		DB::DB()->update( DB::table( 'schedules' ), $update_arr, [ 'id' => $id ] );

		Helper::response( TRUE );
	}

	public function schedule_get_calendar ()
	{
		$month = (int) Request::post( 'month', Date::month(), 'int', [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 ] );
		$year  = (int) Request::post( 'year', Date::year(), 'int' );

		if ( $year > Date::year() + 4 || $year < Date::year() - 4 )
		{
			Helper::response( FALSE, fsp__( 'Loooooooooooooooolll :)' ) );
		}

		$firstDate = Date::datee( "{$year}-{$month}-01" );
		$lastDate  = Date::lastDateOfMonth( $year, $month );
		$myId      = get_current_user_id();

		if ( Date::epoch( $firstDate ) < Date::epoch( Date::dateSQL() ) )
		{
			$firstDate = Date::datee();
		}

		$getPlannedDays = DB::DB()->get_results( "SELECT * FROM `" . DB::table( 'schedules' ) . "` WHERE `start_date` <= '$lastDate' AND `status` = 'active' AND `user_id` = '$myId' AND `blog_id` = '" . Helper::getBlogId() . "'", ARRAY_A );

		$days = [];

		foreach ( $getPlannedDays as $planInf )
		{
			$scheduleId = (int) $planInf[ 'id' ];
			//$planStart  = Date::epoch( $planInf[ 'start_date' ] );
			$planStart = Date::epoch( $planInf[ 'next_execute_time' ] );
			$planEnd   = Date::epoch( $lastDate );
			$interval  = (int) $planInf[ 'interval' ] > 0 ? (int) $planInf[ 'interval' ] : 1;
			$postCount = empty( $planInf[ 'post_ids' ] ) || $planInf[ 'post_freq' ] === 'repeat' ? -1 : count( explode( ',', $planInf[ 'post_ids' ] ) );

			if ( $postCount > 0 && $planInf[ 'post_freq' ] === 'once' )
			{
				$duration      = $interval * ( $postCount - 1 );
				$last_run_date = $planStart + $duration * 60;

				if ( Date::epoch( $firstDate ) > $last_run_date )
				{
					continue;
				}
			}

			if ( $planStart < Date::epoch( $firstDate ) )
			{
				while ( $planStart < Date::epoch( $firstDate ) )
				{
					$planStart += 60 * $interval;
				}
			}

			if ( $planInf[ 'post_sort' ] != 'random' && $planInf[ 'post_sort' ] != 'random2' )
			{
				$filterQuery = Helper::scheduleFilters( $planInf );
				$calcLimit   = 1 + (int) ( ( $planEnd - $planStart ) / 60 / $interval );

				$calcLimit = $calcLimit > 0 ? $calcLimit : 1;

				$getRandomPost = DB::DB()->get_results( "SELECT * FROM " . DB::WPtable( 'posts', TRUE ) . " tb1 WHERE post_status='publish' {$filterQuery} LIMIT " . $calcLimit, ARRAY_A );
			}

			if ( empty( $planInf[ 'share_time' ] ) )
			{
				$getLastShareTime        = DB::DB()->get_row( "SELECT MAX(send_time) AS max_share_time FROM " . DB::table( 'feeds' ) . " WHERE schedule_id='$scheduleId'", ARRAY_A );
				$planInf[ 'share_time' ] = Date::timeSQL( $getLastShareTime[ 'max_share_time' ] );
			}

			//$cursorDayTimestamp = Date::epoch( Date::dateSQL( $planStart ) . ' ' . $planInf[ 'share_time' ] );
			$cursorDayTimestamp = Date::epoch( Date::dateTimeSQL( $planStart ) );
			$planEnd            = Date::epoch( Date::dateSQL( $planEnd ) . ' 23:59:59' );

			while ( $cursorDayTimestamp <= $planEnd )
			{
				$currentDate = Date::dateSQL( $cursorDayTimestamp );
				$time        = Date::time( $cursorDayTimestamp );

				if ( ! empty( $planInf[ 'sleep_time_start' ] ) && ! empty( $planInf[ 'sleep_time_end' ] ) )
				{
					$sleepTimeStart = Date::epoch( $currentDate . ' ' . $planInf[ 'sleep_time_start' ] );
					$sleepTimeEnd   = Date::epoch( $currentDate . ' ' . $planInf[ 'sleep_time_end' ] );

					if ( Helper::isBetweenDates( $cursorDayTimestamp, $sleepTimeStart, $sleepTimeEnd ) )
					{
						$cursorDayTimestamp += 60 * $interval;

						continue;
					}
				}

				$cursorDayTimestamp += 60 * $interval;

				if ( Date::epoch( $currentDate . ' ' . $time ) < Date::epoch() )
				{
					continue;
				}

				if ( $postCount === 0 )
				{
					break;
				}

				if ( $planInf[ 'post_sort' ] === 'random' || $planInf[ 'post_sort' ] === 'random2' )
				{
					$postDetails = 'Will select randomly';
					$post_id     = NULL;
				}
				else
				{
					$thisPostInf = current( $getRandomPost );
					next( $getRandomPost );

					if ( $thisPostInf )
					{
						$postDetails = '<b>Post ID:</b> ' . $thisPostInf[ 'ID' ] . "<br><b>Title:</b> " . htmlspecialchars( Helper::cutText( $thisPostInf[ 'post_title' ] ) . '<br><br><i>Click to get the post page</i>' );
						$post_id     = $thisPostInf[ 'ID' ];
					}
					else
					{
						$postDetails = 'Post not found with your filters for this date!';
						$post_id     = NULL;
					}
				}

				$days[] = [
					'id'        => $planInf[ 'id' ],
					'title'     => htmlspecialchars( Helper::cutText( $planInf[ 'title' ], 22 ) ),
					'post_data' => $postDetails,
					'post_id'   => $post_id,
					'date'      => $currentDate,
					'time'      => $time
				];

				$postCount--;
			}
		}

		Helper::response( TRUE, [ 'days' => $days ] );
	}

	public function recalculate_filtered_posts_count ()
	{
		$post_type_filter                = Request::post( 'post_type_filter', '', 'string' );
		$dont_post_out_of_stock_products = Request::post( 'dont_post_out_of_stock_products', '0', 'string', [
			'1',
			'0'
		] );
		$category_filter                 = Request::post( 'category_filter', '', 'int' );

		$filter_posts_date_range_from = Request::post( 'filter_posts_date_range_from', '', 'string' );
		$filter_posts_date_range_to   = Request::post( 'filter_posts_date_range_to', '', 'string' );

		$filter_posts_date_range_from = ! empty( $filter_posts_date_range_from ) ? $filter_posts_date_range_from : '1000-00-00';
		$filter_posts_date_range_to   = ! empty( $filter_posts_date_range_to ) ? $filter_posts_date_range_to : '9999-12-31';

		$post_ids = Request::post( 'post_ids', '', 'string' );

		$schedule_info = [
			'id'                              => 0,
			'post_type_filter'                => $post_type_filter,
			'dont_post_out_of_stock_products' => $dont_post_out_of_stock_products,
			'category_filter'                 => $category_filter,
			'post_date_filter'                => 'custom',
			'post_ids'                        => $post_ids,
			'filter_posts_date_range_from'    => $filter_posts_date_range_from,
			'filter_posts_date_range_to'      => $filter_posts_date_range_to
		];

		$filterQuery = Helper::scheduleFilters( $schedule_info );

		$getRandomPost = DB::DB()->get_row( "SELECT count(0) AS `post_count` FROM `" . DB::WPtable( 'posts', TRUE ) . "` tb1 WHERE (`post_status`='publish' OR `post_type`='attachment') {$filterQuery}", ARRAY_A );
		$postsCount    = (int) $getRandomPost[ 'post_count' ];

		Helper::response( TRUE, [
			'count' => $postsCount
		] );
	}
}
