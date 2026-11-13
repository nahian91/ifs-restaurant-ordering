<?php
if (!defined('ABSPATH')) exit;

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
        <h3 class="stf-empty-title">No Staff Members Added Yet</h3>
        <p class="stf-empty-desc">Add your chefs, waiters, and managers to keep track of shift schedules.</p>
        <a href="?page=antalya-pos&tab=staff&subtab=add" class="apos-btn stf-btn-icon">
            <?php echo $svg_plus; ?>
            <span>Add First Staff</span>
        </a>
    </div>
<?php else : ?>
    <table class="apos-table apos-full-table">
        <thead>
            <tr>
                <th>Staff Member</th>
                <th class="col-stf-role">Role</th>
                <th class="col-stf-phone">Phone</th>
                <th class="col-stf-status">Shift Status</th>
                <th class="col-stf-act">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($staff_list as $st) : 
                $initial   = strtoupper(mb_substr($st->name, 0, 1));
                $is_active = ($st->status === 'Active');
            ?>
            <tr class="stf-row" data-search="<?php echo esc_attr(strtolower($st->name . ' ' . $st->phone)); ?>" data-role="<?php echo esc_attr(strtolower($st->role)); ?>">
                <td>
                    <a href="?page=antalya-pos&tab=staff&view_staff=<?php echo intval($st->id); ?>" class="stf-member-link">
                        <div class="stf-avatar"><?php echo esc_html($initial); ?></div>
                        <strong class="stf-member-name"><?php echo esc_html($st->name); ?></strong>
                    </a>
                </td>
                <td>
                    <span class="stf-role-badge"><?php echo esc_html($st->role); ?></span>
                </td>
                <td class="stf-phone-cell"><?php echo esc_html($st->phone ?: '—'); ?></td>
                <td>
                    <span class="stf-status-pill <?php echo $is_active ? 'stf-pill-active' : 'stf-pill-off'; ?>">
                        <span class="stf-dot <?php echo $is_active ? 'stf-dot-active' : 'stf-dot-off'; ?>"></span>
                        <span><?php echo esc_html($st->status); ?></span>
                    </span>
                </td>
                <td>
                    <div class="stf-act-group">
                        <a href="?page=antalya-pos&tab=staff&view_staff=<?php echo intval($st->id); ?>" class="stf-act-btn act-view"><?php echo $svg_eye; ?> View</a>
                        <a href="?page=antalya-pos&tab=staff&subtab=all&toggle_staff=<?php echo intval($st->id); ?>" class="stf-act-btn act-shift" title="Switch Shift Status"><?php echo $svg_refresh; ?> Shift</a>
                        <a href="?page=antalya-pos&tab=staff&edit_staff=<?php echo intval($st->id); ?>" class="stf-act-btn act-edit"><?php echo $svg_edit; ?> Edit</a>
                        <a href="?page=antalya-pos&tab=staff&subtab=all&del_staff=<?php echo intval($st->id); ?>" class="stf-act-btn act-del js-confirm-del" data-name="<?php echo esc_attr($st->name); ?>"><?php echo $svg_trash; ?> Delete</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
jQuery(document).ready(function($) {
    function filterStaff() {
        let q    = ($('#stf-live-search').val() || '').toLowerCase();
        let role = ($('#stf-role-filter').val() || 'all').toLowerCase();

        $('.stf-row').each(function() {
            let matchText = $(this).data('search').indexOf(q) > -1;
            let matchRole = (role === 'all') || ($(this).data('role') === role);
            $(this).toggle(matchText && matchRole);
        });
    }
    $('#stf-live-search').on('keyup', filterStaff);
    $('#stf-role-filter').on('change', filterStaff);

    $(document).on('click', '.js-confirm-del', function(e) {
        if (!confirm('Remove ' + ($(this).data('name') || 'this staff member') + ' from staff list?')) {
            e.preventDefault();
        }
    });
});
</script>