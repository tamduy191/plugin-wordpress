<?php
defined( 'ABSPATH' ) || exit;

class TWR_MediaModel {

    private static array $allowed_image_types = [ 'image/jpeg', 'image/png', 'image/gif', 'image/webp' ];
    private static array $allowed_video_types = [ 'video/mp4', 'video/quicktime', 'video/webm' ];

    public static function handle_uploads( array $files ): array {
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $max_bytes = (int) TWR_SettingsModel::get( 'max_file_size' ) * 1024;
        $max_count = (int) TWR_SettingsModel::get( 'max_file_count' );

        $photos = [];
        $videos = [];

        if ( empty( $files['twr_media']['name'][0] ) ) {
            return compact( 'photos', 'videos' );
        }

        $file_count = min( count( $files['twr_media']['name'] ), $max_count );

        // Redirect uploads to custom folder during this batch
        add_filter( 'upload_dir', [ self::class, 'custom_upload_dir' ] );

        for ( $i = 0; $i < $file_count; $i++ ) {
            if ( $files['twr_media']['error'][ $i ] !== UPLOAD_ERR_OK ) continue;
            if ( $files['twr_media']['size'][ $i ] > $max_bytes ) continue;

            $mime     = $files['twr_media']['type'][ $i ];
            $is_image = in_array( $mime, self::$allowed_image_types, true );
            $is_video = in_array( $mime, self::$allowed_video_types, true );

            $accept = TWR_SettingsModel::get( 'upload_accept' );
            if ( $accept === 'images' && ! $is_image ) continue;
            if ( $accept === 'videos' && ! $is_video ) continue;
            if ( ! $is_image && ! $is_video ) continue;

            $file = [
                'name'     => $files['twr_media']['name'][ $i ],
                'type'     => $mime,
                'tmp_name' => $files['twr_media']['tmp_name'][ $i ],
                'error'    => $files['twr_media']['error'][ $i ],
                'size'     => $files['twr_media']['size'][ $i ],
            ];

            $upload = wp_handle_upload( $file, [ 'test_form' => false ] );

            if ( isset( $upload['url'] ) ) {
                if ( $is_image ) {
                    $photos[] = $upload['url'];
                } else {
                    $videos[] = $upload['url'];
                }
            }
        }

        remove_filter( 'upload_dir', [ self::class, 'custom_upload_dir' ] );

        return compact( 'photos', 'videos' );
    }

    // Redirect wp_upload_dir() to the configured subfolder (flat, no year/month subdir)
    public static function custom_upload_dir( array $dirs ): array {
        $folder = TWR_SettingsModel::get( 'upload_folder' );
        $folder = trim( sanitize_text_field( $folder ), '/' );
        if ( $folder === '' ) $folder = 'reviews';

        $dirs['subdir'] = '/' . $folder;
        $dirs['path']   = $dirs['basedir'] . '/' . $folder;
        $dirs['url']    = $dirs['baseurl'] . '/' . $folder;

        return $dirs;
    }

    public static function save_to_comment( int $comment_id, array $photos, array $videos ): void {
        if ( ! empty( $photos ) ) {
            update_comment_meta( $comment_id, '_twr_photos', $photos );
        }
        if ( ! empty( $videos ) ) {
            update_comment_meta( $comment_id, '_twr_videos', $videos );
        }
    }
}
