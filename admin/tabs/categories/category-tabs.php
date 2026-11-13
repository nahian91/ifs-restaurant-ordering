<?php
if (!defined('ABSPATH')) exit;

$svg_folder = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>';
$svg_plus   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
$svg_edit   = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_eye    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
$svg_back   = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';
$svg_search = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';
?>

<div class="apos-header">
    <div>
        <h1>Food Categories</h1>
        <p>Organize your restaurant menu into clean, browsable sections for customer tablets.</p>
    </div>
</div>

<div class="cat-topbar-row">
    <div class="cat-subtabs">
        <a href="?page=antalya-pos&tab=categories&subtab=all" class="cat-subtab <?php echo $subtab === 'all' ? 'active' : ''; ?>">
            <?php echo $svg_folder; ?>
            <span>All Categories</span>
            <span class="cat-count-pill"><?php echo count($cats); ?></span>
        </a>
        <a href="?page=antalya-pos&tab=categories&subtab=add" class="cat-subtab <?php echo $subtab === 'add' ? 'active' : ''; ?>">
            <?php echo $edit_cat ? $svg_edit . '<span>Edit Category</span>' : $svg_plus . '<span>Add Category</span>'; ?>
        </a>
        <?php if ($subtab === 'view' && $view_cat) : ?>
            <span class="cat-subtab active">
                <?php echo $svg_eye; ?>
                <span>Viewing #<?php echo intval($view_cat->id); ?></span>
            </span>
        <?php endif; ?>
    </div>

    <?php if ($subtab === 'all' && !empty($cats)) : ?>
        <div class="cat-search-box">
            <?php echo $svg_search; ?>
            <input type="text" id="cat-live-search" class="apos-input" placeholder="Search category...">
        </div>
    <?php elseif ($subtab === 'view') : ?>
        <a href="?page=antalya-pos&tab=categories&subtab=all" class="apos-btn-secondary cat-btn-icon">
            <?php echo $svg_back; ?>
            <span>Back to All Categories</span>
        </a>
    <?php endif; ?>
</div>

<style>
    .cat-topbar-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 24px; width: 100%; }
    .cat-subtabs { display: inline-flex; background: #e2e8f0; padding: 4px; border-radius: 12px; gap: 4px; }
    .cat-subtab { padding: 9px 18px; border-radius: 9px; font-size: 13.5px; font-weight: 700; text-decoration: none; color: #475569; display: inline-flex; align-items: center; gap: 7px; }
    .cat-subtab.active { background: #fff; color: #f97316; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08); }
    .cat-count-pill { background: #f1f5f9; color: #334155; padding: 1px 7px; border-radius: 10px; font-size: 11px; }
    .cat-search-box { position: relative; width: 300px; max-width: 100%; }
    .cat-search-box svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); pointer-events: none; }
    .cat-search-box input { padding: 9px 14px 9px 38px; }
    .cat-btn-icon { display: inline-flex; align-items: center; gap: 7px; }
</style>