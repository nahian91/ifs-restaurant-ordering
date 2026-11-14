<?php
/**
 * Antalya POS Single Extra / Add-on Details View
 *
 * @package Antalya_POS
 * @since 6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options') || empty($view_extra)) {
    return;
}

// Safe fallback for currency if not passed
if (!isset($cur)) {
    $s   = rtpos_get_settings();
    $cur = esc_html($s['currency'] ?? '৳');
}

$svg_sparkles = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/></svg>';
$svg_edit     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_trash    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';
$svg_utensils = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>';

$ex_id           = intval($view_extra->id);
$assigned_dishes = isset($dishes_by_extra[$ex_id]) && is_array($dishes_by_extra[$ex_id]) ? $dishes_by_extra[$ex_id] : [];

$base_extras_url = admin_url('admin.php?page=antalya-pos&tab=extras');
$base_items_url  = admin_url('admin.php?page=antalya-pos&tab=items');

$edit_url      = esc_url(add_query_arg('edit_extra', $ex_id, $base_extras_url));
$del_url       = wp_nonce_url(
    add_query_arg(['subtab' => 'all', 'del_extra' => $ex_id], $base_extras_url),
    'rtpos_delete_extra_' . $ex_id
);
$all_items_url = esc_url(add_query_arg('subtab', 'all', $base_items_url));
?>

<div class="apos-card apos-full-card">
    <div class="ex-view-hero">
        <div class="ex-view-hero-left">
            <?php if (!empty($view_extra->image)) : ?>
                <img src="<?php echo esc_url($view_extra->image); ?>" alt="<?php echo esc_attr($view_extra->name); ?>" class="ex-view-hero-img">
            <?php else : ?>
                <div class="ex-view-hero-ico"><?php echo $svg_sparkles; ?></div>
            <?php endif; ?>
            <div>
                <div class="ex-view-id">
                    <?php
                    printf(
                        /* translators: %d: add-on ID */
                        esc_html__('ADD-ON ID #%d', 'antalya-pos'),
                        $ex_id
                    );
                    ?>
                </div>
                <h2 class="ex-view-title"><?php echo esc_html($view_extra->name); ?></h2>
            </div>
        </div>

        <div class="ex-view-btns">
            <a href="<?php echo $edit_url; ?>" class="apos-btn ex-btn-icon">
                <?php echo $svg_edit; ?>
                <span><?php esc_html_e('Edit Extra', 'antalya-pos'); ?></span>
            </a>
            <a href="<?php echo esc_url($del_url); ?>" class="ex-act-btn act-del ex-del-lg js-confirm-del" data-name="<?php echo esc_attr($view_extra->name); ?>">
                <?php echo $svg_trash; ?>
                <span><?php esc_html_e('Delete', 'antalya-pos'); ?></span>
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="ex-view-stats">
        <div class="ex-stat-box ex-stat-orange">
            <div class="ex-stat-lbl"><?php esc_html_e('Add-on Price', 'antalya-pos'); ?></div>
            <div class="ex-stat-val ex-val-orange">+<?php echo $cur . esc_html(number_format((float) $view_extra->price, 2)); ?></div>
        </div>
        <div class="ex-stat-box ex-stat-green">
            <div class="ex-stat-lbl"><?php esc_html_e('Attached Menu Dishes', 'antalya-pos'); ?></div>
            <div class="ex-stat-val">
                <?php
                printf(
                    /* translators: %d: count of attached dishes */
                    esc_html__('%d Dish(es)', 'antalya-pos'),
                    count($assigned_dishes)
                );
                ?>
            </div>
        </div>
        <div class="ex-stat-box ex-stat-blue">
            <div class="ex-stat-lbl"><?php esc_html_e('Availability Status', 'antalya-pos'); ?></div>
            <div class="ex-stat-val ex-val-live"><?php esc_html_e('Active on Customer Tablet', 'antalya-pos'); ?></div>
        </div>
    </div>

    <!-- Assigned Dishes Table -->
    <h3 class="ex-dishes-heading">
        <span class="ex-title-ico"><?php echo $svg_utensils; ?></span>
        <span>
            <?php
            printf(
                /* translators: %s: extra add-on name */
                esc_html__('Dishes Offering "%s"', 'antalya-pos'),
                esc_html($view_extra->name)
            );
            ?>
        </span>
    </h3>

    <?php if (empty($assigned_dishes)) : ?>
        <div class="ex-dishes-empty">
            <p class="ex-dishes-empty-txt"><?php esc_html_e('This add-on is not attached to any specific dish yet.', 'antalya-pos'); ?></p>
            <a href="<?php echo $all_items_url; ?>" class="apos-btn-secondary"><?php esc_html_e('Go to Food Items to Attach', 'antalya-pos'); ?></a>
        </div>
    <?php else : ?>
        <table class="apos-table apos-full-table ex-view-dishes-table">
            <thead>
                <tr>
                    <th class="col-ed-photo"><?php esc_html_e('Photo', 'antalya-pos'); ?></th>
                    <th><?php esc_html_e('Dish Name', 'antalya-pos'); ?></th>
                    <th><?php esc_html_e('Category', 'antalya-pos'); ?></th>
                    <th><?php esc_html_e('Base Price', 'antalya-pos'); ?></th>
                    <th><?php esc_html_e('Price With This Extra', 'antalya-pos'); ?></th>
                    <th class="col-ed-action"><?php esc_html_e('Action', 'antalya-pos'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assigned_dishes as $dish) :
                    $dish_id       = intval($dish->id);
                    $combined      = floatval($dish->price) + floatval($view_extra->price);
                    $dish_img      = !empty($dish->image_url) ? $dish->image_url : 'https://via.placeholder.com/80?text=Dish';
                    $edit_dish_url = esc_url(add_query_arg('edit_item', $dish_id, $base_items_url));
                ?>
                <tr>
                    <td>
                        <img src="<?php echo esc_url($dish_img); ?>" alt="<?php echo esc_attr($dish->name); ?>" class="ex-dish-thumb">
                    </td>
                    <td><strong><?php echo esc_html($dish->name); ?></strong></td>
                    <td><span class="ex-dish-cat"><?php echo esc_html($dish->category); ?></span></td>
                    <td><?php echo $cur . esc_html(number_format((float) $dish->price, 2)); ?></td>
                    <td><strong class="ex-val-green"><?php echo $cur . esc_html(number_format($combined, 2)); ?></strong></td>
                    <td>
                        <a href="<?php echo $edit_dish_url; ?>" class="ex-act-btn act-edit">
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
    .ex-view-hero { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 18px; padding-bottom: 20px; border-bottom: 1.5px solid #f1f5f9; margin-bottom: 22px; }
    .ex-view-hero-left { display: flex; align-items: center; gap: 14px; }
    .ex-view-hero-ico { width: 54px; height: 54px; border-radius: 14px; background: #fff7ed; color: #f97316; border: 1px solid #fed7aa; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .ex-view-hero-img { width: 54px; height: 54px; border-radius: 14px; object-fit: cover; border: 1.5px solid #e2e8f0; display: block; flex-shrink: 0; }
    .ex-view-id { font-size: 12px; font-weight: 700; color: #64748b; }
    .ex-view-title { margin: 2px 0 0; font-size: 24px; font-weight: 800; color: #0f172a; }
    .ex-view-btns { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .ex-del-lg { padding: 10px 16px; font-size: 13px; border-radius: 10px; }

    .ex-view-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 26px; width: 100%; }
    .ex-stat-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px 20px; }
    .ex-stat-orange { border-left: 4px solid #f97316; }
    .ex-stat-green { border-left: 4px solid #10b981; }
    .ex-stat-blue { border-left: 4px solid #3b82f6; }
    .ex-stat-lbl { font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.4px; }
    .ex-stat-val { font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 6px; }
    .ex-val-orange { color: #f97316; font-family: 'Courier New', Courier, monospace; }
    .ex-val-live { color: #059669; font-size: 18px; }

    .ex-dishes-heading { margin: 0 0 14px; font-size: 16.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; }
    .ex-title-ico { color: #f97316; display: inline-flex; }
    .ex-dishes-empty { background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 14px; padding: 34px 20px; text-align: center; color: #64748b; }
    .ex-dishes-empty-txt { margin: 0 0 12px; font-size: 14px; }

    .ex-view-dishes-table { box-shadow: none; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
    .col-ed-photo { width: 70px; }
    .col-ed-action { width: 130px; }
    .ex-dish-thumb { width: 44px; height: 44px; object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0; display: block; }
    .ex-dish-cat { background: #f1f5f9; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block; }
    .ex-val-green { color: #059669; font-family: 'Courier New', Courier, monospace; }
</style>

<script>
jQuery(document).ready(function($) {
    $(document).on('click', '.js-confirm-del', function(e) {
        let exName = $(this).data('name') || <?php echo wp_json_encode(__('this extra', 'antalya-pos')); ?>;
        let msg    = <?php echo wp_json_encode(__('Delete extra "%s"?', 'antalya-pos')); ?>.replace('%s', exName);
        if (!confirm(msg)) {
            e.preventDefault();
        }
    });
});
</script>