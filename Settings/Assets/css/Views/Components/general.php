<?php

namespace FSPoster\App\Pages\Settings\Views;

use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Allowed post types' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Add post types that you want to share.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<select class="fsp-form-input select2-init" id="fs_allowed_post_types" name="fs_allowed_post_types[]" multiple>
			<?php
			$selectedTypes = explode( '|', Helper::getOption( 'allowed_post_types', 'post|page|attachment|product' ) );
			foreach ( get_post_types() as $post_type )
			{
				echo '<option value="' . htmlspecialchars( $post_type ) . '"' . ( in_array( $post_type, $selectedTypes ) ? ' selected' : '' ) . '>' . htmlspecialchars( $post_type ) . '</option>';
			}
			?>
		</select>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Hide FS Poster for' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Select the user roles to hide the FS Poster plugin for some users.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<select class="fsp-form-input select2-init" id="fs_hide_for_roles" name="fs_hide_for_roles[]" multiple>
			<?php
			$hideForRoles = explode( '|', Helper::getOption( 'hide_menu_for', '' ) );
			$wp_roles     = get_editable_roles();
			foreach ( $wp_roles as $roleId => $roleInf )
			{
				if ( $roleId === 'administrator' )
				{
					continue;
				}

				echo '<option value="' . htmlspecialchars( $roleId ) . '"' . ( in_array( $roleId, $hideForRoles ) ? ' selected' : '' ) . '>' . htmlspecialchars( $roleInf[ 'name' ] ) . '</option>';
			}
			?>
		</select>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Show FS Poster column on the posts table' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'If you don\'t want to show FS Poster <i class="far fa-question-circle fsp-tooltip"  data-title="Click to learn more" data-open-img="%s"></i> column on posts table, you can disable this option.', [ Pages::asset( 'Base', 'img/fs_poster_column_help.png' ) ], FALSE ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_show_fs_poster_column" class="fsp-toggle-checkbox" id="fs_show_fs_poster_column"<?php echo Helper::getOption( 'show_fs_poster_column', '1' ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_show_fs_poster_column"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Hide notifications' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable the option to hide notifications for failed posts and disconnected accounts.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_hide_notifications" class="fsp-toggle-checkbox" id="fspHideNotifications" <?php echo Helper::getOption( 'hide_notifications', '0' ) ? 'checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fspHideNotifications"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Check accounts' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable the option to check the status of all active accounts daily.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_check_accounts" class="fsp-toggle-checkbox" id="fspCheckAccounts" <?php echo Helper::getOption( 'check_accounts', 1 ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fspCheckAccounts"></label>
		</div>
	</div>
</div>
<div id="fspDisableAccountsRow" class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Disable failed accounts' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable the option to deactivate failed accounts. If you have enabled the option to check the status of your accounts and if the connection to the account is unable, the plugin will not share posts on those failed accounts.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_check_accounts_disable" class="fsp-toggle-checkbox" id="fs_check_accounts_disable"<?php echo Helper::getOption( 'check_accounts_disable', 0 ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_check_accounts_disable"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Auto-clean' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Clean deleted user\'s accounts weekly.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_clean_accounts" class="fsp-toggle-checkbox" id="fspCleanAccounts" <?php echo Helper::getOption( 'clean_accounts', 0 ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fspCleanAccounts"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'License status' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'If you want to transfer the plugin to another website, you have to disable the license on this website. Disabling the license keeps all data, except the license. We recommend <a href="?page=fs-poster-settings&setting=export_import" target="_blank">backing up</a> the plugin before disabling the license.', [], FALSE ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fsp_license_status" class="fsp-toggle-checkbox" id="fspLicenseStatus" checked>
			<label class="fsp-toggle-label" for="fspLicenseStatus"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Collect FS Poster statistics' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'The plugin appends a "feed_id" parameter to a post link to get statistics. Disabling the option prevents you from getting statistics in the Dashboard tab. And because the plugin does not collect statistics, you might also have duplicate posts on Social Networks even if you select the "Randomly without duplicates" option when you use the schedule module.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_collect_statistics" class="fsp-toggle-checkbox" id="fs_collect_statistics"<?php echo Helper::getOption( 'collect_statistics', '1' ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_collect_statistics"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Cron Job settings - IMPORTANT!' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'You need to configure <a href="https://www.fs-poster.com/documentation/how-to-set-up-a-cron-job-on-wordpress-fs-poster-wp-plugin" target="_blank">a real Cron Job</a> on your hosting/server and enable this option to ignore the default Cron Job for more accurate results. Otherwise, the auto-post and schedule features might not work punctually, and you might encounter delays.', [], FALSE ); ?></div>
		<br>
		<?php
		$cron_last_runned_on = Helper::getOption( 'cron_job_runned_on', 0 );

		if ( empty( $cron_last_runned_on ) )
		{
			$cron_text = fsp__( 'Not runned! Your schedule posts and background share may be not work! Please follow this <a href="https://www.fs-poster.com/documentation/how-to-set-up-a-cron-job-on-wordpress-fs-poster-wp-plugin" target="_blank">documentation</a> and configure your WordPress Cron Jobs.', [], FALSE );
		}
		else
		{
			$cron_time = intval( ( Date::epoch() - $cron_last_runned_on ) / 60 );
			$cron_time = $cron_time > 0 ? $cron_time : 1;

			if ( $cron_time > 10 )
			{
				$delay = fsp__( ' - it looks like your Cron job runs with delay. Therefore your Scheduled posts and background shares may post with delay.' );
			}

			$cron_text = Date::dateTime( $cron_last_runned_on ) . ' ' . fsp__( '( %s minute%s ago%s )', [
					$cron_time,
					( $cron_time > 1 ? 's' : '' ),
					( isset( $delay ) ? $delay : '' )
				] );
		}

		$real_cron_last_runned_on = Helper::getOption( 'real_cron_job_runned_on', 0 );

		if ( empty( $real_cron_last_runned_on ) )
		{
			$real_cron_text = fsp__( 'Not runned' );
		}
		else
		{
			$real_cron_time = intval( ( Date::epoch() - $real_cron_last_runned_on ) / 60 );
			$real_cron_time = $real_cron_time > 0 ? $real_cron_time : 1;

			$real_cron_text = Date::dateTime( $real_cron_last_runned_on ) . ' ' . fsp__( '( %s minute%s ago )', [
					$real_cron_time,
					( $real_cron_time > 1 ? 's' : '' )
				] );
		}

		echo '<div class="fsp-settings-label-subtext">' . fsp__( 'The last time, the Cron Job ran on your website ' . $cron_text ) . '</div>';
		echo '<div class="fsp-settings-label-subtext">' . fsp__( 'The last time, <a href="https://www.fs-poster.com/documentation/how-to-set-up-a-cron-job-on-wordpress-fs-poster-wp-plugin" target="_blank">the Real Cron Job</a> ran on your website ' . $real_cron_text, [], FALSE ) . '</div>';
		?>
		<br>
		<div class="fsp-settings-label-text"><?php echo fsp__( 'The Cron Job command for your website is:' ); ?></div>
		<div class="fsp-note-text">
			<span>wget -O /dev/null <?php echo site_url(); ?>/wp-cron.php?doing_wp_cron > /dev/null 2>&1</span>&emsp;<i id="fspClickToCopy" class="far fa-copy fsp-tooltip" data-title="<?php echo fsp__( 'Click to copy' ); ?>"></i>
		</div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_virtual_cron_job_disabled" class="fsp-toggle-checkbox" id="fs_virtual_cron_job_disabled" <?php echo Helper::getOption( 'virtual_cron_job_disabled', '0' ) ? 'checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_virtual_cron_job_disabled"></label>
		</div>
	</div>
</div>
