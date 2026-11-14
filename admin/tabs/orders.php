<?php
/**
 * Antalya POS Orders & Kitchen Monitor View
 *
 * @package Antalya_POS
 * @since 6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

function rtpos_view_orders($s) {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'antalya-pos'));
    }

    global $wpdb;
    $orders_table = $wpdb->prefix . 'rtpos_orders';

    // Include Edit & Delete handlers safely
    $orders_dir = plugin_dir_path(__FILE__) . 'orders/';
    if (file_exists($orders_dir . 'order-edit.php')) {
        include_once $orders_dir . 'order-edit.php';
    }
    if (file_exists($orders_dir . 'order-delete.php')) {
        include_once $orders_dir . 'order-delete.php';
    }

    // 1. Handle Status Update
    if (isset($_GET['set_status']) && isset($_GET['order_id']) && function_exists('rtpos_handle_order_status_update')) {
        rtpos_handle_order_status_update($orders_table);
    }

    // 2. Handle Full Order Edit Form Submission
    if (isset($_POST['rtpos_save_order_edit']) && function_exists('rtpos_handle_full_order_edit')) {
        rtpos_handle_full_order_edit($orders_table, $s);
    }

    // 3. Handle Delete Order
    if (isset($_GET['delete_order']) && function_exists('rtpos_handle_order_delete')) {
        rtpos_handle_order_delete($orders_table);
    }

    // Check Dedicated Order View Page
    $view_order = null;
    if (isset($_GET['view_order'])) {
        $view_order = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$orders_table} WHERE id = %d", intval($_GET['view_order']))
        );
    }

    // Filters & Pagination Setup
    $allowed_filters = ['all', 'pending', 'preparing', 'served', 'completed'];
    $raw_filter      = isset($_GET['status_filter']) ? sanitize_key($_GET['status_filter']) : 'all';
    $filter          = in_array($raw_filter, $allowed_filters, true) ? $raw_filter : 'all';
    $search_query    = isset($_GET['s_ord']) ? sanitize_text_field(wp_unslash($_GET['s_ord'])) : '';
    $paged           = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page        = 10;
    $offset          = ($paged - 1) * $per_page;

    // Build Secure SQL Query Where Clause
    $where_sql = [];
    if ($filter !== 'all') {
        $where_sql[] = $wpdb->prepare("status = %s", $filter);
    }
    if (!empty($search_query)) {
        $like = '%' . $wpdb->esc_like($search_query) . '%';
        $where_sql[] = $wpdb->prepare("(CAST(id AS CHAR) LIKE %s OR table_no LIKE %s)", $like, $like);
    }
    $where_clause = !empty($where_sql) ? 'WHERE ' . implode(' AND ', $where_sql) : '';

    // Status Counts for Filter Pills (Secured with $wpdb->prepare)
    $counts = [
        'all'       => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$orders_table}"),
        'pending'   => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$orders_table} WHERE status = %s", 'pending')),
        'preparing' => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$orders_table} WHERE status = %s", 'preparing')),
        'served'    => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$orders_table} WHERE status = %s", 'served')),
        'completed' => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$orders_table} WHERE status = %s", 'completed')),
    ];

    // Total filtered records for pagination
    $total_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$orders_table} {$where_clause}");
    $total_pages = max(1, (int) ceil($total_items / $per_page));

    // Fetch paginated orders securely
    $orders = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$orders_table} {$where_clause} ORDER BY id DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        )
    ) ?: [];
    ?>
    <style>
        .ord-alert-flex { display: flex; align-items: center; gap: 8px; }
        .ord-full-wrap { width: 100%; box-sizing: border-box; }
        .apos-full-card { width: 100%; max-width: 100%; box-sizing: border-box; }
        .apos-full-table { width: 100%; max-width: 100%; }

        .ord-header-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .ord-back-btn { display: inline-flex; align-items: center; gap: 6px; }

        .ord-toolbar {
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;
            gap: 14px; margin-bottom: 22px; width: 100%;
        }
        .ord-pills-no-margin { margin: 0; }
        .ord-search-box { position: relative; width: 280px; max-width: 100%; }
        .ord-search-box svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #64748b; }
        .ord-search-input {
            width: 100%; padding: 11px 16px 11px 42px; border: 1.5px solid #cbd5e1; border-radius: 12px;
            font-size: 14px; font-weight: 500; background: #fff; color: #0f172a; box-sizing: border-box;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
        }
        .ord-search-input:hover { border-color: #94a3b8; }
        .ord-search-input:focus {
            outline: none; border-color: #f97316;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.14);
        }

        .autoprint-box {
            background: #0f172a; color: #f8fafc; padding: 10px 16px; border-radius: 12px;
            font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 10px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }
        .autoprint-toggle-btn {
            background: #334155; color: #fff; border: none; padding: 5px 11px;
            border-radius: 7px; font-size: 11.5px; font-weight: 700; cursor: pointer; transition: 0.15s;
        }
        .autoprint-toggle-btn:hover { background: #475569; }
        .ap-dot-paused { background-color: #ef4444 !important; }

        .pill-count {
            background: rgba(0, 0, 0, 0.08); padding: 2px 8px; border-radius: 12px; font-size: 11px; margin-left: 5px; font-weight: 750;
        }
        .apos-pill.active .pill-count { background: rgba(255, 255, 255, 0.22); color: #fff; }
        .st-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 6px; }
        .dot-pending { background: #f59e0b; }
        .dot-preparing { background: #3b82f6; }
        .dot-served { background: #a855f7; }
        .dot-completed { background: #10b981; }

        /* Table Column Widths & Cell Styles */
        .col-ord-id { width: 95px; }
        .col-ord-tbl { width: 120px; }
        .col-ord-status { width: 165px; }
        .col-ord-actions { width: 370px; }

        .ord-empty-cell { text-align: center; padding: 48px; color: #64748b; font-weight: 500; }
        .ord-id-link { font-size: 15px; font-weight: 850; color: #0f172a; text-decoration: none; transition: color 0.15s; }
        .ord-id-link:hover { color: #f97316; }
        .ord-time-sm { color: #64748b; font-size: 12px; }
        .ord-table-badge {
            background: #fff7ed; color: #ea580c; border: 1.5px solid #fed7aa;
            padding: 5px 12px; border-radius: 9px; font-weight: 800; font-size: 13px; display: inline-block;
        }

        /* Status Select Dropdown */
        .ord-select-wrapper { position: relative; display: block; width: 100%; }
        .ord-status-select {
            appearance: none; -webkit-appearance: none; -moz-appearance: none;
            width: 100%; display: block; padding: 9px 36px 9px 14px; border-radius: 11px;
            font-weight: 800; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.4px;
            border: 1.5px solid transparent; cursor: pointer; box-sizing: border-box;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 12px center; background-size: 14px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.04);
        }
        .ord-status-select:hover { filter: brightness(0.97); transform: translateY(-1px); }
        .ord-status-select:focus { outline: none; box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.14); }

        .ord-st-pending { background-color: #fef3c7; color: #b45309; border-color: #fde68a; }
        .ord-st-preparing { background-color: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .ord-st-served { background-color: #faf5ff; color: #7e22ce; border-color: #e9d5ff; }
        .ord-st-completed { background-color: #ecfdf5; color: #047857; border-color: #a7f3d0; }

        /* Action Buttons */
        .ord-action-group { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .ord-act-btn {
            padding: 7px 11px; border-radius: 8px; font-size: 12px; font-weight: 750; text-decoration: none;
            display: inline-flex; align-items: center; gap: 5px; transition: all 0.18s ease; border: none; cursor: pointer;
        }
        .act-view  { background: #eff6ff; color: #1d4ed8; }
        .act-view:hover  { background: #dbeafe; }
        .act-edit  { background: #f1f5f9; color: #334155; }
        .act-edit:hover  { background: #e2e8f0; color: #0f172a; }
        .step-cook { background: #dbeafe; color: #1d4ed8; }
        .step-cook:hover { background: #bfdbfe; color: #1e40af; }
        .step-serve { background: #f3e8ff; color: #7e22ce; }
        .step-serve:hover { background: #e9d5ff; color: #6b21a8; }
        .step-pay { background: #d1fae5; color: #059669; }
        .step-pay:hover { background: #a7f3d0; color: #047857; }
        .act-del { background: #fef2f2; color: #dc2626; }
        .act-del:hover { background: #fee2e2; }
        .ord-print-kot-btn { display: inline-flex; align-items: center; gap: 5px; }
        .ord-print-bill-btn { padding: 7px 11px; font-size: 12px; display: inline-flex; align-items: center; gap: 5px; }

        /* Pagination Bar */
        .ord-pagination {
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;
            gap: 12px; margin-top: 22px; padding: 14px 18px; background: #fff; border-radius: 14px;
            border: 1px solid #e2e8f0; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.02);
        }
        .ord-pag-info { font-size: 13px; color: #64748b; font-weight: 650; }
        .ord-pag-links { display: flex; gap: 6px; align-items: center; }
        .ord-pag-btn {
            padding: 7px 13px; border-radius: 9px; font-size: 12.5px; font-weight: 750;
            text-decoration: none; background: #f8fafc; color: #334155; border: 1px solid #cbd5e1;
            transition: all 0.18s ease;
        }
        .ord-pag-btn:hover { background: #e2e8f0; color: #0f172a; }
        .ord-pag-btn.current { background: #f97316; color: #fff; border-color: #f97316; box-shadow: 0 2px 8px rgba(249, 115, 22, 0.3); }
        .ord-pag-disabled { opacity: 0.4; pointer-events: none; }
    </style>

    <div class="ord-full-wrap">
        <?php
        // Load Navigation Header / Tabs
        if (file_exists($orders_dir . 'order-tabs.php')) {
            include $orders_dir . 'order-tabs.php';
        }

        if ($view_order) {
            if (isset($_GET['edit_order']) && function_exists('rtpos_render_order_edit_form')) {
                rtpos_render_order_edit_form($view_order, $s);
            } elseif (file_exists($orders_dir . 'order-view.php')) {
                include $orders_dir . 'order-view.php';
            }
        } elseif (file_exists($orders_dir . 'order-list.php')) {
            include $orders_dir . 'order-list.php';
        }
        ?>
    </div>
    <?php
}