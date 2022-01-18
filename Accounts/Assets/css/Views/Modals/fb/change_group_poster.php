<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;

defined( 'MODAL' ) or exit;
?>

<div class="fsp-modal-header">
	<div class="fsp-modal-title">
		<div class="fsp-modal-title-icon">
			<i class="far fa-user"></i>
		</div>
		<div class="fsp-modal-title-text">
			<?php echo fsp__( 'Post as a Facebook Page in Groups' ); ?>
		</div>
	</div>
	<div class="fsp-modal-close" data-modal-close="true">
		<i class="fas fa-times"></i>
	</div>
</div>
<div class="fsp-modal-body">
	<div class="fsp-modal-step">
		<input type="hidden" value="<?php echo $fsp_params[ 'group_id' ]; ?>" id="fspjs-group-id">
		<div class="fsp-form-group">
			<label>
				<?php echo fsp__( 'Select a Facebook page to share your posts in Facebook groups on behalf of the page. Note that the page has to be <a href="https://www.fs-poster.com/documentation/commonly-encountered-issues#issue4" target="_blank">linked to the group</a>; otherwise, the post will fail.', [], FALSE ); ?>
			</label>
			<select id="fspjs-page-id" class="fsp-form-select">
				<?php foreach ( $fsp_params[ 'pages' ] as $page ) { ?>
					<option value="<?php echo $page[ 'id' ]; ?>" <?php echo $page[ 'selected' ] ? 'selected' : ''; ?>><?php echo esc_html( $page[ 'name' ] ); ?></option>
				<?php } ?>
			</select>
		</div>
	</div>
</div>
<div class="fsp-modal-footer">
	<button class="fsp-button fsp-is-gray" data-modal-close="true"><?php echo fsp__( 'CANCEL' ); ?></button>
	<button id="fspjs-save" class="fsp-button"><?php echo fsp__( 'SAVE' ); ?></button>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Accounts', 'js/fsp-change-fb-poster.js' ); ?>' );
	} );
</script>
