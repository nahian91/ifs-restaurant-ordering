<?php
if (!defined('ABSPATH')) exit;

$svg_check = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>';

if (isset($_GET['del_extra'])) {
    global $wpdb;
    $t_extras = $wpdb->prefix . 'rtpos_extras';
    $wpdb->delete($t_extras, ['id' => intval($_GET['del_extra'])]);
    echo '<div class="apos-alert ex-alert-flex">' . $svg_check . '<span>Extra deleted successfully!</span></div>';
}