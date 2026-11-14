<?php
if (!defined('ABSPATH')) exit;

// Include All Admin Tab Files
require_once RTPOS_PATH . 'admin/tabs/dashboard.php';
require_once RTPOS_PATH . 'admin/tabs/orders.php';
require_once RTPOS_PATH . 'admin/tabs/tables.php';
require_once RTPOS_PATH . 'admin/tabs/items.php';
require_once RTPOS_PATH . 'admin/tabs/categories.php';
require_once RTPOS_PATH . 'admin/tabs/extras.php';
require_once RTPOS_PATH . 'admin/tabs/reports.php';
require_once RTPOS_PATH . 'admin/tabs/staff.php';
require_once RTPOS_PATH . 'admin/tabs/settings.php';

add_action('admin_menu', 'rtpos_register_admin_menu');
function rtpos_register_admin_menu() {
    add_menu_page('Antalya POS', 'Antalya POS', 'manage_options', 'antalya-pos', 'rtpos_render_standalone_admin', 'dashicons-food', 25);
}

add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'toplevel_page_antalya-pos') {
        wp_enqueue_media();
    }
});

function rtpos_render_standalone_admin() {
    global $wpdb;
    $s  = rtpos_get_settings();
    $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard';
    $pending_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rtpos_orders WHERE status IN ('pending','preparing','served')") ?: 0;

    $nav_items = [
        'dashboard'  => ['label' => 'Dashboard',  'svg' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>'],
        'orders'     => ['label' => 'Orders',     'svg' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>', 'badge' => $pending_count],
        'tables'     => ['label' => 'Tables',     'svg' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M6 10v8M18 10v8M9 14h6"/></svg>'],
        'items'      => ['label' => 'Items',      'svg' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>'],
        'categories' => ['label' => 'Categories', 'svg' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>'],
        'extras'     => ['label' => 'Extras',     'svg' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>'],
        'reports'    => ['label' => 'Reports',    'svg' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>'],
        'staff'      => ['label' => 'Staff',      'svg' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>'],
        'settings'   => ['label' => 'Settings',   'svg' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>'],
    ];
    ?>
    <style>
        #adminmenumain, #wpadminbar, #wpfooter, .update-nag, .notice { display: none !important; }
        html.wp-toolbar { padding-top: 0 !important; }
        #wpcontent, #wpbody-content { margin-left: 0 !important; padding: 0 !important; }
        body { background: #f8fafc !important; font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color: #0f172a; margin: 0; }

        .apos-shell { display: flex; min-height: 100vh; position: relative; }
        .apos-sidebar {
            width: 260px; background: #0b1120; color: #fff;
            display: flex; flex-direction: column; position: fixed; top: 0; bottom: 0; left: 0; z-index: 9999;
            border-right: 1px solid rgba(255,255,255,0.06);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .apos-sidebar.sidebar-hidden {
            transform: translateX(-100%);
        }
        .apos-brand {
            padding: 24px 22px; border-bottom: 1px solid rgba(255,255,255,0.07);
            display: flex; align-items: center; gap: 12px;
        }
        .apos-brand-logo {
            width: 40px; height: 40px; border-radius: 10px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 20px; color: #fff;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
        }
        .apos-brand-title { font-size: 19px; font-weight: 800; letter-spacing: 0.8px; color: #fff; margin: 0; line-height: 1.2; }
        .apos-brand-sub { font-size: 11px; color: #94a3b8; font-weight: 500; }

        .apos-nav { flex: 1; padding: 18px 14px; overflow-y: auto; }
        .apos-nav-label { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #475569; font-weight: 700; padding: 0 14px 8px; }
        .apos-nav a {
            display: flex; align-items: center; justify-content: space-between; padding: 11px 14px; margin-bottom: 4px;
            color: #94a3b8; text-decoration: none; border-radius: 10px; font-size: 14px; font-weight: 500; transition: all 0.2s ease;
        }
        .apos-nav a:hover { background: rgba(255,255,255,0.05); color: #f8fafc; }
        .apos-nav a.active {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; font-weight: 600;
            box-shadow: 0 4px 14px rgba(249, 115, 22, 0.35);
        }
        .apos-nav-left { display: flex; align-items: center; gap: 12px; }
        .apos-nav-badge { background: #ef4444; color: #fff; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 20px; }

        .apos-content-wrap { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-width: 0; transition: margin-left 0.25s cubic-bezier(0.16, 1, 0.3, 1); }
        .apos-content-wrap.sidebar-hidden { margin-left: 0 !important; }

        /* Professional Fixed Toggle Button with Visible Text & Dynamic Icon */
        .apos-sidebar-toggle {
            position: fixed; bottom: 20px; left: 20px;
            background: #1e293b; border: 1.5px solid #334155; color: #f8fafc;
            padding: 0 14px; height: 45px; border-radius: 12px; display: inline-flex;
            align-items: center; gap: 8px; cursor: pointer; transition: all 0.18s ease;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35); z-index: 100000;
            font-size: 13px; font-weight: 800; letter-spacing: 0.3px;
        }
        .apos-sidebar-toggle:hover { 
            background: #f97316; border-color: #f97316; color: #fff; 
            transform: translateY(-1px);
        }

        .pulse-dot { height: 8px; width: 8px; background-color: #10b981; border-radius: 50%; display: inline-block; margin-right: 8px; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); } 70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }

        .apos-main { padding: 30px 34px; max-width: 1440px; width: 100%; box-sizing: border-box; }
        .apos-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
        .apos-header h1 { margin: 0; font-size: 24px; color: #0f172a; font-weight: 800; letter-spacing: -0.5px; }
        .apos-header p { margin: 4px 0 0; color: #64748b; font-size: 13.5px; }

        .apos-card {
            background: #fff; padding: 24px; border-radius: 16px; border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03), 0 4px 12px rgba(15, 23, 42, 0.02); margin-bottom: 24px;
        }
        .apos-btn {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; border: none;
            padding: 10px 18px; border-radius: 9px; font-weight: 600; font-size: 13.5px; cursor: pointer;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: 0.2s;
            box-shadow: 0 2px 8px rgba(249, 115, 22, 0.25);
        }
        .apos-btn:hover { opacity: 0.92; color: #fff; transform: translateY(-1px); }
        .apos-btn-secondary { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .apos-btn-secondary:hover { background: #e2e8f0; color: #0f172a; }
        .apos-btn-green { background: #10b981; color: #fff; border: none; padding: 6px 12px; border-radius: 7px; font-weight: 600; font-size: 12.5px; cursor: pointer; text-decoration: none; }
        .apos-btn-dark { background: #0f172a; color: #fff; border: none; padding: 6px 12px; border-radius: 7px; font-weight: 600; font-size: 12.5px; cursor: pointer; }
        
        .apos-label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; }
        .apos-input {
            width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 9px;
            font-size: 14px; transition: 0.2s; box-sizing: border-box; background: #fff;
        }
        .apos-input:focus { outline: none; border-color: #f97316; box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15); }

        .apos-table {
            width: 100%; border-collapse: collapse; background: #fff; border-radius: 16px; overflow: hidden;
            border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        }
        .apos-table th { background: #f8fafc; text-align: left; padding: 14px 18px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; border-bottom: 1px solid #e2e8f0; }
        .apos-table td { padding: 14px 18px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; vertical-align: middle; }
        .apos-table tr:last-child td { border-bottom: none; }
        .apos-table tr:hover td { background: #f8fafc; }

        .apos-alert { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 13px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 14px; }

        .apos-pills { display: flex; gap: 8px; margin-bottom: 18px; flex-wrap: wrap; }
        .apos-pill { padding: 7px 15px; border-radius: 20px; background: #fff; border: 1px solid #e2e8f0; color: #475569; font-size: 13px; font-weight: 600; text-decoration: none; transition: 0.2s; }
        .apos-pill.active, .apos-pill:hover { background: #0f172a; color: #fff; border-color: #0f172a; }

        #apos-waiter-alerts { margin-bottom: 20px; display: flex; flex-direction: column; gap: 10px; }
        .waiter-call-box {
            background: linear-gradient(90deg, #dc2626 0%, #ef4444 100%); color: #fff; padding: 14px 22px;
            border-radius: 12px; display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 8px 20px rgba(220, 38, 38, 0.35); animation: shake 0.45s ease-in-out;
        }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }

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
        <!-- Sidebar Toggle Button with Text and Dynamic Icon -->
        <button type="button" id="apos-toggle-sidebar" class="apos-sidebar-toggle" title="Toggle Menu">
            <span id="toggle-icon-wrap">
                <svg id="icon-close" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                <svg id="icon-menu" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </span>
            <span id="toggle-text">Hide Menu</span>
        </button>

        <aside class="apos-sidebar" id="apos-sidebar">
            <div class="apos-brand">
                <div class="apos-brand-logo"><?php echo esc_html(substr($s['restaurant_name'], 0, 1)); ?></div>
                <div>
                    <h2 class="apos-brand-title"><?php echo esc_html($s['restaurant_name']); ?></h2>
                    <div class="apos-brand-sub">POS & Kitchen Control</div>
                </div>
            </div>

            <nav class="apos-nav">
                <div class="apos-nav-label">Main Menu</div>
                <?php foreach ($nav_items as $slug => $item) : 
                    $active = ($tab === $slug) ? 'active' : '';
                    $url = admin_url('admin.php?page=antalya-pos&tab=' . $slug);
                ?>
                    <a href="<?php echo esc_url($url); ?>" class="<?php echo $active; ?>">
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

        // Restore Sidebar Hidden State from LocalStorage
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
                                <div style="font-size:15.5px; font-weight:700;">
                                    🔔 WAITER REQUEST: <span style="background:#fff; color:#dc2626; padding:2px 8px; border-radius:5px; margin:0 4px;">Table #${call.table_no}</span> is calling for assistance!
                                </div>
                                <button class="apos-dismiss-call" data-id="${call.id}" style="background:#fff; color:#dc2626; border:none; padding:7px 15px; border-radius:7px; font-weight:700; cursor:pointer;">
                                    ✔ Attend & Dismiss
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