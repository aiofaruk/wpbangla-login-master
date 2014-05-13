<?php
/*
 WPB Login Master
 (c) 2014. Shameem Reza
 http://shameemreza.info
*/

class wpb_ln_common extends wpb_login_master {
  // get all registered roles
  function get_role_names() {
    global $wp_roles;
    $rolenames = array();

    foreach ($wp_roles->roles as $role_id => $role) {
      $rolenames[$role_id] = $role['name'];
    }

    return $rolenames;
  } // get_role_names


  // prune temp. banned list
  function prune_banned() {
    $update = false;
    $options = wpb_login_master::fetch_options('options');
    $banned_list = wpb_login_master::fetch_options('banned');

    foreach ($banned_list as $ip => $time) {
      if ($time < current_time('timestamp')) {
        unset($banned_list[$ip]);
        $update = true;
      }
    }

    if ($update) {
      update_option(WPB_LOGIN_MASTER_OPTIONS_KEY . 'banned', $banned_list);
    }
  } // prune_banned


  // parse custom URL vars
  function parse_url_vars($url) {
    global $user;

    $known_vars = array('{username}', '{user_id}', '{month}', '{day}', '{year}', '{site_url}');
    $replace_vars = array($user->data->user_login, $user->data->ID, date('m'), date('d'), date('Y'), home_url());
    $url = str_replace($known_vars, $replace_vars, $url);

    return $url;
  } // parse_url_vars


  // write event to log
  function write_log($username, $msg) {
    global $wpdb;

    $date = date('Y-m-d H:i:m', current_time('timestamp'));

    $query = $wpdb->prepare('INSERT INTO ' . $wpdb->prefix . wpb_login_master_TABLE .
                            ' (username, ip, useragent, timestamp, msg)
                            VALUES (%s, %s, %s, %s, %s)',
                            $username, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $date, $msg);
    $wpdb->query($query);

    if ($msg == 'failed_login') {
      self::update_bans();
    }
  } // write_log


  // add new bansif necessary
  function update_bans() {
    global $wpdb;

    $options = wpb_login_master::fetch_options('options');
    $banned_list = wpb_login_master::fetch_options('banned');
    $date = date('Y-m-d H:i:m', current_time('timestamp'));
    $ip = $_SERVER['REMOTE_ADDR'];

    $query = $wpdb->prepare('SELECT COUNT(id) FROM ' . $wpdb->prefix . wpb_login_master_TABLE .
                            ' WHERE ip =%s AND msg = %s AND timestamp >= DATE_SUB(%s, INTERVAL %s MINUTE)',
                            $_SERVER['REMOTE_ADDR'], 'failed_login', $date, $options['max_login_attempts_time']);
    $login_attempts = $wpdb->get_var($query);

    if ($login_attempts >= $options['max_login_attempts'] && !isset($banned_list[$ip])) {
      $banned_list[$ip] = current_time('timestamp') + $options['bruteforce_ban_time'] * 60;
      update_option(WPB_LOGIN_MASTER_OPTIONS_KEY . 'banned', $banned_list);

      self::write_log('', 'IP_banned');

      // send notification on ban
      if (wpb_login_master::send_notification('new_ban')) {
        $message = 'IP: ' . $ip . "\r\n";
        $message .= 'Time: ' . date(get_option('date_format') . ' ' . get_option('time_format'), current_time('timestamp')) . "\r\n";
        $message .= 'User-agent: ' . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
        wp_mail($options['notification_email'], '[' . get_bloginfo('name') . '] New IP has been banned', $message);
      } // send mail
    } // add new ban
  } // update_bans

  // helper function for creating select's options
  function create_select_options($options, $selected = null, $output = true) {
    $out = "\n";

    foreach ($options as $tmp) {
      if ($selected == $tmp['val']) {
        $out .= "<option selected=\"selected\" value=\"{$tmp['val']}\">{$tmp['label']}&nbsp;</option>\n";
      } else {
        $out .= "<option value=\"{$tmp['val']}\">{$tmp['label']}&nbsp;</option>\n";
      }
    }

    if($output) {
      echo $out;
    } else {
      return $out;
    }
  } //  create_select_options


  // helper function for $_POST checkbox handling
  function check_var_isset(&$values, $variables) {
    foreach ($variables as $key => $value) {
      if (!isset($values[$key])) {
        $values[$key] = $value;
      }
    }
  } // check_var_isset

  // return user ban status
  function check_ban($redirect = false) {
    self::prune_banned();

    $options = wpb_login_master::fetch_options('options');
    $bans = wpb_login_master::fetch_options('banned');

    if (isset($bans[$_SERVER['REMOTE_ADDR']])) {
      $ret = $options['ban_rule'];
    } else {
      $ret = 0;
    }

    if ($redirect) {
      if ($ret == 1) {
        wpb_ln_common::write_log('', 'access_denied_banned_ip');
        wp_clear_auth_cookie();
        die($options['ban_msg']);
      }
    } else {
      return $ret;
    }
  } // check ban
} // class wpb_common
?>