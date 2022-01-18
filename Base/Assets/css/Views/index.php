<?php

namespace FSPoster\App\Pages\Base\Views;

use FSPoster\App\Providers\Pages;

defined( 'ABSPATH' ) or exit;
?>

<div class="fsp-container">
	<div class="fsp-header">
		<div class="fsp-nav">
			<a class="fsp-nav-link <?php echo( $fsp_params[ 'page_name' ] === 'Dashboard' ? 'active' : '' ); ?>" href="?page=fs-poster"><?php echo fsp__( 'Dashboard' ); ?></a>
			<a class="fsp-nav-link <?php echo( $fsp_params[ 'page_name' ] === 'Accounts' ? 'active' : '' ); ?>" href="?page=fs-poster-accounts"><?php echo fsp__( 'Accounts' ); ?></a>
			<a class="fsp-nav-link <?php echo( $fsp_params[ 'page_name' ] === 'Schedules' ? 'active' : '' ); ?>" href="?page=fs-poster-schedules"><?php echo fsp__( 'Schedules' ); ?></a>
			<a class="fsp-nav-link <?php echo( $fsp_params[ 'page_name' ] === 'Share' ? 'active' : '' ); ?>" href="?page=fs-poster-share"><?php echo fsp__( 'Direct Share' ); ?></a>
			<a class="fsp-nav-link <?php echo( $fsp_params[ 'page_name' ] === 'Logs' ? 'active' : '' ); ?>" href="?page=fs-poster-logs"><?php echo fsp__( 'Logs' ); ?></a>
			<a class="fsp-nav-link <?php echo( $fsp_params[ 'page_name' ] === 'Apps' ? 'active' : '' ); ?>" href="?page=fs-poster-apps"><?php echo fsp__( 'Apps' ); ?></a>
			<?php if ( ( current_user_can( 'administrator' ) || defined( 'FS_POSTER_IS_DEMO' ) ) ) { ?>
				<a class="fsp-nav-link <?php echo( $fsp_params[ 'page_name' ] === 'Settings' ? 'active' : '' ); ?>" href="?page=fs-poster-settings"><?php echo fsp__( 'Settings' ); ?></a>
			<?php } ?>
		</div>
	</div>
	<div class="fsp-body">
		<?php Pages::controller( $fsp_params[ 'page_name' ], 'Main', 'index' ); ?>
	</div>
</div>
