<?php
if (!defined('ABSPATH')) exit;

function rtpos_view_settings($s = []) {
    // Safe fallback for settings if called without parameters
    if (empty($s) && function_exists('rtpos_get_settings')) {
        $s = rtpos_get_settings();
    }

    // Reusable SVG Icons
    $svg_check    = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
    $svg_settings = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>';
    $svg_store    = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2v0a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12v0a2 2 0 0 1-2-2V7"/></svg>';
    $svg_link     = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>';
    $svg_coins    = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>';
    $svg_msg      = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';
    $svg_hash     = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/></svg>';
    $svg_phone    = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>';
    $svg_email    = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>';
    $svg_receipt  = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>';
    $svg_copy     = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>';

    if (isset($_POST['rtpos_save_settings'])) {
        update_option('rtpos_settings', [
            'restaurant_name' => sanitize_text_field($_POST['restaurant_name']),
            'phone'           => sanitize_text_field($_POST['phone'] ?? ''),
            'email'           => sanitize_email($_POST['email'] ?? ''),
            'currency'        => sanitize_text_field($_POST['currency']),
            'tax_percent'     => floatval($_POST['tax_percent']),
            'menu_page_url'   => esc_url_raw($_POST['menu_page_url']),
            'order_prefix'    => sanitize_text_field($_POST['order_prefix'] ?? 'ORD-'),
            'receipt_footer'  => sanitize_text_field($_POST['receipt_footer'])
        ]);
        echo '<div class="apos-alert set-alert-flex">' . $svg_check . '<span>POS & Receipt settings saved successfully!</span></div>';
    }

    $s          = rtpos_get_settings();
    $pages      = get_pages(['sort_column' => 'post_title', 'sort_order' => 'ASC']) ?: [];
    $tax_rate   = floatval($s['tax_percent'] ?? 5);
    $tax_amt    = (1000 * $tax_rate) / 100;
    $tot_bill   = 1000 + $tax_amt;
    $ord_prefix = esc_attr($s['order_prefix'] ?? 'ORD-');
    $rest_phone = esc_attr($s['phone'] ?? '');
    $rest_email = esc_attr($s['email'] ?? '');
    ?>

    <style>
        .set-alert-flex { display: flex; align-items: center; gap: 8px; }
        .set-full-wrap { width: 100%; box-sizing: border-box; }
        .apos-full-card { width: 100%; max-width: 100%; box-sizing: border-box; }
        .set-card-no-margin { margin: 0; }
        .set-preview-card { margin: 0; background: #f8fafc; }

        /* Form & Controls */
        .apos-form-header { border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 22px; }
        .apos-form-header h3 {
            margin: 0; font-size: 18px; font-weight: 800; color: #0f172a;
            display: flex; align-items: center; gap: 8px;
        }
        .apos-form-header p { margin: 5px 0 0; font-size: 13px; color: #64748b; }
        .set-title-ico { color: #f97316; display: inline-flex; }

        .apos-form-group { margin-bottom: 18px; width: 100%; }
        .apos-label {
            display: flex; align-items: center; justify-content: space-between;
            font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 7px; letter-spacing: 0.2px;
        }
        .apos-label small { font-weight: 500; color: #64748b; font-size: 12px; }
        .apos-input-wrap { position: relative; display: flex; align-items: center; width: 100%; }
        .apos-input-prefix {
            position: absolute; left: 14px; font-weight: 800; color: #64748b; font-size: 15px;
            pointer-events: none; display: flex; align-items: center; justify-content: center;
        }
        .apos-input {
            width: 100%; padding: 11px 15px; border: 1.5px solid #e2e8f0; border-radius: 11px;
            font-size: 14px; font-weight: 500; color: #0f172a; background-color: #f8fafc;
            transition: all 0.22s ease; box-sizing: border-box;
        }
        .apos-input.has-prefix { padding-left: 40px; }
        .apos-input:hover { border-color: #cbd5e1; background-color: #fff; }
        .apos-input:focus {
            outline: none; background-color: #fff; border-color: #f97316;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.14);
        }
        select.apos-input {
            appearance: none; -webkit-appearance: none; padding-right: 38px; cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 14px center; background-size: 16px;
        }

        .set-url-row { display: flex; gap: 10px; flex-wrap: wrap; }
        .set-url-wrap { flex: 1; min-width: 220px; }
        .set-page-select { width: 200px; }

        .set-layout-grid { display: grid; grid-template-columns: 1.55fr 1fr; gap: 24px; align-items: start; width: 100%; }
        .set-two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; width: 100%; }
        @media (max-width: 960px) { .set-layout-grid { grid-template-columns: 1fr; } }
        @media (max-width: 640px) { .set-two-col { grid-template-columns: 1fr; } }

        .set-btn-icon { display: inline-flex; align-items: center; gap: 7px; }
        .set-form-footer {
            margin-top: 22px; padding-top: 16px; border-top: 1px solid #f1f5f9;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;
        }
        .set-sc-box {
            display: flex; align-items: center; gap: 8px; background: #f8fafc;
            padding: 7px 12px; border-radius: 9px; border: 1px solid #e2e8f0; font-size: 12.5px;
        }
        .set-sc-lbl { color: #64748b; }
        .set-sc-code { font-weight: 700; color: #f97316; }
        .set-copy-btn { padding: 4px 9px; font-size: 11.5px; }

        /* Highly Polished Professional Thermal Receipt Preview */
        .set-preview-head { text-align: center; margin-bottom: 16px; }
        .set-preview-badge {
            background: #e2e8f0; color: #334155; padding: 6px 14px; border-radius: 20px;
            font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
            display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        }
        .receipt-mock {
            background: #fff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 28px 24px;
            font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #111;
            box-shadow: 0 12px 30px -6px rgba(15, 23, 42, 0.1); max-width: 330px; margin: 0 auto;
            position: relative; line-height: 1.45;
        }
        .receipt-mock::after {
            content: ''; display: block; height: 12px; margin-top: 18px;
            background: linear-gradient(-45deg, transparent 33.33%, #cbd5e1 33.33%, #cbd5e1 66.66%, transparent 66.66%),
                        linear-gradient(45deg, transparent 33.33%, #cbd5e1 33.33%, #cbd5e1 66.66%, transparent 66.66%);
            background-size: 12px 16px;
        }
        .rc-center { text-align: center; }
        .rc-rest-title { margin: 0 0 3px; font-size: 18px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.8px; color: #000; }
        .rc-invoice-sub { font-size: 10px; font-weight: 800; color: #475569; margin-bottom: 6px; letter-spacing: 0.5px; }
        .rc-meta-line { font-size: 11px; color: #334155; }
        .rc-hr { border: none; border-top: 1px dashed #64748b; margin: 10px 0; }
        .rc-table-hdr { display: flex; justify-content: space-between; font-weight: 800; font-size: 11px; border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 6px; }
        .rc-row { display: flex; justify-content: space-between; }
        .rc-row-bold { font-weight: 700; }
        .rc-row-mb { margin-bottom: 5px; }
        .rc-row-sm { font-size: 11.5px; color: #334155; }
        .rc-row-grand { font-size: 14.5px; font-weight: 900; margin-top: 8px; border-top: 1.5px solid #000; padding-top: 6px; }
        .rc-footer-txt { text-align: center; font-size: 11px; color: #334155; margin-top: 10px; font-style: italic; }
    </style>

    <div class="set-full-wrap">
        <!-- Page Header -->
        <div class="apos-header">
            <div>
                <h1>POS & Receipt Settings</h1>
                <p>Configure restaurant branding, currency symbol, VAT/tax rates, and thermal receipt layout.</p>
            </div>
        </div>

        <div class="set-layout-grid">
            <!-- Left Column: Configuration Form -->
            <div class="apos-card apos-full-card set-card-no-margin">
                <div class="apos-form-header">
                    <h3>
                        <span class="set-title-ico"><?php echo $svg_settings; ?></span>
                        <span>General & Billing Configuration</span>
                    </h3>
                    <p>Update your store preferences below. Changes apply instantly to tablets and printed receipts.</p>
                </div>

                <form method="post">
                    <div class="apos-form-group">
                        <label class="apos-label">Restaurant Name <span>*</span></label>
                        <div class="apos-input-wrap">
                            <span class="apos-input-prefix"><?php echo $svg_store; ?></span>
                            <input type="text" name="restaurant_name" id="set-rest-name" class="apos-input has-prefix" value="<?php echo esc_attr($s['restaurant_name']); ?>" required>
                        </div>
                    </div>

                    <div class="set-two-col">
                        <div class="apos-form-group">
                            <label class="apos-label">Phone Number <small>(Optional)</small></label>
                            <div class="apos-input-wrap">
                                <span class="apos-input-prefix"><?php echo $svg_phone; ?></span>
                                <input type="text" name="phone" id="set-phone" class="apos-input has-prefix" value="<?php echo esc_attr($rest_phone); ?>" placeholder="+88017XXXXXXXX">
                            </div>
                        </div>

                        <div class="apos-form-group">
                            <label class="apos-label">Email Address <small>(Optional)</small></label>
                            <div class="apos-input-wrap">
                                <span class="apos-input-prefix"><?php echo $svg_email; ?></span>
                                <input type="email" name="email" id="set-email" class="apos-input has-prefix" value="<?php echo esc_attr($rest_email); ?>" placeholder="info@restaurant.com">
                            </div>
                        </div>
                    </div>

                    <div class="apos-form-group">
                        <label class="apos-label">Tablet Menu Page URL <span>*</span> <small>(Page with [restaurant_menu])</small></label>
                        <div class="set-url-row">
                            <div class="apos-input-wrap set-url-wrap">
                                <span class="apos-input-prefix"><?php echo $svg_link; ?></span>
                                <input type="url" name="menu_page_url" id="set-menu-url" class="apos-input has-prefix" value="<?php echo esc_attr($s['menu_page_url']); ?>" required>
                            </div>
                            <?php if (!empty($pages)) : ?>
                                <select id="set-page-picker" class="apos-input set-page-select">
                                    <option value="">Pick WP Page...</option>
                                    <?php foreach ($pages as $pg) : ?>
                                        <option value="<?php echo esc_url(get_permalink($pg->ID)); ?>"><?php echo esc_html($pg->post_title); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="set-two-col">
                        <div class="apos-form-group">
                            <label class="apos-label">Currency Symbol <span>*</span></label>
                            <div class="apos-input-wrap">
                                <span class="apos-input-prefix"><?php echo $svg_coins; ?></span>
                                <input type="text" name="currency" id="set-currency" class="apos-input has-prefix" value="<?php echo esc_attr($s['currency']); ?>" required>
                            </div>
                        </div>

                        <div class="apos-form-group">
                            <label class="apos-label">VAT / Tax Rate (%) <small>(0 for no tax)</small></label>
                            <div class="apos-input-wrap">
                                <span class="apos-input-prefix">%</span>
                                <input type="number" step="0.01" min="0" max="100" name="tax_percent" id="set-tax" class="apos-input has-prefix" value="<?php echo esc_attr($s['tax_percent']); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="apos-form-group">
                        <label class="apos-label">Order ID Prefix <small>(e.g. ORD-, #)</small></label>
                        <div class="apos-input-wrap">
                            <span class="apos-input-prefix"><?php echo $svg_hash; ?></span>
                            <input type="text" name="order_prefix" id="set-prefix" class="apos-input has-prefix" value="<?php echo esc_attr($s['order_prefix'] ?? 'ORD-'); ?>" placeholder="ORD-" required>
                        </div>
                    </div>

                    <div class="apos-form-group">
                        <label class="apos-label">Receipt Footer Message</label>
                        <div class="apos-input-wrap">
                            <span class="apos-input-prefix"><?php echo $svg_msg; ?></span>
                            <input type="text" name="receipt_footer" id="set-footer" class="apos-input has-prefix" value="<?php echo esc_attr($s['receipt_footer']); ?>" placeholder="Thank you for dining with us!">
                        </div>
                    </div>

                    <div class="set-form-footer">
                        <button type="submit" name="rtpos_save_settings" class="apos-btn set-btn-icon">
                            <?php echo $svg_check; ?>
                            <span>Save Configuration</span>
                        </button>

                        <div class="set-sc-box">
                            <span class="set-sc-lbl">Shortcode:</span>
                            <code id="rtpos-sc-text" class="set-sc-code">[restaurant_menu]</code>
                            <button type="button" id="rtpos-copy-sc" class="apos-btn-secondary set-btn-icon set-copy-btn">
                                <?php echo $svg_copy; ?>
                                <span>Copy</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Right Column: Professional Live Thermal Receipt Preview -->
            <div class="apos-card apos-full-card set-preview-card">
                <div class="set-preview-head">
                    <span class="set-preview-badge">
                        <?php echo $svg_receipt; ?>
                        <span>Professional Thermal Receipt Preview</span>
                    </span>
                </div>

                <div class="receipt-mock">
                    <div class="rc-center">
                        <h3 id="prev-rest-name" class="rc-rest-title"><?php echo esc_html($s['restaurant_name']); ?></h3>
                        <div id="prev-rest-contact" class="rc-meta-line" style="font-size:10px;">
                            <?php if (!empty($rest_phone)) echo 'Phone: ' . esc_html($rest_phone); ?>
                            <?php if (!empty($rest_phone) && !empty($rest_email)) echo ' | '; ?>
                            <?php if (!empty($rest_email)) echo esc_html($rest_email); ?>
                        </div>
                        <div class="rc-invoice-sub">*** CUSTOMER TAX INVOICE ***</div>
                        <div class="rc-meta-line">Table: <strong>Table 01</strong> | Order: <strong id="prev-order-id"><?php echo $ord_prefix; ?>104</strong></div>
                        <div class="rc-meta-line" style="font-size:9.5px; color:#555;"><?php echo date('M d, Y • h:i A'); ?></div>
                    </div>

                    <hr class="rc-hr">

                    <div class="rc-table-hdr">
                        <span>ITEM DESCRIPTION</span>
                        <span>TOTAL</span>
                    </div>

                    <div class="rc-row rc-row-mb">
                        <span>2x Adana Kebab</span>
                        <span><span class="prev-cur"><?php echo esc_html($s['currency']); ?></span>900.00</span>
                    </div>
                    <div class="rc-row rc-row-mb rc-row-sm" style="color:#475569; padding-left:8px;">
                        <span>  - Extra Cheese</span>
                        <span><span class="prev-cur"><?php echo esc_html($s['currency']); ?></span>50.00</span>
                    </div>
                    <div class="rc-row">
                        <span>1x Garlic Naan</span>
                        <span><span class="prev-cur"><?php echo esc_html($s['currency']); ?></span>100.00</span>
                    </div>

                    <hr class="rc-hr">

                    <div class="rc-row rc-row-sm rc-row-mb">
                        <span>Subtotal:</span>
                        <span><span class="prev-cur"><?php echo esc_html($s['currency']); ?></span>1,050.00</span>
                    </div>
                    <div class="rc-row rc-row-sm rc-row-mb">
                        <span>VAT / Tax (<span id="prev-tax-rate"><?php echo esc_html($tax_rate); ?></span>%):</span>
                        <span><span class="prev-cur"><?php echo esc_html($s['currency']); ?></span><span id="prev-tax-amt"><?php echo number_format($tax_amt, 2); ?></span></span>
                    </div>
                    
                    <!-- Payable, Payment Method & Return -->
                    <div class="rc-row rc-row-grand rc-row-mb">
                        <span>TOTAL PAYABLE:</span>
                        <span><span class="prev-cur"><?php echo esc_html($s['currency']); ?></span><span id="prev-grand-total"><?php echo number_format($tot_bill, 2); ?></span></span>
                    </div>
                    <div class="rc-row rc-row-sm rc-row-mb" style="border-top: 1px dotted #64748b; padding-top: 4px; margin-top: 4px;">
                        <span>Payment Method:</span>
                        <strong>CASH</strong>
                    </div>
                    <div class="rc-row rc-row-sm rc-row-mb">
                        <span>Cash Tendered:</span>
                        <span><span class="prev-cur"><?php echo esc_html($s['currency']); ?></span>1,200.00</span>
                    </div>
                    <div class="rc-row rc-row-sm rc-row-bold" style="color: #047857;">
                        <span>CHANGE / RETURN:</span>
                        <span><span class="prev-cur"><?php echo esc_html($s['currency']); ?></span>150.00</span>
                    </div>

                    <hr class="rc-hr">

                    <div id="prev-footer" class="rc-footer-txt">
                        <?php echo esc_html($s['receipt_footer']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        let copySvg  = <?php echo json_encode($svg_copy); ?>;
        let checkSvg = <?php echo json_encode($svg_check); ?>;

        // Auto-fill URL when selecting a WP Page
        $('#set-page-picker').on('change', function() {
            let url = $(this).val();
            if (url) $('#set-menu-url').val(url);
        });

        // Copy Shortcode
        $('#rtpos-copy-sc').on('click', function() {
            navigator.clipboard.writeText($('#rtpos-sc-text').text());
            let btn = $(this);
            btn.html(checkSvg + '<span>Copied</span>');
            setTimeout(() => btn.html(copySvg + '<span>Copy</span>'), 1800);
        });

        // Live Professional Receipt Preview Updates
        $('#set-rest-name').on('input', function() {
            $('#prev-rest-name').text($(this).val() || 'RESTAURANT');
        });
        $('#set-phone, #set-email').on('input', function() {
            let ph = $('#set-phone').val().trim();
            let em = $('#set-email').val().trim();
            let contactStr = '';
            if (ph && em) contactStr = 'Phone: ' + ph + ' | ' + em;
            else if (ph) contactStr = 'Phone: ' + ph;
            else if (em) contactStr = em;
            $('#prev-rest-contact').text(contactStr);
        });
        $('#set-currency').on('input', function() {
            $('.prev-cur').text($(this).val() || '৳');
        });
        $('#set-prefix').on('input', function() {
            let pfx = $(this).val() || 'ORD-';
            $('#prev-order-id').text(pfx + '104');
        });
        $('#set-tax').on('input', function() {
            let rate = parseFloat($(this).val()) || 0;
            let tax  = (1000 * rate) / 100;
            let tot  = 1000 + tax;
            $('#prev-tax-rate').text(rate);
            $('#prev-tax-amt').text(tax.toFixed(2));
            $('#prev-grand-total').text(tot.toFixed(2));
        });
        $('#set-footer').on('input', function() {
            $('#prev-footer').text($(this).val() || 'Thank you!');
        });
    });
    </script>
    <?php
}