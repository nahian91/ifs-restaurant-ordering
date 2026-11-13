<?php
if (!defined('ABSPATH')) exit;

$svg_check = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>';

if (isset($_GET['del_staff'])) {
    global $wpdb;
    $t_staff = $wpdb->prefix . 'rtpos_staff';
    $wpdb->delete($t_staff, ['id' => intval($_GET['del_staff'])]);
    echo '<div class="apos-alert stf-alert-flex">' . $svg_check . '<span>Staff member removed successfully!</span></div>';
}