<?php
if (!defined('ABSPATH')) exit;

/**
 * Helper: Get Authenticated Table Row from Secure Cookie
 */
function rtpos_get_authenticated_table() {
    global $wpdb;
    $t_tables = $wpdb->prefix . 'rtpos_tables';

    if (empty($_COOKIE['rtpos_table_auth'])) {
        return null;
    }

    $raw   = sanitize_text_field(wp_unslash($_COOKIE['rtpos_table_auth']));
    $parts = explode('|', $raw);
    if (count($parts) !== 2) {
        return null;
    }

    $table_id = intval($parts[0]);
    $token    = $parts[1];

    $tbl = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t_tables WHERE id = %d", $table_id));
    if (!$tbl || empty($tbl->table_login)) {
        return null;
    }

    $expected_token = hash_hmac('sha256', $tbl->id . '|' . $tbl->table_login . '|' . $tbl->table_password, wp_salt('auth'));
    if (!hash_equals($expected_token, $token)) {
        return null;
    }

    return $tbl;
}

/**
 * Handle Table Login & Full-Screen Tablet Mode
 */
add_action('template_redirect', 'rtpos_fullscreen_tablet_mode');
function rtpos_fullscreen_tablet_mode() {
    global $post, $wpdb;
    if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'restaurant_menu')) {
        return;
    }

    $t_tables = $wpdb->prefix . 'rtpos_tables';
    $page_url = get_permalink($post->ID);
    $auth_err = '';

    // Handle Table Login Submission
    if (isset($_POST['rtpos_table_login_submit'])) {
        $login_input = sanitize_text_field(wp_unslash($_POST['rtpos_login'] ?? ''));
        $pass_input  = sanitize_text_field(wp_unslash($_POST['rtpos_pass'] ?? ''));

        $matched_tbl = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $t_tables WHERE table_login = %s LIMIT 1",
            $login_input
        ));

        if ($matched_tbl && !empty($matched_tbl->table_password) && hash_equals((string) $matched_tbl->table_password, $pass_input)) {
            $token      = hash_hmac('sha256', $matched_tbl->id . '|' . $matched_tbl->table_login . '|' . $matched_tbl->table_password, wp_salt('auth'));
            $cookie_val = $matched_tbl->id . '|' . $token;
            setcookie('rtpos_table_auth', $cookie_val, time() + (30 * DAY_IN_SECONDS), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
            $_COOKIE['rtpos_table_auth'] = $cookie_val;

            wp_safe_redirect($page_url);
            exit;
        } else {
            $auth_err = 'Incorrect table username or password. Please check and try again.';
        }
    }

    $s = rtpos_get_settings();
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="mobile-web-app-capable" content="yes">
        <title><?php echo esc_html($s['restaurant_name']); ?> — Table Menu</title>
        <?php wp_head(); ?>
        <style>
            html, body {
                margin: 0 !important; padding: 0 !important;
                background: #f1f5f9 !important;
                overflow-x: clip !important;
                -webkit-tap-highlight-color: transparent;
                touch-action: manipulation;
                scroll-behavior: smooth;
            }
            #wpadminbar { display: none !important; }
        </style>
    </head>
    <body>
        <?php
        $GLOBALS['rtpos_login_error'] = $auth_err;
        echo do_shortcode('[restaurant_menu]');
        ?>
        <?php wp_footer(); ?>
    </body>
    </html>
    <?php
    exit;
}

/**
 * Customer Tablet Shortcode: [restaurant_menu]
 */
add_shortcode('restaurant_menu', 'rtpos_customer_tablet_ui');
function rtpos_customer_tablet_ui() {
    global $wpdb;
    $s         = rtpos_get_settings();
    $cur       = esc_html($s['currency']);
    $tax_rate  = floatval($s['tax_percent']);
    $auth_tbl  = rtpos_get_authenticated_table();
    $login_err = $GLOBALS['rtpos_login_error'] ?? '';

    // Reusable SVG Icons
    $svg_lock      = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';
    $svg_user      = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
    $svg_key       = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7.5" cy="15.5" r="5.5"/><path d="m21 2-9.6 9.6"/><path d="m15.5 7.5 3 3L22 7l-3-3"/></svg>';
    $svg_eye       = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
    $svg_bell      = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>';
    $svg_receipt   = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>';
    $svg_table     = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>';
    $svg_search    = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';
    $svg_close     = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
    $svg_utensils  = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>';
    $svg_bag       = '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>';
    $svg_sparkles  = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/></svg>';
    $svg_check     = '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
    $svg_send      = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>';
    $svg_plus      = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
    $svg_radio     = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="2"/><path d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.14 0a10 10 0 0 1 0-14.14"/></svg>';
    $svg_sidebar   = '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/></svg>';

    // =========================================================================
    // STEP 1: TABLE LOGIN GATE
    // =========================================================================
    if (!$auth_tbl) {
        $prefill_login = '';
        if (!empty($_POST['rtpos_login'])) {
            $prefill_login = sanitize_text_field(wp_unslash($_POST['rtpos_login']));
        } elseif (!empty($_GET['table'])) {
            $q_tbl = $wpdb->get_row($wpdb->prepare(
                "SELECT table_login FROM {$wpdb->prefix}rtpos_tables WHERE table_name = %s LIMIT 1",
                sanitize_text_field(wp_unslash($_GET['table']))
            ));
            if ($q_tbl && !empty($q_tbl->table_login)) {
                $prefill_login = $q_tbl->table_login;
            }
        }

        ob_start(); ?>
        <style id="rtpos-login-styles">
            .rt-login-screen {
                min-height: 100vh; width: 100vw; display: flex; align-items: center; justify-content: center;
                background: #f1f5f9; padding: 24px; box-sizing: border-box;
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            }
            .rt-login-card {
                background: #ffffff; width: 100%; max-width: 440px; border-radius: 28px;
                padding: 42px 38px; box-shadow: 0 20px 50px -12px rgba(15, 23, 42, 0.12);
                box-sizing: border-box; border: 1.5px solid #e2e8f0;
            }
            .rt-login-logo {
                width: 70px; height: 70px; border-radius: 20px; margin: 0 auto 20px;
                background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
                color: #fff; font-size: 32px; font-weight: 900;
                display: flex; align-items: center; justify-content: center;
                box-shadow: 0 8px 20px rgba(249, 115, 22, 0.35);
            }
            .rt-login-title { margin: 0; text-align: center; font-size: 26px; font-weight: 900; color: #0f172a; }
            .rt-login-sub { margin: 8px 0 28px; text-align: center; font-size: 14px; color: #64748b; font-weight: 500; line-height: 1.5; }
            .rt-login-err { background: #fef2f2; border: 1.5px solid #fecaca; color: #dc2626; padding: 13px 18px; border-radius: 14px; font-size: 13.5px; font-weight: 700; margin-bottom: 20px; text-align: center; }
            .rt-l-group { margin-bottom: 20px; }
            .rt-l-label { display: block; font-size: 13px; font-weight: 800; color: #334155; margin-bottom: 8px; }
            .rt-l-wrap { position: relative; display: flex; align-items: center; }
            .rt-l-ico { position: absolute; left: 16px; display: flex; align-items: center; pointer-events: none; }
            .rt-l-input {
                width: 100%; height: 54px; padding: 14px 48px 14px 46px; border: 1.5px solid #cbd5e1;
                border-radius: 16px; font-size: 16px; font-weight: 600; color: #0f172a; background: #f8fafc; box-sizing: border-box;
            }
            .rt-l-input:focus { outline: none; background: #fff; border-color: #f97316; box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.15); }
            .rt-l-eye-btn { position: absolute; right: 12px; background: none; border: none; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; cursor: pointer; border-radius: 10px; }
            .rt-l-submit {
                width: 100%; height: 56px; margin-top: 12px; border: none; border-radius: 16px;
                background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; font-size: 16px; font-weight: 900; cursor: pointer;
                display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 8px 20px rgba(249, 115, 22, 0.35);
            }
        </style>

        <div class="rt-login-screen">
            <div class="rt-login-card">
                <div class="rt-login-logo"><?php echo esc_html(substr($s['restaurant_name'], 0, 1)); ?></div>
                <h1 class="rt-login-title"><?php echo esc_html($s['restaurant_name']); ?></h1>
                <p class="rt-login-sub">Table Setup<br>Sign in once with your table username and password.</p>

                <?php if (!empty($login_err)) : ?>
                    <div class="rt-login-err"><?php echo esc_html($login_err); ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="rt-l-group">
                        <label class="rt-l-label">Table Username</label>
                        <div class="rt-l-wrap">
                            <span class="rt-l-ico"><?php echo $svg_user; ?></span>
                            <input type="text" name="rtpos_login" class="rt-l-input" placeholder="e.g. table_01" value="<?php echo esc_attr($prefill_login); ?>" required autocomplete="username">
                        </div>
                    </div>

                    <div class="rt-l-group">
                        <label class="rt-l-label">Password</label>
                        <div class="rt-l-wrap">
                            <span class="rt-l-ico"><?php echo $svg_key; ?></span>
                            <input type="password" name="rtpos_pass" id="rt-login-pass-inp" class="rt-l-input" placeholder="Enter password" required autocomplete="current-password">
                            <button type="button" id="rt-toggle-pass" class="rt-l-eye-btn" title="Show or Hide Password"><?php echo $svg_eye; ?></button>
                        </div>
                    </div>

                    <button type="submit" name="rtpos_table_login_submit" class="rt-l-submit">
                        <?php echo $svg_lock; ?>
                        <span>Open Table Menu</span>
                    </button>
                </form>
            </div>
        </div>
        <script id="rtpos-login-scripts">
            document.addEventListener('DOMContentLoaded', function() {
                var toggleBtn = document.getElementById('rt-toggle-pass');
                if (toggleBtn) {
                    toggleBtn.addEventListener('click', function() {
                        var inp = document.getElementById('rt-login-pass-inp');
                        inp.type = (inp.type === 'password') ? 'text' : 'password';
                    });
                }
            });
        </script>
        <?php
        return ob_get_clean();
    }

    // =========================================================================
    // STEP 2: AUTHENTICATED TABLET MENU UI (ZERO INLINE CSS/JS, FULL NAMES)
    // =========================================================================
    $table_no    = $auth_tbl->table_name;
    $table_seats = intval($auth_tbl->seats);

    $items      = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}rtpos_menu ORDER BY category ASC, id DESC") ?: [];
    $all_extras = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}rtpos_extras ORDER BY name ASC", OBJECT_K) ?: [];

    $cat_counts = [];
    foreach ($items as $it) {
        $cat_counts[$it->category] = ($cat_counts[$it->category] ?? 0) + 1;
    }

    $js_all_extras = [];
    foreach ($all_extras as $ex) {
        $js_all_extras[] = [
            'id'    => intval($ex->id),
            'name'  => $ex->name,
            'price' => floatval($ex->price)
        ];
    }

    ob_start(); ?>
    <style id="rtpos-tablet-styles">
        .rt-wrapper {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            width: 100%; max-width: 1680px; margin: 0 auto;
            padding: 18px 22px 90px; color: #0f172a; box-sizing: border-box;
            touch-action: manipulation;
        }

        /* Toast Notification */
        .rt-toast-container {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 999999;
            display: flex; flex-direction: column; gap: 8px; pointer-events: none; width: max-content; max-width: 92vw;
        }
        .rt-toast {
            background: #0f172a; color: #fff; padding: 13px 26px; border-radius: 50px;
            font-size: 14px; font-weight: 800; box-shadow: 0 15px 35px rgba(15, 23, 42, 0.25);
            display: flex; align-items: center; gap: 9px; animation: rtSlideDown 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid rgba(255,255,255,0.12);
        }
        .rt-toast.warn { background: #dc2626; }
        .rt-toast.success { background: #059669; }
        @keyframes rtSlideDown {
            from { opacity: 0; transform: translateY(-16px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Top Clean Header */
        .rt-header {
            background: #ffffff; color: #0f172a; padding: 16px 22px; border-radius: 22px;
            margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 14px; box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
            border: 1.5px solid #e2e8f0; box-sizing: border-box;
        }
        .rt-brand-wrap { display: flex; align-items: center; gap: 14px; }
        
        /* Left Sidebar Toggle Button */
        .rt-sidebar-toggle-btn {
            width: 44px; height: 44px; border-radius: 13px;
            background: #f8fafc; border: 1.5px solid #cbd5e1; color: #334155;
            display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.18s ease; flex-shrink: 0;
        }
        .rt-sidebar-toggle-btn:hover { background: #fff7ed; border-color: #f97316; color: #f97316; }
        .rt-sidebar-toggle-btn:active { transform: scale(0.94); }

        .rt-brand-icon {
            width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; font-weight: 900; color: #fff; box-shadow: 0 6px 18px rgba(249, 115, 22, 0.35);
        }
        .rt-header h2 { margin: 0; font-size: 20px; font-weight: 900; letter-spacing: -0.3px; color: #0f172a; }
        .rt-header-sub { font-size: 12.5px; color: #64748b; margin-top: 2px; font-weight: 600; }
        .rt-header-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

        /* Top Right Live Cart & Order Status Pill Button */
        .rt-tray-toggle-btn {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; border: none;
            padding: 0 18px; height: 46px; border-radius: 30px; font-weight: 800; font-size: 13.5px; cursor: pointer;
            display: inline-flex; align-items: center; gap: 9px; box-shadow: 0 6px 16px rgba(15, 23, 42, 0.25);
            transition: all 0.18s ease;
        }
        .rt-tray-toggle-btn:hover { background: #f97316; }
        .rt-tray-toggle-btn:active { transform: scale(0.95); }
        .rt-tray-toggle-btn.is-active { background: #f97316; box-shadow: 0 6px 16px rgba(249, 115, 22, 0.35); }
        .rt-btn-badge {
            background: #f97316; color: #fff; font-size: 11.5px; font-weight: 900;
            padding: 2px 8px; border-radius: 12px; transition: all 0.2s ease;
        }
        .rt-btn-total-pill {
            font-family: 'SFMono-Regular', Consolas, monospace;
            background: rgba(255, 255, 255, 0.16); color: #fff;
            padding: 3px 9px; border-radius: 10px; font-size: 13px; font-weight: 900;
        }
        .rt-tray-toggle-btn.is-active .rt-btn-badge, .rt-tray-toggle-btn:hover .rt-btn-badge {
            background: #fff; color: #f97316;
        }

        .rt-waiter-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #fff; border: none;
            padding: 0 18px; height: 46px; border-radius: 30px; font-weight: 800; font-size: 13px; cursor: pointer;
            display: inline-flex; align-items: center; gap: 7px; box-shadow: 0 6px 16px rgba(239, 68, 68, 0.3);
            transition: all 0.18s ease;
        }
        .rt-waiter-btn:active { transform: scale(0.95); }
        .rt-waiter-btn:disabled {
            background: #94a3b8; color: #f8fafc; box-shadow: none; cursor: not-allowed; opacity: 0.85;
        }

        .rt-bill-btn {
            background: #eff6ff; color: #1d4ed8; border: 1.5px solid #bfdbfe;
            padding: 0 16px; height: 46px; border-radius: 30px; font-weight: 800; font-size: 13px; cursor: pointer;
            display: inline-flex; align-items: center; gap: 7px; transition: all 0.18s ease;
        }
        .rt-bill-btn:hover:not(:disabled) { background: #dbeafe; border-color: #93c5fd; }
        .rt-bill-btn:active:not(:disabled) { transform: scale(0.95); }
        .rt-bill-btn:disabled {
            background: #f1f5f9; color: #94a3b8; border-color: #e2e8f0; cursor: not-allowed; opacity: 0.7;
        }

        .rt-table-pill {
            background: #fff7ed; border: 1.5px solid #fed7aa; color: #ea580c;
            padding: 0 16px; height: 44px; border-radius: 30px; font-weight: 900; font-size: 13.5px;
            display: inline-flex; align-items: center; gap: 6px;
        }

        /* Main Split App Layout */
        .rt-app { display: flex; gap: 18px; align-items: flex-start; width: 100%; box-sizing: border-box; position: relative; }

        /* Left Sidebar Category Menu - Full Text Visible */
        .rt-sidebar-cats {
            width: 195px; min-width: 195px; flex-shrink: 0; background: #fff; border: 1.5px solid #e2e8f0;
            border-radius: 20px; padding: 10px; display: flex; flex-direction: column; gap: 6px;
            position: -webkit-sticky; position: sticky; top: 14px; box-sizing: border-box;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .rt-sidebar-cats.sidebar-hidden {
            display: none !important;
        }
        .rt-scat-title {
            font-size: 11px; font-weight: 800; text-transform: uppercase; color: #94a3b8;
            letter-spacing: 0.6px; padding: 4px 10px 2px;
        }
        .rt-scat-btn {
            width: 100%; background: #f8fafc; border: 1.5px solid #e2e8f0; padding: 11px 13px; border-radius: 12px;
            font-weight: 800; font-size: 13px; color: #475569; cursor: pointer; text-align: left;
            display: flex; justify-content: space-between; align-items: center; gap: 6px; transition: all 0.18s ease;
            box-sizing: border-box; white-space: normal !important; height: auto !important;
        }
        .rt-scat-btn span:first-child {
            white-space: normal !important; overflow: visible !important; text-overflow: unset !important; word-break: break-word;
        }
        .rt-scat-label-wrap { display: flex; align-items: center; gap: 7px; min-width: 0; }
        .rt-scat-btn:hover { background: #fff7ed; border-color: #f97316; color: #f97316; }
        .rt-scat-btn.active {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; border-color: transparent;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }
        .rt-scat-btn .scnt {
            background: #e2e8f0; color: #475569; font-size: 11px; padding: 2px 7px; border-radius: 8px; font-weight: 800; flex-shrink: 0;
        }
        .rt-scat-btn.active .scnt { background: rgba(255,255,255,0.25); color: #fff; }

        .rt-middle { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 16px; box-sizing: border-box; }

        /* Search Bar & Quick Tip */
        .rt-toolbar {
            background: #fff; padding: 12px 16px; border-radius: 18px; border: 1.5px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.03); display: flex; align-items: center; gap: 14px; flex-wrap: wrap; box-sizing: border-box;
        }
        .rt-search-wrap { position: relative; flex: 1; min-width: 220px; box-sizing: border-box; }
        .rt-search {
            width: 100%; height: 46px; padding: 11px 44px 11px 42px; border: 1.5px solid #cbd5e1; border-radius: 13px;
            font-size: 14.5px; font-weight: 600; color: #0f172a; background: #f8fafc; box-sizing: border-box;
            transition: all 0.2s ease;
        }
        .rt-search:focus { outline: none; background: #fff; border-color: #f97316; box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.15); }
        .rt-search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); display: flex; align-items: center; pointer-events: none; }
        .rt-search-clear {
            display: none; position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: #e2e8f0; border: none; width: 28px; height: 28px; border-radius: 50%;
            color: #475569; cursor: pointer; align-items: center; justify-content: center;
        }
        .rt-search-clear:hover { background: #cbd5e1; color: #0f172a; }
        .rt-quick-guide {
            font-size: 12.5px; font-weight: 700; color: #64748b; background: #f8fafc;
            padding: 8px 14px; border-radius: 11px; border: 1px solid #e2e8f0;
        }

        /* Empty & No Results Box */
        .rt-empty-state-box {
            text-align: center; padding: 60px 20px; color: #64748b;
            background: #fff; border-radius: 22px; border: 1.5px solid #e2e8f0;
        }
        .rt-empty-state-box h3 { margin: 0 0 6px; color: #0f172a; font-size: 20px; }
        .rt-empty-state-box p { margin: 0; font-size: 14.5px; }
        .rt-hidden { display: none; }

        /* Food Menu Grid - Full Item Name Visible (No Truncation) */
        .rt-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; width: 100%; box-sizing: border-box; }
        .rt-card {
            background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 20px; padding: 14px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.03); display: flex; flex-direction: column;
            justify-content: space-between; box-sizing: border-box; min-width: 0;
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.22s ease, border-color 0.22s ease;
        }
        .rt-card:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(15, 23, 42, 0.08); border-color: #cbd5e1; }
        .rt-card.in-cart { border-color: #f97316; box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.2); }
        .rt-img-box { position: relative; overflow: hidden; border-radius: 14px; height: 150px; background: #f8fafc; width: 100%; box-sizing: border-box; }
        .rt-card img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease; }
        .rt-card:hover img { transform: scale(1.05); }
        .rt-cat-tag {
            position: absolute; top: 9px; left: 9px; background: rgba(15, 23, 42, 0.8); color: #fff;
            font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; backdrop-filter: blur(4px);
        }
        .rt-in-tray-badge {
            display: none; position: absolute; top: 9px; right: 9px;
            background: #10b981; color: #fff; font-size: 11px; font-weight: 900;
            padding: 4px 10px; border-radius: 20px; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.35);
        }
        .rt-card h4 {
            margin: 11px 0 6px; font-size: 15.5px; font-weight: 900; color: #0f172a; line-height: 1.35;
            white-space: normal !important; overflow: visible !important; text-overflow: unset !important; word-break: break-word;
        }
        .rt-card .price { color: #f97316; font-weight: 900; font-size: 18px; margin-bottom: 10px; }

        /* Touch-Friendly Extras Box on Card - Full Visible Name */
        .rt-extras-box {
            background: #f8fafc; padding: 8px; border-radius: 13px; margin-bottom: 12px;
            border: 1.5px solid #e2e8f0; display: flex; flex-direction: column; gap: 7px; box-sizing: border-box;
        }
        .rt-extras-hint {
            font-size: 11px; font-weight: 700; color: #64748b; padding: 0 4px;
        }
        .rt-extra-item {
            display: flex; align-items: center; justify-content: space-between; gap: 8px;
            cursor: pointer; padding: 8px 11px; border-radius: 10px; background: #fff;
            border: 1.5px solid #e2e8f0; font-size: 12.5px; font-weight: 700; color: #334155;
            transition: all 0.16s ease; user-select: none; box-sizing: border-box;
            height: auto !important; min-height: 26px;
        }
        .rt-extra-item:hover { border-color: #f97316; color: #0f172a; }
        .rt-extra-item:active { transform: scale(0.98); }
        .rt-extra-item:has(input:checked) { background: #fff7ed; border-color: #f97316; color: #c2410c; }
        .rt-extra-left {
            display: flex; align-items: center; gap: 8px; min-width: 0; overflow: visible !important; text-overflow: unset !important; white-space: normal !important; word-break: break-word;
        }
        .rt-extra-item input { accent-color: #f97316; width: 16px; height: 16px; margin: 0; cursor: pointer; flex-shrink: 0; }
        .rt-extra-price-tag { color: #f97316; }

        /* Card Action Row: Quantity Selector + Add Button */
        .rt-card-actions {
            display: flex; align-items: center; gap: 8px; width: 100%; box-sizing: border-box;
        }
        .rt-card-qty-ctrl {
            display: inline-flex; align-items: center; background: #f1f5f9;
            border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 3px; height: 46px; box-sizing: border-box; flex-shrink: 0;
        }
        .rt-cq-btn {
            width: 32px; height: 36px; border: none; background: #fff; border-radius: 9px;
            font-weight: 900; font-size: 16px; color: #0f172a; cursor: pointer;
            display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        }
        .rt-cq-btn:active { background: #e2e8f0; transform: scale(0.93); }
        .rt-cq-val {
            min-width: 24px; text-align: center; font-weight: 900; font-size: 14.5px; color: #0f172a;
        }

        .rt-add {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; border: none; padding: 0 12px; height: 46px; flex: 1; border-radius: 13px;
            font-weight: 900; font-size: 14px; cursor: pointer; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.2); box-sizing: border-box;
            transition: transform 0.14s cubic-bezier(0.16, 1, 0.3, 1); white-space: nowrap;
        }
        .rt-add:hover { background: #f97316; }
        .rt-add:active { transform: scale(0.96); }

        /* ==============================================================
           RIGHT PANEL: COMBINED LIVE STATUS & RECEIPT BOX
           ============================================================== */
        .rt-right-col {
            display: none;
            flex: 1;
            min-width: 0;
            flex-direction: column; gap: 18px;
            box-sizing: border-box;
            animation: rtFadeInPanel 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .rt-right-col.panel-open {
            display: flex;
        }
        @keyframes rtFadeInPanel {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .rt-panel-top-bar {
            display: flex; justify-content: space-between; align-items: center;
            background: #fff; border: 1.5px solid #e2e8f0; border-radius: 18px;
            padding: 14px 20px; box-shadow: 0 4px 12px rgba(15,23,42,0.03);
        }
        .rt-panel-top-title { font-size: 16px; color: #0f172a; display: flex; align-items: center; gap: 8px; }
        .rt-back-menu-btn {
            background: #f1f5f9; border: 1px solid #cbd5e1; padding: 7px 15px; border-radius: 20px;
            display: inline-flex; align-items: center; gap: 6px; cursor: pointer; color: #334155; font-weight: 800; font-size: 13px;
        }
        .rt-back-menu-btn:hover { background: #e2e8f0; color: #0f172a; }

        .rt-box { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 22px; padding: 22px; box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04); box-sizing: border-box; }

        /* Super App-Style Live Order Status Card */
        .rt-live-tracker {
            display: none; flex-shrink: 0; box-sizing: border-box;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1.5px solid #334155; border-radius: 22px; padding: 22px; color: #fff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18);
        }
        .rt-live-tracker-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .rt-live-tracker-title { margin: 0; font-size: 15.5px; color: #fff; font-weight: 900; display: flex; align-items: center; gap: 8px; }
        .rt-running-total-pill {
            background: rgba(59, 130, 246, 0.25); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.5);
            padding: 5px 14px; border-radius: 20px; font-size: 12.5px; font-weight: 900;
        }
        .rt-order-track-card {
            background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 16px;
            padding: 16px; margin-bottom: 12px; box-sizing: border-box;
        }
        .rt-order-track-card:last-child { margin-bottom: 0; }
        .rt-track-top {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px dashed rgba(255,255,255,0.1);
        }
        .rt-track-badge {
            display: inline-flex; align-items: center; gap: 6px; background: rgba(249, 115, 22, 0.2); color: #fdba74;
            padding: 3px 9px; border-radius: 8px; font-size: 12.5px; font-weight: 900; border: 1px solid rgba(249, 115, 22, 0.4);
        }
        .rt-track-time { color: #94a3b8; font-weight: 600; }
        .rt-track-amt { font-size: 14px; font-weight: 900; color: #fff; font-family: monospace; }
        .rt-track-breakdown {
            background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 11px;
            padding: 8px 10px; margin-bottom: 12px; box-sizing: border-box; font-size: 12px; color: #cbd5e1;
        }
        .rt-tb-row { display: flex; flex-direction: column; gap: 2px; padding: 5px 0; border-bottom: 1px dashed rgba(255,255,255,0.08); }
        .rt-tb-row:last-of-type { border-bottom: none; }
        .rt-tb-item-head { display: flex; justify-content: space-between; align-items: flex-start; width: 100%; gap: 8px; }
        .rt-tb-item-name { color: #fff; }
        .rt-tb-item-sub { color: #94a3b8; font-size: 11px; font-family: monospace; }
        .rt-tb-item-total { font-family: monospace; white-space: nowrap; color: #fff; }
        .rt-tb-ex-line { display: flex; justify-content: space-between; font-size: 11px; color: #fdba74; font-weight: 700; padding-left: 10px; }
        .rt-tb-ex-price { font-family: monospace; }
        .rt-tb-totals { margin-top: 6px; padding-top: 6px; border-top: 1px dashed rgba(255,255,255,0.15); display: flex; flex-direction: column; gap: 2px; font-size: 11.5px; color: #94a3b8; font-weight: 700; }
        .rt-tb-sum-line { display: flex; justify-content: space-between; }
        .rt-tb-sum-val { font-family: monospace; color: #cbd5e1; }
        .rt-tb-grand-line { display: flex; justify-content: space-between; color: #fff; font-size: 12.5px; font-weight: 900; padding-top: 3px; border-top: 1px dashed #475569; }
        .rt-tb-grand-val { font-family: monospace; color: #60a5fa; }

        /* Connected Visual 3-Step Progress Bar */
        .rt-stepper-wrap { display: flex; align-items: flex-start; justify-content: space-between; position: relative; margin-bottom: 12px; padding: 0 4px; box-sizing: border-box; }
        .rt-step-node { flex: 1; display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2; text-align: center; }
        .rt-step-node:not(:last-child)::after { content: ''; position: absolute; top: 14px; left: 50%; width: 100%; height: 3px; background: #334155; z-index: -1; }
        .rt-step-node.done:not(:last-child)::after { background: #3b82f6; }
        .rt-step-node.served-done:not(:last-child)::after { background: #10b981; }
        .rt-step-circle { width: 30px; height: 30px; border-radius: 50%; background: #1e293b; border: 2px solid #475569; color: #94a3b8; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; }
        .rt-step-node.done .rt-step-circle { background: #1e293b; border-color: #3b82f6; color: #60a5fa; }
        .rt-step-node.current .rt-step-circle { background: #f97316; border-color: #f97316; color: #fff; box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.3); transform: scale(1.06); }
        .rt-step-node.current.is-served .rt-step-circle, .rt-step-node.served-done .rt-step-circle { background: #10b981; border-color: #10b981; color: #fff; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.3); }
        .rt-step-lbl { font-size: 11px; font-weight: 800; color: #94a3b8; margin-top: 5px; }
        .rt-step-node.done .rt-step-lbl { color: #93c5fd; }
        .rt-step-node.current .rt-step-lbl { color: #fb923c; font-weight: 900; }
        .rt-step-node.current.is-served .rt-step-lbl { color: #34d399; font-weight: 900; }

        .rt-step-msg-box { display: flex; align-items: center; gap: 8px; padding: 9px 12px; border-radius: 10px; font-size: 12.5px; font-weight: 700; line-height: 1.4; box-sizing: border-box; }
        .rt-step-msg-box.msg-pending { background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(59, 130, 246, 0.4); color: #93c5fd; }
        .rt-step-msg-box.msg-preparing { background: rgba(249, 115, 22, 0.2); border: 1px solid rgba(249, 115, 22, 0.4); color: #fdba74; }
        .rt-step-msg-box.msg-served { background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; }
        .rt-served-check { display: inline-flex; color: #059669; }
        .rt-pulse-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; animation: rtPulse 1.5s infinite; }
        .rt-pulse-blue { background: #2563eb; }
        .rt-pulse-orange { background: #f97316; }
        @keyframes rtPulse { 0% { transform: scale(0.95); opacity: 1; } 50% { transform: scale(1.4); opacity: 0.5; } 100% { transform: scale(0.95); opacity: 1; } }

        /* Print-Style Order Receipt Tray */
        .rt-receipt-box { background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 20px 20px 10px 10px; padding: 24px 24px 28px; box-shadow: 0 15px 35px -8px rgba(15, 23, 42, 0.12); position: relative; color: #0f172a; box-sizing: border-box; }
        .rt-receipt-box::after { content: ''; position: absolute; left: 0; right: 0; bottom: -8px; height: 9px; background: linear-gradient(-45deg, transparent 33.33%, #ffffff 33.33%, #ffffff 66.66%, transparent 66.66%), linear-gradient(45deg, transparent 33.33%, #ffffff 33.33%, #ffffff 66.66%, transparent 66.66%); background-size: 14px 16px; }
        .rt-rc-head { text-align: center; padding-bottom: 14px; border-bottom: 1.5px dashed #cbd5e1; }
        .rt-rc-brand { font-size: 17px; font-weight: 900; letter-spacing: 1.5px; text-transform: uppercase; color: #0f172a; }
        .rt-rc-meta { font-family: 'SFMono-Regular', Consolas, monospace; font-size: 12px; color: #64748b; margin-top: 4px; font-weight: 700; }
        .rt-rc-subbar { display: flex; justify-content: space-between; align-items: center; margin-top: 12px; padding-top: 10px; border-top: 1px dashed #e2e8f0; }
        .rt-cart-count { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 900; display: inline-block; }
        .rt-cart-count.bump { transform: scale(1.18); background: #f97316; color: #fff; }
        .rt-clear-btn { background: none; border: none; color: #94a3b8; font-size: 12.5px; font-weight: 800; cursor: pointer; text-decoration: underline; padding: 3px; display: none; }
        .rt-clear-btn:hover { color: #ef4444; }

        .rt-rc-cols { display: grid; grid-template-columns: 1fr auto; font-family: 'SFMono-Regular', Consolas, monospace; font-size: 11.5px; font-weight: 800; text-transform: uppercase; color: #64748b; padding: 10px 0 8px; border-bottom: 1.5px dashed #cbd5e1; }
        .rt-cart-list { margin: 6px 0 12px; max-height: 360px; overflow-y: auto; -webkit-overflow-scrolling: touch; box-sizing: border-box; }
        .rt-empty-cart-msg { color: #94a3b8; text-align: center; padding: 35px 0; font-size: 14px; line-height: 1.5; margin: 0; }
        .rt-rc-item { padding: 12px 0; border-bottom: 1px dashed #e2e8f0; box-sizing: border-box; }
        .rt-rc-item:last-child { border-bottom: none; }
        .rt-rc-item-main { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
        .rt-rc-item-left { display: flex; align-items: flex-start; gap: 9px; min-width: 0; }
        .rt-rc-qty-badge { font-family: 'SFMono-Regular', Consolas, monospace; background: #0f172a; color: #fff; font-size: 12.5px; font-weight: 900; padding: 2px 8px; border-radius: 7px; flex-shrink: 0; }
        .rt-rc-name { font-size: 14.5px; font-weight: 900; color: #0f172a; line-height: 1.35; }
        .rt-rc-unit-sub { font-family: 'SFMono-Regular', Consolas, monospace; font-size: 11.5px; color: #64748b; margin-top: 3px; font-weight: 600; }
        .rt-rc-line-amt { font-family: 'SFMono-Regular', Consolas, monospace; font-size: 15px; font-weight: 900; color: #0f172a; white-space: nowrap; }

        .rt-rc-extras-list { margin: 6px 0 4px 12px; padding-left: 10px; border-left: 2px solid #fed7aa; display: flex; flex-direction: column; gap: 3px; }
        .rt-rc-ex-line { display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #c2410c; font-weight: 700; }
        .rt-rc-ex-price { font-family: 'SFMono-Regular', Consolas, monospace; font-size: 11.5px; color: #ea580c; }
        .rt-rc-item-controls { display: flex; justify-content: space-between; align-items: center; margin-top: 9px; padding-top: 7px; }
        .rt-edit-ex-trigger { display: inline-flex; align-items: center; gap: 5px; background: #fff7ed; color: #ea580c; border: 1.5px solid #fed7aa; padding: 5px 11px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; }
        .rt-qty-box { display: flex; align-items: center; gap: 8px; background: #f1f5f9; padding: 3px 6px; border-radius: 10px; border: 1.5px solid #e2e8f0; }
        .rt-qty-btn { background: #fff; border: 1px solid #cbd5e1; width: 32px; height: 32px; border-radius: 8px; font-weight: 900; font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #0f172a; }
        .rt-qty-num { font-weight: 900; min-width: 20px; text-align: center; font-size: 14px; }

        .rt-rc-summary { border-top: 1.5px dashed #94a3b8; border-bottom: 1.5px dashed #94a3b8; padding: 14px; margin-top: 8px; background: #f8fafc; border-radius: 12px; box-sizing: border-box; }
        .rt-rc-sum-row { display: flex; justify-content: space-between; align-items: center; font-size: 14px; color: #475569; margin-bottom: 6px; font-weight: 700; }
        .rt-rc-sum-row span:last-child { font-family: 'SFMono-Regular', Consolas, monospace; font-weight: 800; color: #0f172a; }
        .rt-rc-grand-row { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 10px; border-top: 1.5px dashed #cbd5e1; font-size: 17.5px; font-weight: 900; color: #0f172a; }
        .rt-rc-grand-amt { font-family: 'SFMono-Regular', Consolas, monospace; font-size: 22px; color: #f97316; }

        .rt-note-label { display: block; font-size: 12px; font-weight: 800; color: #475569; margin-top: 14px; margin-bottom: 6px; }
        .rt-note-chips { display: flex; flex-wrap: wrap; gap: 7px; }
        .rt-note-chip { background: #f8fafc; border: 1.5px solid #e2e8f0; color: #475569; padding: 6px 13px; border-radius: 20px; font-size: 12.5px; font-weight: 700; cursor: pointer; }
        .rt-note-chip:hover { background: #fff7ed; border-color: #f97316; color: #ea580c; }
        .rt-note-textarea { width: 100%; margin-top: 10px; padding: 11px 14px; border-radius: 12px; border: 1.5px solid #cbd5e1; font-size: 14px; box-sizing: border-box; background: #f8fafc; font-family: inherit; }
        .rt-note-textarea:focus { outline: none; border-color: #f97316; background: #fff; }

        .rt-submit { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; border: none; width: 100%; height: 56px; padding: 0 16px; font-size: 16.5px; font-weight: 900; border-radius: 16px; cursor: pointer; margin-top: 14px; box-shadow: 0 10px 24px rgba(16, 185, 129, 0.35); display: flex; align-items: center; justify-content: center; gap: 10px; box-sizing: border-box; }

        /* Modals */
        .rt-modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(6px); z-index: 99999; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box; }
        .rt-modal { background: #fff; border-radius: 26px; padding: 28px; max-width: 480px; width: 100%; box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.4); position: relative; max-height: 90vh; overflow-y: auto; color: #0f172a; box-sizing: border-box; }
        .rt-modal-title { margin: 0 0 4px; font-size: 20px; font-weight: 900; color: #0f172a; display: flex; align-items: center; gap: 8px; }
        .rt-modal-sparkle { color: #f97316; display: inline-flex; }
        .rt-modal-subtitle { margin: 0 0 18px; font-size: 14px; color: #64748b; font-weight: 700; }
        .rt-modal-scroll { max-height: 290px; overflow-y: auto; margin-bottom: 20px; }
        .rt-modal-actions { display: flex; gap: 11px; }
        .rt-modal-save-btn { flex: 1; height: 50px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; border: none; padding: 13px; border-radius: 14px; font-weight: 900; font-size: 15.5px; cursor: pointer; }
        .rt-modal-cancel-btn { height: 50px; background: #f1f5f9; color: #334155; border: none; padding: 13px 20px; border-radius: 14px; font-weight: 800; cursor: pointer; }

        .rt-cart-ex-pill { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border: 1.5px solid #cbd5e1; border-radius: 14px; margin-bottom: 10px; cursor: pointer; font-weight: 700; font-size: 14.5px; box-sizing: border-box; }
        .rt-cart-ex-pill:has(input:checked) { border-color: #f97316; background: #fff7ed; color: #c2410c; }
        .rt-cart-ex-pill-left { display: flex; align-items: center; }
        .rt-cart-ex-pill input { width: 20px; height: 20px; accent-color: #f97316; margin-right: 12px; }
        .rt-cart-ex-pill-price { color: #f97316; font-weight: 900; }

        /* Confirmation Modal */
        .rt-conf-center { text-align: center; }
        .rt-conf-check-circle { width: 64px; height: 64px; border-radius: 50%; background: #d1fae5; color: #059669; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; box-shadow: 0 8px 20px rgba(5,150,105,0.2); }
        .rt-conf-heading { margin: 0 0 5px; font-size: 23px; color: #0f172a; font-weight: 900; }
        .rt-conf-desc { color: #64748b; font-size: 14px; margin: 0; font-weight: 500; }
        .rt-conf-receipt { background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 18px; padding: 16px 18px; margin: 16px 0 20px; text-align: left; box-sizing: border-box; }
        .rt-conf-head { display: flex; justify-content: space-between; align-items: center; padding-bottom: 9px; margin-bottom: 9px; border-bottom: 1.5px dashed #cbd5e1; font-size: 12.5px; font-weight: 900; color: #475569; text-transform: uppercase; }
        .rt-conf-badge-cnt { color: #f97316; }
        .rt-conf-items-scroll { max-height: 250px; overflow-y: auto; padding-right: 4px; box-sizing: border-box; }
        .rt-conf-item-block { padding: 10px 0; border-bottom: 1px dashed #e2e8f0; box-sizing: border-box; }
        .rt-conf-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; font-size: 14px; }
        .rt-conf-item-title { color: #0f172a; }
        .rt-conf-sub-line { font-family: 'SFMono-Regular', Consolas, monospace; font-size: 12px; color: #64748b; margin-top: 3px; font-weight: 600; }
        .rt-conf-item-amt { font-family: 'SFMono-Regular', Consolas, monospace; color: #0f172a; font-size: 14.5px; white-space: nowrap; }
        .rt-conf-ex-breakdown { margin: 6px 0 0 12px; padding-left: 10px; border-left: 2px solid #fed7aa; display: flex; flex-direction: column; gap: 3px; }
        .rt-conf-ex-row { display: flex; justify-content: space-between; font-size: 12px; color: #c2410c; font-weight: 700; }
        .rt-conf-note { display: none; margin-top: 10px; padding: 8px 12px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; font-size: 12.5px; color: #92400e; font-weight: 700; }
        .rt-conf-totals { margin-top: 12px; font-size: 13.5px; color: #475569; background: #fff; padding: 12px 14px; border-radius: 12px; border: 1.5px solid #cbd5e1; box-sizing: border-box; }
        .rt-conf-totals strong, .rt-conf-mono { font-family: 'SFMono-Regular', Consolas, monospace; }
        .rt-conf-tot-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .rt-conf-tax-row { display: flex; justify-content: space-between; margin-bottom: 7px; }
        .rt-conf-grand-row { display: flex; justify-content: space-between; font-size: 17px; font-weight: 900; color: #0f172a; padding-top: 9px; border-top: 1.5px dashed #cbd5e1; }
        .rt-conf-grand-val { color: #059669; }
        .rt-conf-btn-stack { display: flex; flex-direction: column; gap: 10px; }
        .rt-conf-more-btn { height: 52px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; border: none; padding: 0 22px; border-radius: 15px; font-weight: 900; font-size: 15.5px; cursor: pointer; width: 100%; display: flex; align-items: center; justify-content: center; gap: 9px; }
        .rt-conf-track-btn { height: 46px; background: #f1f5f9; color: #334155; border: none; padding: 0 20px; border-radius: 13px; font-weight: 800; font-size: 14.5px; cursor: pointer; width: 100%; }

        /* Mobile Bottom Bar & Drawer Backdrop */
        .rt-mobile-bar { display: none; position: fixed; bottom: 16px; left: 16px; right: 16px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; padding: 16px 22px; border-radius: 18px; justify-content: space-between; align-items: center; z-index: 990; box-shadow: 0 15px 35px rgba(15, 23, 42, 0.4); font-weight: 900; font-size: 16px; cursor: pointer; border: 1px solid rgba(255,255,255,0.15); box-sizing: border-box; }
        .rt-mob-bar-left { display: flex; align-items: center; gap: 9px; }
        .rt-mob-bar-price { background: #f97316; padding: 6px 14px; border-radius: 20px; font-size: 15px; font-weight: 900; }
        .rt-drawer-backdrop { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); z-index: 995; }

        /* Responsive Layout (< 740px) */
        @media (max-width: 739px) {
            .rt-wrapper { padding: 12px 12px 96px; }
            .rt-header-right-stack { align-items: flex-start; width: 100%; }
            .rt-app { flex-direction: column; }
            .rt-sidebar-cats { width: 100%; min-width: 0; position: relative; top: 0; flex-direction: row; overflow-x: auto; padding: 8px; }
            .rt-scat-title { display: none; }
            .rt-scat-btn { width: auto; padding: 10px 14px; white-space: nowrap; }
            .rt-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 12px; }
            .rt-right-col {
                display: none; position: fixed; left: 0; right: 0; bottom: 0; top: auto;
                width: 100%; max-width: 100%; max-height: 88vh; background: #f8fafc; z-index: 998;
                border-radius: 28px 28px 0 0; padding: 18px 18px 30px;
                box-shadow: 0 -20px 50px rgba(15, 23, 42, 0.35); overflow-y: auto; box-sizing: border-box;
            }
            .rt-right-col.panel-open { display: flex; }
            .rt-mobile-bar { display: flex; }
        }
    </style>

    <!-- =====================================================================
         CLEAN HTML MARKUP (NO INLINE STYLES OR INLINE JS)
         ===================================================================== -->
    <div class="rt-toast-container" id="rt-toast-box"></div>
    <div class="rt-drawer-backdrop" id="rt-drawer-backdrop"></div>

    <div class="rt-wrapper">
        <div class="rt-header">
            <div class="rt-brand-wrap">
                <button type="button" id="rt-toggle-cat-sidebar" class="rt-sidebar-toggle-btn" title="Show or Hide Food Categories">
                    <?php echo $svg_sidebar; ?>
                </button>

                <div class="rt-brand-icon"><?php echo esc_html(substr($s['restaurant_name'], 0, 1)); ?></div>
                <div>
                    <h2><?php echo esc_html($s['restaurant_name']); ?></h2>
                    <div class="rt-header-sub">Welcome to Table #<?php echo esc_html($table_no); ?> (<?php echo $table_seats; ?> Seats)</div>
                </div>
            </div>

            <div class="rt-header-actions">
                <button type="button" id="rt-toggle-tray-panel" class="rt-tray-toggle-btn" title="View Your Order Cart & Live Food Status">
                    <?php echo $svg_bag; ?>
                    <span>Order</span>
                    <span class="rt-btn-badge" id="rt-header-badge">0</span>
                    <span class="rt-btn-total-pill"><?php echo $cur; ?><span id="rt-header-total">0.00</span></span>
                </button>

                <button type="button" id="rt-call-waiter-btn" class="rt-waiter-btn" title="Tap to call staff to your table"><?php echo $svg_bell; ?><span>Call Waiter</span></button>
                <button type="button" id="rt-request-bill-btn" class="rt-bill-btn" disabled title="Available once your order is served"><?php echo $svg_receipt; ?><span>Ask for Bill</span></button>
                <span class="rt-table-pill"><?php echo $svg_table; ?><span>Table #<?php echo esc_html($table_no); ?></span></span>
                <input type="hidden" id="rt-selected-table" value="<?php echo esc_attr($table_no); ?>">
            </div>
        </div>

        <div class="rt-app" id="rt-pos-app">
            
            <!-- Left Sidebar Categories Menu -->
            <div class="rt-sidebar-cats" id="rt-cat-sidebar">
                <div class="rt-scat-title">Categories</div>
                <button class="rt-scat-btn active" data-cat="all">
                    <span class="rt-scat-label-wrap"><?php echo $svg_utensils; ?><span>All Dishes</span></span>
                    <span class="scnt"><?php echo count($items); ?></span>
                </button>
                <?php foreach ($cat_counts as $cat_name => $cnt) : ?>
                    <button class="rt-scat-btn" data-cat="<?php echo esc_attr($cat_name); ?>">
                        <span><?php echo esc_html($cat_name); ?></span>
                        <span class="scnt"><?php echo $cnt; ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Middle Menu Grid -->
            <div class="rt-middle" id="rt-menu-section">
                <div class="rt-toolbar">
                    <div class="rt-search-wrap">
                        <span class="rt-search-icon"><?php echo $svg_search; ?></span>
                        <input type="text" id="rt-search-input" class="rt-search" placeholder="Type dish name to search (e.g. Burger, Pizza, Drinks)...">
                        <button type="button" id="rt-search-clear" class="rt-search-clear" title="Clear search"><?php echo $svg_close; ?></button>
                    </div>
                    <div class="rt-quick-guide">
                        Step 1: Choose dishes &nbsp;•&nbsp; Step 2: Tap <strong>Order</strong> above to send to kitchen
                    </div>
                </div>

                <?php if (empty($items)) : ?>
                    <div class="rt-empty-state-box">
                        <h3>Menu is Being Updated</h3>
                        <p>Please check back in a moment or tap <strong>"Call Waiter"</strong> above.</p>
                    </div>
                <?php else : ?>
                    <div class="rt-grid" id="rt-menu-grid">
                        <?php foreach ($items as $item) : 
                            $item_extra_ids = json_decode($item->extras, true) ?: [];
                            $base_price     = floatval($item->price);
                        ?>
                            <div class="rt-card"
                                data-item-id="<?php echo intval($item->id); ?>"
                                data-cat="<?php echo esc_attr($item->category); ?>"
                                data-name="<?php echo esc_attr(strtolower($item->name . ' ' . $item->category)); ?>">
                                <div>
                                    <div class="rt-img-box">
                                        <span class="rt-cat-tag"><?php echo esc_html($item->category); ?></span>
                                        <span class="rt-in-tray-badge">0 added</span>
                                        <img src="<?php echo esc_url($item->image_url ?: 'https://via.placeholder.com/400x280?text=Antalya+Dish'); ?>" alt="<?php echo esc_attr($item->name); ?>">
                                    </div>
                                    <h4><?php echo esc_html($item->name); ?></h4>
                                    <div class="price"><?php echo $cur . number_format($base_price, 0); ?></div>

                                    <?php if (!empty($item_extra_ids) && !empty($all_extras)) : ?>
                                        <div class="rt-extras-box">
                                            <div class="rt-extras-hint">Optional Add-ons:</div>
                                            <?php foreach ($item_extra_ids as $eid) : if (isset($all_extras[$eid])) : $ex = $all_extras[$eid]; ?>
                                                <label class="rt-extra-item">
                                                    <span class="rt-extra-left">
                                                        <input type="checkbox" class="rt-ex-chk" data-name="<?php echo esc_attr($ex->name); ?>" data-price="<?php echo floatval($ex->price); ?>">
                                                        <span><?php echo esc_html($ex->name); ?></span>
                                                    </span>
                                                    <strong class="rt-extra-price-tag">+<?php echo $cur . number_format($ex->price, 0); ?></strong>
                                                </label>
                                            <?php endif; endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="rt-card-actions">
                                    <div class="rt-card-qty-ctrl" title="Select Quantity">
                                        <button type="button" class="rt-cq-btn rt-cq-dec" aria-label="Decrease quantity">−</button>
                                        <span class="rt-cq-val">1</span>
                                        <button type="button" class="rt-cq-btn rt-cq-inc" aria-label="Increase quantity">+</button>
                                    </div>
                                    <button type="button" class="rt-add"
                                        data-id="<?php echo $item->id; ?>"
                                        data-name="<?php echo esc_attr($item->name); ?>"
                                        data-price="<?php echo $base_price; ?>"
                                        data-allowed-extras='<?php echo esc_attr(wp_json_encode(array_map('intval', $item_extra_ids))); ?>'>
                                        Add • <?php echo $cur . number_format($base_price, 0); ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="rt-no-results" class="rt-empty-state-box rt-hidden">
                        <h3>No Dishes Found</h3>
                        <p>Try searching with a different word or select <strong>"All Dishes"</strong> on the left.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Combined Live Status & Receipt Order Tray -->
            <div class="rt-right-col" id="rt-tray-section">
                <div class="rt-panel-top-bar">
                    <strong class="rt-panel-top-title">
                        <?php echo $svg_bag; ?>
                        <span>Table #<?php echo esc_html($table_no); ?> — Review Order & Live Status</span>
                    </strong>
                    <button type="button" id="rt-close-drawer" class="rt-back-menu-btn" title="Return to Food Menu">
                        <?php echo $svg_close; ?>
                        <span>Back to Menu</span>
                    </button>
                </div>

                <!-- 1. Live Order Status Card -->
                <div class="rt-box rt-live-tracker" id="rt-live-tracker">
                    <div class="rt-live-tracker-header">
                        <h4 class="rt-live-tracker-title">
                            <?php echo $svg_radio; ?><span>Live Kitchen Progress</span>
                        </h4>
                        <span class="rt-running-total-pill">Total Ordered: <?php echo $cur; ?><span id="rt-running-bill">0.00</span></span>
                    </div>
                    <div id="rt-live-orders-list"></div>
                </div>

                <!-- 2. Print-Style Order Receipt Box -->
                <div class="rt-receipt-box">
                    <div class="rt-rc-head">
                        <div class="rt-rc-brand"><?php echo esc_html($s['restaurant_name']); ?></div>
                        <div class="rt-rc-meta">TABLE #<?php echo esc_html($table_no); ?> • YOUR CURRENT TRAY</div>
                        <div class="rt-rc-subbar">
                            <span class="rt-cart-count" id="rt-cart-badge"><span id="rt-item-count">0</span> Item(s) Selected</span>
                            <button type="button" id="rt-clear-cart" class="rt-clear-btn">Remove All</button>
                        </div>
                    </div>

                    <div class="rt-rc-cols">
                        <span>Selected Dishes</span>
                        <span>Price</span>
                    </div>

                    <div class="rt-cart-list" id="rt-cart-list">
                        <p class="rt-empty-cart-msg">
                            Your tray is currently empty.<br>
                            <small>Tap <strong>"Back to Menu"</strong> and click <strong>"Add"</strong> on any dish you want!</small>
                        </p>
                    </div>

                    <div class="rt-rc-summary">
                        <div class="rt-rc-sum-row">
                            <span>Subtotal</span>
                            <span><?php echo $cur; ?><span id="rt-sub">0.00</span></span>
                        </div>
                        <?php if ($tax_rate > 0) : ?>
                            <div class="rt-rc-sum-row">
                                <span>VAT / Tax (<?php echo $tax_rate; ?>%)</span>
                                <span><?php echo $cur; ?><span id="rt-tax">0.00</span></span>
                            </div>
                        <?php endif; ?>
                        <div class="rt-rc-grand-row">
                            <span>TOTAL TO PAY</span>
                            <span class="rt-rc-grand-amt"><?php echo $cur; ?><span id="rt-total">0.00</span></span>
                        </div>
                    </div>

                    <label class="rt-note-label">Quick Kitchen Preferences (Optional):</label>
                    <div class="rt-note-chips">
                        <span class="rt-note-chip" data-note="Less spicy">Less spicy</span>
                        <span class="rt-note-chip" data-note="Extra spicy">Extra spicy</span>
                        <span class="rt-note-chip" data-note="No onion">No onion</span>
                        <span class="rt-note-chip" data-note="Serve together">Serve all together</span>
                    </div>

                    <textarea id="rt-note" class="rt-note-textarea" rows="2" placeholder="Write any special request for the chef here (optional)..."></textarea>
                    
                    <button class="rt-submit" id="rt-submit-order">
                        <?php echo $svg_send; ?>
                        <span>SEND ORDER TO KITCHEN</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Floating Bottom Bar -->
    <div class="rt-mobile-bar" id="rt-open-drawer-btn">
        <span class="rt-mob-bar-left"><?php echo $svg_bag; ?><span>My Order (<span id="rt-mob-count">0</span>)</span></span>
        <span class="rt-mob-bar-price"><?php echo $cur; ?><span id="rt-mob-total">0.00</span></span>
    </div>

    <!-- Post-Add Extras Editor Modal -->
    <div class="rt-modal-backdrop" id="rt-cart-extras-modal">
        <div class="rt-modal">
            <h3 class="rt-modal-title">
                <span class="rt-modal-sparkle"><?php echo $svg_sparkles; ?></span>
                <span>Customize Add-ons</span>
            </h3>
            <p id="cem-dish-title" class="rt-modal-subtitle"></p>
            <div id="cem-extras-list" class="rt-modal-scroll"></div>
            <div class="rt-modal-actions">
                <button type="button" id="cem-save-btn" class="rt-modal-save-btn">Save Changes</button>
                <button type="button" id="cem-cancel-btn" class="rt-modal-cancel-btn">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Order Confirmation Modal -->
    <div class="rt-modal-backdrop" id="rt-success-modal">
        <div class="rt-modal">
            <div class="rt-conf-center">
                <div class="rt-conf-check-circle"><?php echo $svg_check; ?></div>
                <h3 class="rt-conf-heading">Order Sent to Kitchen!</h3>
                <p class="rt-conf-desc">Our chefs have received your order for <strong>Table #<?php echo esc_html($table_no); ?></strong>.</p>
            </div>

            <div class="rt-conf-receipt">
                <div class="rt-conf-head">
                    <span id="conf-order-id">Order Summary</span>
                    <span id="conf-item-count" class="rt-conf-badge-cnt">0 Item(s)</span>
                </div>
                <div class="rt-conf-items-scroll" id="conf-items-list"></div>
                <div id="conf-note-box" class="rt-conf-note"></div>
                <div class="rt-conf-totals">
                    <div class="rt-conf-tot-row"><span>Subtotal</span><strong id="conf-sub"></strong></div>
                    <div id="conf-tax-row" class="rt-conf-tax-row"><span>VAT / Tax (<?php echo $tax_rate; ?>%)</span><strong id="conf-tax"></strong></div>
                    <div class="rt-conf-grand-row"><span>Total Amount</span><span id="conf-total" class="rt-conf-mono rt-conf-grand-val"></span></div>
                </div>
            </div>

            <div class="rt-conf-btn-stack">
                <button type="button" id="rt-add-another-order-btn" class="rt-conf-more-btn"><?php echo $svg_plus; ?><span>Order More Food</span></button>
                <button type="button" id="rt-modal-track-btn" class="rt-conf-track-btn">Watch Live Kitchen Status</button>
            </div>
        </div>
    </div>

    <!-- =====================================================================
         SEPARATE JAVASCRIPT BLOCK
         ===================================================================== -->
    <script id="rtpos-tablet-scripts">
    jQuery(document).ready(function($) {
        let ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
        let cur = <?php echo json_encode($s['currency']); ?>;
        let taxRate = <?php echo $tax_rate; ?>;
        let allExtrasCatalog = <?php echo wp_json_encode($js_all_extras); ?>;
        let sparklesSvg = <?php echo json_encode($svg_sparkles); ?>;
        let bellSvg     = <?php echo json_encode($svg_bell); ?>;
        let receiptSvg  = <?php echo json_encode($svg_receipt); ?>;
        let sendSvg     = <?php echo json_encode($svg_send); ?>;
        let tableKey    = $('#rt-selected-table').val() || 'default';
        let storageKey  = 'rtpos_cart_' + tableKey;
        let waiterLockKey = 'rtpos_waiter_locked_' + tableKey;
        let cart = [];

        try {
            let saved = localStorage.getItem(storageKey);
            if (saved) cart = JSON.parse(saved) || [];
        } catch(e) {}

        function saveCartState() {
            try { localStorage.setItem(storageKey, JSON.stringify(cart)); } catch(e) {}
        }

        function showToast(msg, type = 'default') {
            let t = $(`<div class="rt-toast ${type}">${msg}</div>`);
            $('#rt-toast-box').append(t);
            setTimeout(() => { t.fadeOut(250, () => t.remove()); }, 2800);
        }

        function playSuccessChime() {
            try {
                let ctx = new (window.AudioContext || window.webkitAudioContext)();
                let osc = ctx.createOscillator();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(660, ctx.currentTime);
                osc.frequency.setValueAtTime(880, ctx.currentTime + 0.12);
                osc.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.28);
            } catch(e) {}
        }

        // Restore Call Waiter Locked State on Reload until Admin Panel Acknowledges
        function setWaiterButtonLocked(isLocked) {
            let btn = $('#rt-call-waiter-btn');
            if (isLocked) {
                localStorage.setItem(waiterLockKey, '1');
                btn.prop('disabled', true).html(bellSvg + '<span>Waiting for Staff...</span>');
            } else {
                localStorage.removeItem(waiterLockKey);
                btn.prop('disabled', false).html(bellSvg + '<span>Call Waiter</span>');
            }
        }

        if (localStorage.getItem(waiterLockKey) === '1') {
            setWaiterButtonLocked(true);
        }

        // Toggle Left Category Sidebar
        $('#rt-toggle-cat-sidebar').on('click', function() {
            $('#rt-cat-sidebar').toggleClass('sidebar-hidden');
        });

        // Combined Toggle Logic: When Order & Live Status is active, hide right side items (#rt-menu-section) completely
        function toggleTrayPanel(forceOpen = null) {
            let panel   = $('#rt-tray-section');
            let menuSec = $('#rt-menu-section');
            let btn     = $('#rt-toggle-tray-panel');
            let isOpen  = panel.hasClass('panel-open');

            if (forceOpen === true || (!isOpen && forceOpen === null)) {
                panel.addClass('panel-open');
                btn.addClass('is-active');
                menuSec.hide();
                if ($(window).width() < 740) {
                    $('#rt-drawer-backdrop').fadeIn(180);
                }
                pollTableOrderStatus();
            } else {
                panel.removeClass('panel-open');
                btn.removeClass('is-active');
                menuSec.show();
                $('#rt-drawer-backdrop').fadeOut(180);
            }
        }

        $('#rt-toggle-tray-panel, #rt-open-drawer-btn').on('click', function() { toggleTrayPanel(); });
        $('#rt-close-drawer, #rt-drawer-backdrop').on('click', function() { toggleTrayPanel(false); });
        $('#rt-modal-track-btn').on('click', function() {
            $('#rt-success-modal').fadeOut(160);
            toggleTrayPanel(true);
        });

        $('#rt-add-another-order-btn').on('click', function() {
            $('#rt-success-modal').fadeOut(160);
            toggleTrayPanel(false);
            let menuEl = document.getElementById('rt-menu-section');
            if (menuEl) menuEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        // Call Waiter: Disable until received/cleared in Admin Panel
        $('#rt-call-waiter-btn').on('click', function() {
            let tableNo = $('#rt-selected-table').val();
            if (!tableNo) return showToast('Table number not found!', 'warn');
            let btn = $(this);
            btn.prop('disabled', true).text('Calling Staff...');
            $.post(ajaxUrl, { action: 'rtpos_call_waiter', table_no: tableNo }, function(res) {
                if (res.success) {
                    showToast('Staff notified! Please wait for assistance at Table #' + tableNo + '.', 'success');
                    setWaiterButtonLocked(true);
                } else {
                    setWaiterButtonLocked(false);
                }
            }).fail(function() {
                setWaiterButtonLocked(false);
            });
        });

        // Ask for Bill (Enabled only when at least one order is Served)
        $('#rt-request-bill-btn').on('click', function() {
            let tableNo = $('#rt-selected-table').val();
            if (!tableNo) return showToast('Table number not found!', 'warn');
            let btn = $(this);
            btn.prop('disabled', true).text('Asking for Bill...');
            $.post(ajaxUrl, { action: 'rtpos_call_waiter', table_no: tableNo + ' (BILL REQUEST)' }, function(res) {
                if (res.success) {
                    showToast('Bill requested! Our staff will bring your receipt shortly.', 'success');
                    btn.text('Bill Requested!');
                    setTimeout(() => { btn.prop('disabled', false).html(receiptSvg + '<span>Ask for Bill</span>'); }, 20000);
                }
            });
        });

        function pollTableOrderStatus() {
            let tableNo = $('#rt-selected-table').val();
            if (!tableNo) return;

            const iconReceived = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>';
            const iconCooking  = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>';
            const iconServed   = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>';
            const iconCheckSm  = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

            $.post(ajaxUrl, { action: 'rtpos_get_table_orders', table_no: tableNo }, function(res) {
                // Unlock Call Waiter button if admin panel has acknowledged/cleared the waiter alert
                if (res && typeof res.waiter_pending !== 'undefined' && !res.waiter_pending) {
                    if (localStorage.getItem(waiterLockKey) === '1') {
                        setWaiterButtonLocked(false);
                    }
                }

                if (res.success && res.data.length > 0) {
                    let html = '', runningBill = 0, hasServedOrder = false;

                    res.data.forEach((o, index) => {
                        runningBill += parseFloat(String(o.total_amount).replace(/,/g, '')) || 0;
                        let isServed = (o.status === 'served'), isCooking = (o.status === 'preparing');
                        if (isServed) hasServedOrder = true;

                        let s1Class = isServed ? 'served-done' : (isCooking ? 'done' : 'current');
                        let s2Class = isServed ? 'served-done' : (isCooking ? 'current' : '');
                        let s3Class = isServed ? 'current is-served' : '';

                        let stepMsgHtml = '';
                        if (o.status === 'pending') {
                            stepMsgHtml = `<div class="rt-step-msg-box msg-pending"><span class="rt-pulse-dot rt-pulse-blue"></span><span>We received your order! Our kitchen will start preparing it soon.</span></div>`;
                        } else if (o.status === 'preparing') {
                            stepMsgHtml = `<div class="rt-step-msg-box msg-preparing"><span class="rt-pulse-dot rt-pulse-orange"></span><span>Cooking now! Our chef is preparing your fresh meal.</span></div>`;
                        } else {
                            stepMsgHtml = `<div class="rt-step-msg-box msg-served"><span class="rt-served-check">${iconCheckSm}</span><span>Served at your table! Enjoy your meal.</span></div>`;
                        }

                        let roundLabel = res.data.length > 1 ? `Order #${index + 1} • ` : '';
                        let timeStr = o.time ? ` (${o.time})` : '';

                        let breakdownHtml = '';
                        if (Array.isArray(o.items_detail) && o.items_detail.length > 0) {
                            breakdownHtml += '<div class="rt-track-breakdown">';
                            o.items_detail.forEach(d => {
                                let exLines = '';
                                if (Array.isArray(d.extras_details) && d.extras_details.length > 0) {
                                    d.extras_details.forEach(exObj => {
                                        exLines += `<div style="display:flex; justify-content:space-between; font-size:11px; color:#fdba74; font-weight:700; padding-left:10px;"><span>+ ${exObj.name}</span><span style="font-family:monospace;">+${cur}${exObj.price}</span></div>`;
                                    });
                                }
                                breakdownHtml += `
                                    <div class="rt-tb-row">
                                        <div class="rt-tb-item-head">
                                            <div>
                                                <strong class="rt-tb-item-name">${d.qty}× ${d.name}</strong>
                                                <div class="rt-tb-item-sub">Unit: ${cur}${d.unit_price} × ${d.qty}</div>
                                            </div>
                                            <strong class="rt-tb-item-total">${cur}${d.line_total}</strong>
                                        </div>
                                        ${exLines}
                                    </div>`;
                            });
                            breakdownHtml += `
                                <div class="rt-tb-totals">
                                    <div class="rt-tb-sum-line"><span>Subtotal</span><span class="rt-tb-sum-val">${cur}${o.subtotal || o.total_amount}</span></div>
                                    ${parseFloat(o.tax_amount || 0) > 0 ? `<div class="rt-tb-sum-line"><span>VAT / Tax</span><span class="rt-tb-sum-val">${cur}${o.tax_amount}</span></div>` : ''}
                                    <div class="rt-tb-grand-line"><span>Order Total</span><span class="rt-tb-grand-val">${cur}${o.total_amount}</span></div>
                                </div></div>`;
                        }

                        html += `
                            <div class="rt-order-track-card">
                                <div class="rt-track-top">
                                    <span class="rt-track-badge">${roundLabel}Ticket #${o.id}<small class="rt-track-time">${timeStr}</small></span>
                                    <span class="rt-track-amt">${cur}${o.total_amount}</span>
                                </div>
                                ${breakdownHtml}
                                <div class="rt-stepper-wrap">
                                    <div class="rt-step-node ${s1Class}"><div class="rt-step-circle">${(isCooking || isServed) ? iconCheckSm : iconReceived}</div><div class="rt-step-lbl">1. Received</div></div>
                                    <div class="rt-step-node ${s2Class}"><div class="rt-step-circle">${isServed ? iconCheckSm : iconCooking}</div><div class="rt-step-lbl">2. Cooking</div></div>
                                    <div class="rt-step-node ${s3Class}"><div class="rt-step-circle">${iconServed}</div><div class="rt-step-lbl">3. Served</div></div>
                                </div>
                                ${stepMsgHtml}
                            </div>`;
                    });

                    // Enable "Ask for Bill" ONLY if at least one order has been Served
                    let billBtn = $('#rt-request-bill-btn');
                    if (hasServedOrder) {
                        if (billBtn.text().indexOf('Requested') === -1 && billBtn.text().indexOf('Asking') === -1) {
                            billBtn.prop('disabled', false).attr('title', 'Ready to pay? Tap to request your bill');
                        }
                    } else {
                        billBtn.prop('disabled', true).attr('title', 'Available once your order is served');
                    }

                    $('#rt-running-bill').text(runningBill.toFixed(2));
                    $('#rt-live-orders-list').html(html);
                    $('#rt-live-tracker').show();
                } else {
                    $('#rt-live-tracker').hide();
                    $('#rt-request-bill-btn').prop('disabled', true).attr('title', 'Available once your order is served');
                }
            });
        }
        pollTableOrderStatus();
        setInterval(pollTableOrderStatus, 5000);

        let activeCategory = 'all';
        function filterMenuCards() {
            let q = $('#rt-search-input').val().toLowerCase().trim();
            $('#rt-search-clear').css('display', q.length > 0 ? 'flex' : 'none');
            let visibleCount = 0;

            $('.rt-card').each(function() {
                let matchCat = (activeCategory === 'all') || ($(this).data('cat') === activeCategory);
                let matchText = $(this).data('name').indexOf(q) > -1;
                let show = matchCat && matchText;
                $(this).toggle(show);
                if (show) visibleCount++;
            });

            $('#rt-no-results').toggle(visibleCount === 0);
        }

        $('.rt-scat-btn').on('click', function() {
            $('.rt-scat-btn').removeClass('active');
            $(this).addClass('active');
            activeCategory = $(this).data('cat');
            if ($('#rt-tray-section').hasClass('panel-open')) {
                toggleTrayPanel(false);
            }
            filterMenuCards();
        });

        $('#rt-search-input').on('keyup', filterMenuCards);
        $('#rt-search-clear').on('click', function() {
            $('#rt-search-input').val('');
            filterMenuCards();
        });

        // Helper to update Card Button Price dynamically with Qty & Extras
        function updateCardButtonPrice(card) {
            let btn       = card.find('.rt-add');
            let basePrice = parseFloat(btn.data('price')) || 0;
            let qty       = parseInt(card.find('.rt-cq-val').text()) || 1;
            let extraSum  = 0;

            card.find('.rt-ex-chk:checked').each(function() {
                extraSum += parseFloat($(this).data('price')) || 0;
            });

            let totalUnit = (basePrice + extraSum) * qty;
            btn.text('Add • ' + cur + totalUnit.toFixed(0));
        }

        // Card Quantity Selector Increment / Decrement
        $(document).on('click', '.rt-cq-inc', function() {
            let card  = $(this).closest('.rt-card');
            let valEl = card.find('.rt-cq-val');
            let curQ  = parseInt(valEl.text()) || 1;
            valEl.text(curQ + 1);
            updateCardButtonPrice(card);
        });

        $(document).on('click', '.rt-cq-dec', function() {
            let card  = $(this).closest('.rt-card');
            let valEl = card.find('.rt-cq-val');
            let curQ  = parseInt(valEl.text()) || 1;
            if (curQ > 1) {
                valEl.text(curQ - 1);
                updateCardButtonPrice(card);
            }
        });

        $(document).on('change', '.rt-ex-chk', function() {
            let card = $(this).closest('.rt-card');
            updateCardButtonPrice(card);
        });

        function syncCardTrayBadges() {
            let qtyById = {};
            cart.forEach(it => { qtyById[it.id] = (qtyById[it.id] || 0) + (parseInt(it.qty) || 0); });
            $('.rt-card').each(function() {
                let itemId = parseInt($(this).data('item-id')) || 0;
                let count = qtyById[itemId] || 0;
                let badge = $(this).find('.rt-in-tray-badge');
                if (count > 0) { $(this).addClass('in-cart'); badge.text(count + ' in tray').show(); }
                else { $(this).removeClass('in-cart'); badge.hide(); }
            });
        }

        $('.rt-add').on('click', function() {
            let btn = $(this), card = btn.closest('.rt-card');
            let id = btn.data('id'), name = btn.data('name');
            let basePrice = parseFloat(btn.data('price')) || 0;
            let allowedEx = btn.data('allowed-extras') || [];
            let qtyToAdd  = parseInt(card.find('.rt-cq-val').text()) || 1;

            let extras = [], extraCost = 0;
            card.find('.rt-ex-chk:checked').each(function() {
                extras.push($(this).data('name'));
                extraCost += parseFloat($(this).data('price')) || 0;
            });

            // Reset card checkboxes & quantity back to 1
            card.find('.rt-ex-chk').prop('checked', false);
            card.find('.rt-cq-val').text('1');
            btn.text('Add • ' + cur + basePrice.toFixed(0));

            let unitPrice = basePrice + extraCost;
            let key = id + '_' + extras.slice().sort().join('_');
            let found = cart.find(i => i.key === key);
            if (found) found.qty += qtyToAdd;
            else cart.push({ key, id, name, basePrice, unitPrice, extras, allowedEx, qty: qtyToAdd });

            updateCartUI(true);
            showToast(qtyToAdd + '× ' + name + ' added to your order!', 'success');
            btn.text('Added!').css('background', '#10b981');
            setTimeout(() => { btn.text('Add • ' + cur + basePrice.toFixed(0)).css('background', ''); }, 650);
        });

        function updateCartUI(animateBump = false) {
            saveCartState();
            syncCardTrayBadges();

            let html = '', subTotal = 0, totalQty = 0;
            if (cart.length === 0) {
                $('#rt-cart-list').html('<p class="rt-empty-cart-msg">Your tray is currently empty.<br><small>Tap <strong>"Back to Menu"</strong> and click <strong>"Add"</strong> on any dish you want!</small></p>');
                $('#rt-sub, #rt-tax, #rt-total, #rt-mob-total, #rt-header-total').text('0.00');
                $('#rt-item-count, #rt-mob-count, #rt-header-badge').text('0');
                $('#rt-clear-cart').hide();
                return;
            }
            $('#rt-clear-cart').show();

            cart.forEach((item, idx) => {
                let lineTotal = item.unitPrice * item.qty;
                subTotal += lineTotal;
                totalQty += item.qty;

                let baseVal = parseFloat(item.basePrice);
                if (isNaN(baseVal)) {
                    let sumEx = 0;
                    (item.extras || []).forEach(exName => {
                        let foundEx = allExtrasCatalog.find(e => e.name === exName);
                        if (foundEx) sumEx += parseFloat(foundEx.price) || 0;
                    });
                    baseVal = Math.max(0, item.unitPrice - sumEx);
                }

                let exLinesHtml = '';
                if (item.extras && item.extras.length > 0) {
                    exLinesHtml += '<div class="rt-rc-extras-list">';
                    item.extras.forEach(exName => {
                        let foundEx = allExtrasCatalog.find(e => e.name === exName);
                        let exPriceStr = foundEx ? `+${cur}${parseFloat(foundEx.price).toFixed(2)}` : '';
                        exLinesHtml += `<div class="rt-rc-ex-line"><span>+ ${exName}</span><span class="rt-rc-ex-price">${exPriceStr}</span></div>`;
                    });
                    exLinesHtml += '</div>';
                }

                let editExBtn = allExtrasCatalog.length > 0 ? `<button type="button" class="rt-edit-ex-trigger" data-idx="${idx}">${sparklesSvg}<span>${item.extras.length ? 'Change Add-ons' : '+ Add Extras'}</span></button>` : '<span></span>';

                html += `
                    <div class="rt-rc-item">
                        <div class="rt-rc-item-main">
                            <div class="rt-rc-item-left">
                                <span class="rt-rc-qty-badge">${item.qty}×</span>
                                <div>
                                    <div class="rt-rc-name">${item.name}</div>
                                    <div class="rt-rc-unit-sub">${cur}${item.unitPrice.toFixed(2)} each</div>
                                </div>
                            </div>
                            <div class="rt-rc-line-amt">${cur}${lineTotal.toFixed(2)}</div>
                        </div>
                        ${exLinesHtml}
                        <div class="rt-rc-item-controls">
                            ${editExBtn}
                            <div class="rt-qty-box">
                                <button type="button" class="rt-qty-btn rt-dec" data-idx="${idx}" title="Decrease">−</button>
                                <span class="rt-qty-num">${item.qty}</span>
                                <button type="button" class="rt-qty-btn rt-inc" data-idx="${idx}" title="Increase">+</button>
                            </div>
                        </div>
                    </div>`;
            });

            let taxAmt = (subTotal * taxRate) / 100;
            let grandTotal = subTotal + taxAmt;

            $('#rt-cart-list').html(html);
            $('#rt-item-count, #rt-mob-count, #rt-header-badge').text(totalQty);
            $('#rt-sub').text(subTotal.toFixed(2));
            $('#rt-tax').text(taxAmt.toFixed(2));
            $('#rt-total, #rt-mob-total, #rt-header-total').text(grandTotal.toFixed(2));

            if (animateBump) {
                let badge = $('#rt-cart-badge');
                badge.addClass('bump');
                setTimeout(() => badge.removeClass('bump'), 220);
                let listEl = document.getElementById('rt-cart-list');
                if (listEl) listEl.scrollTop = listEl.scrollHeight;
            }
        }

        let editingCartIdx = null;
        $(document).on('click', '.rt-edit-ex-trigger', function() {
            editingCartIdx = parseInt($(this).data('idx'));
            let item = cart[editingCartIdx];
            if (!item) return;

            $('#cem-dish-title').text(item.name + ' (Base price: ' + cur + parseFloat(item.basePrice ?? item.unitPrice).toFixed(2) + ')');

            let listToShow = allExtrasCatalog;
            if (Array.isArray(item.allowedEx) && item.allowedEx.length > 0) {
                let assigned = allExtrasCatalog.filter(e => item.allowedEx.includes(e.id));
                let others = allExtrasCatalog.filter(e => !item.allowedEx.includes(e.id));
                listToShow = assigned.concat(others);
            }

            let exHtml = '';
            listToShow.forEach(ex => {
                let isChecked = item.extras.includes(ex.name) ? 'checked' : '';
                exHtml += `
                    <label class="rt-cart-ex-pill">
                        <span class="rt-cart-ex-pill-left"><input type="checkbox" class="cem-chk" data-name="${ex.name}" data-price="${ex.price}" ${isChecked}>${ex.name}</span>
                        <span class="rt-cart-ex-pill-price">+${cur}${ex.price.toFixed(2)}</span>
                    </label>`;
            });

            $('#cem-extras-list').html(exHtml);
            $('#rt-cart-extras-modal').css('display', 'flex').hide().fadeIn(180);
        });

        $('#cem-save-btn').on('click', function() {
            if (editingCartIdx === null || !cart[editingCartIdx]) return;
            let item = cart[editingCartIdx];
            let base = parseFloat(item.basePrice ?? item.unitPrice) || 0;
            let newExtras = [], extraCost = 0;

            $('#cem-extras-list .cem-chk:checked').each(function() {
                newExtras.push($(this).data('name'));
                extraCost += parseFloat($(this).data('price')) || 0;
            });

            item.basePrice = base;
            item.extras = newExtras;
            item.unitPrice = base + extraCost;
            item.key = item.id + '_' + newExtras.slice().sort().join('_');

            updateCartUI();
            $('#rt-cart-extras-modal').fadeOut(150);
            showToast('Updated add-ons for ' + item.name, 'success');
        });

        $('#cem-cancel-btn').on('click', function() {
            $('#rt-cart-extras-modal').fadeOut(150);
        });

        $('#rt-cart-extras-modal').on('click', function(e) { if (e.target === this) $(this).fadeOut(150); });

        updateCartUI();

        $('#rt-clear-cart').on('click', function() {
            if (confirm('Remove all selected dishes from your tray?')) { cart = []; updateCartUI(); }
        });

        $(document).on('click', '.rt-inc', function() { cart[$(this).data('idx')].qty++; updateCartUI(); });
        $(document).on('click', '.rt-dec', function() {
            let idx = $(this).data('idx');
            if (cart[idx].qty > 1) cart[idx].qty--; else cart.splice(idx, 1);
            updateCartUI();
        });

        $('.rt-note-chip').on('click', function() {
            let chip = $(this).data('note');
            let curNote = $('#rt-note').val().trim();
            if (curNote.indexOf(chip) === -1) {
                $('#rt-note').val(curNote ? curNote + ', ' + chip : chip);
            }
        });

        $('#rt-submit-order').on('click', function() {
            let tableNo = $('#rt-selected-table').val();
            if (!tableNo) return showToast('Table number is missing!', 'warn');
            if (cart.length === 0) return showToast('Your tray is empty! Please add at least one dish first.', 'warn');

            let submittedCart = JSON.parse(JSON.stringify(cart));
            let subStr = $('#rt-sub').text();
            let taxStr = $('#rt-tax').text() || '0.00';
            let totStr = $('#rt-total').text();
            let noteStr = $('#rt-note').val().trim();

            let btn = $(this);
            btn.prop('disabled', true).text('Sending to Kitchen...');

            $.post(ajaxUrl, {
                action: 'rtpos_place_order',
                table_no: tableNo,
                items: submittedCart,
                subtotal: subStr,
                tax: taxStr,
                total: totStr,
                note: noteStr
            }, function(res) {
                if (res.success) {
                    let orderId = (res.data && res.data.id) ? res.data.id : '';
                    let totalItemsCount = 0, confHtml = '';

                    submittedCart.forEach(item => {
                        let qty = intval(item.qty) || 1;
                        let unitPrice = parseFloat(item.unitPrice) || 0;
                        let lineTotal = (unitPrice * qty).toFixed(2);
                        totalItemsCount += qty;

                        let exBreakdownHtml = '';
                        if (item.extras && item.extras.length > 0) {
                            exBreakdownHtml += '<div class="rt-conf-ex-breakdown">';
                            item.extras.forEach(exName => {
                                let foundEx = allExtrasCatalog.find(e => e.name === exName);
                                let exPriceText = foundEx ? `+${cur}${parseFloat(foundEx.price).toFixed(2)}` : '';
                                exBreakdownHtml += `<div class="rt-conf-ex-row"><span>+ ${exName}</span><span class="rt-conf-mono">${exPriceText}</span></div>`;
                            });
                            exBreakdownHtml += '</div>';
                        }

                        confHtml += `
                            <div class="rt-conf-item-block">
                                <div class="rt-conf-row">
                                    <div>
                                        <strong class="rt-conf-item-title">${qty}× ${item.name}</strong>
                                        <div class="rt-conf-sub-line">${cur}${unitPrice.toFixed(2)} each</div>
                                    </div>
                                    <strong class="rt-conf-mono rt-conf-item-amt">${cur}${lineTotal}</strong>
                                </div>
                                ${exBreakdownHtml}
                            </div>`;
                    });

                    $('#conf-order-id').text(orderId ? 'Ticket #' + orderId : 'Order Summary');
                    $('#conf-item-count').text(totalItemsCount + ' Item(s)');
                    $('#conf-items-list').html(confHtml);
                    $('#conf-sub').text(cur + subStr);
                    $('#conf-total').text(cur + totStr);

                    if (taxRate > 0) { $('#conf-tax').text(cur + taxStr); $('#conf-tax-row').show(); }
                    else { $('#conf-tax-row').hide(); }

                    if (noteStr) { $('#conf-note-box').text('Chef Note: ' + noteStr).show(); }
                    else { $('#conf-note-box').hide(); }

                    cart = [];
                    updateCartUI();
                    $('#rt-note').val('');
                    toggleTrayPanel(false);
                    playSuccessChime();
                    $('#rt-success-modal').css('display', 'flex').hide().fadeIn(200);
                    pollTableOrderStatus();
                } else {
                    showToast('Could not send order. Please try again.', 'warn');
                }
                btn.prop('disabled', false).html(sendSvg + '<span>SEND ORDER TO KITCHEN</span>');
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}