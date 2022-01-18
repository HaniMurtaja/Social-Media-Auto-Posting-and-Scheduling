<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;

defined( 'MODAL' ) or exit;
?>

<div class="fsp-modal-header">
	<div class="fsp-modal-title">
		<div class="fsp-modal-title-icon">
			<i class="fas fa-plus"></i>
		</div>
		<div class="fsp-modal-title-text">
			<?php echo fsp__( 'Edit the group' ); ?>
		</div>
	</div>
	<div class="fsp-modal-close" data-modal-close="true">
		<i class="fas fa-times"></i>
	</div>
</div>
<div class="fsp-modal-body">
	<div class="fsp-modal-step">
		<div class="fsp-form-group">
			<input id="fspModalGroupId" type="hidden" value="<?php echo $fsp_params['id'] ?>">
			<label class="fsp-is-jb">
				<?php echo fsp__( 'Name' ); ?>
			</label>
			<input id="fspModalGroupName" class="fsp-form-input" type="text" value="<?php echo $fsp_params['name'] ?>">
		</div>
	</div>
</div>
<div class="fsp-modal-footer">
	<button class="fsp-button fsp-is-gray" data-modal-close="true"><?php echo fsp__( 'Cancel' ); ?></button>
	<button id="fspModalSaveButton" class="fsp-button"><?php echo fsp__( 'SAVE' ); ?></button>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Accounts', 'js/fsp-account-groups.js' ); ?>' );
	} );
</script>
