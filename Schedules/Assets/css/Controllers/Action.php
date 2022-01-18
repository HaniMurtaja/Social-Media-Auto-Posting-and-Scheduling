<?php

namespace FSPoster\App\Pages\Schedules\Controllers;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;

class Action
{
	public function get_list ()
	{
		$schedules = DB::DB()->get_results( DB::DB()->prepare( 'SELECT *, (SELECT COUNT(0) FROM `' . DB::table( 'feeds' ) . '` WHERE `schedule_id`=tb1.id and `is_sended`=1) AS `shares_count` FROM `' . DB::table( 'schedules' ) . '` tb1 WHERE `user_id`=%d AND `blog_id`=%d ORDER BY `id` DESC', [
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		$names_array1 = [
			'random2'   => fsp__( 'Randomly without dublicates' ),
			'random'    => fsp__( 'Randomly' ),
			'old_first' => fsp__( 'Start from the oldest to new posts' ),
			'new_first' => fsp__( 'Start from the latest to old posts' )
		];

		$names_array2 = [
			'all'              => fsp__( 'All posts' ),
			'this_week'        => fsp__( 'This week added posts' ),
			'previously_week'  => fsp__( 'Previous week added posts' ),
			'this_month'       => fsp__( 'This month added posts' ),
			'previously_month' => fsp__( 'Previous month added posts' ),
			'this_year'        => fsp__( 'This year added posts' ),
			'last_30_days'     => fsp__( 'Last 30 days' ),
			'last_60_days'     => fsp__( 'Last 60 days' ),
			'custom'           => fsp__( 'Custom date range' )
		];

		foreach ( $schedules as $key => $schedule )
		{
			$selectedAccounts = explode( ',', $schedule[ 'share_on_accounts' ] );
			$accountList      = [];
			$nodeList         = [];

			foreach ( $selectedAccounts as $account )
			{
				$accountData = explode( ':', $account );

				if ( ! isset( $accountData[ 1 ] ) )
				{
					continue;
				}
				else
				{
					if ( $accountData[ 0 ] === 'account' )
					{
						$accountList[] = $accountData[ 1 ];
					}
					else
					{
						$nodeList[] = $accountData[ 1 ];
					}
				}
			}

			$count1 = ! empty( $accountList ) ? DB::DB()->get_row( 'SELECT COUNT(0) AS c FROM ' . DB::table( 'accounts' ) . ' WHERE id IN (' . implode( ',', $accountList ) . ')', ARRAY_A ) : [ 'c' => 0 ];
			$count2 = ! empty( $nodeList ) ? DB::DB()->get_row( 'SELECT COUNT(0) AS c FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (' . implode( ',', $nodeList ) . ')', ARRAY_A ) : [ 'c' => 0 ];

			$schedules[ $key ][ 'accounts_count' ] = $count1[ 'c' ] + $count2[ 'c' ];
		}

		return [
			'schedules'   => $schedules,
			'namesArray1' => $names_array1,
			'namesArray2' => $names_array2
		];
	}
}
