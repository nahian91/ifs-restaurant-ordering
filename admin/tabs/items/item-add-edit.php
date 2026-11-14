<?php
/**
 * Antalya POS Add / Edit Food Item Form View
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

$svg_utensils = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>';
$svg_edit     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_upload   = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>';
$svg_sparkles = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/></svg>';
$svg_plus     = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
$svg_check    = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
$svg_close    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
$svg_order    = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>';
$svg_file     = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>';
$svg_ratio    = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v12M6 12h12"/></svg>';

$all_items_url    = esc_url(admin_url('admin.php?page=antalya-pos&tab=items&subtab=all'));
$quick_extra_nonce = wp_create_nonce('rtpos_quick_add_extra_nonce');
$placeholder_img   = 'https://via.placeholder.com/80?text=Photo';
$preview_img       = (!empty($edit_item) && !empty($edit_item->image_url)) ? $edit_item->image_url : $placeholder_img;
?>

<div class="apos-card apos-full-card">
    <div class="apos-form-header">
        <h3>
            <?php if (!empty($edit_item)) : ?>
                <?php echo $svg_edit; ?>
                <span>
                    <?php
                    printf(
                        /* translators: %s: dish name */
                        esc_html__('Edit Dish: %s', 'antalya-pos'),
                        esc_html($edit_item->name)
                    );
                    ?>
                </span>
            <?php else : ?>
                <?php echo $svg_utensils; ?>
                <span><?php esc_html_e('Add New Food Item', 'antalya-pos'); ?></span>
            <?php endif; ?>
        </h3>
        <p><?php esc_html_e('Enter dish details below. Changes reflect immediately on all customer tablets.', 'antalya-pos'); ?></p>
    </div>

    <form method="post" action="<?php echo $all_items_url; ?>">
        <?php wp_nonce_field('rtpos_save_item_action'); ?>
        <input type="hidden" name="edit_id" value="<?php echo !empty($edit_item) ? intval($edit_item->id) : ''; ?>">

        <div class="itm-form-grid-top">
            <div class="apos-form-group">
                <label class="apos-label"><?php esc_html_e('Dish Name', 'antalya-pos'); ?> <span class="required-star">*</span></label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $svg_utensils; ?></span>
                    <input type="text" name="item_name" class="apos-input has-prefix" placeholder="<?php esc_attr_e('e.g. Turkish Adana Kebab', 'antalya-pos'); ?>" value="<?php echo !empty($edit_item) ? esc_attr($edit_item->name) : ''; ?>" required>
                </div>
            </div>

            <div class="apos-form-group">
                <label class="apos-label"><?php esc_html_e('Category', 'antalya-pos'); ?> <span class="required-star">*</span></label>
                <select name="item_cat" class="apos-input" required>
                    <option value=""><?php esc_html_e('-- Select Category --', 'antalya-pos'); ?></option>
                    <?php if (!empty($cats) && is_array($cats)) : foreach ($cats as $c) : ?>
                        <option value="<?php echo esc_attr($c->name); ?>" <?php selected(!empty($edit_item) ? $edit_item->category : '', $c->name); ?>><?php echo esc_html($c->name); ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
        </div>

        <div class="itm-form-grid-bot">
            <div class="apos-form-group" style="margin: 0;">
                <label class="apos-label"><?php esc_html_e('Price', 'antalya-pos'); ?> <span class="required-star">*</span></label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $cur; ?></span>
                    <input type="number" step="0.01" min="0" name="item_price" class="apos-input has-prefix" placeholder="0.00" value="<?php echo !empty($edit_item) ? esc_attr($edit_item->price) : ''; ?>" required>
                </div>
            </div>

            <div class="apos-form-group" style="margin: 0;">
                <label class="apos-label"><?php esc_html_e('Portion / Ratio', 'antalya-pos'); ?> <small><?php esc_html_e('(Optional)', 'antalya-pos'); ?></small></label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $svg_ratio; ?></span>
                    <input type="text" name="item_ratio" class="apos-input has-prefix" placeholder="<?php esc_attr_e('e.g. 1:1, 1:2, 1:4', 'antalya-pos'); ?>" value="<?php echo (!empty($edit_item) && isset($edit_item->ratio)) ? esc_attr($edit_item->ratio) : ''; ?>">
                </div>
            </div>

            <div class="apos-form-group" style="margin: 0;">
                <label class="apos-label"><?php esc_html_e('Display Order / Number', 'antalya-pos'); ?> <small><?php esc_html_e('(Lower first)', 'antalya-pos'); ?></small></label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $svg_order; ?></span>
                    <input type="number" name="item_order" class="apos-input has-prefix" placeholder="0" value="<?php echo (!empty($edit_item) && isset($edit_item->item_order)) ? intval($edit_item->item_order) : '0'; ?>">
                </div>
            </div>
        </div>

        <!-- Short Description / Ingredients Field -->
        <div class="apos-form-group" style="margin-top: 20px;">
            <label class="apos-label"><?php esc_html_e('Short Description / Ingredients', 'antalya-pos'); ?> <small><?php esc_html_e('(Optional)', 'antalya-pos'); ?></small></label>
            <div class="apos-input-wrap">
                <span class="apos-input-prefix" style="align-items: flex-start; padding-top: 12px;"><?php echo $svg_file; ?></span>
                <textarea name="item_description" class="apos-input has-prefix" rows="3" placeholder="<?php esc_attr_e('e.g. Minced lamb meat, red bell peppers, special Turkish spices...', 'antalya-pos'); ?>" style="resize: vertical; padding-top: 10px;"><?php echo (!empty($edit_item) && isset($edit_item->description)) ? esc_textarea($edit_item->description) : ''; ?></textarea>
            </div>
        </div>

        <div class="apos-form-group">
            <label class="apos-label"><?php esc_html_e('Dish Photo', 'antalya-pos'); ?> <small><?php esc_html_e('(Upload or Paste URL)', 'antalya-pos'); ?></small></label>
            <div class="itm-photo-row">
                <img id="rtpos-img-preview" class="itm-photo-preview" src="<?php echo esc_url($preview_img); ?>" alt="<?php esc_attr_e('Preview', 'antalya-pos'); ?>">
                <input type="url" name="item_img" id="rtpos-item-img" class="apos-input" value="<?php echo !empty($edit_item) ? esc_attr($edit_item->image_url) : ''; ?>" placeholder="https://...">
                <button type="button" id="rtpos-upload-btn" class="apos-btn-secondary itm-btn-icon itm-upload-btn">
                    <?php echo $svg_upload; ?>
                    <span><?php esc_html_e('Upload', 'antalya-pos'); ?></span>
                </button>
            </div>
        </div>

        <!-- Extras & Add-ons Section -->
        <div class="apos-form-group">
            <div class="apos-label apos-label-mb">
                <span><?php esc_html_e('Attach Available Extras', 'antalya-pos'); ?> <small><?php esc_html_e('(Optional add-ons)', 'antalya-pos'); ?></small></span>
                <div class="itm-ex-controls">
                    <?php if (!empty($extras)) : ?>
                        <button type="button" id="rtpos-select-all-ex" class="apos-btn-secondary itm-sel-all-btn"><?php esc_html_e('Select All', 'antalya-pos'); ?></button>
                    <?php endif; ?>
                    <button type="button" id="rtpos-toggle-quick-ex" class="itm-btn-icon itm-quick-ex-trigger">
                        <?php echo $svg_plus; ?>
                        <span><?php esc_html_e('Quick New Extra', 'antalya-pos'); ?></span>
                    </button>
                </div>
            </div>

            <!-- Inline Quick-Create Extra Drawer -->
            <div class="quick-extra-box" id="rtpos-quick-ex-box">
                <div class="quick-extra-title"><?php echo $svg_sparkles; ?> <span><?php esc_html_e('Create & Auto-Attach New Add-on:', 'antalya-pos'); ?></span></div>
                <div class="quick-extra-row">
                    <input type="text" id="q-ex-name" class="apos-input" placeholder="<?php esc_attr_e('Extra name (e.g. Extra Cheese)', 'antalya-pos'); ?>">
                    <div class="apos-input-wrap">
                        <span class="apos-input-prefix"><?php echo $cur; ?></span>
                        <input type="number" step="0.01" min="0" id="q-ex-price" class="apos-input has-prefix" placeholder="<?php esc_attr_e('Price', 'antalya-pos'); ?>">
                    </div>
                    <button type="button" id="q-ex-save-btn" class="apos-btn itm-btn-icon">
                        <?php echo $svg_check; ?>
                        <span><?php esc_html_e('Save & Attach', 'antalya-pos'); ?></span>
                    </button>
                    <button type="button" id="q-ex-cancel-btn" class="apos-btn-secondary" title="<?php esc_attr_e('Close', 'antalya-pos'); ?>"><?php echo $svg_close; ?></button>
                </div>
            </div>

            <!-- Modern Switch Grid for Extras -->
            <div class="apos-switch-grid" id="rtpos-extras-grid">
                <?php if (empty($extras)) : ?>
                    <span id="rtpos-no-extras-msg" style="color:#64748b; font-size:13px;">
                        <?php esc_html_e('No extras created yet. Click "Quick New Extra" above!', 'antalya-pos'); ?>
                    </span>
                <?php else : foreach ($extras as $ex) :
                    $ex_id      = intval($ex->id);
                    $is_checked = in_array($ex_id, array_map('intval', $edit_extras), true);
                ?>
                    <label class="apos-switch-pill <?php echo $is_checked ? 'checked' : ''; ?>">
                        <div class="apos-switch-info">
                            <span class="apos-switch-name"><?php echo esc_html($ex->name); ?></span>
                            <span class="apos-switch-price">+<?php echo $cur . esc_html(number_format((float) $ex->price, 2)); ?></span>
                        </div>
                        <div class="apos-switch-control">
                            <input type="checkbox" name="item_extras[]" value="<?php echo $ex_id; ?>" <?php checked($is_checked); ?>>
                            <span class="apos-slider"></span>
                        </div>
                    </label>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <div class="itm-form-footer">
            <button type="submit" name="rtpos_save_item" class="apos-btn itm-btn-icon">
                <?php echo $svg_check; ?>
                <span><?php echo !empty($edit_item) ? esc_html__('Update Food Item', 'antalya-pos') : esc_html__('Save Food Item', 'antalya-pos'); ?></span>
            </button>
            <a href="<?php echo $all_items_url; ?>" class="apos-btn-secondary"><?php esc_html_e('Cancel', 'antalya-pos'); ?></a>
        </div>
    </form>
</div>

<style>
    .required-star { color: #ef4444; font-weight: 800; margin-left: 3px; }

    /* Modern iOS Switch Grid Design */
    .apos-switch-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px;
        background: #f8fafc; padding: 16px; border-radius: 14px; border: 1.5px dashed #cbd5e1;
    }
    .apos-switch-pill {
        display: flex; justify-content: space-between; align-items: center;
        background: #fff; border: 1.5px solid #e2e8f0; padding: 12px 16px;
        border-radius: 12px; cursor: pointer; transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        user-select: none; box-shadow: 0 2px 4px rgba(15, 23, 42, 0.02);
    }
    .apos-switch-pill:hover { border-color: #cbd5e1; background: #fafaf9; transform: translateY(-1px); }
    .apos-switch-pill.checked {
        border-color: #f97316; background: #fff7ed;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.08);
    }
    .apos-switch-info { display: flex; flex-direction: column; gap: 2px; }
    .apos-switch-name { font-size: 13.5px; font-weight: 700; color: #0f172a; }
    .apos-switch-pill.checked .apos-switch-name { color: #c2410c; }
    .apos-switch-price { font-size: 12px; font-weight: 700; color: #f97316; font-family: 'Courier New', Courier, monospace; }

    /* Switch Toggle Button Styles */
    .apos-switch-control { position: relative; display: inline-block; width: 42px; height: 22px; flex-shrink: 0; }
    .apos-switch-control input { opacity: 0; width: 0; height: 0; }
    .apos-slider {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1; transition: .25s cubic-bezier(0.16, 1, 0.3, 1); border-radius: 22px;
    }
    .apos-slider:before {
        position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px;
        background-color: white; transition: .25s cubic-bezier(0.16, 1, 0.3, 1); border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }
    .apos-switch-control input:checked + .apos-slider { background-color: #f97316; }
    .apos-switch-control input:focus + .apos-slider { box-shadow: 0 0 1px #f97316; }
    .apos-switch-control input:checked + .apos-slider:before { transform: translateX(20px); }
</style>

<script>
jQuery(document).ready(function($) {
    let cur           = <?php echo wp_json_encode($s['currency'] ?? '৳'); ?>;
    let quickExNonce  = <?php echo wp_json_encode($quick_extra_nonce); ?>;
    let placeholder   = <?php echo wp_json_encode($placeholder_img); ?>;

    function escHtml(str) {
        return String(str === null || str === undefined ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Toggle Switch Active Class on Change
    $(document).on('change', '.apos-switch-control input[type="checkbox"]', function() {
        if ($(this).is(':checked')) {
            $(this).closest('.apos-switch-pill').addClass('checked');
        } else {
            $(this).closest('.apos-switch-pill').removeClass('checked');
        }
    });

    $('#rtpos-upload-btn').on('click', function(e) {
        e.preventDefault();
        let frame = wp.media({
            title: <?php echo wp_json_encode(__('Select Food Photo', 'antalya-pos')); ?>,
            button: { text: <?php echo wp_json_encode(__('Use Photo', 'antalya-pos')); ?> },
            multiple: false
        });
        frame.on('select', function() {
            let attachment = frame.state().get('selection').first().toJSON();
            $('#rtpos-item-img').val(attachment.url);
            $('#rtpos-img-preview').attr('src', attachment.url);
        });
        frame.open();
    });

    $('#rtpos-item-img').on('input', function() {
        let url = $(this).val().trim();
        $('#rtpos-img-preview').attr('src', url ? url : placeholder);
    });

    $('#rtpos-toggle-quick-ex').on('click', function() {
        $('#rtpos-quick-ex-box').slideToggle(180);
        $('#q-ex-name').focus();
    });
    $('#q-ex-cancel-btn').on('click', function() {
        $('#rtpos-quick-ex-box').slideUp(150);
    });

    $('#q-ex-save-btn').on('click', function() {
        let name  = $('#q-ex-name').val().trim();
        let price = $('#q-ex-price').val();
        if (!name) {
            alert(<?php echo wp_json_encode(__('Please enter extra name!', 'antalya-pos')); ?>);
            return;
        }
        let btn = $(this);
        btn.prop('disabled', true);

        $.post(ajaxurl, {
            action: 'rtpos_quick_add_extra',
            _ajax_nonce: quickExNonce,
            name: name,
            price: price || 0
        }, function(res) {
            if (res && res.success && res.data) {
                $('#rtpos-no-extras-msg').remove();
                let exId    = parseInt(res.data.id, 10);
                let exName  = escHtml(res.data.name);
                let exPrice = escHtml(res.data.price);

                let switchHtml = `
                    <label class="apos-switch-pill checked">
                        <div class="apos-switch-info">
                            <span class="apos-switch-name">${exName}</span>
                            <span class="apos-switch-price">+${escHtml(cur)}${exPrice}</span>
                        </div>
                        <div class="apos-switch-control">
                            <input type="checkbox" name="item_extras[]" value="${exId}" checked>
                            <span class="apos-slider"></span>
                        </div>
                    </label>`;
                $('#rtpos-extras-grid').append(switchHtml);
                $('#q-ex-name, #q-ex-price').val('');
                $('#rtpos-quick-ex-box').slideUp(150);
            }
            btn.prop('disabled', false);
        });
    });

    let allSelected = false;
    $('#rtpos-select-all-ex').on('click', function() {
        allSelected = !allSelected;
        $('#rtpos-extras-grid input[type="checkbox"]').prop('checked', allSelected).trigger('change');
        $(this).text(allSelected
            ? <?php echo wp_json_encode(__('Clear All', 'antalya-pos')); ?>
            : <?php echo wp_json_encode(__('Select All', 'antalya-pos')); ?>
        );
    });
});
</script>