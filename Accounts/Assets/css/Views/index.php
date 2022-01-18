<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;

defined( 'ABSPATH' ) or exit;
?>

<div id="fspActivateMenu" class="fsp-dropdown">
	<div id="fspActivatesDiv">
		<div id="fspActivate" class="fsp-dropdown-item"><?php echo fsp__( 'Activate' ); ?></div>
		<?php if ( ( current_user_can( 'administrator' ) || defined( 'FS_POSTER_IS_DEMO' ) ) ) { ?>
			<div id="fspActivateForAll" class="fsp-dropdown-item"><?php echo fsp__( 'Activate for all users' ); ?></div>
		<?php } ?>
	</div>
	<div id="fspActivateConditionally" class="fsp-dropdown-item"><?php echo fsp__( 'Activate (condition)' ); ?></div>
	<div id="fspDeactivatesDiv">
		<div id="fspDeactivate" class="fsp-dropdown-item"><?php echo fsp__( 'Deactivate' ); ?></div>
		<?php if ( ( current_user_can( 'administrator' ) || defined( 'FS_POSTER_IS_DEMO' ) ) ) { ?>
			<div id="fspDeactivateForAll" class="fsp-dropdown-item"><?php echo fsp__( 'Deactivate for all users' ); ?></div>
		<?php } ?>
	</div>
</div>

<div id="fspMoreMenu" class="fsp-dropdown">
	<div class="fsp-dropdown-item fsp-make-public" data-type="public"><?php echo fsp__( 'Make public' ); ?></div>
	<div class="fsp-dropdown-item fsp-make-public" data-type="private"><?php echo fsp__( 'Make private' ); ?></div>
	<div id="fspDelete" class="fsp-dropdown-item"><?php echo fsp__( 'Delete' ); ?></div>
	<div class="fsp-dropdown-item fspjs-hide-account" data-type="hide"><?php echo fsp__( 'Hide' ); ?></div>
	<div class="fsp-dropdown-item fspjs-hide-account" data-type="unhide"><?php echo fsp__( 'Unhide' ); ?></div>
	<div id="fsp-update-cookies" class="fsp-dropdown-item"><?php echo fsp__( 'Update cookie(s)' ); ?></div>
	<div class="fsp-dropdown-item fsp-custom-settings"><?php echo fsp__( 'Custom settings' ); ?></div>
	<div class="fsp-dropdown-item fsp-add-to-groups"><?php echo fsp__( 'Add to group' ); ?></div>
	<?php if ( ! $fsp_params[ 'show_accounts' ] ) { ?>
		<div class="fsp-dropdown-item fsp-remove-from-group"><?php echo fsp__( 'Remove from group' ); ?></div> <?php } ?>
</div>

<div id="fspGroupMoreMenu" class="fsp-dropdown">
	<div class="fsp-dropdown-item fsp-group-add"><?php echo fsp__( 'Add accounts' ); ?></div>
	<div class="fsp-dropdown-item fsp-group-schedule"><?php echo fsp__( 'Schedule' ); ?></div>
	<div class="fsp-dropdown-item fsp-group-edit"><?php echo fsp__( 'Edit' ); ?></div>
	<div class="fsp-dropdown-item fsp-group-delete"><?php echo fsp__( 'Delete' ); ?></div>
</div>

<div class="fsp-row">
	<div class="fsp-col-12 fsp-title">
		<div class="fsp-title-text">
			<?php echo fsp__( 'Accounts' ); ?>
			<span class="fsp-title-count"><?php echo $fsp_params[ 'accounts_count' ][ 'total' ]; ?></span>
		</div>
		<div class="fsp-title-button">
			<div class="fsp-form-input-has-icon fsp-node-search">
				<i class="fas fa-search"></i>
				<input id="fsp-node-search-input" autocomplete="off" class="fsp-form-input fsp-search-account" placeholder="Search">
			</div>
			<button id="fspSelectMode" data-mode="ui" class="fsp-button fsp-is-info">
				<i class="far fa-clone"></i>
				<span><?php echo fsp__( 'BULK ACTION' ); ?></span>
			</button>
			<button class="fsp-button fsp-accounts-add-button" <?php echo ! $fsp_params[ 'show_accounts' ] ? "data-load-modal='create_group'" : '' ?> >
				<i class="fas fa-plus"></i>
				<span><?php echo $fsp_params[ 'button_text' ]; ?></span>
			</button>
		</div>
	</div>

	<div class="fsp-col-12 fsp-row fsp-accounts-toolbar">
		<div class="fsp-layout-left fsp-col-12 fsp-col-md-5 fsp-col-lg-4">
			<div class="fsp-account-group-btns">
				<a class="<?php echo $fsp_params[ 'show_accounts' ] ? 'active' : '' ?>" href="?page=fs-poster-accounts&view=accounts">Social media</a>
				<a class="<?php echo ! $fsp_params[ 'show_accounts' ] ? 'active' : '' ?>" href="?page=fs-poster-accounts&view=groups">Groups</a>
			</div>
		</div>

		<div class="fsp-layout-right fsp-col-12 fsp-col-md-7 fsp-col-lg-8">
			<div class="fsp-accounts-filter">
				<button id="fspCollapseAccounts" class="fsp-button fsp-is-info fsp-account-collapse">
					<i>
						<img src="<?php echo Pages::asset( 'Accounts', 'img/collapse.svg' ); ?>">
					</i>
					<span><?php echo fsp__( 'COLLAPSE ALL' ); ?></span>
				</button>

				<div class="fsp-title-selector">
					<select id="fspAccountsFilterSelector" class="fsp-form-select">
						<option value="all" <?php echo $fsp_params[ 'filter' ] === 'all' ? 'selected' : ''; ?>><?php echo fsp__( 'All accounts' ); ?></option>
						<option value="active" <?php echo $fsp_params[ 'filter' ] === 'active' ? 'selected' : ''; ?>><?php echo fsp__( 'Active accounts' ); ?></option>
						<option value="inactive" <?php echo $fsp_params[ 'filter' ] === 'inactive' ? 'selected' : ''; ?>><?php echo fsp__( 'Deactive accounts' ); ?></option>
						<option value="visible" <?php echo $fsp_params[ 'filter' ] === 'visible' ? 'selected' : ''; ?>><?php echo fsp__( 'Visible accounts' ); ?></option>
						<option value="hidden" <?php echo $fsp_params[ 'filter' ] === 'hidden' ? 'selected' : ''; ?>><?php echo fsp__( 'Hidden accounts' ); ?></option>
						<option value="failed" <?php echo $fsp_params[ 'filter' ] === 'failed' ? 'selected' : ''; ?>><?php echo fsp__( 'Failed accounts' ); ?></option>
					</select>
				</div>
			</div>

			<div class="fsp-accounts-actions">
				<div id="fspSelectedAccountsActionContainer" class="fsp-title-selector fsp-hide">
					<select id="fspSelectedAccountsAction" class="fsp-form-select" disabled>
						<option value="" selected disabled><?php echo fsp__( 'Select an action (0)' ); ?></option>
						<option value="public" data-text="<?php echo fsp__( 'Do you want to make the selected accounts public?' ); ?>"><?php echo fsp__( 'Make public' ); ?></option>
						<option value="private" data-text="<?php echo fsp__( 'Do you want to make the selected accounts private?' ); ?>"><?php echo fsp__( 'Make private' ); ?></option>
						<option value="activate" data-text="<?php echo fsp__( 'Do you want to activate the selected accounts?' ); ?>"><?php echo fsp__( 'Activate' ); ?></option>
						<?php if ( ( current_user_can( 'administrator' ) || defined( 'FS_POSTER_IS_DEMO' ) ) ) { ?>
							<option value="activate_all" data-text="<?php echo fsp__( 'Do you want to activate the selected accounts for all users?' ); ?>"><?php echo fsp__( 'Activate for all users' ); ?></option>
						<?php } ?>
						<option value="activate_condition" data-text=""><?php echo fsp__( 'Activate (condition)' ); ?></option>
						<option value="deactivate" data-text="<?php echo fsp__( 'Do you want to deactivate the selected accounts?' ); ?>"><?php echo fsp__( 'Deactivate' ); ?></option>
						<?php if ( ( current_user_can( 'administrator' ) || defined( 'FS_POSTER_IS_DEMO' ) ) ) { ?>
							<option value="deactivate_all" data-text="<?php echo fsp__( 'Do you want to deactivate the selected accounts for all users?' ); ?>"><?php echo fsp__( 'Deactivate for all users' ); ?></option>
						<?php } ?>
						<option value="delete" data-text="<?php echo fsp__( 'Are you sure you want to delete the selected accounts?' ); ?>"><?php echo fsp__( 'Delete' ); ?></option>
						<option value="hide" data-text="<?php echo fsp__( 'Do you want to hide the selected accounts?' ); ?>"><?php echo fsp__( 'Hide' ); ?></option>
						<option value="unhide" data-text="<?php echo fsp__( 'Do you want to unhide the selected accounts?' ); ?>"><?php echo fsp__( 'Unhide' ); ?></option>
					</select>
				</div>
				<div class="fsp-account-inline fsp-is-select-container">
					<input id="fspToggleSelectboxes" type="checkbox" class="fsp-form-checkbox">
				</div>
			</div>
		</div>
	</div>

	<div class="fsp-col-12 fsp-row">
		<div class="fsp-layout-left fsp-col-12 fsp-col-md-5 fsp-col-lg-4 <?php echo ( ! $fsp_params[ 'show_accounts' ] && empty( $fsp_params[ 'groups' ] ) ) ? 'fsp-hide' : '' ?>">
			<div class="fsp-card">
				<?php
				if ( $fsp_params[ 'show_accounts' ] )
				{ ?>
					<div class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'fb' ? 'fsp-is-active' : '' ); ?>" data-component="fb">
						<div class="fsp-tab-title">
							<i class="fab fa-facebook-f fsp-tab-title-icon"></i>
							<span class="fsp-tab-title-text">Facebook</span>
						</div>
						<div class="fsp-tab-badges <?php echo( $fsp_params[ 'accounts_count' ][ 'fb' ][ 'active' ] > 0 ? 'fsp-has-active-accounts' : '' ); ?>">
							<?php echo( $fsp_params[ 'accounts_count' ][ 'fb' ][ 'failed' ] > 0 ? '<span class="fsp-tab-failed">' . $fsp_params[ 'accounts_count' ][ 'fb' ][ 'failed' ] . '</span>' : '' ); ?>
							<span class="fsp-tab-all"><?php echo $fsp_params[ 'accounts_count' ][ 'fb' ][ 'total' ]; ?></span>
						</div>
					</div>
					<div class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'twitter' ? 'fsp-is-active' : '' ); ?>" data-component="twitter">
						<div class="fsp-tab-title">
							<i class="fab fa-twitter fsp-tab-title-icon"></i>
							<span class="fsp-tab-title-text">Twitter</span>
						</div>
						<div class="fsp-tab-badges <?php echo( $fsp_params[ 'accounts_count' ][ 'twitter' ][ 'active' ] > 0 ? 'fsp-has-active-accounts' : '' ); ?>">
							<?php echo( $fsp_params[ 'accounts_count' ][ 'twitter' ][ 'failed' ] > 0 ? '<span class="fsp-tab-failed">' . $fsp_params[ 'accounts_count' ][ 'twitter' ][ 'failed' ] . '</span>' : '' ); ?>
							<span class="fsp-tab-all"><?php echo $fsp_params[ 'accounts_count' ][ 'twitter' ][ 'total' ]; ?></span>
						</div>
					</div>
					<div class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'instagram' ? 'fsp-is-active' : '' ); ?>" data-component="instagram">
						<div class="fsp-tab-title">
							<i class="fab fa-instagram fsp-tab-title-icon"></i>
							<span class="fsp-tab-title-text">Instagram</span>
						</div>
						<div class="fsp-tab-badges <?php echo( $fsp_params[ 'accounts_count' ][ 'instagram' ][ 'active' ] > 0 ? 'fsp-has-active-accounts' : '' ); ?>">
							<?php echo( $fsp_params[ 'accounts_count' ][ 'instagram' ][ 'failed' ] > 0 ? '<span class="fsp-tab-failed">' . $fsp_params[ 'accounts_count' ][ 'instagram' ][ 'failed' ] . '</span>' : '' ); ?>
							<span class="fsp-tab-all"><?php echo $fsp_params[ 'accounts_count' ][ 'instagram' ][ 'total' ]; ?></span>
						</div>
					</div>
					<div class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'linkedin' ? 'fsp-is-active' : '' ); ?>" data-component="linkedin">
						<div class="fsp-tab-title">
							<i class="fab fa-linkedin-in fsp-tab-title-icon"></i>
							<span class="fsp-tab-title-text">LinkedIn</span>
						</div>
						<div class="fsp-tab-badges <?php echo( $fsp_params[ 'accounts_count' ][ 'linkedin' ][ 'active' ] > 0 ? 'fsp-has-active-accounts' : '' ); ?>">
							<?php echo( $fsp_params[ 'accounts_count' ][ 'linkedin' ][ 'failed' ] > 0 ? '<span class="fsp-tab-failed">' . $fsp_params[ 'accounts_count' ][ 'linkedin' ][ 'failed' ] . '</span>' : '' ); ?>
							<span class="fsp-tab-all"><?php echo $fsp_params[ 'accounts_count' ][ 'linkedin' ][ 'total' ]; ?></span>
						</div>
					</div>
					<div class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'vk' ? 'fsp-is-active' : '' ); ?>" data-component="vk">
						<div class="fsp-tab-title">
							<i class="fab fa-vk fsp-tab-title-icon"></i>
							<span class="fsp-tab-title-text">VK</span>
						</div>
						<div class="fsp-tab-badges <?php echo( $fsp_params[ 'accounts_count' ][ 'vk' ][ 'active' ] > 0 ? 'fsp-has-active-accounts' : '' ); ?>">
							<?php echo( $fsp_params[ 'accounts_count' ][ 'vk' ][ 'failed' ] > 0 ? '<span class="fsp-tab-failed">' . $fsp_params[ 'accounts_count' ][ 'vk' ][ 'failed' ] . '</span>' : '' ); ?>
							<span class="fsp-tab-all"><?php echo $fsp_params[ 'accounts_count' ][ 'vk' ][ 'total' ]; ?></span>
						</div>
					</div>
					<div class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'pinterest' ? 'fsp-is-active' : '' ); ?>" data-component="pinterest">
						<div class="fsp-tab-title">
							<i class="fab fa-pinterest-p fsp-tab-title-icon"></i>
							<span class="fsp-tab-title-text">Pinterest</span>
						</div>
						<div class="fsp-tab-badges <?php echo( $fsp_params[ 'accounts_count' ][ 'pinterest' ][ 'active' ] > 0 ? 'fsp-has-active-accounts' : '' ); ?>">
							<?php echo( $fsp_params[ 'accounts_count' ][ 'pinterest' ][ 'failed' ] > 0 ? '<span class="fsp-tab-failed">' . $fsp_params[ 'accounts_count' ][ 'pinterest' ][ 'failed' ] . '</span>' : '' ); ?>
							<span class="fsp-tab-all"><?php echo $fsp_params[ 'accounts_count' ][ 'pinterest' ][ 'total' ]; ?></span>
						</div>
					</div>
					<div class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'reddit' ? 'fsp-is-active' : '' ); ?>" data-component="reddit">
						<div class="fsp-tab-title">
							<i class="fab fa-reddit-alien fsp-tab-title-icon"></i>
							<span class="fsp-tab-title-text">Reddit</span>
						</div>
						<div class="fsp-tab-badges <?php echo( $fsp_params[ 'accounts_count' ][ 'reddit' ][ 'active' ] > 0 ? 'fsp-has-active-accounts' : '' ); ?>">
							<?php echo( $fsp_params[ 'accounts_count' ][ 'reddit' ][ 'failed' ] > 0 ? '<span class="fsp-tab-failed">' . $fsp_params[ 'accounts_count' ][ 'reddit' ][ 'failed' ] . '</span>' : '' ); ?>
							<span class="fsp-tab-all"><?php echo $fsp_params[ 'accounts_count' ][ 'reddit' ][ 'total' ]; ?></span>
						</div>
					</div>
					<div class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'tumblr' ? 'fsp-is-active' : '' ); ?>" data-component="tumblr">
						<div class="fsp-tab-title">
							<i class="fab fa-tumblr fsp-tab-title-icon"></i>
							<span class="fsp-tab-title-text">Tumblr</span>
						</div>
						<div class="fsp-tab-badges <?php echo( $fsp_params[ 'accounts_count' ][ 'tumblr' ][ 'active' ] > 0 ? 'fsp-has-active-accounts' : '' ); ?>">
							<?php echo( $fsp_params[ 'accounts_count' ][ 'tumblr' ][ 'failed' ] > 0 ? '<span class="fsp-tab-failed">' . $fsp_params[ 'accounts_count' ][ 'tumblr' ][ 'failed' ] . '</span>' : '' ); ?>
							<span class="fsp-tab-all"><?php echo $fsp_params[ 'accounts_count' ][ 'tumblr' ][ 'total' ]; ?></span>
						</div>
					</div>
					<div class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'ok' ? 'fsp-is-active' : '' ); ?>" data-component="ok">
						<div class="fsp-tab-title">
							<i class="fab fa-odnoklassniki fsp-tab-title-icon"></i>
							<span class="fsp-tab-title-text">Odnoklassniki</span>
						</div>
						<div class="fsp-tab-badges <?php echo( $fsp_params[ 'accounts_count' ][ 'ok' ][ 'active' ] > 0 ? 'fsp-has-active-accounts' : '' ); ?>">
							<?php echo( $fsp_params[ 'accounts_count' ][ 'ok' ][ 'failed' ] > 0 ? '<span class="fsp-tab-failed">' . $fsp_params[ 'accounts_count' ][ 'ok' ][ 'failed' ] . '</span>' : '' ); ?>
							<span class="fsp-tab-all"><?php echo $fsp_params[ 'accounts_count' ][ 'ok' ][ 'total' ]; ?></span>
						</div>
					</div>
					<div class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'plurk' ? 'fsp-is-active' : '' ); ?>" data-component="plurk">
						<div class="fsp-tab-title">
							<i class="fas fa-parking fsp-tab-title-icon"></i>
							<span class="fsp-tab-title-text">Plurk</span>
						</div>
						<div class="fsp-tab-badges <?php echo( $fsp_params[ 'accounts_count' ][ 'plurk' ][ 'active' ] > 0 ? 'fsp-has-active-accounts' : '' ); ?>">
							<?php echo( $fsp_params[ 'accounts_count' ][ 'plurk' ][ 'failed' ] > 0 ? '<span class="fsp-tab-failed">' . $fsp_params[ 'accounts_count' ][ 'plurk' ][ 'failed' ] . '</span>' : '' ); ?>
							<span class="fsp-tab-all"><?php echo $fsp_params[ 'accounts_count' ][ 'plurk' ][ 'total' ]; ?></span>
						</div>
					</div>
					<div class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'google_b' ? 'fsp-is-active' : '' ); ?>" data-component="google_b">
						<div class="fsp-tab-title">
							<i class="fab fa-google fsp-tab-title-icon"></i>
							<span class="fsp-tab-title-text">Google My Business</span>
						</div>
						<div class="fsp-tab-badges <?php echo( $fsp_params[ 'accounts_count' ][ 'google_b' ][ 'active' ] > 0 ? 'fsp-has-active-accounts' : '' ); ?>">
							<?php echo( $fsp_params[ 'accounts_count' ][ 'google_b' ][ 'failed' ] > 0 ? '<span class="fsp-tab-failed">' . $fsp_params[ 'accounts_count' ][ 'google_b' ][ 'failed' ] . '</span>' : '' ); ?>
							<span class="fsp-tab-all"><?php echo $fsp_params[ 'accounts_count' ][ 'google_b' ][ 'total' ]; ?></span>
						</div>
					</div>
					<div class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'blogger' ? 'fsp-is-active' : '' ); ?>" data-component="blogger">
						<div class="fsp-tab-title">
							<i class="fab fa-blogger fsp-tab-title-icon"></i>
							<span class="fsp-tab-title-text">Blogger</span>
						</div>
						<div class="fsp-tab-badges <?php echo( $fsp_params[ 'accounts_count' ][ 'blogger' ][ 'active' ] > 0 ? 'fsp-has-active-accounts' : '' ); ?>">
							<?php echo( $fsp_params[ 'accounts_count' ][ 'blogger' ][ 'failed' ] > 0 ? '<span class="fsp-tab-failed">' . $fsp_params[ 'accounts_count' ][ 'blogger' ][ 'failed' ] . '</span>' : '' ); ?>
							<span class="fsp-tab-all"><?php echo $fsp_params[ 'accounts_count' ][ 'blogger' ][ 'total' ]; ?></span>
						</div>
					</div>
					<div class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'telegram' ? 'fsp-is-active' : '' ); ?>" data-component="telegram">
						<div class="fsp-tab-title">
							<i class="fab fa-telegram-plane fsp-tab-title-icon"></i>
							<span class="fsp-tab-title-text">Telegram</span>
						</div>
						<div class="fsp-tab-badges <?php echo( $fsp_params[ 'accounts_count' ][ 'telegram' ][ 'active' ] > 0 ? 'fsp-has-active-accounts' : '' ); ?>">
							<?php echo( $fsp_params[ 'accounts_count' ][ 'telegram' ][ 'failed' ] > 0 ? '<span class="fsp-tab-failed">' . $fsp_params[ 'accounts_count' ][ 'telegram' ][ 'failed' ] . '</span>' : '' ); ?>
							<span class="fsp-tab-all"><?php echo $fsp_params[ 'accounts_count' ][ 'telegram' ][ 'total' ]; ?></span>
						</div>
					</div>
					<div class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'medium' ? 'fsp-is-active' : '' ); ?>" data-component="medium">
						<div class="fsp-tab-title">
							<i class="fab fa-medium-m fsp-tab-title-icon"></i>
							<span class="fsp-tab-title-text">Medium</span>
						</div>
						<div class="fsp-tab-badges <?php echo( $fsp_params[ 'accounts_count' ][ 'medium' ][ 'active' ] > 0 ? 'fsp-has-active-accounts' : '' ); ?>">
							<?php echo( $fsp_params[ 'accounts_count' ][ 'medium' ][ 'failed' ] > 0 ? '<span class="fsp-tab-failed">' . $fsp_params[ 'accounts_count' ][ 'medium' ][ 'failed' ] . '</span>' : '' ); ?>
							<span class="fsp-tab-all"><?php echo $fsp_params[ 'accounts_count' ][ 'medium' ][ 'total' ]; ?></span>
						</div>
					</div>
					<div class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'wordpress' ? 'fsp-is-active' : '' ); ?>" data-component="wordpress">
						<div class="fsp-tab-title">
							<i class="fab fa-wordpress-simple fsp-tab-title-icon"></i>
							<span class="fsp-tab-title-text">Wordpress</span>
						</div>
						<div class="fsp-tab-badges <?php echo( $fsp_params[ 'accounts_count' ][ 'wordpress' ][ 'active' ] > 0 ? 'fsp-has-active-accounts' : '' ); ?>">
							<?php echo( $fsp_params[ 'accounts_count' ][ 'wordpress' ][ 'failed' ] > 0 ? '<span class="fsp-tab-failed">' . $fsp_params[ 'accounts_count' ][ 'wordpress' ][ 'failed' ] . '</span>' : '' ); ?>
							<span class="fsp-tab-all"><?php echo $fsp_params[ 'accounts_count' ][ 'wordpress' ][ 'total' ]; ?></span>
						</div>
					</div>
				<?php }
				else
				{
					foreach ( $fsp_params[ 'groups' ] as $group )
					{ ?>
						<div class="fsp-tab <?php echo( $fsp_params[ 'active_group' ] == $group[ 'id' ] ? 'fsp-is-active' : '' ); ?>" data-id="<?php echo $group[ 'id' ] ?>">
							<div class="fsp-tab-title">
								<span class="fsp-tab-title-icon fsp-account-group-badge" style="background-color: <?php echo isset( $group[ 'color' ] ) ? $group[ 'color' ] : '#55D56E' ?>;"></span>
								<span class="fsp-tab-title-text"><?php echo $group[ 'name' ] ?></span>
							</div>
							<div class="fsp-account-group-actions">
								<div class="fsp-tab-badges <?php echo( $group[ 'active' ] > 0 ? 'fsp-has-active-accounts' : '' ); ?>">
									<?php echo( $group[ 'failed' ] > 0 ? '<span class="fsp-tab-failed">' . $group[ 'failed' ] . '</span>' : '' ); ?>
									<span class="fsp-tab-all"><?php echo $group[ 'total' ]; ?></span>
								</div>
								<div class="fsp-group-more">
									<i class="fas fa-ellipsis-h"></i>
								</div>
							</div>
						</div>
					<?php }
				} ?>
			</div>
		</div>
		<div id="js-filter-mobile" class="fsp-accounts-filter-mobile"></div>
		<div id="fspComponent" class="fsp-layout-right fsp-col-12 fsp-col-md-7 fsp-col-lg-8">
			<?php if ( ! $fsp_params[ 'show_accounts' ] ) { ?>
				<div class="fsp-card fsp-emptiness">
					<div class="fsp-emptiness-image">
						<img src="<?php echo Pages::asset( 'Base', 'img/empty.svg' ); ?>">
					</div>
					<div class="fsp-emptiness-text">
						<?php echo fsp__( 'Create account groups to organize and manage your accounts easily' ); ?>
					</div>
					<div class="fsp-emptiness-button">
						<button class="fsp-button fsp-accounts-add-button" data-load-modal='create_group'>
							<i class="fas fa-plus"></i>
							<span><?php echo fsp__( "Create a group" ); ?></span>
						</button>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</div>

<script>
	FSPObject.filter_by = '<?php echo $fsp_params[ 'filter' ]; ?>';
</script>