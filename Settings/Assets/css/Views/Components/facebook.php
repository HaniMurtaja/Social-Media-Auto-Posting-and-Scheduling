<?php

namespace FSPoster\App\Pages\Settings\Views;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;
?>

<link rel="stylesheet" href="<?php echo Pages::asset( 'Settings', 'css/fsp-shared-preview.css' ); ?>">
<link rel="stylesheet" href="<?php echo Pages::asset( 'Settings', 'css/fsp-facebook-preview.css' ); ?>">
<?php if ( $fb_story_custom_font = Helper::getOption( 'facebook_story_custom_font', '' ) ) {
	$fb_story_custom_font_url = str_replace( $_SERVER[ 'DOCUMENT_ROOT' ], site_url() . '/', wp_normalize_path( $fb_story_custom_font ) );
	?>
	<style>
        @font-face {
            font-family: FS-Poster-fb-font;
            src: url( '<?php echo $fb_story_custom_font_url; ?>' );
            font-weight: normal;
            font-style: normal;
        }

        .fsp-settings-fbstory-title {
            font-family: FS-Poster-fb-font;
        }
	</style>
<?php } ?>

<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Share Facebook posts on' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Select where to share posts on Facebook. Note that Facebook API does not support sharing posts on the story so that accounts have to be added to the plugin via the cookie method to share posts on the account story.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<select name="fs_fb_post_in_type" id="fspFbPostInTypeSelector" class="fsp-form-select">
			<option value="1"<?php echo Helper::getOption( 'fb_post_in_type', '1' ) == '1' ? ' selected' : ''; ?>>Profile only</option>
			<option value="2"<?php echo Helper::getOption( 'fb_post_in_type', '1' ) == '2' ? ' selected' : ''; ?>>Story only</option>
			<option value="3"<?php echo Helper::getOption( 'fb_post_in_type', '1' ) == '3' ? ' selected' : ''; ?>>Profile and Story</option>
		</select>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Upload custom font files' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can upload and use a <a href="https://www.fs-poster.com/documentation/commonly-encountered-issues#issue12" target="_blank">custom font</a> for Facebook stories.', [], FALSE ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<?php if ( $fb_story_custom_font ) { ?>
			<button id="fspFbCustomFontReset" type="button" class="fsp-button fsp-is-red fsp-tooltip" data-title="<?php echo fsp__( 'Reset to default.' ); ?>">
				<?php echo fsp__( 'RESET' ); ?>
			</button>
		<?php } ?>
		<input id="fspFbCustomFontResetInput" name="fs_facebook_story_custom_font_reset" type="number" value="0" class="fsp-hide">
		<button id="fspFbCustomFontButton" type="button" class="fsp-button fsp-is-gray">
			<?php echo fsp__( 'CHOOSE FONT' ); ?>
		</button>
		<input id="fspFbCustomFontInput" name="fs_facebook_story_custom_font" type="file" class="fsp-hide" accept=".ttf, font/ttf">
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Posting type' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Link card view – shares the post link and the custom message. Facebook fetches the post as a link card view. <a href=\'https://www.fs-poster.com/documentation/commonly-encountered-issues#issue9\' target=\'_blank\'>Debug your website</a>;<br>Only custom message – shares the custom message without any image or link;<br>Featured image - Shares the featured image and the custom message;<br>All post images - Shares all post images as an album and the custom message.', [], FALSE ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<select name="fs_facebook_posting_type" id="fspFacebookPostingType" class="fsp-form-select">
			<option value="1" <?php echo Helper::getOption( 'facebook_posting_type', '1' ) == '1' ? 'selected' : ''; ?>><?php echo fsp__( 'Link card view' ); ?></option>
			<option value="4" <?php echo Helper::getOption( 'facebook_posting_type', '1' ) == '4' ? 'selected' : ''; ?>><?php echo fsp__( 'Only custom message' ); ?></option>
			<option value="2" <?php echo Helper::getOption( 'facebook_posting_type', '1' ) == '2' ? 'selected' : ''; ?>><?php echo fsp__( 'Featured image' ); ?></option>
			<option value="3" <?php echo Helper::getOption( 'facebook_posting_type', '1' ) == '3' ? 'selected' : ''; ?>><?php echo fsp__( 'All post images' ); ?></option>
		</select>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Load my pages' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable to add all your pages to the plugin, pages that you are an admin.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_load_own_pages" class="fsp-toggle-checkbox" id="fs_load_own_pages"<?php echo Helper::getOption( 'load_own_pages', '1' ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_load_own_pages"></label>
		</div>
	</div>
</div>
<div class="fsp-settings-row">
	<div class="fsp-settings-col">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Load groups' ); ?></div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'Enable to add all your groups to the plugin.' ); ?></div>
	</div>
	<div class="fsp-settings-col">
		<div class="fsp-toggle">
			<input type="checkbox" name="fs_load_groups" class="fsp-toggle-checkbox" id="fs_load_groups"<?php echo Helper::getOption( 'load_groups', '1' ) ? ' checked' : ''; ?>>
			<label class="fsp-toggle-label" for="fs_load_groups"></label>
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
				<textarea name="fs_post_text_message_fb" class="fsp-form-textarea"><?php echo esc_html( Helper::getOption( 'post_text_message_fb', "{title}" ) ); ?></textarea>
				<div class="fsp-custom-post-buttons">
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{id}">
						{ID}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post ID' ); ?>"></i>
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
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{product_regular_price}">
						{PRODUCT_REGULAR_PRICE}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'WooCommerce - product price' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{terms}">
						{TERMS}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'Post Terms' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{product_sale_price}">
						{PRODUCT_SALE_PRICE}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'WooCommerce - product sale price' ); ?>"></i>
					</button>
					<button type="button" class="fsp-button fsp-is-gray fsp-append-to-text" data-key="{product_current_price}">
						{PRODUCT_CURRENT_PRICE}
						<i class="fas fa-info-circle fsp-tooltip" data-title="<?php echo fsp__( 'WooCommerce - the current price of product' ); ?>"></i>
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
					<i class="fas fa-ellipsis-v fsp-settings-preview-dots"></i>
				</div>
				<div class="fsp-settings-preview-body">
					<span id="fspCustomPostPreview" class="fsp-settings-preview-body-text"><?php echo esc_html( Helper::getOption( 'post_text_message_fb', "{title}" ) ); ?></span>
					<div class="fsp-settings-preview-image"></div>
				</div>
				<div class="fsp-settings-preview-footer">
					<div class="fsp-settings-preview-footer-icon">
						<i class="fas fa-thumbs-up"></i>
					</div>
					<div class="fsp-settings-preview-footer-icon">
						<i class="far fa-comment"></i>
					</div>
					<div class="fsp-settings-preview-footer-icon">
						<i class="fas fa-share"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="fsp-settings-row fsp-is-collapser">
	<div class="fsp-settings-collapser">
		<div class="fsp-settings-label-text"><?php echo fsp__( 'Customize story' ); ?>
			<i class="fas fa-angle-up fsp-settings-collapse-state fsp-is-rotated"></i>
		</div>
		<div class="fsp-settings-label-subtext"><?php echo fsp__( 'You can customize the text of the shared story as you like by using the available keywords. You can add the keywords to the custom message section easily by clicking on the keyword. You can also customize the appearance of the story using available options.' ); ?></div>
	</div>
	<div class="fsp-settings-collapse">
		<div id="fspSettingsFbStoryMessageRow" class="fsp-settings-col fsp-flex-grow-1">
			<div class="fsp-settings-fbstory-tabs">
				<div id="fspSettingsFbStoryMessageTab" class="fsp-settings-fbstory-tab fsp-is-active" data-tab="message">
					<?php echo fsp__( 'Customize message', [], FALSE ); ?>
				</div>
				<div id="fspSettingsFbStoryAppearanceTab" class="fsp-settings-fbstory-tab" data-tab="appearance">
					<?php echo fsp__( 'Customize appearance', [], FALSE ); ?>
				</div>
			</div>
			<div class="fsp-settings-col-title"><?php echo fsp__( 'Text' ); ?></div>
			<div class="fsp-custom-post" data-preview="fspStoryTitle">
				<textarea name="fs_post_text_message_fb_h" class="fsp-form-textarea"><?php echo esc_html( Helper::getOption( 'post_text_message_fb_h', "{title}" ) ); ?></textarea>
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
		<div id="fspSettingsFbStoryAppearanceRow" class="fsp-settings-col fsp-flex-grow-1 fsp-hide">
			<div class="fsp-settings-fbstory-tabs">
				<div class="fsp-settings-fbstory-tab" data-tab="message">
					<?php echo fsp__( 'Customize message', [], FALSE ); ?>
				</div>
				<div class="fsp-settings-fbstory-tab fsp-is-active" data-tab="appearance">
					<?php echo fsp__( 'Customize appearance', [], FALSE ); ?>
				</div>
			</div>
			<div class="fsp-settings-fbcontrols">
				<div class="fsp-settings-fbcontrol">
					<div class="fsp-settings-fbcontrol-label"><?php echo fsp__( 'Story background color:' ); ?></div>
					<div class="fsp-settings-fbcontrol-input">
						<span id="fspStoryBgInput">#</span>
						<input data-jscolor="{ previewElement: '' }" data-type="story-background" autocomplete="off" class="fsp-form-input" name="fs_facebook_story_background" value="<?php echo Helper::getOption( 'facebook_story_background', 'ED5458' ); ?>">
					</div>
				</div>
				<div class="fsp-settings-fbcontrol">
					<div class="fsp-settings-fbcontrol-label"><?php echo fsp__( 'Title background:' ); ?></div>
					<div class="fsp-settings-fbcontrol-input">
						<span id="fspStoryTitleBgInput">#</span>
						<input autocomplete="off" data-jscolor="{ previewElement: '' }" class="fsp-form-input jscolor" name="fs_facebook_story_title_background" value="<?php echo Helper::getOption( 'facebook_story_title_background', 'FFFFFF' ); ?>" data-type="title-background-color">
					</div>
				</div>
				<div class="fsp-settings-fbcontrol">
					<div class="fsp-settings-fbcontrol-label"><?php echo fsp__( 'Title background opacity:' ); ?></div>
					<div class="fsp-settings-fbcontrol-input">
						<span>%</span>
						<input autocomplete="off" class="fsp-form-input" name="fs_facebook_story_title_background_opacity" value="<?php echo Helper::getOption( 'facebook_story_title_background_opacity', '30' ); ?>" data-type="title-background-opacity">
					</div>
				</div>
				<div class="fsp-settings-fbcontrol">
					<div class="fsp-settings-fbcontrol-label"><?php echo fsp__( 'Title color:' ); ?></div>
					<div class="fsp-settings-fbcontrol-input">
						<span id="fspStoryTitleColorInput">#</span>
						<input autocomplete="off" data-jscolor="{ previewElement: '' }" class="fsp-form-input" name="fs_facebook_story_title_color" value="<?php echo Helper::getOption( 'facebook_story_title_color', 'FFFFFF' ); ?>" data-type="title-color">
					</div>
				</div>
				<div class="fsp-settings-fbcontrol">
					<div class="fsp-settings-fbcontrol-label"><?php echo fsp__( 'Title top offset:' ); ?></div>
					<div class="fsp-settings-fbcontrol-input">
						<span>px</span>
						<input autocomplete="off" class="fsp-form-input" name="fs_facebook_story_title_top" value="<?php echo Helper::getOption( 'facebook_story_title_top', '125' ); ?>" data-type="title-top">
					</div>
				</div>
				<div class="fsp-settings-fbcontrol">
					<div class="fsp-settings-fbcontrol-label"><?php echo fsp__( 'Title left offset:' ); ?></div>
					<div class="fsp-settings-fbcontrol-input">
						<span>px</span>
						<input autocomplete="off" class="fsp-form-input" name="fs_facebook_story_title_left" value="<?php echo Helper::getOption( 'facebook_story_title_left', '30' ); ?>" data-type="title-left">
					</div>
				</div>
				<div class="fsp-settings-fbcontrol">
					<div class="fsp-settings-fbcontrol-label"><?php echo fsp__( 'Title width:' ); ?></div>
					<div class="fsp-settings-fbcontrol-input">
						<span>px</span>
						<input autocomplete="off" class="fsp-form-input" name="fs_facebook_story_title_width" value="<?php echo Helper::getOption( 'facebook_story_title_width', '660' ); ?>" data-type="title-width">
					</div>
				</div>
				<div class="fsp-settings-fbcontrol">
					<div class="fsp-settings-fbcontrol-label"><?php echo fsp__( 'Title font size:' ); ?></div>
					<div class="fsp-settings-fbcontrol-input">
						<span>px</span>
						<input autocomplete="off" class="fsp-form-input" name="fs_facebook_story_title_font_size" value="<?php echo Helper::getOption( 'facebook_story_title_font_size', '30' ); ?>" data-type="title-font-size">
					</div>
				</div>
				<div class="fsp-settings-fbcontrol">
					<div class="fsp-form-checkbox-group">
						<input id="fspRTLDirection" type="checkbox" class="fsp-form-checkbox" name="fs_facebook_story_title_rtl" <?php echo Helper::getOption( 'facebook_story_title_rtl', 'off' ) == 'on' ? 'checked' : ''; ?>>
						<label for="fspRTLDirection">
							<?php echo fsp__( 'RTL direction' ); ?>
						</label>
					</div>
				</div>
			</div>
		</div>
		<div class="fsp-settings-col fsp-settings-fbstory-container">
			<div id="fspStory" class="fsp-settings-fbstory">
				<div id="fspStoryTitle" class="fsp-settings-fbstory-title"><?php echo esc_html( Helper::getOption( 'post_text_message_fb_h', "{title}" ) ); ?></div>
				<div class="fsp-settings-fbstory-image"></div>
			</div>
		</div>
	</div>
</div>