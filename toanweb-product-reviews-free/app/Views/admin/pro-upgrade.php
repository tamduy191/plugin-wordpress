<?php
/**
 * Shared PRO upgrade page template.
 * Variables expected: $page_icon (SVG string), $page_title, $page_desc, $features (array of strings).
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap twr-pro-page-wrap">
  <div class="twr-pro-page">

    <div class="twr-pro-page__icon-wrap">
      <?php echo $page_icon; // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </div>

    <div class="twr-pro-page__badge">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
      </svg>
      CHỈ CÓ Ở BẢN PRO
    </div>

    <h1 class="twr-pro-page__title"><?php echo esc_html( $page_title ); ?></h1>
    <p class="twr-pro-page__desc"><?php echo esc_html( $page_desc ); ?></p>

    <ul class="twr-pro-page__features">
      <?php foreach ( $features as $feature ) : ?>
      <li class="twr-pro-page__feature">
        <span class="twr-pro-page__feature-icon">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </span>
        <span><?php echo esc_html( $feature ); ?></span>
      </li>
      <?php endforeach; ?>
    </ul>

    <a href="https://doxuantoan.com/toanweb-product-reviews"
       target="_blank"
       rel="noopener noreferrer"
       class="twr-pro-page__cta">
      Nâng cấp lên PRO
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <line x1="5" y1="12" x2="19" y2="12"/>
        <polyline points="12 5 19 12 12 19"/>
      </svg>
    </a>

    <p class="twr-pro-page__footnote">Mua 1 lần, dùng vĩnh viễn. Không phí ẩn.</p>

  </div>
</div>
