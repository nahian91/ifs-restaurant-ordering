<?php
/**
 * Plugin Name: Antalya Restaurant Table POS & Kitchen Auto-Print
 * Description: Modular Restaurant POS with Separate Admin Panel, Customer Tablet UI, Call Waiter & Live Order Status.
 * Version: 6.3
 * Author: Custom Dev
 */

if (!defined('ABSPATH')) exit;

define('RTPOS_PATH', plugin_dir_path(__FILE__));
define('RTPOS_URL', plugin_dir_url(__FILE__));

// Include Core, Admin, Frontend & AJAX Files
require_once RTPOS_PATH . 'includes/database.php';
require_once RTPOS_PATH . 'includes/ajax.php';
require_once RTPOS_PATH . 'admin/admin-panel.php';
require_once RTPOS_PATH . 'frontend/customer-tablet.php';

// Database Activation Hook
register_activation_hook(__FILE__, 'rtpos_install_db');

// Ensure jQuery is loaded on frontend securely
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('jquery');
});

// Helper function to get settings with robust defaults
if (!function_exists('rtpos_get_settings')) {
    function rtpos_get_settings() {
        $defaults = [
            'restaurant_name' => 'ANTALYA',
            'phone'           => '',
            'email'           => '',
            'currency'        => '৳',
            'tax_percent'     => 5,
            'menu_page_url'   => home_url('/menu/'),
            'order_prefix'    => 'ORD-',
            'receipt_footer'  => 'Thank you for dining with us!',
        ];
        $saved = get_option('rtpos_settings', []);
        return wp_parse_args($saved, $defaults);
    }
}

/**
 * Auto redirect to Antalya POS dashboard after successful user login
 */
add_filter('login_redirect', 'rtpos_redirect_to_pos_dashboard', 10, 3);
function rtpos_redirect_to_pos_dashboard($redirect_to, $request, $user) {
    if (isset($user->ID) && is_a($user, 'WP_User')) {
        if (user_can($user, 'manage_options') || in_array('administrator', (array) $user->roles, true)) {
            return admin_url('admin.php?page=antalya-pos&tab=dashboard');
        }
    }
    return $redirect_to;
}

/**
 * Custom WordPress Login Page Professional Clean Styling (Without Logo)
 */
add_action('login_enqueue_scripts', function() {
    $s = rtpos_get_settings();
    ?>
    <style type="text/css">
        body.login {
            background: linear-gradient(135deg, #090d16 0%, #0f172a 50%, #1e1b4b 100%) !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        body.login::before {
            content: "";
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.12) 0%, rgba(0,0,0,0) 70%);
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 0;
            pointer-events: none;
        }

        body.login div#login {
            width: 100% !important;
            max-width: 420px !important;
            padding: 40px 20px !important;
            z-index: 1;
        }

        /* Completely remove logo box */
        body.login h1 a {
            display: none !important;
        }

        /* Clean Glassmorphism Login Card */
        body.login form#loginform {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 28px !important;
            padding: 46px 36px 40px !important;
            box-shadow: 0 30px 60px -12px rgba(15, 23, 42, 0.45) !important;
            border: 1px solid rgba(255, 255, 255, 0.8) !important;
            margin-top: 0 !important;
        }

        body.login label {
            font-size: 12.5px !important;
            font-weight: 850 !important;
            color: #334155 !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 7px !important;
            display: block !important;
        }

        body.login input.input {
            width: 100% !important;
            height: 50px !important;
            padding: 12px 16px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 12px !important;
            font-size: 14.5px !important;
            font-weight: 600 !important;
            color: #0f172a !important;
            background: #f8fafc !important;
            box-shadow: none !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        body.login input.input:focus {
            background: #fff !important;
            border-color: #f97316 !important;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.12) !important;
        }

        body.login .submit input#wp-submit {
            width: 100% !important;
            height: 52px !important;
            margin-top: 12px !important;
            border: none !important;
            border-radius: 12px !important;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important;
            color: #fff !important;
            font-size: 15.5px !important;
            font-weight: 900 !important;
            letter-spacing: 0.3px;
            cursor: pointer !important;
            box-shadow: 0 10px 22px rgba(249, 115, 22, 0.35) !important;
            text-shadow: none !important;
            float: none !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        body.login .submit input#wp-submit:hover {
            opacity: 0.95;
            transform: translateY(-1.5px);
            box-shadow: 0 14px 28px rgba(249, 115, 22, 0.45) !important;
        }

        body.login p#nav, body.login p.backtohome {
            text-align: center !important;
            padding: 0 !important;
            margin-top: 22px !important;
        }

        body.login p#nav a, body.login p.backtohome a {
            color: #94a3b8 !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            text-decoration: none !important;
            transition: color 0.2s ease;
        }

        body.login p#nav a:hover, body.login p.backtohome a:hover {
            color: #f97316 !important;
        }

        body.login .forgetmenot {
            margin-top: 12px !important;
            font-weight: 650;
            font-size: 13px;
            color: #64748b;
        }

        body.login .forgetmenot input[type="checkbox"] {
            border-radius: 4px;
            border-color: #cbd5e1;
            accent-color: #f97316;
            width: 16px;
            height: 16px;
            margin-right: 6px;
        }

        .login .message, .login input.error, .login .success {
            border-radius: 12px !important;
            border-left-width: 4px !important;
        }
    </style>
    <?php
});

add_filter('login_headertext', function() {
    $s = rtpos_get_settings();
    return esc_html($s['restaurant_name'] . ' POS Portal');
});

add_filter('login_headerurl', function() {
    return home_url();
});