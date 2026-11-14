<?php
/**
 * Antalya POS Admin Panel Router & Framework
 * 
 * @package Antalya_POS
 * @since 6.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include All Admin Tab Files Safely
$apos_tabs_path = RTPOS_PATH . 'admin/tabs/';
require_once $apos_tabs_path . 'dashboard.php';
require_once $apos_tabs_path . 'orders.php';
require_once $apos_tabs_path . 'tables.php';
require_once $apos_tabs_path . 'items.php';
require_once $apos_tabs_path . 'categories.php';
require_once $apos_tabs_path . 'extras.php';
require_once $apos_tabs_path . 'reports.php';
require_once $apos_tabs_path . 'staff.php';
require_once $apos_tabs_path . 'settings.php';

/**
 * Register Standalone Admin Menu with strict capability checks
 */
add_action('admin_menu', 'rtpos_register_admin_menu');
function rtpos_register_admin_menu() {
    add_menu_page(
        __('Antalya POS', 'antalya-pos'),
        __('Antalya POS', 'antalya-pos'),
        'manage_options',
        'antalya-pos',
        'rtpos_render_standalone_admin',
        'dashicons-food',
        25
    );
}

/**
 * Enqueue WordPress Media Uploader securely on POS admin page only
 */
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'toplevel_page_antalya-pos') {
        wp_enqueue_media();
    }
});

/**
 * Render Standalone Admin Panel with Refined Professional UI/UX
 */
function rtpos_render_standalone_admin() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'antalya-pos'));
    }

    global $wpdb;
    $s   = rtpos_get_settings();
    $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard';
    
    $orders_table = $wpdb->prefix . 'rtpos_orders';
    $pending_count = 0;
    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $orders_table)) === $orders_table) {
        $pending_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$orders_table} WHERE status IN ('pending','preparing','served')");
    }

    $nav_items = [
        'dashboard'  => [
            'label' => __('Dashboard', 'antalya-pos'),  
            'svg'   => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>'
        ],
        'orders'     => [
            'label' => __('Orders', 'antalya-pos'),     
            'svg'   => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>', 
            'badge' => $pending_count
        ],
        'tables'     => [
            'label' => __('Tables', 'antalya-pos'),     
            'svg'   => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M6 10v8M18 10v8M9 14h6"/></svg>'
        ],
        'items'      => [
            'label' => __('Items', 'antalya-pos'),      
            'svg'   => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>'
        ],
        'categories' => [
            'label' => __('Categories', 'antalya-pos'), 
            'svg'   => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>'
        ],
        'extras'     => [
            'label' => __('Extras', 'antalya-pos'),     
            'svg'   => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>'
        ],
        'reports'    => [
            'label' => __('Reports', 'antalya-pos'),    
            'svg'   => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>'
        ],
        'staff'      => [
            'label' => __('Staff', 'antalya-pos'),      
            'svg'   => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>'
        ],
        'settings'   => [
            'label' => __('Settings', 'antalya-pos'),   
            'svg'   => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>'
        ],
    ];
    ?>
    <style>
        #adminmenumain, #wpadminbar, #wpfooter, .update-nag, .notice { display: none !important; }
        html.wp-toolbar { padding-top: 0 !important; }
        #wpcontent, #wpbody-content { margin-left: 0 !important; padding: 0 !important; }
        body { background: #f1f5f9 !important; font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color: #0f172a; margin: 0; -webkit-font-smoothing: antialiased; }

        .apos-shell { display: flex; min-height: 100vh; position: relative; }
        
        /* Modern Sidebar UI */
        .apos-sidebar {
            width: 270px; background: #070b14; color: #fff;
            display: flex; flex-direction: column; position: fixed; top: 0; bottom: 0; left: 0; z-index: 9999;
            border-right: 1px solid rgba(255,255,255,0.08);
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 10px 0 30px rgba(0,0,0,0.15);
        }
        .apos-sidebar.sidebar-hidden {
            transform: translateX(-100%);
        }
        .apos-brand {
            padding: 26px 22px; border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex; align-items: center; gap: 14px;
        }
        .apos-brand-logo {
            width: 42px; height: 42px; border-radius: 12px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 21px; color: #fff;
            box-shadow: 0 6px 16px rgba(249, 115, 22, 0.45);
        }
        .apos-brand-title { font-size: 18.5px; font-weight: 850; letter-spacing: 0.5px; color: #fff; margin: 0; line-height: 1.2; }
        .apos-brand-sub { font-size: 11.5px; color: #64748b; font-weight: 600; margin-top: 2px; }

        .apos-nav { flex: 1; padding: 20px 14px; overflow-y: auto; }
        .apos-nav-label { font-size: 11px; text-transform: uppercase; letter-spacing: 1.2px; color: #475569; font-weight: 800; padding: 0 14px 10px; }
        .apos-nav a {
            display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; margin-bottom: 5px;
            color: #94a3b8; text-decoration: none; border-radius: 12px; font-size: 14px; font-weight: 600; transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .apos-nav a:hover { background: rgba(255,255,255,0.06); color: #f8fafc; transform: translateX(3px); }
        .apos-nav a.active {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; font-weight: 700;
            box-shadow: 0 6px 18px rgba(249, 115, 22, 0.35);
        }
        .apos-nav-left { display: flex; align-items: center; gap: 12px; }
        .apos-nav-badge { background: #ef4444; color: #fff; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 20px; box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4); }

        .apos-content-wrap { margin-left: 270px; flex: 1; display: flex; flex-direction: column; min-width: 0; transition: margin-left 0.28s cubic-bezier(0.16, 1, 0.3, 1); }
        .apos-content-wrap.sidebar-hidden { margin-left: 0 !important; }

        /* Professional Fixed Toggle Button with Glassmorphism */
        .apos-sidebar-toggle {
            position: fixed; bottom: 24px; left: 24px;
            background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(12px);
            border: 1.5px solid rgba(255, 255, 255, 0.12); color: #f8fafc;
            padding: 0 16px; height: 48px; border-radius: 14px; display: inline-flex;
            align-items: center; gap: 10px; cursor: pointer; transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4); z-index: 100000;
            font-size: 13.5px; font-weight: 800; letter-spacing: 0.3px;
        }
        .apos-sidebar-toggle:hover { 
            background: #f97316; border-color: #f97316; color: #fff; 
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(249, 115, 22, 0.45);
        }

        .pulse-dot { height: 8px; width: 8px; background-color: #10b981; border-radius: 50%; display: inline-block; margin-right: 8px; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }

        .apos-main { padding: 36px 40px; max-width: 1480px; width: 100%; box-sizing: border-box; }
        .apos-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 14px; }
        .apos-header h1 { margin: 0; font-size: 26px; color: #0f172a; font-weight: 850; letter-spacing: -0.6px; }
        .apos-header p { margin: 6px 0 0; color: #64748b; font-size: 14px; font-weight: 500; }

        /* Modern Refined Card UI */
        .apos-card {
            background: #fff; padding: 28px; border-radius: 20px; border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04), 0 2px 6px -1px rgba(15, 23, 42, 0.02); margin-bottom: 28px;
            transition: box-shadow 0.2s ease;
        }
        .apos-card:hover {
            box-shadow: 0 10px 30px -4px rgba(15, 23, 42, 0.07);
        }

        /* Buttons with Gradient & Soft Glow */
        .apos-btn {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; border: none;
            padding: 11px 20px; border-radius: 12px; font-weight: 700; font-size: 14px; cursor: pointer;
            text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(249, 115, 22, 0.3);
        }
        .apos-btn:hover { opacity: 0.95; color: #fff; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4); }
        
        .apos-btn-secondary { background: #f1f5f9; color: #334155; border: 1.5px solid #cbd5e1; padding: 9px 16px; border-radius: 11px; font-weight: 700; font-size: 13.5px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; }
        .apos-btn-secondary:hover { background: #e2e8f0; color: #0f172a; border-color: #94a3b8; }
        
        .apos-btn-green { background: #10b981; color: #fff; border: none; padding: 8px 14px; border-radius: 9px; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: none; box-shadow: 0 3px 10px rgba(16, 185, 129, 0.25); }
        .apos-btn-dark { background: #0f172a; color: #fff; border: none; padding: 8px 14px; border-radius: 9px; font-weight: 700; font-size: 13px; cursor: pointer; }
        
        .apos-label { display: block; font-size: 13px; font-weight: 750; color: #334155; margin-bottom: 7px; letter-spacing: 0.2px; }
        .apos-input {
            width: 100%; padding: 12px 16px; border: 1.5px solid #cbd5e1; border-radius: 12px;
            font-size: 14.5px; font-weight: 500; transition: all 0.2s ease; box-sizing: border-box; background: #fff; color: #0f172a;
        }
        .apos-input:focus { outline: none; border-color: #f97316; box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.12); background: #fff; }

        /* Modern Clean Table */
        .apos-table {
            width: 100%; border-collapse: collapse; background: #fff; border-radius: 18px; overflow: hidden;
            border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.02);
        }
        .apos-table th { background: #f8fafc; text-align: left; padding: 16px 20px; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; border-bottom: 1.5px solid #e2e8f0; }
        .apos-table td { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; font-size: 14.5px; color: #1e293b; vertical-align: middle; }
        .apos-table tr:last-child td { border-bottom: none; }
        .apos-table tr:hover td { background: #f8fafc; }

        .apos-alert { background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; padding: 14px 20px; border-radius: 14px; margin-bottom: 24px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08); }

        #apos-waiter-alerts { margin-bottom: 24px; display: flex; flex-direction: column; gap: 12px; }
        .waiter-call-box {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); color: #fff; padding: 16px 24px;
            border-radius: 16px; display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.4); animation: shake 0.45s ease-in-out;
        }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-6px); } 75% { transform: translateX(6px); } }

        #rtpos-print-area { display: none; }
        @media print {
            body * { visibility: hidden; }
            #rtpos-print-area, #rtpos-print-area * { visibility: visible; }
            #rtpos-print-area {
                display: block !important; position: fixed; left: 0; top: 0; width: 76mm;
                font-family: 'Courier New', monospace; font-size: 13px; color: #000; background: #fff;
            }
            @page { margin: 0; }
        }
    </style>

    <div class="apos-shell">
        <!-- Sidebar Toggle Button with Text and Dynamic SVG Icon -->
        <button type="button" id="apos-toggle-sidebar" class="apos-sidebar-toggle" title="<?php esc_attr_e('Toggle Menu', 'antalya-pos'); ?>">
            <span id="toggle-icon-wrap">
                <svg id="icon-close" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                <svg id="icon-menu" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </span>
            <span id="toggle-text"><?php esc_html_e('Hide Menu', 'antalya-pos'); ?></span>
        </button>

        <aside class="apos-sidebar" id="apos-sidebar">
            <div class="apos-brand">
                <div class="apos-brand-logo"><?php echo esc_html(substr($s['restaurant_name'], 0, 1)); ?></div>
                <div>
                    <h2 class="apos-brand-title"><?php echo esc_html($s['restaurant_name']); ?></h2>
                    <div class="apos-brand-sub"><?php esc_html_e('POS & Kitchen Control', 'antalya-pos'); ?></div>
                </div>
            </div>

            <nav class="apos-nav">
                <div class="apos-nav-label"><?php esc_html_e('Main Menu', 'antalya-pos'); ?></div>
                <?php foreach ($nav_items as $slug => $item) : 
                    $active = ($tab === $slug) ? 'active' : '';
                    $url = admin_url('admin.php?page=antalya-pos&tab=' . $slug);
                ?>
                    <a href="<?php echo esc_url($url); ?>" class="<?php echo esc_attr($active); ?>">
                        <span class="apos-nav-left">
                            <?php echo $item['svg']; ?>
                            <span><?php echo esc_html($item['label']); ?></span>
                        </span>
                        <?php if (!empty($item['badge']) && $item['badge'] > 0) : ?>
                            <span class="apos-nav-badge"><?php echo intval($item['badge']); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <div class="apos-content-wrap" id="apos-content-wrap">
            <main class="apos-main">
                <div id="apos-waiter-alerts"></div>
                <?php
                switch ($tab) {
                    case 'orders':    rtpos_view_orders($s); break;
                    case 'tables':    rtpos_view_tables($s); break;
                    case 'items':     rtpos_view_items($s); break;
                    case 'categories': rtpos_view_categories(); break;
                    case 'extras':    rtpos_view_extras($s); break;
                    case 'reports':   rtpos_view_reports($s); break;
                    case 'staff':     rtpos_view_staff(); break;
                    case 'settings':  rtpos_view_settings(); break;
                    default:          rtpos_view_dashboard($s); break;
                }
                ?>
            </main>
        </div>
    </div>

    <div id="rtpos-print-area"></div>

    <script>
    jQuery(document).ready(function($) {
        function updateToggleUI(isHidden) {
            if (isHidden) {
                $('#icon-close').hide();
                $('#icon-menu').show();
                $('#toggle-text').text('Show Menu');
            } else {
                $('#icon-menu').hide();
                $('#icon-close').show();
                $('#toggle-text').text('Hide Menu');
            }
        }

        if (localStorage.getItem('apos_sidebar_hidden') === '1') {
            $('#apos-sidebar').addClass('sidebar-hidden');
            $('#apos-content-wrap').addClass('sidebar-hidden');
            updateToggleUI(true);
        } else {
            updateToggleUI(false);
        }

        $(document).on('click', '#apos-toggle-sidebar', function() {
            $('#apos-sidebar').toggleClass('sidebar-hidden');
            $('#apos-content-wrap').toggleClass('sidebar-hidden');
            let isHidden = $('#apos-sidebar').hasClass('sidebar-hidden');
            localStorage.setItem('apos_sidebar_hidden', isHidden ? '1' : '0');
            updateToggleUI(isHidden);
        });

        let lastCallCount = 0;
        function playAlertBeep() {
            try {
                let ctx = new (window.AudioContext || window.webkitAudioContext)();
                let osc = ctx.createOscillator();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                osc.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.3);
            } catch(e) {}
        }

        function pollWaiterCalls() {
            $.post(ajaxurl, { action: 'rtpos_check_waiter_calls' }, function(res) {
                if (res.success && res.data.length > 0) {
                    if (res.data.length > lastCallCount) playAlertBeep();
                    lastCallCount = res.data.length;

                    let html = '';
                    res.data.forEach(call => {
                        html += `
                            <div class="waiter-call-box">
                                <div style="font-size:15px; font-weight:750; display:inline-flex; align-items:center; gap:9px;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                                    WAITER REQUEST: <span style="background:#fff; color:#dc2626; padding:3px 10px; border-radius:6px; margin:0 4px; font-weight:800;">Table #${call.table_no}</span> is calling for assistance!
                                </div>
                                <button class="apos-dismiss-call" data-id="${call.id}" style="background:#fff; color:#dc2626; border:none; padding:8px 16px; border-radius:9px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: 0.15s;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Attend & Dismiss
                                </button>
                            </div>`;
                    });
                    $('#apos-waiter-alerts').html(html);
                } else {
                    lastCallCount = 0;
                    $('#apos-waiter-alerts').empty();
                }
            });
        }

        $(document).on('click', '.apos-dismiss-call', function() {
            $.post(ajaxurl, { action: 'rtpos_dismiss_waiter_call', id: $(this).data('id') }, pollWaiterCalls);
        });

        pollWaiterCalls();
        setInterval(pollWaiterCalls, 4000);
    });
    </script>
    <?php
}