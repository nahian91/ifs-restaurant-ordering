<?php
if (!defined('ABSPATH')) exit;

$svg_users  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
$svg_plus   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
$svg_edit   = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_eye    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
$svg_back   = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';
$svg_search = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';
?>

<div class="apos-header">
    <div>
        <h1>Staff & Shift Management</h1>
        <p>Manage restaurant managers, chefs, waiters, and track active duty shifts (<strong><?php echo $active_count; ?> Active</strong>).</p>
    </div>
</div>

<div class="stf-topbar-row">
    <div class="stf-subtabs">
        <a href="?page=antalya-pos&tab=staff&subtab=all" class="stf-subtab <?php echo $subtab === 'all' ? 'active' : ''; ?>">
            <?php echo $svg_users; ?>
            <span>All Staff</span>
            <span class="stf-count-pill"><?php echo count($staff_list); ?></span>
        </a>
        <a href="?page=antalya-pos&tab=staff&subtab=add" class="stf-subtab <?php echo $subtab === 'add' ? 'active' : ''; ?>">
            <?php echo $edit_staff ? $svg_edit . '<span>Edit Staff</span>' : $svg_plus . '<span>Add Staff</span>'; ?>
        </a>
        <?php if ($subtab === 'view' && $view_staff) : ?>
            <span class="stf-subtab active">
                <?php echo $svg_eye; ?>
                <span>Profile #<?php echo intval($view_staff->id); ?></span>
            </span>
        <?php endif; ?>
    </div>

    <?php if ($subtab === 'all' && !empty($staff_list)) : ?>
        <div class="stf-filter-group">
            <select id="stf-role-filter" class="apos-input stf-role-select">
                <option value="all">All Roles</option>
                <?php foreach ($roles as $r) : ?>
                    <option value="<?php echo esc_attr(strtolower($r)); ?>"><?php echo esc_html($r); ?></option>
                <?php endforeach; ?>
            </select>
            <div class="stf-search-box">
                <?php echo $svg_search; ?>
                <input type="text" id="stf-live-search" class="apos-input" placeholder="Search name or phone...">
            </div>
        </div>
    <?php elseif ($subtab === 'view') : ?>
        <a href="?page=antalya-pos&tab=staff&subtab=all" class="apos-btn-secondary stf-btn-icon">
            <?php echo $svg_back; ?>
            <span>Back to All Staff</span>
        </a>
    <?php endif; ?>
</div>

<style>
    .stf-topbar-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 24px; width: 100%; }
    .stf-subtabs { display: inline-flex; background: #e2e8f0; padding: 4px; border-radius: 12px; gap: 4px; }
    .stf-subtab { padding: 9px 18px; border-radius: 9px; font-size: 13.5px; font-weight: 700; text-decoration: none; color: #475569; display: inline-flex; align-items: center; gap: 7px; }
    .stf-subtab.active { background: #fff; color: #f97316; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08); }
    .stf-count-pill { background: #f1f5f9; color: #334155; padding: 1px 7px; border-radius: 10px; font-size: 11px; }
    .stf-filter-group { display: flex; gap: 10px; flex-wrap: wrap; }
    .stf-role-select { width: 185px; padding: 9px 36px 9px 14px; }
    .stf-search-box { position: relative; width: 250px; max-width: 100%; }
    .stf-search-box svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); pointer-events: none; }
    .stf-search-box input { padding: 9px 14px 9px 38px; }
    .stf-btn-icon { display: inline-flex; align-items: center; gap: 7px; }
</style>