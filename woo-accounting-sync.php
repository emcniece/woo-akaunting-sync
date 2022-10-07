<?php
/*
Plugin Name: WooCommerce Akaunting Sync
Description: Send WooCommerce orders to Akaunting
Version: 0.0.1
Author: Eric McNiece
Author URI: https://github.com/emcniece
Developer: EMC2 Innovation
Developer URI: https://github.com/emcniece/woo-akaunting-sync
Text Domain: wasync
*/

defined( 'ABSPATH' ) or die( 'No direct execution' );

if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}

class WASync{
  public function __construct(){
    $this->constants();
    $this->init();
    require_once(WASYNC_PATH.'requester.php');
    $this->ping();
  }

  public function init(){
    add_action('admin_menu', array($this, 'menu'));
    add_action('admin_init', array($this, 'register_options'));

    add_action('save_post_product', array($this, 'woo_product'));
    add_action('user_register', array($this, 'woo_customer'));
    add_action('woocommerce_checkout_order_processed', array($this, 'woo_order'));
    add_action('woocommerce_update_order', array($this, 'woo_order'));
  }

  public function constants(){
    if (!defined('WASYNC_PATH')) {
      define('WASYNC_PATH', plugin_dir_path(__FILE__));
    }
  }

  public function menu(){
    add_options_page('WooCommerce Akaunting Sync Settings', 'WASync', 'manage_options', 'wasync', array($this, 'display_options'));
  }

  public function register_options(){
    register_setting('wasync-options', 'wasync_url');
    register_setting('wasync-options', 'wasync_user');
    register_setting('wasync-options', 'wasync_password');
  }

  public function display_options(){
    if (!current_user_can('manage_options'))  {
      wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    write_log('GET:');
    write_log($_GET);

    write_log('POST:');
    write_log($_POST);


    // check if the user have submitted the settings
    // wordpress will add the "settings-updated" $_GET parameter to the url
    if (isset($_GET['settings-updated'])) {
      settings_errors('wasync-options');
    } elseif (isset($_GET['wasync']) && ($_GET['wasync'] == 'send-success')) {
      do_action('send_success');
    }

    $pingresp = $this->ping();

    require_once(WASYNC_PATH . 'options.php');
  }

  public function woo_product($post_id, $post = null, $update = null){
    if ($update) {
      return;
    }

    // Akaunting helper
    $helper = $this->check_options();

    if (empty($helper)) {
      return;
    }

    $product = wc_get_product($post_id);

    $data = $product->get_data();

    if ($data['status'] != 'publish') {
      return;
    }

    if ($product->get_type() == 'variable') {
      $data['variations'] = $product->get_available_variations();
    }

    $helper->storeProduct($data);
  }

  public function woo_customer($user_id){
        // Akaunting helper
        $helper = $this->check_options();

        if (empty($helper)) {
            return;
        }

        $user = get_user_by('id', $user_id);

        $address = $this->getCustomerAddress($user);

        $customer = array(
            'meta' => get_user_meta($user->ID),
            'email' => $user->user_email,
            'address' => $address,
        );

        $helper->storeCustomer($customer);
    }

    public function woo_order($order_id){
        $helper = $this->check_options();

        if (empty($helper)) {
            return;
        }

        $order = wc_get_order($order_id);

        $paid_statuses = ['processing', 'completed'];

        if (!in_array(strtolower($order->status), $paid_statuses)) {
            return;
        }

        $helper->storeOrder($order->get_data());
    }

  /* TODO: make the woo_* functions work */

  public function ping(){
    $url = get_option('wasync_url').'/api/ping';
    $user = get_option('wasync_user');
    $pass = get_option('wasync_password');

    $requester = new Requester($user, $pass);
    return $requester->get($url);
  }
}
function WASyncInit() {
    new WASync();
}
add_action('init', 'WASyncInit');
