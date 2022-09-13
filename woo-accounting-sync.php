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

  public function ping(){
    $url = get_option('wasync_url').'/api/ping';
    $user = get_option('wasync_user');
    $pass = get_option('wasync_password');

    $requester = new Requester($user, $pass);
    print_r($requester->get($url));
  }
}
function WASyncInit() {
    new WASync();
}
add_action('init', 'WASyncInit');
