<?php
if (!defined('ABSPATH')) exit;

$svg_back = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';
?>
<div class="apos-header">
    <div>
        <h1>Orders & Kitchen Auto-Print</h1>
        <p>Track incoming table orders, update kitchen progress, and print KOT or Customer Bills.</p>
    </div>

    <div class="ord-header-actions">
        <?php if (isset($view_order) && $view_order) : ?>
            <a href="?page=antalya-pos&tab=orders" class="apos-btn-secondary ord-back-btn">
                <?php echo $svg_back; ?>
                <span>Back to All Orders</span>
            </a>
        <?php endif; ?>

        <div class="autoprint-box">
            <span class="pulse-dot" id="ap-dot"></span>
            <span id="ap-label">Kitchen Auto-Print: ON (3s)</span>
            <button type="button" id="toggle-autoprint" class="autoprint-toggle-btn">Pause</button>
        </div>
    </div>
</div>