<?php
/**
 * Antalya POS Orders List & Print Hub
 *
 * @package Antalya_POS
 * @since 6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

$svg_search  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';
$svg_eye     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
$svg_edit    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_print   = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>';
$svg_receipt = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1-2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>';
$svg_trash   = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';

$base_orders_url = admin_url('admin.php?page=antalya-pos&tab=orders');
$filter_tabs     = [
    'all'       => ['label' => __('All Orders', 'antalya-pos'), 'dot' => ''],
    'pending'   => ['label' => __('Pending', 'antalya-pos'),    'dot' => 'dot-pending'],
    'preparing' => ['label' => __('Preparing', 'antalya-pos'),  'dot' => 'dot-preparing'],
    'served'    => ['label' => __('Served', 'antalya-pos'),     'dot' => 'dot-served'],
    'completed' => ['label' => __('Paid', 'antalya-pos'),       'dot' => 'dot-completed'],
];
?>

<div class="ord-toolbar-pro">
    <div class="apos-pills ord-pills-modern">
        <?php foreach ($filter_tabs as $tab_key => $tab_meta) :
            $tab_args = ['status_filter' => $tab_key];
            if (!empty($search_query)) {
                $tab_args['s_ord'] = $search_query;
            }
            $tab_url = esc_url(add_query_arg($tab_args, $base_orders_url));
        ?>
            <a href="<?php echo $tab_url; ?>" class="apos-pill <?php echo ($filter === $tab_key) ? 'active' : ''; ?>">
                <?php if (!empty($tab_meta['dot'])) : ?>
                    <span class="st-indicator <?php echo esc_attr($tab_meta['dot']); ?>"></span>
                <?php endif; ?>
                <span><?php echo esc_html($tab_meta['label']); ?></span>
                <span class="pill-badge"><?php echo intval($counts[$tab_key] ?? 0); ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="ord-search-box-pro">
        <input type="hidden" name="page" value="antalya-pos">
        <input type="hidden" name="tab" value="orders">
        <input type="hidden" name="status_filter" value="<?php echo esc_attr($filter); ?>">
        <div class="search-input-wrap">
            <?php echo $svg_search; ?>
            <input type="text" name="s_ord" id="ord-live-search" class="ord-search-input-pro" value="<?php echo esc_attr($search_query); ?>" placeholder="<?php esc_attr_e('Search Order # or Table...', 'antalya-pos'); ?>">
        </div>
    </form>
</div>

<div class="apos-card ord-table-card-pro" style="padding: 0; overflow: hidden; border: 1px solid #e2e8f0; border-radius: 18px; box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);">
    <table class="apos-table apos-full-table" id="apos-orders-table" style="margin: 0; border: none;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0;">
                <th style="padding: 16px 20px;"><?php esc_html_e('Order ID & Time', 'antalya-pos'); ?></th>
                <th style="padding: 16px 16px;"><?php esc_html_e('Table No', 'antalya-pos'); ?></th>
                <th style="padding: 16px 16px;"><?php esc_html_e('Instant Status Switcher', 'antalya-pos'); ?></th>
                <th style="padding: 16px 20px; text-align: right;"><?php esc_html_e('Actions & Print Hub', 'antalya-pos'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)) : ?>
                <tr><td colspan="4" class="ord-empty-cell-pro"><?php esc_html_e('No orders found matching your criteria.', 'antalya-pos'); ?></td></tr>
            <?php endif; ?>

            <?php foreach ($orders as $ord) :
                $ord_id = intval($ord->id);

                $state_args = [
                    'status_filter' => $filter,
                    'paged'         => $paged,
                    'order_id'      => $ord_id,
                ];
                if (!empty($search_query)) {
                    $state_args['s_ord'] = $search_query;
                }

                $url_pending   = wp_nonce_url(add_query_arg(array_merge($state_args, ['set_status' => 'pending']), $base_orders_url), 'rtpos_order_status_' . $ord_id);
                $url_preparing = wp_nonce_url(add_query_arg(array_merge($state_args, ['set_status' => 'preparing']), $base_orders_url), 'rtpos_order_status_' . $ord_id);
                $url_served    = wp_nonce_url(add_query_arg(array_merge($state_args, ['set_status' => 'served']), $base_orders_url), 'rtpos_order_status_' . $ord_id);
                $url_completed = wp_nonce_url(add_query_arg(array_merge($state_args, ['set_status' => 'completed']), $base_orders_url), 'rtpos_order_status_' . $ord_id);

                $view_url = esc_url(add_query_arg('view_order', $ord_id, $base_orders_url));
                $edit_url = esc_url(add_query_arg(['view_order' => $ord_id, 'edit_order' => 1], $base_orders_url));

                $del_args = [
                    'status_filter' => $filter,
                    'paged'         => $paged,
                    'delete_order'  => $ord_id,
                ];
                if (!empty($search_query)) {
                    $del_args['s_ord'] = $search_query;
                }
                $del_url = wp_nonce_url(add_query_arg($del_args, $base_orders_url), 'rtpos_delete_order_' . $ord_id);
            ?>
            <tr class="ord-row-pro">
                <td style="padding: 16px 20px; vertical-align: middle;">
                    <a href="<?php echo $view_url; ?>" class="ord-id-link-pro">
                        #<?php echo $ord_id; ?>
                    </a>
                    <div class="ord-time-pro"><?php echo esc_html(date('h:i A', strtotime($ord->created_at))); ?></div>
                </td>
                <td style="padding: 16px 16px; vertical-align: middle;">
                    <span class="ord-table-badge-pro">
                        <?php esc_html_e('Table', 'antalya-pos'); ?> <?php echo esc_html($ord->table_no); ?>
                    </span>
                </td>
                <td style="padding: 16px 16px; vertical-align: middle;">
                    <!-- Highly Visible Status Switcher with SVG Icons -->
                    <div class="ord-fast-status-pro">
                        <a href="<?php echo esc_url($url_pending); ?>" class="pro-pill pill-pending-btn <?php echo ($ord->status === 'pending') ? 'active-state' : ''; ?>" title="<?php esc_attr_e('Set as Pending', 'antalya-pos'); ?>">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span><?php esc_html_e('Pending', 'antalya-pos'); ?></span>
                        </a>
                        <a href="<?php echo esc_url($url_preparing); ?>" class="pro-pill pill-preparing-btn <?php echo ($ord->status === 'preparing') ? 'active-state' : ''; ?>" title="<?php esc_attr_e('Set as Cooking', 'antalya-pos'); ?>">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
                            <span><?php esc_html_e('Cooking', 'antalya-pos'); ?></span>
                        </a>
                        <a href="<?php echo esc_url($url_served); ?>" class="pro-pill pill-served-btn <?php echo ($ord->status === 'served') ? 'active-state' : ''; ?>" title="<?php esc_attr_e('Set as Served', 'antalya-pos'); ?>">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                            <span><?php esc_html_e('Served', 'antalya-pos'); ?></span>
                        </a>
                        <a href="<?php echo esc_url($url_completed); ?>" class="pro-pill pill-completed-btn <?php echo ($ord->status === 'completed') ? 'active-state' : ''; ?>" title="<?php esc_attr_e('Set as Paid', 'antalya-pos'); ?>">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <span><?php esc_html_e('Paid', 'antalya-pos'); ?></span>
                        </a>
                    </div>
                </td>
                <td style="padding: 16px 20px; vertical-align: middle; text-align: right;">
                    <div class="ord-action-group-pro" style="justify-content: flex-end;">
                        <a href="<?php echo $view_url; ?>" class="ord-act-btn-pro act-view-pro" title="<?php esc_attr_e('View Details', 'antalya-pos'); ?>">
                            <?php echo $svg_eye; ?>
                            <span><?php esc_html_e('View', 'antalya-pos'); ?></span>
                        </a>
                        <a href="<?php echo $edit_url; ?>" class="ord-act-btn-pro act-edit-pro" title="<?php esc_attr_e('Edit Order', 'antalya-pos'); ?>">
                            <?php echo $svg_edit; ?>
                            <span><?php esc_html_e('Edit', 'antalya-pos'); ?></span>
                        </a>
                        <button type="button" class="ord-act-btn-pro act-kot-pro rtpos-print-kot" data-order="<?php echo esc_attr(wp_json_encode($ord)); ?>" title="<?php esc_attr_e('Print Kitchen Ticket', 'antalya-pos'); ?>">
                            <?php echo $svg_print; ?>
                            <span><?php esc_html_e('KOT', 'antalya-pos'); ?></span>
                        </button>
                        <button type="button" class="ord-act-btn-pro act-bill-pro rtpos-print-bill" data-order="<?php echo esc_attr(wp_json_encode($ord)); ?>" title="<?php esc_attr_e('Print Tax Invoice', 'antalya-pos'); ?>">
                            <?php echo $svg_receipt; ?>
                            <span><?php esc_html_e('Bill', 'antalya-pos'); ?></span>
                        </button>
                        <a href="<?php echo esc_url($del_url); ?>" class="ord-act-btn-pro act-del-pro js-delete-order" title="<?php esc_attr_e('Delete Order', 'antalya-pos'); ?>">
                            <?php echo $svg_trash; ?>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination Bar Pro -->
<?php if ($total_pages > 1) :
    $pag_args = ['status_filter' => $filter];
    if (!empty($search_query)) {
        $pag_args['s_ord'] = $search_query;
    }
    $base_pagination_url = add_query_arg($pag_args, $base_orders_url);
    $svg_chev_l = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>';
    $svg_chev_r = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>';
?>
    <div class="ord-pagination-pro">
        <div class="ord-pag-info">
            <?php
            printf(
                /* translators: 1: current page, 2: total pages, 3: total items */
                esc_html__('Showing Page %1$s of %2$s (Total %3$s orders)', 'antalya-pos'),
                '<strong>' . intval($paged) . '</strong>',
                '<strong>' . intval($total_pages) . '</strong>',
                '<strong>' . intval($total_items) . '</strong>'
            );
            ?>
        </div>
        <div class="ord-pag-links">
            <a href="<?php echo esc_url(add_query_arg('paged', 1, $base_pagination_url)); ?>" class="ord-pag-btn <?php echo ($paged <= 1) ? 'ord-pag-disabled' : ''; ?>"><?php esc_html_e('First', 'antalya-pos'); ?></a>
            <a href="<?php echo esc_url(add_query_arg('paged', max(1, $paged - 1), $base_pagination_url)); ?>" class="ord-pag-btn <?php echo ($paged <= 1) ? 'ord-pag-disabled' : ''; ?>" style="display:inline-flex;align-items:center;gap:4px;">
                <?php echo $svg_chev_l; ?>
                <span><?php esc_html_e('Prev', 'antalya-pos'); ?></span>
            </a>

            <?php for ($p = max(1, $paged - 2); $p <= min($total_pages, $paged + 2); $p++) : ?>
                <a href="<?php echo esc_url(add_query_arg('paged', $p, $base_pagination_url)); ?>" class="ord-pag-btn <?php echo ($p === $paged) ? 'current' : ''; ?>"><?php echo intval($p); ?></a>
            <?php endfor; ?>

            <a href="<?php echo esc_url(add_query_arg('paged', min($total_pages, $paged + 1), $base_pagination_url)); ?>" class="ord-pag-btn <?php echo ($paged >= $total_pages) ? 'ord-pag-disabled' : ''; ?>" style="display:inline-flex;align-items:center;gap:4px;">
                <span><?php esc_html_e('Next', 'antalya-pos'); ?></span>
                <?php echo $svg_chev_r; ?>
            </a>
            <a href="<?php echo esc_url(add_query_arg('paged', $total_pages, $base_pagination_url)); ?>" class="ord-pag-btn <?php echo ($paged >= $total_pages) ? 'ord-pag-disabled' : ''; ?>"><?php esc_html_e('Last', 'antalya-pos'); ?></a>
        </div>
    </div>
<?php endif; ?>

<style>
    /* Pro Toolbar Layout */
    .ord-toolbar-pro {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 22px; flex-wrap: wrap; gap: 14px;
    }
    .ord-pills-modern {
        display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin: 0 !important;
    }
    .ord-pills-modern .apos-pill {
        background: #fff; border: 1.5px solid #e2e8f0; color: #475569; padding: 8px 16px;
        border-radius: 12px; font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; gap: 8px;
        box-shadow: 0 2px 4px rgba(15, 23, 42, 0.02); transition: all 0.2s ease; text-decoration: none;
    }
    .ord-pills-modern .apos-pill:hover { border-color: #cbd5e1; background: #f8fafc; color: #0f172a; }
    .ord-pills-modern .apos-pill.active {
        background: #0f172a; color: #fff; border-color: #0f172a;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
    }
    .st-indicator { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .pill-badge {
        background: rgba(0, 0, 0, 0.08); padding: 1px 7px; border-radius: 10px; font-size: 11px; font-weight: 800;
    }
    .apos-pill.active .pill-badge { background: rgba(255, 255, 255, 0.2); color: #fff; }

    /* Search Box Pro */
    .ord-search-box-pro { width: 300px; max-width: 100%; }
    .search-input-wrap { position: relative; display: flex; align-items: center; }
    .search-input-wrap svg { position: absolute; left: 14px; pointer-events: none; }
    .ord-search-input-pro {
        width: 100%; padding: 10px 16px 10px 42px; border: 1.5px solid #e2e8f0; border-radius: 12px;
        font-size: 13.5px; font-weight: 600; background: #fff; color: #0f172a; box-sizing: border-box;
        transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(15, 23, 42, 0.02);
    }
    .ord-search-input-pro:focus {
        outline: none; border-color: #f97316; box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.12);
    }

    /* Table Typography & Rows */
    .ord-row-pro { transition: background 0.15s ease; border-bottom: 1px solid #f1f5f9; }
    .ord-row-pro:hover { background-color: #fafaf9; }
    .ord-row-pro:last-child { border-bottom: none; }

    .ord-id-link-pro { font-size: 15px; font-weight: 900; color: #0f172a; text-decoration: none; transition: color 0.15s; }
    .ord-id-link-pro:hover { color: #f97316; }
    .ord-time-pro { font-size: 12px; font-weight: 600; color: #64748b; margin-top: 3px; font-family: monospace; }

    .ord-table-badge-pro {
        background: #f1f5f9; color: #334155; padding: 6px 14px; border-radius: 20px;
        font-size: 12.5px; font-weight: 800; display: inline-block; border: 1px solid #e2e8f0;
    }

    /* Enhanced Status Switcher Buttons */
    .ord-fast-status-pro { display: flex; gap: 5px; align-items: center; flex-wrap: wrap; }
    .pro-pill {
        padding: 6px 10px; border-radius: 9px; font-size: 11px; font-weight: 800;
        text-decoration: none; color: #475569; background: #f8fafc; border: 1.5px solid #cbd5e1;
        transition: all 0.18s ease; display: inline-flex; align-items: center; gap: 5px;
        text-transform: uppercase; letter-spacing: 0.3px; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .pro-pill:hover { background: #f1f5f9; color: #0f172a; border-color: #94a3b8; transform: translateY(-1px); }

    /* Active / Current Status State Highlights */
    .pro-pill.active-state {
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.12); transform: scale(1.03); font-weight: 900;
    }
    .pill-pending-btn.active-state { background: #fef3c7 !important; color: #b45309 !important; border-color: #f59e0b !important; }
    .pill-preparing-btn.active-state { background: #eff6ff !important; color: #1d4ed8 !important; border-color: #3b82f6 !important; }
    .pill-served-btn.active-state { background: #faf5ff !important; color: #7e22ce !important; border-color: #a855f7 !important; }
    .pill-completed-btn.active-state { background: #ecfdf5 !important; color: #047857 !important; border-color: #10b981 !important; }

    /* Action Buttons Group Pro */
    .ord-action-group-pro { display: flex; gap: 6px; align-items: center; }
    .ord-act-btn-pro {
        padding: 6px 12px; border-radius: 9px; font-size: 12px; font-weight: 700;
        text-decoration: none; cursor: pointer; border: none; transition: all 0.18s ease;
        display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
    }
    .act-view-pro { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
    .act-view-pro:hover { background: #dbeafe; }
    .act-edit-pro { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }
    .act-edit-pro:hover { background: #e2e8f0; color: #0f172a; }
    .act-kot-pro { background: #0f172a; color: #fff; border: 1px solid #0f172a; }
    .act-kot-pro:hover { background: #1e293b; }
    .act-bill-pro { background: #f8fafc; color: #0f172a; border: 1.5px solid #cbd5e1; }
    .act-bill-pro:hover { background: #f1f5f9; border-color: #94a3b8; }
    .act-del-pro { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; padding: 6px 9px; }
    .act-del-pro:hover { background: #fee2e2; }

    .ord-pagination-pro {
        display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;
        gap: 12px; margin-top: 22px; padding: 14px 18px; background: #fff; border-radius: 14px;
        border: 1px solid #e2e8f0; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.02);
    }

    .ord-empty-cell-pro { text-align: center; padding: 50px 20px; color: #64748b; font-weight: 600; font-size: 14px; }
</style>

<script>
jQuery(document).ready(function($) {
    let restName  = <?php echo wp_json_encode($s['restaurant_name'] ?? 'Restaurant'); ?>;
    let restPhone = <?php echo wp_json_encode($s['phone'] ?? ''); ?>;
    let restEmail = <?php echo wp_json_encode($s['email'] ?? ''); ?>;
    let currency  = <?php echo wp_json_encode($s['currency'] ?? '৳'); ?>;
    let footerTxt = <?php echo wp_json_encode($s['receipt_footer'] ?? 'Thank you!'); ?>;
    let ordPrefix = <?php echo wp_json_encode($s['order_prefix'] ?? 'ORD-'); ?>;

    // Escape HTML helper to prevent XSS in dynamic print markup
    function escHtml(str) {
        return String(str === null || str === undefined ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Confirm Delete Order
    $(document).on('click', '.js-delete-order', function(e) {
        if (!confirm(<?php echo wp_json_encode(__('Are you sure you want to delete this order permanently?', 'antalya-pos')); ?>)) {
            e.preventDefault();
        }
    });

    // 1. Kitchen Order Ticket (KOT) Print
    function printKitchenKOT(order) {
        let items = typeof order.items === 'string' ? JSON.parse(order.items) : (order.items || []);
        let rows = '';
        items.forEach(function(i) {
            let extraStr = (i.extras && i.extras.length)
                ? `<br><span style="font-size:12px; font-weight:normal;">  + ${i.extras.map(escHtml).join(', ')}</span>`
                : '';
            rows += `<tr><td style="padding:4px 0;">${escHtml(i.name)}${extraStr}</td><td style="text-align:right; vertical-align:top;">x${parseInt(i.qty || 1, 10)}</td></tr>`;
        });

        let receipt = `
            <div style="text-align:center;">
                <h2 style="margin:0; font-size:20px;">${escHtml(restName)}</h2>
                <p style="margin:2px 0; font-size:12px;">*** KITCHEN TICKET (KOT) ***</p>
                <h1 style="margin:6px 0; border:2px solid #000; padding:4px;">TABLE: ${escHtml(order.table_no)}</h1>
                <p style="margin:2px 0;">Order #${parseInt(order.id, 10)} | ${escHtml(order.created_at)}</p>
            </div>
            <hr style="border-top:1px dashed #000;">
            <table style="width:100%; font-size:14px; font-weight:bold;">${rows}</table>
            <hr style="border-top:1px dashed #000;">
            ${order.note ? `<p><strong>NOTE:</strong> ${escHtml(order.note)}</p><hr style="border-top:1px dashed #000;">` : ''}
        `;
        if ($('#rtpos-print-area').length === 0) {
            $('body').append('<div id="rtpos-print-area" style="display:none;"></div>');
        }
        $('#rtpos-print-area').html(receipt);
        window.print();
    }

    // 2. Customer Receipt / Tax Bill Print
    function printCustomerBill(order) {
        let items = typeof order.items === 'string' ? JSON.parse(order.items) : (order.items || []);
        let rows = '';
        items.forEach(function(i) {
            let unitPr = parseFloat(i.unitPrice || i.price || 0);
            let qty    = parseInt(i.qty || 1, 10);
            let line   = (unitPr * qty).toFixed(2);

            rows += `
                <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
                    <span>${qty}x ${escHtml(i.name)}</span>
                    <span>${escHtml(currency)}${line}</span>
                </div>`;

            if (i.extras && i.extras.length) {
                i.extras.forEach(function(ex) {
                    rows += `
                        <div style="display:flex; justify-content:space-between; font-size:10.5px; color:#475569; padding-left:8px; margin-bottom:2px;">
                            <span>  - ${escHtml(ex)}</span>
                        </div>`;
                });
            }
        });

        let contactStr = '';
        if (restPhone && restEmail) contactStr = 'Ph: ' + escHtml(restPhone) + ' | ' + escHtml(restEmail);
        else if (restPhone) contactStr = 'Ph: ' + escHtml(restPhone);
        else if (restEmail) contactStr = escHtml(restEmail);

        let totalAmt     = parseFloat(order.total_amount || 0);
        let totalPayable = totalAmt.toFixed(2);
        let tendered     = (totalAmt + 150).toFixed(2);
        let changeReturn = (parseFloat(tendered) - totalAmt).toFixed(2);

        let receipt = `
            <div style="font-family:'Courier New',Courier,monospace; font-size:11px; color:#111; width:280px; margin:0 auto; line-height:1.35;">
                <div style="text-align:center;">
                    <h3 style="margin:0 0 2px; font-size:15px; font-weight:900; text-transform:uppercase; letter-spacing:0.5px;">${escHtml(restName)}</h3>
                    <div style="font-size:9.5px; color:#334155;">${contactStr}</div>
                    <div style="font-size:9.5px; font-weight:800; color:#475569; margin-bottom:4px;">*** TAX INVOICE ***</div>
                    <div style="font-size:10px; color:#334155;">Table: <strong>${escHtml(order.table_no)}</strong> | Ord: <strong>${escHtml(ordPrefix)}${parseInt(order.id, 10)}</strong></div>
                    <div style="font-size:9px; color:#555;">${escHtml(order.created_at)}</div>
                </div>

                <hr style="border:none; border-top:1px dashed #64748b; margin:8px 0;">

                <div style="display:flex; justify-content:space-between; font-weight:800; font-size:10px; border-bottom:1px solid #000; padding-bottom:2px; margin-bottom:4px;">
                    <span>ITEM DESC</span>
                    <span>TOTAL</span>
                </div>

                ${rows}

                <hr style="border:none; border-top:1px dashed #64748b; margin:8px 0;">

                <div style="display:flex; justify-content:space-between; font-size:10.5px; color:#334155; margin-bottom:3px;">
                    <span>Subtotal:</span>
                    <span>${escHtml(currency)}${parseFloat(order.subtotal || 0).toFixed(2)}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:10.5px; color:#334155; margin-bottom:3px;">
                    <span>VAT/Tax:</span>
                    <span>${escHtml(currency)}${parseFloat(order.tax_amount || 0).toFixed(2)}</span>
                </div>

                <div style="display:flex; justify-content:space-between; font-size:13.5px; font-weight:900; margin-top:6px; border-top:1.5px solid #000; padding-top:5px;">
                    <span>TOTAL PAYABLE:</span>
                    <span>${escHtml(currency)}${totalPayable}</span>
                </div>

                <div style="display:flex; justify-content:space-between; font-size:10.5px; color:#334155; border-top:1px dotted #64748b; padding-top:3px; margin-top:3px;">
                    <span>Method:</span>
                    <strong>CASH</strong>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:10.5px; color:#334155;">
                    <span>Tendered:</span>
                    <span>${escHtml(currency)}${tendered}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:10.5px; font-weight:700; color:#059669;">
                    <span>CHANGE:</span>
                    <span>${escHtml(currency)}${changeReturn}</span>
                </div>

                <hr style="border:none; border-top:1px dashed #64748b; margin:8px 0;">

                <div style="text-align:center; font-size:10px; color:#334155; margin-top:8px; font-style:italic;">
                    ${escHtml(footerTxt)}
                </div>
            </div>
        `;

        if ($('#rtpos-print-area').length === 0) {
            $('body').append('<div id="rtpos-print-area" style="display:none;"></div>');
        }
        $('#rtpos-print-area').html(receipt);
        window.print();
    }

    $(document).on('click', '.rtpos-print-kot', function() { printKitchenKOT($(this).data('order')); });
    $(document).on('click', '.rtpos-print-bill', function() { printCustomerBill($(this).data('order')); });
});
</script>