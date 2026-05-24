<?php defined( 'ABSPATH' ) || exit;
// $force_lightbox is set by ReviewController when photos gallery mode is active
if ( ! TWR_SettingsModel::get( 'lightbox' ) && empty( $force_lightbox ) ) return;
?>

<div class="twr-lightbox" id="twr-lightbox" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="twr-lightbox__backdrop" id="twr-lb-backdrop"></div>

    <button class="twr-lightbox__close" id="twr-lb-close" aria-label="<?php esc_attr_e( 'Đóng', 'toanweb-product-reviews' ); ?>">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>

    <div class="twr-lightbox__inner">

        <!-- Ảnh -->
        <div class="twr-lightbox__img-panel">
            <button class="twr-lb-nav twr-lb-nav--prev" id="twr-lb-prev" aria-label="<?php esc_attr_e( 'Ảnh trước', 'toanweb-product-reviews' ); ?>">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
            </button>

            <div class="twr-lb-img-wrap" id="twr-lb-img-wrap">
                <!-- img injected by JS -->
            </div>

            <button class="twr-lb-nav twr-lb-nav--next" id="twr-lb-next" aria-label="<?php esc_attr_e( 'Ảnh sau', 'toanweb-product-reviews' ); ?>">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </button>

            <div class="twr-lb-counter" id="twr-lb-counter"></div>
        </div>

        <!-- Thông tin review -->
        <div class="twr-lightbox__review-panel" id="twr-lb-review">
            <!-- populated by JS -->
        </div>

    </div>
</div>
