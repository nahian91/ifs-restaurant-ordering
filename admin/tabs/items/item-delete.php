<?php
if (!defined('ABSPATH')) exit;

$svg_check = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>';

if (isset($_GET['del_item'])) {
    global $wpdb;
    $t_menu = $wpdb->prefix . 'rtpos_menu';
    $wpdb->delete($t_menu, ['id' => intval($_GET['del_item'])]);
    echo '<div class="apos-alert itm-alert-flex">' . $svg_check . '<span>Dish deleted successfully!</span></div>';
}