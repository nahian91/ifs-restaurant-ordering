<?php
if (!defined('ABSPATH')) exit;

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

$v_ex_ids = json_decode($view_item->extras, true) ?: [];
$v_extras = [];
foreach ($v_ex_ids as $eid) {
    if (isset($extras_map[$eid])) $v_extras[] = $extras_map[$eid];
}
$v_img = $view_item->image_url ?: 'https://via.placeholder.com/400x300?text=Antalya+Dish';
?>

<div class="apos-card apos-full-card itm-detail-wrapper">
    <div class="itm-view-grid">
        <!-- Left Column: Dish Image & Quick Card -->
        <div class="itm-view-left-col">
            <div class="itm-photo-container">
                <img src="<?php echo esc_url($v_img); ?>" alt="<?php echo esc_attr($view_item->name); ?>" class="itm-view-photo">
                <div class="itm-photo-badge">
                    <?php echo $svg_check; ?>
                    <span>Active on Menu</span>
                </div>
            </div>

            <div class="itm-quick-info-box">
                <div class="itm-q-row">
                    <span>Database ID:</span>
                    <strong>#<?php echo intval($view_item->id); ?></strong>
                </div>
                <div class="itm-q-row">
                    <span>Category:</span>
                    <strong style="color:#f97316;"><?php echo esc_html($view_item->category); ?></strong>
                </div>
                <div class="itm-q-row">
                    <span>Total Add-ons:</span>
                    <strong><?php echo count($v_extras); ?> Attached</strong>
                </div>
            </div>
        </div>

        <!-- Right Column: Details, Stats & Add-ons Breakdown -->
        <div class="itm-view-right-col">
            <div class="itm-view-head">
                <div>
                    <div class="itm-view-meta">
                        <span class="itm-view-id">DISH SPECIFICATION RECORD</span>
                    </div>
                    <h2 class="itm-view-title"><?php echo esc_html($view_item->name); ?></h2>
                </div>

                <div class="itm-view-btns">
                    <a href="?page=antalya-pos&tab=items&edit_item=<?php echo intval($view_item->id); ?>" class="apos-btn itm-btn-icon">
                        <?php echo $svg_edit; ?>
                        <span>Edit Dish</span>
                    </a>
                    <a href="?page=antalya-pos&tab=items&subtab=all&del_item=<?php echo intval($view_item->id); ?>" class="itm-act-btn act-del itm-del-lg js-confirm-del" data-name="<?php echo esc_attr($view_item->name); ?>">
                        <?php echo $svg_trash; ?>
                        <span>Delete</span>
                    </a>
                </div>
            </div>

            <!-- Stats Boxes -->
            <div class="itm-view-stats">
                <div class="itm-stat-box itm-stat-orange">
                    <div class="itm-stat-lbl">Base Menu Price</div>
                    <div class="itm-stat-val itm-val-orange"><?php echo $cur . number_format($view_item->price, 2); ?></div>
                </div>
                <div class="itm-stat-box itm-stat-green">
                    <div class="itm-stat-lbl">Optional Add-ons</div>
                    <div class="itm-stat-val"><?php echo count($v_extras); ?> Configured</div>
                </div>
                <div class="itm-stat-box itm-stat-blue">
                    <div class="itm-stat-lbl">Tablet Availability</div>
                    <div class="itm-stat-val itm-val-live">Instant Sync</div>
                </div>
            </div>

            <!-- Detailed Extras Section -->
            <h3 class="itm-view-ex-heading">
                <span class="itm-sparkles-ico"><?php echo $svg_sparkles; ?></span>
                <span>Attached Extras & Customization Add-ons</span>
            </h3>

            <?php if (empty($v_extras)) : ?>
                <div class="itm-view-ex-empty">
                    <p style="margin:0 0 10px; color:#64748b;">No optional extras or add-ons are currently attached to this dish.</p>
                    <a href="?page=antalya-pos&tab=items&edit_item=<?php echo intval($view_item->id); ?>" class="apos-btn-secondary" style="font-size:12px; padding:6px 14px;">Edit Dish to Attach Add-ons</a>
                </div>
            <?php else : ?>
                <table class="apos-table apos-full-table itm-view-ex-table">
                    <thead>
                        <tr>
                            <th>Add-on / Extra Name</th>
                            <th width="140">Extra Charge</th>
                            <th width="190" style="text-align:right;">Total with Add-on</th>
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
                            <td><strong class="itm-val-orange">+<?php echo $cur . number_format($vx->price, 2); ?></strong></td>
                            <td style="text-align:right;"><strong class="itm-val-green"><?php echo $cur . number_format($combined_total, 2); ?></strong></td>
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
        display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

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
    .itm-view-btns { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }

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
        let itemName = $(this).data('name') || 'this item';
        if (!confirm('Are you sure you want to delete "' + itemName + '"?')) {
            e.preventDefault();
        }
    });
});
</script>