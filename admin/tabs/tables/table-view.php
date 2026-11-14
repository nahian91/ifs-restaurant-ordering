<?php
/**
 * Antalya POS Single Restaurant Table Details & QR Tent View
 *
 * @package Antalya_POS
 * @since 6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options') || empty($view_tbl)) {
    return;
}

global $wpdb;
$t_orders = $wpdb->prefix . 'rtpos_orders';

// Safe currency & restaurant name fallback
if (!isset($cur)) {
    $s   = rtpos_get_settings();
    $cur = esc_html($s['currency'] ?? '৳');
}
$rest_name = isset($s['restaurant_name']) ? $s['restaurant_name'] : 'Antalya POS Restaurant';

$tbl_id        = intval($view_tbl->id);
$tbl_name      = $view_tbl->table_name ?? '';
$v_is_busy     = isset($active_rows[$tbl_name]);
$v_active_info = $v_is_busy ? $active_rows[$tbl_name] : null;
$base_url      = isset($base_url) ? $base_url : home_url('/');
$v_link        = esc_url(add_query_arg('table', $tbl_name, $base_url));
$v_qr          = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' . urlencode($v_link);

$v_orders      = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$t_orders} WHERE table_no = %s ORDER BY id DESC LIMIT 10",
        $tbl_name
    )
) ?: [];

$base_tables_url = admin_url('admin.php?page=antalya-pos&tab=tables');
$edit_url        = esc_url(add_query_arg('edit_table', $tbl_id, $base_tables_url));
$del_url         = wp_nonce_url(
    add_query_arg(['subtab' => 'all', 'del_table' => $tbl_id], $base_tables_url),
    'rtpos_delete_table_' . $tbl_id
);
?>

<div class="apos-card apos-full-card">
    <div style="display:grid; grid-template-columns: 320px 1fr; gap:28px; align-items:start;">
        <div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:18px; padding:24px; text-align:center;">
            <div style="font-size:12px; font-weight:800; color:#64748b; text-transform:uppercase; margin-bottom:12px;"><?php esc_html_e('Table QR Code', 'antalya-pos'); ?></div>
            <img id="v-tbl-qr-img" src="<?php echo esc_url($v_qr); ?>" alt="<?php echo esc_attr('QR for Table ' . $tbl_name); ?>" style="width:200px; height:200px; background:#fff; padding:8px; border:1px solid #e2e8f0; border-radius:12px;">
            <div style="margin-top:14px; display:flex; flex-direction:column; gap:8px;">
                <a href="<?php echo $v_link; ?>" target="_blank" rel="noopener noreferrer" class="apos-btn" style="justify-content:center;"><?php esc_html_e('Launch Tablet Menu', 'antalya-pos'); ?></a>
                <button type="button" id="v-print-qr-btn" data-tname="<?php echo esc_attr($tbl_name); ?>" class="apos-btn-dark" style="padding:10px;"><?php esc_html_e('Print QR Tent', 'antalya-pos'); ?></button>
            </div>
        </div>

        <div>
            <div style="display:flex; justify-content:space-between; align-items:flex-start; padding-bottom:16px; border-bottom:1.5px solid #f1f5f9;">
                <div>
                    <div style="font-size:12px; font-weight:700; color:#64748b;">
                        <?php
                        printf(
                            /* translators: %d: table ID */
                            esc_html__('TABLE ID #%d', 'antalya-pos'),
                            $tbl_id
                        );
                        ?>
                    </div>
                    <h2 style="margin:0; font-size:25px; font-weight:800; color:#0f172a;">
                        <?php
                        printf(
                            /* translators: %s: table name/number */
                            esc_html__('Table %s', 'antalya-pos'),
                            esc_html($tbl_name)
                        );
                        ?>
                    </h2>
                </div>
                <div style="display:flex; gap:10px;">
                    <a href="<?php echo $edit_url; ?>" class="apos-btn"><?php esc_html_e('Edit', 'antalya-pos'); ?></a>
                    <a href="<?php echo esc_url($del_url); ?>" class="tbl-act-btn act-del js-confirm-del" data-tname="<?php echo esc_attr($tbl_name); ?>" style="padding:10px 16px; border-radius:8px; text-decoration:none; background:#fef2f2; color:#dc2626; font-weight:700;"><?php esc_html_e('Delete', 'antalya-pos'); ?></a>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:14px; margin:18px 0;">
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:13px; padding:14px; border-left:4px solid <?php echo $v_is_busy ? '#f59e0b' : '#10b981'; ?>;">
                    <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;"><?php esc_html_e('Status', 'antalya-pos'); ?></div>
                    <div style="font-size:18px; font-weight:800; color:<?php echo $v_is_busy ? '#d97706' : '#059669'; ?>; margin-top:4px;">
                        <?php echo $v_is_busy ? esc_html__('Occupied', 'antalya-pos') : esc_html__('Available', 'antalya-pos'); ?>
                    </div>
                </div>
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:14px; border-left:4px solid #3b82f6;">
                    <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;"><?php esc_html_e('Capacity & Waiter', 'antalya-pos'); ?></div>
                    <div style="font-size:15px; font-weight:800; color:#0f172a; margin-top:4px;">
                        <?php
                        printf(
                            /* translators: %d: seating capacity */
                            esc_html__('%d Persons', 'antalya-pos'),
                            intval($view_tbl->seats)
                        );
                        ?>
                        <br>
                        <small style="color:#f97316;"><?php echo esc_html(!empty($view_tbl->assigned_waiter) ? $view_tbl->assigned_waiter : __('No Waiter', 'antalya-pos')); ?></small>
                    </div>
                </div>
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:14px; border-left:4px solid #f97316;">
                    <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;"><?php esc_html_e('Running Bill', 'antalya-pos'); ?></div>
                    <div style="font-size:18px; font-weight:800; color:#f97316; margin-top:4px; font-family:monospace;">
                        <?php echo $cur . esc_html(number_format(($v_is_busy ? floatval($v_active_info->running_total) : 0.0), 2)); ?>
                    </div>
                </div>
            </div>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:16px; margin-bottom:18px;">
                <strong style="font-size:13px;"><?php esc_html_e('Login Credentials:', 'antalya-pos'); ?></strong>
                <div style="display:flex; gap:16px; margin-top:8px; font-size:13px;">
                    <span><?php esc_html_e('Username:', 'antalya-pos'); ?> <strong><?php echo esc_html(!empty($view_tbl->table_login) ? $view_tbl->table_login : '—'); ?></strong></span>
                    <span><?php esc_html_e('Password:', 'antalya-pos'); ?> <code style="background:#fff; padding:2px 6px; border:1px solid #e2e8f0; color:#ea580c; font-weight:700;"><?php echo esc_html(!empty($view_tbl->table_password) ? $view_tbl->table_password : '—'); ?></code></span>
                </div>
            </div>

            <h3 style="margin:20px 0 10px; font-size:15px; font-weight:800;">
                <?php
                printf(
                    /* translators: %s: table name/number */
                    esc_html__('Recent Orders on Table %s', 'antalya-pos'),
                    esc_html($tbl_name)
                );
                ?>
            </h3>
            <table class="apos-table" style="width:100%; border:1px solid #e2e8f0; box-shadow:none;">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Order #', 'antalya-pos'); ?></th>
                        <th><?php esc_html_e('Total', 'antalya-pos'); ?></th>
                        <th><?php esc_html_e('Status', 'antalya-pos'); ?></th>
                        <th><?php esc_html_e('Time', 'antalya-pos'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($v_orders)) : ?>
                        <tr><td colspan="4" style="text-align:center; color:#94a3b8; padding:20px;"><?php esc_html_e('No recent orders.', 'antalya-pos'); ?></td></tr>
                    <?php else : 
                        foreach ($v_orders as $vo) :
                    ?>
                        <tr>
                            <td><strong>#<?php echo intval($vo->id); ?></strong></td>
                            <td><strong style="font-family:monospace;"><?php echo $cur . esc_html(number_format((float) $vo->total_amount, 2)); ?></strong></td>
                            <td style="text-transform:uppercase; font-size:11.5px; font-weight:700;"><?php echo esc_html($vo->status); ?></td>
                            <td style="color:#64748b; font-size:12.5px;"><?php echo esc_html(date('M d, h:i A', strtotime($vo->created_at))); ?></td>
                        </tr>
                    <?php endforeach; 
                    endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let restName = <?php echo wp_json_encode($rest_name); ?>;
    let confirmDelMsg = <?php echo wp_json_encode(__('Are you sure you want to delete Table "%s"?', 'antalya-pos')); ?>;

    $(document).on('click', '.js-confirm-del', function(e) {
        let tName = $(this).data('tname') || <?php echo wp_json_encode(__('this table', 'antalya-pos')); ?>;
        let msg   = confirmDelMsg.replace('%s', tName);
        if (!confirm(msg)) {
            e.preventDefault();
        }
    });

    $('#v-print-qr-btn').on('click', function() {
        let tName = $(this).data('tname') || '';
        let qrSrc = $('#v-tbl-qr-img').attr('src') || '';
        let w = window.open('', '_blank', 'width=420,height=550');
        if (w) {
            w.document.write(
                '<html><head><title>Table ' + tName + ' QR</title></head>' +
                '<body style="font-family:sans-serif; text-align:center; padding:30px;">' +
                '<h2>' + restName + '</h2>' +
                '<img src="' + qrSrc + '" style="width:220px; margin: 15px 0;" alt="QR Code">' +
                '<h1 style="margin-top:15px; font-size:28px;">TABLE ' + tName + '</h1>' +
                '<script>window.onload = function() { window.print(); };<\/script>' +
                '</body></html>'
            );
            w.document.close();
        }
    });
});
</script>