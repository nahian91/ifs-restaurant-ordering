<?php
if (!defined('ABSPATH')) exit;
?>
<div class="tbl-forms-grid <?php echo $edit_tbl ? 'tbl-forms-grid-single' : ''; ?>">
    <div class="apos-card apos-full-card tbl-card-no-margin">
        <div class="apos-form-header">
            <h3><?php echo $edit_tbl ? '<span>Edit Table #' . esc_html($edit_tbl->table_name) . '</span>' : '<span>Add New Restaurant Table</span>'; ?></h3>
            <p>Configure table details and assign a Waiter from staff list.</p>
        </div>

        <form method="post" action="?page=antalya-pos&tab=tables&subtab=all">
            <input type="hidden" name="edit_table_id" value="<?php echo $edit_tbl ? intval($edit_tbl->id) : ''; ?>">

            <div class="tbl-two-col">
                <div class="apos-form-group">
                    <label class="apos-label">Table Name / Number <span>*</span></label>
                    <input type="text" name="table_name" id="inp-tbl-name" class="apos-input" placeholder="01" value="<?php echo $edit_tbl ? esc_attr($edit_tbl->table_name) : ''; ?>" required>
                </div>

                <div class="apos-form-group">
                    <label class="apos-label">Seating Capacity</label>
                    <input type="number" name="seats" class="apos-input" value="<?php echo $edit_tbl ? intval($edit_tbl->seats) : 4; ?>" min="1" max="50" required>
                </div>
            </div>

            <!-- Assign Waiter Dropdown -->
            <div class="apos-form-group">
                <label class="apos-label">Assign Waiter <small>(From Active Staff)</small></label>
                <select name="assigned_waiter" class="apos-input">
                    <option value="">-- None / Unassigned --</option>
                    <?php foreach ($staff_list as $st) : ?>
                        <option value="<?php echo esc_attr($st->name); ?>" <?php selected($edit_tbl ? $edit_tbl->assigned_waiter : '', $st->name); ?>>
                            <?php echo esc_html($st->name); ?> (<?php echo esc_html($st->role); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Login Credentials Panel -->
            <div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:14px; padding:16px 18px; margin-top:4px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <strong>Tablet Login Credentials</strong>
                    <button type="button" id="btn-gen-tbl-cred" class="apos-btn-secondary" style="padding:4px 10px; font-size:11.5px;">Auto-Generate</button>
                </div>
                <div class="tbl-two-col">
                    <div>
                        <label class="apos-label" style="font-size:12px;">Username / Email</label>
                        <input type="text" name="table_login" id="inp-tbl-login" class="apos-input" style="background:#fff;" value="<?php echo $edit_tbl ? esc_attr($edit_tbl->table_login) : ''; ?>" required>
                    </div>
                    <div>
                        <label class="apos-label" style="font-size:12px;">Password</label>
                        <input type="text" name="table_password" id="inp-tbl-pass" class="apos-input" style="background:#fff; font-family:monospace;" value="<?php echo $edit_tbl ? esc_attr($edit_tbl->table_password) : ''; ?>" required>
                    </div>
                </div>
            </div>

            <div style="margin-top:20px; display:flex; gap:10px;">
                <button type="submit" name="rtpos_save_table" class="apos-btn"><?php echo $edit_tbl ? 'Update Table' : 'Save Table'; ?></button>
                <a href="?page=antalya-pos&tab=tables&subtab=all" class="apos-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <?php if (!$edit_tbl) : ?>
    <div class="apos-card apos-full-card tbl-bulk-card">
        <div class="apos-form-header">
            <h3><span>Bulk Quick-Generate Tables</span></h3>
            <p>Create multiple tables instantly.</p>
        </div>
        <form method="post" action="?page=antalya-pos&tab=tables&subtab=all">
            <div class="tbl-three-col">
                <div class="apos-form-group"><label class="apos-label">Prefix</label><input type="text" name="bulk_prefix" class="apos-input" placeholder="e.g. T-"></div>
                <div class="apos-form-group"><label class="apos-label">Start #</label><input type="number" name="bulk_start" class="apos-input" value="1" min="1" required></div>
                <div class="apos-form-group"><label class="apos-label">End #</label><input type="number" name="bulk_end" class="apos-input" value="10" min="1" required></div>
            </div>
            <div class="tbl-two-col">
                <div class="apos-form-group"><label class="apos-label">Seats</label><input type="number" name="bulk_seats" class="apos-input" value="4" min="1" required></div>
                <div class="apos-form-group">
                    <label class="apos-label">Assign Waiter</label>
                    <select name="bulk_waiter" class="apos-input">
                        <option value="">-- None --</option>
                        <?php foreach ($staff_list as $st) : ?>
                            <option value="<?php echo esc_attr($st->name); ?>"><?php echo esc_html($st->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="margin-top:14px;"><button type="submit" name="rtpos_bulk_tables" class="apos-btn-dark">Generate Now</button></div>
        </form>
    </div>
    <?php endif; ?>
</div>

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