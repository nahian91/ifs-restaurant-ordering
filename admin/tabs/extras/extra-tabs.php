<?php
/**
 * Antalya POS Extras & Add-ons Header & Sub-Tabs Bar
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

$svg_sparkles = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/></svg>';
$svg_plus     = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
$svg_edit     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_eye      = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
$svg_back     = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';
$svg_search   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';

$base_extras_url = admin_url('admin.php?page=antalya-pos&tab=extras');
$all_extras_url  = esc_url(add_query_arg('subtab', 'all', $base_extras_url));
$add_extra_url   = esc_url(add_query_arg('subtab', 'add', $base_extras_url));
$total_extras    = is_array($extras) ? count($extras) : 0;
?>

<div class="apos-header">
    <div>
        <h1><?php esc_html_e('Extras & Add-ons', 'antalya-pos'); ?></h1>
        <p><?php esc_html_e('Create optional paid toppings, sauces, cheese, or sides that customers can add to dishes.', 'antalya-pos'); ?></p>
    </div>
</div>

<div class="ex-topbar-row">
    <div class="ex-subtabs">
        <a href="<?php echo $all_extras_url; ?>" class="ex-subtab <?php echo ($subtab === 'all') ? 'active' : ''; ?>">
            <?php echo $svg_sparkles; ?>
            <span><?php esc_html_e('All Extras', 'antalya-pos'); ?></span>
            <span class="ex-count-pill"><?php echo intval($total_extras); ?></span>
        </a>
        <a href="<?php echo $add_extra_url; ?>" class="ex-subtab <?php echo ($subtab === 'add') ? 'active' : ''; ?>">
            <?php if (!empty($edit_extra)) : ?>
                <?php echo $svg_edit; ?>
                <span><?php esc_html_e('Edit Extra', 'antalya-pos'); ?></span>
            <?php else : ?>
                <?php echo $svg_plus; ?>
                <span><?php esc_html_e('Add Extra', 'antalya-pos'); ?></span>
            <?php endif; ?>
        </a>
        <?php if ($subtab === 'view' && !empty($view_extra)) : ?>
            <span class="ex-subtab active">
                <?php echo $svg_eye; ?>
                <span>
                    <?php
                    printf(
                        /* translators: %d: extra ID */
                        esc_html__('Viewing #%d', 'antalya-pos'),
                        intval($view_extra->id)
                    );
                    ?>
                </span>
            </span>
        <?php endif; ?>
    </div>

    <?php if ($subtab === 'all' && !empty($extras)) : ?>
        <div class="ex-search-box">
            <?php echo $svg_search; ?>
            <input type="text" id="ex-live-search" class="apos-input" placeholder="<?php esc_attr_e('Search extra add-on...', 'antalya-pos'); ?>">
        </div>
    <?php elseif ($subtab === 'view') : ?>
        <a href="<?php echo $all_extras_url; ?>" class="apos-btn-secondary ex-btn-icon">
            <?php echo $svg_back; ?>
            <span><?php esc_html_e('Back to All Extras', 'antalya-pos'); ?></span>
        </a>
    <?php endif; ?>
</div>

<style>
    .ex-topbar-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 24px; width: 100%; }
    .ex-subtabs { display: inline-flex; background: #e2e8f0; padding: 4px; border-radius: 12px; gap: 4px; }
    .ex-subtab {
        padding: 9px 18px; border-radius: 9px; font-size: 13.5px; font-weight: 700; text-decoration: none;
        color: #475569; display: inline-flex; align-items: center; gap: 7px; transition: all 0.18s ease;
    }
    .ex-subtab:hover { color: #0f172a; }
    .ex-subtab.active { background: #fff; color: #f97316; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08); }
    .ex-count-pill { background: #f1f5f9; color: #334155; padding: 1px 8px; border-radius: 10px; font-size: 11px; font-weight: 800; }
    .ex-subtab.active .ex-count-pill { background: #fff7ed; color: #ea580c; }
    .ex-search-box { position: relative; width: 300px; max-width: 100%; }
    .ex-search-box svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); pointer-events: none; }
    .ex-search-box input { padding: 9px 14px 9px 38px; }
    .ex-btn-icon { display: inline-flex; align-items: center; gap: 7px; }
</style>