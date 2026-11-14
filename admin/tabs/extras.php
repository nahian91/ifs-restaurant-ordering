<?php
/**
 * Antalya POS Extra Add-ons Controller & Router
 *
 * @package Antalya_POS
 * @since 6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

function rtpos_view_extras($s = []) {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'antalya-pos'));
    }

    global $wpdb;

    // Safe fallback for settings & currency if called without parameters
    if (empty($s) && function_exists('rtpos_get_settings')) {
        $s = rtpos_get_settings();
    }
    $cur      = esc_html($s['currency'] ?? '৳');
    $t_extras = $wpdb->prefix . 'rtpos_extras';
    $t_menu   = $wpdb->prefix . 'rtpos_menu';

    $svg_check = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
    $svg_alert = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';

    // Include Delete Handler Safely
    $extras_dir = plugin_dir_path(__FILE__) . 'extras/';
    if (file_exists($extras_dir . 'extra-delete.php')) {
        include_once $extras_dir . 'extra-delete.php';
    }

    // 1. Handle Save / Update Extra
    if (isset($_POST['rtpos_save_extra']) && !empty($_POST['extra_name'])) {
        if (isset($_POST['_wpnonce'])) {
            check_admin_referer('rtpos_save_extra_action');
        }

        $extra_name  = sanitize_text_field(wp_unslash($_POST['extra_name']));
        $extra_price = max(0.0, floatval($_POST['extra_price'] ?? 0));

        $data = [
            'name'  => $extra_name,
            'price' => $extra_price,
        ];
        $formats = ['%s', '%f'];

        if (!empty($_POST['edit_extra_id'])) {
            $edit_id = intval($_POST['edit_extra_id']);
            $wpdb->update($t_extras, $data, ['id' => $edit_id], $formats, ['%d']);
            echo '<div class="apos-alert ex-alert-flex">' . $svg_check . '<span>' . esc_html__('Extra add-on updated successfully!', 'antalya-pos') . '</span></div>';
        } else {
            $exists = $wpdb->get_var(
                $wpdb->prepare("SELECT id FROM {$t_extras} WHERE name = %s", $extra_name)
            );
            if ($exists) {
                echo '<div class="apos-alert ex-alert-flex" style="background:#fef2f2; border-color:#fecaca; color:#991b1b;">' . $svg_alert . '<span>' . sprintf(
                    /* translators: %s: extra add-on name */
                    esc_html__('Extra "%s" already exists!', 'antalya-pos'),
                    esc_html($extra_name)
                ) . '</span></div>';
            } else {
                $wpdb->insert($t_extras, $data, $formats);
                echo '<div class="apos-alert ex-alert-flex">' . $svg_check . '<span>' . esc_html__('New extra add-on created!', 'antalya-pos') . '</span></div>';
            }
        }
    }

    // Routing: 'all', 'add', or 'view'
    $allowed_subtabs = ['all', 'add', 'view'];
    $raw_subtab      = isset($_GET['subtab']) ? sanitize_key($_GET['subtab']) : 'all';
    $subtab          = in_array($raw_subtab, $allowed_subtabs, true) ? $raw_subtab : 'all';
    $edit_extra      = null;
    $view_extra      = null;

    if (isset($_GET['edit_extra'])) {
        $edit_extra = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$t_extras} WHERE id = %d", intval($_GET['edit_extra']))
        );
        $subtab = 'add';
    } elseif (isset($_GET['view_extra'])) {
        $view_extra = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$t_extras} WHERE id = %d", intval($_GET['view_extra']))
        );
        $subtab = 'view';
    }

    $extras    = $wpdb->get_results("SELECT * FROM {$t_extras} ORDER BY id DESC") ?: [];
    $all_items = $wpdb->get_results("SELECT id, name, category, price, image_url, extras FROM {$t_menu} ORDER BY name ASC") ?: [];

    // Map full dish objects by extra ID
    $dishes_by_extra = [];
    foreach ($all_items as $itm) {
        if (!empty($itm->extras)) {
            $ids = json_decode($itm->extras, true);
            if (is_array($ids)) {
                foreach ($ids as $eid) {
                    $dishes_by_extra[intval($eid)][] = $itm;
                }
            }
        }
    }
    ?>

    <style>
        .ex-alert-flex { display: flex; align-items: center; gap: 8px; }
        .ex-full-wrap { width: 100%; box-sizing: border-box; }
        .apos-full-card { width: 100%; max-width: 100%; box-sizing: border-box; }
        .apos-full-table { width: 100%; max-width: 100%; }

        /* Form & Controls */
        .apos-form-header { border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 22px; }
        .apos-form-header h3 { margin: 0; font-size: 18px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; }
        .apos-form-header p { margin: 5px 0 0; font-size: 13px; color: #64748b; }
        .apos-form-group { margin-bottom: 18px; width: 100%; }
        .apos-label { display: flex; align-items: center; justify-content: space-between; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 7px; }
        .apos-label small { font-weight: 500; color: #64748b; font-size: 12px; }
        .apos-input-wrap { position: relative; display: flex; align-items: center; width: 100%; }
        .apos-input-prefix { position: absolute; left: 14px; font-weight: 800; color: #64748b; font-size: 15px; pointer-events: none; display: inline-flex; align-items: center; }
        .apos-input {
            width: 100%; padding: 11px 15px; border: 1.5px solid #e2e8f0; border-radius: 11px;
            font-size: 14px; font-weight: 500; color: #0f172a; background-color: #f8fafc; box-sizing: border-box;
        }
        .apos-input.has-prefix { padding-left: 40px; }
        .apos-input:focus { outline: none; background-color: #fff; border-color: #f97316; box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.14); }

        .ex-form-grid { display: grid; grid-template-columns: 1.6fr 1fr; gap: 20px; width: 100%; }
        @media (max-width: 768px) { .ex-form-grid { grid-template-columns: 1fr; } }
        .ex-form-footer { margin-top: 22px; padding-top: 16px; border-top: 1px solid #f1f5f9; display: flex; gap: 10px; }

        /* Empty State */
        .ex-empty-card { text-align: center; padding: 60px 20px; }
        .ex-empty-ico { width: 56px; height: 56px; border-radius: 16px; background: #fff7ed; color: #f97316; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px; }
        .ex-empty-title { margin: 0 0 8px; font-size: 19px; }
        .ex-empty-desc { color: #64748b; margin: 0 0 20px; font-size: 14px; }

        /* Table & Cards */
        .col-ex-id { width: 80px; }
        .col-ex-price { width: 150px; }
        .col-ex-used { width: 180px; }
        .col-ex-act { width: 230px; }

        .ex-name-link { font-size: 15px; font-weight: 700; color: #0f172a; text-decoration: none; }
        .ex-name-link:hover { color: #f97316; }
        .ex-price-val { color: #f97316; font-size: 15px; }

        .ex-used-badge { padding: 4px 11px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block; }
        .ex-badge-active { background: #ecfdf5; color: #059669; }
        .ex-badge-empty { background: #f1f5f9; color: #64748b; }

        .ex-act-group { display: flex; gap: 7px; align-items: center; }
        .ex-act-btn { padding: 6px 11px; border-radius: 7px; font-size: 12px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .act-view { background: #eff6ff; color: #1d4ed8; }
        .act-edit { background: #f1f5f9; color: #334155; }
        .act-del  { background: #fef2f2; color: #dc2626; }
        .ex-btn-icon { display: inline-flex; align-items: center; gap: 7px; }
    </style>

    <div class="ex-full-wrap">
        <?php
        if (file_exists($extras_dir . 'extra-tabs.php')) {
            include $extras_dir . 'extra-tabs.php';
        }

        if ($subtab === 'view' && $view_extra && file_exists($extras_dir . 'extra-view.php')) {
            include $extras_dir . 'extra-view.php';
        } elseif ($subtab === 'add' && file_exists($extras_dir . 'extra-add-edit.php')) {
            include $extras_dir . 'extra-add-edit.php';
        } elseif (file_exists($extras_dir . 'extra-list.php')) {
            include $extras_dir . 'extra-list.php';
        }
        ?>
    </div>
    <?php
}