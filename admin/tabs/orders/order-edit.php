<?php
if (!defined('ABSPATH')) exit;

/**
 * Handle Single Order Status Quick-Update
 */
function rtpos_handle_order_status_update($orders_table) {
    global $wpdb;
    $svg_check = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';

    $allowed = array('pending', 'preparing', 'served', 'completed');
    $new_st  = sanitize_key($_GET['set_status']);
    if (in_array($new_st, $allowed)) {
        $wpdb->update($orders_table, array('status' => $new_st), array('id' => intval($_GET['order_id'])));
        echo '<div class="apos-alert ord-alert-flex">' . $svg_check . '<span>Order #' . intval($_GET['order_id']) . ' status updated to ' . strtoupper($new_st) . '!</span></div>';
    }
}

/**
 * Handle Full Order Edit Form Submission
 */
function rtpos_handle_full_order_edit($orders_table, $s) {
    global $wpdb;
    $svg_check = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';

    if (!isset($_POST['rtpos_save_order_edit']) || empty($_POST['edit_order_id'])) {
        return;
    }

    $order_id   = intval($_POST['edit_order_id']);
    $table_no   = sanitize_text_field($_POST['edit_table_no'] ?? '1');
    $status     = sanitize_key($_POST['edit_status'] ?? 'pending');
    $note       = sanitize_text_field($_POST['edit_note'] ?? '');
    
    // Fetch existing order
    $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $orders_table WHERE id = %d", $order_id));
    if (!$existing) {
        echo '<div class="apos-alert" style="background:#fef2f2; color:#b91c1c;">Order not found!</div>';
        return;
    }

    $items_raw = json_decode($existing->items, true);
    if (!is_array($items_raw)) {
        $items_raw = array();
    }

    $new_items = array();
    $subtotal  = 0;

    // Recalculate items based on submitted quantities
    if (isset($_POST['item_qty']) && is_array($_POST['item_qty'])) {
        foreach ($_POST['item_qty'] as $index => $q) {
            if (isset($items_raw[$index])) {
                $item = $items_raw[$index];
                $qty  = max(1, intval($q));
                
                $unit = 0;
                if (isset($item['unitPrice'])) {
                    $unit = floatval($item['unitPrice']);
                } elseif (isset($item['price'])) {
                    $unit = floatval($item['price']);
                }
                
                $item['qty'] = $qty;
                $new_items[] = $item;

                $subtotal += ($unit * $qty);
            }
        }
    } else {
        $new_items = $items_raw;
        $subtotal  = floatval($existing->subtotal);
    }

    // Calculate Tax & Grand Total
    $tax_percent = floatval(isset($s['tax_percent']) ? $s['tax_percent'] : 0);
    $tax_amount  = ($subtotal * $tax_percent) / 100;
    $total_amt   = $subtotal + $tax_amount;

    $update_data = array(
        'table_no'     => $table_no,
        'status'       => $status,
        'note'         => $note,
        'items'        => wp_json_encode($new_items),
        'subtotal'     => number_format($subtotal, 2, '.', ''),
        'tax_amount'   => number_format($tax_amount, 2, '.', ''),
        'total_amount' => number_format($total_amt, 2, '.', '')
    );

    $wpdb->update($orders_table, $update_data, array('id' => $order_id));

    echo '<div class="apos-alert ord-alert-flex">' . $svg_check . '<span>Order #' . $order_id . ' updated successfully!</span></div>';
}

/**
 * Render Full Edit Order Form UI
 */
function rtpos_render_order_edit_form($view_order, $s) {
    global $wpdb;
    $cur        = esc_html(isset($s['currency']) ? $s['currency'] : '৳');
    $v_items    = json_decode($view_order->items, true);
    if (!is_array($v_items)) {
        $v_items = array();
    }

    $t_tables   = $wpdb->prefix . 'rtpos_tables';
    $all_tables = $wpdb->get_results("SELECT table_name FROM $t_tables ORDER BY id ASC") ?: array();

    $svg_save   = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
    $svg_back   = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';
    ?>
    <div class="apos-card apos-full-card">
        <div class="ord-view-header">
            <div>
                <div class="ord-view-meta">EDITING ORDER RECORD</div>
                <h2 class="ord-view-title">Modify Order #<?php echo intval($view_order->id); ?></h2>
            </div>
            <div>
                <a href="?page=antalya-pos&tab=orders&view_order=<?php echo intval($view_order->id); ?>" class="apos-btn-secondary" style="display:inline-flex; align-items:center; gap:6px;">
                    <?php echo $svg_back; ?>
                    <span>Cancel & Return</span>
                </a>
            </div>
        </div>

        <form method="post" action="?page=antalya-pos&tab=orders&view_order=<?php echo intval($view_order->id); ?>">
            <input type="hidden" name="edit_order_id" value="<?php echo intval($view_order->id); ?>">

            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px; margin-bottom:20px;">
                <div class="apos-form-group" style="margin:0;">
                    <label class="apos-label">Table Number</label>
                    <select name="edit_table_no" class="apos-input" required>
                        <?php if (!empty($all_tables)) : foreach ($all_tables as $tbl) : ?>
                            <option value="<?php echo esc_attr($tbl->table_name); ?>" <?php selected($view_order->table_no, $tbl->table_name); ?>>
                                Table <?php echo esc_html($tbl->table_name); ?>
                            </option>
                        <?php endforeach; else : ?>
                            <option value="<?php echo esc_attr($view_order->table_no); ?>">Table <?php echo esc_html($view_order->table_no); ?></option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="apos-form-group" style="margin:0;">
                    <label class="apos-label">Order Status</label>
                    <select name="edit_status" class="apos-input" required>
                        <option value="pending" <?php selected($view_order->status, 'pending'); ?>>Pending</option>
                        <option value="preparing" <?php selected($view_order->status, 'preparing'); ?>>Preparing</option>
                        <option value="served" <?php selected($view_order->status, 'served'); ?>>Served</option>
                        <option value="completed" <?php selected($view_order->status, 'completed'); ?>>Paid / Completed</option>
                    </select>
                </div>

                <div class="apos-form-group" style="margin:0;">
                    <label class="apos-label">Kitchen Note</label>
                    <input type="text" name="edit_note" class="apos-input" value="<?php echo esc_attr($view_order->note); ?>" placeholder="e.g. Less spicy">
                </div>
            </div>

            <h3 style="font-size:16px; font-weight:800; color:#0f172a; margin:20px 0 10px;">Edit Item Quantities</h3>
            <table class="apos-table apos-full-table" style="box-shadow:none; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden;">
                <thead>
                    <tr>
                        <th>Dish / Item Name</th>
                        <th>Selected Add-ons</th>
                        <th width="140">Unit Price</th>
                        <th width="120">Quantity</th>
                        <th width="140" style="text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($v_items as $idx => $vi) : 
                        $u_price = floatval($vi['unitPrice'] ?? $vi['price'] ?? 0);
                        $qty     = intval($vi['qty'] ?? 1);
                        $l_tot   = $u_price * $qty;
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html($vi['name']); ?></strong></td>
                        <td>
                            <?php if (!empty($vi['extras'])) : ?>
                                <span style="color:#f97316; font-size:12px; font-weight:700;">+ <?php echo esc_html(implode(', ', $vi['extras'])); ?></span>
                            <?php else : ?>
                                <span style="color:#94a3b8;">None</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $cur . number_format($u_price, 2); ?></td>
                        <td>
                            <input type="number" name="item_qty[<?php echo $idx; ?>]" value="<?php echo $qty; ?>" min="1" max="99" class="apos-input" style="padding:6px 10px; width:75px; font-weight:800; text-align:center;">
                        </td>
                        <td style="text-align:right;"><strong><?php echo $cur . number_format($l_tot, 2); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top:22px; padding-top:16px; border-top:1px solid #f1f5f9; display:flex; gap:10px;">
                <button type="submit" name="rtpos_save_order_edit" class="apos-btn" style="display:inline-flex; align-items:center; gap:7px; padding:11px 20px;">
                    <?php echo $svg_save; ?>
                    <span>Save Order Changes</span>
                </button>
                <a href="?page=antalya-pos&tab=orders&view_order=<?php echo intval($view_order->id); ?>" class="apos-btn-secondary" style="padding:11px 20px;">Cancel</a>
            </div>
        </form>
    </div>
    <?php
}