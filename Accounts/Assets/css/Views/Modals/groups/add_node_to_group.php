<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;

defined( 'MODAL' ) or exit;
?>

<link rel="stylesheet" href="<?php echo Pages::asset( 'Base', 'css/fsp-metabox.css' ); ?>">

<div class="fsp-modal-header">
	<input type="hidden" id="fspModalGroupId" value="<?php echo $fsp_params[ 'group_id' ]; ?>">
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
	<div id="fspAddAccount_1" class="fspAddAccount-step">
		<div class="fsp-form-group">
			<label><?php echo fsp__( 'Search account' ); ?></label>
			<div class="fsp-form-input-has-icon">
				<i class="fas fa-search"></i>
				<input autocomplete="off" class="fsp-form-input fsp-search-account" placeholder="<?php echo fsp__( 'Search' ); ?>">
			</div>
		</div>
		<div class="fsp-metabox-modal-accounts">
			<?php echo $fsp_params[ 'nodes' ]; ?>
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