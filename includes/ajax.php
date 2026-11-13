<?php
if (!defined('ABSPATH')) exit;

// 1. Place Order (Customer Tablet - Supports Multiple Rounds per Table)
add_action('wp_ajax_rtpos_place_order', 'rtpos_ajax_place_order');
add_action('wp_ajax_nopriv_rtpos_place_order', 'rtpos_ajax_place_order');
function rtpos_ajax_place_order() {
    global $wpdb;

    $table_no = sanitize_text_field(wp_unslash($_POST['table_no'] ?? ''));
    $raw_items = isset($_POST['items']) ? wp_unslash($_POST['items']) : [];

    if (empty($table_no) || empty($raw_items) || !is_array($raw_items)) {
        wp_send_json_error('Invalid order data.');
    }

    $clean_items = [];
    foreach ($raw_items as $it) {
        $extras = [];
        if (!empty($it['extras']) && is_array($it['extras'])) {
            foreach ($it['extras'] as $ex) {
                $extras[] = sanitize_text_field($ex);
            }
        }
        $clean_items[] = [
            'id'        => intval($it['id'] ?? 0),
            'name'      => sanitize_text_field($it['name'] ?? 'Item'),
            'basePrice' => floatval($it['basePrice'] ?? $it['unitPrice'] ?? 0),
            'unitPrice' => floatval($it['unitPrice'] ?? $it['price'] ?? 0),
            'extras'    => $extras,
            'qty'       => max(1, intval($it['qty'] ?? 1)),
        ];
    }

    $wpdb->insert($wpdb->prefix . 'rtpos_orders', [
        'table_no'     => $table_no,
        'items'        => wp_json_encode($clean_items),
        'subtotal'     => floatval($_POST['subtotal'] ?? 0),
        'tax_amount'   => floatval($_POST['tax'] ?? 0),
        'total_amount' => floatval($_POST['total'] ?? 0),
        'note'         => sanitize_textarea_field(wp_unslash($_POST['note'] ?? '')),
        'status'       => 'pending',
        'is_printed'   => 0
    ]);

    wp_send_json_success(['id' => $wpdb->insert_id]);
}

// 2. Fetch Unprinted Order (Kitchen Auto-Print - Prints Only New Round Items)
add_action('wp_ajax_rtpos_fetch_unprinted', 'rtpos_ajax_fetch_unprinted');
function rtpos_ajax_fetch_unprinted() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    global $wpdb;
    $table = $wpdb->prefix . 'rtpos_orders';
    $order = $wpdb->get_row("SELECT * FROM $table WHERE is_printed = 0 ORDER BY id ASC LIMIT 1");

    if ($order) {
        $wpdb->update($table, ['is_printed' => 1], ['id' => $order->id]);
        wp_send_json_success($order);
    }
    wp_send_json_error();
}

// 3. Call Waiter / Request Bill (Customer Tablet)
add_action('wp_ajax_rtpos_call_waiter', 'rtpos_ajax_call_waiter');
add_action('wp_ajax_nopriv_rtpos_call_waiter', 'rtpos_ajax_call_waiter');
function rtpos_ajax_call_waiter() {
    global $wpdb;
    $table_no = sanitize_text_field(wp_unslash($_POST['table_no'] ?? ''));
    if (empty($table_no)) {
        wp_send_json_error('Missing table number');
    }

    $t_calls = $wpdb->prefix . 'rtpos_waiter_calls';
    $exists  = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $t_calls WHERE table_no = %s AND status = 'active'",
        $table_no
    ));

    if (!$exists) {
        $wpdb->insert($t_calls, ['table_no' => $table_no, 'status' => 'active']);
    }
    wp_send_json_success();
}

// 4. Check Active Waiter Calls (Admin)
add_action('wp_ajax_rtpos_check_waiter_calls', 'rtpos_ajax_check_waiter_calls');
function rtpos_ajax_check_waiter_calls() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    global $wpdb;
    $t_calls = $wpdb->prefix . 'rtpos_waiter_calls';
    $calls   = $wpdb->get_results("SELECT * FROM $t_calls WHERE status = 'active' ORDER BY id DESC") ?: [];
    wp_send_json_success($calls);
}

// 5. Dismiss Waiter Call (Admin)
add_action('wp_ajax_rtpos_dismiss_waiter_call', 'rtpos_ajax_dismiss_waiter_call');
function rtpos_ajax_dismiss_waiter_call() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    global $wpdb;
    $wpdb->update(
        $wpdb->prefix . 'rtpos_waiter_calls',
        ['status' => 'resolved'],
        ['id' => intval($_POST['id'] ?? 0)]
    );
    wp_send_json_success();
}

// 6. Live Order Status Tracking (Customer Tablet - Returns Full Itemized Price Breakdown)
add_action('wp_ajax_rtpos_get_table_orders', 'rtpos_ajax_get_table_orders');
add_action('wp_ajax_nopriv_rtpos_get_table_orders', 'rtpos_ajax_get_table_orders');
function rtpos_ajax_get_table_orders() {
    global $wpdb;
    $table_no = sanitize_text_field(wp_unslash($_POST['table_no'] ?? ''));
    if (empty($table_no)) {
        wp_send_json_error('Missing table number');
    }

    $t_orders = $wpdb->prefix . 'rtpos_orders';
    $t_menu   = $wpdb->prefix . 'rtpos_menu';
    $t_extras = $wpdb->prefix . 'rtpos_extras';

    $orders = $wpdb->get_results($wpdb->prepare(
        "SELECT id, items, subtotal, tax_amount, total_amount, status, created_at
         FROM $t_orders
         WHERE table_no = %s AND status != 'completed'
         ORDER BY id ASC",
        $table_no
    )) ?: [];

    // Extra prices lookup map (for fallback if older orders lack unitPrice)
    $extra_prices = [];
    $all_ex = $wpdb->get_results("SELECT name, price FROM $t_extras") ?: [];
    foreach ($all_ex as $ex) {
        $extra_prices[$ex->name] = floatval($ex->price);
    }

    $formatted = [];
    foreach ($orders as $o) {
        $items        = json_decode($o->items, true) ?: [];
        $count        = 0;
        $summary      = [];
        $items_detail = [];

        if (is_array($items)) {
            foreach ($items as $i) {
                $qty   = max(1, intval($i['qty'] ?? 1));
                $u_prc = floatval($i['unitPrice'] ?? $i['price'] ?? 0);

                // Fallback price lookup from menu table if missing
                if ($u_prc <= 0 && !empty($i['id'])) {
                    $u_prc = floatval($wpdb->get_var($wpdb->prepare("SELECT price FROM $t_menu WHERE id = %d", intval($i['id']))));
                }

                $ex_details = [];
                $ex_sum     = 0;
                if (!empty($i['extras']) && is_array($i['extras'])) {
                    foreach ($i['extras'] as $ex_name) {
                        $ex_p = $extra_prices[$ex_name] ?? 0;
                        $ex_sum += $ex_p;
                        $ex_details[] = [
                            'name'  => $ex_name,
                            'price' => number_format($ex_p, 2, '.', '')
                        ];
                    }
                }

                $base_prc = isset($i['basePrice']) ? floatval($i['basePrice']) : max(0, $u_prc - $ex_sum);
                $l_tot    = $u_prc * $qty;
                $count   += $qty;

                $ex_str    = !empty($i['extras']) ? ' (+' . implode(', ', $i['extras']) . ')' : '';
                $summary[] = $qty . '× ' . ($i['name'] ?? 'Item') . $ex_str;

                $items_detail[] = [
                    'name'           => $i['name'] ?? 'Item',
                    'qty'            => $qty,
                    'base_price'     => number_format($base_prc, 2, '.', ''),
                    'unit_price'     => number_format($u_prc, 2, '.', ''),
                    'line_total'     => number_format($l_tot, 2, '.', ''),
                    'extras'         => !empty($i['extras']) ? $i['extras'] : [],
                    'extras_details' => $ex_details
                ];
            }
        }

        $formatted[] = [
            'id'           => intval($o->id),
            'item_count'   => $count,
            'item_summary' => implode(', ', $summary),
            'items_detail' => $items_detail,
            'subtotal'     => number_format(floatval($o->subtotal), 2, '.', ''),
            'tax_amount'   => number_format(floatval($o->tax_amount), 2, '.', ''),
            'total_amount' => number_format(floatval($o->total_amount), 2, '.', ''),
            'status'       => $o->status,
            'time'         => date('h:i A', strtotime($o->created_at))
        ];
    }

    wp_send_json_success($formatted);
}

// 7. Settle & Merge All Active Orders for a Table (Admin - Combined Final Bill)
add_action('wp_ajax_rtpos_settle_table_orders', 'rtpos_ajax_settle_table_orders');
function rtpos_ajax_settle_table_orders() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    global $wpdb;
    $table_no = sanitize_text_field(wp_unslash($_POST['table_no'] ?? ''));
    if (empty($table_no)) {
        wp_send_json_error('Table number is required');
    }

    $t_orders = $wpdb->prefix . 'rtpos_orders';
    $active   = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $t_orders WHERE table_no = %s AND status != 'completed' ORDER BY id ASC",
        $table_no
    )) ?: [];

    if (empty($active)) {
        wp_send_json_error('No active orders found for this table.');
    }

    $merged_items = [];
    $sub_total    = 0.0;
    $tax_total    = 0.0;
    $grand_total  = 0.0;
    $order_ids    = [];

    foreach ($active as $ord) {
        $order_ids[]  = intval($ord->id);
        $sub_total   += floatval($ord->subtotal);
        $tax_total   += floatval($ord->tax_amount);
        $grand_total += floatval($ord->total_amount);

        $items = json_decode($ord->items, true) ?: [];
        foreach ($items as $it) {
            $merged_items[] = $it;
        }
    }

    // Mark all active orders for this table as completed (Paid)
    $wpdb->query($wpdb->prepare(
        "UPDATE $t_orders SET status = 'completed' WHERE table_no = %s AND status != 'completed'",
        $table_no
    ));

    wp_send_json_success([
        'table_no'     => $table_no,
        'order_ids'    => $order_ids,
        'items'        => $merged_items,
        'subtotal'     => number_format($sub_total, 2, '.', ''),
        'tax_amount'   => number_format($tax_total, 2, '.', ''),
        'total_amount' => number_format($grand_total, 2, '.', ''),
        'settled_at'   => current_time('Y-m-d H:i:s')
    ]);
}