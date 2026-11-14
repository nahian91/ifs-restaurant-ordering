<?php
/**
 * Antalya POS Single Staff Profile & Shift Details View
 *
 * @package Antalya_POS
 * @since 6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options') || empty($view_staff)) {
    return;
}

$staff_id      = intval($view_staff->id);
$staff_name    = $view_staff->name ?? '';
$staff_role    = $view_staff->role ?? '';
$staff_status  = $view_staff->status ?? 'Active';
$staff_phone   = $view_staff->phone ?? '';

$v_initial     = strtoupper(mb_substr($staff_name, 0, 1));
$v_is_active   = ($staff_status === 'Active');

$base_staff_url = admin_url('admin.php?page=antalya-pos&tab=staff');

// Security Nonces for Actions
$toggle_url = wp_nonce_url(
    add_query_arg(['view_staff' => $staff_id, 'toggle_staff' => $staff_id], $base_staff_url),
    'rtpos_toggle_staff_' . $staff_id
);
$edit_url   = esc_url(add_query_arg('edit_staff', $staff_id, $base_staff_url));
$del_url    = wp_nonce_url(
    add_query_arg(['subtab' => 'all', 'del_staff' => $staff_id], $base_staff_url),
    'rtpos_delete_staff_' . $staff_id
);

$svg_edit    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
$svg_trash   = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';
$svg_refresh = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>';
?>

<div class="apos-card apos-full-card">
    <div class="stf-view-hero">
        <div class="stf-view-hero-left">
            <div class="stf-view-avatar"><?php echo esc_html($v_initial); ?></div>
            <div>
                <div class="stf-view-id">
                    <?php
                    printf(
                        /* translators: %d: staff ID */
                        esc_html__('STAFF ID #%d', 'antalya-pos'),
                        $staff_id
                    );
                    ?>
                </div>
                <h2 class="stf-view-title"><?php echo esc_html($staff_name); ?></h2>
                <span class="stf-view-role-badge"><?php echo esc_html($staff_role); ?></span>
            </div>
        </div>

        <div class="stf-view-btns">
            <a href="<?php echo esc_url($toggle_url); ?>" class="stf-act-btn act-shift stf-act-lg" title="<?php esc_attr_e('Switch Duty Status', 'antalya-pos'); ?>">
                <?php echo $svg_refresh; ?>
                <span><?php esc_html_e('Switch Shift Status', 'antalya-pos'); ?></span>
            </a>
            <a href="<?php echo $edit_url; ?>" class="apos-btn stf-btn-icon" title="<?php esc_attr_e('Edit Profile', 'antalya-pos'); ?>">
                <?php echo $svg_edit; ?>
                <span><?php esc_html_e('Edit Profile', 'antalya-pos'); ?></span>
            </a>
            <a href="<?php echo esc_url($del_url); ?>" class="stf-act-btn act-del stf-act-lg js-confirm-del" data-name="<?php echo esc_attr($staff_name); ?>" title="<?php esc_attr_e('Remove Staff', 'antalya-pos'); ?>">
                <?php echo $svg_trash; ?>
                <span><?php esc_html_e('Remove', 'antalya-pos'); ?></span>
            </a>
        </div>
    </div>

    <!-- Staff Info Cards -->
    <div class="stf-view-stats">
        <div class="stf-stat-box <?php echo $v_is_active ? 'stf-stat-active' : 'stf-stat-off'; ?>">
            <div class="stf-stat-lbl"><?php esc_html_e('Duty Shift Status', 'antalya-pos'); ?></div>
            <div class="stf-stat-val stf-stat-val-flex <?php echo $v_is_active ? 'stf-val-active' : 'stf-val-off'; ?>">
                <span class="stf-dot stf-dot-lg <?php echo $v_is_active ? 'stf-dot-active' : 'stf-dot-off'; ?>"></span>
                <span><?php echo esc_html($staff_status); ?></span>
            </div>
        </div>

        <div class="stf-stat-box stf-stat-orange">
            <div class="stf-stat-lbl"><?php esc_html_e('Assigned Role', 'antalya-pos'); ?></div>
            <div class="stf-stat-val"><?php echo esc_html($staff_role); ?></div>
        </div>

        <div class="stf-stat-box stf-stat-blue">
            <div class="stf-stat-lbl"><?php esc_html_e('Contact Phone', 'antalya-pos'); ?></div>
            <div class="stf-stat-val">
                <?php if (!empty($staff_phone)) : ?>
                    <a href="tel:<?php echo esc_attr($staff_phone); ?>" class="stf-phone-link"><?php echo esc_html($staff_phone); ?></a>
                <?php else : ?>
                    <span class="stf-phone-empty"><?php esc_html_e('Not Provided', 'antalya-pos'); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let confirmDelMsg = <?php echo wp_json_encode(__('Are you sure you want to remove "%s" from staff list?', 'antalya-pos')); ?>;

    $(document).on('click', '.js-confirm-del', function(e) {
        let staffName = $(this).data('name') || <?php echo wp_json_encode(__('this staff member', 'antalya-pos')); ?>;
        let msg       = confirmDelMsg.replace('%s', staffName);
        if (!confirm(msg)) {
            e.preventDefault();
        }
    });
});
</script>