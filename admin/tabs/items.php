<?php
/**
 * Antalya POS Menu Items Controller & Router
 *
 * @package Antalya_POS
 * @since 6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ensure extended item columns exist on rtpos_menu
 */
function rtpos_ensure_menu_item_cols() {
    global $wpdb;
    $t_menu = $wpdb->prefix . 'rtpos_menu';
    $cols   = $wpdb->get_col("DESC {$t_menu}", 0);

    if (is_array($cols)) {
        if (!in_array('ratio', $cols, true)) {
            $wpdb->query("ALTER TABLE {$t_menu} ADD COLUMN ratio varchar(50) DEFAULT '' AFTER price");
        }
        if (!in_array('item_order', $cols, true)) {
            $wpdb->query("ALTER TABLE {$t_menu} ADD COLUMN item_order int(5) NOT NULL DEFAULT 0 AFTER ratio");
        }
        if (!in_array('description', $cols, true)) {
            $wpdb->query("ALTER TABLE {$t_menu} ADD COLUMN description text AFTER item_order");
        }
        if (!in_array('is_available', $cols, true)) {
            $wpdb->query("ALTER TABLE {$t_menu} ADD COLUMN is_available tinyint(1) NOT NULL DEFAULT 1 AFTER extras");
        }
    }
}

/**
 * AJAX Handler: Quick-Create Extra directly from Items Form
 */
add_action('wp_ajax_rtpos_quick_add_extra', 'rtpos_ajax_quick_add_extra');
function rtpos_ajax_quick_add_extra() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Unauthorized access.', 'antalya-pos'), 403);
    }

    if (isset($_POST['_ajax_nonce'])) {
        check_ajax_referer('rtpos_quick_add_extra_nonce', '_ajax_nonce');
    }

    global $wpdb;
    $name  = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;

    if (empty($name)) {
        wp_send_json_error(__('Extra name is required.', 'antalya-pos'));
    }

    $t_extras = $wpdb->prefix . 'rtpos_extras';
    $inserted = $wpdb->insert(
        $t_extras,
        [
            'name'  => $name,
            'price' => $price,
        ],
        ['%s', '%f']
    );

    if ($inserted === false) {
        wp_send_json_error(__('Failed to save extra.', 'antalya-pos'));
    }

    wp_send_json_success([
        'id'    => intval($wpdb->insert_id),
        'name'  => esc_html($name),
        'price' => number_format($price, 2, '.', ''),
    ]);
}

/**
 * AJAX Handler: Toggle Item Live Availability (In Stock / Out of Stock)
 */
add_action('wp_ajax_rtpos_toggle_item_availability', 'rtpos_ajax_toggle_item_availability');
function rtpos_ajax_toggle_item_availability() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Unauthorized access.', 'antalya-pos'), 403);
    }

    if (isset($_POST['_ajax_nonce'])) {
        check_ajax_referer('rtpos_toggle_avail_nonce', '_ajax_nonce');
    }

    global $wpdb;
    $id        = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $available = (isset($_POST['available']) && intval($_POST['available']) === 1) ? 1 : 0;

    if ($id <= 0) {
        wp_send_json_error(__('Invalid item ID.', 'antalya-pos'));
    }

    $t_menu = $wpdb->prefix . 'rtpos_menu';
    $wpdb->update(
        $t_menu,
        ['is_available' => $available],
        ['id' => $id],
        ['%d'],
        ['%d']
    );

    wp_send_json_success(['id' => $id, 'is_available' => $available]);
}

/**
 * Render Menu Items Controller & Sub-Tab Router
 */
function rtpos_view_items($s) {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'antalya-pos'));
    }

    global $wpdb;
    rtpos_ensure_menu_item_cols();

    $cur      = esc_html($s['currency'] ?? '৳');
    $t_menu   = $wpdb->prefix . 'rtpos_menu';
    $t_cats   = $wpdb->prefix . 'rtpos_categories';
    $t_extras = $wpdb->prefix . 'rtpos_extras';

    $svg_check = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

    // Include Delete Handler Safely
    $items_dir = plugin_dir_path(__FILE__) . 'items/';
    if (file_exists($items_dir . 'item-delete.php')) {
        include_once $items_dir . 'item-delete.php';
    }

    // 1. Handle Save / Update Dish
    if (isset($_POST['rtpos_save_item'])) {
        if (isset($_POST['_wpnonce'])) {
            check_admin_referer('rtpos_save_item_action');
        }

        $raw_extras      = isset($_POST['item_extras']) && is_array($_POST['item_extras']) ? wp_unslash($_POST['item_extras']) : [];
        $selected_extras = array_map('intval', $raw_extras);

        $data = [
            'name'        => sanitize_text_field(wp_unslash($_POST['item_name'] ?? '')),
            'category'    => sanitize_text_field(wp_unslash($_POST['item_cat'] ?? '')),
            'price'       => floatval($_POST['item_price'] ?? 0),
            'ratio'       => sanitize_text_field(wp_unslash($_POST['item_ratio'] ?? '')),
            'item_order'  => intval($_POST['item_order'] ?? 0),
            'description' => sanitize_textarea_field(wp_unslash($_POST['item_description'] ?? '')),
            'image_url'   => esc_url_raw(wp_unslash($_POST['item_img'] ?? '')),
            'extras'      => wp_json_encode($selected_extras),
        ];
        $formats = ['%s', '%s', '%f', '%s', '%d', '%s', '%s', '%s'];

        if (!empty($_POST['edit_id'])) {
            $edit_id = intval($_POST['edit_id']);
            $wpdb->update($t_menu, $data, ['id' => $edit_id], $formats, ['%d']);
            echo '<div class="apos-alert itm-alert-flex">' . $svg_check . '<span>' . esc_html__('Dish updated successfully!', 'antalya-pos') . '</span></div>';
        } else {
            $wpdb->insert($t_menu, $data, $formats);
            echo '<div class="apos-alert itm-alert-flex">' . $svg_check . '<span>' . esc_html__('New dish added to menu!', 'antalya-pos') . '</span></div>';
        }
    }

    // Routing: 'all', 'add', or 'view'
    $allowed_subtabs = ['all', 'add', 'view'];
    $raw_subtab      = isset($_GET['subtab']) ? sanitize_key($_GET['subtab']) : 'all';
    $subtab          = in_array($raw_subtab, $allowed_subtabs, true) ? $raw_subtab : 'all';
    $edit_item       = null;
    $view_item       = null;

    if (isset($_GET['edit_item'])) {
        $edit_item = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$t_menu} WHERE id = %d", intval($_GET['edit_item'])));
        $subtab    = 'add';
    } elseif (isset($_GET['view_item'])) {
        $view_item = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$t_menu} WHERE id = %d", intval($_GET['view_item'])));
        $subtab    = 'view';
    }

    $edit_extras = [];
    if ($edit_item && !empty($edit_item->extras)) {
        $decoded_ex = json_decode($edit_item->extras, true);
        if (is_array($decoded_ex)) {
            $edit_extras = $decoded_ex;
        }
    }

    $cats       = $wpdb->get_results("SELECT * FROM {$t_cats} ORDER BY category_order ASC, name ASC") ?: [];
    $extras     = $wpdb->get_results("SELECT * FROM {$t_extras} ORDER BY name ASC") ?: [];
    $extras_map = $wpdb->get_results("SELECT * FROM {$t_extras}", OBJECT_K) ?: [];
    $items      = $wpdb->get_results("SELECT * FROM {$t_menu} ORDER BY item_order ASC, id DESC") ?: [];
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
        .apos-input-prefix { position: absolute; left: 14px; font-weight: 800; color: #64748b; font-size: 14.5px; pointer-events: none; display: inline-flex; align-items: center; }
        .apos-input {
            width: 100%; padding: 11px 15px; border: 1.5px solid #e2e8f0; border-radius: 11px;
            font-size: 14px; font-weight: 500; color: #0f172a; background-color: #f8fafc; box-sizing: border-box;
        }
        .apos-input.has-prefix { padding-left: 40px; }
        .apos-input:focus { outline: none; background-color: #fff; border-color: #f97316; box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.14); }
        select.apos-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; }

        .itm-form-grid-top { display: grid; grid-template-columns: 1.3fr 1fr; gap: 20px; width: 100%; }
        .itm-form-grid-bot { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; width: 100%; }
        @media (max-width: 768px) { .itm-form-grid-top, .itm-form-grid-bot { grid-template-columns: 1fr; } }

        .itm-photo-row { display: flex; gap: 10px; align-items: center; }
        .itm-photo-preview { width: 48px; height: 48px; border-radius: 10px; object-fit: cover; border: 1.5px solid #e2e8f0; flex-shrink: 0; }
        .itm-upload-btn { height: 44px; white-space: nowrap; }

        .itm-ex-controls { display: flex; gap: 8px; align-items: center; }
        .itm-sel-all-btn { padding: 4px 10px; font-size: 11.5px; }
        .itm-quick-ex-trigger { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; padding: 5px 11px; border-radius: 7px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; }

        .quick-extra-box { display: none; background: #fff7ed; border: 1.5px solid #fed7aa; padding: 16px; border-radius: 12px; margin-bottom: 12px; }
        .quick-extra-title { font-size: 13px; font-weight: 700; color: #c2410c; margin-bottom: 10px; display: flex; align-items: center; gap: 6px; }
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
        if (file_exists($items_dir . 'item-tabs.php')) {
            include $items_dir . 'item-tabs.php';
        }

        if ($subtab === 'view' && $view_item && file_exists($items_dir . 'item-view.php')) {
            include $items_dir . 'item-view.php';
        } elseif ($subtab === 'add' && file_exists($items_dir . 'item-add-edit.php')) {
            include $items_dir . 'item-add-edit.php';
        } elseif (file_exists($items_dir . 'item-list.php')) {
            include $items_dir . 'item-list.php';
        }
        ?>
    </div>
    <?php
}