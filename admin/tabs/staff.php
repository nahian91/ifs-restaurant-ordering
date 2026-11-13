<?php
if (!defined('ABSPATH')) exit;

function rtpos_view_staff($s = []) {
    global $wpdb;
    
    // Safe fallback for settings if called without parameters
    if (empty($s) && function_exists('rtpos_get_settings')) {
        $s = rtpos_get_settings();
    }
    
    $t_staff = $wpdb->prefix . 'rtpos_staff';

    // Include Delete Handler
    include_once plugin_dir_path(__FILE__) . 'staff/staff-delete.php';

    // 1. Handle Save / Update Staff
    if (isset($_POST['rtpos_save_staff']) && !empty($_POST['staff_name'])) {
        $data = [
            'name'   => sanitize_text_field($_POST['staff_name']),
            'role'   => sanitize_text_field($_POST['staff_role']),
            'phone'  => sanitize_text_field($_POST['staff_phone']),
            'status' => in_array($_POST['staff_status'], ['Active', 'Off Duty']) ? $_POST['staff_status'] : 'Active'
        ];

        if (!empty($_POST['edit_staff_id'])) {
            $wpdb->update($t_staff, $data, ['id' => intval($_POST['edit_staff_id'])]);
            echo '<div class="apos-alert stf-alert-flex"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg><span>Staff profile updated successfully!</span></div>';
        } else {
            $wpdb->insert($t_staff, $data);
            echo '<div class="apos-alert stf-alert-flex"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg><span>New staff member added!</span></div>';
        }
    }

    // 2. Handle Quick Shift Toggle
    if (isset($_GET['toggle_staff'])) {
        $id     = intval($_GET['toggle_staff']);
        $cur_st = $wpdb->get_var($wpdb->prepare("SELECT status FROM $t_staff WHERE id = %d", $id));
        $new_st = ($cur_st === 'Active') ? 'Off Duty' : 'Active';
        $wpdb->update($t_staff, ['status' => $new_st], ['id' => $id]);
        echo '<div class="apos-alert stf-alert-flex"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg><span>Shift status updated to <strong>' . esc_html($new_st) . '</strong>!</span></div>';
    }

    // Routing: 'all', 'add', or 'view'
    $subtab     = isset($_GET['subtab']) ? sanitize_key($_GET['subtab']) : 'all';
    $edit_staff = null;
    $view_staff = null;

    if (isset($_GET['edit_staff'])) {
        $edit_staff = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t_staff WHERE id = %d", intval($_GET['edit_staff'])));
        $subtab     = 'add';
    } elseif (isset($_GET['view_staff'])) {
        $view_staff = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t_staff WHERE id = %d", intval($_GET['view_staff'])));
        $subtab     = 'view';
    }

    $staff_list   = $wpdb->get_results("SELECT * FROM $t_staff ORDER BY id DESC") ?: [];
    $active_count = 0;
    foreach ($staff_list as $member) {
        if ($member->status === 'Active') $active_count++;
    }

    $roles = ['Manager', 'Head Chef', 'Kitchen Staff', 'Waiter', 'Cashier'];
    ?>

    <style>
        .stf-alert-flex { display: flex; align-items: center; gap: 8px; }
        .stf-full-wrap { width: 100%; box-sizing: border-box; }
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
        .apos-input-prefix { position: absolute; left: 14px; font-weight: 800; color: #64748b; font-size: 15px; pointer-events: none; }
        .apos-input {
            width: 100%; padding: 11px 15px; border: 1.5px solid #e2e8f0; border-radius: 11px;
            font-size: 14px; font-weight: 500; color: #0f172a; background-color: #f8fafc; box-sizing: border-box;
        }
        .apos-input.has-prefix { padding-left: 40px; }
        .apos-input:focus { outline: none; background-color: #fff; border-color: #f97316; box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.14); }
        select.apos-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; }

        .stf-form-grid { display: grid; grid-template-columns: 1.3fr 1fr; gap: 20px; width: 100%; }
        @media (max-width: 768px) { .stf-form-grid { grid-template-columns: 1fr; } }
        .stf-form-footer { margin-top: 22px; padding-top: 16px; border-top: 1px solid #f1f5f9; display: flex; gap: 10px; }

        /* Empty State */
        .stf-empty-card { text-align: center; padding: 60px 20px; }
        .stf-empty-ico { width: 56px; height: 56px; border-radius: 16px; background: #fff7ed; color: #f97316; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px; }
        .stf-empty-title { margin: 0 0 8px; font-size: 19px; }
        .stf-empty-desc { color: #64748b; margin: 0 0 20px; font-size: 14px; }

        /* Table Columns */
        .col-stf-role { width: 190px; }
        .col-stf-phone { width: 180px; }
        .col-stf-status { width: 160px; }
        .col-stf-act { width: 295px; }

        .stf-member-link { display: inline-flex; align-items: center; gap: 12px; text-decoration: none; }
        .stf-member-link:hover .stf-member-name { color: #f97316; }
        .stf-avatar { width: 38px; height: 38px; border-radius: 10px; background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; font-weight: 800; font-size: 15px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .stf-member-name { font-size: 14.5px; color: #0f172a; }
        .stf-role-badge { background: #f1f5f9; color: #334155; padding: 4px 11px; border-radius: 8px; font-size: 12.5px; font-weight: 700; display: inline-block; }
        .stf-phone-cell { color: #475569; font-weight: 500; }

        .stf-status-pill { padding: 4px 11px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; }
        .stf-pill-active { background: #d1fae5; color: #059669; }
        .stf-pill-off { background: #f1f5f9; color: #64748b; }
        .stf-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .stf-dot-active { background: #10b981; }
        .stf-dot-off { background: #94a3b8; }

        .stf-act-group { display: flex; gap: 7px; align-items: center; flex-wrap: wrap; }
        .stf-act-btn { padding: 6px 11px; border-radius: 7px; font-size: 12px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .act-view  { background: #eff6ff; color: #1d4ed8; }
        .act-shift { background: #ecfdf5; color: #059669; }
        .act-edit  { background: #f1f5f9; color: #334155; }
        .act-del   { background: #fef2f2; color: #dc2626; }
        .stf-btn-icon { display: inline-flex; align-items: center; gap: 7px; }
    </style>

    <div class="stf-full-wrap">
        <?php
        // Include Sub-Tabs Header
        include plugin_dir_path(__FILE__) . 'staff/staff-tabs.php';

        if ($subtab === 'view' && $view_staff) {
            include plugin_dir_path(__FILE__) . 'staff/staff-view.php';
        } elseif ($subtab === 'add') {
            include plugin_dir_path(__FILE__) . 'staff/staff-add-edit.php';
        } else {
            include plugin_dir_path(__FILE__) . 'staff/staff-list.php';
        }
        ?>
    </div>
    <?php
}