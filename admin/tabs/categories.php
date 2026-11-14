<?php
/**
 * Antalya POS Menu Categories Controller & Router
 *
 * @package Antalya_POS
 * @since 6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ensure extended category columns (e.g., category_order) exist on rtpos_categories
 */
function rtpos_ensure_category_cols() {
    global $wpdb;
    $t_cats = $wpdb->prefix . 'rtpos_categories';
    $cols   = $wpdb->get_col("DESC {$t_cats}", 0);

    if (is_array($cols) && !in_array('category_order', $cols, true)) {
        $wpdb->query("ALTER TABLE {$t_cats} ADD COLUMN category_order int(5) NOT NULL DEFAULT 0 AFTER name");
    }
}

function rtpos_view_categories($s = []) {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'antalya-pos'));
    }

    global $wpdb;
    rtpos_ensure_category_cols();

    $t_cats = $wpdb->prefix . 'rtpos_categories';
    $t_menu = $wpdb->prefix . 'rtpos_menu';

    // Safe fallback for settings & currency if called without parameters
    if (empty($s) && function_exists('rtpos_get_settings')) {
        $s = rtpos_get_settings();
    }
    $cur = esc_html($s['currency'] ?? '৳');

    $svg_check = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
    $svg_alert = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';

    // Include Delete Handler Safely
    $cats_dir = plugin_dir_path(__FILE__) . 'categories/';
    if (file_exists($cats_dir . 'category-delete.php')) {
        include_once $cats_dir . 'category-delete.php';
    }

    // 1. Handle Save / Update Category
    if (isset($_POST['rtpos_save_cat']) && !empty($_POST['cat_name'])) {
        if (isset($_POST['_wpnonce'])) {
            check_admin_referer('rtpos_save_cat_action');
        }

        $new_name  = sanitize_text_field(wp_unslash($_POST['cat_name']));
        $cat_order = isset($_POST['cat_order']) ? intval($_POST['cat_order']) : 0;

        if (!empty($_POST['edit_cat_id'])) {
            $edit_id  = intval($_POST['edit_cat_id']);
            $old_name = $wpdb->get_var(
                $wpdb->prepare("SELECT name FROM {$t_cats} WHERE id = %d", $edit_id)
            );

            $wpdb->update(
                $t_cats,
                [
                    'name'           => $new_name,
                    'category_order' => $cat_order,
                ],
                ['id' => $edit_id],
                ['%s', '%d'],
                ['%d']
            );

            if ($old_name && $old_name !== $new_name) {
                $wpdb->update(
                    $t_menu,
                    ['category' => $new_name],
                    ['category' => $old_name],
                    ['%s'],
                    ['%s']
                );
            }
            echo '<div class="apos-alert cat-alert-flex">' . $svg_check . '<span>' . esc_html__('Category updated successfully!', 'antalya-pos') . '</span></div>';
        } else {
            $exists = $wpdb->get_var(
                $wpdb->prepare("SELECT id FROM {$t_cats} WHERE name = %s", $new_name)
            );

            if ($exists) {
                echo '<div class="apos-alert cat-alert-flex" style="background:#fef2f2; border-color:#fecaca; color:#991b1b;">' . $svg_alert . '<span>' . sprintf(
                    /* translators: %s: category name */
                    esc_html__('Category "%s" already exists!', 'antalya-pos'),
                    esc_html($new_name)
                ) . '</span></div>';
            } else {
                $wpdb->insert(
                    $t_cats,
                    [
                        'name'           => $new_name,
                        'category_order' => $cat_order,
                    ],
                    ['%s', '%d']
                );
                echo '<div class="apos-alert cat-alert-flex">' . $svg_check . '<span>' . esc_html__('New category added!', 'antalya-pos') . '</span></div>';
            }
        }
    }

    // Routing: 'all', 'add', or 'view'
    $allowed_subtabs = ['all', 'add', 'view'];
    $raw_subtab      = isset($_GET['subtab']) ? sanitize_key($_GET['subtab']) : 'all';
    $subtab          = in_array($raw_subtab, $allowed_subtabs, true) ? $raw_subtab : 'all';
    $edit_cat        = null;
    $view_cat        = null;

    if (isset($_GET['edit_cat'])) {
        $edit_cat = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$t_cats} WHERE id = %d", intval($_GET['edit_cat']))
        );
        $subtab = 'add';
    } elseif (isset($_GET['view_cat'])) {
        $view_cat = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$t_cats} WHERE id = %d", intval($_GET['view_cat']))
        );
        $subtab = 'view';
    }

    $cats      = $wpdb->get_results("SELECT * FROM {$t_cats} ORDER BY category_order ASC, id DESC") ?: [];
    $all_items = $wpdb->get_results("SELECT id, name, category, price, image_url, extras FROM {$t_menu} ORDER BY item_order ASC, name ASC") ?: [];

    // Group full dish objects by category
    $items_by_cat = [];
    foreach ($all_items as $itm) {
        $items_by_cat[$itm->category][] = $itm;
    }
    ?>

    <style>
        .cat-alert-flex { display: flex; align-items: center; gap: 8px; }
        .cat-full-wrap { width: 100%; box-sizing: border-box; }
        .apos-full-card { width: 100%; max-width: 100%; box-sizing: border-box; }
        .apos-full-table { width: 100%; max-width: 100%; }

        /* Form Styles */
        .apos-form-header { border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 22px; }
        .apos-form-header h3 { margin: 0; font-size: 18px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; }
        .apos-form-header p { margin: 5px 0 0; font-size: 13px; color: #64748b; }
        .apos-form-group { margin-bottom: 18px; width: 100%; }
        .apos-label { display: flex; align-items: center; justify-content: space-between; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 7px; }
        .apos-label small { font-weight: 500; color: #64748b; font-size: 12px; }
        .apos-input-wrap { position: relative; display: flex; align-items: center; width: 100%; }
        .apos-input-prefix { position: absolute; left: 14px; font-weight: 800; color: #64748b; font-size: 15px; pointer-events: none; display: inline-flex; align-items: center; }
        .apos-input {
            width: 100%; padding: 11px 15px; border: 1.5px solid #e2e8f0; border-radius: 11px;
            font-size: 14px; font-weight: 500; color: #0f172a; background-color: #f8fafc; box-sizing: border-box;
        }
        .apos-input.has-prefix { padding-left: 40px; }
        .apos-input:focus { outline: none; background-color: #fff; border-color: #f97316; box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.14); }
        .cat-form-footer { margin-top: 22px; padding-top: 16px; border-top: 1px solid #f1f5f9; display: flex; gap: 10px; }

        /* Table & Cards */
        .cat-empty-card { text-align: center; padding: 60px 20px; }
        .cat-empty-ico { width: 56px; height: 56px; border-radius: 16px; background: #fff7ed; color: #f97316; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px; }
        .cat-empty-title { margin: 0 0 8px; font-size: 19px; }
        .cat-empty-desc { color: #64748b; margin: 0 0 20px; font-size: 14px; }

        .col-cat-id { width: 85px; }
        .col-cat-dishes { width: 190px; }
        .col-cat-actions { width: 235px; }

        .cat-name-link { font-size: 15px; font-weight: 700; color: #0f172a; text-decoration: none; }
        .cat-name-link:hover { color: #f97316; }
        .cat-dish-badge { padding: 4px 11px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block; }
        .cat-badge-active { background: #fff7ed; color: #ea580c; }
        .cat-badge-empty { background: #f1f5f9; color: #64748b; }

        .cat-act-group { display: flex; gap: 7px; align-items: center; }
        .cat-act-btn { padding: 6px 11px; border-radius: 7px; font-size: 12px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .act-view { background: #eff6ff; color: #1d4ed8; }
        .act-edit { background: #f1f5f9; color: #334155; }
        .act-del  { background: #fef2f2; color: #dc2626; }
        .cat-btn-icon { display: inline-flex; align-items: center; gap: 7px; }
    </style>

    <div class="cat-full-wrap">
        <?php
        if (file_exists($cats_dir . 'category-tabs.php')) {
            include $cats_dir . 'category-tabs.php';
        }

        if ($subtab === 'view' && $view_cat && file_exists($cats_dir . 'category-view.php')) {
            include $cats_dir . 'category-view.php';
        } elseif ($subtab === 'add' && file_exists($cats_dir . 'category-add-edit.php')) {
            include $cats_dir . 'category-add-edit.php';
        } elseif (file_exists($cats_dir . 'category-list.php')) {
            include $cats_dir . 'category-list.php';
        }
        ?>
    </div>
    <?php
}