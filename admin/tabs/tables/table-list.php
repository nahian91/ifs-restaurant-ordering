<?php
if (!defined('ABSPATH')) exit;
?>
<?php if (empty($tables)) : ?>
    <div class="apos-card apos-full-card" style="text-align:center; padding:60px 20px;">
        <div style="width:56px; height:56px; border-radius:16px; background:#fff7ed; color:#f97316; display:inline-flex; align-items:center; justify-content:center; margin-bottom:14px;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
        </div>
        <h3 style="margin:0 0 8px; font-size:19px;">No Tables Configured Yet</h3>
        <p style="color:#64748b; margin-bottom:20px; font-size:14px;">Add your first restaurant table with login credentials and QR codes.</p>
        <a href="?page=antalya-pos&tab=tables&subtab=add" class="apos-btn">Add First Table</a>
    </div>
<?php else : ?>
    <table class="apos-table apos-full-table">
        <thead>
            <tr>
                <th>Table Name</th>
                <th width="130">Capacity</th>
                <th width="170">Assigned Waiter</th>
                <th width="210">Tablet Login / Pass</th>
                <th width="150">Status</th>
                <th width="260">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tables as $tbl) : 
                $active_info = isset($active_rows[$tbl->table_name]) ? $active_rows[$tbl->table_name] : null;
                $is_busy     = !empty($active_info);
                $tablet_link = add_query_arg('table', $tbl->table_name, $base_url);
                $search_blob = strtolower('table ' . $tbl->table_name . ' ' . ($tbl->table_login ?? '') . ' ' . ($tbl->assigned_waiter ?? ''));
            ?>
            <tr class="tbl-row" data-search="<?php echo esc_attr($search_blob); ?>" data-status="<?php echo $is_busy ? 'busy' : 'free'; ?>">
                <td>
                    <a href="?page=antalya-pos&tab=tables&view_table=<?php echo intval($tbl->id); ?>" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                        <strong style="font-size:15px; color:#0f172a;">Table <?php echo esc_html($tbl->table_name); ?></strong>
                    </a>
                </td>
                <td>
                    <span style="font-weight:700; color:#475569;"><?php echo intval($tbl->seats); ?> Persons</span>
                </td>
                <td>
                    <span style="background:#fff7ed; color:#ea580c; border:1px solid #fed7aa; padding:3px 10px; border-radius:8px; font-size:12px; font-weight:700; display:inline-block;">
                        <?php echo esc_html($tbl->assigned_waiter ?: 'Unassigned'); ?>
                    </span>
                </td>
                <td>
                    <div style="font-size:12px; color:#334155;">
                        <div>Login: <strong><?php echo esc_html($tbl->table_login ?: '—'); ?></strong></div>
                        <div>Pass: <code style="background:#f1f5f9; padding:1px 5px; border-radius:4px; color:#ea580c; font-weight:700;"><?php echo esc_html($tbl->table_password ?: '—'); ?></code></div>
                    </div>
                </td>
                <td>
                    <span style="background:<?php echo $is_busy ? '#fef3c7' : '#d1fae5'; ?>; color:<?php echo $is_busy ? '#d97706' : '#059669'; ?>; padding:4px 11px; border-radius:20px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:5px;">
                        <span style="width:7px; height:7px; border-radius:50%; background:<?php echo $is_busy ? '#f59e0b' : '#10b981'; ?>; display:inline-block;"></span>
                        <span><?php echo $is_busy ? 'Occupied' : 'Available'; ?></span>
                    </span>
                    <?php if ($is_busy) : ?>
                        <br><a href="?page=antalya-pos&tab=orders" style="font-size:11px; color:#ea580c; font-weight:700; text-decoration:none;"><?php echo intval($active_info->ord_cnt); ?> Order (<?php echo $cur . number_format($active_info->running_total, 2); ?>)</a>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                        <a href="?page=antalya-pos&tab=tables&view_table=<?php echo intval($tbl->id); ?>" class="tbl-act-btn act-view" style="padding:6px 10px; border-radius:7px; font-size:12px; font-weight:700; text-decoration:none; background:#eff6ff; color:#1d4ed8;">View</a>
                        <a href="?page=antalya-pos&tab=tables&edit_table=<?php echo intval($tbl->id); ?>" class="tbl-act-btn act-edit" style="padding:6px 10px; border-radius:7px; font-size:12px; font-weight:700; text-decoration:none; background:#f1f5f9; color:#334155;">Edit</a>
                        <button type="button" class="apos-btn-secondary apos-copy-btn" data-link="<?php echo esc_attr($tablet_link); ?>" style="padding:6px 10px; font-size:12px;">Copy URL</button>
                        <a href="?page=antalya-pos&tab=tables&subtab=all&del_table=<?php echo intval($tbl->id); ?>" class="tbl-act-btn act-del js-confirm-del" data-tname="<?php echo esc_attr($tbl->table_name); ?>" style="padding:6px 10px; border-radius:7px; font-size:12px; font-weight:700; text-decoration:none; background:#fef2f2; color:#dc2626;">Del</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
jQuery(document).ready(function($) {
    let currentStatusFilter = 'all';

    function applyTableFilters() {
        let q = ($('#tbl-live-search').val() || '').toLowerCase();
        $('.tbl-row').each(function() {
            let matchText   = $(this).data('search').indexOf(q) > -1;
            let matchStatus = (currentStatusFilter === 'all') || ($(this).data('status') === currentStatusFilter);
            $(this).toggle(matchText && matchStatus);
        });
    }

    $('#tbl-live-search').on('keyup', applyTableFilters);

    $('.tbl-st-pill').on('click', function() {
        $('.tbl-st-pill').removeClass('active');
        $(this).addClass('active');
        currentStatusFilter = $(this).data('st');
        applyTableFilters();
    });

    $(document).on('click', '.js-confirm-del', function(e) {
        if (!confirm('Delete Table ' + ($(this).data('tname') || '') + '?')) {
            e.preventDefault();
        }
    });

    $('.apos-copy-btn').on('click', function() {
        navigator.clipboard.writeText($(this).data('link'));
        let btn = $(this);
        let orig = btn.text();
        btn.text('Copied');
        setTimeout(() => btn.text(orig), 1500);
    });
});
</script>