<?php defined( 'ABSPATH' ) || exit;
$_star_svg = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
?>

<div class="twr-wrap" id="twr-reviews" data-product="<?php echo esc_attr( $product_id ); ?>">

<div class="twr-summary">
    <div class="twr-summary__score">
        <span class="twr-score-big"><?php echo esc_html( number_format( $summary['average'], 1 ) ); ?></span>
        <span class="twr-score-max">/5</span>
        <div class="twr-stars twr-stars--lg">
            <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                <span class="twr-star<?php echo $i <= round( $summary['average'] ) ? ' twr-star--filled' : ''; ?>"><?php echo $_star_svg; ?></span>
            <?php endfor; ?>
        </div>
        <span class="twr-summary__count"><?php echo esc_html( sprintf( _n( '%d đánh giá', '%d đánh giá', $summary['total'], 'toanweb-product-reviews' ), $summary['total'] ) ); ?></span>
        <button class="twr-btn-write" id="twr-open-form">
            <?php esc_html_e( 'Viết đánh giá', 'toanweb-product-reviews' ); ?>
        </button>
    </div>

    <div class="twr-summary__bars">
        <?php for ( $i = 5; $i >= 1; $i-- ) :
            $count   = $summary['counts'][ $i ] ?? 0;
            $percent = $summary['total'] > 0 ? round( $count / $summary['total'] * 100 ) : 0;
        ?>
        <div class="twr-bar-row">
            <span class="twr-bar-label"><?php echo esc_html( $i ); ?> <svg width="12" height="12" viewBox="0 0 24 24" aria-hidden="true" fill="var(--twr-star)" stroke="var(--twr-star)" stroke-width="1" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></span>
            <div class="twr-bar-track">
                <div class="twr-bar-fill" style="width:<?php echo esc_attr( $percent ); ?>%"></div>
            </div>
            <span class="twr-bar-count"><?php echo esc_html( $count ); ?></span>
        </div>
        <?php endfor; ?>
    </div>

</div>
