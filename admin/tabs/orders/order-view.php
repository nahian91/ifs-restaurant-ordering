<?php
if (!defined('ABSPATH')) exit;

// Safe fallback for currency if not passed from main settings
if (!isset($cur)) {
    $s   = rtpos_get_settings();
    $cur = esc_html($s['currency'] ?? '৳');
}

$svg_print   = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>';
$svg_receipt = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1-2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>';
$svg_edit    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_note    = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>';
$svg_clock   = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';
$svg_table   = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 10h16M6 10v8M18 10v8M9 14h6"/></svg>';
$svg_back    = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';

$v_items  = json_decode($view_order->items, true) ?: [];
$st_class = 'ord-st-' . sanitize_html_class($view_order->status);
?>

<div class="ord-view-pro-shell">
    <!-- Back to Orders Bar -->
    <div class="ord-back-bar">
        <a href="?page=antalya-pos&tab=orders" class="ord-back-link">
            <?php echo $svg_back; ?>
            <span>Back to Orders List</span>
        </a>
    </div>

    <!-- Top Header Banner Pro -->
    <div class="ord-pro-header-card">
        <div class="ord-pro-title-wrap">
            <div class="ord-pro-meta">
                <span class="ord-pro-meta-date">
                    <?php echo $svg_clock; ?>
                    <span>PLACED ON <?php echo esc_html(strtoupper(date('M d, Y • h:i A', strtotime($view_order->created_at)))); ?></span>
                </span>
            </div>
            <h2 class="ord-pro-title">
                Order #<?php echo intval($view_order->id); ?>
            </h2>
        </div>

        <div class="ord-pro-controls">
            <div class="ord-select-wrapper" style="width: 190px;">
                <select class="ord-status-select <?php echo esc_attr($st_class); ?> js-ord-status-select" data-redirect="?page=antalya-pos&tab=orders&view_order=<?php echo intval($view_order->id); ?>&order_id=<?php echo intval($view_order->id); ?>&set_status=">
                    <option value="pending" <?php selected($view_order->status, 'pending'); ?>>Status: Pending</option>
                    <option value="preparing" <?php selected($view_order->status, 'preparing'); ?>>Status: Cooking</option>
                    <option value="served" <?php selected($view_order->status, 'served'); ?>>Status: Served</option>
                    <option value="completed" <?php selected($view_order->status, 'completed'); ?>>Status: Paid / Done</option>
                </select>
            </div>

            <a href="?page=antalya-pos&tab=orders&view_order=<?php echo intval($view_order->id); ?>&edit_order=1" class="ord-pro-btn ord-pro-btn-secondary">
                <?php echo $svg_edit; ?>
                <span>Edit Order</span>
            </a>

            <button type="button" class="ord-pro-btn ord-pro-btn-dark rtpos-print-kot" data-order='<?php echo esc_attr(wp_json_encode($view_order)); ?>'>
                <?php echo $svg_print; ?>
                <span>Print KOT</span>
            </button>

            <button type="button" class="ord-pro-btn ord-pro-btn-primary rtpos-print-bill" data-order='<?php echo esc_attr(wp_json_encode($view_order)); ?>'>
                <?php echo $svg_receipt; ?>
                <span>Customer Bill</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards Pro -->
    <div class="ord-pro-stats-grid">
        <div class="ord-pro-stat-card stat-border-orange">
            <div class="ord-pro-stat-lbl">Assigned Table</div>
            <div class="ord-pro-stat-val">
                <?php echo $svg_table; ?>
                <span>Table <?php echo esc_html($view_order->table_no); ?></span>
            </div>
        </div>

        <div class="ord-pro-stat-card stat-border-blue">
            <div class="ord-pro-stat-lbl">Current Status</div>
            <div class="ord-pro-stat-val ord-stat-val-status"><?php echo esc_html($view_order->status); ?></div>
        </div>

        <div class="ord-pro-stat-card stat-border-green">
            <div class="ord-pro-stat-lbl">Total Payable Amount</div>
            <div class="ord-pro-stat-val ord-stat-val-total"><?php echo $cur . number_format($view_order->total_amount, 2); ?></div>
        </div>
    </div>

    <!-- Kitchen Note Box Pro -->
    <?php if (!empty($view_order->note)) : ?>
        <div class="ord-pro-note-box">
            <?php echo $svg_note; ?>
            <div><strong>Special Kitchen Note:</strong> <?php echo esc_html($view_order->note); ?></div>
        </div>
    <?php endif; ?>

    <!-- Itemized Breakdown Table Pro Container -->
    <div class="ord-pro-content-box">
        <h3 class="ord-pro-sec-title">Itemized Order Breakdown</h3>
        <div class="ord-pro-table-wrapper">
            <table class="apos-table apos-full-table ord-pro-items-table">
                <thead>
                    <tr>
                        <th style="padding: 14px 18px;">Dish / Item Name</th>
                        <th style="padding: 14px 18px;">Selected Add-ons / Extras</th>
                        <th style="padding: 14px 18px; width: 140px;">Unit Price</th>
                        <th style="padding: 14px 18px; width: 90px;">Qty</th>
                        <th style="padding: 14px 18px; width: 150px; text-align: right;">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($v_items as $vi) : 
                        $u_price = floatval($vi['unitPrice'] ?? $vi['price'] ?? 0);
                        $qty     = intval($vi['qty'] ?? 1);
                        $l_total = $u_price * $qty;
                    ?>
                    <tr class="ord-pro-item-row">
                        <td style="padding: 14px 18px; vertical-align: middle;">
                            <strong class="ord-pro-dish-name"><?php echo esc_html($vi['name']); ?></strong>
                        </td>
                        <td style="padding: 14px 18px; vertical-align: middle;">
                            <?php if (!empty($vi['extras'])) : ?>
                                <div class="ord-pro-addons-list">
                                    <?php foreach ($vi['extras'] as $ex) : ?>
                                        <span class="ord-pro-addon-pill">+ <?php echo esc_html($ex); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <span class="ord-pro-no-addons">None</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 14px 18px; vertical-align: middle;"><span class="ord-mono"><?php echo $cur . number_format($u_price, 2); ?></span></td>
                        <td style="padding: 14px 18px; vertical-align: middle;"><strong class="ord-pro-qty-badge"><?php echo $qty; ?>×</strong></td>
                        <td style="padding: 14px 18px; vertical-align: middle; text-align: right;"><strong class="ord-mono ord-pro-line-tot"><?php echo $cur . number_format($l_total, 2); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Final Invoice Totals Summary Box Pro -->
        <div class="ord-pro-totals-card">
            <div class="ord-pro-tot-row">
                <span>Subtotal</span>
                <strong class="ord-mono"><?php echo $cur . number_format($view_order->subtotal, 2); ?></strong>
            </div>
            <div class="ord-pro-tot-tax">
                <span>VAT / Tax Included</span>
                <strong class="ord-mono"><?php echo $cur . number_format($view_order->tax_amount, 2); ?></strong>
            </div>
            <div class="ord-pro-tot-grand">
                <span>Total Payable</span>
                <span class="ord-pro-tot-grand-val ord-mono"><?php echo $cur . number_format($view_order->total_amount, 2); ?></span>
            </div>
        </div>
    </div>
</div>

<style>
    .ord-view-pro-shell { padding: 4px; max-width: 1400px; margin: 0 auto; }
    
    .ord-back-bar { margin-bottom: 16px; }
    .ord-back-link {
        display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700;
        color: #64748b; text-decoration: none; transition: color 0.15s;
    }
    .ord-back-link:hover { color: #f97316; }

    /* Top Pro Header */
    .ord-pro-header-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 24px;
        display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;
        gap: 16px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03);
    }
    .ord-pro-meta-date {
        display: inline-flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 800;
        color: #64748b; margin-bottom: 6px; letter-spacing: 0.3px;
    }
    .ord-pro-title { margin: 0; font-size: 24px; font-weight: 900; color: #0f172a; letter-spacing: -0.4px; }
    .ord-pro-controls { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }

    .ord-pro-btn {
        padding: 10px 16px; font-size: 13px; font-weight: 700; border-radius: 11px; cursor: pointer;
        display: inline-flex; align-items: center; gap: 7px; border: none; text-decoration: none;
        transition: all 0.18s ease; box-shadow: 0 2px 5px rgba(15, 23, 42, 0.04);
    }
    .ord-pro-btn-secondary { background: #f1f5f9; color: #334155; border: 1.5px solid #cbd5e1; }
    .ord-pro-btn-secondary:hover { background: #e2e8f0; color: #0f172a; }
    .ord-pro-btn-dark { background: #0f172a; color: #fff; }
    .ord-pro-btn-dark:hover { background: #1e293b; transform: translateY(-1px); }
    .ord-pro-btn-primary { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3); }
    .ord-pro-btn-primary:hover { opacity: 0.95; transform: translateY(-1px); }

    /* Stats Grid Pro */
    .ord-pro-stats-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px; margin-bottom: 20px;
    }
    .ord-pro-stat-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.02);
    }
    .stat-border-orange { border-left: 4.5px solid #f97316; }
    .stat-border-blue { border-left: 4.5px solid #3b82f6; }
    .stat-border-green { border-left: 4.5px solid #10b981; }
    .ord-pro-stat-lbl { font-size: 11.5px; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; }
    .ord-pro-stat-val { font-size: 18.5px; font-weight: 900; color: #0f172a; margin-top: 8px; display: flex; align-items: center; gap: 8px; }
    .ord-stat-val-status { text-transform: uppercase; color: #1d4ed8; }
    .ord-stat-val-total { color: #059669; font-family: 'Courier New', Courier, monospace; }

    /* Note Box Pro */
    .ord-pro-note-box {
        background: #fffbeb; border: 1.5px solid #fef3c7; color: #92400e;
        padding: 16px 20px; border-radius: 16px; margin-bottom: 20px; font-size: 13.5px;
        display: flex; align-items: center; gap: 12px; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.04);
    }

    /* Content Box & Table Pro */
    .ord-pro-content-box {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 24px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03);
    }
    .ord-pro-sec-title { font-size: 17px; font-weight: 900; color: #0f172a; margin: 0 0 16px; }
    .ord-pro-table-wrapper { border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; }
    .ord-pro-items-table th { background: #f8fafc; font-size: 11.5px; font-weight: 800; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0; }
    .ord-pro-item-row { transition: background 0.15s; border-bottom: 1px solid #f1f5f9; }
    .ord-pro-item-row:hover { background: #fafaf9; }
    .ord-pro-item-row:last-child { border-bottom: none; }
    .ord-pro-dish-name { font-size: 14.5px; color: #0f172a; }

    .ord-pro-addons-list { display: flex; flex-direction: column; gap: 4px; margin-top: 4px; }
    .ord-pro-addon-pill {
        display: inline-block; background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa;
        padding: 2px 9px; border-radius: 6px; font-size: 11.5px; font-weight: 700; width: fit-content;
    }
    .ord-pro-no-addons { color: #94a3b8; font-size: 13px; font-weight: 500; }
    .ord-pro-qty-badge { background: #0f172a; color: #fff; padding: 3px 9px; border-radius: 7px; font-size: 12px; }
    .ord-mono { font-family: 'Courier New', Courier, monospace; font-weight: 800; }
    .ord-pro-line-tot { color: #0f172a; }

    /* Totals Summary Card Pro */
    .ord-pro-totals-card {
        max-width: 380px; margin-left: auto; margin-top: 22px; background: #f8fafc;
        border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 20px 24px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02);
    }
    .ord-pro-tot-row { display: flex; justify-content: space-between; font-size: 14px; color: #64748b; margin-bottom: 10px; font-weight: 600; }
    .ord-pro-tot-tax {
        display: flex; justify-content: space-between; font-size: 14px; color: #64748b;
        padding-bottom: 14px; border-bottom: 1.5px dashed #cbd5e1; font-weight: 600;
    }
    .ord-pro-tot-grand {
        display: flex; justify-content: space-between; font-size: 17px; font-weight: 900;
        color: #0f172a; margin-top: 14px; align-items: center;
    }
    .ord-pro-tot-grand-val { color: #f97316; font-size: 22px; font-family: 'Courier New', Courier, monospace; }
</style>

<script>
jQuery(document).ready(function($) {
    let restName  = <?php echo json_encode($s['restaurant_name'] ?? 'Restaurant'); ?>;
    let currency  = <?php echo json_encode($cur); ?>;
    let footerTxt = <?php echo json_encode($s['receipt_footer'] ?? 'Thank you!'); ?>;

    // Status Select Dropdown Change
    $(document).on('change', '.js-ord-status-select', function() {
        let baseUrl = $(this).data('redirect');
        if (baseUrl) {
            window.location.href = baseUrl + encodeURIComponent($(this).val());
        }
    });

    // 1. Kitchen Order Ticket (KOT) Print Handler
    $('.rtpos-print-kot').on('click', function() {
        let order = $(this).data('order');
        if (!order) return;
        
        let items = typeof order.items === 'string' ? JSON.parse(order.items) : order.items;
        let rows = '';
        items.forEach(i => {
            let extraStr = (i.extras && i.extras.length) ? `<br><span style="font-size:12px; font-weight:normal;">  + ${i.extras.join(', ')}</span>` : '';
            rows += `<tr><td style="padding:4px 0;">${i.name}${extraStr}</td><td style="text-align:right; vertical-align:top;">x${i.qty}</td></tr>`;
        });

        let receipt = `
            <div style="text-align:center;">
                <h2 style="margin:0; font-size:20px;">${restName}</h2>
                <p style="margin:2px 0; font-size:12px;">*** KITCHEN TICKET (KOT) ***</p>
                <h1 style="margin:6px 0; border:2px solid #000; padding:4px;">TABLE: ${order.table_no}</h1>
                <p style="margin:2px 0;">Order #${order.id} | ${order.created_at}</p>
            </div>
            <hr style="border-top:1px dashed #000;">
            <table style="width:100%; font-size:14px; font-weight:bold;">${rows}</table>
            <hr style="border-top:1px dashed #000;">
            ${order.note ? `<p><strong>NOTE:</strong> ${order.note}</p><hr style="border-top:1px dashed #000;">` : ''}
        `;
        
        if ($('#rtpos-print-area').length === 0) {
            $('body').append('<div id="rtpos-print-area" style="display:none;"></div>');
        }
        $('#rtpos-print-area').html(receipt);
        window.print();
    });

    // 2. Customer Receipt / Tax Bill Print Handler
    $('.rtpos-print-bill').on('click', function() {
        let order = $(this).data('order');
        if (!order) return;

        let items = typeof order.items === 'string' ? JSON.parse(order.items) : order.items;
        let rows = '';
        items.forEach(i => {
            let line = (parseFloat(i.unitPrice || i.price) * parseInt(i.qty)).toFixed(2);
            let extraStr = (i.extras && i.extras.length) ? `<br><span style="font-size:11px; color:#444;">  + ${i.extras.join(', ')}</span>` : '';
            rows += `<tr><td style="padding:3px 0;">${i.qty}x ${i.name}${extraStr}</td><td style="text-align:right; vertical-align:top;">${currency}${line}</td></tr>`;
        });

        let receipt = `
            <div style="text-align:center;">
                <h2 style="margin:0; font-size:20px;">${restName}</h2>
                <p style="margin:2px 0; font-size:12px;">CUSTOMER RECEIPT</p>
                <p style="margin:2px 0;">Table: <strong>${order.table_no}</strong> | Order #${order.id}</p>
                <p style="margin:2px 0; font-size:11px;">${order.created_at}</p>
            </div>
            <hr style="border-top:1px dashed #000;">
            <table style="width:100%; font-size:13px;">${rows}</table>
            <hr style="border-top:1px dashed #000;">
            <div style="display:flex; justify-content:space-between;"><span>Subtotal:</span><span>${currency}${parseFloat(order.subtotal).toFixed(2)}</span></div>
            <div style="display:flex; justify-content:space-between;"><span>VAT/Tax:</span><span>${currency}${parseFloat(order.tax_amount).toFixed(2)}</span></div>
            <div style="display:flex; justify-content:space-between; font-size:16px; font-weight:bold; margin-top:4px;"><span>TOTAL:</span><span>${currency}${parseFloat(order.total_amount).toFixed(2)}</span></div>
            <hr style="border-top:1px dashed #000;">
            <p style="text-align:center; font-size:11px;">${footerTxt}</p>
        `;
        
        if ($('#rtpos-print-area').length === 0) {
            $('body').append('<div id="rtpos-print-area" style="display:none;"></div>');
        }
        $('#rtpos-print-area').html(receipt);
        window.print();
    });
});
</script>