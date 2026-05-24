<?php defined( 'ABSPATH' ) || exit; ?>

<div class="twr-pg-overlay" id="twr-photo-gallery" aria-hidden="true" role="dialog" aria-labelledby="twr-gallery-title">
    <div class="twr-pg">
        <div class="twr-pg__header">
            <h3 class="twr-pg__title" id="twr-gallery-title"></h3>
            <button class="twr-pg__close" id="twr-gallery-close" aria-label="<?php esc_attr_e( 'Đóng', 'toanweb-product-reviews' ); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="twr-pg__tabs" id="twr-gallery-tabs"></div>
        <div class="twr-pg__grid" id="twr-gallery-grid"></div>
    </div>
</div>
