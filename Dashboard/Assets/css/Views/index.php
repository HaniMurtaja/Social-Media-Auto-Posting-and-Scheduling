<?php

namespace FSPoster\App\Pages\Dashboard\Views;

use FSPoster\App\Providers\Pages;

defined( 'ABSPATH' ) or exit;
?>

<script>
	fspConfig.comparison = {
		data: <?php echo json_encode( $fsp_params[ 'report3' ][ 'data' ] ); ?>,
		labels: <?php echo json_encode( $fsp_params[ 'report3' ][ 'labels' ] ); ?>
	};

	fspConfig.accComparison = {
		data: <?php echo json_encode( $fsp_params[ 'report4' ][ 'data' ] ); ?>,
		labels: <?php echo json_encode( $fsp_params[ 'report4' ][ 'labels' ] ); ?>
	};
</script>

<div class="fsp-row">
	<div class="fsp-col-12">
		<div class="fsp-dashboard-stats fsp-row">
			<div class="fsp-dashboard-stats-col fsp-col-12 fsp-col-md-6 fsp-col-lg-3">
				<img class="fsp-dashboard-stats-icon" src="<?php echo Pages::asset( 'Dashboard', 'img/fsp-icon-share.svg' ); ?>">
				<div>
					<span class="fsp-dashboard-stats-text"><?php echo (int) $fsp_params[ 'sharesThisMonth' ][ 'c' ]; ?></span>
					<span class="fsp-dashboard-stats-subtext"><?php echo fsp__( 'Shares in this month' ); ?></span>
				</div>
			</div>
			<div class="fsp-dashboard-stats-col fsp-col-12 fsp-col-md-6 fsp-col-lg-3">
				<img class="fsp-dashboard-stats-icon" src="<?php echo Pages::asset( 'Dashboard', 'img/fsp-icon-pointer.svg' ); ?>">
				<div>
					<span class="fsp-dashboard-stats-text"><?php echo (int) $fsp_params[ 'hitsThisMonth' ][ 'c' ]; ?></span>
					<span class="fsp-dashboard-stats-subtext"><?php echo fsp__( 'Clicks in this month' ); ?></span>
				</div>
			</div>
			<div class="fsp-dashboard-stats-col fsp-col-12 fsp-col-md-6 fsp-col-lg-3">
				<img class="fsp-dashboard-stats-icon" src="<?php echo Pages::asset( 'Dashboard', 'img/fsp-icon-people.svg' ); ?>">
				<div>
					<span class="fsp-dashboard-stats-text"><?php echo (int) $fsp_params[ 'accounts' ][ 'c' ]; ?></span>
					<span class="fsp-dashboard-stats-subtext"><?php echo fsp__( 'Total accounts' ); ?></span>
				</div>
			</div>
			<div class="fsp-dashboard-stats-col fsp-col-12 fsp-col-md-6 fsp-col-lg-3">
				<img class="fsp-dashboard-stats-icon" src="<?php echo Pages::asset( 'Dashboard', 'img/fsp-icon-calendar.svg' ); ?>">
				<div>
					<span class="fsp-dashboard-stats-text"><?php echo (int) $fsp_params[ 'hitsThisMonthSchedule' ][ 'c' ]; ?></span>
					<span class="fsp-dashboard-stats-subtext"><?php echo fsp__( 'Clicks from schedules' ); ?></span>
				</div>
			</div>
		</div>
	</div>
	<div class="fsp-dashboard-graphs fsp-col-12 fsp-col-md-6">
		<div class="fsp-card">
			<div class="fsp-card-title">
				<?php echo fsp__( 'Shared posts count' ); ?>
				<select id="fspReports_sharesTypes" class="fsp-select2-single">
					<option value="dayly"><?php echo fsp__( 'Daily' ); ?></option>
					<option value="monthly"><?php echo fsp__( 'Monthly' ); ?></option>
					<option value="yearly"><?php echo fsp__( 'Annually' ); ?></option>
				</select>
			</div>
			<div class="fsp-card-body fsp-p-20">
				<canvas id="fspReports_sharesChart"></canvas>
			</div>
		</div>
	</div>
	<div class="fsp-dashboard-graphs fsp-col-12 fsp-col-md-6">
		<div class="fsp-card">
			<div class="fsp-card-title">
				<?php echo fsp__( 'Clicks count' ); ?>
				<select id="fspReports_clicksTypes" class="fsp-select2-single">
					<option value="dayly"><?php echo fsp__( 'Daily' ); ?></option>
					<option value="monthly"><?php echo fsp__( 'Monthly' ); ?></option>
					<option value="yearly"><?php echo fsp__( 'Annually' ); ?></option>
				</select>
			</div>
			<div class="fsp-card-body fsp-p-20">
				<canvas id="fspReports_clicksChart"></canvas>
			</div>
		</div>
	</div>
	<div class="fsp-dashboard-graphs fsp-col-12 fsp-col-md-6">
		<div class="fsp-card">
			<div class="fsp-card-title">
				<?php echo fsp__( 'Social networks comparison (by clicks)' ); ?>
				<div></div>
			</div>
			<div class="fsp-card-body fsp-p-20">
				<canvas id="fspReports_comparisonChart"></canvas>
			</div>
		</div>
	</div>
	<div class="fsp-dashboard-graphs fsp-col-12 fsp-col-md-6">
		<div class="fsp-card">
			<div class="fsp-card-title">
				<?php echo fsp__( 'Accounts comparison (by clicks)' ); ?>
				<div></div>
			</div>
			<div class="fsp-card-body fsp-p-20">
				<canvas id="fspReports_accComparisonChart"></canvas>
			</div>
		</div>
	</div>
</div>