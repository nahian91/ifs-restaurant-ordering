<?php
if (!defined('ABSPATH')) exit;

function rtpos_handle_order_delete($orders_table) {
    global $wpdb;
    $svg_check = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';

    $wpdb->delete($orders_table, ['id' => intval($_GET['delete_order'])]);
    echo '<div class="apos-alert ord-alert-flex">' . $svg_check . '<span>Order deleted!</span></div>';
}