<?php
defined( 'ABSPATH' ) || exit;

class TWR_ReviewModel {

    public static function get_reviews( int $product_id, array $args = [] ): array {
        $defaults = [
            'number'  => TWR_SettingsModel::get( 'reviews_per_page' ),
            'offset'  => 0,
            'filter'  => 'all',
            'orderby' => 'comment_date_gmt',
            'order'   => 'DESC',
        ];
        $args = wp_parse_args( $args, $defaults );

        $query_args = [
            'post_id' => $product_id,
            'status'  => 'approve',
            'type'    => 'review',
            'number'  => (int) $args['number'],
            'offset'  => (int) $args['offset'],
            'orderby' => sanitize_key( $args['orderby'] ),
            'order'   => $args['order'] === 'ASC' ? 'ASC' : 'DESC',
        ];

        switch ( $args['filter'] ) {
            case 'photos':
                $query_args['meta_query'] = [
                    [ 'key' => '_twr_photos', 'compare' => 'EXISTS' ],
                ];
                break;
            case 'verified':
                $query_args['meta_query'] = [
                    [ 'key' => '_twr_verified', 'value' => '1' ],
                ];
                break;
            default:
                if ( is_numeric( $args['filter'] ) ) {
                    $rating = (int) $args['filter'];
                    if ( $rating >= 1 && $rating <= 5 ) {
                        $query_args['meta_query'] = [
                            [ 'key' => 'rating', 'value' => $rating, 'type' => 'NUMERIC' ],
                        ];
                    }
                }
        }

        $comments = get_comments( $query_args );
        return array_map( [ self::class, 'format_review' ], $comments );
    }

    public static function get_total( int $product_id, array $args = [] ): int {
        $filter = $args['filter'] ?? 'all';
        $q_args = [
            'post_id' => $product_id,
            'status'  => 'approve',
            'type'    => 'review',
            'count'   => true,
        ];

        switch ( $filter ) {
            case 'photos':
                $q_args['meta_query'] = [ [ 'key' => '_twr_photos', 'compare' => 'EXISTS' ] ];
                break;
            case 'verified':
                $q_args['meta_query'] = [ [ 'key' => '_twr_verified', 'value' => '1' ] ];
                break;
            default:
                if ( is_numeric( $filter ) ) {
                    $rating = (int) $filter;
                    if ( $rating >= 1 && $rating <= 5 ) {
                        $q_args['meta_query'] = [ [ 'key' => 'rating', 'value' => $rating, 'type' => 'NUMERIC' ] ];
                    }
                }
        }

        return (int) get_comments( $q_args );
    }

    public static function get_summary( int $product_id ): array {
        $counts = [];
        for ( $i = 1; $i <= 5; $i++ ) {
            $counts[ $i ] = (int) get_comments( [
                'post_id'    => $product_id,
                'status'     => 'approve',
                'type'       => 'review',
                'count'      => true,
                'meta_query' => [ [ 'key' => 'rating', 'value' => $i, 'type' => 'NUMERIC' ] ],
            ] );
        }

        $total = array_sum( $counts );
        $avg   = $total > 0
            ? array_sum( array_map( fn( $r, $c ) => $r * $c, array_keys( $counts ), $counts ) ) / $total
            : 0;

        return [
            'average' => round( $avg, 1 ),
            'total'   => $total,
            'counts'  => $counts,
        ];
    }

    public static function get_recent_photos( int $product_id, int $limit = 20 ): array {
        $comments = get_comments( [
            'post_id'    => $product_id,
            'status'     => 'approve',
            'type'       => 'review',
            'number'     => 80,
            'meta_query' => [
                [ 'key' => '_twr_photos', 'compare' => 'EXISTS' ],
            ],
        ] );

        $pool = [];
        foreach ( $comments as $comment ) {
            $urls     = get_comment_meta( $comment->comment_ID, '_twr_photos', true );
            $rating   = (int) get_comment_meta( $comment->comment_ID, 'rating', true );
            $verified = (bool) get_comment_meta( $comment->comment_ID, '_twr_verified', true );

            if ( ! is_array( $urls ) || empty( $urls ) ) continue;

            $score   = ( $verified ? 3 : 0 )
                     + ( $rating >= 5 ? 2 : ( $rating >= 4 ? 1 : 0 ) )
                     + ( mt_rand( 0, 99 ) / 100 );

            $author  = esc_html( $comment->comment_author );
            $avatar  = get_avatar_url( $comment->comment_author_email, [ 'size' => 48 ] );
            $content = wp_kses_post( $comment->comment_content );
            $date    = self::format_date( $comment->comment_date_gmt );

            foreach ( $urls as $url ) {
                $pool[] = [
                    'url'        => $url,
                    'comment_id' => (int) $comment->comment_ID,
                    'rating'     => $rating,
                    'verified'   => $verified,
                    'author'     => $author,
                    'avatar'     => $avatar,
                    'content'    => $content,
                    'date'       => $date,
                    '_score'     => $score,
                ];
            }
        }

        usort( $pool, fn( $a, $b ) => $b['_score'] <=> $a['_score'] );

        return array_map(
            static function ( $p ) { unset( $p['_score'] ); return $p; },
            array_slice( $pool, 0, $limit )
        );
    }

    public static function format_review( WP_Comment $comment ): array {
        $rating   = (int) get_comment_meta( $comment->comment_ID, 'rating', true );
        $photos   = get_comment_meta( $comment->comment_ID, '_twr_photos', true );
        $videos   = get_comment_meta( $comment->comment_ID, '_twr_videos', true );
        $helpful  = (int) get_comment_meta( $comment->comment_ID, '_twr_helpful', true );
        $verified = (bool) get_comment_meta( $comment->comment_ID, '_twr_verified', true );

        return [
            'id'       => (int) $comment->comment_ID,
            'author'   => esc_html( $comment->comment_author ),
            'avatar'   => get_avatar_url( $comment->comment_author_email, [ 'size' => 48 ] ),
            'rating'   => $rating,
            'content'  => wp_kses_post( $comment->comment_content ),
            'date'     => self::format_date( $comment->comment_date_gmt ),
            'date_raw' => $comment->comment_date_gmt,
            'photos'   => is_array( $photos ) ? $photos : [],
            'videos'   => is_array( $videos ) ? $videos : [],
            'helpful'  => $helpful,
            'verified' => $verified,
        ];
    }

    public static function increment_helpful( int $comment_id ): int {
        $current = (int) get_comment_meta( $comment_id, '_twr_helpful', true );
        $new     = $current + 1;
        update_comment_meta( $comment_id, '_twr_helpful', $new );
        return $new;
    }

    public static function format_date( string $date_gmt ): string {
        if ( TWR_SettingsModel::get( 'date_format' ) === 'date' ) {
            return date_i18n( 'd/m/Y', strtotime( $date_gmt ) );
        }
        return human_time_diff( strtotime( $date_gmt ), time() ) . ' trước';
    }
}
