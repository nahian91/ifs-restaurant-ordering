<?php
if (!defined('ABSPATH')) exit;

// Safe fallback for currency if not passed from main settings
if (!isset($cur)) {
    $s   = rtpos_get_settings();
    $cur = esc_html($s['currency'] ?? '৳');
}

$svg_print   = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>';
$svg_receipt = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>';
$svg_edit    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_note    = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>';
$svg_clock   = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';
$svg_table   = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>';

$v_items  = json_decode($view_order->items, true) ?: [];
$st_class = 'ord-st-' . sanitize_html_class($view_order->status);
?>

<div class="apos-card apos-full-card ord-view-wrapper">
    <!-- Top Header Banner -->
    <div class="ord-view-header">
        <div class="ord-view-title-wrap">
            <div class="ord-view-meta">
                <span class="ord-view-meta-date">
                    <?php echo $svg_clock; ?>
                    <span>PLACED ON <?php echo esc_html(strtoupper(date('M d, Y • h:i A', strtotime($view_order->created_at)))); ?></span>
                </span>
            </div>
            <h2 class="ord-view-title">
                Order #<?php echo intval($view_order->id); ?>
            </h2>
        </div>

        <div class="ord-view-controls">
            <div class="ord-select-wrapper" style="width: 180px;">
                <select class="ord-status-select <?php echo esc_attr($st_class); ?> js-ord-status-select" data-redirect="?page=antalya-pos&tab=orders&view_order=<?php echo intval($view_order->id); ?>&order_id=<?php echo intval($view_order->id); ?>&set_status=">
                    <option value="pending" <?php selected($view_order->status, 'pending'); ?>>Status: Pending</option>
                    <option value="preparing" <?php selected($view_order->status, 'preparing'); ?>>Status: Preparing</option>
                    <option value="served" <?php selected($view_order->status, 'served'); ?>>Status: Served</option>
                    <option value="completed" <?php selected($view_order->status, 'completed'); ?>>Status: Paid / Completed</option>
                </select>
            </div>

            <!-- Fixed Edit Button Link -->
            <a href="?page=antalya-pos&tab=orders&view_order=<?php echo intval($view_order->id); ?>&edit_order=1" class="apos-btn-secondary ord-view-edit-btn">
                <?php echo $svg_edit; ?>
                <span>Edit Order</span>
            </a>

            <button type="button" class="apos-btn-dark rtpos-print-kot ord-view-kot-btn" data-order='<?php echo esc_attr(wp_json_encode($view_order)); ?>'>
                <?php echo $svg_print; ?>
                <span>Print KOT</span>
            </button>

            <button type="button" class="apos-btn rtpos-print-bill ord-view-bill-btn" data-order='<?php echo esc_attr(wp_json_encode($view_order)); ?>'>
                <?php echo $svg_receipt; ?>
                <span>Print Customer Bill</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="ord-view-stats">
        <div class="ord-stat-box ord-stat-orange">
            <div class="ord-stat-lbl">Assigned Table</div>
            <div class="ord-stat-val">
                <?php echo $svg_table; ?>
                <span>Table <?php echo esc_html($view_order->table_no); ?></span>
            </div>
        </div>

        <div class="ord-stat-box ord-stat-blue">
            <div class="ord-stat-lbl">Order Status</div>
            <div class="ord-stat-val ord-stat-val-status"><?php echo esc_html($view_order->status); ?></div>
        </div>

        <div class="ord-stat-box ord-stat-green">
            <div class="ord-stat-lbl">Total Payable Amount</div>
            <div class="ord-stat-val ord-stat-val-total"><?php echo $cur . number_format($view_order->total_amount, 2); ?></div>
        </div>
    </div>

    <!-- Kitchen Note Box -->
    <?php if (!empty($view_order->note)) : ?>
        <div class="ord-view-note-box">
            <?php echo $svg_note; ?>
            <div><strong>Special Kitchen Instruction:</strong> <?php echo esc_html($view_order->note); ?></div>
        </div>
    <?php endif; ?>

    <!-- Itemized Breakdown Table -->
    <div class="ord-table-card-wrap">
        <h3 class="ord-sec-title">Itemized Order Breakdown</h3>
        <table class="apos-table apos-full-table ord-view-items-table">
            <thead>
                <tr>
                    <th>Dish / Item Name</th>
                    <th>Selected Add-ons / Extras</th>
                    <th class="col-v-unit">Unit Price</th>
                    <th class="col-v-qty">Qty</th>
                    <th class="col-v-total">Line Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($v_items as $vi) : 
                    $u_price = floatval($vi['unitPrice'] ?? $vi['price'] ?? 0);
                    $qty     = intval($vi['qty'] ?? 1);
                    $l_total = $u_price * $qty;
                ?>
                <tr>
                    <td>
                        <strong class="ord-v-dish-name"><?php echo esc_html($vi['name']); ?></strong>
                    </td>
                    <td>
                        <?php if (!empty($vi['extras'])) : ?>
                            <div class="ord-v-addons-list">
                                <?php foreach ($vi['extras'] as $ex) : ?>
                                    <span class="ord-v-addon-pill">+ <?php echo esc_html($ex); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <span class="ord-v-no-addons">None</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="ord-mono"><?php echo $cur . number_format($u_price, 2); ?></span></td>
                    <td><strong class="ord-qty-badge"><?php echo $qty; ?>×</strong></td>
                    <td class="ord-v-line-total"><strong class="ord-mono"><?php echo $cur . number_format($l_total, 2); ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Final Invoice Totals Summary Box -->
    <div class="ord-totals-card">
        <div class="ord-tot-row">
            <span>Subtotal</span>
            <strong class="ord-mono"><?php echo $cur . number_format($view_order->subtotal, 2); ?></strong>
        </div>
        <div class="ord-tot-tax">
            <span>VAT / Tax</span>
            <strong class="ord-mono"><?php echo $cur . number_format($view_order->tax_amount, 2); ?></strong>
        </div>
        <div class="ord-tot-grand">
            <span>Total Payable</span>
            <span class="ord-tot-grand-val ord-mono"><?php echo $cur . number_format($view_order->total_amount, 2); ?></span>
        </div>
    </div>
</div>

<style>
    .ord-view-wrapper { padding: 4px; }
    .ord-view-header {
        display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;
        gap: 16px; padding-bottom: 20px; border-bottom: 1.5px solid #f1f5f9; margin-bottom: 22px;
    }
    .ord-view-meta-date {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 4px;
    }
    .ord-view-title { margin: 0; font-size: 24px; font-weight: 900; color: #0f172a; letter-spacing: -0.3px; }
    .ord-view-controls { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }

    .ord-view-edit-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 15px; font-size: 13px; font-weight: 700; border-radius: 11px; text-decoration: none; }
    .ord-view-kot-btn { display: inline-flex; align-items: center; gap: 7px; padding: 11px 16px; font-size: 13px; font-weight: 700; border-radius: 11px; cursor: pointer; }
    .ord-view-bill-btn { display: inline-flex; align-items: center; gap: 7px; padding: 11px 18px; font-size: 13px; font-weight: 700; border-radius: 11px; cursor: pointer; }

    .ord-view-stats {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px; margin-bottom: 22px;
    }
    .ord-stat-box {
        background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px 20px;
        box-shadow: 0 2px 4px rgba(15, 23, 42, 0.02);
    }
    .ord-stat-orange { border-left: 4px solid #f97316; }
    .ord-stat-blue { border-left: 4px solid #3b82f6; }
    .ord-stat-green { border-left: 4px solid #10b981; }
    .ord-stat-lbl { font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; }
    .ord-stat-val { font-size: 18px; font-weight: 800; color: #0f172a; margin-top: 6px; display: flex; align-items: center; gap: 6px; }
    .ord-stat-val-status { text-transform: uppercase; color: #1d4ed8; }
    .ord-stat-val-total { color: #059669; font-family: 'Courier New', Courier, monospace; }

    .ord-view-note-box {
        background: #fffbeb; border: 1.5px solid #fde68a; color: #92400e;
        padding: 14px 18px; border-radius: 14px; margin-bottom: 22px; font-size: 13.5px;
        display: flex; align-items: center; gap: 10px; box-shadow: 0 2px 6px rgba(245, 158, 11, 0.05);
    }

    .ord-sec-title { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 12px; }
    .ord-view-items-table { box-shadow: none; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
    .col-v-unit { width: 130px; }
    .col-v-qty { width: 90px; }
    .col-v-total { width: 150px; text-align: right; }
    .ord-v-dish-name { font-size: 14.5px; color: #0f172a; }
    
    .ord-v-addons-list { display: flex; flex-direction: column; gap: 3px; margin-top: 3px; }
    .ord-v-addon-pill {
        display: inline-block; background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa;
        padding: 2px 8px; border-radius: 6px; font-size: 11.5px; font-weight: 700; width: fit-content;
    }
    .ord-v-no-addons { color: #94a3b8; font-size: 13px; }
    .ord-qty-badge { background: #0f172a; color: #fff; padding: 2px 8px; border-radius: 6px; font-size: 12px; }
    .ord-v-line-total { text-align: right; }
    .ord-mono { font-family: 'Courier New', Courier, monospace; font-weight: 700; }

    .ord-totals-card {
        max-width: 380px; margin-left: auto; margin-top: 20px; background: #f8fafc;
        border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 18px 22px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
    }
    .ord-tot-row { display: flex; justify-content: space-between; font-size: 14px; color: #64748b; margin-bottom: 8px; }
    .ord-tot-tax {
        display: flex; justify-content: space-between; font-size: 14px; color: #64748b;
        padding-bottom: 12px; border-bottom: 1.5px dashed #cbd5e1;
    }
    .ord-tot-grand {
        display: flex; justify-content: space-between; font-size: 18px; font-weight: 900;
        color: #0f172a; margin-top: 12px; align-items: center;
    }
    .ord-tot-grand-val { color: #f97316; font-size: 21px; font-family: 'Courier New', Courier, monospace; }
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