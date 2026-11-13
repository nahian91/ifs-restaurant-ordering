<?php
if (!defined('ABSPATH')) exit;

// AJAX Handler: Quick-Create Extra directly from Items Form
add_action('wp_ajax_rtpos_quick_add_extra', 'rtpos_ajax_quick_add_extra');
function rtpos_ajax_quick_add_extra() {
    global $wpdb;
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $name  = sanitize_text_field($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);

    if (empty($name)) wp_send_json_error('Extra name is required');

    $t_extras = $wpdb->prefix . 'rtpos_extras';
    $wpdb->insert($t_extras, ['name' => $name, 'price' => $price]);

    wp_send_json_success([
        'id'    => $wpdb->insert_id,
        'name'  => $name,
        'price' => number_format($price, 2, '.', '')
    ]);
}

function rtpos_view_items($s) {
    global $wpdb;
    $cur      = esc_html($s['currency']);
    $t_menu   = $wpdb->prefix . 'rtpos_menu';
    $t_cats   = $wpdb->prefix . 'rtpos_categories';
    $t_extras = $wpdb->prefix . 'rtpos_extras';

    // Include Delete Handler
    include_once plugin_dir_path(__FILE__) . 'items/item-delete.php';

    // 1. Handle Save / Update Dish
    if (isset($_POST['rtpos_save_item'])) {
        $selected_extras = isset($_POST['item_extras']) ? array_map('intval', $_POST['item_extras']) : [];
        $data = [
            'name'      => sanitize_text_field($_POST['item_name']),
            'category'  => sanitize_text_field($_POST['item_cat']),
            'price'     => floatval($_POST['item_price']),
            'image_url' => esc_url_raw($_POST['item_img']),
            'extras'    => wp_json_encode($selected_extras)
        ];

        if (!empty($_POST['edit_id'])) {
            $wpdb->update($t_menu, $data, ['id' => intval($_POST['edit_id'])]);
            echo '<div class="apos-alert itm-alert-flex"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg><span>Dish updated successfully!</span></div>';
        } else {
            $wpdb->insert($t_menu, $data);
            echo '<div class="apos-alert itm-alert-flex"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg><span>New dish added to menu!</span></div>';
        }
    }

    // Routing: 'all', 'add', or 'view'
    $subtab    = isset($_GET['subtab']) ? sanitize_key($_GET['subtab']) : 'all';
    $edit_item = null;
    $view_item = null;

    if (isset($_GET['edit_item'])) {
        $edit_item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t_menu WHERE id = %d", intval($_GET['edit_item'])));
        $subtab    = 'add';
    } elseif (isset($_GET['view_item'])) {
        $view_item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t_menu WHERE id = %d", intval($_GET['view_item'])));
        $subtab    = 'view';
    }
    $edit_extras = $edit_item ? (json_decode($edit_item->extras, true) ?: []) : [];

    $cats       = $wpdb->get_results("SELECT * FROM $t_cats ORDER BY name ASC") ?: [];
    $extras     = $wpdb->get_results("SELECT * FROM $t_extras ORDER BY name ASC") ?: [];
    $extras_map = $wpdb->get_results("SELECT * FROM $t_extras", OBJECT_K) ?: [];
    $items      = $wpdb->get_results("SELECT * FROM $t_menu ORDER BY id DESC") ?: [];
    ?>

    <style>
        .itm-alert-flex { display: flex; align-items: center; gap: 8px; }
        .itm-full-wrap { width: 100%; box-sizing: border-box; }
        .apos-full-card { width: 100%; max-width: 100%; box-sizing: border-box; }
        .apos-full-table { width: 100%; max-width: 100%; }

        /* Form Styles */
        .apos-form-header { border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 22px; }
        .apos-form-header h3 { margin: 0; font-size: 18px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; }
        .apos-form-header p { margin: 5px 0 0; font-size: 13px; color: #64748b; }
        .apos-form-group { margin-bottom: 18px; width: 100%; }
        .apos-label { display: flex; align-items: center; justify-content: space-between; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 7px; }
        .apos-label-mb { margin-bottom: 8px; }
        .apos-label small { font-weight: 500; color: #64748b; font-size: 12px; }
        .apos-input-wrap { position: relative; display: flex; align-items: center; width: 100%; }
        .apos-input-prefix { position: absolute; left: 14px; font-weight: 800; color: #64748b; font-size: 14.5px; pointer-events: none; }
        .apos-input {
            width: 100%; padding: 11px 15px; border: 1.5px solid #e2e8f0; border-radius: 11px;
            font-size: 14px; font-weight: 500; color: #0f172a; background-color: #f8fafc; box-sizing: border-box;
        }
        .apos-input.has-prefix { padding-left: 40px; }
        .apos-input:focus { outline: none; background-color: #fff; border-color: #f97316; box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.14); }
        select.apos-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; }

        .itm-form-grid-top { display: grid; grid-template-columns: 1.3fr 1fr; gap: 20px; width: 100%; }
        .itm-form-grid-bot { display: grid; grid-template-columns: 1fr 1.8fr; gap: 20px; width: 100%; }
        @media (max-width: 768px) { .itm-form-grid-top, .itm-form-grid-bot { grid-template-columns: 1fr; } }

        .itm-photo-row { display: flex; gap: 10px; align-items: center; }
        .itm-photo-preview { width: 45px; height: 45px; border-radius: 10px; object-fit: cover; border: 1.5px solid #e2e8f0; flex-shrink: 0; }
        .itm-upload-btn { height: 44px; white-space: nowrap; }

        .itm-ex-controls { display: flex; gap: 8px; align-items: center; }
        .itm-sel-all-btn { padding: 4px 10px; font-size: 11.5px; }
        .itm-quick-ex-trigger { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; padding: 5px 11px; border-radius: 7px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; }
        
        .apos-check-grid { display: flex; flex-wrap: wrap; gap: 10px; background: #f8fafc; padding: 14px; border-radius: 12px; border: 1.5px dashed #cbd5e1; }
        .apos-check-pill { display: inline-flex; align-items: center; gap: 8px; background: #fff; border: 1.5px solid #e2e8f0; padding: 8px 14px; border-radius: 10px; font-size: 13.5px; font-weight: 600; color: #334155; cursor: pointer; }
        .apos-check-pill input[type="checkbox"] { width: 16px; height: 16px; accent-color: #f97316; margin: 0; }
        .apos-check-pill:has(input:checked) { border-color: #f97316; background: #fff7ed; color: #c2410c; }
        .itm-ex-price { color: #f97316; }

        .quick-extra-box { display: none; background: #fff7ed; border: 1.5px solid #fed7aa; padding: 16px; border-radius: 12px; margin-bottom: 12px; }
        .quick-extra-row { display: grid; grid-template-columns: 1.6fr 1fr auto auto; gap: 10px; align-items: center; }
        @media (max-width: 640px) { .quick-extra-row { grid-template-columns: 1fr; } }

        .itm-form-footer { margin-top: 22px; padding-top: 16px; border-top: 1px solid #f1f5f9; display: flex; gap: 10px; }
        .col-itm-act { width: 235px; }
        .itm-act-btn { padding: 6px 11px; border-radius: 7px; font-size: 12px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .act-view { background: #eff6ff; color: #1d4ed8; }
        .act-edit { background: #f1f5f9; color: #334155; }
        .act-del  { background: #fef2f2; color: #dc2626; }
        .itm-btn-icon { display: inline-flex; align-items: center; gap: 7px; }
    </style>

    <div class="itm-full-wrap">
        <?php
        // Include Sub-Tabs Header
        include plugin_dir_path(__FILE__) . 'items/item-tabs.php';

        if ($subtab === 'view' && $view_item) {
            include plugin_dir_path(__FILE__) . 'items/item-view.php';
        } elseif ($subtab === 'add') {
            include plugin_dir_path(__FILE__) . 'items/item-add-edit.php';
        } else {
            include plugin_dir_path(__FILE__) . 'items/item-list.php';
        }
        ?>
    </div>
    <?php
}