<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;

defined( 'MODAL' ) or exit;
?>

<div class="fsp-modal-header">
	<div class="fsp-modal-title">
		<div class="fsp-modal-title-icon">
			<i class="fas fa-user"></i>
		</div>
		<div class="fsp-modal-title-text">
			<?php echo fsp__( 'Define conditions for the account' ); ?>
		</div>
	</div>
	<div class="fsp-modal-close" data-modal-close="true">
		<i class="fas fa-times"></i>
	</div>
</div>
<div class="fsp-modal-body">
	<input type="hidden" id="fspActivateURL" value="<?php echo $fsp_params[ 'ajaxUrl' ]; ?>">
	<input type="hidden" id="fspActivateID" value="<?php echo $fsp_params[ 'id' ]; ?>">
	<input type="hidden" id="fspActivateIDS" value="<?php echo esc_html( json_encode( $fsp_params[ 'ids' ] ) ); ?>">
	<div class="fsp-modal-step">
		<div class="fsp-form-group">
			<select id="fspCategories" class="fsp-form-input select2-init" style="width: 100%" multiple>
				<?php
				foreach ( $fsp_params[ 'categories' ] as $term_id )
				{
					$term = get_term( ( int ) $term_id ); ?>

					<option value="<?php echo ( int ) $term_id; ?>" selected><?php echo esc_html( $term->name ); ?></option>
				<?php } ?>
			</select>
		</div>
	</div>
	<div class="fsp-modal-options">
		<div class="fsp-modal-option <?php echo $fsp_params[ 'filter_type' ] === 'in' ? 'fsp-is-selected' : ''; ?>" data-name="in">
			<?php echo fsp__( 'Share only the posts of the selected categories, tags, etc...' ); ?>
		</div>
		<div class="fsp-modal-option <?php echo $fsp_params[ 'filter_type' ] === 'ex' ? 'fsp-is-selected' : ''; ?>" data-name="ex">
			<?php echo fsp__( 'Do not share the posts of the selected categories, tags, etc...' ); ?>
		</div>
	</div>
	<?php if ( ( current_user_can( 'administrator' ) || defined( 'FS_POSTER_IS_DEMO' ) ) ) { ?>
		<div class="fsp-form-checkbox-group">
			<input id="fspActivateConditionallyForAll" type="checkbox" class="fsp-form-checkbox" <?php echo ( $fsp_params[ 'for_all' ] == 1 ? 'checked' : '' ); ?>>
			<label for="fspActivateConditionallyForAll">
				<?php echo fsp__( 'Activate for all users' ); ?>
			</label>
		</div>
	<?php } ?>
</div>
<div class="fsp-modal-footer">
	<button class="fsp-button fsp-is-gray" data-modal-close="true"><?php echo fsp__( 'Cancel' ); ?></button>
	<button id="fspModalActivateBtn" class="fsp-button"><?php echo fsp__( 'ACTIVATE' ); ?></button>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Accounts', 'js/fsp-accounts-activate.js' ); ?>' );
	} );
</script>
