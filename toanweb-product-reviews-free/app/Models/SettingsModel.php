<?php
defined( 'ABSPATH' ) || exit;

class TWR_SettingsModel {

    private static string $option_key = 'twr_settings';

    private static array $defaults = [
        'enabled'                => true,
        'primary_color'          => '#64b2fa',
        'show_verified'          => true,
        'verified_text'          => 'Đã mua tại cửa hàng',
        'reviews_per_page'       => 5,
        'load_more_style'        => 'loadmore',
        'layout'                 => 'list',
        'masonry_columns'        => 2,
        'show_filter_bar'        => true,
        'filter_bar_style'       => 'text',
        'lightbox'               => true,
        'bar_fill_color'         => '#64b2fa',
        'bar_track_color'        => '#e5e5ea',
        'summary_right_panel'    => 'photos',
        'upload_folder'          => 'reviews',
        'max_file_size'          => 5120,
        'max_file_count'         => 5,
        'upload_accept'          => 'both',
        'date_format'            => 'time_ago',
        'block_links_in_review'  => true,
        'max_comment_length'     => 0,
        'require_approval'       => false,
        'disable_tab'            => false,
    ];

    public static function install_defaults(): void {
        if ( false === get_option( self::$option_key ) ) {
            update_option( self::$option_key, self::$defaults, false );
        }
    }

    public static function get( string $key = '' ) {
        $settings = get_option( self::$option_key, self::$defaults );
        $settings = wp_parse_args( $settings, self::$defaults );

        if ( $key === '' ) {
            return $settings;
        }

        return $settings[ $key ] ?? ( self::$defaults[ $key ] ?? null );
    }

    public static function update( array $data ): bool {
        $current  = self::get();
        $allowed  = array_keys( self::$defaults );
        $filtered = [];

        foreach ( $allowed as $key ) {
            if ( array_key_exists( $key, $data ) ) {
                $filtered[ $key ] = $data[ $key ];
            } else {
                $filtered[ $key ] = $current[ $key ];
            }
        }

        return (bool) update_option( self::$option_key, $filtered, false );
    }

    public static function get_defaults(): array {
        return self::$defaults;
    }
}
