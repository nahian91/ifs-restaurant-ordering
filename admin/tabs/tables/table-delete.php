<?php
if (!defined('ABSPATH')) exit;

if (isset($_GET['del_table'])) {
    global $wpdb;
    $t_tables = $wpdb->prefix . 'rtpos_tables';
    $wpdb->delete($t_tables, ['id' => intval($_GET['del_table'])]);
    echo '<div class="apos-alert" style="display:flex;align-items:center;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg><span>Table deleted successfully!</span></div>';
}