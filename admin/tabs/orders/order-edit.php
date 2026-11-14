<?php
/**
 * Antalya POS Order Status & Full Edit Handler
 *
 * @package Antalya_POS
 * @since 6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle Single Order Status Quick-Update
 */
function rtpos_handle_order_status_update($orders_table) {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to perform this action.', 'antalya-pos'));
    }

    global $wpdb;
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    if ($order_id <= 0) {
        return;
    }

    check_admin_referer('rtpos_order_status_' . $order_id);

    $svg_check = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
    $allowed   = ['pending', 'preparing', 'served', 'completed'];
    $new_st    = isset($_GET['set_status']) ? sanitize_key($_GET['set_status']) : '';

    if (in_array($new_st, $allowed, true)) {
        $wpdb->update(
            $orders_table,
            ['status' => $new_st],
            ['id' => $order_id],
            ['%s'],
            ['%d']
        );
        echo '<div class="apos-alert ord-alert-flex">' . $svg_check . '<span>' . sprintf(
            /* translators: 1: order ID, 2: status label */
            esc_html__('Order #%1$d status updated to %2$s!', 'antalya-pos'),
            $order_id,
            esc_html(strtoupper($new_st))
        ) . '</span></div>';
    }
}

/**
 * Handle Full Order Edit Form Submission (Quantities + New Items + Removal)
 */
function rtpos_handle_full_order_edit($orders_table, $s) {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to perform this action.', 'antalya-pos'));
    }

    global $wpdb;
    $svg_check = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';

    if (!isset($_POST['rtpos_save_order_edit']) || empty($_POST['edit_order_id'])) {
        return;
    }

    $order_id = intval($_POST['edit_order_id']);
    check_admin_referer('rtpos_edit_order_' . $order_id);

    $allowed_statuses = ['pending', 'preparing', 'served', 'completed'];
    $raw_status       = isset($_POST['edit_status']) ? sanitize_key($_POST['edit_status']) : 'pending';
    $status           = in_array($raw_status, $allowed_statuses, true) ? $raw_status : 'pending';
    $table_no         = isset($_POST['edit_table_no']) ? sanitize_text_field(wp_unslash($_POST['edit_table_no'])) : '1';
    $note             = isset($_POST['edit_note']) ? sanitize_text_field(wp_unslash($_POST['edit_note'])) : '';

    // Fetch existing order securely
    $existing = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$orders_table} WHERE id = %d", $order_id)
    );
    if (!$existing) {
        echo '<div class="apos-alert" style="background:#fef2f2; border-color:#fecaca; color:#b91c1c;">' . esc_html__('Order not found!', 'antalya-pos') . '</div>';
        return;
    }

    $items_raw = json_decode($existing->items, true);
    if (!is_array($items_raw)) {
        $items_raw = [];
    }

    $new_items    = [];
    $subtotal     = 0.0;
    $remove_flags = isset($_POST['remove_item']) && is_array($_POST['remove_item']) ? wp_unslash($_POST['remove_item']) : [];

    // 1. Process Existing Items (Quantities & Removals via Delete Icon Flag)
    if (isset($_POST['item_qty']) && is_array($_POST['item_qty'])) {
        $posted_qtys = wp_unslash($_POST['item_qty']);
        foreach ($posted_qtys as $index => $q) {
            if (isset($remove_flags[$index]) && $remove_flags[$index] === '1') {
                continue; // Skip/Remove this item
            }

            if (isset($items_raw[$index])) {
                $item = $items_raw[$index];
                $qty  = max(1, intval($q));

                $unit = 0.0;
                if (isset($item['unitPrice'])) {
                    $unit = floatval($item['unitPrice']);
                } elseif (isset($item['price'])) {
                    $unit = floatval($item['price']);
                }

                $item['qty'] = $qty;
                $new_items[] = $item;
                $subtotal   += ($unit * $qty);
            }
        }
    }

    // 2. Process Newly Added Items Loaded from Menu Table
    if (isset($_POST['new_item_id']) && is_array($_POST['new_item_id'])) {
        $t_menu        = $wpdb->prefix . 'rtpos_menu';
        $new_item_ids  = wp_unslash($_POST['new_item_id']);
        $new_item_qtys = isset($_POST['new_item_qty']) && is_array($_POST['new_item_qty']) ? wp_unslash($_POST['new_item_qty']) : [];

        foreach ($new_item_ids as $key => $raw_item_id) {
            $item_id = intval($raw_item_id);
            if ($item_id <= 0) {
                continue;
            }

            $new_qty = max(1, intval($new_item_qtys[$key] ?? 1));
            $db_item = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$t_menu} WHERE id = %d", $item_id)
            );

            if ($db_item) {
                $item_name  = $db_item->name ?? __('Dish', 'antalya-pos');
                $unit_price = floatval($db_item->price ?? 0);

                $new_items[] = [
                    'id'        => intval($db_item->id),
                    'name'      => sanitize_text_field($item_name),
                    'unitPrice' => $unit_price,
                    'price'     => $unit_price,
                    'qty'       => $new_qty,
                    'extras'    => [],
                ];
                $subtotal += ($unit_price * $new_qty);
            }
        }
    }

    // Calculate Tax & Grand Total
    $tax_percent = floatval($s['tax_percent'] ?? 0);
    $tax_amount  = ($subtotal * $tax_percent) / 100;
    $total_amt   = $subtotal + $tax_amount;

    $update_data = [
        'table_no'     => $table_no,
        'status'       => $status,
        'note'         => $note,
        'items'        => wp_json_encode($new_items),
        'subtotal'     => number_format($subtotal, 2, '.', ''),
        'tax_amount'   => number_format($tax_amount, 2, '.', ''),
        'total_amount' => number_format($total_amt, 2, '.', ''),
    ];

    $wpdb->update(
        $orders_table,
        $update_data,
        ['id' => $order_id],
        ['%s', '%s', '%s', '%s', '%s', '%s', '%s'],
        ['%d']
    );

    echo '<div class="apos-alert ord-alert-flex">' . $svg_check . '<span>' . sprintf(
        /* translators: %d: order ID */
        esc_html__('Order #%d updated successfully!', 'antalya-pos'),
        $order_id
    ) . '</span></div>';
}

/**
 * Render Full Edit Order Form UI with Add/Remove Item Support
 */
function rtpos_render_order_edit_form($view_order, $s) {
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $cur     = esc_html($s['currency'] ?? '৳');
    $ord_id  = intval($view_order->id);
    $v_items = json_decode($view_order->items, true);
    if (!is_array($v_items)) {
        $v_items = [];
    }

    $t_tables   = $wpdb->prefix . 'rtpos_tables';
    $all_tables = $wpdb->get_results("SELECT table_name FROM {$t_tables} ORDER BY id ASC") ?: [];

    // Fetch active restaurant menu items from rtpos_menu table
    $t_menu     = $wpdb->prefix . 'rtpos_menu';
    $menu_items = $wpdb->get_results("SELECT * FROM {$t_menu} ORDER BY item_order ASC, id DESC") ?: [];

    $cancel_url = esc_url(add_query_arg('view_order', $ord_id, admin_url('admin.php?page=antalya-pos&tab=orders')));

    $svg_save  = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
    $svg_back  = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';
    $svg_plus  = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
    $svg_trash = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';
    ?>
    <div class="apos-card apos-full-card">
        <div class="ord-view-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; flex-wrap:wrap; gap:12px;">
            <div>
                <div class="ord-view-meta" style="font-size:11px; font-weight:800; color:#f97316; letter-spacing:0.8px;"><?php esc_html_e('EDITING ORDER RECORD', 'antalya-pos'); ?></div>
                <h2 class="ord-view-title" style="margin:4px 0 0; font-size:21px; font-weight:850; color:#0f172a;">
                    <?php
                    printf(
                        /* translators: %d: order ID */
                        esc_html__('Modify Order #%d', 'antalya-pos'),
                        $ord_id
                    );
                    ?>
                </h2>
            </div>
            <div>
                <a href="<?php echo $cancel_url; ?>" class="apos-btn-secondary" style="display:inline-flex; align-items:center; gap:6px;">
                    <?php echo $svg_back; ?>
                    <span><?php esc_html_e('Cancel & Return', 'antalya-pos'); ?></span>
                </a>
            </div>
        </div>

        <form method="post" action="<?php echo $cancel_url; ?>">
            <?php wp_nonce_field('rtpos_edit_order_' . $ord_id); ?>
            <input type="hidden" name="edit_order_id" value="<?php echo $ord_id; ?>">

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:20px;">
                <div class="apos-form-group" style="margin:0;">
                    <label class="apos-label"><?php esc_html_e('Table Number', 'antalya-pos'); ?></label>
                    <select name="edit_table_no" class="apos-input" required>
                        <?php if (!empty($all_tables)) : foreach ($all_tables as $tbl) : ?>
                            <option value="<?php echo esc_attr($tbl->table_name); ?>" <?php selected($view_order->table_no, $tbl->table_name); ?>>
                                <?php esc_html_e('Table', 'antalya-pos'); ?> <?php echo esc_html($tbl->table_name); ?>
                            </option>
                        <?php endforeach; else : ?>
                            <option value="<?php echo esc_attr($view_order->table_no); ?>"><?php esc_html_e('Table', 'antalya-pos'); ?> <?php echo esc_html($view_order->table_no); ?></option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="apos-form-group" style="margin:0;">
                    <label class="apos-label"><?php esc_html_e('Order Status', 'antalya-pos'); ?></label>
                    <select name="edit_status" class="apos-input" required>
                        <option value="pending" <?php selected($view_order->status, 'pending'); ?>><?php esc_html_e('Pending', 'antalya-pos'); ?></option>
                        <option value="preparing" <?php selected($view_order->status, 'preparing'); ?>><?php esc_html_e('Preparing', 'antalya-pos'); ?></option>
                        <option value="served" <?php selected($view_order->status, 'served'); ?>><?php esc_html_e('Served', 'antalya-pos'); ?></option>
                        <option value="completed" <?php selected($view_order->status, 'completed'); ?>><?php esc_html_e('Paid / Completed', 'antalya-pos'); ?></option>
                    </select>
                </div>

                <div class="apos-form-group" style="margin:0;">
                    <label class="apos-label"><?php esc_html_e('Kitchen Note', 'antalya-pos'); ?></label>
                    <input type="text" name="edit_note" class="apos-input" value="<?php echo esc_attr($view_order->note); ?>" placeholder="<?php esc_attr_e('e.g. Less spicy', 'antalya-pos'); ?>">
                </div>
            </div>

            <h3 style="font-size:16px; font-weight:800; color:#0f172a; margin:25px 0 10px;"><?php esc_html_e('Existing Order Items', 'antalya-pos'); ?></h3>
            <table class="apos-table apos-full-table" style="box-shadow:none; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden;">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Dish / Item Name', 'antalya-pos'); ?></th>
                        <th><?php esc_html_e('Selected Add-ons', 'antalya-pos'); ?></th>
                        <th width="130"><?php esc_html_e('Unit Price', 'antalya-pos'); ?></th>
                        <th width="110"><?php esc_html_e('Quantity', 'antalya-pos'); ?></th>
                        <th width="130" style="text-align:right;"><?php esc_html_e('Subtotal', 'antalya-pos'); ?></th>
                        <th width="80" style="text-align:center;"><?php esc_html_e('Action', 'antalya-pos'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($v_items as $idx => $vi) :
                        $u_price = floatval($vi['unitPrice'] ?? $vi['price'] ?? 0);
                        $qty     = intval($vi['qty'] ?? 1);
                        $l_tot   = $u_price * $qty;
                    ?>
                    <tr class="rtpos-existing-item-row">
                        <td><strong><?php echo esc_html($vi['name'] ?? ''); ?></strong></td>
                        <td>
                            <?php if (!empty($vi['extras']) && is_array($vi['extras'])) : ?>
                                <span style="color:#f97316; font-size:12px; font-weight:700;">+ <?php echo esc_html(implode(', ', $vi['extras'])); ?></span>
                            <?php else : ?>
                                <span style="color:#94a3b8;"><?php esc_html_e('None', 'antalya-pos'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $cur . esc_html(number_format($u_price, 2)); ?></td>
                        <td>
                            <input type="number" name="item_qty[<?php echo intval($idx); ?>]" value="<?php echo $qty; ?>" min="1" max="99" class="apos-input rtpos-item-qty-input" style="padding:6px 10px; width:70px; font-weight:800; text-align:center;">
                        </td>
                        <td style="text-align:right;"><strong><?php echo $cur . esc_html(number_format($l_tot, 2)); ?></strong></td>
                        <td style="text-align:center;">
                            <input type="hidden" name="remove_item[<?php echo intval($idx); ?>]" class="rtpos-remove-flag" value="0">
                            <button type="button" class="rtpos-delete-item-btn" title="<?php esc_attr_e('Remove this item', 'antalya-pos'); ?>" style="background:#fef2f2; color:#dc2626; border:1px solid #fee2e2; padding:7px; border-radius:8px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; transition:0.2s;">
                                <?php echo $svg_trash; ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Add New Items Section -->
            <h3 style="font-size:16px; font-weight:800; color:#0f172a; margin:30px 0 10px;"><?php esc_html_e('Add New Dishes from Menu', 'antalya-pos'); ?></h3>
            <div id="rtpos-new-items-container" style="display:flex; flex-direction:column; gap:10px; margin-bottom:15px;"></div>

            <button type="button" id="rtpos-add-row-btn" class="apos-btn-secondary" style="display:inline-flex; align-items:center; gap:6px; font-weight:700;">
                <?php echo $svg_plus; ?>
                <span><?php esc_html_e('Add Another Dish', 'antalya-pos'); ?></span>
            </button>

            <div style="margin-top:30px; padding-top:20px; border-top:1.5px solid #e2e8f0; display:flex; gap:12px;">
                <button type="submit" name="rtpos_save_order_edit" class="apos-btn" style="display:inline-flex; align-items:center; gap:7px; padding:12px 24px; font-size:15px;">
                    <?php echo $svg_save; ?>
                    <span><?php esc_html_e('Save Order Changes', 'antalya-pos'); ?></span>
                </button>
                <a href="<?php echo $cancel_url; ?>" class="apos-btn-secondary" style="padding:12px 24px; font-size:15px;"><?php esc_html_e('Cancel', 'antalya-pos'); ?></a>
            </div>
        </form>
    </div>

    <style>
        .rtpos-existing-item-row.row-removed {
            opacity: 0.35;
            background: #f8fafc;
            text-decoration: line-through;
        }
        .rtpos-delete-item-btn:hover {
            background: #fee2e2 !important;
            transform: scale(1.05);
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        let menuItemsOptions = '<option value=""><?php echo esc_js(__('-- Select Dish from Menu --', 'antalya-pos')); ?></option>';
        <?php foreach ($menu_items as $mi) :
            $m_name  = $mi->name ?? __('Item', 'antalya-pos');
            $m_price = floatval($mi->price ?? 0);
        ?>
            menuItemsOptions += '<option value="<?php echo intval($mi->id); ?>"><?php echo esc_js($m_name); ?> (<?php echo esc_js(($s['currency'] ?? '৳') . number_format($m_price, 2)); ?>)</option>';
        <?php endforeach; ?>

        // Toggle Remove Row visually when delete icon is clicked
        $(document).on('click', '.rtpos-delete-item-btn', function() {
            let row = $(this).closest('.rtpos-existing-item-row');
            let flagInput = row.find('.rtpos-remove-flag');

            if (flagInput.val() === '0') {
                flagInput.val('1');
                row.addClass('row-removed');
                row.find('.rtpos-item-qty-input').prop('disabled', true);
                $(this).css('background', '#dc2626').css('color', '#fff');
            } else {
                flagInput.val('0');
                row.removeClass('row-removed');
                row.find('.rtpos-item-qty-input').prop('disabled', false);
                $(this).css('background', '#fef2f2').css('color', '#dc2626');
            }
        });

        // Add new item row dynamically loaded from menu table options
        $('#rtpos-add-row-btn').on('click', function() {
            let rowHtml = `
                <div class="rtpos-new-item-row" style="display:flex; gap:12px; align-items:center; background:#f8fafc; padding:12px; border:1px solid #cbd5e1; border-radius:12px;">
                    <select name="new_item_id[]" class="apos-input" style="flex:2; margin:0;" required>
                        ${menuItemsOptions}
                    </select>
                    <input type="number" name="new_item_qty[]" value="1" min="1" max="99" class="apos-input" style="width:90px; margin:0; text-align:center; font-weight:800;" placeholder="Qty">
                    <button type="button" class="rtpos-remove-new-row" style="background:#fee2e2; color:#dc2626; border:none; padding:8px 12px; border-radius:8px; font-weight:700; cursor:pointer;"><?php echo esc_js(__('Remove', 'antalya-pos')); ?></button>
                </div>
            `;
            $('#rtpos-new-items-container').append(rowHtml);
        });

        $(document).on('click', '.rtpos-remove-new-row', function() {
            $(this).closest('.rtpos-new-item-row').remove();
        });
    });
    </script>
    <?php
}