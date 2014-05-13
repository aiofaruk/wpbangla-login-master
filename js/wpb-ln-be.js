/*
 WPB Login Master
 (c) 2014. Shameem Reza
 http://shameemreza.info
*/

// delete redirection by user
function delete_user_redirect(username, table_row_id) {
  var answer = confirm("Are you sure you want to delete the redirection rule for user \"" + username + "\"?");
  if (answer){
    var data = {action: 'ln_delete_user_redirect', username: username};

    jQuery.post(ajaxurl, data, function(response) {
      if (!response) {
        alert('Bad AJAX response. Please reload the page.');
      } else {
        window.location.reload();
      }
    });
  }
  return false;
} // delete_user_redirect

// delete ban rule
function delete_ban(ip) {
  var answer = confirm("Are you sure you want to delete the ban rule for IP " + ip + "?");
  if (answer){
    var data = {action: 'ln_delete_ban', ip: ip};

    jQuery.post(ajaxurl, data, function(response) {
      if (!response) {
        alert('Bad AJAX response. Please reload the page.');
      } else {
        window.location.reload();
      }
    });
  }
  return false;
} // delete_user_redirect

jQuery(document).ready(function($){
  // init tabs
  $("#tabs").tabs({
    activate: function( event, ui ) {
        $.cookie("ln_tabs_selected", $("#tabs").tabs("option", "active"));
    },
    active: $("#tabs").tabs({ active: $.cookie("ln_tabs_selected") })
  });

  // Fancy Datatables
  $('#wpb-login-master-log').dataTable({"bAutoWidth": false, "sPaginationType": "full_numbers", "aaSorting": [[0,'desc']]  });
  $('#wpb-login-master-banned-users').dataTable({ "sPaginationType": "full_numbers", "aaSorting": [[1,'desc']], "aoColumnDefs": [{ "bSortable": false, "aTargets": [2]}]  });


  // hide additional parameters on dropdown change
  $('[id^="wpb_ln_redirect_type"]').change(function(){
    var tr = $(this).parents('tr');
    var suffix = $(this).attr('id');
    suffix = suffix.replace('wpb_ln_redirect_type_', '');

    if ($(this).val() == '0' || $(this).val() == '1') {
      $('td.login-master-params-input input', tr).val('');
      $('#wpb_ln_params_' + suffix, tr).attr('readonly','readonly');
      $('td.login-master-params-category', tr).hide();
      $('td.login-master-params-input', tr).show();
    } else if ($(this).val() == '3' || $(this).val() == '4') {
      $('td.login-master-params-input input', tr).val('');
      $('#wpb_ln_params_' + suffix, tr).removeAttr('readonly');
      $('td.login-master-params-category', tr).show();
      $('td.login-master-params-input', tr).hide();
    } else if ($(this).val() == '2') {
      $('#wpb_ln_params_' + suffix, tr).removeAttr('readonly');
      $('option:selected', '#wpb_ln_redirect_category_' + suffix, tr).removeAttr('selected');
      $('td.login-master-params-category', tr).hide();
      $('td.login-master-params-input', tr).show();
    }
  }); // on dropdown change


  // truncate log table
  $('#wpb-ln-truncate-log').click(function(){
    var answer = confirm("Are you sure you want to delete all log entries?");
    if (answer) {
      var data = {action: 'ln_truncate_log'};
      $.post(ajaxurl, data, function(response) {
        if (!response) {
          alert('Bad AJAX response. Please reload the page.');
        } else {
          window.location.reload();
        }
      });
    }
    return false;
  });


  // Open dialog for adding new redirection by user
  $('#wpb-ln-add-ban').click(function(){
    var data = {action: 'ln_new_ban_dialog'};

    $.post(ajaxurl, data, function(response) {
      if (!response) {
        alert('Bad AJAX response. Please reload the page.');
      } else {
        $('#wpb-dialog-ban').html(response)
                           .dialog({ title: 'Add new ban' })
                           .dialog('open');
      }
    });
    return false;
  }); // $('#wpb-ln-add-ban')

  // Open dialog for adding new redirection by user
  $('#wpb-ln-add-new').click(function(){
    var data = {action: 'ln_add_new'};

    $.post(ajaxurl, data, function(response) {
      if (!response) {
        alert('Bad AJAX response. Please reload the page.');
      } else {
        $('#wpb-dialog').html(response)
                       .dialog({ title: 'Create a new redirect rule' })
                       .dialog('open');
      }
    });
    return false;
  }); // $('#wpb-ln-add-new')


  $('#wpb-dialog').dialog({
      autoOpen: false,
      dialogClass: 'wp-dialog',
      modal: true,
      open: function(){ close_dialog = 1; },
      buttons: [{ text: 'Save', 'class': 'button-primary',
        'click': function() {
            $('#redirect-err').hide();
            var data = {action:        'ln_new_user_redirection',
                        username:      $('#username').val(),
                        redirect_type: $('#redirect_type').val() };

            jQuery.post(ajaxurl, data, function(response) {
              if (response != '1') {
                $('#redirect-err').html(response).show();
              } else {
                alert('New redirect rule has been created.');
                $('#wpb-dialog').dialog("close");
                window.location.reload();
              }
            });
          }},
        { text: 'Cancel', 'class': 'button-secondary',
        'click': function() {
            $('.ui-widget-overlay').unbind('click');
            $(this).dialog("close");
            $('#wpb-dialog').empty();
          }
        }]
  }); // $('#wpb-dialog').dialog()

  $('#wpb-dialog-ban').dialog({
      autoOpen: false,
      dialogClass: 'wp-dialog',
      modal: true,
      open: function(){ close_dialog = 1; },
      buttons: [{ text: 'Save', 'class': 'button-primary',
        'click': function() {
            $('#redirect-err').hide();
            var data = {action:        'ln_new_ban_save',
                        ip:       $('#ip').val(),
                        ban_time: $('#ban_time').val()};

            jQuery.post(ajaxurl, data, function(response) {
              if (response != '1') {
                $('#redirect-err').html(response).show();
              } else {
                alert('New ban has been added.');
                $('#wpb-dialog-ban').dialog("close");
                window.location.reload();
              }
            });
          }},
        { text: 'Cancel', 'class': 'button-secondary',
        'click': function() {
            $('.ui-widget-overlay').unbind('click');
            $(this).dialog("close");
            $('#wpb-dialog-ban').empty();
          }
        }]
  }); // $('#wpb-dialog-ban').dialog()
}); // jQuery

var farbtastic;

(function($){
  var pickColor = function(a) {
    farbtastic.setColor(a);
    $('#wpb_ln_options_captcha_color').val(a);
    $('#color-example').css('background-color', a);
    $('#color-example').css('color', a);
  };

  $(document).ready( function() {
    farbtastic = $.farbtastic('#colorPickerDiv', pickColor);

    pickColor( $('#wpb_ln_options_captcha_color').val() );

    $('.pickcolor').click( function(e) {
      $('#colorPickerDiv').show();
      e.preventDefault();
    });

    $('#wpb_ln_options_captcha_color').keyup( function() {
      var a = $('#wpb_ln_options_captcha_color').val(),
        b = a;

      a = a.replace(/[^a-fA-F0-9]/, '');
      if ( '#' + a !== b )
        $('#wpb_ln_options_captcha_color').val(a);
      if ( a.length === 3 || a.length === 6 )
        pickColor( '#' + a );
    });

    $(document).mousedown( function() {
      $('#colorPickerDiv').hide();
    });
  });
})(jQuery);