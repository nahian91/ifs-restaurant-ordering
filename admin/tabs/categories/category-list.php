<?php
if (!defined('ABSPATH')) exit;

$svg_folder_lg = '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>';
$svg_plus      = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
$svg_edit      = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_eye       = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
$svg_trash     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';
?>

<?php if (empty($cats)) : ?>
    <div class="apos-card apos-full-card cat-empty-card">
        <div class="cat-empty-ico">
            <?php echo $svg_folder_lg; ?>
        </div>
        <h3 class="cat-empty-title">No Categories Created Yet</h3>
        <p class="cat-empty-desc">Create categories like Starters, Main Course, or Drinks to organize your dishes.</p>
        <a href="?page=antalya-pos&tab=categories&subtab=add" class="apos-btn cat-btn-icon">
            <?php echo $svg_plus; ?>
            <span>Add First Category</span>
        </a>
    </div>
<?php else : ?>
    <div class="apos-card apos-full-card" style="padding: 0; overflow: hidden; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);">
        <table class="apos-table apos-full-table" style="margin: 0; border: none;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0;">
                    <th class="col-cat-order" style="padding: 16px 14px; text-align: center;">Order</th>
                    <th style="padding: 16px 20px;">Category Name</th>
                    <th class="col-cat-dishes" style="padding: 16px 14px;">Assigned Dishes</th>
                    <th class="col-cat-actions" style="padding: 16px 20px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cats as $c) : 
                    $cat_dishes = isset($items_by_cat[$c->name]) ? $items_by_cat[$c->name] : [];
                    $dish_count = count($cat_dishes);
                    $cat_order  = isset($c->category_order) ? intval($c->category_order) : 0;
                ?>
                <tr class="cat-row" data-name="<?php echo esc_attr(strtolower($c->name)); ?>" style="transition: background 0.15s ease;">
                    <td style="padding: 14px 14px; vertical-align: middle; text-align: center;">
                        <span class="cat-order-badge"><?php echo $cat_order; ?></span>
                    </td>
                    <td style="padding: 14px 20px; vertical-align: middle;">
                        <a href="?page=antalya-pos&tab=categories&view_cat=<?php echo intval($c->id); ?>" class="cat-name-link">
                            <?php echo esc_html($c->name); ?>
                        </a>
                    </td>
                    <td style="padding: 14px 14px; vertical-align: middle;">
                        <span class="cat-dish-badge <?php echo $dish_count > 0 ? 'cat-badge-active' : 'cat-badge-empty'; ?>">
                            <?php echo $dish_count; ?> dish(es)
                        </span>
                    </td>
                    <td style="padding: 14px 20px; vertical-align: middle; text-align: right;">
                        <div class="cat-act-group" style="justify-content: flex-end;">
                            <a href="?page=antalya-pos&tab=categories&view_cat=<?php echo intval($c->id); ?>" class="cat-act-btn act-view" title="View Details">
                                <?php echo $svg_eye; ?>
                                <span>View</span>
                            </a>
                            <a href="?page=antalya-pos&tab=categories&edit_cat=<?php echo intval($c->id); ?>" class="cat-act-btn act-edit" title="Edit Category">
                                <?php echo $svg_edit; ?>
                                <span>Edit</span>
                            </a>
                            <a href="?page=antalya-pos&tab=categories&subtab=all&del_cat=<?php echo intval($c->id); ?>" class="cat-act-btn act-del js-confirm-del" data-name="<?php echo esc_attr($c->name); ?>" title="Delete Category">
                                <?php echo $svg_trash; ?>
                                <span>Delete</span>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<style>
    .col-cat-order { width: 80px; }
    .col-cat-dishes { width: 180px; }
    .col-cat-actions { width: 250px; }

    .cat-order-badge {
        background: #f8fafc; color: #475569; border: 1px solid #cbd5e1;
        width: 30px; height: 30px; border-radius: 50%; display: inline-flex;
        align-items: center; justify-content: center; font-size: 12px; font-weight: 800;
        font-family: 'Courier New', Courier, monospace;
    }

    .cat-name-link { font-size: 15px; font-weight: 800; color: #0f172a; text-decoration: none; transition: color 0.15s; }
    .cat-name-link:hover { color: #f97316; }

    .cat-dish-badge {
        padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block;
    }
    .cat-badge-active { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
    .cat-badge-empty { background: #f1f5f9; color: #64748b; }

    .cat-act-group { display: flex; gap: 6px; align-items: center; }
    .cat-act-btn {
        padding: 6px 11px; border-radius: 8px; font-size: 12px; font-weight: 700;
        text-decoration: none; cursor: pointer; border: none; transition: all 0.2s ease;
        display: inline-flex; align-items: center; gap: 5px;
    }
    .act-view { background: #eff6ff; color: #1d4ed8; }
    .act-view:hover { background: #dbeafe; }
    .act-edit { background: #f1f5f9; color: #334155; }
    .act-edit:hover { background: #e2e8f0; color: #0f172a; }
    .act-del { background: #fef2f2; color: #dc2626; }
    .act-del:hover { background: #fee2e2; }

    .cat-empty-card { text-align: center; padding: 60px 20px; background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; }
    .cat-empty-ico {
        width: 64px; height: 64px; border-radius: 18px; background: #fff7ed; color: #f97316;
        display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.1);
    }
    .cat-empty-title { margin: 0 0 8px; font-size: 20px; font-weight: 800; color: #0f172a; }
    .cat-empty-desc { color: #64748b; margin: 0 0 22px; font-size: 14px; }
    .cat-btn-icon { display: inline-flex; align-items: center; gap: 7px; padding: 11px 20px; font-weight: 700; border-radius: 12px; }

    .cat-row:hover { background-color: #fafaf9; }
</style>

<script>
jQuery(document).ready(function($) {
    $('#cat-live-search').on('keyup', function() {
        let q = $(this).val().toLowerCase();
        $('.cat-row').each(function() {
            $(this).toggle($(this).data('name').indexOf(q) > -1);
        });
    });

    $(document).on('click', '.js-confirm-del', function(e) {
        let catName = $(this).data('name') || 'this category';
        if (!confirm('Are you sure you want to delete "' + catName + '"?')) {
            e.preventDefault();
        }
    });
});
</script>