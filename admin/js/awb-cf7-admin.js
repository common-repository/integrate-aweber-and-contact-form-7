(function ($) {
    'use strict';

    $(document).ready(function () {

        // Get access token
        $('#awbc_get_auth_code').on('click', function (e) {
            e.preventDefault();
            $(this).attr('disabled', 'true');
            $.ajax({
                url: awbc.ajaxurl,
                type: 'post',
                data: {
                    action: 'awbc_get_access_token',
                    awbc_form_id: $('#awbc_form_id').val(),
                    awbc_code_verifier: $('#awbc_code_verifier').val(),
                    awbc_auth_code: $('#awbc_auth_code').val()
                }
            }).done(function () {
                get_accounts();
            }).fail(function (xhr) {
                alert_error(xhr);
                $('#awbc_get_auth_code').removeAttr('disabled');
            })
        });

        // Get accounts
        function get_accounts() {
            $.ajax({
                url: awbc.ajaxurl,
                type: 'post',
                data: {
                    action: 'awbc_get_accounts',
                    awbc_form_id: $('#awbc_form_id').val()
                }
            }).done(function () {
                get_lists()
            }).fail(function (xhr) {
                alert_error(xhr);
                $('#awbc_get_auth_code').removeAttr('disabled');
            })
        }

        // Get lists
        function get_lists() {
            $.ajax({
                url: awbc.ajaxurl,
                type: 'post',
                data: {
                    action: 'awbc_get_lists',
                    awbc_form_id: $('#awbc_form_id').val()
                }
            }).done(function (response) {
                populate_lists(response.data);
            }).fail(function (xhr) {
                alert_error(xhr);
            }).always(function () {
                $('#awbc_get_auth_code').removeAttr('disabled');
                $('#awbc_reload_lists').removeAttr('disabled')
            })
        }

        // Populate lists
        function populate_lists(lists) {
            $('#awbc_lists').empty();
            $.each(lists, function (key, value) {
                $('#awbc_lists').append('<option value="' + key + '">[' + key + '] ' + value + '</option>')
            });
            $('#awbc_auth_box').fadeOut();
            $('#awbc_list_box').fadeIn();
            $('.awbc-authorized').fadeIn();
        }

        // Reload lists
        $('#awbc_reload_lists').on('click', function (e) {
            e.preventDefault();
            $(this).attr('disabled', true);
            get_lists();
        });

        // Connect list to form
        $('#awbc_connect_list').on('click', function (e) {
            e.preventDefault();
            $(this).attr('disabled', 'true');
            $.ajax({
                url: awbc.ajaxurl,
                type: 'post',
                data: {
                    action: 'awbc_connect_list',
                    awbc_form_id: $('#awbc_form_id').val(),
                    awbc_list_id: $('#awbc_lists').val()
                }
            }).done(function (response) {
                alert_success(response.data);
                $('#awbc_sub_box').fadeIn();
            }).fail(function (xhr) {
                alert_error(xhr);
            }).always(function () {
                $('#awbc_connect_list').removeAttr('disabled');
            })
        });

        // Revoke auth
        $('#awbc_revoke_auth').on('click', function (e) {
            e.preventDefault();
            $(this).attr('disabled', 'true');
            $.ajax({
                url: awbc.ajaxurl,
                type: 'post',
                data: {
                    action: 'awbc_revoke_auth',
                    awbc_form_id: $('#awbc_form_id').val()
                }
            }).done(function () {
                $('.awbc-authorized').fadeOut();
                $('#awbc_list_box').fadeOut();
                $('#awbc_sub_box').fadeOut();
                $('#awbc_auth_box').fadeIn();
            }).fail(function (xhr, status, error) {
                alert_error(xhr);
            }).always(function () {
                $('#awbc_revoke_auth').removeAttr('disabled')
            })
        });

        // Error alert
        function alert_error(xhr) {
            var alert = $('.awbc-error');
            var message = xhr.status === 500 ? xhr.statusText : xhr.responseJSON.data;
            alert.text(message).fadeIn();
            alert_close(alert);
        }

        // Error alert
        function alert_success(message) {
            var alert = $('.awbc-success');
            alert.text(message).fadeIn();
            alert_close(alert)
        }

        // Close alert
        function alert_close(alert) {
            setTimeout(function () {
                alert.fadeOut();
            }, 3000);
        }
    });

})(jQuery);
