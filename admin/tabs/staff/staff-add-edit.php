<?php
/**
 * Antalya POS Add / Edit Staff Member Form View
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

$base_staff_url = admin_url('admin.php?page=antalya-pos&tab=staff');
$all_staff_url  = esc_url(add_query_arg('subtab', 'all', $base_staff_url));

$is_editing    = !empty($edit_staff);
$staff_id      = $is_editing ? intval($edit_staff->id) : 0;
$staff_name    = $is_editing ? $edit_staff->name : '';
$staff_role    = $is_editing ? $edit_staff->role : 'Waiter';
$staff_email   = ($is_editing && isset($edit_staff->email)) ? $edit_staff->email : '';
$staff_phone   = $is_editing ? $edit_staff->phone : '';
$staff_status  = $is_editing ? $edit_staff->status : 'Active';

// Decode existing permissions if editing
$saved_perms   = ($is_editing && !empty($edit_staff->permissions)) ? json_decode($edit_staff->permissions, true) : [];
if (!is_array($saved_perms)) {
    $saved_perms = [];
}

// Define available system tabs and actions
$pos_menus = [
    'dashboard'  => __('Dashboard', 'antalya-pos'),
    'orders'     => __('Orders & Kitchen KOT', 'antalya-pos'),
    'tables'     => __('Table Management', 'antalya-pos'),
    'items'      => __('Menu Items & Dishes', 'antalya-pos'),
    'categories' => __('Categories', 'antalya-pos'),
    'extras'     => __('Extras / Add-ons', 'antalya-pos'),
    'reports'    => __('Sales Reports', 'antalya-pos'),
    'staff'      => __('Staff Management', 'antalya-pos'),
    'settings'   => __('System Settings', 'antalya-pos'),
];
$actions = [
    'view'   => __('View', 'antalya-pos'),
    'add'    => __('Add', 'antalya-pos'),
    'edit'   => __('Edit', 'antalya-pos'),
    'delete' => __('Delete', 'antalya-pos'),
];

$roles = isset($roles) && is_array($roles) ? $roles : ['Manager', 'Head Chef', 'Kitchen Staff', 'Waiter', 'Cashier'];

// SVG Icons
$svg_users  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
$svg_edit   = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_user   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
$svg_email  = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>';
$svg_phone  = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>';
$svg_lock   = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';
$svg_check  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
?>

<div class="apos-card apos-full-card">
    <div class="apos-form-header">
        <h3>
            <?php if ($is_editing) : ?>
                <?php echo $svg_edit; ?>
                <span>
                    <?php
                    printf(
                        /* translators: %s: staff member name */
                        esc_html__('Edit Staff: %s', 'antalya-pos'),
                        esc_html($staff_name)
                    );
                    ?>
                </span>
            <?php else : ?>
                <?php echo $svg_users; ?>
                <span><?php esc_html_e('Add New Staff Member', 'antalya-pos'); ?></span>
            <?php endif; ?>
        </h3>
        <p><?php esc_html_e('Enter team member details, login credentials, role, duty status, and granular menu permissions.', 'antalya-pos'); ?></p>
    </div>

    <form method="post" action="<?php echo $all_staff_url; ?>" id="stf-staff-form">
        <?php wp_nonce_field('rtpos_save_staff_action'); ?>
        <input type="hidden" name="edit_staff_id" value="<?php echo $staff_id > 0 ? $staff_id : ''; ?>">

        <!-- Row 1: Full Name & Role / Position -->
        <div class="stf-form-grid">
            <div class="apos-form-group">
                <label class="apos-label">
                    <span><?php esc_html_e('Full Name', 'antalya-pos'); ?> <span class="required-star">*</span></span>
                </label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $svg_user; ?></span>
                    <input type="text" name="staff_name" class="apos-input has-prefix" placeholder="<?php esc_attr_e('e.g. Rahim Uddin', 'antalya-pos'); ?>" value="<?php echo esc_attr($staff_name); ?>" required>
                </div>
            </div>

            <div class="apos-form-group">
                <label class="apos-label">
                    <span><?php esc_html_e('Role / Position', 'antalya-pos'); ?> <span class="required-star">*</span></span>
                </label>
                <select name="staff_role" class="apos-input" required aria-label="<?php esc_attr_e('Role / Position', 'antalya-pos'); ?>">
                    <?php foreach ($roles as $r) : ?>
                        <option value="<?php echo esc_attr($r); ?>" <?php selected($staff_role, $r); ?>><?php echo esc_html($r); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Row 2: Email & Phone -->
        <div class="stf-form-grid">
            <div class="apos-form-group">
                <label class="apos-label">
                    <span><?php esc_html_e('Email Address', 'antalya-pos'); ?> <span class="required-star">*</span></span>
                </label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $svg_email; ?></span>
                    <input type="email" name="staff_email" class="apos-input has-prefix" placeholder="staff@restaurant.com" value="<?php echo esc_attr($staff_email); ?>" required>
                </div>
            </div>

            <div class="apos-form-group">
                <label class="apos-label">
                    <span><?php esc_html_e('Phone Number', 'antalya-pos'); ?></span>
                    <small><?php esc_html_e('(Optional)', 'antalya-pos'); ?></small>
                </label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $svg_phone; ?></span>
                    <input type="text" name="staff_phone" class="apos-input has-prefix" placeholder="017XXXXXXXX" value="<?php echo esc_attr($staff_phone); ?>">
                </div>
            </div>
        </div>

        <!-- Row 3: Password & Confirm Password -->
        <div class="stf-form-grid">
            <div class="apos-form-group">
                <label class="apos-label">
                    <span><?php esc_html_e('Password', 'antalya-pos'); ?></span>
                    <?php if ($is_editing) : ?>
                        <small><?php esc_html_e('(Leave blank to keep current)', 'antalya-pos'); ?></small>
                    <?php else : ?>
                        <span class="required-star">*</span>
                    <?php endif; ?>
                </label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $svg_lock; ?></span>
                    <input type="password" name="staff_password" id="staff_password" class="apos-input has-prefix" placeholder="••••••••" <?php echo $is_editing ? '' : 'required'; ?>>
                </div>
                <!-- Password Strength Meter Bar -->
                <div class="stf-strength-wrap">
                    <div class="stf-strength-bars">
                        <span class="bar bar-1"></span>
                        <span class="bar bar-2"></span>
                        <span class="bar bar-3"></span>
                        <span class="bar bar-4"></span>
                    </div>
                    <span id="stf-strength-text"><?php esc_html_e('Password Strength', 'antalya-pos'); ?></span>
                </div>
            </div>

            <div class="apos-form-group">
                <label class="apos-label">
                    <span><?php esc_html_e('Confirm Password', 'antalya-pos'); ?></span>
                    <?php if (!$is_editing) : ?>
                        <span class="required-star">*</span>
                    <?php endif; ?>
                </label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $svg_lock; ?></span>
                    <input type="password" name="staff_confirm_password" id="staff_confirm_password" class="apos-input has-prefix" placeholder="••••••••" <?php echo $is_editing ? '' : 'required'; ?>>
                </div>
                <small id="stf-match-error" style="color: #dc2626; font-weight: 700; display: none; margin-top: 4px;"><?php esc_html_e('Passwords do not match!', 'antalya-pos'); ?></small>
            </div>
        </div>

        <!-- Row 4: Duty Status -->
        <div class="apos-form-group" style="margin-bottom: 24px;">
            <label class="apos-label"><span><?php esc_html_e('Duty Status', 'antalya-pos'); ?></span></label>
            <select name="staff_status" class="apos-input" aria-label="<?php esc_attr_e('Duty Status', 'antalya-pos'); ?>">
                <option value="Active" <?php selected($staff_status, 'Active'); ?>><?php esc_html_e('Active (On Duty)', 'antalya-pos'); ?></option>
                <option value="Off Duty" <?php selected($staff_status, 'Off Duty'); ?>><?php esc_html_e('Off Duty', 'antalya-pos'); ?></option>
            </select>
        </div>

        <!-- Granular Menu Permissions Matrix with iOS Switch -->
        <div class="apos-form-group" style="margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <label class="apos-label" style="font-size: 14.5px; font-weight: 800; color: #0f172a; margin: 0;"><?php esc_html_e('Menu Access & Action Permissions', 'antalya-pos'); ?></label>
                <button type="button" id="stf-select-all-perms" style="background: none; border: none; color: #f97316; font-size: 12px; font-weight: 700; cursor: pointer;"><?php esc_html_e('Select All / Deselect All', 'antalya-pos'); ?></button>
            </div>

            <div class="perm-table-wrapper" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff;">
                <table class="apos-table" style="margin: 0; width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 800; color: #475569;"><?php esc_html_e('POS Module / Menu', 'antalya-pos'); ?></th>
                            <?php foreach ($actions as $act_key => $act_label) : ?>
                                <th style="padding: 12px 16px; font-size: 12px; font-weight: 800; color: #475569; text-align: center; width: 110px;"><?php echo esc_html($act_label); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pos_menus as $menu_slug => $menu_name) : ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 16px; font-weight: 700; color: #1e293b; font-size: 13.5px;"><?php echo esc_html($menu_name); ?></td>
                                <?php foreach ($actions as $act_key => $act_label) :
                                    $is_checked = isset($saved_perms[$menu_slug][$act_key]) && $saved_perms[$menu_slug][$act_key] == '1';
                                ?>
                                    <td style="padding: 12px 16px; text-align: center;">
                                        <label class="apos-switch" title="<?php echo esc_attr(sprintf(__('Toggle %s', 'antalya-pos'), $act_label)); ?>">
                                            <input type="checkbox" name="permissions[<?php echo esc_attr($menu_slug); ?>][<?php echo esc_attr($act_key); ?>]" value="1" <?php checked($is_checked, true); ?>>
                                            <span class="apos-slider"></span>
                                        </label>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="stf-form-footer">
            <button type="submit" name="rtpos_save_staff" id="stf-submit-btn" class="apos-btn stf-btn-icon">
                <?php echo $svg_check; ?>
                <span><?php echo $is_editing ? esc_html__('Update Staff Member', 'antalya-pos') : esc_html__('Save Staff Member', 'antalya-pos'); ?></span>
            </button>
            <a href="<?php echo $all_staff_url; ?>" class="apos-btn-secondary"><?php esc_html_e('Cancel', 'antalya-pos'); ?></a>
        </div>
    </form>
</div>

<!-- iOS Style Toggle Switch & Custom CSS Styles -->
<style>
    .required-star {
        color: #ef4444;
        font-weight: 800;
        margin-left: 3px;
    }
    .apos-switch {
        position: relative;
        display: inline-block;
        width: 38px;
        height: 22px;
        vertical-align: middle;
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
    .apos-switch input:checked + .apos-slider { background-color: #f97316; }
    .apos-switch input:focus + .apos-slider { box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15); }
    .apos-switch input:checked + .apos-slider:before { transform: translateX(16px); }

    /* Password Strength Meter CSS */
    .stf-strength-wrap { margin-top: 6px; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .stf-strength-bars { display: flex; gap: 4px; flex: 1; }
    .stf-strength-bars .bar { height: 4px; flex: 1; background: #e2e8f0; border-radius: 2px; transition: 0.25s ease; }
    #stf-strength-text { font-size: 11.5px; font-weight: 700; color: #64748b; white-space: nowrap; }
</style>

<!-- Strength Meter & Validation Script -->
<script>
jQuery(document).ready(function($) {
    let lblStrength = <?php echo wp_json_encode(__('Password Strength', 'antalya-pos')); ?>;
    let lblVeryWeak = <?php echo wp_json_encode(__('Very Weak', 'antalya-pos')); ?>;
    let lblWeak     = <?php echo wp_json_encode(__('Weak', 'antalya-pos')); ?>;
    let lblGood     = <?php echo wp_json_encode(__('Good', 'antalya-pos')); ?>;
    let lblStrong   = <?php echo wp_json_encode(__('Strong', 'antalya-pos')); ?>;

    // Select All / Deselect All
    let allChecked = false;
    $('#stf-select-all-perms').on('click', function() {
        allChecked = !allChecked;
        $('.perm-table-wrapper input[type="checkbox"]').prop('checked', allChecked);
    });

    // Password Strength Checker
    $('#staff_password').on('input', function() {
        let val = $(this).val();
        let bars = $('.stf-strength-bars .bar');
        let txt = $('#stf-strength-text');
        
        bars.css('background', '#e2e8f0');
        if (val.length === 0) {
            txt.text(lblStrength);
            return;
        }

        let score = 0;
        if (val.length >= 6) score++;
        if (val.length >= 10) score++;
        if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        let colors = ['#ef4444', '#f59e0b', '#3b82f6', '#10b981'];
        let labels = [lblVeryWeak, lblWeak, lblGood, lblStrong];

        for (let i = 0; i < score; i++) {
            bars.eq(i).css('background', colors[score - 1]);
        }
        txt.text(labels[score - 1] || lblWeak).css('color', colors[score - 1] || '#ef4444');
    });

    // Confirm Password Matching Validation
    $('#stf-staff-form').on('submit', function(e) {
        let pass = $('#staff_password').val();
        let confirmPass = $('#staff_confirm_password').val();

        if (pass.length > 0 && pass !== confirmPass) {
            e.preventDefault();
            $('#stf-match-error').show();
            $('#staff_confirm_password').focus();
        } else {
            $('#stf-match-error').hide();
        }
    });
});
</script>