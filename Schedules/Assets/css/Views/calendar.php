<?php

namespace FSPoster\App\Pages\Base\Views;

use FSPoster\App\Providers\Pages;

defined( 'ABSPATH' ) or exit;
?>

<div class="fsp-row">
	<div class="fsp-col-12 fsp-title">
		<div class="fsp-title-text">
			<?php echo fsp__( 'Calendar' ); ?>
		</div>
		<div class="fsp-title-button">
			<a href="?page=fs-poster-schedules&view=list" class="fsp-button fsp-is-gray">
				<i class="fas fa-chevron-left"></i>
				<span><?php echo fsp__( 'BACK TO SCHEDULES' ); ?></span>
			</a>
		</div>
	</div>
	<div class="fsp-col-12 fsp-row">
		<div class="fsp-col-12 fsp-col-md-6 fsp-calendar-left">
			<div class="fsp-card fsp-calendar-card">
				<div class="fsp-card-body">
					<div id="prev_month" class="fsp-calendar-arrow">
						<i class="fas fa-chevron-left"></i>
					</div>
					<div id="calendar_area" class="fsp-calendar-area"></div>
					<div id="next_month" class="fsp-calendar-arrow">
						<i class="fas fa-chevron-right"></i>
					</div>
				</div>
				<div class="fsp-card-footer fsp-is-center">
					<button class="fsp-button" data-load-modal="add_schedule" type="button">
						<i class="fas fa-plus"></i>
						<span><?php echo fsp__( 'SCHEDULE' ); ?></span>
					</button>
				</div>
			</div>
		</div>
		<div class="fsp-col-12 fsp-col-md-6 fsp-calendar-right">
			<div class="fsp-card">
				<div class="fsp-card-title">
					<?php echo fsp__( 'SCHEDULED POSTS' ); ?>
				</div>
				<div class="fsp-card-body fsp-calendar-posts plan_posts_list"></div>
				<div class="fsp-calendar-emptiness">
					<img src="<?php echo Pages::asset( 'Schedules', 'img/empty-calendar.svg' ); ?>">
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Schedules', 'js/fsp-calendar.js' ); ?>' );
	} );
</script>