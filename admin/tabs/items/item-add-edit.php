<?php
if (!defined('ABSPATH')) exit;

$svg_utensils = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>';
$svg_edit     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_upload   = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>';
$svg_sparkles = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/></svg>';
$svg_plus     = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
$svg_check    = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
$svg_close    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
$svg_order    = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>';
?>

<div class="apos-card apos-full-card">
    <div class="apos-form-header">
        <h3><?php echo $edit_item ? $svg_edit . '<span>Edit Dish: ' . esc_html($edit_item->name) . '</span>' : $svg_utensils . '<span>Add New Food Item</span>'; ?></h3>
        <p>Enter dish details below. Changes reflect immediately on all customer tablets.</p>
    </div>

    <form method="post" action="?page=antalya-pos&tab=items&subtab=all">
        <input type="hidden" name="edit_id" value="<?php echo $edit_item ? intval($edit_item->id) : ''; ?>">

        <div class="itm-form-grid-top">
            <div class="apos-form-group">
                <label class="apos-label">Dish Name <span>*</span></label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $svg_utensils; ?></span>
                    <input type="text" name="item_name" class="apos-input has-prefix" placeholder="e.g. Turkish Adana Kebab" value="<?php echo $edit_item ? esc_attr($edit_item->name) : ''; ?>" required>
                </div>
            </div>

            <div class="apos-form-group">
                <label class="apos-label">Category <span>*</span></label>
                <select name="item_cat" class="apos-input" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($cats as $c) : ?>
                        <option value="<?php echo esc_attr($c->name); ?>" <?php selected($edit_item ? $edit_item->category : '', $c->name); ?>><?php echo esc_html($c->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="itm-form-grid-bot">
            <div class="apos-form-group">
                <label class="apos-label">Price <span>*</span></label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $cur; ?></span>
                    <input type="number" step="0.01" name="item_price" class="apos-input has-prefix" placeholder="0.00" value="<?php echo $edit_item ? esc_attr($edit_item->price) : ''; ?>" required>
                </div>
            </div>

            <div class="apos-form-group">
                <label class="apos-label">Display Order / Number <small>(Lower numbers appear first)</small></label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $svg_order; ?></span>
                    <input type="number" name="item_order" class="apos-input has-prefix" placeholder="0" value="<?php echo $edit_item && isset($edit_item->item_order) ? intval($edit_item->item_order) : '0'; ?>">
                </div>
            </div>
        </div>

        <div class="apos-form-group">
            <label class="apos-label">Dish Photo <small>(Upload or Paste URL)</small></label>
            <div class="itm-photo-row">
                <img id="rtpos-img-preview" class="itm-photo-preview" src="<?php echo esc_url(($edit_item && $edit_item->image_url) ? $edit_item->image_url : 'https://via.placeholder.com/80?text=Photo'); ?>" alt="Preview">
                <input type="url" name="item_img" id="rtpos-item-img" class="apos-input" value="<?php echo $edit_item ? esc_attr($edit_item->image_url) : ''; ?>" placeholder="https://...">
                <button type="button" id="rtpos-upload-btn" class="apos-btn-secondary itm-btn-icon itm-upload-btn"><?php echo $svg_upload; ?> Upload</button>
            </div>
        </div>

        <!-- Extras & Add-ons Section -->
        <div class="apos-form-group">
            <div class="apos-label apos-label-mb">
                <span>Attach Available Extras <small>(Optional add-ons)</small></span>
                <div class="itm-ex-controls">
                    <?php if (!empty($extras)) : ?>
                        <button type="button" id="rtpos-select-all-ex" class="apos-btn-secondary itm-sel-all-btn">Select All</button>
                    <?php endif; ?>
                    <button type="button" id="rtpos-toggle-quick-ex" class="itm-btn-icon itm-quick-ex-trigger"><?php echo $svg_plus; ?> <span>Quick New Extra</span></button>
                </div>
            </div>

            <!-- Inline Quick-Create Extra Drawer -->
            <div class="quick-extra-box" id="rtpos-quick-ex-box">
                <div class="quick-extra-title"><?php echo $svg_sparkles; ?> <span>Create & Auto-Attach New Add-on:</span></div>
                <div class="quick-extra-row">
                    <input type="text" id="q-ex-name" class="apos-input" placeholder="Extra name (e.g. Extra Cheese)">
                    <div class="apos-input-wrap"><span class="apos-input-prefix"><?php echo $cur; ?></span><input type="number" step="0.01" id="q-ex-price" class="apos-input has-prefix" placeholder="Price"></div>
                    <button type="button" id="q-ex-save-btn" class="apos-btn itm-btn-icon"><?php echo $svg_check; ?> Save & Attach</button>
                    <button type="button" id="q-ex-cancel-btn" class="apos-btn-secondary"><?php echo $svg_close; ?></button>
                </div>
            </div>

            <!-- Modern Switch Grid for Extras -->
            <div class="apos-switch-grid" id="rtpos-extras-grid">
                <?php if (empty($extras)) : ?>
                    <span id="rtpos-no-extras-msg" style="color:#64748b; font-size:13px;">No extras created yet. Click <strong>"Quick New Extra"</strong> above!</span>
                <?php else : foreach ($extras as $ex) : 
                    $is_checked = in_array($ex->id, $edit_extras);
                ?>
                    <label class="apos-switch-pill <?php echo $is_checked ? 'checked' : ''; ?>">
                        <div class="apos-switch-info">
                            <span class="apos-switch-name"><?php echo esc_html($ex->name); ?></span>
                            <span class="apos-switch-price">+<?php echo $cur . esc_html($ex->price); ?></span>
                        </div>
                        <div class="apos-switch-control">
                            <input type="checkbox" name="item_extras[]" value="<?php echo intval($ex->id); ?>" <?php checked($is_checked); ?>>
                            <span class="apos-slider"></span>
                        </div>
                    </label>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <div class="itm-form-footer">
            <button type="submit" name="rtpos_save_item" class="apos-btn itm-btn-icon"><?php echo $svg_check; ?> <span><?php echo $edit_item ? 'Update Food Item' : 'Save Food Item'; ?></span></button>
            <a href="?page=antalya-pos&tab=items&subtab=all" class="apos-btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
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
    let cur = <?php echo json_encode($s['currency']); ?>;
    let checkSvg = <?php echo json_encode($svg_check); ?>;

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
        let frame = wp.media({ title: 'Select Food Photo', button: { text: 'Use Photo' }, multiple: false });
        frame.on('select', function() {
            let attachment = frame.state().get('selection').first().toJSON();
            $('#rtpos-item-img').val(attachment.url);
            $('#rtpos-img-preview').attr('src', attachment.url);
        });
        frame.open();
    });

    $('#rtpos-item-img').on('input', function() {
        let url = $(this).val();
        if (url) $('#rtpos-img-preview').attr('src', url);
    });

    $('#rtpos-toggle-quick-ex').on('click', function() { $('#rtpos-quick-ex-box').slideToggle(180); $('#q-ex-name').focus(); });
    $('#q-ex-cancel-btn').on('click', function() { $('#rtpos-quick-ex-box').slideUp(150); });

    $('#q-ex-save-btn').on('click', function() {
        let name = $('#q-ex-name').val().trim(), price = $('#q-ex-price').val();
        if (!name) return alert('Please enter extra name!');
        let btn = $(this);
        btn.prop('disabled', true);
        $.post(ajaxurl, { action: 'rtpos_quick_add_extra', name: name, price: price || 0 }, function(res) {
            if (res.success) {
                $('#rtpos-no-extras-msg').remove();
                let switchHtml = `
                    <label class="apos-switch-pill checked">
                        <div class="apos-switch-info">
                            <span class="apos-switch-name">${res.data.name}</span>
                            <span class="apos-switch-price">+${cur}${res.data.price}</span>
                        </div>
                        <div class="apos-switch-control">
                            <input type="checkbox" name="item_extras[]" value="${res.data.id}" checked>
                            <span class="apos-slider"></span>
                        </div>
                    </label>`;
                $('#rt-extras-grid').append(switchHtml);
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
        $(this).text(allSelected ? 'Clear All' : 'Select All');
    });
});
</script>