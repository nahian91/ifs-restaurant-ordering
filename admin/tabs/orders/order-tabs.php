<?php
/**
 * Antalya POS Orders Header & Auto-Print Controls
 *
 * @package Antalya_POS
 * @since 6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

$svg_back    = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';
$svg_printer = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>';
$orders_url  = esc_url(admin_url('admin.php?page=antalya-pos&tab=orders'));
?>
<div class="apos-header">
    <div>
        <h1><?php esc_html_e('Orders & Kitchen Auto-Print', 'antalya-pos'); ?></h1>
        <p><?php esc_html_e('Track incoming table orders, update kitchen progress, and print KOT or Customer Bills.', 'antalya-pos'); ?></p>
    </div>

    <div class="ord-header-actions">
        <?php if (isset($view_order) && $view_order) : ?>
            <a href="<?php echo $orders_url; ?>" class="apos-btn-secondary ord-back-btn">
                <?php echo $svg_back; ?>
                <span><?php esc_html_e('Back to All Orders', 'antalya-pos'); ?></span>
            </a>
        <?php endif; ?>

        <div class="autoprint-box">
            <span class="pulse-dot" id="ap-dot"></span>
            <?php echo $svg_printer; ?>
            <span id="ap-label"><?php esc_html_e('Kitchen Auto-Print: ON (3s)', 'antalya-pos'); ?></span>
            <button type="button" id="toggle-autoprint" class="autoprint-toggle-btn"><?php esc_html_e('Pause', 'antalya-pos'); ?></button>
        </div>
    </div>
</div>