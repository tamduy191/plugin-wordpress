<?php defined( 'ABSPATH' ) || exit;
$_star_svg = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
?>

<div class="twr-item" id="twr-review-<?php echo esc_attr( $review['id'] ); ?>" data-id="<?php echo esc_attr( $review['id'] ); ?>">
    <div class="twr-item__header">
        <img class="twr-avatar" src="<?php echo esc_url( $review['avatar'] ); ?>" alt="<?php echo esc_attr( $review['author'] ); ?>" width="48" height="48" loading="lazy">
        <div class="twr-item__meta">
            <span class="twr-item__author"><?php echo esc_html( $review['author'] ); ?></span>
            <?php if ( $review['verified'] && TWR_SettingsModel::get( 'show_verified' ) ) : ?>
            <span class="twr-badge-verified">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <?php echo esc_html( TWR_SettingsModel::get( 'verified_text' ) ); ?>
            </span>
            <?php endif; ?>
            <div class="twr-stars twr-stars--sm">
                <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                    <span class="twr-star<?php echo $i <= $review['rating'] ? ' twr-star--filled' : ''; ?>"><?php echo $_star_svg; ?></span>
                <?php endfor; ?>
                <span class="twr-rating-label">
                    <?php
                    $labels = [ 1 => 'Rất tệ', 2 => 'Tệ', 3 => 'Bình thường', 4 => 'Tốt', 5 => 'Tuyệt vời' ];
                    echo esc_html( $labels[ $review['rating'] ] ?? '' );
                    ?>
                </span>
            </div>
        </div>
    </div>


    <?php if ( $review['content'] ) : ?>
    <div class="twr-item__content"><?php echo wp_kses_post( $review['content'] ); ?></div>
    <?php endif; ?>

    <?php if ( ! empty( $review['photos'] ) ) :
        $review_meta = wp_json_encode( [
            'author'        => $review['author'],
            'avatar'        => $review['avatar'],
            'rating'        => $review['rating'],
            'content'       => $review['content'],
            'verified'      => $review['verified'],
            'verified_text' => TWR_SettingsModel::get( 'show_verified' ) ? TWR_SettingsModel::get( 'verified_text' ) : '',
            'date'          => $review['date'],
        ] );
    ?>
    <div class="twr-item__photos">
        <?php foreach ( $review['photos'] as $idx => $photo ) : ?>
        <button class="twr-photo-thumb"
                data-gallery='<?php echo esc_attr( wp_json_encode( $review['photos'] ) ); ?>'
                data-index="<?php echo esc_attr( $idx ); ?>"
                data-review='<?php echo esc_attr( $review_meta ); ?>'>
            <img src="<?php echo esc_url( $photo ); ?>" alt="" loading="lazy">
        </button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ( ! empty( $review['videos'] ) ) : ?>
    <div class="twr-item__videos">
        <?php foreach ( $review['videos'] as $video ) : ?>
        <video class="twr-video-thumb" src="<?php echo esc_url( $video ); ?>" controls preload="none"></video>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="twr-item__footer">
        <span class="twr-item__date">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <?php echo esc_html( sprintf( __( '%s', 'toanweb-product-reviews' ), $review['date'] ) ); ?>
        </span>
        <button class="twr-helpful-btn" data-id="<?php echo esc_attr( $review['id'] ); ?>">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"/><path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
            <?php esc_html_e( 'Hữu ích', 'toanweb-product-reviews' ); ?>
            <span class="twr-helpful-count">(<?php echo esc_html( $review['helpful'] ); ?>)</span>
        </button>
    </div>
</div>
