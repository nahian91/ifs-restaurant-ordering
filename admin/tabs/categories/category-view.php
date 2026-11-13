<?php
if (!defined('ABSPATH')) exit;

$svg_folder   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>';
$svg_edit     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_trash    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';
$svg_utensils = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>';
$svg_plus     = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';

$assigned_dishes = isset($items_by_cat[$view_cat->name]) ? $items_by_cat[$view_cat->name] : [];
$avg_price = 0;
if (!empty($assigned_dishes)) {
    $sum = array_sum(array_map(function($d) { return floatval($d->price); }, $assigned_dishes));
    $avg_price = $sum / count($assigned_dishes);
}
?>

<div class="apos-card apos-full-card">
    <div class="cat-view-hero">
        <div class="cat-view-hero-left">
            <div class="cat-view-hero-ico"><?php echo $svg_folder; ?></div>
            <div>
                <div class="cat-view-id">CATEGORY ID #<?php echo intval($view_cat->id); ?></div>
                <h2 class="cat-view-title"><?php echo esc_html($view_cat->name); ?></h2>
            </div>
        </div>

        <div class="cat-view-btns">
            <a href="?page=antalya-pos&tab=categories&edit_cat=<?php echo intval($view_cat->id); ?>" class="apos-btn cat-btn-icon">
                <?php echo $svg_edit; ?> <span>Edit Category</span>
            </a>
            <a href="?page=antalya-pos&tab=categories&subtab=all&del_cat=<?php echo intval($view_cat->id); ?>" class="cat-act-btn act-del cat-del-lg js-confirm-del" data-name="<?php echo esc_attr($view_cat->name); ?>">
                <?php echo $svg_trash; ?> <span>Delete</span>
            </a>
        </div>
    </div>

    <!-- Category Stats -->
    <div class="cat-view-stats">
        <div class="cat-stat-box cat-stat-orange">
            <div class="cat-stat-lbl">Total Assigned Dishes</div>
            <div class="cat-stat-val"><?php echo count($assigned_dishes); ?> Dish(es)</div>
        </div>
        <div class="cat-stat-box cat-stat-green">
            <div class="cat-stat-lbl">Average Dish Price</div>
            <div class="cat-stat-val cat-val-green"><?php echo $cur . number_format($avg_price, 2); ?></div>
        </div>
        <div class="cat-stat-box cat-stat-blue">
            <div class="cat-stat-lbl">Tablet Menu Visibility</div>
            <div class="cat-stat-val cat-val-vis">
                <?php echo count($assigned_dishes) > 0 ? 'Visible on Tablet' : 'Hidden (No Dishes)'; ?>
            </div>
        </div>
    </div>

    <!-- Dishes in this Category -->
    <div class="cat-dishes-head">
        <h3 class="cat-dishes-title">
            <span class="cat-title-ico"><?php echo $svg_utensils; ?></span>
            <span>Menu Items in "<?php echo esc_html($view_cat->name); ?>"</span>
        </h3>
        <a href="?page=antalya-pos&tab=items&subtab=add" class="apos-btn-secondary cat-btn-icon cat-add-dish-sm">
            <?php echo $svg_plus; ?> <span>Add New Dish</span>
        </a>
    </div>

    <?php if (empty($assigned_dishes)) : ?>
        <div class="cat-dishes-empty">
            <p class="cat-dishes-empty-txt">No food items have been assigned to this category yet.</p>
            <a href="?page=antalya-pos&tab=items&subtab=add" class="apos-btn cat-btn-icon">
                <?php echo $svg_plus; ?> <span>Add First Dish to Category</span>
            </a>
        </div>
    <?php else : ?>
        <table class="apos-table apos-full-table cat-view-dishes-table">
            <thead>
                <tr>
                    <th class="col-vd-photo">Photo</th>
                    <th>Dish Name</th>
                    <th class="col-vd-addons">Add-ons</th>
                    <th class="col-vd-price">Price</th>
                    <th class="col-vd-action">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assigned_dishes as $dish) : 
                    $ex_arr = json_decode($dish->extras, true) ?: [];
                ?>
                <tr>
                    <td>
                        <img src="<?php echo esc_url($dish->image_url ?: 'https://via.placeholder.com/80?text=Dish'); ?>" alt="<?php echo esc_attr($dish->name); ?>" class="cat-dish-thumb">
                    </td>
                    <td><strong class="cat-dish-name"><?php echo esc_html($dish->name); ?></strong></td>
                    <td>
                        <?php if (!empty($ex_arr)) : ?>
                            <span class="cat-dish-addons"><?php echo count($ex_arr); ?> extra(s)</span>
                        <?php else : ?>
                            <span class="cat-dish-no-addons">None</span>
                        <?php endif; ?>
                    </td>
                    <td><strong class="cat-dish-price"><?php echo $cur . number_format($dish->price, 2); ?></strong></td>
                    <td>
                        <a href="?page=antalya-pos&tab=items&edit_item=<?php echo intval($dish->id); ?>" class="cat-act-btn act-edit">
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
    .cat-view-hero { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 18px; padding-bottom: 20px; border-bottom: 1.5px solid #f1f5f9; margin-bottom: 22px; }
    .cat-view-hero-left { display: flex; align-items: center; gap: 14px; }
    .cat-view-hero-ico { width: 52px; height: 52px; border-radius: 14px; background: #fff7ed; color: #f97316; border: 1px solid #fed7aa; display: flex; align-items: center; justify-content: center; }
    .cat-view-id { font-size: 12px; font-weight: 700; color: #64748b; }
    .cat-view-title { margin: 2px 0 0; font-size: 24px; font-weight: 800; color: #0f172a; }
    .cat-view-btns { display: flex; gap: 10px; flex-wrap: wrap; }
    .cat-del-lg { padding: 10px 16px; font-size: 13px; }

    .cat-view-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 26px; width: 100%; }
    .cat-stat-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px 20px; }
    .cat-stat-orange { border-left: 4px solid #f97316; }
    .cat-stat-green { border-left: 4px solid #10b981; }
    .cat-stat-blue { border-left: 4px solid #3b82f6; }
    .cat-stat-lbl { font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.4px; }
    .cat-stat-val { font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 6px; }
    .cat-val-green { color: #059669; }
    .cat-val-vis { font-size: 17px; color: #1d4ed8; }

    .cat-dishes-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px; }
    .cat-dishes-title { margin: 0; font-size: 16.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; }
    .cat-title-ico { color: #f97316; display: inline-flex; }
    .cat-add-dish-sm { padding: 7px 14px; font-size: 12.5px; }
    .cat-dishes-empty { background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 14px; padding: 36px 20px; text-align: center; color: #64748b; }
    .cat-dishes-empty-txt { margin: 0 0 12px; font-size: 14px; }

    .cat-view-dishes-table { box-shadow: none; border: 1px solid #e2e8f0; }
    .col-vd-photo { width: 75px; }
    .col-vd-addons { width: 160px; }
    .col-vd-price { width: 150px; }
    .col-vd-action { width: 140px; }

    .cat-dish-thumb { width: 46px; height: 46px; object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0; display: block; }
    .cat-dish-name { font-size: 14.5px; color: #0f172a; }
    .cat-dish-addons { color: #f97316; font-weight: 700; font-size: 12.5px; }
    .cat-dish-no-addons { color: #94a3b8; font-size: 12.5px; }
    .cat-dish-price { font-size: 14.5px; color: #0f172a; }
</style>

<script>
jQuery(document).ready(function($) {
    $(document).on('click', '.js-confirm-del', function(e) {
        if (!confirm('Delete category ' + ($(this).data('name') || 'this category') + '?')) {
            e.preventDefault();
        }
    });
});
</script>