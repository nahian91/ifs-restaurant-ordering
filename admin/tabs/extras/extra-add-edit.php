<?php
/**
 * Antalya POS Add / Edit Extra Add-on Form View
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

$svg_sparkles = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/></svg>';
$svg_edit     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_tag      = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><path d="M7 7h.01"/></svg>';
$svg_check    = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
$svg_image    = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';

$extra_image_url = (!empty($edit_extra) && !empty($edit_extra->image)) ? $edit_extra->image : '';
$all_extras_url  = esc_url(admin_url('admin.php?page=antalya-pos&tab=extras&subtab=all'));
?>

<div class="apos-card apos-full-card">
    <div class="apos-form-header">
        <h3>
            <?php if (!empty($edit_extra)) : ?>
                <?php echo $svg_edit; ?>
                <span>
                    <?php
                    printf(
                        /* translators: %s: extra add-on name */
                        esc_html__('Edit Extra: %s', 'antalya-pos'),
                        esc_html($edit_extra->name)
                    );
                    ?>
                </span>
            <?php else : ?>
                <?php echo $svg_sparkles; ?>
                <span><?php esc_html_e('Add New Extra / Add-on', 'antalya-pos'); ?></span>
            <?php endif; ?>
        </h3>
        <p>
            <?php
            printf(
                /* translators: %s: Items menu label */
                esc_html__('After saving, you can attach this add-on to any dish from the %s menu.', 'antalya-pos'),
                '<strong>' . esc_html__('Items', 'antalya-pos') . '</strong>'
            );
            ?>
        </p>
    </div>

    <form method="post" action="<?php echo $all_extras_url; ?>">
        <?php wp_nonce_field('rtpos_save_extra_action'); ?>
        <input type="hidden" name="edit_extra_id" value="<?php echo !empty($edit_extra) ? intval($edit_extra->id) : ''; ?>">

        <div class="ex-form-grid">
            <div class="apos-form-group">
                <label class="apos-label">
                    <span><?php esc_html_e('Extra Name', 'antalya-pos'); ?> <span class="required-star">*</span></span>
                    <small><?php esc_html_e('(e.g. Extra Cheese)', 'antalya-pos'); ?></small>
                </label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $svg_tag; ?></span>
                    <input type="text" name="extra_name" class="apos-input has-prefix" placeholder="<?php esc_attr_e('Garlic Sauce', 'antalya-pos'); ?>" value="<?php echo !empty($edit_extra) ? esc_attr($edit_extra->name) : ''; ?>" required>
                </div>
            </div>

            <div class="apos-form-group">
                <label class="apos-label">
                    <span><?php esc_html_e('Add-on Price', 'antalya-pos'); ?> <span class="required-star">*</span></span>
                </label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $cur; ?></span>
                    <input type="number" step="0.01" min="0" name="extra_price" class="apos-input has-prefix" placeholder="0.00" value="<?php echo !empty($edit_extra) ? esc_attr($edit_extra->price) : ''; ?>" required>
                </div>
            </div>
        </div>

        <!-- Extra Image Upload Field -->
        <div class="apos-form-group" style="margin-bottom: 24px;">
            <label class="apos-label">
                <span><?php esc_html_e('Add-on Image / Icon', 'antalya-pos'); ?></span>
                <small><?php esc_html_e('(Optional)', 'antalya-pos'); ?></small>
            </label>
            <div class="ex-media-row">
                <input type="hidden" name="extra_image" id="apos_extra_image_input" value="<?php echo esc_url($extra_image_url); ?>">

                <div id="apos_extra_img_preview" class="ex-media-preview-box <?php echo !empty($extra_image_url) ? 'has-img' : ''; ?>">
                    <?php if (!empty($extra_image_url)) : ?>
                        <img src="<?php echo esc_url($extra_image_url); ?>" alt="<?php esc_attr_e('Add-on preview', 'antalya-pos'); ?>">
                    <?php else : ?>
                        <span class="ex-media-placeholder"><?php echo $svg_image; ?></span>
                    <?php endif; ?>
                </div>

                <div class="ex-media-actions">
                    <button type="button" id="apos_upload_extra_img_btn" class="apos-btn-secondary ex-btn-icon" style="font-weight: 700;">
                        <?php echo $svg_image; ?>
                        <span><?php esc_html_e('Choose Image', 'antalya-pos'); ?></span>
                    </button>
                    <button type="button" id="apos_remove_extra_img_btn" class="apos-btn-secondary ex-media-remove-btn" style="<?php echo empty($extra_image_url) ? 'display:none;' : ''; ?>">
                        <?php esc_html_e('Remove', 'antalya-pos'); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="ex-form-footer">
            <button type="submit" name="rtpos_save_extra" class="apos-btn ex-btn-icon">
                <?php echo $svg_check; ?>
                <span><?php echo !empty($edit_extra) ? esc_html__('Update Extra', 'antalya-pos') : esc_html__('Save Extra', 'antalya-pos'); ?></span>
            </button>
            <a href="<?php echo $all_extras_url; ?>" class="apos-btn-secondary"><?php esc_html_e('Cancel', 'antalya-pos'); ?></a>
        </div>
    </form>
</div>

<style>
    .required-star {
        color: #ef4444;
        font-weight: 800;
        margin-left: 3px;
    }
    .ex-media-row {
        display: flex;
        gap: 14px;
        align-items: center;
        flex-wrap: wrap;
    }
    .ex-media-preview-box {
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
    .ex-media-preview-box.has-img {
        border-style: solid;
        border-color: #e2e8f0;
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.06);
    }
    .ex-media-preview-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .ex-media-placeholder {
        color: #94a3b8;
        display: inline-flex;
    }
    .ex-media-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .ex-media-remove-btn {
        color: #dc2626 !important;
        border-color: #fee2e2 !important;
        background: #fef2f2 !important;
    }
    .ex-media-remove-btn:hover {
        background: #fee2e2 !important;
    }
</style>

<script>
jQuery(document).ready(function($) {
    let mediaUploader;
    let placeholderIcon = <?php echo wp_json_encode($svg_image); ?>;

    $('#apos_upload_extra_img_btn').on('click', function(e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: <?php echo wp_json_encode(__('Choose Add-on Image', 'antalya-pos')); ?>,
            button: { text: <?php echo wp_json_encode(__('Use Image', 'antalya-pos')); ?> },
            multiple: false
        });

        mediaUploader.on('select', function() {
            let attachment = mediaUploader.state().get('selection').first().toJSON();
            let imgUrl = attachment.url || '';
            $('#apos_extra_image_input').val(imgUrl);

            let imgEl = $('<img>', {
                src: imgUrl,
                alt: <?php echo wp_json_encode(__('Add-on preview', 'antalya-pos')); ?>
            });
            $('#apos_extra_img_preview').addClass('has-img').empty().append(imgEl);
            $('#apos_remove_extra_img_btn').show();
        });

        mediaUploader.open();
    });

    $('#apos_remove_extra_img_btn').on('click', function() {
        $('#apos_extra_image_input').val('');
        $('#apos_extra_img_preview')
            .removeClass('has-img')
            .html('<span class="ex-media-placeholder">' + placeholderIcon + '</span>');
        $(this).hide();
    });
});
</script>