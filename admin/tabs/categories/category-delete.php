<?php
if (!defined('ABSPATH')) exit;

$svg_check = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>';

if (isset($_GET['del_cat'])) {
    global $wpdb;
    $t_cats = $wpdb->prefix . 'rtpos_categories';
    $wpdb->delete($t_cats, ['id' => intval($_GET['del_cat'])]);
    echo '<div class="apos-alert cat-alert-flex">' . $svg_check . '<span>Category deleted successfully!</span></div>';
}