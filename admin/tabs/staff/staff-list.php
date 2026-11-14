<?php
/**
 * Antalya POS Staff Management Table List View
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

$base_staff_url = admin_url('admin.php?page=antalya-pos&tab=staff');
$add_staff_url  = esc_url(add_query_arg('subtab', 'add', $base_staff_url));

$svg_users_lg = '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
$svg_plus     = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
$svg_edit     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_eye      = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
$svg_trash    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';
$svg_refresh  = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>';
?>

<?php if (empty($staff_list)) : ?>
    <div class="apos-card apos-full-card stf-empty-card">
        <div class="stf-empty-ico">
            <?php echo $svg_users_lg; ?>
        </div>
        <h3 class="stf-empty-title"><?php esc_html_e('No Staff Members Added Yet', 'antalya-pos'); ?></h3>
        <p class="stf-empty-desc"><?php esc_html_e('Add your chefs, waiters, and managers to keep track of shift schedules.', 'antalya-pos'); ?></p>
        <a href="<?php echo $add_staff_url; ?>" class="apos-btn stf-btn-icon">
            <?php echo $svg_plus; ?>
            <span><?php esc_html_e('Add First Staff', 'antalya-pos'); ?></span>
        </a>
    </div>
<?php else : ?>
    <table class="apos-table apos-full-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Staff Member', 'antalya-pos'); ?></th>
                <th class="col-stf-role"><?php esc_html_e('Role', 'antalya-pos'); ?></th>
                <th class="col-stf-phone"><?php esc_html_e('Phone', 'antalya-pos'); ?></th>
                <th class="col-stf-status"><?php esc_html_e('Shift Status', 'antalya-pos'); ?></th>
                <th class="col-stf-act"><?php esc_html_e('Actions', 'antalya-pos'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($staff_list as $st) :
                $staff_id   = intval($st->id);
                $staff_name = $st->name ?? '';
                $staff_role = $st->role ?? '';
                $staff_phn  = $st->phone ?? '';
                $staff_stat = $st->status ?? 'Active';

                $initial    = strtoupper(mb_substr($staff_name, 0, 1));
                $is_active  = ($staff_stat === 'Active');
                $search_blob = strtolower($staff_name . ' ' . $staff_phn);

                $view_url   = esc_url(add_query_arg('view_staff', $staff_id, $base_staff_url));
                $edit_url   = esc_url(add_query_arg('edit_staff', $staff_id, $base_staff_url));
                $toggle_url = wp_nonce_url(
                    add_query_arg(['subtab' => 'all', 'toggle_staff' => $staff_id], $base_staff_url),
                    'rtpos_toggle_staff_' . $staff_id
                );
                $del_url    = wp_nonce_url(
                    add_query_arg(['subtab' => 'all', 'del_staff' => $staff_id], $base_staff_url),
                    'rtpos_delete_staff_' . $staff_id
                );
            ?>
            <tr class="stf-row" data-search="<?php echo esc_attr($search_blob); ?>" data-role="<?php echo esc_attr(strtolower($staff_role)); ?>">
                <td>
                    <a href="<?php echo $view_url; ?>" class="stf-member-link">
                        <div class="stf-avatar"><?php echo esc_html($initial); ?></div>
                        <strong class="stf-member-name"><?php echo esc_html($staff_name); ?></strong>
                    </a>
                </td>
                <td>
                    <span class="stf-role-badge"><?php echo esc_html($staff_role); ?></span>
                </td>
                <td class="stf-phone-cell"><?php echo esc_html(!empty($staff_phn) ? $staff_phn : '—'); ?></td>
                <td>
                    <span class="stf-status-pill <?php echo $is_active ? 'stf-pill-active' : 'stf-pill-off'; ?>">
                        <span class="stf-dot <?php echo $is_active ? 'stf-dot-active' : 'stf-dot-off'; ?>"></span>
                        <span><?php echo esc_html($staff_stat); ?></span>
                    </span>
                </td>
                <td>
                    <div class="stf-act-group">
                        <a href="<?php echo $view_url; ?>" class="stf-act-btn act-view" title="<?php esc_attr_e('View Profile', 'antalya-pos'); ?>"><?php echo $svg_eye; ?> <?php esc_html_e('View', 'antalya-pos'); ?></a>
                        <a href="<?php echo esc_url($toggle_url); ?>" class="stf-act-btn act-shift" title="<?php esc_attr_e('Switch Shift Status', 'antalya-pos'); ?>"><?php echo $svg_refresh; ?> <?php esc_html_e('Shift', 'antalya-pos'); ?></a>
                        <a href="<?php echo $edit_url; ?>" class="stf-act-btn act-edit" title="<?php esc_attr_e('Edit Staff', 'antalya-pos'); ?>"><?php echo $svg_edit; ?> <?php esc_html_e('Edit', 'antalya-pos'); ?></a>
                        <a href="<?php echo esc_url($del_url); ?>" class="stf-act-btn act-del js-confirm-del" data-name="<?php echo esc_attr($staff_name); ?>" title="<?php esc_attr_e('Delete Staff', 'antalya-pos'); ?>"><?php echo $svg_trash; ?> <?php esc_html_e('Delete', 'antalya-pos'); ?></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
jQuery(document).ready(function($) {
    let confirmDelMsg = <?php echo wp_json_encode(__('Are you sure you want to remove "%s" from staff list?', 'antalya-pos')); ?>;

    function filterStaff() {
        let q    = ($('#stf-live-search').val() || '').toLowerCase();
        let role = ($('#stf-role-filter').val() || 'all').toLowerCase();

        $('.stf-row').each(function() {
            let matchText = String($(this).data('search') || '').indexOf(q) > -1;
            let matchRole = (role === 'all') || ($(this).data('role') === role);
            $(this).toggle(matchText && matchRole);
        });
    }

    $('#stf-live-search').on('keyup', filterStaff);
    $('#stf-role-filter').on('change', filterStaff);

    $(document).on('click', '.js-confirm-del', function(e) {
        let staffName = $(this).data('name') || <?php echo wp_json_encode(__('this staff member', 'antalya-pos')); ?>;
        let msg       = confirmDelMsg.replace('%s', staffName);
        if (!confirm(msg)) {
            e.preventDefault();
        }
    });
});
</script>