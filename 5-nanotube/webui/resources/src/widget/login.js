SCRIPT.require('resources/src/util/validator.js');

function login_widget() {
    this.init = false;
    this.popup;
    this.login_email;
    this.login_password;
    this.register_email;
    this.register_password;
    this.register_username;
    this.form = {
        login: null,
        registration: null
    }
    this.primary_button;
    this.secondary_button;
    this.captcha;

    this.show = function () {
        if (this.popup === undefined) {
            LAYOUT.echo(APP.LAYOUT.LOGIN_MODAL, APP.BODY, LAYOUT.POSITION.BACK);
        }
        this.popup = $('#login_modal');        
        this.login_email = $('#login_email');
        this.login_password = $('#login_password');
        this.register_email = $('#register_email');
        this.register_password = $('#register_password');
        this.register_username = $('#register_name');
        this.form.login = $('#login_form');
        this.form.registration = $('#registration_form');
        this.form.registration.hide();
        this.primary_button = $('#login_button');
        this.secondary_button = $('#register_button');
        this.captcha = $('#auth_captcha_user');
        this.show_login_form();
        if (this.init) return;

        this.secondary_button.on('click', function () {
            widget = APP.current_page.login;
            if (widget.form.login.is(':visible')) {
                widget.show_register_form();
                widget.secondary_button.find('span').text("Got an account?");
                widget.primary_button.text('Sign me up!');
            } else {
                widget.show_login_form();
                widget.secondary_button.find('span').text("I'm new here");
                widget.primary_button.text('Login');
            }
        });

        this.primary_button.on('click', function () {
            widget = APP.current_page.login;
            widget.form.login.is(':visible') ? widget.login() : widget.register();
        });

        this.update_captcha();

        this.init = true;
    }

    this.show_register_form = function () {
        if (this.popup === undefined) return;
        this.popup.modal('show');
        this.form.login.hide();
        this.form.registration.show();
    }

    this.show_login_form = function () {
        if (this.popup === undefined) return;
        this.popup.modal('show');
        this.form.registration.hide();
        this.form.login.show();
    }

    this.register = function () {
        formValidator = new validator(this.form.registration);
        if (!formValidator.isValid()) return;

        request = $.post(`${APP.BASE_URL}/register`, JSON.stringify({
            'email': this.register_email.val(),
            'password': this.register_password.val(),
            'username': this.register_username.val(),
            'captchaId': SESSION.read(SESSION.KEY.CAPTCHA_SESSION),
            'captcha': this.captcha.val()
        }), function(data) {
            login = APP.current_page.login;
            login.show_login_form();
            login.secondary_button.replaceWith('<small class="text-success"><strong><i class="fas fa-check-circle mr-2"></i>Please log in for the first time.</strong></small>');
            login.update_captcha();
        }, "json");
        request.fail(function(jqXHR) {
            // TODO: Sad modal
        });
    }

    this.login = function () {
        formValidator = new validator(this.form.login);
        if (!formValidator.isValid()) return;

        // TODO
    }

    this.update_captcha = function () {
        $.ajax({
            type: 'POST',
            url: `${APP.BASE_URL}/getCaptchaSession`,
            data: '{}',
            contentType: 'application/json',
            dataType: 'json',
            headers: { 'Query': '1' },
            success: function (data) {
                APP.current_page.login.popup.find('#auth_captcha').css('background-image', `url(data:image/png;base64,${data.blob})`);
                SESSION.write(SESSION.KEY.CAPTCHA_SESSION, data.uuid);
            }
        });
    }
}