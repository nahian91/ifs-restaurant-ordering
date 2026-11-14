<?php
/**
 * Antalya POS Add / Edit Restaurant Table Form View
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

$base_tables_url = admin_url('admin.php?page=antalya-pos&tab=tables');
$all_tables_url  = esc_url(add_query_arg('subtab', 'all', $base_tables_url));

$is_editing = !empty($edit_tbl);
$table_id   = $is_editing ? intval($edit_tbl->id) : 0;
$table_name = $is_editing ? $edit_tbl->table_name : '';
$table_seats = $is_editing ? intval($edit_tbl->seats) : 4;
$table_waiter = $is_editing ? $edit_tbl->assigned_waiter : '';
$table_login  = $is_editing ? $edit_tbl->table_login : '';
$table_pass   = $is_editing ? $edit_tbl->table_password : '';
$staff_list   = isset($staff_list) && is_array($staff_list) ? $staff_list : [];
?>

<div class="tbl-forms-grid <?php echo $is_editing ? 'tbl-forms-grid-single' : ''; ?>">
    <div class="apos-card apos-full-card tbl-card-no-margin">
        <div class="apos-form-header">
            <h3>
                <?php if ($is_editing) : ?>
                    <span>
                        <?php
                        printf(
                            /* translators: %s: table name/number */
                            esc_html__('Edit Table #%s', 'antalya-pos'),
                            esc_html($table_name)
                        );
                        ?>
                    </span>
                <?php else : ?>
                    <span><?php esc_html_e('Add New Restaurant Table', 'antalya-pos'); ?></span>
                <?php endif; ?>
            </h3>
            <p><?php esc_html_e('Configure table details and assign a Waiter from staff list.', 'antalya-pos'); ?></p>
        </div>

        <form method="post" action="<?php echo $all_tables_url; ?>">
            <?php wp_nonce_field('rtpos_save_table_action'); ?>
            <input type="hidden" name="edit_table_id" value="<?php echo $table_id > 0 ? $table_id : ''; ?>">

            <div class="tbl-two-col">
                <div class="apos-form-group">
                    <label class="apos-label">
                        <span><?php esc_html_e('Table Name / Number', 'antalya-pos'); ?> <span class="required-star">*</span></span>
                    </label>
                    <input type="text" name="table_name" id="inp-tbl-name" class="apos-input" placeholder="01" value="<?php echo esc_attr($table_name); ?>" required>
                </div>

                <div class="apos-form-group">
                    <label class="apos-label">
                        <span><?php esc_html_e('Seating Capacity', 'antalya-pos'); ?></span>
                    </label>
                    <input type="number" name="seats" class="apos-input" value="<?php echo $table_seats; ?>" min="1" max="50" required>
                </div>
            </div>

            <!-- Assign Waiter Dropdown -->
            <div class="apos-form-group">
                <label class="apos-label">
                    <span><?php esc_html_e('Assign Waiter', 'antalya-pos'); ?></span>
                    <small><?php esc_html_e('(From Active Staff)', 'antalya-pos'); ?></small>
                </label>
                <select name="assigned_waiter" class="apos-input" aria-label="<?php esc_attr_e('Assign Waiter', 'antalya-pos'); ?>">
                    <option value=""><?php esc_html_e('-- None / Unassigned --', 'antalya-pos'); ?></option>
                    <?php foreach ($staff_list as $st) :
                        $st_name = $st->name ?? '';
                        $st_role = $st->role ?? '';
                    ?>
                        <option value="<?php echo esc_attr($st_name); ?>" <?php selected($table_waiter, $st_name); ?>>
                            <?php echo esc_html($st_name); ?> (<?php echo esc_html($st_role); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Login Credentials Panel -->
            <div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:14px; padding:16px 18px; margin-top:4px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <strong><?php esc_html_e('Tablet Login Credentials', 'antalya-pos'); ?></strong>
                    <button type="button" id="btn-gen-tbl-cred" class="apos-btn-secondary" style="padding:4px 10px; font-size:11.5px;"><?php esc_html_e('Auto-Generate', 'antalya-pos'); ?></button>
                </div>
                <div class="tbl-two-col">
                    <div>
                        <label class="apos-label" style="font-size:12px;"><?php esc_html_e('Username / Email', 'antalya-pos'); ?></label>
                        <input type="text" name="table_login" id="inp-tbl-login" class="apos-input" style="background:#fff;" value="<?php echo esc_attr($table_login); ?>" required>
                    </div>
                    <div>
                        <label class="apos-label" style="font-size:12px;"><?php esc_html_e('Password', 'antalya-pos'); ?></label>
                        <input type="text" name="table_password" id="inp-tbl-pass" class="apos-input" style="background:#fff; font-family:monospace;" value="<?php echo esc_attr($table_pass); ?>" required>
                    </div>
                </div>
            </div>

            <div style="margin-top:20px; display:flex; gap:10px;">
                <button type="submit" name="rtpos_save_table" class="apos-btn">
                    <?php echo $is_editing ? esc_html__('Update Table', 'antalya-pos') : esc_html__('Save Table', 'antalya-pos'); ?>
                </button>
                <a href="<?php echo $all_tables_url; ?>" class="apos-btn-secondary"><?php esc_html_e('Cancel', 'antalya-pos'); ?></a>
            </div>
        </form>
    </div>

    <?php if (!$is_editing) : ?>
    <div class="apos-card apos-full-card tbl-bulk-card">
        <div class="apos-form-header">
            <h3><span><?php esc_html_e('Bulk Quick-Generate Tables', 'antalya-pos'); ?></span></h3>
            <p><?php esc_html_e('Create multiple tables instantly.', 'antalya-pos'); ?></p>
        </div>
        <form method="post" action="<?php echo $all_tables_url; ?>">
            <?php wp_nonce_field('rtpos_bulk_tables_action'); ?>
            <div class="tbl-three-col">
                <div class="apos-form-group">
                    <label class="apos-label"><?php esc_html_e('Prefix', 'antalya-pos'); ?></label>
                    <input type="text" name="bulk_prefix" class="apos-input" placeholder="e.g. T-">
                </div>
                <div class="apos-form-group">
                    <label class="apos-label"><?php esc_html_e('Start #', 'antalya-pos'); ?></label>
                    <input type="number" name="bulk_start" class="apos-input" value="1" min="1" required>
                </div>
                <div class="apos-form-group">
                    <label class="apos-label"><?php esc_html_e('End #', 'antalya-pos'); ?></label>
                    <input type="number" name="bulk_end" class="apos-input" value="10" min="1" required>
                </div>
            </div>
            <div class="tbl-two-col">
                <div class="apos-form-group">
                    <label class="apos-label"><?php esc_html_e('Seats', 'antalya-pos'); ?></label>
                    <input type="number" name="bulk_seats" class="apos-input" value="4" min="1" required>
                </div>
                <div class="apos-form-group">
                    <label class="apos-label"><?php esc_html_e('Assign Waiter', 'antalya-pos'); ?></label>
                    <select name="bulk_waiter" class="apos-input" aria-label="<?php esc_attr_e('Bulk Assign Waiter', 'antalya-pos'); ?>">
                        <option value=""><?php esc_html_e('-- None --', 'antalya-pos'); ?></option>
                        <?php foreach ($staff_list as $st) :
                            $st_name = $st->name ?? '';
                        ?>
                            <option value="<?php echo esc_attr($st_name); ?>"><?php echo esc_html($st_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="margin-top:14px;">
                <button type="submit" name="rtpos_bulk_tables" class="apos-btn-dark"><?php esc_html_e('Generate Now', 'antalya-pos'); ?></button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<style>
    .required-star { color: #ef4444; font-weight: 800; margin-left: 3px; }
</style>

<script>
jQuery(document).ready(function($) {
    $('#btn-gen-tbl-cred').on('click', function() {
        let tName = ($('#inp-tbl-name').val() || '01').toLowerCase().replace(/[^a-z0-9]/g, '');
        let randNum = Math.floor(1000 + Math.random() * 9000);
        $('#inp-tbl-login').val('table_' + tName);
        $('#inp-tbl-pass').val('pos' + randNum);
    });
});
</script>