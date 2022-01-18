<?php

namespace FSPoster\App\Providers;

class CronJob
{
	private static $reScheduledList = [];
	/**
	 * @var BackgrouondProcess
	 */
	private static $backgroundProcess;

	public static function init ()
	{
		self::$backgroundProcess = new BackgrouondProcess();

		$last_runned_on = Helper::getOption( 'cron_job_runned_on', 0 );
		$last_runned_on = is_numeric( $last_runned_on ) ? $last_runned_on : 0;
		$diff           = Date::epoch() - $last_runned_on;

		if ( $diff < 50 )
		{
			return;
		}

		if ( defined( 'DOING_CRON' ) )
		{
			Helper::setOption( 'cron_job_runned_on', Date::epoch(), FALSE, FALSE );
			Helper::setOption( 'real_cron_job_runned_on', Date::epoch(), FALSE, FALSE );

			add_action( 'init', function () {
				set_time_limit( 0 );

				ShareService::shareQueuedFeeds();
				ShareService::shareSchedules();

				if ( Helper::getOption( 'check_accounts', 1 ) && Helper::getOption( 'check_accounts_last', Date::epoch( 'now', '-2 days' ) ) < Date::epoch( 'now', '-1 day' ) )
				{
					AccountService::checkAccounts();
				}
			}, 20 );
		}
		else if ( Helper::getOption( 'virtual_cron_job_disabled', '0' ) != '1' )
		{
			Helper::setOption( 'cron_job_runned_on', Date::epoch(), FALSE, FALSE );

			if ( ! self::isThisProcessBackgroundTask() )
			{
				self::runBackgroundTaksIfNeeded();
			}
		}
	}

	public static function runBackgroundTaksIfNeeded ()
	{
		$notSendedFeeds = DB::DB()->prepare( 'SELECT COUNT(0) as `feed_count` FROM `' . DB::table( 'feeds' ) . '` WHERE `share_on_background`=1 and `is_sended`=0 and `send_time`<=%s', [ Date::dateTimeSQL() ] );
		$notSendedFeeds = DB::DB()->get_row( $notSendedFeeds, ARRAY_A );

		if ( $notSendedFeeds[ 'feed_count' ] > 0 )
		{
			add_action( 'init', function () {
				self::$backgroundProcess->dispatch();
			}, 20 );
		}
		else
		{
			$schdules = DB::DB()->prepare( 'SELECT COUNT(0) as `schedule_count` FROM `' . DB::table( 'schedules' ) . '` WHERE `status`=\'active\' and `next_execute_time`<=%s', [ Date::dateTimeSQL() ] );
			$schdules = DB::DB()->get_row( $schdules, ARRAY_A );

			if ( $schdules[ 'schedule_count' ] > 0 )
			{
				add_action( 'init', function () {
					self::$backgroundProcess->dispatch();
				}, 20 );
			}
			else
			{
				$notCheckedAccounts = DB::DB()->get_row( 'SELECT COUNT(0) as `account_count` FROM ' . DB::table( 'accounts' ) . ' WHERE ((id IN (SELECT account_id FROM ' . DB::table( 'account_status' ) . ')) OR (id IN (SELECT account_id FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (SELECT node_id FROM ' . DB::table( 'account_node_status' ) . '))))', ARRAY_A );

				if ( $notCheckedAccounts[ 'account_count' ] > 0 )
				{
					add_action( 'init', function () {
						self::$backgroundProcess->dispatch();
					}, 20 );
				}
			}
		}
	}

	public static function isThisProcessBackgroundTask ()
	{
		$action = Request::get( 'action' );

		return $action === self::$backgroundProcess->getAction();
	}
}
