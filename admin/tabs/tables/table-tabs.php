<?php
if (!defined('ABSPATH')) exit;

// Reusable SVG Icons for Tabs Header
$svg_grid = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>';
$svg_plus = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
$svg_edit = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_eye  = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
$svg_back = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';
$svg_search = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';
?>

<!-- Page Header -->
<div class="apos-header">
    <div>
        <h1>Restaurant Tables</h1>
        <p>Total Capacity: <strong><?php echo $total_seats; ?> Seats</strong> across <strong><?php echo count($tables); ?> Tables</strong> (<?php echo $occupied_count; ?> Occupied, <?php echo $free_count; ?> Available)</p>
    </div>
</div>

<!-- Sub-Tabs & Filter Toolbar -->
<div class="tbl-topbar-row">
    <div class="tbl-topbar-left">
        <div class="tbl-subtabs">
            <a href="?page=antalya-pos&tab=tables&subtab=all" class="tbl-subtab <?php echo $subtab === 'all' ? 'active' : ''; ?>">
                <?php echo $svg_grid; ?>
                <span>All Tables</span>
                <span class="tbl-count-pill"><?php echo count($tables); ?></span>
            </a>
            <a href="?page=antalya-pos&tab=tables&subtab=add" class="tbl-subtab <?php echo $subtab === 'add' ? 'active' : ''; ?>">
                <?php echo isset($edit_tbl) && $edit_tbl ? $svg_edit . '<span>Edit Table</span>' : $svg_plus . '<span>Add Table</span>'; ?>
            </a>
            <?php if ($subtab === 'view' && isset($view_tbl) && $view_tbl) : ?>
                <span class="tbl-subtab active">
                    <?php echo $svg_eye; ?>
                    <span>Table <?php echo esc_html($view_tbl->table_name); ?></span>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($subtab === 'all' && !empty($tables)) : ?>
            <div class="tbl-status-filters">
                <button type="button" class="tbl-st-pill active" data-st="all">All (<?php echo count($tables); ?>)</button>
                <button type="button" class="tbl-st-pill" data-st="free"><span class="dot-free"></span> Available (<?php echo $free_count; ?>)</button>
                <button type="button" class="tbl-st-pill" data-st="busy"><span class="dot-busy"></span> Occupied (<?php echo $occupied_count; ?>)</button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($subtab === 'all' && !empty($tables)) : ?>
        <div class="tbl-search-box">
            <?php echo $svg_search; ?>
            <input type="text" id="tbl-live-search" class="apos-input" placeholder="Search table or login...">
        </div>
    <?php elseif ($subtab === 'view') : ?>
        <a href="?page=antalya-pos&tab=tables&subtab=all" class="apos-btn-secondary tbl-btn-icon">
            <?php echo $svg_back; ?>
            <span>Back to All Tables</span>
        </a>
    <?php endif; ?>
</div>

<style>
    .tbl-topbar-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 24px; width: 100%; }
    .tbl-topbar-left { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .tbl-subtabs { display: inline-flex; background: #e2e8f0; padding: 4px; border-radius: 12px; gap: 4px; }
    .tbl-subtab { padding: 9px 18px; border-radius: 9px; font-size: 13.5px; font-weight: 700; text-decoration: none; color: #475569; transition: 0.2s; display: inline-flex; align-items: center; gap: 7px; }
    .tbl-subtab:hover { color: #0f172a; }
    .tbl-subtab.active { background: #fff; color: #f97316; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08); }
    .tbl-count-pill { background: #f1f5f9; color: #334155; padding: 1px 7px; border-radius: 10px; font-size: 11px; }

    .tbl-status-filters { display: flex; gap: 8px; flex-wrap: wrap; }
    .tbl-st-pill { padding: 7px 14px; border-radius: 20px; font-size: 12.5px; font-weight: 700; border: 1.5px solid #e2e8f0; background: #fff; color: #475569; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; }
    .tbl-st-pill.active, .tbl-st-pill:hover { background: #0f172a; color: #fff; border-color: #0f172a; }
    .dot-free { width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; }
    .dot-busy { width: 8px; height: 8px; border-radius: 50%; background: #f59e0b; display: inline-block; }

    .tbl-search-box { position: relative; width: 260px; max-width: 100%; }
    .tbl-search-box svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); pointer-events: none; }
    .tbl-search-box input { padding: 9px 14px 9px 38px; }
    .tbl-btn-icon { display: inline-flex; align-items: center; gap: 7px; }
</style>