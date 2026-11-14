<?php
/**
 * Antalya POS Restaurant Tables Table List View
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

// Safe currency fallback
if (!isset($cur)) {
    $s   = rtpos_get_settings();
    $cur = esc_html($s['currency'] ?? '৳');
}

$base_tables_url = admin_url('admin.php?page=antalya-pos&tab=tables');
$add_table_url   = esc_url(add_query_arg('subtab', 'add', $base_tables_url));
$orders_url      = esc_url(admin_url('admin.php?page=antalya-pos&tab=orders'));
$base_url        = isset($base_url) ? $base_url : home_url('/');
?>

<?php if (empty($tables)) : ?>
    <div class="apos-card apos-full-card" style="text-align:center; padding:60px 20px;">
        <div style="width:56px; height:56px; border-radius:16px; background:#fff7ed; color:#f97316; display:inline-flex; align-items:center; justify-content:center; margin-bottom:14px;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
        </div>
        <h3 style="margin:0 0 8px; font-size:19px;"><?php esc_html_e('No Tables Configured Yet', 'antalya-pos'); ?></h3>
        <p style="color:#64748b; margin-bottom:20px; font-size:14px;"><?php esc_html_e('Add your first restaurant table with login credentials and QR codes.', 'antalya-pos'); ?></p>
        <a href="<?php echo $add_table_url; ?>" class="apos-btn"><?php esc_html_e('Add First Table', 'antalya-pos'); ?></a>
    </div>
<?php else : ?>
    <table class="apos-table apos-full-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Table Name', 'antalya-pos'); ?></th>
                <th width="130"><?php esc_html_e('Capacity', 'antalya-pos'); ?></th>
                <th width="170"><?php esc_html_e('Assigned Waiter', 'antalya-pos'); ?></th>
                <th width="210"><?php esc_html_e('Tablet Login / Pass', 'antalya-pos'); ?></th>
                <th width="150"><?php esc_html_e('Status', 'antalya-pos'); ?></th>
                <th width="260"><?php esc_html_e('Actions', 'antalya-pos'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tables as $tbl) :
                $tbl_id       = intval($tbl->id);
                $tbl_name     = $tbl->table_name ?? '';
                $active_info  = (isset($active_rows) && isset($active_rows[$tbl_name])) ? $active_rows[$tbl_name] : null;
                $is_busy      = !empty($active_info);
                $tablet_link  = esc_url(add_query_arg('table', $tbl_name, $base_url));
                $search_blob  = strtolower('table ' . $tbl_name . ' ' . ($tbl->table_login ?? '') . ' ' . ($tbl->assigned_waiter ?? ''));

                $view_url     = esc_url(add_query_arg('view_table', $tbl_id, $base_tables_url));
                $edit_url     = esc_url(add_query_arg('edit_table', $tbl_id, $base_tables_url));
                $del_url      = wp_nonce_url(
                    add_query_arg(['subtab' => 'all', 'del_table' => $tbl_id], $base_tables_url),
                    'rtpos_delete_table_' . $tbl_id
                );
            ?>
            <tr class="tbl-row" data-search="<?php echo esc_attr($search_blob); ?>" data-status="<?php echo $is_busy ? 'busy' : 'free'; ?>">
                <td>
                    <a href="<?php echo $view_url; ?>" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                        <strong style="font-size:15px; color:#0f172a;">
                            <?php
                            printf(
                                /* translators: %s: table name/number */
                                esc_html__('Table %s', 'antalya-pos'),
                                esc_html($tbl_name)
                            );
                            ?>
                        </strong>
                    </a>
                </td>
                <td>
                    <span style="font-weight:700; color:#475569;">
                        <?php
                        printf(
                            /* translators: %d: number of seats */
                            esc_html__('%d Persons', 'antalya-pos'),
                            intval($tbl->seats)
                        );
                        ?>
                    </span>
                </td>
                <td>
                    <span style="background:#fff7ed; color:#ea580c; border:1px solid #fed7aa; padding:3px 10px; border-radius:8px; font-size:12px; font-weight:700; display:inline-block;">
                        <?php echo esc_html(!empty($tbl->assigned_waiter) ? $tbl->assigned_waiter : __('Unassigned', 'antalya-pos')); ?>
                    </span>
                </td>
                <td>
                    <div style="font-size:12px; color:#334155;">
                        <div><?php esc_html_e('Login:', 'antalya-pos'); ?> <strong><?php echo esc_html(!empty($tbl->table_login) ? $tbl->table_login : '—'); ?></strong></div>
                        <div><?php esc_html_e('Pass:', 'antalya-pos'); ?> <code style="background:#f1f5f9; padding:1px 5px; border-radius:4px; color:#ea580c; font-weight:700;"><?php echo esc_html(!empty($tbl->table_password) ? $tbl->table_password : '—'); ?></code></div>
                    </div>
                </td>
                <td>
                    <span style="background:<?php echo $is_busy ? '#fef3c7' : '#d1fae5'; ?>; color:<?php echo $is_busy ? '#d97706' : '#059669'; ?>; padding:4px 11px; border-radius:20px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:5px;">
                        <span style="width:7px; height:7px; border-radius:50%; background:<?php echo $is_busy ? '#f59e0b' : '#10b981'; ?>; display:inline-block;"></span>
                        <span><?php echo $is_busy ? esc_html__('Occupied', 'antalya-pos') : esc_html__('Available', 'antalya-pos'); ?></span>
                    </span>
                    <?php if ($is_busy) : ?>
                        <br><a href="<?php echo $orders_url; ?>" style="font-size:11px; color:#ea580c; font-weight:700; text-decoration:none;">
                            <?php
                            printf(
                                /* translators: 1: order count, 2: running total amount */
                                esc_html__('%1$d Order (%2$s)', 'antalya-pos'),
                                intval($active_info->ord_cnt),
                                $cur . number_format((float) $active_info->running_total, 2)
                            );
                            ?>
                        </a>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                        <a href="<?php echo $view_url; ?>" class="tbl-act-btn act-view" style="padding:6px 10px; border-radius:7px; font-size:12px; font-weight:700; text-decoration:none; background:#eff6ff; color:#1d4ed8;" title="<?php esc_attr_e('View Table', 'antalya-pos'); ?>"><?php esc_html_e('View', 'antalya-pos'); ?></a>
                        <a href="<?php echo $edit_url; ?>" class="tbl-act-btn act-edit" style="padding:6px 10px; border-radius:7px; font-size:12px; font-weight:700; text-decoration:none; background:#f1f5f9; color:#334155;" title="<?php esc_attr_e('Edit Table', 'antalya-pos'); ?>"><?php esc_html_e('Edit', 'antalya-pos'); ?></a>
                        <button type="button" class="apos-btn-secondary apos-copy-btn" data-link="<?php echo esc_attr($tablet_link); ?>" style="padding:6px 10px; font-size:12px;"><?php esc_html_e('Copy URL', 'antalya-pos'); ?></button>
                        <a href="<?php echo esc_url($del_url); ?>" class="tbl-act-btn act-del js-confirm-del" data-tname="<?php echo esc_attr($tbl_name); ?>" style="padding:6px 10px; border-radius:7px; font-size:12px; font-weight:700; text-decoration:none; background:#fef2f2; color:#dc2626;" title="<?php esc_attr_e('Delete Table', 'antalya-pos'); ?>"><?php esc_html_e('Del', 'antalya-pos'); ?></a>
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
    let copiedText = <?php echo wp_json_encode(__('Copied', 'antalya-pos')); ?>;

    function applyTableFilters() {
        let q = ($('#tbl-live-search').val() || '').toLowerCase();
        $('.tbl-row').each(function() {
            let matchText   = String($(this).data('search') || '').indexOf(q) > -1;
            let matchStatus = (currentStatusFilter === 'all') || ($(this).data('status') === currentStatusFilter);
            $(this).toggle(matchText && matchStatus);
        });
    }

    $('#tbl-live-search').on('keyup', applyTableFilters);

    $('.tbl-st-pill').on('click', function() {
        $('.tbl-st-pill').removeClass('active');
        $(this).addClass('active');
        currentStatusFilter = $(this).data('st') || 'all';
        applyTableFilters();
    });

    $(document).on('click', '.js-confirm-del', function(e) {
        let tName = $(this).data('tname') || <?php echo wp_json_encode(__('this table', 'antalya-pos')); ?>;
        let msg   = <?php echo wp_json_encode(__('Are you sure you want to delete Table "%s"?', 'antalya-pos')); ?>.replace('%s', tName);
        if (!confirm(msg)) {
            e.preventDefault();
        }
    });

    $('.apos-copy-btn').on('click', function() {
        let link = $(this).data('link') || '';
        navigator.clipboard.writeText(link);
        let btn = $(this);
        let orig = btn.text();
        btn.text(copiedText);
        setTimeout(function() {
            btn.text(orig);
        }, 1500);
    });
});
</script>