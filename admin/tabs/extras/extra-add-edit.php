<?php
if (!defined('ABSPATH')) exit;

$svg_sparkles = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/></svg>';
$svg_edit     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_tag      = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><path d="M7 7h.01"/></svg>';
$svg_check    = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>';
$svg_image    = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';

$extra_image_url = ($edit_extra && isset($edit_extra->image)) ? $edit_extra->image : '';
?>

<div class="apos-card apos-full-card">
    <div class="apos-form-header">
        <h3><?php echo $edit_extra ? $svg_edit . '<span>Edit Extra: ' . esc_html($edit_extra->name) . '</span>' : $svg_sparkles . '<span>Add New Extra / Add-on</span>'; ?></h3>
        <p>After saving, you can attach this add-on to any dish from the <strong>Items</strong> menu.</p>
    </div>

    <form method="post" action="?page=antalya-pos&tab=extras&subtab=all">
        <input type="hidden" name="edit_extra_id" value="<?php echo $edit_extra ? intval($edit_extra->id) : ''; ?>">

        <div class="ex-form-grid">
            <div class="apos-form-group">
                <label class="apos-label">Extra Name <span class="required-star">*</span> <small>(e.g. Extra Cheese)</small></label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $svg_tag; ?></span>
                    <input type="text" name="extra_name" class="apos-input has-prefix" placeholder="Garlic Sauce" value="<?php echo $edit_extra ? esc_attr($edit_extra->name) : ''; ?>" required>
                </div>
            </div>

            <div class="apos-form-group">
                <label class="apos-label">Add-on Price <span class="required-star">*</span></label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $cur; ?></span>
                    <input type="number" step="0.01" name="extra_price" class="apos-input has-prefix" placeholder="0.00" value="<?php echo $edit_extra ? esc_attr($edit_extra->price) : ''; ?>" required>
                </div>
            </div>
        </div>

        <!-- Extra Image Upload Field -->
        <div class="apos-form-group" style="margin-bottom: 24px;">
            <label class="apos-label">Add-on Image / Icon <small>(Optional)</small></label>
            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="extra_image" id="apos_extra_image_input" value="<?php echo esc_attr($extra_image_url); ?>">
                
                <div id="apos_extra_img_preview" style="width: 64px; height: 64px; border: 2px dashed #cbd5e1; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: #f8fafc; overflow: hidden; position: relative;">
                    <?php if (!empty($extra_image_url)) : ?>
                        <img src="<?php echo esc_url($extra_image_url); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else : ?>
                        <span style="color: #94a3b8;"><?php echo $svg_image; ?></span>
                    <?php endif; ?>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button type="button" id="apos_upload_extra_img_btn" class="apos-btn-secondary" style="font-weight: 700;">
                        <?php echo $svg_image; ?> Choose Image
                    </button>
                    <button type="button" id="apos_remove_extra_img_btn" class="apos-btn-secondary" style="color: #dc2626; border-color: #fee2e2; background: #fef2f2; <?php echo empty($extra_image_url) ? 'display:none;' : ''; ?>">Remove</button>
                </div>
            </div>
        </div>

        <div class="ex-form-footer">
            <button type="submit" name="rtpos_save_extra" class="apos-btn ex-btn-icon">
                <?php echo $svg_check; ?>
                <span><?php echo $edit_extra ? 'Update Extra' : 'Save Extra'; ?></span>
            </button>
            <a href="?page=antalya-pos&tab=extras&subtab=all" class="apos-btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
    .required-star {
        color: #ef4444;
        font-weight: 800;
        margin-left: 3px;
    }
</style>

<!-- WordPress Media Uploader Integration Script -->
<script>
jQuery(document).ready(function($) {
    let mediaUploader;
    
    $('#apos_upload_extra_img_btn').on('click', function(e) {
        e.preventDefault();
        
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Add-on Image',
            button: { text: 'Use Image' },
            multiple: false
        });
        
        mediaUploader.on('select', function() {
            let attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#apos_extra_image_input').val(attachment.url);
            $('#apos_extra_img_preview').html('<img src="' + attachment.url + '" style="width: 100%; height: 100%; object-fit: cover;">');
            $('#apos_remove_extra_img_btn').show();
        });
        
        mediaUploader.open();
    });

    $('#apos_remove_extra_img_btn').on('click', function() {
        $('#apos_extra_image_input').val('');
        $('#apos_extra_img_preview').html('<span style="color: #94a3b8;"><?php echo addslashes($svg_image); ?></span>');
        $(this).hide();
    });
});
</script>