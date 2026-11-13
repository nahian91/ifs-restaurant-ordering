<?php
/**
 * Plugin Name: Antalya Restaurant Table POS & Kitchen Auto-Print
 * Description: Modular Restaurant POS with Separate Admin Panel, Customer Tablet UI, Call Waiter & Live Order Status.
 * Version: 6.1
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

// Ensure jQuery is loaded on frontend
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('jquery');
});

// Helper function to get settings with robust defaults
if (!function_exists('rtpos_get_settings')) {
    function rtpos_get_settings() {
        $defaults = [
            'restaurant_name' => 'Antalya Restaurant',
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
 * Custom WordPress Login Page Styling & Branding for Antalya POS
 */
add_action('login_enqueue_scripts', function() {
    $s = rtpos_get_settings();
    ?>
    <style type="text/css">
        body.login {
            background: radial-gradient(circle at 50% -20%, #ffedd5 0%, #0f172a 65%) !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        body.login div#login {
            width: 100% !important;
            max-width: 440px !important;
            padding: 40px 20px !important;
        }

        body.login h1 a {
            background-image: none !important;
            text-indent: 0 !important;
            width: 76px !important;
            height: 76px !important;
            border-radius: 22px !important;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important;
            color: #fff !important;
            font-size: 36px !important;
            font-weight: 900 !important;
            line-height: 76px !important;
            text-align: center !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 auto 20px !important;
            box-shadow: 0 10px 25px rgba(249, 115, 22, 0.45);
        }
        
        body.login h1 a::after {
            content: "<?php echo esc_html(substr($s['restaurant_name'], 0, 1)); ?>";
        }

        body.login form#loginform {
            background: rgba(255, 255, 255, 0.96) !important;
            backdrop-filter: blur(16px);
            border-radius: 32px !important;
            padding: 42px 38px !important;
            box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.35) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.9) !important;
            margin-top: 15px !important;
        }

        body.login label {
            font-size: 13px !important;
            font-weight: 800 !important;
            color: #334155 !important;
            margin-bottom: 8px !important;
            display: block !important;
        }

        body.login input.input {
            width: 100% !important;
            height: 52px !important;
            padding: 12px 16px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 14px !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            color: #0f172a !important;
            background: #f8fafc !important;
            box-shadow: none !important;
            transition: all 0.2s ease;
        }

        body.login input.input:focus {
            background: #fff !important;
            border-color: #f97316 !important;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.15) !important;
        }

        body.login .submit input#wp-submit {
            width: 100% !important;
            height: 56px !important;
            margin-top: 10px !important;
            border: none !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important;
            color: #fff !important;
            font-size: 16px !important;
            font-weight: 900 !important;
            cursor: pointer !important;
            box-shadow: 0 10px 25px rgba(249, 115, 22, 0.4) !important;
            text-shadow: none !important;
            float: none !important;
            transition: transform 0.15s ease, opacity 0.15s ease;
        }

        body.login .submit input#wp-submit:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }

        body.login p#nav, body.login p.backtohome {
            text-align: center !important;
            padding: 0 !important;
            margin-top: 20px !important;
        }

        body.login p#nav a, body.login p.backtohome a {
            color: #94a3b8 !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            text-decoration: none !important;
            transition: color 0.2s;
        }

        body.login p#nav a:hover, body.login p.backtohome a:hover {
            color: #f97316 !important;
        }

        body.login .forgetmenot {
            margin-top: 10px !important;
            font-weight: 600;
            color: #64748b;
        }
    </style>
    <?php
});

add_filter('login_headertext', function() {
    $s = rtpos_get_settings();
    return esc_html($s['restaurant_name']);
});

add_filter('login_headerurl', function() {
    return home_url();
});