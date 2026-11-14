<?php
/**
 * Antalya POS Extras & Add-ons Table List View
 *
 * @package Antalya_POS
 * @since 6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    return;
}

$svg_sparkles_lg = '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/></svg>';
$svg_sparkles_sm = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/></svg>';
$svg_plus        = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
$svg_edit        = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_eye         = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
$svg_trash       = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';

$base_extras_url = admin_url('admin.php?page=antalya-pos&tab=extras');
$add_extra_url   = esc_url(add_query_arg('subtab', 'add', $base_extras_url));
?>

<?php if (empty($extras)) : ?>
    <div class="apos-card apos-full-card ex-empty-card">
        <div class="ex-empty-ico">
            <?php echo $svg_sparkles_lg; ?>
        </div>
        <h3 class="ex-empty-title"><?php esc_html_e('No Extras Created Yet', 'antalya-pos'); ?></h3>
        <p class="ex-empty-desc"><?php esc_html_e('Create add-ons like Extra Cheese, Spicy Sauce, or Fries to boost order value.', 'antalya-pos'); ?></p>
        <a href="<?php echo $add_extra_url; ?>" class="apos-btn ex-btn-icon">
            <?php echo $svg_plus; ?>
            <span><?php esc_html_e('Add First Extra', 'antalya-pos'); ?></span>
        </a>
    </div>
<?php else : ?>
    <table class="apos-table apos-full-table">
        <thead>
            <tr>
                <th class="col-ex-id"><?php esc_html_e('ID', 'antalya-pos'); ?></th>
                <th><?php esc_html_e('Extra Name', 'antalya-pos'); ?></th>
                <th class="col-ex-price"><?php esc_html_e('Price', 'antalya-pos'); ?></th>
                <th class="col-ex-used"><?php esc_html_e('Used In Dishes', 'antalya-pos'); ?></th>
                <th class="col-ex-act"><?php esc_html_e('Actions', 'antalya-pos'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($extras as $ex) :
                $ex_id       = intval($ex->id);
                $used_dishes = isset($dishes_by_extra[$ex_id]) ? $dishes_by_extra[$ex_id] : [];
                $used_count  = count($used_dishes);

                $view_url    = esc_url(add_query_arg('view_extra', $ex_id, $base_extras_url));
                $edit_url    = esc_url(add_query_arg('edit_extra', $ex_id, $base_extras_url));
                $del_url     = wp_nonce_url(
                    add_query_arg(['subtab' => 'all', 'del_extra' => $ex_id], $base_extras_url),
                    'rtpos_delete_extra_' . $ex_id
                );
            ?>
            <tr class="ex-row" data-name="<?php echo esc_attr(strtolower($ex->name)); ?>">
                <td><strong>#<?php echo $ex_id; ?></strong></td>
                <td>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <?php if (!empty($ex->image)) : ?>
                            <img src="<?php echo esc_url($ex->image); ?>" alt="<?php echo esc_attr($ex->name); ?>" style="width:34px; height:34px; border-radius:8px; object-fit:cover; border:1px solid #e2e8f0;">
                        <?php else : ?>
                            <span style="width:34px; height:34px; border-radius:8px; background:#fff7ed; color:#f97316; display:inline-flex; align-items:center; justify-content:center; border:1px solid #ffedd5;">
                                <?php echo $svg_sparkles_sm; ?>
                            </span>
                        <?php endif; ?>
                        <a href="<?php echo $view_url; ?>" class="ex-name-link">
                            <?php echo esc_html($ex->name); ?>
                        </a>
                    </div>
                </td>
                <td><strong class="ex-price-val">+<?php echo $cur . esc_html(number_format((float) $ex->price, 2)); ?></strong></td>
                <td>
                    <span class="ex-used-badge <?php echo ($used_count > 0) ? 'ex-badge-active' : 'ex-badge-empty'; ?>">
                        <?php
                        printf(
                            /* translators: %d: count of dishes using this extra */
                            esc_html__('%d dish(es)', 'antalya-pos'),
                            intval($used_count)
                        );
                        ?>
                    </span>
                </td>
                <td>
                    <div class="ex-act-group">
                        <a href="<?php echo $view_url; ?>" class="ex-act-btn act-view" title="<?php esc_attr_e('View Details', 'antalya-pos'); ?>">
                            <?php echo $svg_eye; ?>
                            <span><?php esc_html_e('View', 'antalya-pos'); ?></span>
                        </a>
                        <a href="<?php echo $edit_url; ?>" class="ex-act-btn act-edit" title="<?php esc_attr_e('Edit Extra', 'antalya-pos'); ?>">
                            <?php echo $svg_edit; ?>
                            <span><?php esc_html_e('Edit', 'antalya-pos'); ?></span>
                        </a>
                        <a href="<?php echo esc_url($del_url); ?>" class="ex-act-btn act-del js-confirm-del" data-name="<?php echo esc_attr($ex->name); ?>" title="<?php esc_attr_e('Delete Extra', 'antalya-pos'); ?>">
                            <?php echo $svg_trash; ?>
                            <span><?php esc_html_e('Delete', 'antalya-pos'); ?></span>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
jQuery(document).ready(function($) {
    $('#ex-live-search').on('keyup', function() {
        let q = ($(this).val() || '').toLowerCase();
        $('.ex-row').each(function() {
            let nameAttr = String($(this).data('name') || '');
            $(this).toggle(nameAttr.indexOf(q) > -1);
        });
    });

    $(document).on('click', '.js-confirm-del', function(e) {
        let exName = $(this).data('name') || <?php echo wp_json_encode(__('this extra', 'antalya-pos')); ?>;
        let msg    = <?php echo wp_json_encode(__('Delete extra "%s"?', 'antalya-pos')); ?>.replace('%s', exName);
        if (!confirm(msg)) {
            e.preventDefault();
        }
    });
});
</script>