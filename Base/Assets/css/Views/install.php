<?php

namespace FSPoster\App\Pages\Base\Views;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<div class="fsp-box-container">
	<div class="fsp-card fsp-box">
		<div class="fsp-box-info">
			<i class="fas fa-info-circle"></i><?php echo fsp__( 'Please activate the plugin.' ); ?>
		</div>
		<div class="fsp-box-logo">
			<img src="<?php echo Pages::asset( 'Base', 'img/logo.svg' ); ?>">
		</div>
		<div class="fsp-form-group">
			<input type="text" autocomplete="off" id="fspPurchaseKey" class="fsp-form-input" placeholder="<?php echo fsp__( 'Enter the purchase key' ); ?>">
		</div>
		<div class="fsp-form-group">
			<input type="email" autocomplete="off" id="fspEmail" class="fsp-form-input" placeholder="<?php echo fsp__( 'Enter your e-mail address (optional)' ); ?>">
		</div>
		<div class="fsp-form-group">
			<select id="fspMarketingStatistics" class="fsp-form-select">
				<option disabled selected><?php echo fsp__( 'Where did You find us?' ); ?></option>
				<?php echo Helper::fetchStatisticOptions(); ?>
			</select>
		</div>
		<div class="fsp-form-group">
			<button type="button" class="fsp-button" id="fspInstallBtn"><?php echo fsp__( 'ACTIVATE' ); ?></button>
		</div>
	</div>
</div>