<?php
/*
Plugin Name: WPBangla Login Master
Plugin URI: http://wpbangla.com
Description: Gives your site a bodyguard by adding captcha to login and register forms, auto-bans malicious IPs and logs all suspicious activities.
Author: Shameem Reza
Version: 1.0
Author URI: http://shameemreza.info
*/

include 'js/class.php';
if (!function_exists('add_action')) {
  die('Please don\'t open this file directly!');
}

define('WPB_LOGIN_MASTER_CORE_VER', '1.0');
define('WPB_LOGIN_MASTER_OPTIONS_KEY', 'wpb_login_master_');
define('wpb_login_master_TABLE', 'wpb_ln_log');
define('wpb_login_master_LOG_LIMIT', 3000);

require_once('wpb-ln-common.php');
require_once('wpb-ln-gui.php');
require_once('wpb-ln-captcha.php');
require_once('wpb-ln-ajax.php');


class wpb_login_master {
  function init() {
    if (is_admin()) {
      // check min WP version
      self::check_wp_version(3.5);

      // add menu item
      add_action('admin_menu', array(__CLASS__, 'admin_menu'));

      // aditional links in plugin description
      add_filter('plugin_action_links_' . basename(dirname(__FILE__)) . '/' . basename(__FILE__),
      					 array(__CLASS__, 'plugin_action_links'));
      add_filter('plugin_row_meta', array(__CLASS__, 'plugin_meta_links'), 10, 2);

      // enqueue scripts
      add_action('admin_enqueue_scripts', array(__CLASS__, 'backend_enqueue'));

      // settings registration
      add_action('admin_init', array(__CLASS__, 'register_settings'));

      // ajax endpoints
      add_action('wp_ajax_ln_add_new', array('wpb_ln_ajax', 'callback_add_new_dialog'));
      add_action('wp_ajax_ln_new_user_redirection', array('wpb_ln_ajax','callback_save_new_user_redirect'));
      add_action('wp_ajax_ln_delete_user_redirect', array('wpb_ln_ajax', 'callback_delete_user_redirect'));
      add_action('wp_ajax_ln_delete_ban', array('wpb_ln_ajax', 'callback_delete_ban'));
      add_action('wp_ajax_ln_new_ban_dialog', array('wpb_ln_ajax', 'callback_add_new_ban'));
      add_action('wp_ajax_ln_new_ban_save', array('wpb_ln_ajax', 'callback_save_new_ban'));
      add_action('wp_ajax_ln_truncate_log', array('wpb_ln_ajax', 'callback_truncate_log'));
    } else { // frontend
      $options = self::fetch_options('options');

      if ($options['captcha']) {
        // add captcha to login and register form
        add_action('login_form', array(__CLASS__, 'captcha_print'));
        add_action('register_form', array(__CLASS__, 'captcha_print'));

        // show captcha errors
        add_filter('login_errors', array(__CLASS__, 'login_captcha_errors'));
        add_filter('wp_authenticate_user', array(__CLASS__, 'authenticate_user_check'), 15, 2);
        add_filter('registration_errors', array(__CLASS__, 'register_captcha_errors'));
      }

      // add ban check to login header
      add_action('login_init', array(__CLASS__, 'form_init_check'));

      // redirect user after successful login
      add_filter('login_redirect', array(__CLASS__, 'redirect'), 1, 3);
      // log failed login attempt
      add_action('wp_login_failed', array(__CLASS__, 'failed_login'));
      // redirect user after logout
      add_action('wp_logout', array(__CLASS__, 'redirect_logout'));
      add_filter('login_message', array(__CLASS__, 'login_message'));
    }

    @session_start();
    wpb_ln_common::check_ban(true);
    $ban = wpb_ln_common::check_ban(false);
    if ($ban && is_user_logged_in()) {
      wpb_ln_common::write_log('', 'login_denied_banned_IP');
      wp_clear_auth_cookie();
      wp_redirect(home_url());
      die();
    }
  } // init


  // display message above login form
  function login_message($msg) {
    $options = self::fetch_options('options');

    if ($options['login_msg'] &&
       @$_GET['action'] != 'register' &&
       @$_GET['action'] != 'lostpassword') {
      $msg = '<p class="message register">' . $options['login_msg'] . '</p>' . $msg;
    }

    return $msg;
  } // login message

  // registration form captcha errors
  function register_captcha_errors($errors) {
    $options = self::fetch_options('options');

    if ($options['captcha'] &&
        (!isset($_POST['wpb_ln_captcha']) || $_POST['wpb_ln_captcha'] != wpb_ln_captcha::get())) {
      $errors->add('captcha_wrong', '<strong>ERROR:</strong> Wrong captcha value!');
    }

    return $errors;
  } // register_captcha_errors


  // registration captcha errors
  function login_captcha_errors($errors) {
    $options = self::fetch_options('options');

    if ($options['captcha'] && get_transient('wpb_ln_captcha_failed')) {
      delete_transient('wpb_ln_captcha_failed');
      $errors = '<strong>ERROR:</strong> Wrong captcha value!';
    }

    return $errors;
  } // register_captcha_errors


  // add captcha to form
  function captcha_print() {
    $options = self::fetch_options('options');

    echo '<p><label for="wpb_ln_captcha">' . $options['captcha_text'];
    echo '<img src="' . plugins_url('wpb-ln-captcha.php?wpb-generate-image=true&color=' . urlencode($options['captcha_color']) . '&noise=' . $options['captcha_noise'] . '&rnd=' . rand(0, 10000), __FILE__) . '" alt="Captcha" />';
    echo '<br /><input tabindex="21" class="input" type="text" size="3" name="wpb_ln_captcha" id="wpb_ln_captcha" />';
    echo '</label></p><br />';
  } // captcha_print


  // enqueue CSS and JS
  function backend_enqueue() {
    if (self::is_plugin_page()) {
      wp_enqueue_style('wp-jquery-ui-dialog');
      wp_enqueue_style('farbtastic');

      wp_enqueue_script('jquery-ui-dialog');
      wp_enqueue_script('jquery-ui-tabs');
      wp_enqueue_script('farbtastic');

      wp_enqueue_style('wpb-ln-css', plugin_dir_url(__FILE__) . 'css/wpb-ln-style.css', array(), '1.0');
      wp_enqueue_style('wpb-ln-css-datatables', plugin_dir_url(__FILE__) . 'css/wpb-ln-datatables.css', array(), WPB_LOGIN_MASTER_CORE_VER);
      wp_enqueue_script('wpb-ln-jquery-datatables', plugin_dir_url(__FILE__) . 'js/jquery.dataTables.min.js', '', WPB_LOGIN_MASTER_CORE_VER);
      wp_enqueue_script('wpb-ln-jquery-cookies', plugin_dir_url(__FILE__) . 'js/jquery.cookie.js', '', WPB_LOGIN_MASTER_CORE_VER);
      wp_enqueue_script('wpb-ln-jquery', plugin_dir_url(__FILE__) . 'js/wpb-ln-be.js', array('jquery'), WPB_LOGIN_MASTER_CORE_VER);
    }
  } // backend_enqueue


  // log failed login
  function failed_login() {
    $options = self::fetch_options('options');

    if (self::send_notification('failed_login')) {
      $message = 'Username: ' . trim($_POST['log']) . "\r\n";
      $message .= 'IP: ' . $_SERVER['REMOTE_ADDR'] . "\r\n";
      $message .= 'Time: ' . date(get_option('date_format') . ' ' . get_option('time_format'), current_time('timestamp')) . "\r\n";
      $message .= 'User-agent: ' . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
      wp_mail($options['notification_email'], '[' . get_bloginfo('name') . '] User failed to login', $message) ;
    }

    wpb_ln_common::write_log(trim($_POST['log']), 'failed_login');

    wpb_ln_common::check_ban(true);
    $ban = wpb_ln_common::check_ban(false);
    if ($ban && is_user_logged_in()) {
      wpb_ln_common::write_log('', 'login_denied_banned_IP');
      wp_clear_auth_cookie();
      wp_redirect(home_url());
      die();
    }
  } // failed_login


  // check if email notifications are set up
  function send_notification($type = 'new_ban') {
	  $options = self::fetch_options('options');

    if (!is_email($options['notification_email'])) {
      return false;
    }

    if ($type == 'new_ban' && $options['notification_banned']) {
      return true;
    } elseif($type == 'failed_login' && $options['notification_failed_login']) {
      return true;
    } elseif($type == 'successful_login' && $options['notification_successful_login']) {
      return true;
    } else {
      return false;
    }
  } // send_notification


  // handle redirection on login
  function redirect($redirect_to, $requested_redirect_to, $user) {
    // login page? do nothing!
    if(!isset($user->user_login)) {
      return $redirect_to;
    }

    // get rules
    $redirect_by_user = self::fetch_options('users');
    $redirect_by_role = self::fetch_options('roles');
    $options = self::fetch_options('options');

    // check if user is banned
    $ban = wpb_ln_common::check_ban(false);
    if ($ban == 2) {
      wpb_ln_common::write_log('', 'login_denied_banned_IP');
      wp_clear_auth_cookie();
      wp_redirect(home_url());
      die();
    } elseif ($ban == 2) { // should never happen but ....
      $options = wpb_login_master::fetch_options('options');
      wp_clear_auth_cookie();
      wpb_ln_common::write_log('', 'access_denied_banned_ip');
      die($options['ban_msg']);
    }

    wpb_ln_common::write_log($user->user_login, 'login');
    if (self::send_notification('successful_login')) {
      $message = 'Username: ' . $user->user_login . "\r\n";
      $message .= 'IP: ' . $_SERVER['REMOTE_ADDR'] . "\r\n";
      $message .= 'Time: ' . date(get_option('date_format') . ' ' . get_option('time_format'), current_time('timestamp')) . "\r\n";
      $message .= 'User-agent: ' . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
      wp_mail($options['notification_email'], '[' . get_bloginfo('name') . '] User logged in', $message);
    }

    // redirect by role or user
    if (isset($redirect_by_user[$user->user_login]) &&
        $redirect_by_user[$user->user_login]['redirect_type']) {
      // redirection controlled by username
      self::redirection_controller('user', $user->user_login);
    } elseif (isset($redirect_by_role[$user->roles[0]]) &&
              $redirect_by_role[$user->roles[0]]['redirect_type']) {
      // redirection controlled by user role
      self::redirection_controller('role', $user->roles[0]);
    } else {
      return $redirect_to;
    }
  } // redirect


  // redirect user on logout and log event
  function redirect_logout() {
    global $current_user, $user;
    $options = self::fetch_options('options');

    if (!$current_user->data->user_login) {
      $tmp = $user->data->user_login;
    } else {
      $tmp = $current_user->data->user_login;
    }

    wpb_ln_common::write_log($tmp, 'logout');

    if ($options['logout_url']) {
      wp_redirect(trim(wpb_ln_common::parse_url_vars($options['logout_url'])));
      die();
    }

    return;
  } // redirect_logout


  // create the admin menu item
  function admin_menu() {
    add_options_page('WPB Login Master', 'WPB Login Master', 'manage_options', 'wpb_login_master', array(__CLASS__, 'options_page'));
  } // admin_menu


  // add settings link to plugins table
  function plugin_action_links($links) {
    $settings_link = '<a href="options-general.php?page=wpb_login_master" title="WPB Login Master Settings">Settings</a>';
    array_unshift($links, $settings_link);

    return $links;
  } // plugin_action_links


  // add links to plugin's description in plugins table
  function plugin_meta_links($links, $file) {
	  $documentation_link = '<a target="_blank" href="' . plugin_dir_url(__FILE__) . 'documentation/" title="View documentation">Documentation</a>';
    $support_link = '<a target="_blank" href="http://www.facebook.com/groups/Wordpress2Smashing/" title="Contact WPBangla">Support</a>';

    if ($file == plugin_basename(__FILE__)) {
      $links[] = $documentation_link;
      $links[] = $support_link;
    }

    return $links;
  } // plugin_meta_links


  // redirection controller
  function redirection_controller($redirect_by = '', $args = '') {
    if ($redirect_by == 'user') {
      $redirect = self::fetch_options('users');
    } elseif ($redirect_by == 'role') {
      $redirect = self::fetch_options('roles');
    }

    // parse vars like {site_url}, {username} ...
    $redirect[$args]['params'] = wpb_ln_common::parse_url_vars($redirect[$args]['params']);

    switch ($redirect[$args]['redirect_type']) {
      case '1':
       // login disabled
       self::login_disabled();
      break;
      case '2':
       // redirect to custom URL
       wp_redirect($redirect[$args]['params']);
      break;
      case '3':
       // random post redirect
       self::post_redirect('random', $redirect[$args]['redirect_category']);
      break;
      case '4':
       // last post redirect
       self::post_redirect('last', $redirect[$args]['redirect_category']);
      break;
      default:
        // just to be sure
        wp_redirect(home_url());
      break;
    } // end switch

    // fix for problematic web servers
    die();
  } // redirection_controller


  // don't let the user login
  function login_disabled($url = '') {
    if (empty($url)) {
      $url = home_url();
    }

    wp_logout();

    wp_redirect($url);
    die();
  } // login_disabled


  // check captcha code
  function authenticate_user_check($user, $password) {
    $options = wpb_login_master::fetch_options('options');

    if ($options['captcha'] &&
        isset($_POST['wpb_ln_captcha']) &&
        $_POST['wpb_ln_captcha'] != wpb_ln_captcha::get()) {
      set_transient('wpb_ln_captcha_failed', 5);
      $user = null;
    }

    return $user;
  } // authenticate_user_check


  // check ban on login form access
  function form_init_check() {
    $ban = wpb_ln_common::check_ban(false);

    if ($ban == 2) {
      wpb_ln_common::write_log('', 'login_denied_banned_IP');
      wp_clear_auth_cookie();
      wp_redirect(home_url());
      die();
    } elseif ($ban == 2) { // should never happen but ....
      $options = wpb_login_master::fetch_options('options');
      wp_clear_auth_cookie();
      wpb_ln_common::write_log('', 'access_denied_banned_ip');
      die($options['ban_msg']);
    }
  } // bruteforce_check


  // redirect to a post
  function post_redirect($switch = 'last', $category = '') {
    switch($switch) {
      case 'last':
        $options = array('numberposts' => '1', 'orderby' => 'post_date', 'order' => 'DESC');
      break;
      case 'random':
        $options = array('numberposts' => '1', 'orderby' => 'rand');
      break;
    }
    if ($category) {
      $options['category_name'] = $category;
    }
    $post = get_posts($options);

    if (sizeof($post)) {
      $url = get_permalink($post[0]->ID);
      wp_redirect($url);
    } else {
      wp_redirect(home_url());
    }

    die();
  } // post_redirect


  // get plugin options
  function fetch_options($option = 'roles') {
    $options = get_option(WPB_LOGIN_MASTER_OPTIONS_KEY . $option);
    if (!is_array($options)) {
      $options = array();
    }

    return $options;
  } // fetch_options


  // register settings option key
  function register_settings() {
    register_setting('wpb_ln_options', WPB_LOGIN_MASTER_OPTIONS_KEY . 'options', array(__CLASS__, 'sanitize_settings'));
  } // register_settings


  // sanitize settings on save
  function sanitize_settings($values) {
    if (isset($_POST['wpb_ln_roles'])) {
      update_option(WPB_LOGIN_MASTER_OPTIONS_KEY . 'roles', $_POST['wpb_ln_roles']);
    }
    if (isset($_POST['wpb_ln_users'])) {
      update_option(WPB_LOGIN_MASTER_OPTIONS_KEY . 'users', $_POST['wpb_ln_users']);
    }

    $old_options = self::fetch_options('options');

    foreach ($values as $key => $value) {
      switch ($key) {
        case 'notification_email':
        case 'logout_url':
        case 'ban_msg':
        case 'login_msg':
        case 'captcha_text':
          $values[$key] = trim($value);
        break;
        case 'captcha':
        case 'captcha_noise':
          $values[$key] = (int) $value;
        break;
        case 'captcha_color':
          $values[$key] = substr($value, 0, 7);
        break;
      } // switch
    } // foreach

    wpb_ln_common::check_var_isset($values, array('captcha' => 0, 'captcha_noise' => 0));

    return array_merge($old_options, $values);
  } // sanitize_settings


  // complete options/log page
  function options_page() {
    if (!current_user_can('manage_options'))  {
      wp_die('You do not have sufficient permissions to access this page.');
    }

    echo '<div class="wrap">
          <div class="icon32" id="icon-options-general"><br /></div>
          <h2>WPB Login Master</h2>';

    echo '<div id="tabs">
          <ul>
            <li><a href="#redirections"><span>Redirections</span></a></li>
            <li><a href="#temporarily-banned"><span>Banned IPs</span></a></li>
            <li><a href="#logs"><span>Log</span></a></li>
            <li><a href="#options"><span>Settings</span></a></li>
          </ul>';

    echo '<form method="post" action="options.php">';
    settings_fields('wpb_ln_options');

    echo '<div id="redirections">';
      wpb_ln_gui::redirect_table_by_roles();
      wpb_ln_gui::redirect_table_by_users();
    echo '</div>';

    echo '<div id="logs">';
      wpb_ln_gui::log_list();
    echo '</div>';

    echo '<div id="temporarily-banned">';
      wpb_ln_gui::banned_users();
    echo '</div>';

    echo '<div id="options">';
      wpb_ln_gui::additional_options();
    echo '</div>';
    echo '</form>';
    echo '</div>'; // div#tabs

    echo '</div>'; // wrap
    echo '<div id="wpb-dialog"></div>';
  }

  // remove all bans
  function clear_banned() {
    update_option(WPB_LOGIN_MASTER_OPTIONS_KEY . 'banned', array());
  } // clear_banned


  // are we on plugin's option page?
  function is_plugin_page() {
    $current_screen = get_current_screen();
    if ($current_screen->id == 'settings_page_wpb_login_master') {
      return true;
		} else {
			return false;
		}
  } // is_plugin_page


  // check if the minimal WP version required by the plugin is installed
  function check_wp_version($min_version) {
    if (!version_compare(get_bloginfo('version'), $min_version,  '>=')) {
        add_action('admin_notices', array(__CLASS__, 'min_version_error'));
    }
  } // check_wp_version


    // display error message if WP version is too low
  function min_version_error() {
    echo '<div class="error"><p>WPB Login Master <b>requires WordPress version 3.5</b> or higher to function properly. You\'re using WordPress version ' . get_bloginfo('version') . '. Please <a href="update-core.php">update it</a>.</p></div>';
  } // min_version_error


  // activate plugin
  function activate() {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $options = self::fetch_options('options');
    $table_name = $wpdb->prefix . wpb_login_master_TABLE;

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
                `id` int(10) unsigned NOT NULL auto_increment,
                `ip` varchar(15) character set latin1 default NULL,
                `useragent` text character set latin1,
                `timestamp` datetime default NULL,
                `username` varchar(24) character set latin1 default NULL,
                `msg` text character set latin1,
                PRIMARY KEY  (`id`)
              ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
      dbDelta($sql);
    }

    // default options
    if (!sizeof($options)) {
      $default['captcha'] = 1;
      $default['captcha_noise'] = 0;
      $default['captcha_color'] = '#FFFFFF';
      $default['captcha_text'] = 'Are you human? Please solve: ';
      $default['max_login_attempts'] = 5;
      $default['max_login_attempts_time'] = 5;
      $default['bruteforce_ban_time'] = 120;
      $default['notification_email'] = get_bloginfo('admin_email');
      $default['notification_failed_login'] = 1;
      $default['notification_successful_login'] = 0;
      $default['notification_banned'] = 1;
      $default['logout_url'] = '';
      $default['ban_rule'] = 2;
      $default['ban_msg'] = 'You are banned!';
      $default['login_msg'] = '<b>NOTICE:</b> 5 failed login attempts in 5 minutes will get you <b>banned</b> for 2 hours.';

      update_option(WPB_LOGIN_MASTER_OPTIONS_KEY . 'options', $default);
    }
  } // activate


  // deactivate the plugin
  function deactivate() {
    global $wpdb;

    // remove option keys
    delete_option(WPB_LOGIN_MASTER_OPTIONS_KEY . 'roles');
    delete_option(WPB_LOGIN_MASTER_OPTIONS_KEY . 'users');
    delete_option(WPB_LOGIN_MASTER_OPTIONS_KEY . 'options');

    // and table
    $drop = $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . wpb_login_master_TABLE);
  } // deactivate
} // class wpb_login_master


// hook everything up
add_action('init', array('wpb_login_master', 'init'));
register_activation_hook(__FILE__, array('wpb_login_master', 'activate'));
register_deactivation_hook(__FILE__, array('wpb_login_master', 'deactivate'));
?>