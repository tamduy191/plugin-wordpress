<?php
defined( 'ABSPATH' ) || exit;

class TWR_FormController {

    public static function init(): void {
        add_action( 'wp_ajax_twr_submit_review',        [ self::class, 'ajax_submit' ] );
        add_action( 'wp_ajax_nopriv_twr_submit_review', [ self::class, 'ajax_submit' ] );
    }

    public static function ajax_submit(): void {
        // Rate limit: max 5 submissions per IP per hour
        // Honeypot: bots fill this field, humans leave it empty
        if ( ! empty( $_POST['twr_website'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Spam detected.', 'toanweb-product-reviews' ) ] );
        }

        $ip       = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );
        $rate_key = 'twr_rl_' . md5( $ip );
        $hits     = (int) get_transient( $rate_key );
        if ( $hits >= 5 ) {
            wp_send_json_error( [ 'message' => __( 'Bạn gửi quá nhiều đánh giá. Vui lòng thử lại sau.', 'toanweb-product-reviews' ) ] );
        }
        set_transient( $rate_key, $hits + 1, HOUR_IN_SECONDS );

        $product_id = (int) ( $_POST['product_id'] ?? 0 );
        $rating     = (int) ( $_POST['rating'] ?? 0 );
        $comment    = sanitize_textarea_field( $_POST['comment'] ?? '' );

        // Block links if setting is enabled
        if ( TWR_SettingsModel::get( 'block_links_in_review' ) && preg_match( '/https?:\/\//i', $comment ) ) {
            wp_send_json_error( [ 'message' => __( 'Nội dung đánh giá không được chứa link.', 'toanweb-product-reviews' ) ] );
        }

        // Max comment length
        $max_len = (int) TWR_SettingsModel::get( 'max_comment_length' );
        if ( $max_len > 0 && mb_strlen( $comment ) > $max_len ) {
            wp_send_json_error( [ 'message' => sprintf( __( 'Nội dung đánh giá tối đa %d ký tự.', 'toanweb-product-reviews' ), $max_len ) ] );
        }

        // Validate product
        if ( ! $product_id || get_post_type( $product_id ) !== 'product' ) {
            wp_send_json_error( [ 'message' => __( 'Sản phẩm không hợp lệ.', 'toanweb-product-reviews' ) ] );
        }

        // Validate rating
        if ( $rating < 1 || $rating > 5 ) {
            wp_send_json_error( [ 'field' => 'rating', 'message' => __( 'Vui lòng chọn số sao đánh giá.', 'toanweb-product-reviews' ) ] );
        }

        // Author & email
        if ( is_user_logged_in() ) {
            $user    = wp_get_current_user();
            $author  = $user->display_name;
            $email   = $user->user_email;
            $user_id = $user->ID;
        } else {
            $author  = sanitize_text_field( $_POST['author'] ?? '' );
            $email   = sanitize_email( $_POST['email'] ?? '' );
            $user_id = 0;

            if ( $author === '' ) {
                wp_send_json_error( [ 'field' => 'author', 'message' => __( 'Vui lòng nhập tên của bạn.', 'toanweb-product-reviews' ) ] );
            }
            if ( ! is_email( $email ) ) {
                wp_send_json_error( [ 'field' => 'email', 'message' => __( 'Email không hợp lệ.', 'toanweb-product-reviews' ) ] );
            }
        }

        // Check for duplicate review from same user/email (admins are exempt)
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            $existing = get_comments( [
                'post_id'      => $product_id,
                'author_email' => $email,
                'type'         => 'review',
                'status'       => 'any',
                'count'        => true,
            ] );
            if ( $existing > 0 ) {
                wp_send_json_error( [ 'message' => __( 'Bạn đã đánh giá sản phẩm này rồi.', 'toanweb-product-reviews' ) ] );
            }
        }

        // Determine approval status:
        // hold if plugin setting OR WordPress Discussion "must manually approve" is on
        $needs_approval = TWR_SettingsModel::get( 'require_approval' ) || (bool) get_option( 'comment_moderation' );
        $approved       = $needs_approval ? 0 : 1;

        // Insert comment
        $comment_id = wp_insert_comment( [
            'comment_post_ID'      => $product_id,
            'comment_author'       => $author,
            'comment_author_email' => $email,
            'comment_content'      => $comment,
            'comment_type'         => 'review',
            'comment_parent'       => 0,
            'user_id'              => $user_id,
            'comment_approved'     => $approved,
        ] );

        if ( ! $comment_id ) {
            wp_send_json_error( [ 'message' => __( 'Không thể gửi đánh giá. Vui lòng thử lại.', 'toanweb-product-reviews' ) ] );
        }

        // Rating meta (WooCommerce reads this key)
        update_comment_meta( $comment_id, 'rating', $rating );

        // Verified purchase check
        $verified = false;
        if ( $user_id ) {
            $orders = wc_get_orders( [
                'customer' => $user_id,
                'status'   => [ 'wc-completed', 'wc-processing' ],
                'limit'    => -1,
            ] );
            foreach ( $orders as $order ) {
                foreach ( $order->get_items() as $item ) {
                    if ( (int) $item->get_product_id() === $product_id ) {
                        update_comment_meta( $comment_id, '_twr_verified', 1 );
                        $verified = true;
                        break 2;
                    }
                }
            }
        }

        // Media uploads
        $photos = [];
        $videos = [];
        if ( ! empty( $_FILES['twr_media']['name'][0] ) ) {
            $media  = TWR_MediaModel::handle_uploads( $_FILES );
            $photos = $media['photos'];
            $videos = $media['videos'];
            TWR_MediaModel::save_to_comment( $comment_id, $photos, $videos );
        }

        // Recalculate product rating cache
        $product = wc_get_product( $product_id );
        if ( $product ) {
            $product->set_rating_counts( null );
            $product->set_average_rating( null );
            $product->save();
        }

        do_action( 'twr_after_review_saved', $comment_id, [
            'rating' => $rating,
            'photos' => $photos,
        ], [
            'comment_author'       => $author,
            'comment_author_email' => $email,
            'comment_post_ID'      => $product_id,
        ] );

        $message = $needs_approval
            ? __( 'Cảm ơn bạn! Đánh giá của bạn đang chờ phê duyệt.', 'toanweb-product-reviews' )
            : __( 'Cảm ơn bạn đã đánh giá sản phẩm!', 'toanweb-product-reviews' );

        wp_send_json_success( [
            'message'  => $message,
            'verified' => $verified,
        ] );
    }
}
