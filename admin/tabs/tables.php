<?php
/**
 * Antalya POS Table Management Controller & Router
 *
 * @package Antalya_POS
 * @since 6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ensure required authentication & waiter columns exist on rtpos_tables
 */
function rtpos_ensure_table_auth_cols() {
    global $wpdb;
    $t_tables = $wpdb->prefix . 'rtpos_tables';
    $cols     = $wpdb->get_col("DESC {$t_tables}", 0);

    if (is_array($cols)) {
        if (!in_array('table_login', $cols, true)) {
            $wpdb->query("ALTER TABLE {$t_tables} ADD COLUMN table_login varchar(150) DEFAULT '' AFTER seats");
        }
        if (!in_array('table_password', $cols, true)) {
            $wpdb->query("ALTER TABLE {$t_tables} ADD COLUMN table_password varchar(150) DEFAULT '' AFTER table_login");
        }
        if (!in_array('assigned_waiter', $cols, true)) {
            $wpdb->query("ALTER TABLE {$t_tables} ADD COLUMN assigned_waiter varchar(150) DEFAULT '' AFTER table_password");
        }
    }
}

/**
 * Render Table Management Views & Handle Form Submissions
 */
function rtpos_view_tables($s) {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'antalya-pos'));
    }

    global $wpdb;
    rtpos_ensure_table_auth_cols();

    $t_tables  = $wpdb->prefix . 'rtpos_tables';
    $t_orders  = $wpdb->prefix . 'rtpos_orders';
    $t_staff   = $wpdb->prefix . 'rtpos_staff';
    $base_url  = rtrim($s['menu_page_url'] ?? home_url('/menu/'), '/');
    $cur       = esc_html($s['currency'] ?? '৳');
    $rest_name = esc_html($s['restaurant_name'] ?? 'Antalya');

    $svg_check = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
    $svg_alert = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';

    // Include Handlers Safely
    $tables_dir = plugin_dir_path(__FILE__) . 'tables/';
    if (file_exists($tables_dir . 'table-delete.php')) {
        include_once $tables_dir . 'table-delete.php';
    }

    // 1. Handle Single Add / Update Table
    if (isset($_POST['rtpos_save_table']) && !empty($_POST['table_name'])) {
        check_admin_referer('rtpos_save_table_action');

        $tbl_name    = sanitize_text_field(wp_unslash($_POST['table_name']));
        $table_login = sanitize_text_field(wp_unslash($_POST['table_login'] ?? ''));
        $table_pass  = sanitize_text_field(wp_unslash($_POST['table_password'] ?? ''));
        $waiter      = sanitize_text_field(wp_unslash($_POST['assigned_waiter'] ?? ''));
        $seats       = max(1, intval($_POST['seats'] ?? 4));

        $data = [
            'table_name'      => $tbl_name,
            'seats'           => $seats,
            'table_login'     => $table_login,
            'table_password'  => $table_pass,
            'assigned_waiter' => $waiter,
        ];
        $formats = ['%s', '%d', '%s', '%s', '%s'];

        if (!empty($_POST['edit_table_id'])) {
            $edit_id = intval($_POST['edit_table_id']);
            $wpdb->update($t_tables, $data, ['id' => $edit_id], $formats, ['%d']);
            echo '<div class="apos-alert tbl-alert-flex">' . $svg_check . '<span>' . esc_html__('Table updated successfully!', 'antalya-pos') . '</span></div>';
        } else {
            $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$t_tables} WHERE table_name = %s", $tbl_name));
            if ($exists) {
                echo '<div class="apos-alert tbl-alert-flex tbl-alert-error">' . $svg_alert . '<span>' . sprintf(
                    /* translators: %s: table name */
                    esc_html__('Table "%s" already exists!', 'antalya-pos'),
                    esc_html($tbl_name)
                ) . '</span></div>';
            } else {
                $wpdb->insert($t_tables, $data, $formats);
                echo '<div class="apos-alert tbl-alert-flex">' . $svg_check . '<span>' . esc_html__('New table created successfully!', 'antalya-pos') . '</span></div>';
            }
        }
    }

    // 2. Handle Bulk Generate
    if (isset($_POST['rtpos_bulk_tables'])) {
        check_admin_referer('rtpos_bulk_tables_action');

        $prefix   = sanitize_text_field(wp_unslash($_POST['bulk_prefix'] ?? ''));
        $start    = max(1, intval($_POST['bulk_start'] ?? 1));
        $end      = min(100, intval($_POST['bulk_end'] ?? 1));
        $seats    = max(1, intval($_POST['bulk_seats'] ?? 4));
        $def_pass = sanitize_text_field(wp_unslash($_POST['bulk_password'] ?? ''));
        $waiter   = sanitize_text_field(wp_unslash($_POST['bulk_waiter'] ?? ''));
        $added    = 0;

        if ($end >= $start) {
            for ($i = $start; $i <= $end; $i++) {
                $num_str = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                $t_name  = $prefix . $num_str;
                $exists  = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$t_tables} WHERE table_name = %s", $t_name));

                if (!$exists) {
                    $auto_login = 'table_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $t_name));
                    $auto_pass  = !empty($def_pass) ? $def_pass : ('tbl' . $num_str . wp_rand(100, 999));

                    $wpdb->insert(
                        $t_tables,
                        [
                            'table_name'      => $t_name,
                            'seats'           => $seats,
                            'table_login'     => $auto_login,
                            'table_password'  => $auto_pass,
                            'assigned_waiter' => $waiter,
                        ],
                        ['%s', '%d', '%s', '%s', '%s']
                    );
                    $added++;
                }
            }
            echo '<div class="apos-alert tbl-alert-flex">' . $svg_check . '<span>' . sprintf(
                /* translators: %d: count of generated tables */
                esc_html__('Bulk generated %d table(s)!', 'antalya-pos'),
                intval($added)
            ) . '</span></div>';
        }
    }

    // Routing Sub-Tabs
    $allowed_subtabs = ['all', 'add', 'view'];
    $raw_subtab      = isset($_GET['subtab']) ? sanitize_key($_GET['subtab']) : 'all';
    $subtab          = in_array($raw_subtab, $allowed_subtabs, true) ? $raw_subtab : 'all';
    $edit_tbl        = null;
    $view_tbl        = null;

    if (isset($_GET['edit_table'])) {
        $edit_tbl = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$t_tables} WHERE id = %d", intval($_GET['edit_table'])));
        $subtab   = 'add';
    } elseif (isset($_GET['view_table'])) {
        $view_tbl = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$t_tables} WHERE id = %d", intval($_GET['view_table'])));
        $subtab   = 'view';
    }

    $tables     = $wpdb->get_results("SELECT * FROM {$t_tables} ORDER BY id ASC") ?: [];
    $staff_list = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$t_staff} WHERE status = %s ORDER BY name ASC", 'Active')) ?: [];

    $active_rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT table_no, COUNT(*) as ord_cnt, SUM(total_amount) as running_total 
             FROM {$t_orders} WHERE status != %s GROUP BY table_no",
            'completed'
        ),
        OBJECT_K
    ) ?: [];

    $total_seats    = 0;
    $occupied_count = 0;
    foreach ($tables as $t) {
        $total_seats += intval($t->seats);
        if (isset($active_rows[$t->table_name])) {
            $occupied_count++;
        }
    }
    $free_count = max(0, count($tables) - $occupied_count);
    ?>

    <style>
        .tbl-alert-flex { display: flex; align-items: center; gap: 8px; }
        .tbl-alert-error { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .tbl-full-wrap { width: 100%; box-sizing: border-box; }
        .apos-full-card { width: 100%; max-width: 100%; box-sizing: border-box; }
        .tbl-card-no-margin { margin: 0; }

        .apos-form-header { border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 20px; }
        .apos-form-header h3 { margin: 0; font-size: 18px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; }
        .apos-form-header p { margin: 5px 0 0; font-size: 13px; color: #64748b; }
        .apos-form-group { margin-bottom: 18px; width: 100%; }
        .apos-label { display: flex; align-items: center; justify-content: space-between; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 7px; }
        .apos-label small { font-weight: 500; color: #64748b; font-size: 12px; }
        .apos-input {
            width: 100%; padding: 11px 15px; border: 1.5px solid #e2e8f0; border-radius: 11px;
            font-size: 14px; font-weight: 500; color: #0f172a; background-color: #f8fafc; box-sizing: border-box;
        }
        .apos-input:focus { outline: none; background-color: #fff; border-color: #f97316; box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.14); }
        select.apos-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; }

        .tbl-two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; width: 100%; }
        .tbl-three-col { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; width: 100%; }
        @media (max-width: 640px) { .tbl-two-col, .tbl-three-col { grid-template-columns: 1fr; } }

        .tbl-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; width: 100%; }
        .tbl-card { background: #fff; padding: 22px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03); display: flex; flex-direction: column; justify-content: space-between; }
        .tbl-card-free { border-top: 4px solid #10b981; }
        .tbl-card-busy { border-top: 4px solid #f59e0b; }
        .tbl-card-head { display: flex; justify-content: space-between; align-items: center; }
        .tbl-card-link { text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .tbl-card-title { margin: 0; font-size: 18px; color: #0f172a; font-weight: 800; }
        .tbl-status-pill { padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; }
        .tbl-pill-free { background: #d1fae5; color: #059669; }
        .tbl-pill-busy { background: #fef3c7; color: #d97706; }
        .tbl-seats-txt { color: #64748b; font-size: 13px; margin: 8px 0; }

        .tbl-cred-badge { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 9px; padding: 8px 11px; margin-bottom: 10px; font-size: 12px; display: flex; flex-direction: column; gap: 4px; }
        .tbl-cred-row { display: flex; justify-content: space-between; align-items: center; }
        .tbl-cred-pass { background: #fff; padding: 1px 6px; border-radius: 5px; border: 1px solid #e2e8f0; font-weight: 700; color: #ea580c; }

        .tbl-running-box { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 10px; padding: 9px 12px; margin-bottom: 10px; display: flex; justify-content: space-between; font-size: 12.5px; }
        .tbl-url-row { display: flex; gap: 6px; }
        .tbl-url-input { font-size: 12px; padding: 8px 10px; background: #f8fafc; color: #475569; }

        .tbl-actions { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-top: 14px; padding-top: 12px; border-top: 1px solid #f1f5f9; }
        .tbl-act-btn { padding: 8px 10px; border-radius: 8px; font-size: 12.5px; font-weight: 700; text-align: center; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 5px; }
        .act-view { background: #eff6ff; color: #1d4ed8; }
        .act-edit { background: #f1f5f9; color: #334155; }
        .act-del  { background: #fef2f2; color: #dc2626; }
        .tbl-bulk-card { margin: 0; background: #f8fafc; }
        .tbl-forms-grid { display: grid; grid-template-columns: 1.35fr 1fr; gap: 24px; align-items: start; width: 100%; }
        .tbl-forms-grid-single { grid-template-columns: 1fr; }
        @media (max-width: 960px) { .tbl-forms-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="tbl-full-wrap">
        <?php
        if (file_exists($tables_dir . 'table-tabs.php')) {
            include $tables_dir . 'table-tabs.php';
        }

        if ($subtab === 'view' && $view_tbl && file_exists($tables_dir . 'table-view.php')) {
            include $tables_dir . 'table-view.php';
        } elseif ($subtab === 'add' && file_exists($tables_dir . 'table-add-edit.php')) {
            include $tables_dir . 'table-add-edit.php';
        } elseif (file_exists($tables_dir . 'table-list.php')) {
            include $tables_dir . 'table-list.php';
        }
        ?>
    </div>
    <?php
}