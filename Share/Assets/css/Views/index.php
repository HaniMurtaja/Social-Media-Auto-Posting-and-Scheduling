<?php

namespace FSPoster\App\Pages\Share\Views;

use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<div class="fsp-row">
	<div class="fsp-col-12 fsp-title">
		<div class="fsp-title-text">
			<?php echo fsp__( 'Direct Share' ); ?>
		</div>
		<div class="fsp-title-button"></div>
	</div>
	<div class="fsp-col-12 fsp-col-lg-6 fsp-share-leftcol">
		<div class="fsp-card">
			<div class="fsp-card-body">
				<div class="fsp-form-group">
					<div class="fsp-form-input-has-icon">
						<i class="far fa-question-circle fsp-tooltip" data-title="<?php echo fsp__( 'Optional field to enter a name for the post if you are going to save the post.' ); ?>"></i>
						<input id="fspPostTitle" autocomplete="off" type="text" class="fsp-form-input" placeholder="<?php echo fsp__( 'Untitled' ); ?> " value="<?php echo esc_html( $fsp_params[ 'title' ] ); ?>">
					</div>
				</div>
				<div class="fsp-form-group">
					<div id="wpMediaBtn" class="fsp-form-image <?php echo $fsp_params[ 'imageId' ] > 0 ? 'fsp-hide' : ''; ?>">
						<i class="fas fa-camera"></i>
					</div>
					<div id="imageShow" class="fsp-form-image-preview <?php echo $fsp_params[ 'imageId' ] > 0 ? '' : 'fsp-hide'; ?>" data-id="<?php echo $fsp_params[ 'imageId' ]; ?>">
						<img src="<?php echo esc_html( $fsp_params[ 'imageURL' ] ); ?>">
						<i class="fas fa-times" id="closeImg"></i>
					</div>
				</div>
				<div id="fspShareURL" class="fsp-form-group <?php echo $fsp_params[ 'imageId' ] > 0 ? 'fsp-hide' : ''; ?>">
					<label><?php echo fsp__( 'Link' ); ?></label>
					<input autocomplete="off" type="text" class="fsp-form-input link_url" placeholder="<?php echo fsp__( 'Example: https://example.com' ); ?> " value="<?php echo esc_html( $fsp_params[ 'link' ] ); ?>">
				</div>
				<div class="fsp-custom-messages-container">
					<div class="fsp-form-group">
						<div class="fsp-custom-messages-tabs">
							<div data-tab="default" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Default custom message<br>for all social networks', [], FALSE ); ?>">
								<i class="fas fa-grip-horizontal"></i>
							</div>
							<div data-tab="fb" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Facebook' ); ?>">
								<i class="fab fa-facebook-f"></i>
							</div>
							<div data-tab="twitter" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Twitter' ); ?>">
								<i class="fab fa-twitter"></i>
							</div>
							<div data-tab="instagram" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Instagram' ); ?>">
								<i class="fab fa-instagram"></i>
							</div>
							<div data-tab="linkedin" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Linkedin' ); ?>">
								<i class="fab fa-linkedin-in"></i>
							</div>
							<div data-tab="vk" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for VKontakte' ); ?>">
								<i class="fab fa-vk"></i>
							</div>
							<div data-tab="pinterest" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Pinterest' ); ?>">
								<i class="fab fa-pinterest-p"></i>
							</div>
							<div data-tab="reddit" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Reddit' ); ?>">
								<i class="fab fa-reddit-alien"></i>
							</div>
							<div data-tab="tumblr" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Tumblr' ); ?>">
								<i class="fab fa-tumblr"></i>
							</div>
							<div data-tab="ok" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for OdnoKlassniki' ); ?>">
								<i class="fab fa-odnoklassniki"></i>
							</div>
							<div data-tab="plurk" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Plurk' ); ?>">
								<i class="fas fa-parking"></i>
							</div>
							<div data-tab="google_b" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Google My Business' ); ?>">
								<i class="fab fa-google"></i>
							</div>
							<div data-tab="blogger" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Blogger' ); ?>">
								<i class="fab fa-blogger"></i>
							</div>
							<div data-tab="telegram" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Telegram' ); ?>">
								<i class="fab fa-telegram-plane"></i>
							</div>
							<div data-tab="medium" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Medium' ); ?>">
								<i class="fab fa-medium-m"></i>
							</div>
							<div data-tab="wordpress" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for WordPress' ); ?>">
								<i class="fab fa-wordpress-simple"></i>
							</div>
						</div>
						<div id="fspCustomMessages" class="fsp-custom-messages">
							<?php foreach ( $fsp_params[ 'sn_list' ] as $sn ) { ?>
								<div data-driver="<?php echo $sn; ?>">
									<div class="fsp-custom-post">
										<div class="fsp-custom-post-counter">
											<span data-character-counter="<?php echo $sn; ?>">0</span><?php echo fsp__( ' chars.' ); ?>
										</div>
										<textarea data-sn-id="<?php echo $sn; ?>" name="fs_post_text_message_<?php echo $sn; ?>" class="fsp-form-textarea message_box" rows="4" placeholder="<?php echo fsp__( 'Enter the custom post message' ); ?>"><?php echo esc_html( $fsp_params[ 'message' ][ $sn ] ); ?></textarea>
									</div>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<div class="fsp-card-footer">
				<button type="button" class="fsp-button shareNowBtn"><?php echo fsp__( 'SHARE NOW' ); ?></button>
				<button type="button" class="fsp-button fsp-is-info schedule_button"><?php echo fsp__( 'SCHEDULE' ); ?></button>
				<button type="button" class="fsp-button fsp-is-gray saveBtnNew">
					<?php echo fsp__( 'SAVE & NEW' ); ?>
					<i class="far fa-question-circle fsp-tooltip" data-title="<?php echo fsp__( 'Save the post and create a new post.' ); ?>"></i>
				</button>
				<button type="button" class="fsp-button fsp-is-gray saveBtn">
					<?php echo fsp__( 'SAVE THE POST' ); ?>
					<i class="far fa-question-circle fsp-tooltip" data-title="<?php echo fsp__( 'Save the currently edited post.' ); ?>"></i>
				</button>
			</div>
		</div>
	</div>
	<div class="fsp-col-12 fsp-col-lg-6 fsp-share-rightcol">
		<?php Pages::controller( 'Base', 'MetaBox', 'post_meta_box', [
			'post_id' => $fsp_params[ 'post_id' ]
		] ); ?>
	</div>
	<div class="fsp-col-12 fsp-title">
		<div class="fsp-title-text">
			<?php echo fsp__( 'Saved posts' ); ?>
			<span class="fsp-title-count"><?php echo count( $fsp_params[ 'posts' ] ); ?></span>
		</div>
		<div class="fsp-title-button">
			<button id="fspClearSavedPosts" class="fsp-button">
				<i class="far fa-trash-alt"></i>
				<span><?php echo fsp__( 'CLEAR ALL' ); ?></span>
			</button>
		</div>
	</div>
	<div id="fspFsPosts" class="fsp-col-12">
		<?php foreach ( $fsp_params[ 'posts' ] as $post )
		{
			$title = get_the_title($post[ 'ID' ]);
			?>
			<div class="fsp-share-post" data-id="<?php echo (int) $post[ 'ID' ]; ?>">
				<div class="fsp-share-post-id">
					<?php echo (int) $post[ 'ID' ]; ?>
				</div>
				<div class="fsp-share-post-title">
					<a href="?page=fs-poster-share&post_id=<?php echo (int) $post[ 'ID' ]; ?>">{<?php echo htmlspecialchars( Helper::cutText( $title ) ); ?>}</a>
				</div>
				<div class="fsp-share-post-date">
					<?php echo Date::dateTime( $post[ 'post_date' ] ); ?>
				</div>
				<div class="fsp-share-post-controls">
					<i class="far fa-trash-alt fsp-tooltip fsp-icon-button delete_post_btn" data-title="<?php echo fsp__( 'Delete the post' ); ?>"></i>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
<script>
	FSPObject.saveID = <?php echo (int) $fsp_params[ 'post_id' ]; ?>;
</script>
