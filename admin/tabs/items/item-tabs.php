<?php
/**
 * Antalya POS Menu Items Header & Sub-Tabs Bar
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

$svg_utensils = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>';
$svg_plus     = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
$svg_edit     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_eye      = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
$svg_back     = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';
$svg_search   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';

$base_items_url = admin_url('admin.php?page=antalya-pos&tab=items');
$all_items_url  = esc_url(add_query_arg('subtab', 'all', $base_items_url));
$add_item_url   = esc_url(add_query_arg('subtab', 'add', $base_items_url));
$total_items    = is_array($items) ? count($items) : 0;
?>

<div class="apos-header">
    <div>
        <h1><?php esc_html_e('Food Items', 'antalya-pos'); ?></h1>
        <p><?php esc_html_e('Manage restaurant menu dishes, upload photos, and attach optional add-ons.', 'antalya-pos'); ?></p>
    </div>
</div>

<div class="itm-topbar-row">
    <div class="itm-subtabs">
        <a href="<?php echo $all_items_url; ?>" class="itm-subtab <?php echo ($subtab === 'all') ? 'active' : ''; ?>">
            <?php echo $svg_utensils; ?>
            <span><?php esc_html_e('All Items', 'antalya-pos'); ?></span>
            <span class="itm-count-pill"><?php echo intval($total_items); ?></span>
        </a>
        <a href="<?php echo $add_item_url; ?>" class="itm-subtab <?php echo ($subtab === 'add') ? 'active' : ''; ?>">
            <?php if (!empty($edit_item)) : ?>
                <?php echo $svg_edit; ?>
                <span><?php esc_html_e('Edit Item', 'antalya-pos'); ?></span>
            <?php else : ?>
                <?php echo $svg_plus; ?>
                <span><?php esc_html_e('Add Item', 'antalya-pos'); ?></span>
            <?php endif; ?>
        </a>
        <?php if ($subtab === 'view' && !empty($view_item)) : ?>
            <span class="itm-subtab active">
                <?php echo $svg_eye; ?>
                <span>
                    <?php
                    printf(
                        /* translators: %d: item ID */
                        esc_html__('Viewing #%d', 'antalya-pos'),
                        intval($view_item->id)
                    );
                    ?>
                </span>
            </span>
        <?php endif; ?>
    </div>

    <?php if ($subtab === 'all' && !empty($items)) : ?>
        <div class="itm-filter-group">
            <select id="itm-cat-filter" class="apos-input itm-cat-select" aria-label="<?php esc_attr_e('Filter by Category', 'antalya-pos'); ?>">
                <option value="all"><?php esc_html_e('All Categories', 'antalya-pos'); ?></option>
                <?php if (!empty($cats) && is_array($cats)) : foreach ($cats as $c) : ?>
                    <option value="<?php echo esc_attr(strtolower($c->name)); ?>"><?php echo esc_html($c->name); ?></option>
                <?php endforeach; endif; ?>
            </select>
            <div class="itm-search-box">
                <?php echo $svg_search; ?>
                <input type="text" id="itm-live-search" class="apos-input" placeholder="<?php esc_attr_e('Search dish name...', 'antalya-pos'); ?>">
            </div>
        </div>
    <?php elseif ($subtab === 'view') : ?>
        <a href="<?php echo $all_items_url; ?>" class="apos-btn-secondary itm-btn-icon">
            <?php echo $svg_back; ?>
            <span><?php esc_html_e('Back to All Items', 'antalya-pos'); ?></span>
        </a>
    <?php endif; ?>
</div>

<style>
    .itm-topbar-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 24px; width: 100%; }
    .itm-subtabs { display: inline-flex; background: #e2e8f0; padding: 4px; border-radius: 12px; gap: 4px; }
    .itm-subtab {
        padding: 9px 18px; border-radius: 9px; font-size: 13.5px; font-weight: 700; text-decoration: none;
        color: #475569; display: inline-flex; align-items: center; gap: 7px; transition: all 0.18s ease;
    }
    .itm-subtab:hover { color: #0f172a; }
    .itm-subtab.active { background: #fff; color: #f97316; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08); }
    .itm-count-pill { background: #f1f5f9; color: #334155; padding: 1px 8px; border-radius: 10px; font-size: 11px; font-weight: 800; }
    .itm-subtab.active .itm-count-pill { background: #fff7ed; color: #ea580c; }
    .itm-filter-group { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .itm-cat-select { width: 195px; padding: 9px 36px 9px 14px; }
    .itm-search-box { position: relative; width: 250px; max-width: 100%; }
    .itm-search-box svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); pointer-events: none; }
    .itm-search-box input { padding: 9px 14px 9px 38px; }
    .itm-btn-icon { display: inline-flex; align-items: center; gap: 7px; }
</style>