<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;

defined( 'MODAL' ) or exit;
?>

<div class="fsp-modal-header">
	<div class="fsp-modal-title">
		<div class="fsp-modal-title-icon">
			<i class="fas fa-edit"></i>
		</div>
		<div class="fsp-modal-title-text">
			<?php echo fsp__( 'Change account groups' ); ?>
		</div>
	</div>
	<div class="fsp-modal-close" data-modal-close="true">
		<i class="fas fa-times"></i>
	</div>
</div>
<div class="fsp-modal-body">
	<input type="hidden" id="fspModalNodeId" value="<?php echo $fsp_params[ 'id' ]; ?>">
	<input type="hidden" id="fspModalNodeType" value="<?php echo $fsp_params[ 'node_type' ]; ?>">
	<div class="fsp-modal-step">
		<div class="fsp-form-group">
			<select id="fspModalGroups" class="fsp-form-input select2-init" style="width: 100%" multiple>
				<?php
				foreach ( $fsp_params[ 'groups' ] as $group )
				{?>
					<option value="<?php echo $group['id']; ?>" selected><?php echo $group['name']; ?></option>
				<?php } ?>
			</select>
		</div>
	</div>
</div>
<div class="fsp-modal-footer">
	<button class="fsp-button fsp-is-gray" data-modal-close="true"><?php echo fsp__( 'Cancel' ); ?></button>
	<button id="fspModalSaveGroupsBtn" class="fsp-button"><?php echo fsp__( 'Save' ); ?></button>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Accounts', 'js/fsp-add-to-groups.js' ); ?>' );
	} );
</script>
