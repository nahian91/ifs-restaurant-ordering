<?php
if (!defined('ABSPATH')) exit;

$svg_edit    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_trash   = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';
$svg_refresh = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>';

$v_initial   = strtoupper(mb_substr($view_staff->name, 0, 1));
$v_is_active = ($view_staff->status === 'Active');
?>

<div class="apos-card apos-full-card">
    <div class="stf-view-hero">
        <div class="stf-view-hero-left">
            <div class="stf-view-avatar"><?php echo esc_html($v_initial); ?></div>
            <div>
                <div class="stf-view-id">STAFF ID #<?php echo intval($view_staff->id); ?></div>
                <h2 class="stf-view-title"><?php echo esc_html($view_staff->name); ?></h2>
                <span class="stf-view-role-badge"><?php echo esc_html($view_staff->role); ?></span>
            </div>
        </div>

        <div class="stf-view-btns">
            <a href="?page=antalya-pos&tab=staff&view_staff=<?php echo intval($view_staff->id); ?>&toggle_staff=<?php echo intval($view_staff->id); ?>" class="stf-act-btn act-shift stf-act-lg">
                <?php echo $svg_refresh; ?> <span>Switch Shift Status</span>
            </a>
            <a href="?page=antalya-pos&tab=staff&edit_staff=<?php echo intval($view_staff->id); ?>" class="apos-btn stf-btn-icon">
                <?php echo $svg_edit; ?> <span>Edit Profile</span>
            </a>
            <a href="?page=antalya-pos&tab=staff&subtab=all&del_staff=<?php echo intval($view_staff->id); ?>" class="stf-act-btn act-del stf-act-lg js-confirm-del" data-name="<?php echo esc_attr($view_staff->name); ?>">
                <?php echo $svg_trash; ?> <span>Remove</span>
            </a>
        </div>
    </div>

    <!-- Staff Info Cards -->
    <div class="stf-view-stats">
        <div class="stf-stat-box <?php echo $v_is_active ? 'stf-stat-active' : 'stf-stat-off'; ?>">
            <div class="stf-stat-lbl">Duty Shift Status</div>
            <div class="stf-stat-val stf-stat-val-flex <?php echo $v_is_active ? 'stf-val-active' : 'stf-val-off'; ?>">
                <span class="stf-dot stf-dot-lg <?php echo $v_is_active ? 'stf-dot-active' : 'stf-dot-off'; ?>"></span>
                <span><?php echo esc_html($view_staff->status); ?></span>
            </div>
        </div>

        <div class="stf-stat-box stf-stat-orange">
            <div class="stf-stat-lbl">Assigned Role</div>
            <div class="stf-stat-val"><?php echo esc_html($view_staff->role); ?></div>
        </div>

        <div class="stf-stat-box stf-stat-blue">
            <div class="stf-stat-lbl">Contact Phone</div>
            <div class="stf-stat-val">
                <?php if (!empty($view_staff->phone)) : ?>
                    <a href="tel:<?php echo esc_attr($view_staff->phone); ?>" class="stf-phone-link"><?php echo esc_html($view_staff->phone); ?></a>
                <?php else : ?>
                    <span class="stf-phone-empty">Not Provided</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $(document).on('click', '.js-confirm-del', function(e) {
        if (!confirm('Remove ' + ($(this).data('name') || 'this staff member') + ' from staff list?')) {
            e.preventDefault();
        }
    });
});
</script>