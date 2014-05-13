<?php
/*
 WPB Login Master
 (c) 2014. Shameem Reza
 http://shameemreza.info
*/

class wpb_ln_gui extends wpb_login_master {
  // redirect table by user roles
  function redirect_table_by_roles() {
    $row_id = 0;
    $roles = wpb_ln_common::get_role_names();
    $rolename_options = wpb_login_master::fetch_options('roles');

    echo '<h3>Redirections by user roles</h3>';
    echo '<table class="wp-list-table widefat" cellspacing="0" id="wpb-login-master-redirect-roles">';
    echo '<thead><tr>';
    echo '<th width="150"><a href="#">Role</a></th>';
    echo '<th><a href="#">Redirect type</a></th>';
    echo '<th width="230"><a href="#">Additional parameters</a></th>';
    echo '</tr></thead>';
    echo '<tbody>';

    foreach($roles as $role => $rolename) {
        // fix for readonly field
        $readonly = '';
        if(!isset($rolename_options[$role]['redirect_type']) ||
           $rolename_options[$role]['redirect_type'] == '0' ||
           $rolename_options[$role]['redirect_type'] == '1' ||
           $rolename_options[$role]['redirect_type'] == '3' ||
           $rolename_options[$role]['redirect_type'] == '4' ) {
          $readonly = 'readonly="readonly"';
        }

      echo '<tr id="wpb-login-master-' . $role . '">';
      echo '<td class="login-master-role">' . $rolename . '</td>';
      echo '<td class="redirect-type">';
      echo self::redirection_type('roles', $role, $rolename_options[$role]['redirect_type']);
      echo '</td>';

      // additional params
      if ($rolename_options[$role]['redirect_type'] == '3' ||
          $rolename_options[$role]['redirect_type'] == '4') {
        $display_params['category'] = 'style="display:block;"';
        $display_params['input'] = 'style="display:none;"';
      } else {
        $display_params['category'] = 'style="display:none;"';
        $display_params['input'] = 'style="display:block;"';
      }

      // category select
      echo '<td class="login-master-params-category" ' . $display_params['category'] . '>';
      echo self::redirection_category('roles', $role, $rolename_options[$role]['redirect_category']);
      echo '</td>';

      // URL input
      echo '<td class="login-master-params-input" ' . $display_params['input'] . '><input type="text" name="wpb_ln_roles[' . $role . '][params]" class="regular-text" value="' . $rolename_options[$role]['params'] . '" id="wpb_ln_params_' . $role . '" ' . $readonly . ' /></td>';
      echo '</tr>';
    } // foreach rules

    echo '</tbody></table>';
    echo '<p><i>Following variables are available to dynamically generate redirect URLs: {username}, {user_id}, {month}, {day}, {year}, {site_url}. <a target="_blank" href="http://demo.shameemreza.info/wpblogin/">Details</a>.</i></p>';

    echo '<p><input type="submit" value="Save Changes" class="button-primary" id="submit" name="submit"></p>';
  } // redirect_table_by_roles


  // redirect table by user name
  function redirect_table_by_users() {
    $row_id = 0;
    $users = wpb_login_master::fetch_options('users');

    echo '<h3>Redirections by users</h3>';
    echo '<p><input type="button" value="Add new redirect by username" class="button-secondary" id="wpb-ln-add-new" name="wpb-ln-add-new"></p>';
    echo '<table class="wp-list-table widefat" cellspacing="0" id="wpb-login-master-redirect-users">';
    echo '<thead><tr>';
    echo '<th width="150"><a href="#">Username</a></th>';
    echo '<th><a href="#">Redirect type</a></th>';
    echo '<th width="230"><a href="#">Additional parameters</a></th>';
    echo '<th>&nbsp;</th>';
    echo '</tr></thead>';
    echo '<tbody>';

    if (is_array($users)) {
      foreach($users as $username => $redirect) {
        // fix for readonly field
        $readonly = '';
        if($redirect['redirect_type'] == '0' ||
           $redirect['redirect_type'] == '1' ||
           $redirect['redirect_type'] == '3' ||
           $redirect['redirect_type'] == '4' ) {
          $readonly = 'readonly="readonly"';
        }

        echo '<tr id="wpb-login-master-user-' . $username . '">';
        echo '<td class="login-master-username">' . $username . '</td>';
        echo '<td class="redirect-type">';
        echo self::redirection_type('users', $username, $redirect['redirect_type']);
        echo '</td>';

      // additional params
      if ($redirect['redirect_type'] == '3' || $redirect['redirect_type'] == '4') {
        $display_params['category'] = 'style="display:block;"';
        $display_params['input'] = 'style="display:none;"';
      } else {
        $display_params['category'] = 'style="display:none;"';
        $display_params['input'] = 'style="display:block;"';
      }

      // category dropdown
      echo '<td class="login-master-params-category" ' . $display_params['category'] . '>';
      echo self::redirection_category('users', $username, $redirect['redirect_category']);
      echo '</td>';

      // URL field
      echo '<td class="login-master-params-input" ' . $display_params['input'] . '>
            <input type="text" name="wpb_ln_users[' . $username . '][params]"
            class="regular-text" value="' . $redirect['params'] . '" id="wpb_ln_params_' . $username . '" ' . $readonly . '/></td>';

      echo '<td><a onclick="delete_user_redirect(\'' . $username . '\', \'' . $row_id .'\'); return false;" href="#"><img src="' . plugin_dir_url(__FILE__) . 'images/delete.png" alt="Delete redirect rule" title="Delete redirect rule" /></a></td>';
      echo '</tr>';
      } // foreach rules
    } // if users

    echo '</tbody></table>';
    echo '<p><i>Following variables are available to dynamically generate redirect URLs: {username}, {user_id}, {month}, {day}, {year}, {site_url}. <a target="_blank" href="http://demo.shameemreza.info/wpblogin">Details</a>.</i></p>';
    echo '<p><input type="submit" value="Save Changes" class="button-primary" id="submit" name="submit"></p>';
  }  // redirect_table_by_users


  // Log of all events
  function log_list() {
    global $wpdb;
    $logs = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . wpb_login_master_TABLE . " ORDER by id DESC LIMIT " . wpb_login_master_LOG_LIMIT);

    echo '<table class="wp-list-table widefat" cellspacing="0" id="wpb-login-master-log">';
    echo '<thead><tr>';
    echo '<th id="header_id"><a href="#">ID</a></th>';
    echo '<th id="header_username"><a href="#">Username</a></th>';
    echo '<th id="header_ip"><a href="#">IP</a></th>';
    echo '<th id="header_agent"><a href="#">User-Agent</a></th>';
    echo '<th id="header_time"><a href="#">Time</a></th>';
    echo '<th id="header_event"><a href="#">Event</a></th>';
    echo '</tr></thead>';
    echo '<tbody>';

    foreach ($logs as $log) {
      $tmp = strtotime($log->timestamp);
      $tmp = date(get_option('date_format') . ' ' . get_option('time_format') ,$tmp);
      echo '<tr>';
      echo '<td class="login-master-id">' . $log->id . '</td>';
      echo '<td class="login-master-username">' . $log->username . '</td>';
      echo '<td class="login-master-ip">' . $log->ip . '</td>';
      echo '<td class="login-master-user-agent">' . $log->useragent . '</td>';
      echo '<td class="login-master-date-time">' . $tmp . '</td>';
      echo '<td class="login-master-date-login">' . $log->msg . '</td>';
      echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';

    echo '<br /><br />';
    echo '<p><input type="button" value="Delete all log entries" class="button-secondary" id="wpb-ln-truncate-log"></p>';
  } // log_list


  // Log of temp. banned users
  function banned_users() {
    global $wpdb;
    wpb_ln_common::prune_banned();
    $options = wpb_login_master::fetch_options('options');
    $banned_list = wpb_login_master::fetch_options('banned');

    echo '<p><input type="button" value="Add new ban" class="button-secondary" id="wpb-ln-add-ban"></p>';
    echo '<br>';
    echo '<table class="wp-list-table widefat" id="wpb-login-master-banned-users">';
    echo '<thead><tr>';
    echo '<th id="header2_ip"><a href="#">IP</a></th>';
    echo '<th id="header2_time2"><a href="#">Banned until</a></th>';
    echo '<th>&nbsp;</th>';
    echo '</tr></thead>';
    echo '<tbody>';

    foreach ($banned_list as $ban_ip => $ban_time) {
      echo '<tr>';
      echo '<td>' . $ban_ip . '</td>';
      echo '<td>' . date(get_option('date_format') . ' ' . get_option('time_format'), $ban_time) . '</td>';
      echo '<td align="center"><a onclick="delete_ban(\'' . $ban_ip . '\'); return false;" href="#"><img src="' . plugin_dir_url(__FILE__) . 'images/delete.png" alt="Delete ban" title="Delete ban" /></a></td>';
      echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '<div id="wpb-dialog-ban"></div>';
  } // log_list


  // options tab
  function additional_options() {
    $options = wpb_login_master::fetch_options('options');

    for ($i = 2; $i <= 10; $i++) {
      $max_login_attempts[] = array('val' => $i, 'label' => $i);
    }
    for ($i = 2; $i <= 15; $i++) {
      $max_login_attempts_time_s[] = array('val' => $i, 'label' => $i);
    }

    $bruteforce_timeouts[] = array('val' => 2, 'label' => '2 minutes');
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

    $notification_settings[] = array('val' => 0, 'label' => 'No');
    $notification_settings[] = array('val' => 1, 'label' => 'Yes');

    $ban_rules[] = array('val' => '1', 'label' => 'Can\'t access the whole site');
    $ban_rules[] = array('val' => '2', 'label' => 'Can\'t login');

    echo '<h3>Ban rules</h3>';
    echo '<table class="form-table">';

    echo '<tr valign="top">';
    echo '<th scope="row"><label for="wpb_ln_options_mla">Maximum number of failed login attempts before ban</label></th>';
    echo '<td>
          <select name="wpb_login_master_options[max_login_attempts]" id="wpb_ln_options_mla">';
          wpb_ln_common::create_select_options($max_login_attempts, $options['max_login_attempts']);
    echo '</select> attempts in ';
    echo '<select name="wpb_login_master_options[max_login_attempts_time]" id="wpb_ln_options_mlat">';
      wpb_ln_common::create_select_options($max_login_attempts_time_s, $options['max_login_attempts_time']);
    echo '</select> minutes';
    echo '</td></tr>';

    echo '<tr valign="top">';
    echo '<th scope="row"><label for="wpb_ln_options_bbt">Default ban time</label></th>';
    echo '<td>
          <select name="wpb_login_master_options[bruteforce_ban_time]" id="wpb_ln_options_bbt">';
          wpb_ln_common::create_select_options($bruteforce_timeouts, $options['bruteforce_ban_time']);
    echo '</select>';
    echo '</td></tr>';

    echo '<tr valign="top">';
    echo '<th scope="row"><label for="wpb_ln_options_ban_rule">Banned users</label></th>';
    echo '<td>
          <select name="wpb_login_master_options[ban_rule]" id="wpb_ln_options_ban_rule">';
          wpb_ln_common::create_select_options($ban_rules, $options['ban_rule']);
    echo '</select>';
    echo '</td></tr>';

    echo '<tr valign="top">';
    echo '<th scope="row"><label for="wpb_ln_options_ban_msg">Message for banned users</label></th>';
    echo '<td>
          <textarea cols="50" rows="3" name="wpb_login_master_options[ban_msg]" id="wpb_ln_options_ban_msg">' . $options['ban_msg'] . '</textarea>';
    echo '</td>';
    echo '</tr>';

    echo '<tr valign="top">';
    echo '<th scope="row"><label for="wpb_ln_options_login_msg">Login notice</label></th>';
    echo '<td>
          <textarea cols="50" rows="3" name="wpb_login_master_options[login_msg]" id="wpb_ln_options_login_msg">' . $options['login_msg'] . '</textarea>';
    echo '</td>';
    echo '</tr>';

    echo '</table><br /><br />';

    echo '<h3>Email notifications</h3>';

    echo '<table class="form-table">';
    echo '<tr valign="top">';
    echo '<th scope="row"><label for="wpb_ln_options_notification_email">Notification e-mail</label></th>';
    echo '<td>
          <input type="text" name="wpb_login_master_options[notification_email]" id="wpb_ln_options_notification_email" value="' . $options['notification_email'] . '" class="regular-text" />';
    echo '</td>';
    echo '</tr>';

    echo '<tr valign="top">';
    echo '<th scope="row"><label for="wpb_ln_options_notification_failed_login">Send notification on failed login</label></th>';
    echo '<td>';
    echo '<select name="wpb_login_master_options[notification_failed_login]" id="wpb_ln_options_notification_failed_login">';
    wpb_ln_common::create_select_options($notification_settings, $options['notification_failed_login']);
    echo '</select>';
    echo '</td>';
    echo '</tr>';

    echo '<tr valign="top">';
    echo '<th scope="row"><label for="wpb_ln_options_notification_successful_login">Send notification on successful login</label></th>';
    echo '<td>';
    echo '<select name="wpb_login_master_options[notification_successful_login]" id="wpb_ln_options_notification_successful_login">';
      wpb_ln_common::create_select_options($notification_settings, $options['notification_successful_login']);
    echo '</select>';
    echo '</td>';
    echo '</tr>';

    echo '<tr valign="top">';
    echo '<th scope="row"><label for="wpb_ln_options_notification_banned">Send notification when new IP gets banned</label></th>';
    echo '<td>';
    echo '<select name="wpb_login_master_options[notification_banned]" id="wpb_ln_options_notification_banned">';
      wpb_ln_common::create_select_options($notification_settings, $options['notification_banned']);
    echo '</select>';
    echo '</td>';
    echo '</tr>';
    echo '</table><br /><br />';

    echo '<h3>Captcha settings</h3>';

    echo '<table class="form-table">';
    echo '<tr valign="top">';
    echo '<th scope="row"><label for="wpb_ln_options_captcha">Protect login and register forms with captcha</label></th>';
    echo '<td>';
    echo '<input type="checkbox" name="wpb_login_master_options[captcha]" id="wpb_ln_options_captcha" value="1" ' . checked($options['captcha'], 1, false) . ' />';
    echo '</td></tr>';

    echo '<tr valign="top">';
    echo '<th scope="row"><label for="wpb_ln_options_captcha_color">Captcha background color</label></th>';
    echo '<td>
          <input type="text" name="wpb_login_master_options[captcha_color]" id="wpb_ln_options_captcha_color" value="' . $options['captcha_color'] . '" class="normal-text" size="8" /> <a href="#" class="pickcolor" id="color-example">&nbsp;</a> <input type="button" value="Pick color" class="button-secondary pickcolor" /> <input type="button" value="Transparent" class="button-secondary" onclick="jQuery(\'#wpb_ln_options_captcha_color\').val(\'\'); jQuery(\'#color-example\').css(\'background-color\', \'\');" />';
    echo '<div id="colorPickerDiv" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>';
    echo '</td>';
    echo '</tr>';

    echo '<tr valign="top">';
    echo '<th scope="row"><label for="wpb_ln_options_captcha_text">Captcha question text</label></th>';
    echo '<td>
          <input type="text" name="wpb_login_master_options[captcha_text]" id="wpb_ln_options_captcha_text" value="' . $options['captcha_text'] . '" class="regular-text" />';
    echo '</td>';
    echo '</tr>';

    echo '<tr valign="top">';
    echo '<th scope="row"><label for="wpb_ln_options_captcha_noise">Add noise to captcha image so it is more difficult to read</label></th>';
    echo '<td>';
    echo '<input type="checkbox" name="wpb_login_master_options[captcha_noise]" id="wpb_ln_options_captcha_noise" value="1" ' . checked($options['captcha_noise'], 1, false) . ' />';
    echo '</td></tr>';
    echo '</table><br /><br />';

    echo '<h3>Other settings</h3>';

    echo '<table class="form-table">';
    echo '<tr valign="top">';
    echo '<th scope="row"><label for="wpb_ln_options_logout_url">Redirect URL on logout</label></th>';
    echo '<td>
          <input type="text" name="wpb_login_master_options[logout_url]" id="wpb_ln_options_logout_url" value="' . $options['logout_url'] . '" class="regular-text" />';
    echo '</td>';
    echo '</tr>';

    echo '</tbody></table><br />';
    echo '<p><input type="submit" value="Save Changes" class="button-primary" id="submit" name="submit"></p>';
  } // additional_options

  // redirect type dropdown
  function redirection_type($suffix = '', $for = '', $selected = '') {
    $out = '';

    $redirect_types[] = array('val' => '0', 'label' => 'Default (normal) behaviour');
    $redirect_types[] = array('val' => '1', 'label' => 'Disable login');
    $redirect_types[] = array('val' => '2', 'label' => 'Redirect to a custom URL');
    $redirect_types[] = array('val' => '3', 'label' => 'Redirect to a random post');
    $redirect_types[] = array('val' => '4', 'label' => 'Redirect to the most recent post');

    if ($for == 'administrator') {
      // remove disable login option
      unset($redirect_types[1]);
    }

    $out .= '<select name="wpb_ln_' . $suffix . '[' . $for . '][redirect_type]" id="wpb_ln_redirect_type_' . $for . '">';
    $out .= wpb_ln_common::create_select_options($redirect_types, $selected, false);
    $out .= '</select>';

    return $out;
  } // redirection_type


  // redirect category dropdown
  function redirection_category($suffix = '', $for = '', $selected_opt = '') {
    $out = '';
    $cat_options['hide_empty'] = 0;
    $categories = get_categories($cat_options);

    $out .= '<select name="wpb_ln_' . $suffix . '[' . $for . '][redirect_category]" id="wpb_ln_redirect_category_' . $for . '">';
    $out .= '<option value="0">from any category</option>';

    foreach ($categories as $category) {
      $selected = '';
      if ($category->slug == $selected_opt) {
        $selected = ' selected="selected"';
      }
      $out .= '<option value="' . $category->slug . '"' . $selected . '>from ' . $category->name . '</option>';
    }
    $out .= '</select>';

    return $out;
  } // redirection_category
} // class wpb_ln_gui
?>