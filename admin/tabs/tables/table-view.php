<?php
if (!defined('ABSPATH')) exit;

$v_is_busy     = isset($active_rows[$view_tbl->table_name]);
$v_active_info = $v_is_busy ? $active_rows[$view_tbl->table_name] : null;
$v_link        = add_query_arg('table', $view_tbl->table_name, $base_url);
$v_qr          = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' . urlencode($v_link);
$v_orders      = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $t_orders WHERE table_no = %s ORDER BY id DESC LIMIT 10",
    $view_tbl->table_name
)) ?: [];
?>
<div class="apos-card apos-full-card">
    <div style="display:grid; grid-template-columns: 320px 1fr; gap:28px; align-items:start;">
        <div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:18px; padding:24px; text-align:center;">
            <div style="font-size:12px; font-weight:800; color:#64748b; text-transform:uppercase; margin-bottom:12px;">Table QR Code</div>
            <img id="v-tbl-qr-img" src="<?php echo esc_url($v_qr); ?>" style="width:200px; height:200px; background:#fff; padding:8px; border:1px solid #e2e8f0; border-radius:12px;">
            <div style="margin-top:14px; display:flex; flex-direction:column; gap:8px;">
                <a href="<?php echo esc_url($v_link); ?>" target="_blank" class="apos-btn" style="justify-content:center;">Launch Tablet Menu</a>
                <button type="button" id="v-print-qr-btn" data-tname="<?php echo esc_attr($view_tbl->table_name); ?>" class="apos-btn-dark" style="padding:10px;">Print QR Tent</button>
            </div>
        </div>

        <div>
            <div style="display:flex; justify-content:space-between; align-items:flex-start; padding-bottom:16px; border-bottom:1.5px solid #f1f5f9;">
                <div>
                    <div style="font-size:12px; font-weight:700; color:#64748b;">TABLE ID #<?php echo intval($view_tbl->id); ?></div>
                    <h2 style="margin:0; font-size:25px; font-weight:800; color:#0f172a;">Table <?php echo esc_html($view_tbl->table_name); ?></h2>
                </div>
                <div style="display:flex; gap:10px;">
                    <a href="?page=antalya-pos&tab=tables&edit_table=<?php echo intval($view_tbl->id); ?>" class="apos-btn">Edit</a>
                    <a href="?page=antalya-pos&tab=tables&subtab=all&del_table=<?php echo intval($view_tbl->id); ?>" class="tbl-act-btn act-del" style="padding:10px 16px;" onclick="return confirm('Delete Table?')">Delete</a>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:14px; margin:18px 0;">
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:13px; padding:14px; border-left:4px solid <?php echo $v_is_busy ? '#f59e0b' : '#10b981'; ?>;">
                    <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Status</div>
                    <div style="font-size:18px; font-weight:800; color:<?php echo $v_is_busy ? '#d97706' : '#059669'; ?>; margin-top:4px;"><?php echo $v_is_busy ? 'Occupied' : 'Available'; ?></div>
                </div>
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:14px; border-left:4px solid #3b82f6;">
                    <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Capacity & Waiter</div>
                    <div style="font-size:15px; font-weight:800; color:#0f172a; margin-top:4px;"><?php echo intval($view_tbl->seats); ?> Persons<br><small style="color:#f97316;"><?php echo esc_html($view_tbl->assigned_waiter ?: 'No Waiter'); ?></small></div>
                </div>
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:14px; border-left:4px solid #f97316;">
                    <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Running Bill</div>
                    <div style="font-size:18px; font-weight:800; color:#f97316; margin-top:4px;"><?php echo $cur . number_format($v_is_busy ? floatval($v_active_info->running_total) : 0, 2); ?></div>
                </div>
            </div>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:16px; margin-bottom:18px;">
                <strong style="font-size:13px;">Login Credentials:</strong>
                <div style="display:flex; gap:16px; margin-top:8px; font-size:13px;">
                    <span>Username: <strong><?php echo esc_html($view_tbl->table_login ?: '—'); ?></strong></span>
                    <span>Password: <code style="background:#fff; padding:2px 6px; border:1px solid #e2e8f0; color:#ea580c;"><?php echo esc_html($view_tbl->table_password ?: '—'); ?></code></span>
                </div>
            </div>

            <h3 style="margin:20px 0 10px; font-size:15px; font-weight:800;">Recent Orders on Table <?php echo esc_html($view_tbl->table_name); ?></h3>
            <table class="apos-table" style="width:100%; border:1px solid #e2e8f0; box-shadow:none;">
                <thead><tr><th>Order #</th><th>Total</th><th>Status</th><th>Time</th></tr></thead>
                <tbody>
                    <?php if (empty($v_orders)) : ?>
                        <tr><td colspan="4" style="text-align:center; color:#94a3b8; padding:20px;">No recent orders.</td></tr>
                    <?php else : foreach ($v_orders as $vo) : ?>
                        <tr>
                            <td><strong>#<?php echo intval($vo->id); ?></strong></td>
                            <td><strong><?php echo $cur . number_format($vo->total_amount, 2); ?></strong></td>
                            <td style="text-transform:uppercase; font-size:11.5px; font-weight:700;"><?php echo esc_html($vo->status); ?></td>
                            <td style="color:#64748b; font-size:12.5px;"><?php echo esc_html(date('M d, h:i A', strtotime($vo->created_at))); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
jQuery(document).ready(function($) {
    $('#v-print-qr-btn').on('click', function() {
        let tName = $(this).data('tname'), qrSrc = $('#v-tbl-qr-img').attr('src');
        let w = window.open('', '_blank', 'width=420,height=550');
        w.document.write(`<html><head><title>Table ${tName} QR</title></head><body style="font-family:sans-serif; text-align:center; padding:30px;"><h2>${rest_name = '<?php echo esc_js($rest_name); ?>'}</h2><img src="${qrSrc}" style="width:220px;"><h1 style="margin-top:15px;">TABLE ${tName}</h1><script>window.onload=function(){window.print();};<\/script></body></html>`);
        w.document.close();
    });
});
</script>