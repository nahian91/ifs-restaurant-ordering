<?php
/**
 * Antalya POS Menu Items Table List & Availability Switcher
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

$svg_utensils_lg = '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>';
$svg_plus        = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
$svg_edit        = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_eye         = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
$svg_trash       = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';

$base_items_url   = admin_url('admin.php?page=antalya-pos&tab=items');
$add_item_url     = esc_url(add_query_arg('subtab', 'add', $base_items_url));
$avail_ajax_nonce = wp_create_nonce('rtpos_toggle_avail_nonce');
$placeholder_img  = 'https://via.placeholder.com/150?text=Dish';
?>

<?php if (empty($items)) : ?>
    <div class="apos-card apos-full-card itm-empty-card">
        <div class="itm-empty-ico">
            <?php echo $svg_utensils_lg; ?>
        </div>
        <h3 class="itm-empty-title"><?php esc_html_e('No Dishes Added Yet', 'antalya-pos'); ?></h3>
        <p class="itm-empty-desc"><?php esc_html_e('Start building your digital restaurant menu by adding your first delicious food item.', 'antalya-pos'); ?></p>
        <a href="<?php echo $add_item_url; ?>" class="apos-btn itm-btn-icon">
            <?php echo $svg_plus; ?>
            <span><?php esc_html_e('Add First Dish', 'antalya-pos'); ?></span>
        </a>
    </div>
<?php else : ?>
    <div class="apos-card apos-full-card" style="padding: 0; overflow: hidden; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);">
        <table class="apos-table apos-full-table" style="margin: 0; border: none;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0;">
                    <th class="col-itm-order" style="padding: 16px 14px; text-align: center;"><?php esc_html_e('Order', 'antalya-pos'); ?></th>
                    <th class="col-itm-photo" style="padding: 16px 20px;"><?php esc_html_e('Photo', 'antalya-pos'); ?></th>
                    <th style="padding: 16px 14px;"><?php esc_html_e('Item Name', 'antalya-pos'); ?></th>
                    <th class="col-itm-cat" style="padding: 16px 14px;"><?php esc_html_e('Category', 'antalya-pos'); ?></th>
                    <th class="col-itm-price" style="padding: 16px 14px;"><?php esc_html_e('Price', 'antalya-pos'); ?></th>
                    <th class="col-itm-avail" style="padding: 16px 14px; text-align: center;"><?php esc_html_e('Availability', 'antalya-pos'); ?></th>
                    <th class="col-itm-act" style="padding: 16px 20px; text-align: right;"><?php esc_html_e('Actions', 'antalya-pos'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item) :
                    $item_id    = intval($item->id);
                    $img_src    = !empty($item->image_url) ? $item->image_url : $placeholder_img;
                    $item_order = isset($item->item_order) ? intval($item->item_order) : 0;
                    $is_avail   = isset($item->is_available) ? intval($item->is_available) : 1;

                    $view_url   = esc_url(add_query_arg('view_item', $item_id, $base_items_url));
                    $edit_url   = esc_url(add_query_arg('edit_item', $item_id, $base_items_url));
                    $del_url    = wp_nonce_url(
                        add_query_arg(['subtab' => 'all', 'del_item' => $item_id], $base_items_url),
                        'rtpos_delete_item_' . $item_id
                    );
                ?>
                <tr class="itm-row" data-name="<?php echo esc_attr(strtolower($item->name)); ?>" data-cat="<?php echo esc_attr(strtolower($item->category)); ?>" style="transition: background 0.15s ease;">
                    <td style="padding: 14px 14px; vertical-align: middle; text-align: center;">
                        <span class="itm-order-badge"><?php echo $item_order; ?></span>
                    </td>
                    <td style="padding: 14px 20px; vertical-align: middle;">
                        <a href="<?php echo $view_url; ?>" style="display: block;">
                            <img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr($item->name); ?>" class="itm-thumb">
                        </a>
                    </td>
                    <td style="padding: 14px 14px; vertical-align: middle;">
                        <a href="<?php echo $view_url; ?>" class="itm-name-link">
                            <?php echo esc_html($item->name); ?>
                        </a>
                    </td>
                    <td style="padding: 14px 14px; vertical-align: middle;">
                        <span class="itm-cat-badge"><?php echo esc_html($item->category); ?></span>
                    </td>
                    <td style="padding: 14px 14px; vertical-align: middle;">
                        <strong class="itm-price-val"><?php echo $cur . esc_html(number_format((float) $item->price, 2)); ?></strong>
                    </td>
                    <!-- Availability Switch Column -->
                    <td style="padding: 14px 14px; vertical-align: middle; text-align: center;">
                        <label class="apos-switch" title="<?php esc_attr_e('Toggle Availability', 'antalya-pos'); ?>">
                            <input type="checkbox" class="rtpos-toggle-avail" data-id="<?php echo $item_id; ?>" <?php checked($is_avail, 1); ?>>
                            <span class="apos-slider"></span>
                        </label>
                    </td>
                    <td style="padding: 14px 20px; vertical-align: middle; text-align: right;">
                        <div class="itm-act-group" style="justify-content: flex-end;">
                            <a href="<?php echo $view_url; ?>" class="itm-act-btn act-view" title="<?php esc_attr_e('View Details', 'antalya-pos'); ?>">
                                <?php echo $svg_eye; ?>
                                <span><?php esc_html_e('View', 'antalya-pos'); ?></span>
                            </a>
                            <a href="<?php echo $edit_url; ?>" class="itm-act-btn act-edit" title="<?php esc_attr_e('Edit Dish', 'antalya-pos'); ?>">
                                <?php echo $svg_edit; ?>
                                <span><?php esc_html_e('Edit', 'antalya-pos'); ?></span>
                            </a>
                            <a href="<?php echo esc_url($del_url); ?>" class="itm-act-btn act-del js-confirm-del" data-name="<?php echo esc_attr($item->name); ?>" title="<?php esc_attr_e('Delete Dish', 'antalya-pos'); ?>">
                                <?php echo $svg_trash; ?>
                                <span><?php esc_html_e('Delete', 'antalya-pos'); ?></span>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<style>
    .col-itm-order { width: 70px; }
    .col-itm-photo { width: 90px; }
    .col-itm-cat { width: 170px; }
    .col-itm-price { width: 130px; }
    .col-itm-avail { width: 120px; }
    .col-itm-act { width: 230px; }

    .itm-order-badge {
        background: #f8fafc; color: #475569; border: 1px solid #cbd5e1;
        width: 30px; height: 30px; border-radius: 50%; display: inline-flex;
        align-items: center; justify-content: center; font-size: 12px; font-weight: 800;
        font-family: 'Courier New', Courier, monospace;
    }

    .itm-thumb {
        width: 52px; height: 52px; object-fit: cover; border-radius: 12px;
        border: 1.5px solid #e2e8f0; display: block; box-shadow: 0 2px 5px rgba(15, 23, 42, 0.04);
        transition: transform 0.2s ease;
    }
    .itm-thumb:hover { transform: scale(1.05); }

    .itm-name-link { font-size: 15px; font-weight: 800; color: #0f172a; text-decoration: none; transition: color 0.15s; }
    .itm-name-link:hover { color: #f97316; }

    .itm-cat-badge {
        background: #f1f5f9; color: #334155; padding: 5px 12px; border-radius: 20px;
        font-size: 12px; font-weight: 700; display: inline-block;
    }
    .itm-price-val { font-size: 15.5px; color: #0f172a; font-family: 'Courier New', Courier, monospace; }

    /* iOS Style Toggle Switch Styles */
    .apos-switch {
        position: relative; display: inline-block; width: 38px; height: 22px; vertical-align: middle;
    }
    .apos-switch input { opacity: 0; width: 0; height: 0; }
    .apos-slider {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1; transition: .2s cubic-bezier(0.16, 1, 0.3, 1); border-radius: 22px;
    }
    .apos-slider:before {
        position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px;
        background-color: white; transition: .2s cubic-bezier(0.16, 1, 0.3, 1); border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }
    .apos-switch input:checked + .apos-slider { background-color: #10b981; }
    .apos-switch input:focus + .apos-slider { box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15); }
    .apos-switch input:checked + .apos-slider:before { transform: translateX(16px); }

    .itm-act-group { display: flex; gap: 6px; align-items: center; }
    .itm-act-btn {
        padding: 6px 11px; border-radius: 8px; font-size: 12px; font-weight: 700;
        text-decoration: none; cursor: pointer; border: none; transition: all 0.2s ease;
        display: inline-flex; align-items: center; gap: 5px;
    }
    .act-view { background: #eff6ff; color: #1d4ed8; }
    .act-view:hover { background: #dbeafe; }
    .act-edit { background: #f1f5f9; color: #334155; }
    .act-edit:hover { background: #e2e8f0; color: #0f172a; }
    .act-del { background: #fef2f2; color: #dc2626; }
    .act-del:hover { background: #fee2e2; }

    .itm-empty-card { text-align: center; padding: 60px 20px; background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; }
    .itm-empty-ico {
        width: 64px; height: 64px; border-radius: 18px; background: #fff7ed; color: #f97316;
        display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.1);
    }
    .itm-empty-title { margin: 0 0 8px; font-size: 20px; font-weight: 800; color: #0f172a; }
    .itm-empty-desc { color: #64748b; margin: 0 0 22px; font-size: 14px; }
    .itm-btn-icon { display: inline-flex; align-items: center; gap: 7px; padding: 11px 20px; font-weight: 700; border-radius: 12px; }

    .itm-row:hover { background-color: #fafaf9; }
</style>

<script>
jQuery(document).ready(function($) {
    let availNonce = <?php echo wp_json_encode($avail_ajax_nonce); ?>;

    $(document).on('click', '.js-confirm-del', function(e) {
        let itemName = $(this).data('name') || <?php echo wp_json_encode(__('this item', 'antalya-pos')); ?>;
        let msg      = <?php echo wp_json_encode(__('Are you sure you want to delete "%s"?', 'antalya-pos')); ?>.replace('%s', itemName);
        if (!confirm(msg)) {
            e.preventDefault();
        }
    });

    // AJAX Handler for Live Availability Toggle with CSRF Nonce & Rollback
    $(document).on('change', '.rtpos-toggle-avail', function() {
        let checkbox = $(this);
        let itemId   = checkbox.data('id');
        let isAvail  = checkbox.is(':checked') ? 1 : 0;

        checkbox.prop('disabled', true);
        $.post(ajaxurl, {
            action: 'rtpos_toggle_item_availability',
            _ajax_nonce: availNonce,
            id: itemId,
            available: isAvail
        }).done(function(res) {
            if (!res || !res.success) {
                checkbox.prop('checked', !isAvail);
            }
        }).fail(function() {
            checkbox.prop('checked', !isAvail);
        }).always(function() {
            checkbox.prop('disabled', false);
        });
    });

    function filterItems() {
        let q   = ($('#itm-live-search').val() || '').toLowerCase();
        let cat = ($('#itm-cat-filter').val() || 'all').toLowerCase();

        $('.itm-row').each(function() {
            let nameAttr = String($(this).data('name') || '');
            let catAttr  = String($(this).data('cat') || '');
            let matchName = nameAttr.indexOf(q) > -1;
            let matchCat  = (cat === 'all') || (catAttr === cat);
            $(this).toggle(matchName && matchCat);
        });
    }
    $('#itm-live-search').on('keyup', filterItems);
    $('#itm-cat-filter').on('change', filterItems);
});
</script>