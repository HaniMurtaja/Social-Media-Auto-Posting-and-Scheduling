<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'MODAL' ) or exit;
?>

<div class="fsp-modal-header">
	<div class="fsp-modal-title">
		<div class="fsp-modal-title-icon">
			<i class="fab fa-facebook-f"></i>
		</div>
		<div class="fsp-modal-title-text">
			<?php echo fsp__( 'Update cookie' ); ?>
		</div>
	</div>
	<div class="fsp-modal-close" data-modal-close="true">
		<i class="fas fa-times"></i>
	</div>
</div>
<div class="fsp-modal-body">
	<p class="fsp-modal-p fsp-is-jb">
		<?php echo fsp__( 'Enter the new cookie value' ) ?>
		<a href="https://www.fs-poster.com/documentation/how-to-schedule-and-auto-publish-to-facebook-from-wordpress" target="_blank" class="fsp-tooltip" data-title="<?php echo fsp__( 'How to?' ); ?>">
			<i class="far fa-question-circle"></i>
		</a>
	</p>
	<input id="account_to_update" type="hidden" value="<?php echo $fsp_params; ?>">
	<div id="fspModalUpdateCookies" class="fsp-modal-step">
		<div class="fsp-form-group">
			<label><?php echo fsp__( 'The cookie' ); ?> xs</label>
			<div class="fsp-form-input-has-icon">
				<i class="fas fa-key"></i>
				<input id="fspCookie_xs" autocomplete="off" class="fsp-form-input" placeholder="<?php echo fsp__( 'Enter the cookie' ); ?> xs">
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
	<button id="fspModalUpdateCookiesButton" class="fsp-button"><?php echo fsp__( 'UPDATE' ); ?></button>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Accounts', 'js/fsp-accounts-fb.js' ); ?>' );
	} );

	fspConfig.standartAppURL = '<?php echo Helper::standartAppRedirectURL( 'fb' ); ?>';
</script>