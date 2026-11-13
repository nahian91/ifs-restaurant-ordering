<?php
if (!defined('ABSPATH')) exit;

function rtpos_view_orders($s) {
    global $wpdb;
    $orders_table = $wpdb->prefix . 'rtpos_orders';

    // Include Edit & Delete handlers
    include_once plugin_dir_path(__FILE__) . 'orders/order-edit.php';
    include_once plugin_dir_path(__FILE__) . 'orders/order-delete.php';

    // 1. Handle Status Update
    if (isset($_GET['set_status']) && isset($_GET['order_id'])) {
        rtpos_handle_order_status_update($orders_table);
    }

    // 2. Handle Full Order Edit Form Submission
    if (isset($_POST['rtpos_save_order_edit'])) {
        rtpos_handle_full_order_edit($orders_table, $s);
    }

    // 3. Handle Delete Order
    if (isset($_GET['delete_order'])) {
        rtpos_handle_order_delete($orders_table);
    }

    // Check Dedicated Order View Page
    $view_order = null;
    if (isset($_GET['view_order'])) {
        $view_order = $wpdb->get_row($wpdb->prepare("SELECT * FROM $orders_table WHERE id = %d", intval($_GET['view_order'])));
    }

    // Filters & Pagination Setup
    $filter       = isset($_GET['status_filter']) ? sanitize_key($_GET['status_filter']) : 'all';
    $search_query = isset($_GET['s_ord']) ? sanitize_text_field(wp_unslash($_GET['s_ord'])) : '';
    $paged        = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page     = 10;
    $offset       = ($paged - 1) * $per_page;

    // Build SQL Query Where Clause
    $where_sql = [];
    if ($filter !== 'all') {
        $where_sql[] = $wpdb->prepare("status = %s", $filter);
    }
    if (!empty($search_query)) {
        $like = '%' . $wpdb->esc_like($search_query) . '%';
        $where_sql[] = $wpdb->prepare("(id LIKE %s OR table_no LIKE %s)", $like, $like);
    }
    $where_clause = !empty($where_sql) ? 'WHERE ' . implode(' AND ', $where_sql) : '';

    // Status Counts for Filter Pills
    $counts = [
        'all'       => (int) $wpdb->get_var("SELECT COUNT(*) FROM $orders_table"),
        'pending'   => (int) $wpdb->get_var("SELECT COUNT(*) FROM $orders_table WHERE status = 'pending'"),
        'preparing' => (int) $wpdb->get_var("SELECT COUNT(*) FROM $orders_table WHERE status = 'preparing'"),
        'served'    => (int) $wpdb->get_var("SELECT COUNT(*) FROM $orders_table WHERE status = 'served'"),
        'completed' => (int) $wpdb->get_var("SELECT COUNT(*) FROM $orders_table WHERE status = 'completed'"),
    ];

    // Total filtered records for pagination
    $total_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM $orders_table $where_clause");
    $total_pages = ceil($total_items / $per_page);

    // Fetch paginated orders
    $orders = $wpdb->get_results("SELECT * FROM $orders_table $where_clause ORDER BY id DESC LIMIT $per_page OFFSET $offset") ?: [];
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
            gap: 14px; margin-bottom: 20px; width: 100%;
        }
        .ord-pills-no-margin { margin: 0; }
        .ord-search-box { position: relative; width: 270px; max-width: 100%; }
        .ord-search-box svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); pointer-events: none; }
        .ord-search-input {
            width: 100%; padding: 11px 16px 11px 40px; border: 1.5px solid #cbd5e1; border-radius: 12px;
            font-size: 14px; font-weight: 500; background: #f8fafc; color: #0f172a; box-sizing: border-box;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.02);
        }
        .ord-search-input:hover { border-color: #94a3b8; background: #fff; }
        .ord-search-input:focus {
            outline: none; background: #fff; border-color: #f97316;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.15), 0 2px 6px rgba(15, 23, 42, 0.04);
        }

        .autoprint-box {
            background: #0f172a; color: #f8fafc; padding: 9px 16px; border-radius: 11px;
            font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 10px;
        }
        .autoprint-toggle-btn {
            background: #334155; color: #fff; border: none; padding: 4px 10px;
            border-radius: 6px; font-size: 11.5px; cursor: pointer;
        }
        .ap-dot-paused { background-color: #ef4444 !important; }

        .pill-count {
            background: rgba(0,0,0,0.08); padding: 1px 7px; border-radius: 10px; font-size: 11px; margin-left: 4px;
        }
        .apos-pill.active .pill-count { background: rgba(255,255,255,0.2); color: #fff; }
        .st-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .dot-pending { background: #f59e0b; }
        .dot-preparing { background: #3b82f6; }
        .dot-served { background: #a855f7; }
        .dot-completed { background: #10b981; }

        /* Table Column Widths & Cell Styles */
        .col-ord-id { width: 95px; }
        .col-ord-tbl { width: 115px; }
        .col-ord-status { width: 160px; }
        .col-ord-actions { width: 360px; }

        .ord-empty-cell { text-align: center; padding: 45px; color: #64748b; }
        .ord-id-link { font-size: 15px; font-weight: 800; color: #0f172a; text-decoration: none; }
        .ord-id-link:hover { color: #f97316; }
        .ord-time-sm { color: #94a3b8; }
        .ord-table-badge {
            background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa;
            padding: 5px 11px; border-radius: 8px; font-weight: 800; font-size: 13px; display: inline-block;
        }

        /* Status Select Dropdown */
        .ord-select-wrapper { position: relative; display: block; width: 100%; }
        .ord-status-select {
            appearance: none; -webkit-appearance: none; -moz-appearance: none;
            width: 100%; display: block; padding: 10px 36px 10px 14px; border-radius: 11px;
            font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 0.3px;
            border: 1.5px solid transparent; cursor: pointer; box-sizing: border-box;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 12px center; background-size: 14px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        }
        .ord-status-select:hover { filter: brightness(0.97); transform: translateY(-1px); box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08); }
        .ord-status-select:focus { outline: none; box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.15); }

        .ord-st-pending { background-color: #fef3c7; color: #b45309; border-color: #fde68a; }
        .ord-st-preparing { background-color: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .ord-st-served { background-color: #faf5ff; color: #7e22ce; border-color: #e9d5ff; }
        .ord-st-completed { background-color: #ecfdf5; color: #047857; border-color: #a7f3d0; }

        /* Action Buttons */
        .ord-action-group { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .ord-act-btn {
            padding: 6px 10px; border-radius: 7px; font-size: 12px; font-weight: 700; text-decoration: none;
            display: inline-flex; align-items: center; gap: 4px; transition: 0.2s; border: none; cursor: pointer;
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
        .ord-print-kot-btn { display: inline-flex; align-items: center; gap: 4px; }
        .ord-print-bill-btn { padding: 6px 10px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; }

        /* Pagination Bar */
        .ord-pagination {
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;
            gap: 12px; margin-top: 20px; padding: 12px 16px; background: #fff; border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        .ord-pag-info { font-size: 13px; color: #64748b; font-weight: 600; }
        .ord-pag-links { display: flex; gap: 6px; align-items: center; }
        .ord-pag-btn {
            padding: 6px 12px; border-radius: 8px; font-size: 12.5px; font-weight: 700;
            text-decoration: none; background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;
            transition: 0.2s;
        }
        .ord-pag-btn:hover { background: #e2e8f0; color: #0f172a; }
        .ord-pag-btn.current { background: #f97316; color: #fff; border-color: #f97316; }
        .ord-pag-disabled { opacity: 0.4; pointer-events: none; }
    </style>

    <div class="ord-full-wrap">
        <?php
        // Load Navigation Header / Tabs
        include plugin_dir_path(__FILE__) . 'orders/order-tabs.php';

        if ($view_order) {
            if (isset($_GET['edit_order'])) {
                // Load Full Edit Form
                rtpos_render_order_edit_form($view_order, $s);
            } else {
                // Load Dedicated Order Details / Invoice View
                include plugin_dir_path(__FILE__) . 'orders/order-view.php';
            }
        } else {
            // Load Orders Table List
            include plugin_dir_path(__FILE__) . 'orders/order-list.php';
        }
        ?>
    </div>
    <?php
}