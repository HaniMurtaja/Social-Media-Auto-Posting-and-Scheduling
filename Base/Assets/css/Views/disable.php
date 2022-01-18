<?php

namespace FSPoster\App\Pages\Base\Views;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<div class="fsp-box-container">
	<div class="fsp-card fsp-box">
		<div class="fsp-box-info">
			<i class="fas fa-info-circle"></i><?php echo fsp__( 'Your plugin is disabled. Please activate the plugin.' ); ?>
		</div>
		<div class="fsp-box-logo">
			<img src="<?php echo Pages::asset( 'Base', 'img/logo.svg' ); ?>">
		</div>
		<div class="fsp-form-group">
			<label><?php echo fsp__( 'Reason: %s', [ Helper::getOption( 'plugin_alert' ) ], FALSE ); ?></label>
		</div>
		<div class="fsp-form-group">
			<input type="text" id="fspPurchaseKey" autocomplete="off" class="fsp-form-input" placeholder="<?php echo fsp__( 'Enter the purchase key' ); ?>">
		</div>
		<div class="fsp-form-group">
			<button type="button" class="fsp-button" id="fspReactivateBtn"><?php echo fsp__( 'RE-ACTIVATE' ); ?></button>
		</div>
	</div>
</div>