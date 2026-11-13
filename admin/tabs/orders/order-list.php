<?php
if (!defined('ABSPATH')) exit;

$svg_search   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';
$svg_eye      = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
$svg_edit     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_flame    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>';
$svg_utensils = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>';
$svg_check    = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
$svg_print    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>';
$svg_receipt  = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1-2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>';
$svg_trash    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';
?>

<div class="ord-toolbar">
    <div class="apos-pills ord-pills-no-margin">
        <a href="?page=antalya-pos&tab=orders&status_filter=all<?php echo !empty($search_query) ? '&s_ord=' . urlencode($search_query) : ''; ?>" class="apos-pill <?php echo $filter === 'all' ? 'active' : ''; ?>">
            All <span class="pill-count"><?php echo $counts['all']; ?></span>
        </a>
        <a href="?page=antalya-pos&tab=orders&status_filter=pending<?php echo !empty($search_query) ? '&s_ord=' . urlencode($search_query) : ''; ?>" class="apos-pill <?php echo $filter === 'pending' ? 'active' : ''; ?>">
            <span class="st-dot dot-pending"></span>Pending <span class="pill-count"><?php echo $counts['pending']; ?></span>
        </a>
        <a href="?page=antalya-pos&tab=orders&status_filter=preparing<?php echo !empty($search_query) ? '&s_ord=' . urlencode($search_query) : ''; ?>" class="apos-pill <?php echo $filter === 'preparing' ? 'active' : ''; ?>">
            <span class="st-dot dot-preparing"></span>Preparing <span class="pill-count"><?php echo $counts['preparing']; ?></span>
        </a>
        <a href="?page=antalya-pos&tab=orders&status_filter=served<?php echo !empty($search_query) ? '&s_ord=' . urlencode($search_query) : ''; ?>" class="apos-pill <?php echo $filter === 'served' ? 'active' : ''; ?>">
            <span class="st-dot dot-served"></span>Served <span class="pill-count"><?php echo $counts['served']; ?></span>
        </a>
        <a href="?page=antalya-pos&tab=orders&status_filter=completed<?php echo !empty($search_query) ? '&s_ord=' . urlencode($search_query) : ''; ?>" class="apos-pill <?php echo $filter === 'completed' ? 'active' : ''; ?>">
            <span class="st-dot dot-completed"></span>Paid <span class="pill-count"><?php echo $counts['completed']; ?></span>
        </a>
    </div>

    <form method="get" class="ord-search-box">
        <input type="hidden" name="page" value="antalya-pos">
        <input type="hidden" name="tab" value="orders">
        <input type="hidden" name="status_filter" value="<?php echo esc_attr($filter); ?>">
        <?php echo $svg_search; ?>
        <input type="text" name="s_ord" id="ord-live-search" class="ord-search-input" value="<?php echo esc_attr($search_query); ?>" placeholder="Search Order # or Table...">
    </form>
</div>

<table class="apos-table apos-full-table" id="apos-orders-table">
    <thead>
        <tr>
            <th class="col-ord-id">Order ID</th>
            <th class="col-ord-tbl">Table</th>
            <th class="col-ord-status">Status</th>
            <th class="col-ord-actions">Actions & Print</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($orders)) : ?>
            <tr><td colspan="4" class="ord-empty-cell">No orders found matching your criteria.</td></tr>
        <?php endif; ?>

        <?php foreach ($orders as $ord) : 
            $st_class = 'ord-st-' . sanitize_html_class($ord->status);
        ?>
        <tr class="ord-row">
            <td>
                <a href="?page=antalya-pos&tab=orders&view_order=<?php echo intval($ord->id); ?>" class="ord-id-link">
                    #<?php echo intval($ord->id); ?>
                </a><br>
                <small class="ord-time-sm"><?php echo date('h:i A', strtotime($ord->created_at)); ?></small>
            </td>
            <td>
                <span class="ord-table-badge">
                    Table <?php echo esc_html($ord->table_no); ?>
                </span>
            </td>
            <td>
                <div class="ord-select-wrapper">
                    <select class="ord-status-select <?php echo esc_attr($st_class); ?> js-ord-status-select" data-redirect="?page=antalya-pos&tab=orders&status_filter=<?php echo esc_attr($filter); ?>&paged=<?php echo $paged; ?><?php echo !empty($search_query) ? '&s_ord=' . urlencode($search_query) : ''; ?>&order_id=<?php echo intval($ord->id); ?>&set_status=">
                        <option value="pending" <?php selected($ord->status, 'pending'); ?>>Pending</option>
                        <option value="preparing" <?php selected($ord->status, 'preparing'); ?>>Preparing</option>
                        <option value="served" <?php selected($ord->status, 'served'); ?>>Served</option>
                        <option value="completed" <?php selected($ord->status, 'completed'); ?>>Paid / Done</option>
                    </select>
                </div>
            </td>
            <td>
                <div class="ord-action-group">
                    <a href="?page=antalya-pos&tab=orders&view_order=<?php echo intval($ord->id); ?>" class="ord-act-btn act-view" title="View Order Details">
                        <?php echo $svg_eye; ?>
                        <span>View</span>
                    </a>

                    <a href="?page=antalya-pos&tab=orders&view_order=<?php echo intval($ord->id); ?>" class="ord-act-btn act-edit" title="Edit Order">
                        <?php echo $svg_edit; ?>
                        <span>Edit</span>
                    </a>

                    <!-- 1-Click Next Stage Button -->
                    <?php if ($ord->status === 'pending') : ?>
                        <a href="?page=antalya-pos&tab=orders&status_filter=<?php echo esc_attr($filter); ?>&paged=<?php echo $paged; ?><?php echo !empty($search_query) ? '&s_ord=' . urlencode($search_query) : ''; ?>&order_id=<?php echo intval($ord->id); ?>&set_status=preparing" class="ord-act-btn step-cook">
                            <?php echo $svg_flame; ?>
                            <span>Cook</span>
                        </a>
                    <?php elseif ($ord->status === 'preparing') : ?>
                        <a href="?page=antalya-pos&tab=orders&status_filter=<?php echo esc_attr($filter); ?>&paged=<?php echo $paged; ?><?php echo !empty($search_query) ? '&s_ord=' . urlencode($search_query) : ''; ?>&order_id=<?php echo intval($ord->id); ?>&set_status=served" class="ord-act-btn step-serve">
                            <?php echo $svg_utensils; ?>
                            <span>Serve</span>
                        </a>
                    <?php elseif ($ord->status === 'served') : ?>
                        <a href="?page=antalya-pos&tab=orders&status_filter=<?php echo esc_attr($filter); ?>&paged=<?php echo $paged; ?><?php echo !empty($search_query) ? '&s_ord=' . urlencode($search_query) : ''; ?>&order_id=<?php echo intval($ord->id); ?>&set_status=completed" class="ord-act-btn step-pay">
                            <?php echo $svg_check; ?>
                            <span>Pay</span>
                        </a>
                    <?php endif; ?>

                    <!-- Print Buttons -->
                    <button type="button" class="apos-btn-dark rtpos-print-kot ord-print-kot-btn" data-order='<?php echo esc_attr(wp_json_encode($ord)); ?>' title="Print Kitchen Ticket">
                        <?php echo $svg_print; ?>
                        <span>KOT</span>
                    </button>
                    <button type="button" class="apos-btn-secondary rtpos-print-bill ord-print-bill-btn" data-order='<?php echo esc_attr(wp_json_encode($ord)); ?>' title="Print Customer Bill">
                        <?php echo $svg_receipt; ?>
                        <span>Bill</span>
                    </button>
                    
                    <a href="?page=antalya-pos&tab=orders&status_filter=<?php echo esc_attr($filter); ?>&paged=<?php echo $paged; ?><?php echo !empty($search_query) ? '&s_ord=' . urlencode($search_query) : ''; ?>&delete_order=<?php echo intval($ord->id); ?>" class="ord-act-btn act-del js-delete-order" title="Delete">
                        <?php echo $svg_trash; ?>
                    </a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Pagination Bar -->
<?php if ($total_pages > 1) : 
    $base_pagination_url = admin_url('admin.php?page=antalya-pos&tab=orders&status_filter=' . $filter . (!empty($search_query) ? '&s_ord=' . urlencode($search_query) : ''));
?>
    <div class="ord-pagination">
        <div class="ord-pag-info">
            Showing Page <strong><?php echo $paged; ?></strong> of <strong><?php echo $total_pages; ?></strong> (Total <?php echo $total_items; ?> orders)
        </div>
        <div class="ord-pag-links">
            <a href="<?php echo esc_url($base_pagination_url . '&paged=1'); ?>" class="ord-pag-btn <?php echo ($paged <= 1) ? 'ord-pag-disabled' : ''; ?>">First</a>
            <a href="<?php echo esc_url($base_pagination_url . '&paged=' . max(1, $paged - 1)); ?>" class="ord-pag-btn <?php echo ($paged <= 1) ? 'ord-pag-disabled' : ''; ?>">« Prev</a>
            
            <?php 
            for ($p = max(1, $paged - 2); $p <= min($total_pages, $paged + 2); $p++) : 
            ?>
                <a href="<?php echo esc_url($base_pagination_url . '&paged=' . $p); ?>" class="ord-pag-btn <?php echo ($p === $paged) ? 'current' : ''; ?>"><?php echo $p; ?></a>
            <?php endfor; ?>

            <a href="<?php echo esc_url($base_pagination_url . '&paged=' . min($total_pages, $paged + 1)); ?>" class="ord-pag-btn <?php echo ($paged >= $total_pages) ? 'ord-pag-disabled' : ''; ?>">Next »</a>
            <a href="<?php echo esc_url($base_pagination_url . '&paged=' . $total_pages); ?>" class="ord-pag-btn <?php echo ($paged >= $total_pages) ? 'ord-pag-disabled' : ''; ?>">Last</a>
        </div>
    </div>
<?php endif; ?>

<style>
    .col-ord-actions { width: 360px; }
    .act-edit { background: #f1f5f9; color: #334155; }
    .act-edit:hover { background: #e2e8f0; color: #0f172a; }

    .ord-search-input {
        width: 100%; padding: 11px 16px 11px 40px; border: 1.5px solid #cbd5e1; border-radius: 12px;
        font-size: 14px; font-weight: 500; background: #f8fafc; color: #0f172a; box-sizing: border-box;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 2px 5px rgba(15, 23, 42, 0.02);
    }
    .ord-search-input:hover { border-color: #94a3b8; background: #fff; }
    .ord-search-input:focus {
        outline: none; background: #fff; border-color: #f97316;
        box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.15), 0 2px 6px rgba(15, 23, 42, 0.04);
    }

    .ord-select-wrapper { position: relative; display: block; width: 100%; }
    .ord-status-select {
        appearance: none; -webkit-appearance: none; -moz-appearance: none;
        width: 100%; display: block; padding: 10px 36px 10px 14px; border-radius: 11px;
        font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 0.3px;
        border: 1.5px solid transparent; cursor: pointer; box-sizing: border-box;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 12px center; background-size: 14px;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
    }
    .ord-status-select:hover { filter: brightness(0.97); transform: translateY(-1px); box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08); }
    .ord-status-select:focus { outline: none; box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.15); }

    .ord-st-pending { background-color: #fef3c7; color: #b45309; border-color: #fde68a; }
    .ord-st-preparing { background-color: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .ord-st-served { background-color: #faf5ff; color: #7e22ce; border-color: #e9d5ff; }
    .ord-st-completed { background-color: #ecfdf5; color: #047857; border-color: #a7f3d0; }
</style>

<script>
jQuery(document).ready(function($) {
    let restName    = <?php echo json_encode($s['restaurant_name'] ?? 'Restaurant'); ?>;
    let restPhone   = <?php echo json_encode($s['phone'] ?? ''); ?>;
    let restEmail   = <?php echo json_encode($s['email'] ?? ''); ?>;
    let currency    = <?php echo json_encode($s['currency'] ?? '৳'); ?>;
    let footerTxt   = <?php echo json_encode($s['receipt_footer'] ?? 'Thank you!'); ?>;
    let ordPrefix   = <?php echo json_encode($s['order_prefix'] ?? 'ORD-'); ?>;

    // Status Select Dropdown Change
    $(document).on('change', '.js-ord-status-select', function() {
        let baseUrl = $(this).data('redirect');
        if (baseUrl) {
            window.location.href = baseUrl + encodeURIComponent($(this).val());
        }
    });

    // Confirm Delete Order
    $(document).on('click', '.js-delete-order', function(e) {
        if (!confirm('Delete this order permanently?')) {
            e.preventDefault();
        }
    });

    // 1. Kitchen Order Ticket (KOT) Print
    function printKitchenKOT(order) {
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
        $('#rtpos-print-area').html(receipt);
        window.print();
    }

    // 2. Customer Receipt / Tax Bill Print
    function printCustomerBill(order) {
        let items = typeof order.items === 'string' ? JSON.parse(order.items) : order.items;
        let rows = '';
        items.forEach(i => {
            let unitPr = parseFloat(i.unitPrice || i.price || 0);
            let qty    = parseInt(i.qty || 1);
            let line   = (unitPr * qty).toFixed(2);
            
            rows += `
                <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
                    <span>${qty}x ${i.name}</span>
                    <span>${currency}${line}</span>
                </div>`;
            
            if (i.extras && i.extras.length) {
                i.extras.forEach(ex => {
                    rows += `
                        <div style="display:flex; justify-content:space-between; font-size:10.5px; color:#475569; padding-left:8px; margin-bottom:2px;">
                            <span>  - ${ex}</span>
                        </div>`;
                });
            }
        });

        let contactStr = '';
        if (restPhone && restEmail) contactStr = 'Ph: ' + restPhone + ' | ' + restEmail;
        else if (restPhone) contactStr = 'Ph: ' + restPhone;
        else if (restEmail) contactStr = restEmail;

        let totalPayable = parseFloat(order.total_amount).toFixed(2);
        let tendered     = (parseFloat(order.total_amount) + 150).toFixed(2);
        let changeReturn = (tendered - order.total_amount).toFixed(2);

        let receipt = `
            <div style="font-family:'Courier New',Courier,monospace; font-size:11px; color:#111; width:280px; margin:0 auto; line-height:1.35;">
                <div style="text-align:center;">
                    <h3 style="margin:0 0 2px; font-size:15px; font-weight:900; text-transform:uppercase; letter-spacing:0.5px;">${restName}</h3>
                    <div style="font-size:9.5px; color:#334155;">${contactStr}</div>
                    <div style="font-size:9.5px; font-weight:800; color:#475569; margin-bottom:4px;">*** TAX INVOICE ***</div>
                    <div style="font-size:10px; color:#334155;">Table: <strong>${order.table_no}</strong> | Ord: <strong>${ordPrefix}${order.id}</strong></div>
                    <div style="font-size:9px; color:#555;">${order.created_at}</div>
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
                    <span>${currency}${parseFloat(order.subtotal).toFixed(2)}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:10.5px; color:#334155; margin-bottom:3px;">
                    <span>VAT/Tax:</span>
                    <span>${currency}${parseFloat(order.tax_amount).toFixed(2)}</span>
                </div>

                <div style="display:flex; justify-content:space-between; font-size:13.5px; font-weight:900; margin-top:6px; border-top:1.5px solid #000; padding-top:5px;">
                    <span>TOTAL PAYABLE:</span>
                    <span>${currency}${totalPayable}</span>
                </div>

                <div style="display:flex; justify-content:space-between; font-size:10.5px; color:#334155; border-top:1px dotted #64748b; padding-top:3px; margin-top:3px;">
                    <span>Method:</span>
                    <strong>CASH</strong>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:10.5px; color:#334155;">
                    <span>Tendered:</span>
                    <span>${currency}${tendered}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:10.5px; font-weight:700; color:#059669;">
                    <span>CHANGE:</span>
                    <span>${currency}${changeReturn}</span>
                </div>

                <hr style="border:none; border-top:1px dashed #64748b; margin:8px 0;">

                <div style="text-align:center; font-size:10px; color:#334155; margin-top:8px; font-style:italic;">
                    ${footerTxt}
                </div>
            </div>
        `;
        
        if ($('#rtpos-print-area').length === 0) {
            $('body').append('<div id="rtpos-print-area" style="display:none;"></div>');
        }
        $('#rtpos-print-area').html(receipt);
        window.print();
    }

    $('.rtpos-print-kot').on('click', function() { printKitchenKOT($(this).data('order')); });
    $('.rtpos-print-bill').on('click', function() { printCustomerBill($(this).data('order')); });
});
</script>