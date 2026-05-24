<?php
defined( 'ABSPATH' ) || exit;

class TWR_ReviewController {

    public static function init(): void {
        if ( ! TWR_SettingsModel::get( 'disable_tab' ) ) {
            add_filter( 'woocommerce_product_tabs', [ self::class, 'override_review_tab' ], 98 );
        }

        add_action( 'wp_enqueue_scripts', [ self::class, 'enqueue_assets' ] );
        add_shortcode( 'twr_reviews', [ self::class, 'shortcode' ] );

        add_action( 'wp_ajax_twr_load_reviews',        [ self::class, 'ajax_load_reviews' ] );
        add_action( 'wp_ajax_nopriv_twr_load_reviews', [ self::class, 'ajax_load_reviews' ] );
        add_action( 'wp_ajax_twr_helpful',        [ self::class, 'ajax_helpful' ] );
        add_action( 'wp_ajax_nopriv_twr_helpful', [ self::class, 'ajax_helpful' ] );
    }

    public static function override_review_tab( array $tabs ): array {
        if ( ! TWR_SettingsModel::get( 'enabled' ) ) {
            return $tabs;
        }
        if ( isset( $tabs['reviews'] ) ) {
            $tabs['reviews']['callback'] = [ self::class, 'render_reviews_tab' ];
        }
        return $tabs;
    }

    public static function render_reviews_tab(): void {
        global $product;
        if ( ! $product ) return;
        self::render( $product->get_id() );
    }

    /**
     * [twr_reviews] — hiển thị block reviews ở bất kỳ đâu.
     * Attr: id (product_id). Nếu bỏ trống, dùng ID trang hiện tại.
     */
    public static function shortcode( array $atts ): string {
        $settings = TWR_SettingsModel::get();
        if ( ! $settings['enabled'] ) return '';

        $atts       = shortcode_atts( [ 'id' => 0 ], $atts, 'twr_reviews' );
        $product_id = (int) $atts['id'] ?: get_the_ID();

        if ( ! $product_id || get_post_type( $product_id ) !== 'product' ) return '';

        // Localize JS data cho product_id này nếu chưa được localize qua enqueue_assets
        if ( ! is_product() ) {
            wp_add_inline_script(
                'twr-frontend',
                'var TWR = ' . wp_json_encode( self::get_localize_data( $product_id, $settings ) ) . ';',
                'before'
            );
        }

        ob_start();
        self::render( $product_id );
        return ob_get_clean();
    }

    public static function render( int $product_id ): void {
        $settings            = TWR_SettingsModel::get();
        $summary       = TWR_ReviewModel::get_summary( $product_id );
        $reviews       = TWR_ReviewModel::get_reviews( $product_id );
        $total         = TWR_ReviewModel::get_total( $product_id );
        $filter_counts = [
            'photos'   => TWR_ReviewModel::get_total( $product_id, [ 'filter' => 'photos' ] ),
            'verified' => TWR_ReviewModel::get_total( $product_id, [ 'filter' => 'verified' ] ),
        ];

        include TWR_DIR . 'app/Views/frontend/review-summary.php';
        include TWR_DIR . 'app/Views/frontend/review-list.php';
        include TWR_DIR . 'app/Views/frontend/review-form.php';
        include TWR_DIR . 'app/Views/frontend/lightbox.php';
    }

    private static function get_localize_data( int $product_id, array $settings ): array {
        return [
            'ajax_url'           => admin_url( 'admin-ajax.php' ),
            'per_page'           => (int) $settings['reviews_per_page'],
            'masonry_columns'    => (int) ( $settings['masonry_columns'] ?? 2 ),
            'lightbox'           => (bool) $settings['lightbox'],
            'primary'            => esc_attr( $settings['primary_color'] ),
            'bar_fill'           => esc_attr( $settings['bar_fill_color'] ?? '#64b2fa' ),
            'bar_track'          => esc_attr( $settings['bar_track_color'] ?? '#e5e5ea' ),
            'product_id'         => $product_id,
            'max_file_count'     => (int) $settings['max_file_count'],
            'max_file_size'      => (int) $settings['max_file_size'],
            'upload_accept'      => $settings['upload_accept'] ?? 'both',
            'max_comment_length' => (int) ( $settings['max_comment_length'] ?? 0 ),
            'show_verified'      => (bool) $settings['show_verified'],
            'verified_text'      => esc_attr( $settings['verified_text'] ?? '' ),
            'i18n'               => [
                'helpful'   => __( 'Hữu ích', 'toanweb-product-reviews' ),
                'load_more' => __( 'Xem thêm đánh giá', 'toanweb-product-reviews' ),
                'no_more'   => __( 'Đã hiển thị tất cả đánh giá', 'toanweb-product-reviews' ),
            ],
        ];
    }

    public static function enqueue_assets(): void {
        $settings = TWR_SettingsModel::get();
        if ( ! $settings['enabled'] ) return;

        $is_product   = is_product();
        $has_shortcode = false;

        if ( ! $is_product ) {
            global $post;
            $has_shortcode = $post && has_shortcode( $post->post_content, 'twr_reviews' );
        }

        if ( ! $is_product && ! $has_shortcode ) return;

        wp_enqueue_style( 'twr-frontend', TWR_URL . 'assets/css/frontend.css', [], TWR_VERSION );
        wp_enqueue_script( 'twr-frontend', TWR_URL . 'assets/js/frontend.js', [], TWR_VERSION, true );

        // Trên product page: localize ngay với product_id hiện tại.
        // Trên shortcode page: shortcode() sẽ inject TWR qua wp_add_inline_script.
        if ( $is_product ) {
            wp_localize_script( 'twr-frontend', 'TWR', self::get_localize_data( get_the_ID(), $settings ) );
        }
    }

    public static function ajax_load_reviews(): void {
        $product_id = (int) ( $_POST['product_id'] ?? 0 );
        $offset     = (int) ( $_POST['offset'] ?? 0 );
        $filter     = sanitize_key( $_POST['filter'] ?? 'all' );

        if ( ! $product_id ) {
            wp_send_json_error( 'Invalid product' );
        }

        $reviews = TWR_ReviewModel::get_reviews( $product_id, [
            'offset' => $offset,
            'filter' => $filter,
        ] );

        $total = TWR_ReviewModel::get_total( $product_id, [ 'filter' => $filter ] );

        $html = '';
        foreach ( $reviews as $review ) {
            ob_start();
            include TWR_DIR . 'app/Views/frontend/review-item.php';
            $html .= ob_get_clean();
        }

        wp_send_json_success( [
            'html'  => $html,
            'total' => $total,
        ] );
    }

    public static function ajax_helpful(): void {
        $comment_id = (int) ( $_POST['comment_id'] ?? 0 );
        if ( ! $comment_id ) {
            wp_send_json_error();
        }

        $new_count = TWR_ReviewModel::increment_helpful( $comment_id );
        wp_send_json_success( [ 'count' => $new_count ] );
    }
}
