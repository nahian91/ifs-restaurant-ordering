<?php
if (!defined('ABSPATH')) exit;

$svg_sparkles = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/></svg>';
$svg_edit     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_trash    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';
$svg_utensils = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>';

$assigned_dishes = isset($dishes_by_extra[$view_extra->id]) ? $dishes_by_extra[$view_extra->id] : [];
?>

<div class="apos-card apos-full-card">
    <div class="ex-view-hero">
        <div class="ex-view-hero-left">
            <div class="ex-view-hero-ico"><?php echo $svg_sparkles; ?></div>
            <div>
                <div class="ex-view-id">ADD-ON ID #<?php echo intval($view_extra->id); ?></div>
                <h2 class="ex-view-title"><?php echo esc_html($view_extra->name); ?></h2>
            </div>
        </div>

        <div class="ex-view-btns">
            <a href="?page=antalya-pos&tab=extras&edit_extra=<?php echo intval($view_extra->id); ?>" class="apos-btn ex-btn-icon">
                <?php echo $svg_edit; ?> <span>Edit Extra</span>
            </a>
            <a href="?page=antalya-pos&tab=extras&subtab=all&del_extra=<?php echo intval($view_extra->id); ?>" class="ex-act-btn act-del ex-del-lg js-confirm-del" data-name="<?php echo esc_attr($view_extra->name); ?>">
                <?php echo $svg_trash; ?> <span>Delete</span>
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="ex-view-stats">
        <div class="ex-stat-box ex-stat-orange">
            <div class="ex-stat-lbl">Add-on Price</div>
            <div class="ex-stat-val ex-val-orange">+<?php echo $cur . number_format($view_extra->price, 2); ?></div>
        </div>
        <div class="ex-stat-box ex-stat-green">
            <div class="ex-stat-lbl">Attached Menu Dishes</div>
            <div class="ex-stat-val"><?php echo count($assigned_dishes); ?> Dish(es)</div>
        </div>
        <div class="ex-stat-box ex-stat-blue">
            <div class="ex-stat-lbl">Availability Status</div>
            <div class="ex-stat-val ex-val-live">Active on Customer Tablet</div>
        </div>
    </div>

    <!-- Assigned Dishes Table -->
    <h3 class="ex-dishes-heading">
        <span class="ex-title-ico"><?php echo $svg_utensils; ?></span>
        <span>Dishes Offering "<?php echo esc_html($view_extra->name); ?>"</span>
    </h3>

    <?php if (empty($assigned_dishes)) : ?>
        <div class="ex-dishes-empty">
            <p class="ex-dishes-empty-txt">This add-on is not attached to any specific dish yet.</p>
            <a href="?page=antalya-pos&tab=items&subtab=all" class="apos-btn-secondary">Go to Food Items to Attach</a>
        </div>
    <?php else : ?>
        <table class="apos-table apos-full-table ex-view-dishes-table">
            <thead>
                <tr>
                    <th class="col-ed-photo">Photo</th>
                    <th>Dish Name</th>
                    <th>Category</th>
                    <th>Base Price</th>
                    <th>Price With This Extra</th>
                    <th class="col-ed-action">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assigned_dishes as $dish) : 
                    $combined = floatval($dish->price) + floatval($view_extra->price);
                ?>
                <tr>
                    <td>
                        <img src="<?php echo esc_url($dish->image_url ?: 'https://via.placeholder.com/80?text=Dish'); ?>" alt="<?php echo esc_attr($dish->name); ?>" class="ex-dish-thumb">
                    </td>
                    <td><strong><?php echo esc_html($dish->name); ?></strong></td>
                    <td><span class="ex-dish-cat"><?php echo esc_html($dish->category); ?></span></td>
                    <td><?php echo $cur . number_format($dish->price, 2); ?></td>
                    <td><strong class="ex-val-green"><?php echo $cur . number_format($combined, 2); ?></strong></td>
                    <td>
                        <a href="?page=antalya-pos&tab=items&edit_item=<?php echo intval($dish->id); ?>" class="ex-act-btn act-edit">
                            <?php echo $svg_edit; ?> <span>Edit Dish</span>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
    .ex-view-hero { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 18px; padding-bottom: 20px; border-bottom: 1.5px solid #f1f5f9; margin-bottom: 22px; }
    .ex-view-hero-left { display: flex; align-items: center; gap: 14px; }
    .ex-view-hero-ico { width: 52px; height: 52px; border-radius: 14px; background: #fff7ed; color: #f97316; border: 1px solid #fed7aa; display: flex; align-items: center; justify-content: center; }
    .ex-view-id { font-size: 12px; font-weight: 700; color: #64748b; }
    .ex-view-title { margin: 2px 0 0; font-size: 24px; font-weight: 800; color: #0f172a; }
    .ex-view-btns { display: flex; gap: 10px; flex-wrap: wrap; }
    .ex-del-lg { padding: 10px 16px; font-size: 13px; }

    .ex-view-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 26px; width: 100%; }
    .ex-stat-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px 20px; }
    .ex-stat-orange { border-left: 4px solid #f97316; }
    .ex-stat-green { border-left: 4px solid #10b981; }
    .ex-stat-blue { border-left: 4px solid #3b82f6; }
    .ex-stat-lbl { font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.4px; }
    .ex-stat-val { font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 6px; }
    .ex-val-orange { color: #f97316; }
    .ex-val-live { color: #059669; font-size: 18px; }

    .ex-dishes-heading { margin: 0 0 14px; font-size: 16.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; }
    .ex-title-ico { color: #f97316; display: inline-flex; }
    .ex-dishes-empty { background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 14px; padding: 34px 20px; text-align: center; color: #64748b; }
    .ex-dishes-empty-txt { margin: 0 0 12px; font-size: 14px; }

    .ex-view-dishes-table { box-shadow: none; border: 1px solid #e2e8f0; }
    .col-ed-photo { width: 70px; }
    .col-ed-action { width: 130px; }
    .ex-dish-thumb { width: 44px; height: 44px; object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0; display: block; }
    .ex-dish-cat { background: #f1f5f9; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block; }
    .ex-val-green { color: #059669; }
</style>

<script>
jQuery(document).ready(function($) {
    $(document).on('click', '.js-confirm-del', function(e) {
        if (!confirm('Delete extra ' + ($(this).data('name') || 'this extra') + '?')) {
            e.preventDefault();
        }
    });
});
</script>