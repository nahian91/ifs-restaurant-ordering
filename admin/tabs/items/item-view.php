<?php
/**
 * Antalya POS Single Food Item Specification View
 *
 * @package Antalya_POS
 * @since 6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options') || empty($view_item)) {
    return;
}

// Safe fallback for currency if not passed
if (!isset($cur)) {
    $s   = rtpos_get_settings();
    $cur = esc_html($s['currency'] ?? '৳');
}

$svg_edit     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_trash    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';
$svg_sparkles = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/></svg>';
$svg_tag      = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><path d="M7 7h.01"/></svg>';
$svg_check    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
$svg_alert    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';

$item_id  = intval($view_item->id);
$v_ex_ids = !empty($view_item->extras) ? json_decode($view_item->extras, true) : [];
if (!is_array($v_ex_ids)) {
    $v_ex_ids = [];
}

$v_extras = [];
foreach ($v_ex_ids as $eid) {
    $eid_int = intval($eid);
    if (isset($extras_map[$eid_int])) {
        $v_extras[] = $extras_map[$eid_int];
    }
}

$v_img          = !empty($view_item->image_url) ? $view_item->image_url : 'https://via.placeholder.com/400x300?text=Antalya+Dish';
$is_avail       = isset($view_item->is_available) ? intval($view_item->is_available) : 1;
$base_items_url = admin_url('admin.php?page=antalya-pos&tab=items');
$edit_url       = esc_url(add_query_arg('edit_item', $item_id, $base_items_url));
$del_url        = wp_nonce_url(
    add_query_arg(['subtab' => 'all', 'del_item' => $item_id], $base_items_url),
    'rtpos_delete_item_' . $item_id
);
?>

<div class="apos-card apos-full-card itm-detail-wrapper">
    <div class="itm-view-grid">
        <!-- Left Column: Dish Image & Quick Card -->
        <div class="itm-view-left-col">
            <div class="itm-photo-container">
                <img src="<?php echo esc_url($v_img); ?>" alt="<?php echo esc_attr($view_item->name); ?>" class="itm-view-photo">
                <div class="itm-photo-badge <?php echo $is_avail ? '' : 'is-out'; ?>">
                    <?php echo $is_avail ? $svg_check : $svg_alert; ?>
                    <span><?php echo $is_avail ? esc_html__('Active on Menu', 'antalya-pos') : esc_html__('Out of Stock', 'antalya-pos'); ?></span>
                </div>
            </div>

            <div class="itm-quick-info-box">
                <div class="itm-q-row">
                    <span><?php esc_html_e('Database ID:', 'antalya-pos'); ?></span>
                    <strong>#<?php echo $item_id; ?></strong>
                </div>
                <div class="itm-q-row">
                    <span><?php esc_html_e('Category:', 'antalya-pos'); ?></span>
                    <strong style="color:#f97316;"><?php echo esc_html($view_item->category); ?></strong>
                </div>
                <?php if (!empty($view_item->ratio)) : ?>
                    <div class="itm-q-row">
                        <span><?php esc_html_e('Portion / Ratio:', 'antalya-pos'); ?></span>
                        <strong><?php echo esc_html($view_item->ratio); ?></strong>
                    </div>
                <?php endif; ?>
                <div class="itm-q-row">
                    <span><?php esc_html_e('Total Add-ons:', 'antalya-pos'); ?></span>
                    <strong>
                        <?php
                        printf(
                            /* translators: %d: number of attached add-ons */
                            esc_html__('%d Attached', 'antalya-pos'),
                            count($v_extras)
                        );
                        ?>
                    </strong>
                </div>
            </div>
        </div>

        <!-- Right Column: Details, Stats & Add-ons Breakdown -->
        <div class="itm-view-right-col">
            <div class="itm-view-head">
                <div>
                    <div class="itm-view-meta">
                        <span class="itm-view-id"><?php esc_html_e('DISH SPECIFICATION RECORD', 'antalya-pos'); ?></span>
                    </div>
                    <h2 class="itm-view-title"><?php echo esc_html($view_item->name); ?></h2>
                    <?php if (!empty($view_item->description)) : ?>
                        <p class="itm-view-desc"><?php echo esc_html($view_item->description); ?></p>
                    <?php endif; ?>
                </div>

                <div class="itm-view-btns">
                    <a href="<?php echo $edit_url; ?>" class="apos-btn itm-btn-icon">
                        <?php echo $svg_edit; ?>
                        <span><?php esc_html_e('Edit Dish', 'antalya-pos'); ?></span>
                    </a>
                    <a href="<?php echo esc_url($del_url); ?>" class="itm-act-btn act-del itm-del-lg js-confirm-del" data-name="<?php echo esc_attr($view_item->name); ?>">
                        <?php echo $svg_trash; ?>
                        <span><?php esc_html_e('Delete', 'antalya-pos'); ?></span>
                    </a>
                </div>
            </div>

            <!-- Stats Boxes -->
            <div class="itm-view-stats">
                <div class="itm-stat-box itm-stat-orange">
                    <div class="itm-stat-lbl"><?php esc_html_e('Base Menu Price', 'antalya-pos'); ?></div>
                    <div class="itm-stat-val itm-val-orange"><?php echo $cur . esc_html(number_format((float) $view_item->price, 2)); ?></div>
                </div>
                <div class="itm-stat-box itm-stat-green">
                    <div class="itm-stat-lbl"><?php esc_html_e('Optional Add-ons', 'antalya-pos'); ?></div>
                    <div class="itm-stat-val">
                        <?php
                        printf(
                            /* translators: %d: count of configured extras */
                            esc_html__('%d Configured', 'antalya-pos'),
                            count($v_extras)
                        );
                        ?>
                    </div>
                </div>
                <div class="itm-stat-box itm-stat-blue">
                    <div class="itm-stat-lbl"><?php esc_html_e('Tablet Availability', 'antalya-pos'); ?></div>
                    <div class="itm-stat-val itm-val-live">
                        <?php echo $is_avail ? esc_html__('In Stock (Live)', 'antalya-pos') : esc_html__('Unavailable', 'antalya-pos'); ?>
                    </div>
                </div>
            </div>

            <!-- Detailed Extras Section -->
            <h3 class="itm-view-ex-heading">
                <span class="itm-sparkles-ico"><?php echo $svg_sparkles; ?></span>
                <span><?php esc_html_e('Attached Extras & Customization Add-ons', 'antalya-pos'); ?></span>
            </h3>

            <?php if (empty($v_extras)) : ?>
                <div class="itm-view-ex-empty">
                    <p style="margin:0 0 12px; color:#64748b;"><?php esc_html_e('No optional extras or add-ons are currently attached to this dish.', 'antalya-pos'); ?></p>
                    <a href="<?php echo $edit_url; ?>" class="apos-btn-secondary" style="font-size:12px; padding:7px 14px;"><?php esc_html_e('Edit Dish to Attach Add-ons', 'antalya-pos'); ?></a>
                </div>
            <?php else : ?>
                <table class="apos-table apos-full-table itm-view-ex-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Add-on / Extra Name', 'antalya-pos'); ?></th>
                            <th width="140"><?php esc_html_e('Extra Charge', 'antalya-pos'); ?></th>
                            <th width="190" style="text-align:right;"><?php esc_html_e('Total with Add-on', 'antalya-pos'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($v_extras as $vx) :
                            $combined_total = floatval($view_item->price) + floatval($vx->price);
                        ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <span style="color:#f97316; display:inline-flex;"><?php echo $svg_tag; ?></span>
                                    <strong><?php echo esc_html($vx->name); ?></strong>
                                </div>
                            </td>
                            <td><strong class="itm-val-orange">+<?php echo $cur . esc_html(number_format((float) $vx->price, 2)); ?></strong></td>
                            <td style="text-align:right;"><strong class="itm-val-green"><?php echo $cur . esc_html(number_format($combined_total, 2)); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .itm-detail-wrapper { padding: 4px; }
    .itm-view-grid {
        display: grid; grid-template-columns: 320px 1fr; gap: 28px; align-items: start;
    }
    @media (max-width: 920px) { .itm-view-grid { grid-template-columns: 1fr; } }

    .itm-view-left-col { display: flex; flex-direction: column; gap: 16px; }
    .itm-photo-container { position: relative; width: 100%; border-radius: 18px; overflow: hidden; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08); border: 1px solid #e2e8f0; }
    .itm-view-photo { width: 100%; height: 260px; object-fit: cover; display: block; }
    .itm-photo-badge {
        position: absolute; bottom: 12px; left: 12px; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(4px);
        color: #10b981; padding: 5px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 700;
        display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }
    .itm-photo-badge.is-out { color: #f87171; }

    .itm-quick-info-box {
        background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 16px 20px;
        display: flex; flex-direction: column; gap: 10px;
    }
    .itm-q-row { display: flex; justify-content: space-between; font-size: 13.5px; color: #64748b; }
    .itm-q-row strong { color: #0f172a; }

    .itm-view-head {
        display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap;
        gap: 16px; padding-bottom: 20px; border-bottom: 1.5px solid #f1f5f9; margin-bottom: 22px;
    }
    .itm-view-meta { margin-bottom: 4px; }
    .itm-view-id { font-size: 11.5px; font-weight: 800; color: #64748b; letter-spacing: 0.5px; }
    .itm-view-title { margin: 0; font-size: 26px; font-weight: 900; color: #0f172a; letter-spacing: -0.3px; }
    .itm-view-desc { margin: 8px 0 0; font-size: 13.5px; color: #64748b; line-height: 1.55; max-width: 620px; }
    .itm-view-btns { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .itm-del-lg { padding: 10px 16px; border-radius: 11px; font-size: 13px; }

    .itm-view-stats {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 16px; margin-bottom: 24px;
    }
    .itm-stat-box {
        background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px 20px;
        box-shadow: 0 2px 4px rgba(15, 23, 42, 0.02);
    }
    .itm-stat-orange { border-left: 4px solid #f97316; }
    .itm-stat-green { border-left: 4px solid #10b981; }
    .itm-stat-blue { border-left: 4px solid #3b82f6; }
    .itm-stat-lbl { font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; }
    .itm-stat-val { font-size: 20px; font-weight: 800; color: #0f172a; margin-top: 6px; }
    .itm-val-orange { color: #f97316; font-family: 'Courier New', Courier, monospace; }
    .itm-val-live { color: #059669; font-size: 16px; }

    .itm-view-ex-heading {
        margin: 0 0 14px; font-size: 16px; font-weight: 800; color: #0f172a;
        display: flex; align-items: center; gap: 8px;
    }
    .itm-sparkles-ico { color: #f97316; display: inline-flex; }
    .itm-view-ex-empty {
        background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 14px;
        padding: 26px; text-align: center; color: #64748b; font-size: 13.5px;
    }
    .itm-view-ex-table { box-shadow: none; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
    .itm-val-green { color: #059669; font-family: 'Courier New', Courier, monospace; }
</style>

<script>
jQuery(document).ready(function($) {
    $(document).on('click', '.js-confirm-del', function(e) {
        let itemName = $(this).data('name') || <?php echo wp_json_encode(__('this item', 'antalya-pos')); ?>;
        let msg      = <?php echo wp_json_encode(__('Are you sure you want to delete "%s"?', 'antalya-pos')); ?>.replace('%s', itemName);
        if (!confirm(msg)) {
            e.preventDefault();
        }
    });
});
</script>