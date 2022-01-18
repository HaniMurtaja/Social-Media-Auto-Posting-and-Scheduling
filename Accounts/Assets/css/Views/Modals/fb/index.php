<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<div>
	<?php foreach ( $fsp_params[ 'accounts_list' ] as $account_info )
	{
		$node_list = $account_info[ 'node_list' ];
		$username  = get_userdata( $account_info[ 'user_id' ] ) !== FALSE ? get_userdata( $account_info[ 'user_id' ] )->nickname : fsp__( 'deleted user' );
		?>
		<div class="fsp-account">
			<div class="fsp-card fsp-account-item" data-id="<?php echo $account_info[ 'id' ]; ?>" data-type="account" data-active="<?php echo isset( $account_info[ 'is_active' ] ) && ! empty( $account_info[ 'is_active' ] ) ? 1 : 0; ?>" data-hidden="<?php echo $account_info[ 'is_hidden' ]; ?>" data-failed="<?php echo $account_info[ 'status' ] === 'error' ? 1 : 0; ?>" data-cookie="<?php echo isset( $account_info[ 'options' ] ) ? 1 : 0; ?>">
				<div class="fsp-account-inline">
					<?php echo ! empty( $node_list ) ? '<div class="fsp-account-caret fsp-is-rotated"><i class="fas fa-angle-up"></i></div>' : ''; ?>
					<div class="fsp-account-image">
						<img src="<?php echo Helper::profilePic( $account_info ); ?>" onerror="FSPoster.no_photo( this );">
					</div>
					<div class="fsp-account-name">
						<?php echo esc_html( $account_info[ 'name' ] ); ?>
						<span><?php echo $account_info[ 'ownpages' ]; ?> <?php echo fsp__( 'pages' ); ?>&emsp;<?php echo $account_info[ 'groups' ]; ?> <?php echo fsp__( 'groups' ); ?></span>
					</div>
				</div>
				<div class="fsp-account-inline fsp-is-buttons-container">
					<?php if ( $account_info[ 'status' ] === 'error' ) { ?>
						<div class="fsp-account-failed fsp-tooltip" data-title="<?php echo esc_html( $account_info[ 'error_msg' ] ); ?>">
							<i class="fas fa-exclamation-triangle"></i>
						</div>
					<?php } ?>
					<div class="fsp-account-is-public fsp-tooltip <?php echo ! $account_info[ 'is_public' ] ? 'fsp-hide' : ''; ?>" data-title="<?php echo fsp__( 'It\'s public for all the users by %s.', [ $username ] ); ?>">
						<i class="far fa-star"></i>
					</div>
					<?php if ( ! empty( $account_info[ 'proxy' ] ) ) { ?>
						<div class="fsp-account-proxy fsp-tooltip" data-title="<?php echo fsp__( 'Proxy' ); ?>: <?php echo esc_html( $account_info[ 'proxy' ] ); ?>">
							<i class="fas fa-globe"></i>
						</div>
					<?php } ?>
					<div class="fsp-account-link fspjs-refetch-account" data-id="<?php echo $account_info[ 'id' ]; ?>">
						<i class="fas fa-sync fsp-tooltip" data-title="<?php echo fsp__( 'Re-fetch account' ); ?>"></i>
					</div>
					<?php if ( isset( $account_info[ 'options' ] ) && ! empty( $account_info[ 'options' ] ) ) { ?>
						<div class="fsp-account-checkbox">
							<i class="<?php echo empty( $account_info[ 'is_active' ] ) ? 'far fa-check-square' : ( 'fas fa-check-square fsp-is-checked' . ( $account_info[ 'is_active' ] === 'no' ? '' : '-conditionally' ) ); ?>"></i>
						</div>
						<a class="fsp-account-link fsp-tooltip" href="<?php echo Helper::profileLink( $account_info ); ?>" data-title="<?php echo fsp__( 'Profile link' ); ?>" target="_blank">
							<i class="fas fa-external-link-alt"></i>
						</a>
					<?php } ?>
					<div class="fsp-account-more">
						<i class="fas fa-ellipsis-h"></i>
					</div>
				</div>
				<div class="fsp-account-inline fsp-is-select-container">
					<input type="checkbox" class="fsp-form-checkbox fsp-account-selectbox" data-id="<?php echo $account_info[ 'id' ]; ?>" data-type="account">
				</div>
			</div>

			<div class="fsp-account-nodes-container">
				<div class="fsp-account-nodes">
					<?php foreach ( $node_list as $node_info ) { ?>
						<div class="fsp-card fsp-account-item" data-name="<?php echo $node_info[ 'name' ]; ?>" data-id="<?php echo $node_info[ 'id' ]; ?>" data-type="community" data-active="<?php echo isset( $node_info[ 'is_active' ] ) && ! empty( $node_info[ 'is_active' ] ) ? 1 : 0; ?>" data-hidden="<?php echo $node_info[ 'is_hidden' ]; ?>" data-failed="<?php echo $account_info[ 'status' ] === 'error' ? 1 : 0; ?>">
							<div class="fsp-account-inline">
								<div class="fsp-account-image">
									<img src="<?php echo Helper::profilePic( $node_info ); ?>" onerror="FSPoster.no_photo( this );">
								</div>
								<div class="fsp-account-name">
									<?php echo esc_html( $node_info[ 'name' ] ); ?>
									<span><?php echo ucfirst( esc_html( $node_info[ 'node_type' ] ) ); ?></span>
								</div>
							</div>
							<div class="fsp-account-inline fsp-is-buttons-container">
								<div class="fsp-account-is-public fsp-tooltip <?php echo ! $node_info[ 'is_public' ] ? 'fsp-hide' : ''; ?>" data-title="<?php echo fsp__( 'It\'s public for all the users by %s.', [ $username ] ); ?>">
									<i class="far fa-star"></i>
								</div>
								<?php if ( ( ! isset( $account_info[ 'options' ] ) || empty( $account_info[ 'options' ] ) ) && $node_info[ 'node_type' ] === 'group' ) { ?>
									<div class="fsp-account-link" data-load-modal="change_fb_group_poster" data-parameter-account_id="<?php echo $account_info[ 'id' ]; ?>" data-parameter-group_id="<?php echo $node_info[ 'id' ]; ?>">
										<i class="far fa-user fsp-tooltip" data-title="<?php echo fsp__( 'Posts as %s. Click to change.', [ isset( $node_info[ 'poster_name' ] ) ? esc_html( $node_info[ 'poster_name' ] ) : esc_html( $account_info[ 'name' ] ) ] ); ?>"></i>
									</div>
								<?php } ?>
								<div class="fsp-account-checkbox">
									<i class="<?php echo empty( $node_info[ 'is_active' ] ) ? 'far fa-check-square' : ( 'fas fa-check-square fsp-is-checked' . ( $node_info[ 'is_active' ] === 'no' ? '' : '-conditionally' ) ); ?>"></i>
								</div>
								<a class="fsp-account-link fsp-tooltip" href="<?php echo Helper::profileLink( $node_info ); ?>" data-title="<?php echo fsp__( 'Profile link' ); ?>" target="_blank">
									<i class="fas fa-external-link-alt"></i>
								</a>
								<div class="fsp-account-more">
									<i class="fas fa-ellipsis-h"></i>
								</div>
							</div>
							<div class="fsp-account-inline fsp-is-select-container">
								<input type="checkbox" class="fsp-form-checkbox fsp-account-selectbox" data-id="<?php echo $node_info[ 'id' ]; ?>" data-type="node">
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	<?php } ?>

	<?php if ( ! empty( $fsp_params[ 'public_communities' ] ) ) { ?>
		<div class="fsp-account">
			<div class="fsp-card fsp-account-item">
				<div class="fsp-account-inline">
					<div class="fsp-account-caret fsp-is-rotated">
						<i class="fas fa-angle-up"></i>
					</div>
					<div class="fsp-account-name">
						<?php echo fsp__( 'Public communities' ); ?>
						<span><?php echo count( $fsp_params[ 'public_communities' ] ); ?>&nbsp;<?php echo fsp__( 'communities' ); ?></span>
					</div>
				</div>
				<div class="fsp-account-inline"></div>
			</div>
			<div class="fsp-account-nodes-container">
				<div class="fsp-account-nodes">
					<?php foreach ( $fsp_params[ 'public_communities' ] as $node_info )
					{
						$username = get_userdata( $node_info[ 'user_id' ] ) !== FALSE ? get_userdata( $node_info[ 'user_id' ] )->nickname : fsp__( 'deleted user' ); ?>
						<div class="fsp-card fsp-account-item" data-id="<?php echo $node_info[ 'id' ]; ?>" data-type="community" data-active="<?php echo isset( $node_info[ 'is_active' ] ) && ! empty( $node_info[ 'is_active' ] ) ? 1 : 0; ?>" data-hidden="<?php echo $node_info[ 'is_hidden' ]; ?>" data-failed="0">
							<div class="fsp-account-inline">
								<div class="fsp-account-image">
									<img src="<?php echo Helper::profilePic( $node_info ); ?>" onerror="FSPoster.no_photo( this );">
								</div>
								<div class="fsp-account-name">
									<?php echo esc_html( $node_info[ 'name' ] ); ?>
									<span><?php echo ucfirst( esc_html( $node_info[ 'node_type' ] ) ); ?></span>
								</div>
							</div>
							<div class="fsp-account-inline fsp-is-buttons-container">
								<div class="fsp-account-is-public fsp-tooltip <?php echo ! $node_info[ 'is_public' ] ? 'fsp-hide' : ''; ?>" data-title="<?php echo fsp__( 'It\'s public for all the users by %s.', [ $username ] ); ?>">
									<i class="far fa-star"></i>
								</div>
								<div class="fsp-account-checkbox">
									<i class="<?php echo empty( $node_info[ 'is_active' ] ) ? 'far fa-check-square' : ( 'fas fa-check-square fsp-is-checked' . ( $node_info[ 'is_active' ] === 'no' ? '' : '-conditionally' ) ); ?>"></i>
								</div>
								<a class="fsp-account-link fsp-tooltip" href="<?php echo Helper::profileLink( $node_info ); ?>" data-title="<?php echo fsp__( 'Profile link' ); ?>" target="_blank">
									<i class="fas fa-external-link-alt"></i>
								</a>
								<div class="fsp-account-more">
									<i class="fas fa-ellipsis-h"></i>
								</div>
							</div>
							<div class="fsp-account-inline fsp-is-select-container">
								<input type="checkbox" class="fsp-form-checkbox fsp-account-selectbox" data-id="<?php echo $node_info[ 'id' ]; ?>" data-type="node">
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	<?php } ?>

	<div class="fsp-card fsp-emptiness <?php echo( empty( $fsp_params[ 'accounts_list' ] ) && empty( $fsp_params[ 'public_communities' ] ) ? '' : 'fsp-hide' ); ?>">
		<div class="fsp-emptiness-image">
			<img src="<?php echo Pages::asset( 'Base', 'img/empty.svg' ); ?>">
		</div>
		<div class="fsp-emptiness-text">
			<?php echo fsp__( 'No %s found!', [ $fsp_params[ 'err_text' ] ] ); ?>
		</div>
		<div class="fsp-emptiness-button">
			<button class="fsp-button fsp-accounts-add-button">
				<i class="fas fa-plus"></i>
				<span><?php echo $fsp_params[ 'button_text' ]; ?></span>
			</button>
		</div>
	</div>
</div>

<?php
$count = count( $fsp_params[ 'accounts_list' ] );

if ( ! empty( $fsp_params[ 'public_communities' ] ) )
{
	$count += 1;
}
?>

<script>
	FSPObject.modalURL = 'add_fb_account';
	FSPObject.editModalURL = 'edit_fb_account';
	FSPObject.accountsCount = <?php echo $count; ?>;
</script>