<?php

namespace FSPoster\App\Pages\Base\Views;

use FSPoster\App\Providers\Pages;

defined( 'MODAL' ) or exit;
?>

<div class="fsp-modal-header">
	<div class="fsp-modal-title">
		<div class="fsp-modal-title-icon">
			<i class="fas fa-users"></i>
		</div>
		<div class="fsp-modal-title-text">
			<?php echo fsp__( 'Select accounts' ); ?>
		</div>
	</div>
	<div class="fsp-modal-close" data-modal-close="true">
		<i class="fas fa-times"></i>
	</div>
</div>

<div class="fsp-modal-body">
	<!-- Tab links -->
	<div id="fspAddAccount" class="fsp-modal-tabs">
		<div class="fsp-modal-tab" data-step="1"><?php echo fsp__( 'Accounts' ); ?></div>
		<div class="fsp-modal-tab" data-step="2"><?php echo fsp__( 'Groups' ); ?></div>
	</div>

	<!-- Accounts tab body -->
	<div id="fspAddAccount_1" class="fspAddAccount-step">
		<div class="fsp-form-group">
			<label><?php echo fsp__( 'Search account' ); ?></label>
			<div class="fsp-form-input-has-icon">
				<i class="fas fa-search"></i>
				<input autocomplete="off" class="fsp-form-input fsp-search-account" placeholder="<?php echo fsp__( 'Search' ); ?>">
			</div>
		</div>
		<div class="fsp-metabox-modal-accounts">
			<?php echo $fsp_params[ 'metabox_accounts' ]; ?>
		</div>
	</div>

	<!-- Groups tab body -->
	<div id="fspAddAccount_2" class="fspAddAccount-step">
		<div class="fsp-form-group">
			<label><?php echo fsp__( 'Search group' ); ?></label>
			<div class="fsp-form-input-has-icon">
				<i class="fas fa-search"></i>
				<input autocomplete="off" class="fsp-form-input fsp-search-account" placeholder="<?php echo fsp__( 'Search' ); ?>">
			</div>
		</div>
		<div class="fsp-metabox-modal-accounts">
			<?php echo $fsp_params[ 'metabox_groups' ]; ?>
		</div>
	</div>
</div>

<div class="fsp-modal-footer">
	<button class="fsp-button" data-modal-close="true"><?php echo fsp__( 'CLOSE' ); ?></button>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Base', 'js/fsp-tabs.js' ); ?>' );
	} );
</script>