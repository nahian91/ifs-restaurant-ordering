<?php
if (!defined('ABSPATH')) exit;

define('RTPOS_DB_VERSION', '6.2');

/**
 * Install & Upgrade POS Database Tables
 */
function rtpos_install_db() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();

    $t_menu   = $wpdb->prefix . 'rtpos_menu';
    $t_cats   = $wpdb->prefix . 'rtpos_categories';
    $t_extras = $wpdb->prefix . 'rtpos_extras';
    $t_orders = $wpdb->prefix . 'rtpos_orders';
    $t_staff  = $wpdb->prefix . 'rtpos_staff';
    $t_tables = $wpdb->prefix . 'rtpos_tables';
    $t_calls  = $wpdb->prefix . 'rtpos_waiter_calls';

    $sql1 = "CREATE TABLE $t_menu (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(150) NOT NULL,
        category varchar(100) NOT NULL,
        price decimal(10,2) NOT NULL DEFAULT 0.00,
        image_url text,
        extras text,
        PRIMARY KEY (id),
        KEY category (category)
    ) $charset;";

    $sql2 = "CREATE TABLE $t_cats (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        category_order int(5) NOT NULL DEFAULT 0,
        image text,
        PRIMARY KEY (id)
    ) $charset;";

    $sql3 = "CREATE TABLE $t_extras (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        price decimal(10,2) NOT NULL DEFAULT 0.00,
        image text,
        PRIMARY KEY (id)
    ) $charset;";

    $sql4 = "CREATE TABLE $t_orders (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        table_no varchar(30) NOT NULL,
        items longtext NOT NULL,
        subtotal decimal(10,2) NOT NULL DEFAULT 0.00,
        tax_amount decimal(10,2) NOT NULL DEFAULT 0.00,
        total_amount decimal(10,2) NOT NULL DEFAULT 0.00,
        note text,
        status varchar(20) NOT NULL DEFAULT 'pending',
        is_printed tinyint(1) NOT NULL DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY table_no (table_no),
        KEY status (status),
        KEY created_at (created_at)
    ) $charset;";

    $sql5 = "CREATE TABLE $t_staff (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        role varchar(50) NOT NULL,
        email varchar(150) NOT NULL,
        password varchar(255) NOT NULL,
        phone varchar(30),
        status varchar(20) NOT NULL DEFAULT 'Active',
        permissions longtext,
        PRIMARY KEY (id),
        KEY email (email)
    ) $charset;";

    $sql6 = "CREATE TABLE $t_tables (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        table_name varchar(50) NOT NULL,
        seats int(5) NOT NULL DEFAULT 4,
        table_login varchar(150) DEFAULT '',
        table_password varchar(150) DEFAULT '',
        PRIMARY KEY (id)
    ) $charset;";

    $sql7 = "CREATE TABLE $t_calls (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        table_no varchar(30) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'active',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY status (status)
    ) $charset;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql1);
    dbDelta($sql2);
    dbDelta($sql3);
    dbDelta($sql4);
    dbDelta($sql5);
    dbDelta($sql6);
    dbDelta($sql7);

    // Default Plugin Settings (Uses /menu/ slug)
    if (!get_option('rtpos_settings')) {
        update_option('rtpos_settings', [
            'restaurant_name' => 'ANTALYA',
            'currency'        => '৳',
            'tax_percent'     => 0,
            'menu_page_url'   => home_url('/menu/'),
            'receipt_footer'  => 'Thank you for dining at Antalya!'
        ]);
    }

    // Seed Default Categories if Empty
    if ((int) $wpdb->get_var("SELECT COUNT(*) FROM $t_cats") === 0) {
        $c_order = 1;
        foreach (['Starters', 'Main Course', 'Drinks'] as $cat) {
            $wpdb->insert($t_cats, ['name' => $cat, 'category_order' => $c_order++]);
        }
    }

    // Seed Default Tables (01 to 04) if Empty
    if ((int) $wpdb->get_var("SELECT COUNT(*) FROM $t_tables") === 0) {
        foreach (['01', '02', '03', '04'] as $tbl) {
            $wpdb->insert($t_tables, [
                'table_name'     => $tbl,
                'seats'          => 4,
                'table_login'    => 'table_' . $tbl,
                'table_password' => 'pos' . $tbl . '123'
            ]);
        }
    }

    update_option('rtpos_db_version', RTPOS_DB_VERSION);
}

/**
 * Auto-run DB check if plugin version updates without reactivation
 */
add_action('plugins_loaded', function() {
    if (get_option('rtpos_db_version') !== RTPOS_DB_VERSION) {
        rtpos_install_db();
    }
});

/**
 * Retrieve POS Settings with Safe Defaults (/menu/ slug)
 */
function rtpos_get_settings() {
    return wp_parse_args(get_option('rtpos_settings', []), [
        'restaurant_name' => 'ANTALYA',
        'currency'        => '৳',
        'tax_percent'     => 0,
        'menu_page_url'   => home_url('/menu/'),
        'receipt_footer'  => 'Thank you for dining at Antalya!'
    ]);
}