<?php
if (!defined('ABSPATH')) exit;

function rtpos_view_dashboard($s) {
    global $wpdb;
    $cur      = esc_html($s['currency']);
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

    // Dynamic Time-based Greeting & Admin Name
    $current_user = wp_get_current_user();
    $admin_name   = $current_user->display_name ?: 'Admin';
    $hour         = (int) current_time('G');
    if ($hour < 12) {
        $greeting = 'Good Morning';
    } elseif ($hour < 18) {
        $greeting = 'Good Afternoon';
    } else {
        $greeting = 'Good Evening';
    }

    // 1. Core KPI Metrics
    $today_sales    = $wpdb->get_var($wpdb->prepare("SELECT SUM(total_amount) FROM $t_orders WHERE DATE(created_at) = %s AND status = 'completed'", $today)) ?: 0;
    $today_paid_cnt = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $t_orders WHERE DATE(created_at) = %s AND status = 'completed'", $today)) ?: 0;
    $avg_order_val  = $today_paid_cnt > 0 ? ($today_sales / $today_paid_cnt) : 0;

    $active_orders  = $wpdb->get_var("SELECT COUNT(*) FROM $t_orders WHERE status != 'completed'") ?: 0;
    $total_tables   = (int) ($wpdb->get_var("SELECT COUNT(*) FROM $t_tables") ?: 0);
    $occupied_list  = $wpdb->get_col("SELECT DISTINCT table_no FROM $t_orders WHERE status != 'completed'") ?: [];
    $occupied_count = count($occupied_list);
    $free_tables    = max(0, $total_tables - $occupied_count);

    // 2. Last 7 Days Revenue Data for Chart
    $chart_days = [];
    $max_rev    = 1;
    for ($i = 6; $i >= 0; $i--) {
        $d   = date('Y-m-d', strtotime("-$i days", current_time('timestamp')));
        $rev = (float) ($wpdb->get_var($wpdb->prepare("SELECT SUM(total_amount) FROM $t_orders WHERE DATE(created_at) = %s AND status = 'completed'", $d)) ?: 0);
        if ($rev > $max_rev) $max_rev = $rev;
        $chart_days[] = [
            'label'    => date('D', strtotime($d)),
            'date'     => date('M d', strtotime($d)),
            'raw_date' => $d,
            'rev'      => $rev,
            'is_today' => ($d === $today)
        ];
    }

    // 3. Top Selling Items (From last 100 orders)
    $recent_all  = $wpdb->get_results("SELECT items FROM $t_orders ORDER BY id DESC LIMIT 100") ?: [];
    $item_counts = [];
    foreach ($recent_all as $row) {
        $decoded = json_decode($row->items, true);
        if (is_array($decoded)) {
            foreach ($decoded as $it) {
                $name = $it['name'] ?? 'Unknown Item';
                $qty  = intval($it['qty'] ?? 0);
                if (!isset($item_counts[$name])) $item_counts[$name] = 0;
                $item_counts[$name] += $qty;
            }
        }
    }
    arsort($item_counts);
    $top_items = array_slice($item_counts, 0, 5, true);

    // 4. Recent Orders & Tables
    $recent_orders = $wpdb->get_results("SELECT * FROM $t_orders ORDER BY id DESC LIMIT 6") ?: [];
    $all_tables    = $wpdb->get_results("SELECT * FROM $t_tables ORDER BY id ASC") ?: [];
    ?>

    <style>
        .dash-full-wrap { width: 100%; box-sizing: border-box; }
        .apos-full-card { width: 100%; max-width: 100%; box-sizing: border-box; margin: 0; }

        /* Welcome Banner & Live Clock */
        .dash-welcome-card {
            background: linear-gradient(135deg, #0b1120 0%, #1e293b 100%);
            color: #fff; padding: 26px 30px; border-radius: 18px; margin-bottom: 26px;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;
            box-shadow: 0 10px 25px -5px rgba(11, 17, 32, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.06); width: 100%; box-sizing: border-box;
        }
        .dash-welcome-title { margin: 0; font-size: 24px; font-weight: 800; color: #fff; letter-spacing: -0.4px; }
        .dash-welcome-title span { color: #f97316; }
        .dash-welcome-sub { margin: 6px 0 0; color: #94a3b8; font-size: 13.5px; }
        .dash-welcome-right { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
        .dash-clock-box {
            background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 20px; border-radius: 14px; text-align: right; backdrop-filter: blur(6px);
        }
        .dash-clock-time {
            font-family: 'Courier New', monospace; font-size: 24px; font-weight: 800;
            color: #f8fafc; letter-spacing: 1px; line-height: 1.1;
        }
        .dash-clock-date {
            font-size: 12px; color: #94a3b8; font-weight: 600; margin-top: 5px;
            display: flex; align-items: center; justify-content: flex-end; gap: 6px;
        }
        .dash-monitor-btn {
            padding: 13px 20px; display: inline-flex; align-items: center; gap: 8px;
        }

        /* Top 4 KPI Cards */
        .dash-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(235px, 1fr)); gap: 20px; margin-bottom: 26px; width: 100%; }
        .dash-kpi {
            background: #fff; padding: 22px; border-radius: 16px; border: 1px solid #e2e8f0;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03); display: flex; justify-content: space-between; align-items: flex-start;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .dash-kpi:hover { transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(15, 23, 42, 0.07); }
        .dash-kpi-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
        .dash-kpi-val { font-size: 28px; font-weight: 800; color: #0f172a; margin: 8px 0 6px; letter-spacing: -0.5px; }
        .dash-kpi-total { font-size: 16px; color: #94a3b8; font-weight: 600; }
        .dash-kpi-sub { font-size: 12px; font-weight: 600; }
        .dash-sub-green { color: #10b981; }
        .dash-sub-amber { color: #f59e0b; }
        .dash-sub-orange { color: #f97316; }
        .dash-sub-blue { color: #3b82f6; }

        .dash-icon-box { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .dash-ico-green { background: #ecfdf5; color: #10b981; }
        .dash-ico-amber { background: #fffbeb; color: #f59e0b; }
        .dash-ico-orange { background: #fff7ed; color: #f97316; }
        .dash-ico-blue { background: #eff6ff; color: #3b82f6; }

        /* Split Grids & Card Headers */
        .dash-split-grid { display: grid; grid-template-columns: 1.6fr 1fr; gap: 24px; width: 100%; }
        .dash-split-mb { margin-bottom: 24px; }
        @media (max-width: 960px) { .dash-split-grid { grid-template-columns: 1fr; } }

        .dash-card-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .dash-card-head-chart { margin-bottom: 10px; }
        .dash-card-head-floor { margin-bottom: 14px; }
        .dash-card-title { margin: 0; font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px; }
        .dash-title-ico { color: #f97316; display: inline-flex; }
        .dash-card-sub { font-size: 12px; color: #64748b; }
        .dash-link-orange { font-size: 13px; color: #f97316; font-weight: 700; text-decoration: none; }
        .dash-link-orange:hover { text-decoration: underline; }
        .dash-badge-popular { font-size: 11.5px; color: #64748b; background: #f1f5f9; padding: 3px 9px; border-radius: 6px; font-weight: 700; }

        /* 7-Day Bar Chart */
        .dash-chart-wrap { display: flex; align-items: flex-end; justify-content: space-between; height: 185px; padding-top: 20px; gap: 12px; border-bottom: 1px solid #e2e8f0; }
        .dash-bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: flex-end; text-decoration: none; }
        .dash-bar {
            width: 100%; max-width: 44px; border-radius: 8px 8px 0 0; background: #cbd5e1;
            transition: height 0.4s ease, background 0.2s; position: relative; min-height: 6px;
        }
        .dash-bar.today { background: linear-gradient(180deg, #f97316 0%, #ea580c 100%); box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3); }
        .dash-bar-col:hover .dash-bar { background: #0f172a; }
        .dash-bar-val { font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 6px; }
        .dash-bar-lbl { font-size: 12px; font-weight: 700; color: #64748b; margin-top: 8px; }
        .dash-bar-lbl.is-today { color: #f97316; }

        /* Top Selling Dishes List */
        .dash-empty-msg { color: #94a3b8; text-align: center; padding: 40px 0; margin: 0; }
        .dash-top-list { display: flex; flex-direction: column; gap: 12px; }
        .dash-top-row { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px dashed #f1f5f9; }
        .dash-top-row:last-child { border-bottom: none; padding-bottom: 0; }
        .dash-top-left { display: flex; align-items: center; gap: 10px; }
        .dash-rank-badge {
            width: 24px; height: 24px; border-radius: 6px; background: #f1f5f9; color: #475569;
            font-size: 12px; font-weight: 800; display: flex; align-items: center; justify-content: center;
        }
        .dash-rank-badge.rank-1 { background: #fef3c7; color: #d97706; }
        .dash-dish-name { font-size: 13.5px; color: #1e293b; }
        .dash-sold-pill { font-size: 12.5px; font-weight: 700; color: #f97316; background: #fff7ed; padding: 3px 9px; border-radius: 12px; }

        /* Recent Orders Table */
        .dash-recent-table { width: 100%; box-shadow: none; border: 1px solid #f1f5f9; }
        .dash-table-empty { text-align: center; color: #94a3b8; padding: 24px; }
        .dash-ord-link { font-weight: 800; color: #0f172a; text-decoration: none; }
        .dash-ord-link:hover { color: #f97316; }
        .dash-tbl-name { font-weight: 700; }
        .dash-ord-time { color: #64748b; font-size: 13px; }

        /* Status Pills */
        .st-pill { padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; text-transform: capitalize; display: inline-block; }
        .st-pending { background: #fef3c7; color: #d97706; }
        .st-preparing { background: #dbeafe; color: #1d4ed8; }
        .st-served { background: #f3e8ff; color: #7e22ce; }
        .st-completed { background: #d1fae5; color: #059669; }

        /* Floor Table Pills */
        .dash-no-tables { color: #94a3b8; font-size: 13.5px; }
        .floor-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(95px, 1fr)); gap: 10px; }
        .floor-tile {
            padding: 12px 8px; border-radius: 12px; text-align: center; font-weight: 700; font-size: 13px;
            border: 1px solid #e2e8f0; transition: 0.2s; text-decoration: none; display: block;
        }
        .floor-tile:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(15, 23, 42, 0.06); }
        .floor-free { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .floor-busy { background: #fff7ed; border-color: #fed7aa; color: #c2410c; }
        .floor-status-row { font-size: 10.5px; font-weight: 700; margin-top: 4px; opacity: 0.9; }
        .dot-sm { width: 6px; height: 6px; border-radius: 50%; display: inline-block; margin-right: 4px; vertical-align: middle; }
        .dot-free { background: #16a34a; }
        .dot-busy { background: #ea580c; }

        .dash-shortcode-box {
            margin-top: 18px; padding: 12px 14px; background: #f8fafc;
            border-radius: 10px; border: 1px solid #e2e8f0; font-size: 12.5px; color: #475569;
        }
        .dash-shortcode-code { color: #f97316; font-weight: 700; }
    </style>

    <div class="dash-full-wrap">
        <!-- Welcome Message & Live Clock Banner -->
        <div class="dash-welcome-card">
            <div>
                <h1 class="dash-welcome-title"><?php echo esc_html($greeting); ?>, <span><?php echo esc_html($admin_name); ?></span></h1>
                <p class="dash-welcome-sub">Welcome back to <strong><?php echo esc_html($s['restaurant_name']); ?></strong> POS — Monitor live orders, tables, and daily revenue.</p>
            </div>

            <div class="dash-welcome-right">
                <div class="dash-clock-box">
                    <div class="dash-clock-time" id="dash-live-clock"><?php echo current_time('h:i:s A'); ?></div>
                    <div class="dash-clock-date">
                        <?php echo $svg_calendar; ?>
                        <span><?php echo date('l, F j, Y', current_time('timestamp')); ?></span>
                    </div>
                </div>
                <a href="?page=antalya-pos&tab=orders" class="apos-btn dash-monitor-btn">
                    <?php echo $svg_monitor; ?>
                    <span>Live Kitchen Monitor</span>
                </a>
            </div>
        </div>

        <!-- Top 4 KPI Cards -->
        <div class="dash-kpi-grid">
            <div class="dash-kpi">
                <div>
                    <div class="dash-kpi-title">Today's Paid Revenue</div>
                    <div class="dash-kpi-val"><?php echo $cur . number_format($today_sales, 2); ?></div>
                    <div class="dash-kpi-sub dash-sub-green">From <?php echo intval($today_paid_cnt); ?> paid bill(s)</div>
                </div>
                <div class="dash-icon-box dash-ico-green">
                    <?php echo $svg_wallet; ?>
                </div>
            </div>

            <div class="dash-kpi">
                <div>
                    <div class="dash-kpi-title">Active Kitchen Orders</div>
                    <div class="dash-kpi-val"><?php echo intval($active_orders); ?></div>
                    <div class="dash-kpi-sub dash-sub-amber">Pending / Cooking / Served</div>
                </div>
                <div class="dash-icon-box dash-ico-amber">
                    <?php echo $svg_flame; ?>
                </div>
            </div>

            <div class="dash-kpi">
                <div>
                    <div class="dash-kpi-title">Table Occupancy</div>
                    <div class="dash-kpi-val"><?php echo $occupied_count; ?> <span class="dash-kpi-total">/ <?php echo $total_tables; ?></span></div>
                    <div class="dash-kpi-sub dash-sub-orange"><?php echo $free_tables; ?> table(s) available</div>
                </div>
                <div class="dash-icon-box dash-ico-orange">
                    <?php echo $svg_grid; ?>
                </div>
            </div>

            <div class="dash-kpi">
                <div>
                    <div class="dash-kpi-title">Avg. Ticket Size</div>
                    <div class="dash-kpi-val"><?php echo $cur . number_format($avg_order_val, 2); ?></div>
                    <div class="dash-kpi-sub dash-sub-blue">Per completed order today</div>
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
                            <span>7-Day Revenue Trend</span>
                        </h3>
                        <span class="dash-card-sub">Daily completed sales comparison (click any bar for details)</span>
                    </div>
                    <a href="?page=antalya-pos&tab=reports" class="dash-link-orange">Full Report →</a>
                </div>

                <div class="dash-chart-wrap" id="dash-revenue-chart">
                    <?php foreach ($chart_days as $cd) : 
                        $height_pct = max(4, round(($cd['rev'] / $max_rev) * 100));
                    ?>
                    <a href="?page=antalya-pos&tab=reports&view_date=<?php echo esc_attr($cd['raw_date']); ?>" class="dash-bar-col" title="<?php echo esc_attr($cd['date'] . ': ' . $cur . number_format($cd['rev'], 2)); ?>">
                        <div class="dash-bar-val"><?php echo $cd['rev'] > 0 ? $cur . number_format($cd['rev'], 0) : ''; ?></div>
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
                        <span>Top Selling Dishes</span>
                    </h3>
                    <span class="dash-badge-popular">Popular</span>
                </div>

                <?php if (empty($top_items)) : ?>
                    <p class="dash-empty-msg">No order data yet.</p>
                <?php else : ?>
                    <div class="dash-top-list">
                        <?php $rank = 1; foreach ($top_items as $dish_name => $sold_qty) : ?>
                        <div class="dash-top-row">
                            <div class="dash-top-left">
                                <span class="dash-rank-badge <?php echo $rank === 1 ? 'rank-1' : ''; ?>">
                                    #<?php echo $rank++; ?>
                                </span>
                                <strong class="dash-dish-name"><?php echo esc_html($dish_name); ?></strong>
                            </div>
                            <span class="dash-sold-pill">
                                <?php echo intval($sold_qty); ?> sold
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
                        <span>Recent Table Orders</span>
                    </h3>
                    <a href="?page=antalya-pos&tab=orders" class="dash-link-orange">Manage All →</a>
                </div>
                <table class="apos-table dash-recent-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Table</th>
                            <th>Time</th>
                            <th>Bill</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_orders)) : ?>
                            <tr><td colspan="5" class="dash-table-empty">No orders placed yet.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($recent_orders as $ro) : 
                            $st_class = 'st-' . sanitize_html_class($ro->status);
                        ?>
                        <tr>
                            <td>
                                <a href="?page=antalya-pos&tab=orders&view_order=<?php echo intval($ro->id); ?>" class="dash-ord-link">
                                    #<?php echo intval($ro->id); ?>
                                </a>
                            </td>
                            <td><span class="dash-tbl-name">Table <?php echo esc_html($ro->table_no); ?></span></td>
                            <td class="dash-ord-time"><?php echo date('h:i A', strtotime($ro->created_at)); ?></td>
                            <td><strong><?php echo $cur . number_format($ro->total_amount, 2); ?></strong></td>
                            <td><span class="st-pill <?php echo $st_class; ?>"><?php echo esc_html($ro->status === 'completed' ? 'Paid' : $ro->status); ?></span></td>
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
                        <span>Live Floor Status</span>
                    </h3>
                    <a href="?page=antalya-pos&tab=tables" class="dash-link-orange">Tables →</a>
                </div>

                <?php if (empty($all_tables)) : ?>
                    <p class="dash-no-tables">No tables configured yet. <a href="?page=antalya-pos&tab=tables">Add tables</a>.</p>
                <?php else : ?>
                    <div class="floor-grid">
                        <?php foreach ($all_tables as $tbl) : 
                            $busy = in_array($tbl->table_name, $occupied_list);
                        ?>
                        <a href="?page=antalya-pos&tab=tables&view_table=<?php echo intval($tbl->id); ?>" class="floor-tile <?php echo $busy ? 'floor-busy' : 'floor-free'; ?>">
                            <div>T-<?php echo esc_html($tbl->table_name); ?></div>
                            <div class="floor-status-row">
                                <span class="dot-sm <?php echo $busy ? 'dot-busy' : 'dot-free'; ?>"></span>
                                <span><?php echo $busy ? 'Busy' : 'Free'; ?></span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="dash-shortcode-box">
                    <strong>Tablet Menu Shortcode:</strong> <code class="dash-shortcode-code">[restaurant_menu]</code>
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