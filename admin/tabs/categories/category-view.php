<?php
/**
 * Antalya POS Single Menu Category Details View
 *
 * @package Antalya_POS
 * @since 6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options') || empty($view_cat)) {
    return;
}

// Safe fallback for currency if not passed
if (!isset($cur)) {
    $s   = rtpos_get_settings();
    $cur = esc_html($s['currency'] ?? '৳');
}

$svg_folder   = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>';
$svg_edit     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_trash    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';
$svg_utensils = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>';
$svg_plus     = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';

$cat_id          = intval($view_cat->id);
$assigned_dishes = isset($items_by_cat[$view_cat->name]) && is_array($items_by_cat[$view_cat->name]) ? $items_by_cat[$view_cat->name] : [];

$avg_price = 0.0;
if (!empty($assigned_dishes)) {
    $sum = array_sum(array_map(function($d) {
        return floatval($d->price ?? 0);
    }, $assigned_dishes));
    $avg_price = $sum / count($assigned_dishes);
}

$base_cats_url  = admin_url('admin.php?page=antalya-pos&tab=categories');
$base_items_url = admin_url('admin.php?page=antalya-pos&tab=items');

$edit_url       = esc_url(add_query_arg('edit_cat', $cat_id, $base_cats_url));
$del_url        = wp_nonce_url(
    add_query_arg(['subtab' => 'all', 'del_cat' => $cat_id], $base_cats_url),
    'rtpos_delete_cat_' . $cat_id
);
$add_item_url   = esc_url(add_query_arg('subtab', 'add', $base_items_url));
?>

<div class="apos-card apos-full-card">
    <div class="cat-view-hero">
        <div class="cat-view-hero-left">
            <?php if (!empty($view_cat->image)) : ?>
                <img src="<?php echo esc_url($view_cat->image); ?>" alt="<?php echo esc_attr($view_cat->name); ?>" class="cat-view-hero-img">
            <?php else : ?>
                <div class="cat-view-hero-ico"><?php echo $svg_folder; ?></div>
            <?php endif; ?>
            <div>
                <div class="cat-view-id">
                    <?php
                    printf(
                        /* translators: %d: category ID */
                        esc_html__('CATEGORY ID #%d', 'antalya-pos'),
                        $cat_id
                    );
                    ?>
                </div>
                <h2 class="cat-view-title"><?php echo esc_html($view_cat->name); ?></h2>
            </div>
        </div>

        <div class="cat-view-btns">
            <a href="<?php echo $edit_url; ?>" class="apos-btn cat-btn-icon">
                <?php echo $svg_edit; ?>
                <span><?php esc_html_e('Edit Category', 'antalya-pos'); ?></span>
            </a>
            <a href="<?php echo esc_url($del_url); ?>" class="cat-act-btn act-del cat-del-lg js-confirm-del" data-name="<?php echo esc_attr($view_cat->name); ?>">
                <?php echo $svg_trash; ?>
                <span><?php esc_html_e('Delete', 'antalya-pos'); ?></span>
            </a>
        </div>
    </div>

    <!-- Category Stats -->
    <div class="cat-view-stats">
        <div class="cat-stat-box cat-stat-orange">
            <div class="cat-stat-lbl"><?php esc_html_e('Total Assigned Dishes', 'antalya-pos'); ?></div>
            <div class="cat-stat-val">
                <?php
                printf(
                    /* translators: %d: count of assigned dishes */
                    esc_html__('%d Dish(es)', 'antalya-pos'),
                    count($assigned_dishes)
                );
                ?>
            </div>
        </div>
        <div class="cat-stat-box cat-stat-green">
            <div class="cat-stat-lbl"><?php esc_html_e('Average Dish Price', 'antalya-pos'); ?></div>
            <div class="cat-stat-val cat-val-green"><?php echo $cur . esc_html(number_format($avg_price, 2)); ?></div>
        </div>
        <div class="cat-stat-box cat-stat-blue">
            <div class="cat-stat-lbl"><?php esc_html_e('Tablet Menu Visibility', 'antalya-pos'); ?></div>
            <div class="cat-stat-val cat-val-vis">
                <?php echo !empty($assigned_dishes) ? esc_html__('Visible on Tablet', 'antalya-pos') : esc_html__('Hidden (No Dishes)', 'antalya-pos'); ?>
            </div>
        </div>
    </div>

    <!-- Dishes in this Category -->
    <div class="cat-dishes-head">
        <h3 class="cat-dishes-title">
            <span class="cat-title-ico"><?php echo $svg_utensils; ?></span>
            <span>
                <?php
                printf(
                    /* translators: %s: category name */
                    esc_html__('Menu Items in "%s"', 'antalya-pos'),
                    esc_html($view_cat->name)
                );
                ?>
            </span>
        </h3>
        <a href="<?php echo $add_item_url; ?>" class="apos-btn-secondary cat-btn-icon cat-add-dish-sm">
            <?php echo $svg_plus; ?>
            <span><?php esc_html_e('Add New Dish', 'antalya-pos'); ?></span>
        </a>
    </div>

    <?php if (empty($assigned_dishes)) : ?>
        <div class="cat-dishes-empty">
            <p class="cat-dishes-empty-txt"><?php esc_html_e('No food items have been assigned to this category yet.', 'antalya-pos'); ?></p>
            <a href="<?php echo $add_item_url; ?>" class="apos-btn cat-btn-icon">
                <?php echo $svg_plus; ?>
                <span><?php esc_html_e('Add First Dish to Category', 'antalya-pos'); ?></span>
            </a>
        </div>
    <?php else : ?>
        <table class="apos-table apos-full-table cat-view-dishes-table">
            <thead>
                <tr>
                    <th class="col-vd-photo"><?php esc_html_e('Photo', 'antalya-pos'); ?></th>
                    <th><?php esc_html_e('Dish Name', 'antalya-pos'); ?></th>
                    <th class="col-vd-addons"><?php esc_html_e('Add-ons', 'antalya-pos'); ?></th>
                    <th class="col-vd-price"><?php esc_html_e('Price', 'antalya-pos'); ?></th>
                    <th class="col-vd-action"><?php esc_html_e('Action', 'antalya-pos'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assigned_dishes as $dish) :
                    $dish_id       = intval($dish->id);
                    $ex_arr        = !empty($dish->extras) ? json_decode($dish->extras, true) : [];
                    if (!is_array($ex_arr)) {
                        $ex_arr = [];
                    }
                    $dish_img      = !empty($dish->image_url) ? $dish->image_url : 'https://via.placeholder.com/80?text=Dish';
                    $edit_item_url = esc_url(add_query_arg('edit_item', $dish_id, $base_items_url));
                ?>
                <tr>
                    <td>
                        <img src="<?php echo esc_url($dish_img); ?>" alt="<?php echo esc_attr($dish->name); ?>" class="cat-dish-thumb">
                    </td>
                    <td><strong class="cat-dish-name"><?php echo esc_html($dish->name); ?></strong></td>
                    <td>
                        <?php if (!empty($ex_arr)) : ?>
                            <span class="cat-dish-addons">
                                <?php
                                printf(
                                    /* translators: %d: count of extras */
                                    esc_html__('%d extra(s)', 'antalya-pos'),
                                    count($ex_arr)
                                );
                                ?>
                            </span>
                        <?php else : ?>
                            <span class="cat-dish-no-addons"><?php esc_html_e('None', 'antalya-pos'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><strong class="cat-dish-price"><?php echo $cur . esc_html(number_format((float) $dish->price, 2)); ?></strong></td>
                    <td>
                        <a href="<?php echo $edit_item_url; ?>" class="cat-act-btn act-edit">
                            <?php echo $svg_edit; ?>
                            <span><?php esc_html_e('Edit Dish', 'antalya-pos'); ?></span>
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
    .cat-view-hero-ico { width: 54px; height: 54px; border-radius: 14px; background: #fff7ed; color: #f97316; border: 1px solid #fed7aa; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .cat-view-hero-img { width: 54px; height: 54px; border-radius: 14px; object-fit: cover; border: 1.5px solid #e2e8f0; display: block; flex-shrink: 0; }
    .cat-view-id { font-size: 12px; font-weight: 700; color: #64748b; }
    .cat-view-title { margin: 2px 0 0; font-size: 24px; font-weight: 800; color: #0f172a; }
    .cat-view-btns { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .cat-del-lg { padding: 10px 16px; font-size: 13px; border-radius: 10px; }

    .cat-view-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 26px; width: 100%; }
    .cat-stat-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px 20px; }
    .cat-stat-orange { border-left: 4px solid #f97316; }
    .cat-stat-green { border-left: 4px solid #10b981; }
    .cat-stat-blue { border-left: 4px solid #3b82f6; }
    .cat-stat-lbl { font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.4px; }
    .cat-stat-val { font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 6px; }
    .cat-val-green { color: #059669; font-family: 'Courier New', Courier, monospace; }
    .cat-val-vis { font-size: 16px; color: #1d4ed8; }

    .cat-dishes-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px; }
    .cat-dishes-title { margin: 0; font-size: 16.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; }
    .cat-title-ico { color: #f97316; display: inline-flex; }
    .cat-add-dish-sm { padding: 7px 14px; font-size: 12.5px; border-radius: 9px; }
    .cat-dishes-empty { background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 14px; padding: 36px 20px; text-align: center; color: #64748b; }
    .cat-dishes-empty-txt { margin: 0 0 12px; font-size: 14px; }

    .cat-view-dishes-table { box-shadow: none; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
    .col-vd-photo { width: 75px; }
    .col-vd-addons { width: 160px; }
    .col-vd-price { width: 150px; }
    .col-vd-action { width: 140px; }

    .cat-dish-thumb { width: 46px; height: 46px; object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0; display: block; }
    .cat-dish-name { font-size: 14.5px; color: #0f172a; }
    .cat-dish-addons { color: #f97316; font-weight: 700; font-size: 12.5px; }
    .cat-dish-no-addons { color: #94a3b8; font-size: 12.5px; }
    .cat-dish-price { font-size: 14.5px; color: #0f172a; font-family: 'Courier New', Courier, monospace; }
</style>

<script>
jQuery(document).ready(function($) {
    $(document).on('click', '.js-confirm-del', function(e) {
        let catName = $(this).data('name') || <?php echo wp_json_encode(__('this category', 'antalya-pos')); ?>;
        let msg     = <?php echo wp_json_encode(__('Delete category "%s"?', 'antalya-pos')); ?>.replace('%s', catName);
        if (!confirm(msg)) {
            e.preventDefault();
        }
    });
});
</script>