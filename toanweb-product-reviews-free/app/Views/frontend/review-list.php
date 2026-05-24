<?php defined( 'ABSPATH' ) || exit; ?>

<?php if ( $settings['show_filter_bar'] ) : ?>
<?php $is_tiktok = ( ( $settings['filter_bar_style'] ?? 'text' ) === 'tiktok' ); ?>
<div class="twr-filter-bar<?php echo $is_tiktok ? ' twr-filter-bar--tiktok' : ''; ?>">

<?php if ( $is_tiktok ) : ?>

    <button class="twr-filter-btn twr-filter-btn--active" data-filter="all">
        <?php esc_html_e( 'Tất cả', 'toanweb-product-reviews' ); ?>
        <span class="twr-filter-count">(<?php echo esc_html( $summary['total'] ); ?>)</span>
    </button>

    <?php if ( $filter_counts['photos'] > 0 ) : ?>
    <button class="twr-filter-btn" data-filter="photos">
        <svg class="twr-filter-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
        <?php esc_html_e( 'Có ảnh/video', 'toanweb-product-reviews' ); ?>
        <span class="twr-filter-count">(<?php echo esc_html( $filter_counts['photos'] ); ?>)</span>
    </button>
    <?php endif; ?>

    <?php for ( $s = 5; $s >= 1; $s-- ) :
        $cnt = $summary['counts'][ $s ] ?? 0;
        if ( $cnt <= 0 ) continue; ?>
    <button class="twr-filter-btn" data-filter="<?php echo esc_attr( $s ); ?>">
        <span class="twr-filter-star">&#9733;</span>
        <?php echo esc_html( $s ); ?>
        <span class="twr-filter-count">(<?php echo esc_html( $cnt ); ?>)</span>
    </button>
    <?php endfor; ?>

    <?php if ( $filter_counts['verified'] > 0 ) : ?>
    <button class="twr-filter-btn" data-filter="verified">
        <?php esc_html_e( 'Đã mua hàng', 'toanweb-product-reviews' ); ?>
        <span class="twr-filter-count">(<?php echo esc_html( $filter_counts['verified'] ); ?>)</span>
    </button>
    <?php endif; ?>

<?php else : ?>

    <button class="twr-filter-btn twr-filter-btn--active" data-filter="all">
        <?php esc_html_e( 'Tất cả', 'toanweb-product-reviews' ); ?>
    </button>
    <button class="twr-filter-btn" data-filter="photos">
        <?php esc_html_e( 'Có hình ảnh', 'toanweb-product-reviews' ); ?>
    </button>
    <button class="twr-filter-btn" data-filter="verified">
        <?php esc_html_e( 'Đã mua hàng', 'toanweb-product-reviews' ); ?>
    </button>
    <?php for ( $s = 5; $s >= 1; $s-- ) : ?>
    <button class="twr-filter-btn" data-filter="<?php echo esc_attr( $s ); ?>">
        <?php echo esc_html( $s ); ?> <?php esc_html_e( 'sao', 'toanweb-product-reviews' ); ?>
    </button>
    <?php endfor; ?>

<?php endif; ?>

</div>
<?php endif; ?>

<div class="twr-list<?php
    if ( $settings['layout'] === 'grid' ) echo ' twr-list--grid';
    elseif ( $settings['layout'] === 'masonry' ) echo ' twr-list--masonry';
?>"<?php if ( $settings['layout'] === 'masonry' ) : $cols = max( 2, min( 4, (int) ( $settings['masonry_columns'] ?? 2 ) ) ); ?>
     style="--twr-masonry-cols:<?php echo esc_attr( $cols ); ?>"<?php endif; ?>
     id="twr-review-list">
    <?php foreach ( $reviews as $review ) :
        include __DIR__ . '/review-item.php';
    endforeach; ?>

    <?php if ( empty( $reviews ) ) : ?>
    <p class="twr-empty"><?php esc_html_e( 'Chưa có đánh giá nào. Hãy là người đầu tiên!', 'toanweb-product-reviews' ); ?></p>
    <?php endif; ?>
</div>

<?php if ( ( $settings['load_more_style'] ?? 'loadmore' ) === 'pagination' ) : ?>
<div class="twr-pagination" id="twr-pagination"
     data-total="<?php echo esc_attr( $total ); ?>"
     data-per-page="<?php echo esc_attr( $settings['reviews_per_page'] ); ?>">
</div>
<?php elseif ( $total > count( $reviews ) ) : ?>
<div class="twr-load-more-wrap">
    <button class="twr-btn-load-more" id="twr-load-more"
            data-offset="<?php echo esc_attr( count( $reviews ) ); ?>"
            data-filter="all">
        <?php esc_html_e( 'Xem thêm đánh giá', 'toanweb-product-reviews' ); ?>
    </button>
</div>
<?php endif; ?>

</div><!-- /.twr-wrap -->
