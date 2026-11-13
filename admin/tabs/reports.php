<?php
if (!defined('ABSPATH')) exit;

function rtpos_view_reports($s = []) {
    global $wpdb;
    
    // Safe fallback for settings & currency if called without parameters
    if (empty($s) && function_exists('rtpos_get_settings')) {
        $s = rtpos_get_settings();
    }
    $cur      = esc_html($s['currency'] ?? '৳');
    $t_orders = $wpdb->prefix . 'rtpos_orders';

    // Reusable SVG Icons
    $svg_download = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>';
    $svg_print    = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>';
    $svg_chart    = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>';
    $svg_calendar = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
    $svg_award    = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>';
    $svg_eye      = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
    $svg_back     = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';

    $today        = current_time('Y-m-d');
    $month_start  = current_time('Y-m-01');
    $last7_start  = date('Y-m-d', strtotime('-6 days', current_time('timestamp')));
    $prev_m_start = date('Y-m-01', strtotime('first day of last month', current_time('timestamp')));
    $prev_m_end   = date('Y-m-t', strtotime('last day of last month', current_time('timestamp')));

    $from       = isset($_GET['from']) ? sanitize_text_field($_GET['from']) : $month_start;
    $to         = isset($_GET['to']) ? sanitize_text_field($_GET['to']) : $today;
    $view_date  = isset($_GET['view_date']) ? sanitize_text_field($_GET['view_date']) : '';

    // 1. Summary Metrics with Safe Fallback
    $summary = $wpdb->get_row($wpdb->prepare(
        "SELECT COUNT(*) as total_orders, COALESCE(SUM(subtotal), 0) as sub_rev, COALESCE(SUM(total_amount), 0) as revenue, COALESCE(SUM(tax_amount), 0) as total_tax
         FROM $t_orders WHERE DATE(created_at) BETWEEN %s AND %s AND status = 'completed'",
        $from, $to
    ));

    $total_orders = $summary ? intval($summary->total_orders) : 0;
    $sub_rev      = $summary ? floatval($summary->sub_rev) : 0.0;
    $revenue      = $summary ? floatval($summary->revenue) : 0.0;
    $total_tax    = $summary ? floatval($summary->total_tax) : 0.0;
    $avg_ticket   = ($total_orders > 0) ? ($revenue / $total_orders) : 0.0;

    // 2. Daily Breakdown
    $daily_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT DATE(created_at) as sale_date, COUNT(*) as ord_count, COALESCE(SUM(subtotal), 0) as net_sales, COALESCE(SUM(tax_amount), 0) as tax_sum, COALESCE(SUM(total_amount), 0) as gross_sales
         FROM $t_orders WHERE DATE(created_at) BETWEEN %s AND %s AND status = 'completed'
         GROUP BY DATE(created_at) ORDER BY sale_date DESC",
        $from, $to
    )) ?: [];

    // 3. Top Selling Dishes in Selected Period
    $period_orders = $wpdb->get_results($wpdb->prepare(
        "SELECT items FROM $t_orders WHERE DATE(created_at) BETWEEN %s AND %s AND status = 'completed'",
        $from, $to
    )) ?: [];

    $dish_stats = [];
    foreach ($period_orders as $po) {
        $items = json_decode($po->items, true);
        if (is_array($items)) {
            foreach ($items as $it) {
                $name = $it['name'] ?? 'Unknown Item';
                $qty  = intval($it['qty'] ?? 0);
                $line = floatval($it['unitPrice'] ?? $it['price'] ?? 0) * $qty;
                if (!isset($dish_stats[$name])) {
                    $dish_stats[$name] = ['qty' => 0, 'rev' => 0];
                }
                $dish_stats[$name]['qty'] += $qty;
                $dish_stats[$name]['rev'] += $line;
            }
        }
    }
    uasort($dish_stats, function($a, $b) { return $b['qty'] <=> $a['qty']; });
    $top_dishes = array_slice($dish_stats, 0, 6, true);
    ?>

    <style>
        .rep-full-wrap { width: 100%; box-sizing: border-box; }
        .apos-full-card { width: 100%; max-width: 100%; box-sizing: border-box; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 22px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03); margin-bottom: 24px; }
        .apos-full-table { width: 100%; max-width: 100%; }
        
        .rep-header-actions { display: flex; gap: 10px; flex-wrap: wrap; }

        /* Date Filter & Presets */
        .rep-filter-bar { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px; width: 100%; }
        .rep-date-inputs { display: flex; gap: 14px; flex-wrap: wrap; flex: 1; }
        .rep-date-field { flex: 1; min-width: 180px; }
        .rep-generate-btn { height: 42px; }

        .rep-presets { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
        .rep-preset-btn {
            padding: 7px 16px; border-radius: 20px; font-size: 12.5px; font-weight: 700;
            text-decoration: none; border: 1.5px solid #e2e8f0; background: #fff; color: #475569; transition: all 0.2s;
        }
        .rep-preset-btn:hover { border-color: #f97316; color: #f97316; background: #fff7ed; }
        .rep-preset-btn.active { background: #0f172a; color: #fff; border-color: #0f172a; box-shadow: 0 2px 6px rgba(15,23,42,0.15); }

        /* Premium KPI Cards */
        .rep-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 18px; margin-bottom: 24px; width: 100%; }
        .rep-kpi-card {
            background: #fff; padding: 22px 24px; border-radius: 18px; border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03); position: relative; overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .rep-kpi-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08); }
        .rep-kpi-day { background: #f8fafc; }
        
        .rep-border-green { border-left: 5px solid #10b981; }
        .rep-border-blue { border-left: 5px solid #3b82f6; }
        .rep-border-amber { border-left: 5px solid #f59e0b; }
        .rep-border-orange { border-left: 5px solid #f97316; }

        .rep-kpi-lbl { font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; }
        .rep-kpi-num { font-size: 28px; font-weight: 900; color: #0f172a; margin-top: 8px; letter-spacing: -0.5px; }
        .rep-num-green { color: #059669; }
        .rep-kpi-sub { font-size: 12.5px; color: #64748b; margin-top: 6px; font-weight: 500; }

        /* Split Grid & Card Headers */
        .rep-split-grid { display: grid; grid-template-columns: 1.6fr 1fr; gap: 24px; width: 100%; }
        @media (max-width: 960px) { .rep-split-grid { grid-template-columns: 1fr; } }

        .rep-card-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9; }
        .rep-card-title { margin: 0; font-size: 17px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; }
        .rep-title-ico { color: #f97316; display: inline-flex; }
        .rep-days-cnt { font-size: 12.5px; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 3px 10px; border-radius: 12px; }

        /* Form Elements */
        .apos-form-group { margin: 0; }
        .apos-label { display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 7px; }
        .apos-input {
            width: 100%; padding: 11px 15px; border: 1.5px solid #e2e8f0; border-radius: 11px;
            font-size: 14px; font-weight: 600; color: #0f172a; background: #f8fafc; box-sizing: border-box;
            transition: all 0.2s;
        }
        .apos-input:focus { outline: none; background: #fff; border-color: #f97316; box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.14); }

        .rep-btn-icon { display: inline-flex; align-items: center; gap: 7px; font-weight: 700; }
        .rep-act-btn {
            padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 700;
            text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
            background: #eff6ff; color: #1d4ed8; transition: 0.2s;
        }
        .rep-act-btn:hover { background: #dbeafe; }

        /* Tables & Columns */
        .rep-inner-table { box-shadow: none; border: 1px solid #f1f5f9; border-radius: 12px; overflow: hidden; }
        .rep-day-table { box-shadow: none; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
        .rep-table-empty { text-align: center; padding: 40px; color: #94a3b8; font-size: 14px; }

        .col-rep-details { width: 100px; text-align: right; }
        .col-do-id { width: 100px; }
        .col-do-time { width: 110px; }
        .col-do-tbl { width: 110px; }
        .col-do-sub { width: 130px; }
        .col-do-tax { width: 110px; }
        .col-do-tot { width: 140px; }
        .col-do-act { width: 110px; text-align: right; }

        .rep-date-link { font-weight: 700; color: #0f172a; text-decoration: none; }
        .rep-date-link:hover { color: #f97316; }
        .rep-gross-val { color: #059669; font-family: 'Courier New', Courier, monospace; }
        .rep-do-time { color: #64748b; }
        .rep-do-tbl-badge {
            background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; padding: 3px 10px;
            border-radius: 8px; font-weight: 800; font-size: 12px; display: inline-block;
        }
        .rep-do-items { font-size: 13px; color: #334155; }

        /* Top Selling Dishes List */
        .rep-empty-dishes { color: #94a3b8; text-align: center; padding: 40px 0; margin: 0; font-size: 14px; }
        .rep-top-list { display: flex; flex-direction: column; gap: 14px; }
        .rep-top-row {
            display: flex; justify-content: space-between; align-items: center;
            padding-bottom: 12px; border-bottom: 1px dashed #e2e8f0;
        }
        .rep-top-row:last-child { border-bottom: none; padding-bottom: 0; }
        .rep-top-left { display: flex; align-items: center; gap: 12px; }
        .rep-rank-badge {
            width: 28px; height: 28px; border-radius: 8px; background: #f1f5f9; color: #475569;
            font-size: 12.5px; font-weight: 900; display: flex; align-items: center; justify-content: center;
        }
        .rep-rank-badge.rank-1 { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
        .rep-dish-name { font-size: 14px; font-weight: 700; color: #0f172a; display: block; }
        .rep-dish-qty { color: #64748b; font-size: 12px; }
        .rep-dish-rev { font-size: 15px; font-weight: 800; color: #f97316; font-family: 'Courier New', Courier, monospace; }

        /* Dedicated Daily View Hero */
        .rep-day-hero {
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;
            gap: 16px; padding-bottom: 20px; border-bottom: 1.5px solid #f1f5f9; margin-bottom: 24px;
        }
        .rep-day-hero-left { display: flex; align-items: center; gap: 16px; }
        .rep-day-hero-ico {
            width: 56px; height: 56px; border-radius: 16px; background: #fff7ed; color: #f97316;
            border: 1.5px solid #fed7aa; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 12px rgba(249,115,22,0.1);
        }
        .rep-day-sub { font-size: 11.5px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .rep-day-title { margin: 2px 0 0; font-size: 24px; font-weight: 900; color: #0f172a; }
        .rep-day-section-title { margin: 0 0 16px; font-size: 17px; font-weight: 800; color: #0f172a; }
    </style>

    <div class="rep-full-wrap">
        <!-- Page Header -->
        <div class="apos-header">
            <div>
                <h1>Sales & Tax Reports</h1>
                <?php if (!empty($view_date)) : ?>
                    <p>Detailed daily transaction breakdown for <strong><?php echo esc_html(date('l, M d, Y', strtotime($view_date))); ?></strong></p>
                <?php else : ?>
                    <p>Financial analytics and revenue breakdown from <strong><?php echo esc_html(date('M d, Y', strtotime($from))); ?></strong> to <strong><?php echo esc_html(date('M d, Y', strtotime($to))); ?></strong></p>
                <?php endif; ?>
            </div>
            <div class="rep-header-actions">
                <?php if (!empty($view_date)) : ?>
                    <a href="?page=antalya-pos&tab=reports&from=<?php echo esc_attr($from); ?>&to=<?php echo esc_attr($to); ?>" class="apos-btn-secondary rep-btn-icon">
                        <?php echo $svg_back; ?>
                        <span>Back to Report Overview</span>
                    </a>
                <?php else : ?>
                    <button type="button" id="rep-export-csv" class="apos-btn-secondary rep-btn-icon">
                        <?php echo $svg_download; ?>
                        <span>Export CSV</span>
                    </button>
                <?php endif; ?>
                <button type="button" id="rep-print-btn" class="apos-btn rep-btn-icon">
                    <?php echo $svg_print; ?>
                    <span>Print Report</span>
                </button>
            </div>
        </div>

        <?php if (!empty($view_date)) : 
            // ==============================================================
            // DEDICATED SINGLE-DAY SALES REPORT VIEW PAGE
            // ==============================================================
            $day_summary = $wpdb->get_row($wpdb->prepare(
                "SELECT COUNT(*) as total_orders, COALESCE(SUM(subtotal), 0) as sub_rev, COALESCE(SUM(total_amount), 0) as revenue, COALESCE(SUM(tax_amount), 0) as total_tax
                 FROM $t_orders WHERE DATE(created_at) = %s AND status = 'completed'",
                $view_date
            ));
            $d_orders_cnt = $day_summary ? intval($day_summary->total_orders) : 0;
            $d_sub_rev    = $day_summary ? floatval($day_summary->sub_rev) : 0.0;
            $d_tax        = $day_summary ? floatval($day_summary->total_tax) : 0.0;
            $d_rev        = $day_summary ? floatval($day_summary->revenue) : 0.0;

            $day_orders = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $t_orders WHERE DATE(created_at) = %s AND status = 'completed' ORDER BY id DESC",
                $view_date
            )) ?: [];
        ?>
            <div class="apos-card apos-full-card">
                <div class="rep-day-hero">
                    <div class="rep-day-hero-left">
                        <div class="rep-day-hero-ico">
                            <?php echo $svg_calendar; ?>
                        </div>
                        <div>
                            <div class="rep-day-sub">Daily Sales Statement</div>
                            <h2 class="rep-day-title"><?php echo esc_html(date('F d, Y (l)', strtotime($view_date))); ?></h2>
                        </div>
                    </div>
                </div>

                <!-- Day KPIs -->
                <div class="rep-kpi-grid">
                    <div class="rep-kpi-card rep-kpi-day rep-border-green">
                        <div class="rep-kpi-lbl">Day Gross Revenue</div>
                        <div class="rep-kpi-num rep-num-green"><?php echo $cur . number_format($d_rev, 2); ?></div>
                    </div>
                    <div class="rep-kpi-card rep-kpi-day rep-border-blue">
                        <div class="rep-kpi-lbl">Net Food Sales</div>
                        <div class="rep-kpi-num"><?php echo $cur . number_format($d_sub_rev, 2); ?></div>
                    </div>
                    <div class="rep-kpi-card rep-kpi-day rep-border-amber">
                        <div class="rep-kpi-lbl">Tax Collected</div>
                        <div class="rep-kpi-num"><?php echo $cur . number_format($d_tax, 2); ?></div>
                    </div>
                    <div class="rep-kpi-card rep-kpi-day rep-border-orange">
                        <div class="rep-kpi-lbl">Paid Orders</div>
                        <div class="rep-kpi-num"><?php echo $d_orders_cnt; ?></div>
                    </div>
                </div>

                <!-- Day Completed Orders Table -->
                <h3 class="rep-day-section-title">Completed Orders on <?php echo esc_html(date('M d, Y', strtotime($view_date))); ?></h3>
                <table class="apos-table apos-full-table rep-day-table">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:1.5px solid #e2e8f0;">
                            <th class="col-do-id" style="padding:15px 18px;">Order #</th>
                            <th class="col-do-time" style="padding:15px 14px;">Time</th>
                            <th class="col-do-tbl" style="padding:15px 14px;">Table</th>
                            <th style="padding:15px 14px;">Items Sold</th>
                            <th class="col-do-sub" style="padding:15px 14px;">Subtotal</th>
                            <th class="col-do-tax" style="padding:15px 14px;">Tax</th>
                            <th class="col-do-tot" style="padding:15px 14px;">Total Paid</th>
                            <th class="col-do-act" style="padding:15px 18px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($day_orders)) : ?>
                            <tr><td colspan="8" class="rep-table-empty">No completed orders recorded on this date.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($day_orders as $do) : 
                            $do_items     = json_decode($do->items, true) ?: [];
                            $item_summary = [];
                            foreach ($do_items as $it) {
                                $item_summary[] = intval($it['qty']) . 'x ' . $it['name'];
                            }
                        ?>
                        <tr style="transition:background 0.15s;">
                            <td style="padding:14px 18px;"><strong>#<?php echo intval($do->id); ?></strong></td>
                            <td style="padding:14px 14px;" class="rep-do-time"><?php echo esc_html(date('h:i A', strtotime($do->created_at))); ?></td>
                            <td style="padding:14px 14px;"><span class="rep-do-tbl-badge">Table <?php echo esc_html($do->table_no); ?></span></td>
                            <td style="padding:14px 14px;" class="rep-do-items"><?php echo esc_html(implode(', ', $item_summary)); ?></td>
                            <td style="padding:14px 14px; font-family:monospace;"><?php echo $cur . number_format(floatval($do->subtotal), 2); ?></td>
                            <td style="padding:14px 14px; font-family:monospace;"><?php echo $cur . number_format(floatval($do->tax_amount), 2); ?></td>
                            <td style="padding:14px 14px;"><strong class="rep-gross-val"><?php echo $cur . number_format(floatval($do->total_amount), 2); ?></strong></td>
                            <td style="padding:14px 18px; text-align:right;">
                                <a href="?page=antalya-pos&tab=orders&view_order=<?php echo intval($do->id); ?>" class="rep-act-btn">
                                    <?php echo $svg_eye; ?>
                                    <span>Invoice</span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else : ?>
            <!-- ============================================================== -->
            <!-- MAIN REPORTS OVERVIEW (FULL WIDTH) -->
            <!-- ============================================================== -->
            <!-- Date Range & Quick Presets Card -->
            <div class="apos-card apos-full-card">
                <div class="rep-presets">
                    <a href="?page=antalya-pos&tab=reports&from=<?php echo esc_attr($today); ?>&to=<?php echo esc_attr($today); ?>" class="rep-preset-btn <?php echo ($from === $today && $to === $today) ? 'active' : ''; ?>">Today</a>
                    <a href="?page=antalya-pos&tab=reports&from=<?php echo esc_attr($last7_start); ?>&to=<?php echo esc_attr($today); ?>" class="rep-preset-btn <?php echo ($from === $last7_start && $to === $today) ? 'active' : ''; ?>">Last 7 Days</a>
                    <a href="?page=antalya-pos&tab=reports&from=<?php echo esc_attr($month_start); ?>&to=<?php echo esc_attr($today); ?>" class="rep-preset-btn <?php echo ($from === $month_start && $to === $today) ? 'active' : ''; ?>">This Month</a>
                    <a href="?page=antalya-pos&tab=reports&from=<?php echo esc_attr($prev_m_start); ?>&to=<?php echo esc_attr($prev_m_end); ?>" class="rep-preset-btn <?php echo ($from === $prev_m_start && $to === $prev_m_end) ? 'active' : ''; ?>">Last Month</a>
                </div>

                <form method="get" class="rep-filter-bar">
                    <input type="hidden" name="page" value="antalya-pos">
                    <input type="hidden" name="tab" value="reports">

                    <div class="rep-date-inputs">
                        <div class="apos-form-group rep-date-field">
                            <label class="apos-label">From Date</label>
                            <input type="date" name="from" class="apos-input" value="<?php echo esc_attr($from); ?>" required>
                        </div>
                        <div class="apos-form-group rep-date-field">
                            <label class="apos-label">To Date</label>
                            <input type="date" name="to" class="apos-input" value="<?php echo esc_attr($to); ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="apos-btn rep-btn-icon rep-generate-btn">
                        <?php echo $svg_chart; ?>
                        <span>Generate Report</span>
                    </button>
                </form>
            </div>

            <!-- Financial Summary KPIs -->
            <div class="rep-kpi-grid">
                <div class="rep-kpi-card rep-border-green">
                    <div class="rep-kpi-lbl">Gross Revenue</div>
                    <div class="rep-kpi-num rep-num-green"><?php echo $cur . number_format($revenue, 2); ?></div>
                    <div class="rep-kpi-sub">Total paid bills collected</div>
                </div>

                <div class="rep-kpi-card rep-border-blue">
                    <div class="rep-kpi-lbl">Net Food Sales</div>
                    <div class="rep-kpi-num" style="font-family:monospace;"><?php echo $cur . number_format($sub_rev, 2); ?></div>
                    <div class="rep-kpi-sub">Excluding VAT / tax</div>
                </div>

                <div class="rep-kpi-card rep-border-amber">
                    <div class="rep-kpi-lbl">VAT / Tax Collected</div>
                    <div class="rep-kpi-num" style="font-family:monospace;"><?php echo $cur . number_format($total_tax, 2); ?></div>
                    <div class="rep-kpi-sub">Tax rate: <?php echo floatval($s['tax_percent'] ?? 0); ?>%</div>
                </div>

                <div class="rep-kpi-card rep-border-orange">
                    <div class="rep-kpi-lbl">Completed Orders</div>
                    <div class="rep-kpi-num"><?php echo $total_orders; ?></div>
                    <div class="rep-kpi-sub">Avg. ticket: <strong style="font-family:monospace;"><?php echo $cur . number_format($avg_ticket, 2); ?></strong></div>
                </div>
            </div>

            <!-- Daily Sales Breakdown & Top Dishes -->
            <div class="rep-split-grid">
                <!-- Daily Breakdown Table -->
                <div class="apos-card apos-full-card rep-card-no-margin">
                    <div class="rep-card-head">
                        <h3 class="rep-card-title">
                            <span class="rep-title-ico"><?php echo $svg_calendar; ?></span>
                            <span>Daily Sales Breakdown</span>
                        </h3>
                        <span class="rep-days-cnt"><?php echo count($daily_rows); ?> active day(s)</span>
                    </div>

                    <table class="apos-table apos-full-table rep-inner-table" id="rep-daily-table">
                        <thead>
                            <tr style="background:#f8fafc; border-bottom:1.5px solid #e2e8f0;">
                                <th style="padding:15px 18px;">Date</th>
                                <th style="padding:15px 14px;">Orders</th>
                                <th style="padding:15px 14px;">Net Sales</th>
                                <th style="padding:15px 14px;">Tax</th>
                                <th style="padding:15px 14px;">Gross Total</th>
                                <th class="col-rep-details" style="padding:15px 18px;">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($daily_rows)) : ?>
                                <tr><td colspan="6" class="rep-table-empty">No completed orders in this date range.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($daily_rows as $dr) : ?>
                            <tr>
                                <td style="padding:14px 18px;">
                                    <a href="?page=antalya-pos&tab=reports&from=<?php echo esc_attr($from); ?>&to=<?php echo esc_attr($to); ?>&view_date=<?php echo esc_attr($dr->sale_date); ?>" class="rep-date-link">
                                        <?php echo esc_html(date('M d, Y (D)', strtotime($dr->sale_date))); ?>
                                    </a>
                                </td>
                                <td style="padding:14px 14px;"><strong><?php echo intval($dr->ord_count); ?></strong></td>
                                <td style="padding:14px 14px; font-family:monospace;"><?php echo $cur . number_format(floatval($dr->net_sales), 2); ?></td>
                                <td style="padding:14px 14px; font-family:monospace;"><?php echo $cur . number_format(floatval($dr->tax_sum), 2); ?></td>
                                <td style="padding:14px 14px;"><strong class="rep-gross-val"><?php echo $cur . number_format(floatval($dr->gross_sales), 2); ?></strong></td>
                                <td style="padding:14px 18px; text-align:right;">
                                    <a href="?page=antalya-pos&tab=reports&from=<?php echo esc_attr($from); ?>&to=<?php echo esc_attr($to); ?>&view_date=<?php echo esc_attr($dr->sale_date); ?>" class="rep-act-btn">
                                        <?php echo $svg_eye; ?>
                                        <span>View</span>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Top Selling Items in Period -->
                <div class="apos-card apos-full-card rep-card-no-margin">
                    <div class="rep-card-head">
                        <h3 class="rep-card-title">
                            <span class="rep-title-ico"><?php echo $svg_award; ?></span>
                            <span>Top Dishes (Selected Period)</span>
                        </h3>
                    </div>

                    <?php if (empty($top_dishes)) : ?>
                        <p class="rep-empty-dishes">No item sales data available.</p>
                    <?php else : ?>
                        <div class="rep-top-list">
                            <?php $rank = 1; foreach ($top_dishes as $d_name => $st) : ?>
                            <div class="rep-top-row">
                                <div class="rep-top-left">
                                    <span class="rep-rank-badge <?php echo $rank === 1 ? 'rank-1' : ''; ?>">
                                        #<?php echo $rank++; ?>
                                    </span>
                                    <div>
                                        <strong class="rep-dish-name"><?php echo esc_html($d_name); ?></strong>
                                        <small class="rep-dish-qty"><?php echo intval($st['qty']); ?> units sold</small>
                                    </div>
                                </div>
                                <strong class="rep-dish-rev"><?php echo $cur . number_format($st['rev'], 2); ?></strong>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#rep-print-btn').on('click', function() {
            window.print();
        });

        $('#rep-export-csv').on('click', function() {
            let csv = [];
            $('#rep-daily-table tr').each(function() {
                let row = [];
                $(this).find('th, td').not(':last-child').each(function() {
                    let txt = $(this).text().trim().replace(/"/g, '""');
                    row.push('"' + txt + '"');
                });
                csv.push(row.join(','));
            });

            let blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
            let url  = URL.createObjectURL(blob);
            let a    = document.createElement('a');
            a.href     = url;
            a.download = 'sales-report-<?php echo esc_js($from . '-to-' . $to); ?>.csv';
            a.click();
        });
    });
    </script>
    <?php
}