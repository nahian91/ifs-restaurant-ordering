<?php
if (!defined('ABSPATH')) exit;

$svg_folder = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>';
$svg_edit   = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_check  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>';
$svg_order  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>';
?>

<div class="apos-card apos-full-card">
    <div class="apos-form-header">
        <h3><?php echo $edit_cat ? $svg_edit . '<span>Edit Category: ' . esc_html($edit_cat->name) . '</span>' : $svg_folder . '<span>Add New Menu Category</span>'; ?></h3>
        <p>Categories appear as quick-filter tabs on the top of the customer tablet menu.</p>
    </div>

    <form method="post" action="?page=antalya-pos&tab=categories&subtab=all">
        <input type="hidden" name="edit_cat_id" value="<?php echo $edit_cat ? intval($edit_cat->id) : ''; ?>">

        <div class="apos-form-group">
            <label class="apos-label">Category Name <span>*</span> <small>(e.g. Turkish Kebab, Pizza, Drinks)</small></label>
            <div class="apos-input-wrap">
                <span class="apos-input-prefix"><?php echo $svg_folder; ?></span>
                <input type="text" name="cat_name" class="apos-input has-prefix" placeholder="Enter category name..." value="<?php echo $edit_cat ? esc_attr($edit_cat->name) : ''; ?>" required>
            </div>
        </div>

        <div class="apos-form-group">
            <label class="apos-label">Display Order / Number <small>(Lower numbers appear first, e.g. 1, 2, 3)</small></label>
            <div class="apos-input-wrap">
                <span class="apos-input-prefix"><?php echo $svg_order; ?></span>
                <input type="number" name="cat_order" class="apos-input has-prefix" placeholder="0" value="<?php echo $edit_cat && isset($edit_cat->category_order) ? intval($edit_cat->category_order) : '0'; ?>">
            </div>
        </div>

        <div class="cat-form-footer">
            <button type="submit" name="rtpos_save_cat" class="apos-btn cat-btn-icon">
                <?php echo $svg_check; ?>
                <span><?php echo $edit_cat ? 'Update Category' : 'Save Category'; ?></span>
            </button>
            <a href="?page=antalya-pos&tab=categories&subtab=all" class="apos-btn-secondary">Cancel</a>
        </div>
    </form>
</div>