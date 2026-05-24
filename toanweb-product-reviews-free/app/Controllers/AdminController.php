<?php
defined( 'ABSPATH' ) || exit;

class TWR_AdminController {

    private static array $pro_hooks = [];

    public static function init(): void {
        add_action( 'rest_api_init', [ self::class, 'register_rest_routes' ] );

        if ( ! is_admin() ) return;

        add_action( 'admin_menu',            [ self::class, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_assets' ] );
    }

    public static function register_menu(): void {
        $icon = 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>'
        );

        add_menu_page(
            __( 'ToanWeb Reviews', 'toanweb-product-reviews' ),
            __( 'ToanWeb Reviews', 'toanweb-product-reviews' ),
            'manage_woocommerce',
            'twr-reviews',
            [ self::class, 'render_settings_page' ],
            $icon,
            57
        );

        // Rename auto-generated first submenu
        add_submenu_page( 'twr-reviews', __( 'Cài đặt', 'toanweb-product-reviews' ), __( 'Cài đặt', 'toanweb-product-reviews' ), 'manage_woocommerce', 'twr-reviews', [ self::class, 'render_settings_page' ] );

        // PRO-only sub-pages — visible but locked
        self::$pro_hooks['add-review'] = add_submenu_page( 'twr-reviews', __( 'Thêm đánh giá', 'toanweb-product-reviews' ), __( 'Thêm đánh giá ✦', 'toanweb-product-reviews' ), 'manage_woocommerce', 'twr-add-review', [ self::class, 'render_add_review_page' ] );
        self::$pro_hooks['import']     = add_submenu_page( 'twr-reviews', __( 'Nhập đánh giá',  'toanweb-product-reviews' ), __( 'Nhập đánh giá ✦',  'toanweb-product-reviews' ), 'manage_woocommerce', 'twr-import',     [ self::class, 'render_import_page'     ] );
        self::$pro_hooks['export']     = add_submenu_page( 'twr-reviews', __( 'Xuất đánh giá',  'toanweb-product-reviews' ), __( 'Xuất đánh giá ✦',  'toanweb-product-reviews' ), 'manage_woocommerce', 'twr-export',     [ self::class, 'render_export_page'     ] );
        self::$pro_hooks['license']    = add_submenu_page( 'twr-reviews', __( 'Nâng cấp PRO',   'toanweb-product-reviews' ), __( '⭐ Nâng cấp PRO',  'toanweb-product-reviews' ), 'manage_woocommerce', 'twr-license',    [ self::class, 'render_license_page'    ] );
    }

    public static function render_settings_page(): void {
        include TWR_DIR . 'app/Views/admin/settings/page.php';
    }

    public static function render_add_review_page(): void {
        include TWR_DIR . 'app/Views/admin/add-review/page.php';
    }

    public static function render_import_page(): void {
        include TWR_DIR . 'app/Views/admin/import/page.php';
    }

    public static function render_export_page(): void {
        include TWR_DIR . 'app/Views/admin/export/page.php';
    }

    public static function render_license_page(): void {
        include TWR_DIR . 'app/Views/admin/license/page.php';
    }

    public static function enqueue_assets( string $hook ): void {
        $all_hooks = array_merge(
            [ 'toplevel_page_twr-reviews' ],
            array_filter( array_values( self::$pro_hooks ) )
        );

        if ( ! in_array( $hook, $all_hooks, true ) ) return;

        // CSS needed on all TWR pages (settings + pro-upgrade pages)
        wp_enqueue_style( 'twr-admin', TWR_URL . 'assets/css/admin.css', [], TWR_VERSION );

        // Vue + settings JS only on the main settings page
        if ( $hook === 'toplevel_page_twr-reviews' ) {
            wp_enqueue_script( 'vue3', 'https://unpkg.com/vue@3/dist/vue.global.prod.js', [], '3.4.0', true );
            wp_enqueue_script( 'twr-admin', TWR_URL . 'assets/js/admin.js', [ 'vue3' ], TWR_VERSION, true );
            wp_localize_script( 'twr-admin', 'TWR_ADMIN', [
                'rest_url' => esc_url_raw( rest_url( 'twr/v1/' ) ),
                'nonce'    => wp_create_nonce( 'wp_rest' ),
                'settings' => TWR_SettingsModel::get(),
            ] );
        }
    }

    public static function register_rest_routes(): void {
        register_rest_route( 'twr/v1', '/settings', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ self::class, 'rest_get_settings' ],
                'permission_callback' => [ self::class, 'rest_permission' ],
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ self::class, 'rest_save_settings' ],
                'permission_callback' => [ self::class, 'rest_permission' ],
            ],
        ] );
    }

    public static function rest_permission(): bool {
        return current_user_can( 'manage_woocommerce' );
    }

    public static function rest_get_settings(): WP_REST_Response {
        return new WP_REST_Response( TWR_SettingsModel::get(), 200 );
    }

    public static function rest_save_settings( WP_REST_Request $request ): WP_REST_Response {
        $data = $request->get_json_params();
        if ( empty( $data ) ) {
            return new WP_REST_Response( [ 'message' => 'No data' ], 400 );
        }

        $saved = TWR_SettingsModel::update( $data );
        if ( ! $saved ) {
            return new WP_REST_Response( [ 'message' => 'Failed to save' ], 500 );
        }

        return new WP_REST_Response( TWR_SettingsModel::get(), 200 );
    }
}
