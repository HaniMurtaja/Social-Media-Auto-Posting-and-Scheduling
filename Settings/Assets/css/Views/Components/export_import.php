<?php

namespace FSPoster\App\Pages\Settings\Views;

use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<input id="fspImportFileInput" type="file" class="fsp-hide">

<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Export multisite' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable to export the plugin settings for all websites on this network. Disable to export only the current website settings.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_export_multisite" class="fsp-toggle-checkbox" id="fspExportMultisite" <?php echo Helper::getOption( 'export_multisite', '0' ) ? 'checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fspExportMultisite"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Export accounts & communities' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable to export all your accounts and communities like pages, groups, companies, etc.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_export_accounts" class="fsp-toggle-checkbox" id="fspExportAccounts" <?php echo Helper::getOption( 'export_accounts', '1' ) ? 'checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fspExportAccounts"></label>
		</div>
	</div>
</div>
<div id="fspExportFailedAccountsRow" class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Export failed accounts' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable to export the failed accounts. Disabling the option and adding the failed accounts to the plugin again is recommended.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_export_failed_accounts" class="fsp-toggle-checkbox" id="fs_export_failed_accounts" <?php echo Helper::getOption( 'export_failed_accounts', '0' ) ? 'checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_export_failed_accounts"></label>
		</div>
	</div>
</div>
<div id="fspExportAccountsStatusesRow" class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Export status of accounts' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable to export status of accounts, pages, groups, companies, etc. The status includes if it is activated or activated with conditions.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_export_accounts_statuses" class="fsp-toggle-checkbox" id="fs_export_accounts_statuses" <?php echo Helper::getOption( 'accounts_statuses', '1' ) ? 'checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_export_accounts_statuses"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Export apps' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable to export all your personal Apps.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_export_apps" class="fsp-toggle-checkbox" id="fs_export_apps" <?php echo Helper::getOption( 'export_apps', '1' ) ? 'checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_export_apps"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Export logs' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'If you are re-installing the plugin on the same website for any reason, we recommend exporting logs as well because your future schedules might share your posts that are already shared by schedules.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_export_logs" class="fsp-toggle-checkbox" id="fspExportLogs" <?php echo Helper::getOption( 'export_logs', '0' ) ? 'checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fspExportLogs"></label>
		</div>
	</div>
</div>
<div id="fspExportSchedulesRow" class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Export schedules' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'If you are re-installing the plugin on the same website for any reason, you can export all your current schedules. If you are importing schedules of a different website to a new website, the schedules won\'t work.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_export_schedules" class="fsp-toggle-checkbox" id="fs_export_schedules" <?php echo Helper::getOption( 'export_schedules', '0' ) ? 'checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_export_schedules"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Export settings' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable to export all your current settings and custom messages.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_export_settings" class="fsp-toggle-checkbox" id="fs_export_settings" <?php echo Helper::getOption( 'export_settings', '1' ) ? 'checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_export_settings"></label>
		</div>
	</div>
</div>
