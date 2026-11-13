<?php
if (!defined('ABSPATH')) exit;

$svg_users = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
$svg_edit  = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_user  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
$svg_phone = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>';
$svg_check = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>';
?>

<div class="apos-card apos-full-card">
    <div class="apos-form-header">
        <h3><?php echo $edit_staff ? $svg_edit . '<span>Edit Staff: ' . esc_html($edit_staff->name) . '</span>' : $svg_users . '<span>Add New Staff Member</span>'; ?></h3>
        <p>Enter team member details and assign their restaurant role and duty status.</p>
    </div>

    <form method="post" action="?page=antalya-pos&tab=staff&subtab=all">
        <input type="hidden" name="edit_staff_id" value="<?php echo $edit_staff ? intval($edit_staff->id) : ''; ?>">

        <div class="stf-form-grid">
            <div class="apos-form-group">
                <label class="apos-label">Full Name <span>*</span></label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $svg_user; ?></span>
                    <input type="text" name="staff_name" class="apos-input has-prefix" placeholder="e.g. Rahim Uddin" value="<?php echo $edit_staff ? esc_attr($edit_staff->name) : ''; ?>" required>
                </div>
            </div>

            <div class="apos-form-group">
                <label class="apos-label">Role / Position <span>*</span></label>
                <select name="staff_role" class="apos-input" required>
                    <?php foreach ($roles as $r) : ?>
                        <option value="<?php echo esc_attr($r); ?>" <?php selected($edit_staff ? $edit_staff->role : 'Waiter', $r); ?>><?php echo esc_html($r); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="stf-form-grid">
            <div class="apos-form-group">
                <label class="apos-label">Phone Number <small>(Optional)</small></label>
                <div class="apos-input-wrap">
                    <span class="apos-input-prefix"><?php echo $svg_phone; ?></span>
                    <input type="text" name="staff_phone" class="apos-input has-prefix" placeholder="017XXXXXXXX" value="<?php echo $edit_staff ? esc_attr($edit_staff->phone) : ''; ?>">
                </div>
            </div>

            <div class="apos-form-group">
                <label class="apos-label">Duty Status</label>
                <select name="staff_status" class="apos-input">
                    <option value="Active" <?php selected($edit_staff ? $edit_staff->status : 'Active', 'Active'); ?>>Active (On Duty)</option>
                    <option value="Off Duty" <?php selected($edit_staff ? $edit_staff->status : '', 'Off Duty'); ?>>Off Duty</option>
                </select>
            </div>
        </div>

        <div class="stf-form-footer">
            <button type="submit" name="rtpos_save_staff" class="apos-btn stf-btn-icon">
                <?php echo $svg_check; ?>
                <span><?php echo $edit_staff ? 'Update Staff' : 'Save Staff Member'; ?></span>
            </button>
            <a href="?page=antalya-pos&tab=staff&subtab=all" class="apos-btn-secondary">Cancel</a>
        </div>
    </form>
</div>