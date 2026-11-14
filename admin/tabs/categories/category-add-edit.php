<?php
/**
 * Antalya POS Add / Edit Menu Category Form View
 *
 * @package Antalya_POS
 * @since 6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    return;
}

$svg_folder = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>';
$svg_edit   = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_check  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
$svg_order  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>';
$svg_image  = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';

$cat_image_url  = (!empty($edit_cat) && !empty($edit_cat->image)) ? $edit_cat->image : '';
$all_cats_url   = esc_url(admin_url('admin.php?page=antalya-pos&tab=categories&subtab=all'));
?>

<div class="apos-card apos-full-card">
    <div class="apos-form-header">
        <h3>
            <?php if (!empty($edit_cat)) : ?>
                <?php echo $svg_edit; ?>
                <span>
                    <?php
                    printf(
                        /* translators: %s: category name */
                        esc_html__('Edit Category: %s', 'antalya-pos'),
                        esc_html($edit_cat->name)
                    );
                    ?>
                </span>
            <?php else : ?>
                <?php echo $svg_folder; ?>
                <span><?php esc_html_e('Add New Menu Category', 'antalya-pos'); ?></span>
            <?php endif; ?>
        </h3>
        <p><?php esc_html_e('Categories appear as quick-filter tabs on the top of the customer tablet menu.', 'antalya-pos'); ?></p>
    </div>

    <form method="post" action="<?php echo $all_cats_url; ?>">
        <?php wp_nonce_field('rtpos_save_cat_action'); ?>
        <input type="hidden" name="edit_cat_id" value="<?php echo !empty($edit_cat) ? intval($edit_cat->id) : ''; ?>">

        <div class="apos-form-group">
            <label class="apos-label">
                <span><?php esc_html_e('Category Name', 'antalya-pos'); ?> <span class="required-star">*</span></span>
                <small><?php esc_html_e('(e.g. Turkish Kebab, Pizza, Drinks)', 'antalya-pos'); ?></small>
            </label>
            <div class="apos-input-wrap">
                <span class="apos-input-prefix"><?php echo $svg_folder; ?></span>
                <input type="text" name="cat_name" class="apos-input has-prefix" placeholder="<?php esc_attr_e('Enter category name...', 'antalya-pos'); ?>" value="<?php echo !empty($edit_cat) ? esc_attr($edit_cat->name) : ''; ?>" required>
            </div>
        </div>

        <div class="apos-form-group">
            <label class="apos-label">
                <span><?php esc_html_e('Display Order / Number', 'antalya-pos'); ?></span>
                <small><?php esc_html_e('(Lower numbers appear first, e.g. 1, 2, 3)', 'antalya-pos'); ?></small>
            </label>
            <div class="apos-input-wrap">
                <span class="apos-input-prefix"><?php echo $svg_order; ?></span>
                <input type="number" name="cat_order" class="apos-input has-prefix" placeholder="0" value="<?php echo (!empty($edit_cat) && isset($edit_cat->category_order)) ? intval($edit_cat->category_order) : '0'; ?>">
            </div>
        </div>

        <!-- Category Image Upload Field -->
        <div class="apos-form-group" style="margin-bottom: 24px;">
            <label class="apos-label">
                <span><?php esc_html_e('Category Image / Icon', 'antalya-pos'); ?></span>
                <small><?php esc_html_e('(Optional)', 'antalya-pos'); ?></small>
            </label>
            <div class="cat-media-row">
                <input type="hidden" name="cat_image" id="apos_cat_image_input" value="<?php echo esc_url($cat_image_url); ?>">

                <div id="apos_cat_img_preview" class="cat-media-preview-box <?php echo !empty($cat_image_url) ? 'has-img' : ''; ?>">
                    <?php if (!empty($cat_image_url)) : ?>
                        <img src="<?php echo esc_url($cat_image_url); ?>" alt="<?php esc_attr_e('Category preview', 'antalya-pos'); ?>">
                    <?php else : ?>
                        <span class="cat-media-placeholder"><?php echo $svg_image; ?></span>
                    <?php endif; ?>
                </div>

                <div class="cat-media-actions">
                    <button type="button" id="apos_upload_cat_img_btn" class="apos-btn-secondary cat-btn-icon" style="font-weight: 700;">
                        <?php echo $svg_image; ?>
                        <span><?php esc_html_e('Choose Image', 'antalya-pos'); ?></span>
                    </button>
                    <button type="button" id="apos_remove_cat_img_btn" class="apos-btn-secondary cat-media-remove-btn" style="<?php echo empty($cat_image_url) ? 'display:none;' : ''; ?>">
                        <?php esc_html_e('Remove', 'antalya-pos'); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="cat-form-footer">
            <button type="submit" name="rtpos_save_cat" class="apos-btn cat-btn-icon">
                <?php echo $svg_check; ?>
                <span><?php echo !empty($edit_cat) ? esc_html__('Update Category', 'antalya-pos') : esc_html__('Save Category', 'antalya-pos'); ?></span>
            </button>
            <a href="<?php echo $all_cats_url; ?>" class="apos-btn-secondary"><?php esc_html_e('Cancel', 'antalya-pos'); ?></a>
        </div>
    </form>
</div>

<style>
    .required-star { color: #ef4444; font-weight: 800; margin-left: 3px; }
    .cat-media-row {
        display: flex;
        gap: 14px;
        align-items: center;
        flex-wrap: wrap;
    }
    .cat-media-preview-box {
        width: 68px;
        height: 68px;
        border: 2px dashed #cbd5e1;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    .cat-media-preview-box.has-img {
        border-style: solid;
        border-color: #e2e8f0;
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.06);
    }
    .cat-media-preview-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .cat-media-placeholder {
        color: #94a3b8;
        display: inline-flex;
    }
    .cat-media-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .cat-media-remove-btn {
        color: #dc2626 !important;
        border-color: #fee2e2 !important;
        background: #fef2f2 !important;
    }
    .cat-media-remove-btn:hover {
        background: #fee2e2 !important;
    }
</style>

<!-- WordPress Media Uploader Integration Script -->
<script>
jQuery(document).ready(function($) {
    let mediaUploader;
    let placeholderIcon = <?php echo wp_json_encode($svg_image); ?>;

    $('#apos_upload_cat_img_btn').on('click', function(e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: <?php echo wp_json_encode(__('Choose Category Image', 'antalya-pos')); ?>,
            button: { text: <?php echo wp_json_encode(__('Use Image', 'antalya-pos')); ?> },
            multiple: false
        });

        mediaUploader.on('select', function() {
            let attachment = mediaUploader.state().get('selection').first().toJSON();
            let imgUrl = attachment.url || '';
            $('#apos_cat_image_input').val(imgUrl);

            let imgEl = $('<img>', {
                src: imgUrl,
                alt: <?php echo wp_json_encode(__('Category preview', 'antalya-pos')); ?>
            });
            $('#apos_cat_img_preview').addClass('has-img').empty().append(imgEl);
            $('#apos_remove_cat_img_btn').show();
        });

        mediaUploader.open();
    });

    $('#apos_remove_cat_img_btn').on('click', function() {
        $('#apos_cat_image_input').val('');
        $('#apos_cat_img_preview')
            .removeClass('has-img')
            .html('<span class="cat-media-placeholder">' + placeholderIcon + '</span>');
        $(this).hide();
    });
});
</script>