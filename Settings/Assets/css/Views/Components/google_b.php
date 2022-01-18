<?php

namespace FSPoster\App\Pages\Settings\Views;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<link rel="stylesheet" href="<?php echo Pages::asset( 'Settings', 'css/fsp-shared-preview.css' ); ?>">
<link rel="stylesheet" href="<?php echo Pages::asset( 'Settings', 'css/fsp-google_b-preview.css' ); ?>">

<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Posting type' ); ?><?php echo fsp__( '(only the app method)' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Link card view – shares the post link and the custom message;<br>Only custom message – shares the custom message without any image or link;<br>Featured image - Shares the featured image and the custom message;<br>All post images - Shares all post images as an album and the custom message.', [], FALSE ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<select name="fs_google_b_posting_type" id="fspGoogleBPostingType" class="fsp-form-select">
			<option value="1" <?php echo Helper::getOption( 'google_b_posting_type', '1' ) == '1' ? 'selected' : ''; ?>><?php echo fsp__( 'Link card view' ); ?></option>
			<option value="4" <?php echo Helper::getOption( 'google_b_posting_type', '1' ) == '4' ? 'selected' : ''; ?>><?php echo fsp__( 'Only custom message' ); ?></option>
			<option value="2" <?php echo Helper::getOption( 'google_b_posting_type', '1' ) == '2' ? 'selected' : ''; ?>><?php echo fsp__( 'Featured image' ); ?></option>
			<option value="3" <?php echo Helper::getOption( 'google_b_posting_type', '1' ) == '3' ? 'selected' : ''; ?>><?php echo fsp__( 'All post images' ); ?></option>
		</select>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Add a button' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Select a post link button.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<select name="fs_google_b_button_type" class="fsp-form-select">
			<option value="BOOK" <?php echo Helper::getOption( 'google_b_button_type', 'LEARN_MORE' ) == 'BOOK' ? ' selected' : ''; ?>><?php echo fsp__( 'BOOK' ); ?></option>
			<option value="ORDER" <?php echo Helper::getOption( 'google_b_button_type', 'LEARN_MORE' ) == 'ORDER' ? ' selected' : ''; ?>><?php echo fsp__( 'ORDER ONLINE' ); ?></option>
			<option value="SHOP" <?php echo Helper::getOption( 'google_b_button_type', 'LEARN_MORE' ) == 'SHOP' ? ' selected' : ''; ?>><?php echo fsp__( 'BUY' ); ?></option>
			<option value="LEARN_MORE" <?php echo Helper::getOption( 'google_b_button_type', 'LEARN_MORE' ) == 'LEARN_MORE' ? ' selected' : ''; ?>><?php echo fsp__( 'LEARN MORE' ); ?></option>
			<option value="SIGN_UP" <?php echo Helper::getOption( 'google_b_button_type', 'LEARN_MORE' ) == 'SIGN_UP' ? ' selected' : ''; ?>><?php echo fsp__( 'SIGN UP' ); ?></option>
			<option value="WATCH_VIDEO" <?php echo Helper::getOption( 'google_b_button_type', 'LEARN_MORE' ) == 'WATCH_VIDEO' ? ' selected' : ''; ?>><?php echo fsp__( 'WATCH VIDEO' ); ?></option>
			<option value="RESERVE" <?php echo Helper::getOption( 'google_b_button_type', 'LEARN_MORE' ) == 'RESERVE' ? ' selected' : ''; ?>><?php echo fsp__( 'RESERVE' ); ?></option>
			<option value="GET_OFFER" <?php echo Helper::getOption( 'google_b_button_type', 'LEARN_MORE' ) == 'GET_OFFER' ? ' selected' : ''; ?>><?php echo fsp__( 'GET OFFER' ); ?></option>
			<option value="CALL" <?php echo Helper::getOption( 'google_b_button_type', 'LEARN_MORE' ) == 'CALL' ? ' selected' : ''; ?>><?php echo fsp__( 'CALL NOW' ); ?></option>
		</select>
	</div>
</div>
<?php if ( function_exists( 'wc_get_product' ) ) { ?>
	<div class="fsp-settings-row">
		<div class="fsp-settings-col">
			<div class="fsp-settings-label-text"><?php echo fsp__( 'Share WooCommerce products as a product' ); ?><?php echo fsp__( '(only the cookie method)' ); ?></div>
			<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Disable to share WooCommerce products as a post.' ); ?></div>
		</div>
		<div class="fsp-settings-col">
			<div class="fsp-toggle">
				<input type="checkbox" name="fs_google_b_share_as_product" class="fsp-toggle-checkbox" id="fs_google_b_share_as_product"<?php echo Helper::getOption( 'google_b_share_as_product', '0' ) ? ' checked' : ''; ?>>
				<label class="fsp-toggle-label" for="fs_google_b_share_as_product"></label>
			</div>
		</div>
	</div>
<?php } ?>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Limit the custom message characters count' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Google My Business limits a custom message length to a maximum of 1500 characters. By enabling the option, the first 1500 characters of your custom message will be shared; otherwise, the post won\'t be shared because of the limit.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_gmb_autocut" class="fsp-toggle-checkbox" id="fs_gmb_autocut"<?php echo Helper::getOption( 'gmb_autocut', '1' ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_gmb_autocut"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row fsp-is-collapser">
	<div class="fsp-settings-collapser">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Custom message' ); ?>
			<i class="fas fa-angle-up fsp-settings-collapse-state fsp-is-rotated"></i>
		</div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can customize the text of the shared post as you like by using the available keywords. You can add the keywords to the custom message section easily by clicking on the keyword.' ); ?></div>
	</div>
	<div class="fsp-settings-collapse">
		<div class="fsp-settings-col">
			<div class="fsp-settings-col-title"><?php echo fsp__( 'Text' ); ?></div>
			<div class="fsp-custom-post" data-preview="fspCustomPostPreview">
				<textarea name="fs_post_text_message_google_b" class="fsp-form-textarea"><?php echo esc_html( Helper::getOption( 'post_text_message_google_b', "{title}" ) ); ?></textarea>
				<div class="fsp-custom-post-buttons">
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{id}">
						{ID}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post ID' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{product_regular_price}">
						{PRODUCT_REGULAR_PRICE}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'WooCommerce - product price' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{author}">
						{AUTHOR}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post author name' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{content_short_40}">
						{CONTENT_SHORT_40}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'The default is the first 40 characters. You can set the number whatever you want. The plugin will share that number of characters.' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{title}">
						{TITLE}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post title' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{featured_image_url}">
						{FEATURED_IMAGE_URL}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Featured image URL' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{tags}">
						{TAGS}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post Tags' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{product_sale_price}">
						{PRODUCT_SALE_PRICE}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'WooCommerce - product sale price' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{terms}">
						{TERMS}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post Terms' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{content_full}">
						{CONTENT_FULL}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post full content' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{short_link}">
						{SHORT_LINK}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post short link' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{excerpt}">
						{EXCERPT}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post excerpt' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{categories}">
						{CATEGORIES}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post Categories' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{uniq_id}">
						{UNIQ_ID}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Unique ID' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{cf_KEY}">
						{CF_KEY}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Custom fields. Replace KEY with the custom field name.' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{link}">
						{LINK}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post link' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-red fsp-clear-button fsp-tooltip" data-title="<?php echo fsp__( 'Click to clear the textbox' ); ?>">
						<?php echo fsp__( 'CLEAR' ); ?>
					</button>
				</div>
			</div>
		</div>
		<div class="fsp-settings-col">
			<div class="fsp-settings-col-title"><?php echo fsp__( 'Preview' ); ?></div>
			<div class="fsp-settings-preview">
				<div class="fsp-settings-preview-header">
					<img src="#" onerror="FSPoster.no_photo( this );">
					<div class="fsp-settings-preview-header-title"><?php echo get_bloginfo( 'name' ); ?></div>
				</div>
				<div class="fsp-settings-preview-body">
					<div class="fsp-settings-preview-image"></div>
					<span id="fspCustomPostPreview" class="fsp-settings-preview-body-text"><?php echo esc_html( Helper::getOption( 'post_text_message_google_mb', "{title}" ) ); ?></span>
				</div>
				<div class="fsp-settings-preview-footer">
					<span class="fsp-settings-preview-footer-item">Learn more</span>
				</div>
			</div>
		</div>
	</div>
</div>