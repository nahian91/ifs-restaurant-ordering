<?php
if (!defined('ABSPATH')) {
    exit;
}

function rtpos_view_dashboard($s) {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'antalya-pos'));
    }

    global $wpdb;
    $cur      = esc_html($s['currency'] ?? '৳');
    $today    = current_time('Y-m-d');
    $t_orders = $wpdb->prefix . 'rtpos_orders';
    $t_tables = $wpdb->prefix . 'rtpos_tables';

    // Reusable SVG Icons
    $svg_calendar  = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
    $svg_monitor   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>';
    $svg_wallet    = '<svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>';
    $svg_flame     = '<svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>';
    $svg_grid      = '<svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>';
    $svg_grid_sm   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>';
    $svg_trend     = '<svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>';
    $svg_award     = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>';
    $svg_receipt   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>';
    $svg_bar_chart = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>';
    $svg_arrow_rt  = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';

    // Dynamic Time-based Greeting & Admin Name
    $current_user = wp_get_current_user();
    $admin_name   = $current_user->display_name ?: __('Admin', 'antalya-pos');
    $hour         = (int) current_time('G');
    if ($hour < 12) {
        $greeting = __('Good Morning', 'antalya-pos');
    } elseif ($hour < 18) {
        $greeting = __('Good Afternoon', 'antalya-pos');
    } else {
        $greeting = __('Good Evening', 'antalya-pos');
    }

    // 1. Core KPI Metrics (Secured with $wpdb->prepare)
    $today_sales    = (float) ($wpdb->get_var($wpdb->prepare("SELECT SUM(total_amount) FROM {$t_orders} WHERE DATE(created_at) = %s AND status = %s", $today, 'completed')) ?: 0);
    $today_paid_cnt = (int) ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$t_orders} WHERE DATE(created_at) = %s AND status = %s", $today, 'completed')) ?: 0);
    $avg_order_val  = $today_paid_cnt > 0 ? ($today_sales / $today_paid_cnt) : 0;

    $active_orders  = (int) ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$t_orders} WHERE status != %s", 'completed')) ?: 0);
    $total_tables   = (int) ($wpdb->get_var("SELECT COUNT(*) FROM {$t_tables}") ?: 0);
    $occupied_list  = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT table_no FROM {$t_orders} WHERE status != %s", 'completed')) ?: [];
    $occupied_count = count($occupied_list);
    $free_tables    = max(0, $total_tables - $occupied_count);

    // 2. Last 7 Days Revenue Data for Chart
    $chart_days = [];
    $max_rev    = 1;
    $now_ts     = current_time('timestamp');
    for ($i = 6; $i >= 0; $i--) {
        $d   = date('Y-m-d', strtotime("-$i days", $now_ts));
        $rev = (float) ($wpdb->get_var($wpdb->prepare("SELECT SUM(total_amount) FROM {$t_orders} WHERE DATE(created_at) = %s AND status = %s", $d, 'completed')) ?: 0);
        if ($rev > $max_rev) {
            $max_rev = $rev;
        }
        $chart_days[] = [
            'label'    => date('D', strtotime($d)),
            'date'     => date('M d', strtotime($d)),
            'raw_date' => $d,
            'rev'      => $rev,
            'is_today' => ($d === $today),
        ];
    }

    // 3. Top Selling Items (From last 100 orders)
    $recent_all  = $wpdb->get_results("SELECT items FROM {$t_orders} ORDER BY id DESC LIMIT 100") ?: [];
    $item_counts = [];
    foreach ($recent_all as $row) {
        $decoded = json_decode($row->items, true);
        if (is_array($decoded)) {
            foreach ($decoded as $it) {
                $name = isset($it['name']) ? sanitize_text_field($it['name']) : __('Unknown Item', 'antalya-pos');
                $qty  = isset($it['qty']) ? intval($it['qty']) : 0;
                if (!isset($item_counts[$name])) {
                    $item_counts[$name] = 0;
                }
                $item_counts[$name] += $qty;
            }
        }
    }
    arsort($item_counts);
    $top_items = array_slice($item_counts, 0, 5, true);

    // 4. Recent Orders & Tables
    $recent_orders = $wpdb->get_results("SELECT * FROM {$t_orders} ORDER BY id DESC LIMIT 6") ?: [];
    $all_tables    = $wpdb->get_results("SELECT * FROM {$t_tables} ORDER BY id ASC") ?: [];

    // Pre-escaped Action URLs
    $orders_url  = esc_url(admin_url('admin.php?page=antalya-pos&tab=orders'));
    $reports_url = esc_url(admin_url('admin.php?page=antalya-pos&tab=reports'));
    $tables_url  = esc_url(admin_url('admin.php?page=antalya-pos&tab=tables'));
    ?>

    <style>
        .dash-full-wrap { width: 100%; box-sizing: border-box; }
        .apos-full-card { width: 100%; max-width: 100%; box-sizing: border-box; margin: 0; }

        /* Welcome Banner & Live Clock */
        .dash-welcome-card {
            background: linear-gradient(135deg, #090d16 0%, #1e293b 100%);
            color: #fff; padding: 28px 32px; border-radius: 20px; margin-bottom: 28px;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;
            box-shadow: 0 12px 30px -6px rgba(9, 13, 22, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.08); width: 100%; box-sizing: border-box;
            position: relative; overflow: hidden;
        }
        .dash-welcome-card::after {
            content: ""; position: absolute; right: -40px; top: -60px; width: 240px; height: 240px;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.16) 0%, transparent 70%);
            pointer-events: none;
        }
        .dash-welcome-title { margin: 0; font-size: 25px; font-weight: 850; color: #fff; letter-spacing: -0.5px; }
        .dash-welcome-title span { color: #f97316; }
        .dash-welcome-sub { margin: 6px 0 0; color: #94a3b8; font-size: 14px; font-weight: 500; }
        .dash-welcome-right { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; z-index: 1; }
        .dash-clock-box {
            background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 12px 20px; border-radius: 14px; text-align: right; backdrop-filter: blur(8px);
        }
        .dash-clock-time {
            font-family: 'Courier New', monospace; font-size: 23px; font-weight: 800;
            color: #f8fafc; letter-spacing: 1px; line-height: 1.1;
        }
        .dash-clock-date {
            font-size: 12px; color: #94a3b8; font-weight: 600; margin-top: 5px;
            display: flex; align-items: center; justify-content: flex-end; gap: 6px;
        }
        .dash-monitor-btn {
            padding: 14px 22px; display: inline-flex; align-items: center; gap: 9px; font-weight: 700;
        }

        /* Top 4 KPI Cards */
        .dash-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(235px, 1fr)); gap: 22px; margin-bottom: 28px; width: 100%; }
        .dash-kpi {
            background: #fff; padding: 24px; border-radius: 18px; border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03); display: flex; justify-content: space-between; align-items: flex-start;
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease;
        }
        .dash-kpi:hover { transform: translateY(-3px); box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.08); }
        .dash-kpi-title { font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; }
        .dash-kpi-val { font-size: 28px; font-weight: 850; color: #0f172a; margin: 8px 0 6px; letter-spacing: -0.6px; }
        .dash-kpi-total { font-size: 16px; color: #94a3b8; font-weight: 700; }
        .dash-kpi-sub { font-size: 12.5px; font-weight: 650; }
        .dash-sub-green { color: #10b981; }
        .dash-sub-amber { color: #f59e0b; }
        .dash-sub-orange { color: #f97316; }
        .dash-sub-blue { color: #3b82f6; }

        .dash-icon-box { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .dash-ico-green { background: #ecfdf5; color: #10b981; border: 1px solid #d1fae5; }
        .dash-ico-amber { background: #fffbeb; color: #f59e0b; border: 1px solid #fef3c7; }
        .dash-ico-orange { background: #fff7ed; color: #f97316; border: 1px solid #ffedd5; }
        .dash-ico-blue { background: #eff6ff; color: #3b82f6; border: 1px solid #dbeafe; }

        /* Split Grids & Card Headers */
        .dash-split-grid { display: grid; grid-template-columns: 1.6fr 1fr; gap: 24px; width: 100%; }
        .dash-split-mb { margin-bottom: 24px; }
        @media (max-width: 960px) { .dash-split-grid { grid-template-columns: 1fr; } }

        .dash-card-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
        .dash-card-head-chart { margin-bottom: 12px; }
        .dash-card-head-floor { margin-bottom: 16px; }
        .dash-card-title { margin: 0; font-size: 16.5px; font-weight: 850; color: #0f172a; display: flex; align-items: center; gap: 9px; }
        .dash-title-ico { color: #f97316; display: inline-flex; }
        .dash-card-sub { font-size: 12.5px; color: #64748b; display: block; margin-top: 2px; }
        .dash-link-orange { font-size: 13px; color: #f97316; font-weight: 750; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; transition: color 0.15s; }
        .dash-link-orange:hover { color: #ea580c; text-decoration: underline; }
        .dash-badge-popular { font-size: 11.5px; color: #475569; background: #f1f5f9; padding: 4px 10px; border-radius: 8px; font-weight: 750; }

        /* 7-Day Bar Chart */
        .dash-chart-wrap { display: flex; align-items: flex-end; justify-content: space-between; height: 190px; padding-top: 24px; gap: 14px; border-bottom: 1.5px solid #e2e8f0; }
        .dash-bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: flex-end; text-decoration: none; }
        .dash-bar {
            width: 100%; max-width: 46px; border-radius: 10px 10px 0 0; background: #cbd5e1;
            transition: height 0.45s cubic-bezier(0.16, 1, 0.3, 1), background 0.2s; position: relative; min-height: 6px;
        }
        .dash-bar.today { background: linear-gradient(180deg, #f97316 0%, #ea580c 100%); box-shadow: 0 4px 14px rgba(249, 115, 22, 0.35); }
        .dash-bar-col:hover .dash-bar { background: #0f172a; }
        .dash-bar-val { font-size: 11px; font-weight: 750; color: #475569; margin-bottom: 6px; }
        .dash-bar-lbl { font-size: 12px; font-weight: 750; color: #64748b; margin-top: 10px; }
        .dash-bar-lbl.is-today { color: #f97316; font-weight: 850; }

        /* Top Selling Dishes List */
        .dash-empty-msg { color: #94a3b8; text-align: center; padding: 44px 0; margin: 0; font-weight: 500; }
        .dash-top-list { display: flex; flex-direction: column; gap: 13px; }
        .dash-top-row { display: flex; justify-content: space-between; align-items: center; padding-bottom: 11px; border-bottom: 1px dashed #e2e8f0; }
        .dash-top-row:last-child { border-bottom: none; padding-bottom: 0; }
        .dash-top-left { display: flex; align-items: center; gap: 11px; }
        .dash-rank-badge {
            width: 26px; height: 26px; border-radius: 8px; background: #f1f5f9; color: #475569;
            font-size: 12px; font-weight: 800; display: flex; align-items: center; justify-content: center;
        }
        .dash-rank-badge.rank-1 { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
        .dash-dish-name { font-size: 14px; color: #1e293b; font-weight: 700; }
        .dash-sold-pill { font-size: 12.5px; font-weight: 750; color: #f97316; background: #fff7ed; padding: 4px 10px; border-radius: 20px; border: 1px solid #ffedd5; }

        /* Recent Orders Table */
        .dash-recent-table { width: 100%; box-shadow: none; border: 1px solid #e2e8f0; border-radius: 14px; }
        .dash-table-empty { text-align: center; color: #94a3b8; padding: 28px; font-weight: 500; }
        .dash-ord-link { font-weight: 850; color: #0f172a; text-decoration: none; transition: color 0.15s; }
        .dash-ord-link:hover { color: #f97316; }
        .dash-tbl-name { font-weight: 700; color: #334155; }
        .dash-ord-time { color: #64748b; font-size: 13px; font-weight: 500; }

        /* Status Pills */
        .st-pill { padding: 4px 11px; border-radius: 20px; font-size: 11.5px; font-weight: 750; text-transform: capitalize; display: inline-block; }
        .st-pending { background: #fef3c7; color: #b45309; }
        .st-preparing { background: #dbeafe; color: #1d4ed8; }
        .st-served { background: #f3e8ff; color: #7e22ce; }
        .st-completed { background: #d1fae5; color: #047857; }

        /* Floor Table Pills */
        .dash-no-tables { color: #94a3b8; font-size: 13.5px; }
        .floor-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(98px, 1fr)); gap: 11px; }
        .floor-tile {
            padding: 13px 10px; border-radius: 14px; text-align: center; font-weight: 800; font-size: 13.5px;
            border: 1.5px solid #e2e8f0; transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); text-decoration: none; display: block;
        }
        .floor-tile:hover { transform: translateY(-2px); box-shadow: 0 6px 14px rgba(15, 23, 42, 0.08); }
        .floor-free { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .floor-busy { background: #fff7ed; border-color: #fed7aa; color: #c2410c; }
        .floor-status-row { font-size: 11px; font-weight: 750; margin-top: 5px; opacity: 0.95; display: flex; align-items: center; justify-content: center; gap: 5px; }
        .dot-sm { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
        .dot-free { background: #16a34a; }
        .dot-busy { background: #ea580c; }

        .dash-shortcode-box {
            margin-top: 20px; padding: 13px 16px; background: #f8fafc;
            border-radius: 12px; border: 1px solid #e2e8f0; font-size: 13px; color: #475569;
            display: flex; align-items: center; justify-content: space-between;
        }
        .dash-shortcode-code { color: #f97316; font-weight: 800; background: #fff7ed; padding: 3px 8px; border-radius: 6px; border: 1px solid #ffedd5; }
    </style>

    <div class="dash-full-wrap">
        <!-- Welcome Message & Live Clock Banner -->
        <div class="dash-welcome-card">
            <div>
                <h1 class="dash-welcome-title"><?php echo esc_html($greeting); ?>, <span><?php echo esc_html($admin_name); ?></span></h1>
                <p class="dash-welcome-sub">
                    <?php
                    printf(
                        /* translators: %s: restaurant name */
                        esc_html__('Welcome back to %s POS — Monitor live orders, tables, and daily revenue.', 'antalya-pos'),
                        '<strong>' . esc_html($s['restaurant_name'] ?? 'Antalya') . '</strong>'
                    );
                    ?>
                </p>
            </div>

            <div class="dash-welcome-right">
                <div class="dash-clock-box">
                    <div class="dash-clock-time" id="dash-live-clock"><?php echo esc_html(current_time('h:i:s A')); ?></div>
                    <div class="dash-clock-date">
                        <?php echo $svg_calendar; ?>
                        <span><?php echo esc_html(date('l, F j, Y', $now_ts)); ?></span>
                    </div>
                </div>
                <a href="<?php echo $orders_url; ?>" class="apos-btn dash-monitor-btn">
                    <?php echo $svg_monitor; ?>
                    <span><?php esc_html_e('Live Kitchen Monitor', 'antalya-pos'); ?></span>
                </a>
            </div>
        </div>

        <!-- Top 4 KPI Cards -->
        <div class="dash-kpi-grid">
            <div class="dash-kpi">
                <div>
                    <div class="dash-kpi-title"><?php esc_html_e("Today's Paid Revenue", 'antalya-pos'); ?></div>
                    <div class="dash-kpi-val"><?php echo $cur . esc_html(number_format($today_sales, 2)); ?></div>
                    <div class="dash-kpi-sub dash-sub-green">
                        <?php
                        printf(
                            /* translators: %d: number of paid bills */
                            esc_html__('From %d paid bill(s)', 'antalya-pos'),
                            intval($today_paid_cnt)
                        );
                        ?>
                    </div>
                </div>
                <div class="dash-icon-box dash-ico-green">
                    <?php echo $svg_wallet; ?>
                </div>
            </div>

            <div class="dash-kpi">
                <div>
                    <div class="dash-kpi-title"><?php esc_html_e('Active Kitchen Orders', 'antalya-pos'); ?></div>
                    <div class="dash-kpi-val"><?php echo intval($active_orders); ?></div>
                    <div class="dash-kpi-sub dash-sub-amber"><?php esc_html_e('Pending / Cooking / Served', 'antalya-pos'); ?></div>
                </div>
                <div class="dash-icon-box dash-ico-amber">
                    <?php echo $svg_flame; ?>
                </div>
            </div>

            <div class="dash-kpi">
                <div>
                    <div class="dash-kpi-title"><?php esc_html_e('Table Occupancy', 'antalya-pos'); ?></div>
                    <div class="dash-kpi-val"><?php echo intval($occupied_count); ?> <span class="dash-kpi-total">/ <?php echo intval($total_tables); ?></span></div>
                    <div class="dash-kpi-sub dash-sub-orange">
                        <?php
                        printf(
                            /* translators: %d: number of available tables */
                            esc_html__('%d table(s) available', 'antalya-pos'),
                            intval($free_tables)
                        );
                        ?>
                    </div>
                </div>
                <div class="dash-icon-box dash-ico-orange">
                    <?php echo $svg_grid; ?>
                </div>
            </div>

            <div class="dash-kpi">
                <div>
                    <div class="dash-kpi-title"><?php esc_html_e('Avg. Ticket Size', 'antalya-pos'); ?></div>
                    <div class="dash-kpi-val"><?php echo $cur . esc_html(number_format($avg_order_val, 2)); ?></div>
                    <div class="dash-kpi-sub dash-sub-blue"><?php esc_html_e('Per completed order today', 'antalya-pos'); ?></div>
                </div>
                <div class="dash-icon-box dash-ico-blue">
                    <?php echo $svg_trend; ?>
                </div>
            </div>
        </div>

        <!-- Middle Section: 7-Day Revenue Chart + Top Selling Items -->
        <div class="dash-split-grid dash-split-mb">
            <!-- Sales Chart Card -->
            <div class="apos-card apos-full-card">
                <div class="dash-card-head dash-card-head-chart">
                    <div>
                        <h3 class="dash-card-title">
                            <span class="dash-title-ico"><?php echo $svg_bar_chart; ?></span>
                            <span><?php esc_html_e('7-Day Revenue Trend', 'antalya-pos'); ?></span>
                        </h3>
                        <span class="dash-card-sub"><?php esc_html_e('Daily completed sales comparison (click any bar for details)', 'antalya-pos'); ?></span>
                    </div>
                    <a href="<?php echo $reports_url; ?>" class="dash-link-orange">
                        <span><?php esc_html_e('Full Report', 'antalya-pos'); ?></span>
                        <?php echo $svg_arrow_rt; ?>
                    </a>
                </div>

                <div class="dash-chart-wrap" id="dash-revenue-chart">
                    <?php foreach ($chart_days as $cd) :
                        $height_pct = max(4, round(($cd['rev'] / $max_rev) * 100));
                        $bar_url    = esc_url(add_query_arg('view_date', $cd['raw_date'], admin_url('admin.php?page=antalya-pos&tab=reports')));
                    ?>
                    <a href="<?php echo $bar_url; ?>" class="dash-bar-col" title="<?php echo esc_attr($cd['date'] . ': ' . $cur . number_format($cd['rev'], 2)); ?>">
                        <div class="dash-bar-val"><?php echo $cd['rev'] > 0 ? $cur . esc_html(number_format($cd['rev'], 0)) : ''; ?></div>
                        <div class="dash-bar <?php echo $cd['is_today'] ? 'today' : ''; ?>" data-height="<?php echo intval($height_pct); ?>"></div>
                        <div class="dash-bar-lbl <?php echo $cd['is_today'] ? 'is-today' : ''; ?>"><?php echo esc_html($cd['label']); ?></div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top Selling Dishes Card -->
            <div class="apos-card apos-full-card">
                <div class="dash-card-head">
                    <h3 class="dash-card-title">
                        <span class="dash-title-ico"><?php echo $svg_award; ?></span>
                        <span><?php esc_html_e('Top Selling Dishes', 'antalya-pos'); ?></span>
                    </h3>
                    <span class="dash-badge-popular"><?php esc_html_e('Popular', 'antalya-pos'); ?></span>
                </div>

                <?php if (empty($top_items)) : ?>
                    <p class="dash-empty-msg"><?php esc_html_e('No order data yet.', 'antalya-pos'); ?></p>
                <?php else : ?>
                    <div class="dash-top-list">
                        <?php $rank = 1; foreach ($top_items as $dish_name => $sold_qty) : ?>
                        <div class="dash-top-row">
                            <div class="dash-top-left">
                                <span class="dash-rank-badge <?php echo $rank === 1 ? 'rank-1' : ''; ?>">
                                    #<?php echo intval($rank++); ?>
                                </span>
                                <strong class="dash-dish-name"><?php echo esc_html($dish_name); ?></strong>
                            </div>
                            <span class="dash-sold-pill">
                                <?php
                                printf(
                                    /* translators: %d: quantity sold */
                                    esc_html__('%d sold', 'antalya-pos'),
                                    intval($sold_qty)
                                );
                                ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bottom Section: Recent Orders + Live Floor Overview -->
        <div class="dash-split-grid">
            <!-- Recent Orders Table -->
            <div class="apos-card apos-full-card">
                <div class="dash-card-head">
                    <h3 class="dash-card-title">
                        <span class="dash-title-ico"><?php echo $svg_receipt; ?></span>
                        <span><?php esc_html_e('Recent Table Orders', 'antalya-pos'); ?></span>
                    </h3>
                    <a href="<?php echo $orders_url; ?>" class="dash-link-orange">
                        <span><?php esc_html_e('Manage All', 'antalya-pos'); ?></span>
                        <?php echo $svg_arrow_rt; ?>
                    </a>
                </div>
                <table class="apos-table dash-recent-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Order', 'antalya-pos'); ?></th>
                            <th><?php esc_html_e('Table', 'antalya-pos'); ?></th>
                            <th><?php esc_html_e('Time', 'antalya-pos'); ?></th>
                            <th><?php esc_html_e('Bill', 'antalya-pos'); ?></th>
                            <th><?php esc_html_e('Status', 'antalya-pos'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_orders)) : ?>
                            <tr><td colspan="5" class="dash-table-empty"><?php esc_html_e('No orders placed yet.', 'antalya-pos'); ?></td></tr>
                        <?php endif; ?>
                        <?php foreach ($recent_orders as $ro) :
                            $st_class = 'st-' . sanitize_html_class($ro->status);
                            $ord_url  = esc_url(add_query_arg('view_order', intval($ro->id), admin_url('admin.php?page=antalya-pos&tab=orders')));
                        ?>
                        <tr>
                            <td>
                                <a href="<?php echo $ord_url; ?>" class="dash-ord-link">
                                    #<?php echo intval($ro->id); ?>
                                </a>
                            </td>
                            <td><span class="dash-tbl-name"><?php esc_html_e('Table', 'antalya-pos'); ?> <?php echo esc_html($ro->table_no); ?></span></td>
                            <td class="dash-ord-time"><?php echo esc_html(date('h:i A', strtotime($ro->created_at))); ?></td>
                            <td><strong><?php echo $cur . esc_html(number_format((float) $ro->total_amount, 2)); ?></strong></td>
                            <td><span class="st-pill <?php echo esc_attr($st_class); ?>"><?php echo esc_html($ro->status === 'completed' ? __('Paid', 'antalya-pos') : ucfirst($ro->status)); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Live Floor Status Card -->
            <div class="apos-card apos-full-card">
                <div class="dash-card-head dash-card-head-floor">
                    <h3 class="dash-card-title">
                        <span class="dash-title-ico"><?php echo $svg_grid_sm; ?></span>
                        <span><?php esc_html_e('Live Floor Status', 'antalya-pos'); ?></span>
                    </h3>
                    <a href="<?php echo $tables_url; ?>" class="dash-link-orange">
                        <span><?php esc_html_e('Tables', 'antalya-pos'); ?></span>
                        <?php echo $svg_arrow_rt; ?>
                    </a>
                </div>

                <?php if (empty($all_tables)) : ?>
                    <p class="dash-no-tables">
                        <?php esc_html_e('No tables configured yet.', 'antalya-pos'); ?>
                        <a href="<?php echo $tables_url; ?>"><?php esc_html_e('Add tables', 'antalya-pos'); ?></a>.
                    </p>
                <?php else : ?>
                    <div class="floor-grid">
                        <?php foreach ($all_tables as $tbl) :
                            $busy    = in_array($tbl->table_name, $occupied_list, true);
                            $tbl_url = esc_url(add_query_arg('view_table', intval($tbl->id), admin_url('admin.php?page=antalya-pos&tab=tables')));
                        ?>
                        <a href="<?php echo $tbl_url; ?>" class="floor-tile <?php echo $busy ? 'floor-busy' : 'floor-free'; ?>">
                            <div>T-<?php echo esc_html($tbl->table_name); ?></div>
                            <div class="floor-status-row">
                                <span class="dot-sm <?php echo $busy ? 'dot-busy' : 'dot-free'; ?>"></span>
                                <span><?php echo $busy ? esc_html__('Busy', 'antalya-pos') : esc_html__('Free', 'antalya-pos'); ?></span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="dash-shortcode-box">
                    <strong><?php esc_html_e('Tablet Menu Shortcode:', 'antalya-pos'); ?></strong>
                    <code class="dash-shortcode-code">[restaurant_menu]</code>
                </div>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        function updateDashClock() {
            let now = new Date();
            $('#dash-live-clock').text(now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
        }
        updateDashClock();
        setInterval(updateDashClock, 1000);

        $('#dash-revenue-chart .dash-bar').each(function() {
            let h = parseInt($(this).data('height'), 10) || 4;
            $(this).css('height', h + '%');
        });
    });
    </script>
    <?php
}