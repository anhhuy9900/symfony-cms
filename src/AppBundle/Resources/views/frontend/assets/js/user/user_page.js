/**
 * Created by AnhHuy.Nguyen on 9/1/2016.
 */
var User = window.User || {};

(function($){
    User.Register = {
        init : function(){
            User.Register.user_register();
            User.Register.user_login();
            User.Register.user_forgot_password();
        },

        user_register : function(){
            $("#submit-form-register").on('click',function(){
                //$(".ajax-loader").show();
                /*var data = {
                 key : $(this).val(),
                 access_token : $('#access_token').val()
                 };*/
                var data = {
                    csrf_token : $('#form_csrf_token').val(),
                    fullname : $('#register_fullname').val(),
                    email : $('#register_email').val(),
                    password : $('#register_password').val(),
                    confirm_password : $('#register_confirm_password').val(),
                    phone : $('#register_phone').val(),
                    address : $('#register_address').val(),
                    gender : $('#register_gender').val()
                };

                var data_type ={
                    action : 'user-register',
                    data : data
                };

                var json_data = {
                    'json_data' : JSON.stringify(data_type)
                };

                $.ajax({
                    type: "POST",
                    url: "/user/api",
                    data: json_data,
                    dataType: "json",
                    jsonpCallback:"callback",
                    crossDomain:true,
                    beforeSend: function(){

                    },
                    success: function(response){
                        if (data.status) {
                            $('#popup-register .modal-body').html('<p>' + response.msg+ '</p>');
                            $('#popup-register').modal('show');

                            User.Register.redirect_page();
                        } else {
                            $('#popup-register .modal-body').html('<p>' + response.msg+ '</p>');
                            $('#popup-register').modal('show');
                        }
                    },
                    error: function () {

                    }
                });
                return false;
            });
        },

        user_login : function(){
            $("#submit-form-login").on('click',function(){

                var data = {
                    csrf_token : $('#form_csrf_token').val(),
                    account : $('#login_account').val(),
                    password : $('#login_password').val(),
                    user_remember : $('#login_user_remember').val()
                };

                var data_type ={
                    action : 'user-login',
                    data : data
                };

                var json_data = {
                    'json_data' : JSON.stringify(data_type)
                };

                $.ajax({
                    type: "POST",
                    url: "/user/api",
                    data: json_data,
                    dataType: "json",
                    jsonpCallback:"callback",
                    crossDomain:true,
                    beforeSend: function(){

                    },
                    success: function(response){
                        if (data.status) {
                            $('#popup-login .modal-body').html('<p>' + response.msg+ '</p>');
                            $('#popup-login').modal('show');
                            console.log(22222);
                            User.Register.response_success();

                        } else {
                            $('#popup-login .modal-body').html('<p>' + response.msg+ '</p>');
                            $('#popup-login').modal('show');
                        }
                    },
                    error: function () {

                    }
                });
                return false;
            });
        },

        user_forgot_password : function(){
            $("#submit-form-forgot-password").on('click',function(){

                var data = {
                    csrf_token : $('#form_csrf_token').val(),
                    email : $('#forgot_email').val()
                };

                var data_type ={
                    action : 'user-forgot-password',
                    data : data
                };

                var json_data = {
                    'json_data' : JSON.stringify(data_type)
                };

                $.ajax({
                    type: "POST",
                    url: "/user/api",
                    data: json_data,
                    dataType: "json",
                    jsonpCallback:"callback",
                    crossDomain:true,
                    beforeSend: function(){

                    },
                    success: function(response){
                        if (data.status) {
                            $('#popup-forgot-password .modal-body').html('<p>' + response.msg+ '</p>');
                            $('#popup-forgot-password').modal('show');

                            $('#popup-forgot-password .close').addClass('success-redirect');
                        } else {
                            $('#popup-forgot-password .modal-body').html('<p>' + response.msg+ '</p>');
                            $('#popup-forgot-password').modal('show');
                        }
                    },
                    error: function () {

                    }
                });
                return false;
            });
        },

        response_success : function(){
            console.log(1111);
            $('#popup-register .close').on('click', function(){
                window.location.href = '/';
            });

        }
    }
})(jQuery);

$(document).ready(function(){
    User.Register.init();
});
