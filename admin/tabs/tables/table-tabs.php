<?php
/**
 * Antalya POS Restaurant Tables Header & Sub-Tabs Bar
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

// Reusable SVG Icons for Tabs Header
$svg_grid   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>';
$svg_plus   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
$svg_edit   = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_eye    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
$svg_back   = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';
$svg_search = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';

$base_tables_url = admin_url('admin.php?page=antalya-pos&tab=tables');
$all_tables_url  = esc_url(add_query_arg('subtab', 'all', $base_tables_url));
$add_table_url   = esc_url(add_query_arg('subtab', 'add', $base_tables_url));

$total_seats    = isset($total_seats) ? intval($total_seats) : 0;
$total_tables   = is_array($tables) ? count($tables) : 0;
$occupied_count = isset($occupied_count) ? intval($occupied_count) : 0;
$free_count     = isset($free_count) ? intval($free_count) : 0;
?>

<!-- Page Header -->
<div class="apos-header">
    <div>
        <h1><?php esc_html_e('Restaurant Tables', 'antalya-pos'); ?></h1>
        <p>
            <?php
            printf(
                /* translators: 1: total seats, 2: total tables, 3: occupied count, 4: available count */
                esc_html__('Total Capacity: %1$s Seats across %2$s Tables (%3$s Occupied, %4$s Available)', 'antalya-pos'),
                '<strong>' . $total_seats . '</strong>',
                '<strong>' . $total_tables . '</strong>',
                $occupied_count,
                $free_count
            );
            ?>
        </p>
    </div>
</div>

<!-- Sub-Tabs & Filter Toolbar -->
<div class="tbl-topbar-row">
    <div class="tbl-topbar-left">
        <div class="tbl-subtabs">
            <a href="<?php echo $all_tables_url; ?>" class="tbl-subtab <?php echo ($subtab === 'all') ? 'active' : ''; ?>">
                <?php echo $svg_grid; ?>
                <span><?php esc_html_e('All Tables', 'antalya-pos'); ?></span>
                <span class="tbl-count-pill"><?php echo $total_tables; ?></span>
            </a>
            <a href="<?php echo $add_table_url; ?>" class="tbl-subtab <?php echo ($subtab === 'add') ? 'active' : ''; ?>">
                <?php if (!empty($edit_tbl)) : ?>
                    <?php echo $svg_edit; ?>
                    <span><?php esc_html_e('Edit Table', 'antalya-pos'); ?></span>
                <?php else : ?>
                    <?php echo $svg_plus; ?>
                    <span><?php esc_html_e('Add Table', 'antalya-pos'); ?></span>
                <?php endif; ?>
            </a>
            <?php if ($subtab === 'view' && !empty($view_tbl)) : ?>
                <span class="tbl-subtab active">
                    <?php echo $svg_eye; ?>
                    <span>
                        <?php
                        printf(
                            /* translators: %s: table name */
                            esc_html__('Table %s', 'antalya-pos'),
                            esc_html($view_tbl->table_name)
                        );
                        ?>
                    </span>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($subtab === 'all' && !empty($tables)) : ?>
            <div class="tbl-status-filters">
                <button type="button" class="tbl-st-pill active" data-st="all">
                    <?php
                    printf(
                        /* translators: %d: total table count */
                        esc_html__('All (%d)', 'antalya-pos'),
                        $total_tables
                    );
                    ?>
                </button>
                <button type="button" class="tbl-st-pill" data-st="free">
                    <span class="dot-free"></span>
                    <?php
                    printf(
                        /* translators: %d: available table count */
                        esc_html__('Available (%d)', 'antalya-pos'),
                        $free_count
                    );
                    ?>
                </button>
                <button type="button" class="tbl-st-pill" data-st="busy">
                    <span class="dot-busy"></span>
                    <?php
                    printf(
                        /* translators: %d: occupied table count */
                        esc_html__('Occupied (%d)', 'antalya-pos'),
                        $occupied_count
                    );
                    ?>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($subtab === 'all' && !empty($tables)) : ?>
        <div class="tbl-search-box">
            <?php echo $svg_search; ?>
            <input type="text" id="tbl-live-search" class="apos-input" placeholder="<?php esc_attr_e('Search table or login...', 'antalya-pos'); ?>">
        </div>
    <?php elseif ($subtab === 'view') : ?>
        <a href="<?php echo $all_tables_url; ?>" class="apos-btn-secondary tbl-btn-icon">
            <?php echo $svg_back; ?>
            <span><?php esc_html_e('Back to All Tables', 'antalya-pos'); ?></span>
        </a>
    <?php endif; ?>
</div>

<style>
    .tbl-topbar-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 24px; width: 100%; }
    .tbl-topbar-left { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .tbl-subtabs { display: inline-flex; background: #e2e8f0; padding: 4px; border-radius: 12px; gap: 4px; }
    .tbl-subtab {
        padding: 9px 18px; border-radius: 9px; font-size: 13.5px; font-weight: 700; text-decoration: none;
        color: #475569; transition: all 0.18s ease; display: inline-flex; align-items: center; gap: 7px;
    }
    .tbl-subtab:hover { color: #0f172a; }
    .tbl-subtab.active { background: #fff; color: #f97316; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08); }
    .tbl-count-pill { background: #f1f5f9; color: #334155; padding: 1px 8px; border-radius: 10px; font-size: 11px; font-weight: 800; }
    .tbl-subtab.active .tbl-count-pill { background: #fff7ed; color: #ea580c; }

    .tbl-status-filters { display: flex; gap: 8px; flex-wrap: wrap; }
    .tbl-st-pill {
        padding: 7px 14px; border-radius: 20px; font-size: 12.5px; font-weight: 700;
        border: 1.5px solid #e2e8f0; background: #fff; color: #475569; cursor: pointer;
        transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 6px;
    }
    .tbl-st-pill.active, .tbl-st-pill:hover { background: #0f172a; color: #fff; border-color: #0f172a; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.1); }
    .dot-free { width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; }
    .dot-busy { width: 8px; height: 8px; border-radius: 50%; background: #f59e0b; display: inline-block; }

    .tbl-search-box { position: relative; width: 260px; max-width: 100%; }
    .tbl-search-box svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); pointer-events: none; }
    .tbl-search-box input { padding: 9px 14px 9px 38px; }
    .tbl-btn-icon { display: inline-flex; align-items: center; gap: 7px; }
</style>