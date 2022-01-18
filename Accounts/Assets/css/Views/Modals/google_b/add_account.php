<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'MODAL' ) or exit;
?>

<div class="fsp-modal-header">
	<div class="fsp-modal-title">
		<div class="fsp-modal-title-icon">
			<i class="fab fa-google"></i>
		</div>
		<div class="fsp-modal-title-text">
			<?php echo fsp__( 'Add an account' ); ?>
		</div>
	</div>
	<div class="fsp-modal-close" data-modal-close="true">
		<i class="fas fa-times"></i>
	</div>
</div>
<div class="fsp-modal-body">
	<p class="fsp-modal-p fsp-is-jb">
		<?php echo fsp__( 'Select an authorization method to add a new Google My Business account' ); ?>
		<a href="https://www.fs-poster.com/documentation/fs-poster-auto-publish-wordpress-posts-to-google-my-business" target="_blank" class="fsp-tooltip" data-title="<?php echo fsp__( 'How to?' ); ?>">
			<i class="far fa-question-circle"></i>
		</a>
	</p>
	<div class="fsp-modal-options">
		<div class="fsp-modal-option fsp-is-selected" data-step="1">
			<div class="fsp-modal-option-image">
				<img src="<?php echo Pages::asset( 'Accounts', 'img/android.svg' ); ?>">
			</div>
			<span class="fsp-tooltip" data-title="<?php echo fsp__( 'Recommended method' ); ?>"><?php echo fsp__( 'App method' ); ?></span>
		</div>
		<div class="fsp-modal-option" data-step="2">
			<div class="fsp-modal-option-image">
				<img src="<?php echo Pages::asset( 'Accounts', 'img/rocket.svg' ); ?>">
			</div> <?php echo fsp__( 'Cookie method' ); ?>
		</div>
	</div>
	<div id="fspModalStep_1" class="fsp-modal-step">
		<div class="fsp-form-group">
			<label class="fsp-is-jb">
				<?php echo fsp__( 'Select an App' ); ?>
				<a href="https://www.fs-poster.com/documentation/fs-poster-auto-publish-wordpress-posts-to-google-my-business" target="_blank" class="fsp-tooltip" data-title="<?php echo fsp__( 'How to?' ); ?>">
					<i class="far fa-question-circle"></i>
				</a>
			</label>
			<select class="fsp-form-select" id="fspModalAppSelector">
				<?php foreach ( $fsp_params[ 'applications' ] as $app ) { ?>
					<option value="<?php echo $app[ 'id' ]; ?>" data-is-standart="<?php echo ( int ) $app[ 'is_standart' ]; ?>"><?php echo esc_html( $app[ 'name' ] ); ?></option>
				<?php }
				if ( empty( $fsp_params[ 'applications' ] ) )
				{ ?>
					<option disabled><?php echo fsp__( 'There isn\'t a Google My Business App!' ); ?></option>
				<?php } ?>
			</select>
		</div>
	</div>
	<div id="fspModalStep_2" class="fsp-modal-step fsp-hide">
		<div class="fsp-form-group">
			<label class="fsp-is-jb">
				<?php echo fsp__( 'Enter the cookie' ); ?> SID
				<a href="https://www.fs-poster.com/documentation/fs-poster-auto-publish-wordpress-posts-to-google-my-business" target="_blank" class="fsp-tooltip" data-title="<?php echo fsp__( 'How to?' ); ?>">
					<i class="far fa-question-circle"></i>
				</a>
			</label>
			<div class="fsp-form-input-has-icon">
				<i class="far fa-copy"></i>
				<input id="fspCookie_sid" autocomplete="off" class="fsp-form-input" placeholder="<?php echo fsp__( 'Enter the cookie' ); ?> SID">
			</div>
		</div>
		<div class="fsp-form-group">
			<label><?php echo fsp__( 'Enter the cookie' ); ?> HSID</label>
			<div class="fsp-form-input-has-icon">
				<i class="far fa-copy"></i>
				<input id="fspCookie_hsid" autocomplete="off" class="fsp-form-input" placeholder="<?php echo fsp__( 'Enter the cookie' ); ?> HSID">
			</div>
		</div>
		<div class="fsp-form-group">
			<label><?php echo fsp__( 'Enter the cookie' ); ?> SSID</label>
			<div class="fsp-form-input-has-icon">
				<i class="far fa-copy"></i>
				<input id="fspCookie_ssid" autocomplete="off" class="fsp-form-input" placeholder="<?php echo fsp__( 'Enter the cookie' ); ?> SSID">
			</div>
		</div>
	</div>
	<div class="fsp-form-checkbox-group">
		<input id="fspUseProxy" type="checkbox" class="fsp-form-checkbox">
		<label for="fspUseProxy">
			<?php echo fsp__( 'Use a proxy' ); ?>
		</label>
		<span class="fsp-tooltip" data-title="<?php echo fsp__( 'Optional field. Supported proxy formats: https://127.0.0.1:8888 or https://user:pass@127.0.0.1:8888' ); ?>"><i class="far fa-question-circle"></i></span>
	</div>
	<div id="fspProxyContainer" class="fsp-form-group fsp-hide fsp-proxy-container">
		<div class="fsp-form-input-has-icon">
			<i class="fas fa-globe"></i>
			<input id="fspProxy" autocomplete="off" class="fsp-form-input fsp-proxy" placeholder="<?php echo fsp__( 'Enter a proxy address' ); ?>">
		</div>
	</div>
</div>
<div class="fsp-modal-footer">
	<button class="fsp-button fsp-is-gray" data-modal-close="true"><?php echo fsp__( 'Cancel' ); ?></button>
	<button id="fspModalAddButton" class="fsp-button"><?php echo fsp__( 'ADD' ); ?></button>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Accounts', 'js/fsp-accounts-google-b.js' ); ?>' );
	} );

	fspConfig.standartAppURL = '<?php echo Helper::standartAppRedirectURL( 'google_b' ); ?>';
</script>