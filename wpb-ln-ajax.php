<?php
/*
 WPB Login Master
 (c) 2014. Shameem Reza
 http://shameemreza.info
*/


class wpb_ln_ajax extends wpb_login_master {
  // dialog - Add New Redirect By User
  function callback_add_new_dialog() {
    global $wpdb;

    echo '<div style="padding: 20px;">';
    echo '<p id="redirect-err"></p>';
    echo '<label for="username">Username: </label>';
    echo '<select id="username" name="username">';
    $users = $wpdb->get_results("SELECT ID, user_login, display_name from $wpdb->users ORDER BY display_name");
    foreach($users as $user) {
      echo "<option value=\"{$user->user_login}\">{$user->display_name}</option>";
    }
    echo '</select>';
    echo '<br/>';

    $redirect_types[] = array('val' => '0', 'label' => 'Default (normal) behaviour');
    $redirect_types[] = array('val' => '1', 'label' => 'Disable login');
    $redirect_types[] = array('val' => '2', 'label' => 'Redirect to a custom URL');
    $redirect_types[] = array('val' => '3', 'label' => 'Redirect to a random post');
    $redirect_types[] = array('val' => '4', 'label' => 'Redirect to the most recent post');

    // Redirect types
    echo '<label for="redirect_type">Redirection type: </label>';
    echo '<select name="redirect_type" id="redirect_type">';
      wpb_ln_common::create_select_options($redirect_types, 0);
    echo '</select>';

    echo '</div>';
    die();
  } // callback_add_new_dialog


  // dialog - Add New Ban
  function callback_add_new_ban() {
    global $wpdb;

    echo '<div style="padding: 20px;">';
    echo '<p id="redirect-err"></p>';
    echo '<label for="ip">IP: </label>';
    echo '<input type="text" id="ip" name="ip" value="" size="20" />';
    echo '<br/>';

    $bruteforce_timeouts[] = array('val' => 10, 'label' => '10 minutes');
    $bruteforce_timeouts[] = array('val' => 20, 'label' => '20 minutes');
    $bruteforce_timeouts[] = array('val' => 30, 'label' => '30 minutes');
    $bruteforce_timeouts[] = array('val' => 60, 'label' => '1 hour');
    $bruteforce_timeouts[] = array('val' => 120, 'label' => '2 hours');
    $bruteforce_timeouts[] = array('val' => 1440, 'label' => '1 day');
    $bruteforce_timeouts[] = array('val' => 2880, 'label' => '2 days');
    $bruteforce_timeouts[] = array('val' => 10080, 'label' => '7 days');
    $bruteforce_timeouts[] = array('val' => 43200, 'label' => '1 month');
    $bruteforce_timeouts[] = array('val' => 525600, 'label' => '1 year');

    echo '<label for="ban_time">Ban for: </label>';
    echo '<select name="ban_time" id="ban_time">';
      wpb_ln_common::create_select_options($bruteforce_timeouts, 0);
    echo '</select>';

    echo '<p>IPs are entered in the <i>abc.abc.abc.abc</i> notation. Wildcards (* and %) are not supported and leading zeroes have to be removed. This is not good: <i>192.168.001.017</i>, it should be <i>192.168.1.17</i>.</p>';
    echo '</div>';
    die();
  } // callback_add_new_ban


  // callback - save new user redirect
  function callback_save_new_user_redirect() {
    $username      = trim($_POST['username']);
    $redirect_type = trim($_POST['redirect_type']);

    $options = wpb_login_master::fetch_options('users');
    if (!is_array($options)) {
      $options = array();
    }

    if (!isset($options[$username])) {
      $options[$username]['redirect_type'] = $redirect_type;
      update_option(WPB_LOGIN_MASTER_OPTIONS_KEY . 'users', $options);
      die('1');
    } else {
      die('Redirection for specified username already exists!');
    }
  } // callback_save_new_user_redirect


  // callback - save new user redirect
  function callback_save_new_ban() {
    $ip = substr(trim($_POST['ip']), 0, 15);
    $time = trim($_POST['ban_time']);

    $bans = wpb_login_master::fetch_options('banned');
    if (!is_array($bans)) {
      $bans = array();
    }

    $bans[$ip] = current_time('timestamp') + $time * 60;
    update_option(WPB_LOGIN_MASTER_OPTIONS_KEY . 'banned', $bans);

    die('1');
  } // callback_save_new_user_redirect


  // action - delete user redirection
  function callback_delete_user_redirect() {
    $username = trim($_POST['username']);

    $options = wpb_login_master::fetch_options('users');
    unset($options[$username]);
    if (update_option(WPB_LOGIN_MASTER_OPTIONS_KEY . 'users', $options)) {
      die('1');
		} else {
			die(0);
		}
  } // callback_delete_user_redirect

  // action - delete single ban
  function callback_delete_ban() {
    $ip = trim($_POST['ip']);
    $bans = wpb_login_master::fetch_options('banned');

    unset($bans[$ip]);
    if (update_option(WPB_LOGIN_MASTER_OPTIONS_KEY . 'banned', $bans)) {
      die('1');
    } else {
      die('0');
    }
  } // callback_delete_ban


  // action - truncate log
  function callback_truncate_log() {
    global $wpdb;

    $truncate = $wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . wpb_login_master_TABLE);
    if ($truncate) {
      die('1');
    } else {
      die('0');
    }
  } // ajax_callback_truncate_log
} // class wpb_ajax
?>