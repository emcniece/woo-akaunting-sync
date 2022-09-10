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
    //$this->constants();
    //require_once(AKAWOO_PATH . 'library/helper.php');

    $this->init();
  }

  public function init(){
    global $wp_customize;

    if (!is_admin()) {
        // to do
    } elseif (!isset($wp_customize)) {
      add_action('admin_menu', array($this, 'menu'));
      add_action('admin_init', array($this, 'register_options'));
    }
  }

  public function menu(){
    add_options_page('WooCommerce Akaunting Sync Settings', 'Akaunting WooCommerce', 'manage_options', 'akaunting-woocommerce', array($this, 'display_options'));
  }

  public function register_options(){
    register_setting('wasync-options', 'wasync_url');
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

    require_once(AKAWOO_PATH . 'options.php');
  }
}