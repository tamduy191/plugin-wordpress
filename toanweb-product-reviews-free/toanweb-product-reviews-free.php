<?php
/**
 * Plugin Name: ToanWeb Product Reviews
 * Plugin URI:  https://doxuantoan.com/toanweb-product-reviews
 * Description: Modern WooCommerce product reviews with photo/video upload, criteria ratings, coupon rewards.
 * Version:     1.0.2
 * Author:      ToanWeb
 * Author URI:  https://doxuantoan.com
 * Text Domain: toanweb-product-reviews
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 */


defined( 'ABSPATH' ) || exit;

define( 'TWR_VERSION', '1.0.0' );
define( 'TWR_FILE',    __FILE__ );
define( 'TWR_DIR',     plugin_dir_path( __FILE__ ) );
define( 'TWR_URL',     plugin_dir_url( __FILE__ ) );
define( 'TWR_SLUG',    'toanweb-product-reviews' );

final class ToanWeb_Product_Reviews {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    private function includes(): void {
        // Models
        require_once TWR_DIR . 'app/Models/SettingsModel.php';
        require_once TWR_DIR . 'app/Models/ReviewModel.php';
        require_once TWR_DIR . 'app/Models/MediaModel.php';

        // Controllers
        require_once TWR_DIR . 'app/Controllers/ReviewController.php';
        require_once TWR_DIR . 'app/Controllers/FormController.php';
        require_once TWR_DIR . 'app/Controllers/AdminController.php';
    }

    private function init_hooks(): void {
        add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
        add_action( 'plugins_loaded', [ $this, 'check_woocommerce' ] );
        add_action( 'init',           [ $this, 'boot_controllers' ] );
        register_activation_hook( TWR_FILE, [ $this, 'activate' ] );
    }

    public function load_textdomain(): void {
        load_plugin_textdomain( TWR_SLUG, false, dirname( plugin_basename( TWR_FILE ) ) . '/languages' );
    }

    public function check_woocommerce(): void {
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', function () {
                echo '<div class="notice notice-error"><p>'
                    . esc_html__( 'ToanWeb Product Reviews requires WooCommerce to be installed and active.', 'toanweb-product-reviews' )
                    . '</p></div>';
            } );
        }
    }

    public function boot_controllers(): void {
        TWR_ReviewController::init();
        TWR_FormController::init();
        TWR_AdminController::init();
    }

    public function activate(): void {
        TWR_SettingsModel::install_defaults();
    }
}

ToanWeb_Product_Reviews::instance();
