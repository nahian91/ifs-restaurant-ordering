<?php
if (!defined('ABSPATH')) exit;

$svg_sparkles_lg = '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/></svg>';
$svg_plus        = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
$svg_edit        = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_eye         = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
$svg_trash       = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';
?>

<?php if (empty($extras)) : ?>
    <div class="apos-card apos-full-card ex-empty-card">
        <div class="ex-empty-ico">
            <?php echo $svg_sparkles_lg; ?>
        </div>
        <h3 class="ex-empty-title">No Extras Created Yet</h3>
        <p class="ex-empty-desc">Create add-ons like Extra Cheese, Spicy Sauce, or Fries to boost order value.</p>
        <a href="?page=antalya-pos&tab=extras&subtab=add" class="apos-btn ex-btn-icon">
            <?php echo $svg_plus; ?>
            <span>Add First Extra</span>
        </a>
    </div>
<?php else : ?>
    <table class="apos-table apos-full-table">
        <thead>
            <tr>
                <th class="col-ex-id">ID</th>
                <th>Extra Name</th>
                <th class="col-ex-price">Price</th>
                <th class="col-ex-used">Used In Dishes</th>
                <th class="col-ex-act">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($extras as $ex) : 
                $used_dishes = isset($dishes_by_extra[$ex->id]) ? $dishes_by_extra[$ex->id] : [];
                $used_count  = count($used_dishes);
            ?>
            <tr class="ex-row" data-name="<?php echo esc_attr(strtolower($ex->name)); ?>">
                <td><strong>#<?php echo intval($ex->id); ?></strong></td>
                <td>
                    <a href="?page=antalya-pos&tab=extras&view_extra=<?php echo intval($ex->id); ?>" class="ex-name-link">
                        <?php echo esc_html($ex->name); ?>
                    </a>
                </td>
                <td><strong class="ex-price-val">+<?php echo $cur . number_format($ex->price, 2); ?></strong></td>
                <td>
                    <span class="ex-used-badge <?php echo $used_count > 0 ? 'ex-badge-active' : 'ex-badge-empty'; ?>">
                        <?php echo $used_count; ?> dish(es)
                    </span>
                </td>
                <td>
                    <div class="ex-act-group">
                        <a href="?page=antalya-pos&tab=extras&view_extra=<?php echo intval($ex->id); ?>" class="ex-act-btn act-view">
                            <?php echo $svg_eye; ?> View
                        </a>
                        <a href="?page=antalya-pos&tab=extras&edit_extra=<?php echo intval($ex->id); ?>" class="ex-act-btn act-edit">
                            <?php echo $svg_edit; ?> Edit
                        </a>
                        <a href="?page=antalya-pos&tab=extras&subtab=all&del_extra=<?php echo intval($ex->id); ?>" class="ex-act-btn act-del js-confirm-del" data-name="<?php echo esc_attr($ex->name); ?>">
                            <?php echo $svg_trash; ?> Delete
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
        let q = $(this).val().toLowerCase();
        $('.ex-row').each(function() {
            $(this).toggle($(this).data('name').indexOf(q) > -1);
        });
    });

    $(document).on('click', '.js-confirm-del', function(e) {
        if (!confirm('Delete extra ' + ($(this).data('name') || 'this extra') + '?')) {
            e.preventDefault();
        }
    });
});
</script>