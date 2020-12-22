SCRIPT.require('resources/src/util/validator.js');
SCRIPT.require('resources/src/api/api_get_captcha_session.js');
SCRIPT.require('resources/src/api/api_register.js');

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
            LAYOUT.echo(APP.LAYOUT.MODAL_LOGIN, APP.BODY, LAYOUT.POSITION.BACK);
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
            } else {
                widget.show_login_form();
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
        this.secondary_button.find('span').text("Got an account?");
        this.primary_button.text('Sign me up!');
    }

    this.show_login_form = function () {
        if (this.popup === undefined) return;
        this.popup.modal('show');
        this.form.registration.hide();
        this.form.login.show();
        this.secondary_button.find('span').text("I'm new here");
        this.primary_button.text('Login');
    }

    this.register = function () {
        form = new validator(this.form.registration);
        if (!form.isValid()) return;

        API.call(new api_register(
            this.register_email.val(),
            this.register_password.val(),
            this.register_username.val(),
            SESSION.read(SESSION.KEY.CAPTCHA_SESSION),
            this.captcha.val(),
            function () {
                APP.current_page.login.show_login_form();
                APP.current_page.login.secondary_button.replaceWith('<small class="text-success"><strong><i class="fas fa-check-circle mr-2"></i>Please log in for the first time.</strong></small>');
                APP.current_page.login.update_captcha();
            },
            function () {
                APP.alert(APP.ALERT.WARNING, 'Registration', 'Please double check that you correctly entered the captcha challenge.');
            }
        ));
    }

    this.login = function () {
        form = new validator(this.form.login);
        if (!form.isValid()) return;

        APP.alert(APP.ALERT.DANGER, 'Missing feature', 'Not implemented yet...');
    }

    this.update_captcha = function () {
        API.call(new api_get_captcha_session(function (data) {
            APP.current_page.login.popup.find('#auth_captcha').css('background-image', `url(data:image/png;base64,${data.blob})`);
            SESSION.write(SESSION.KEY.CAPTCHA_SESSION, data.uuid);
            APP.current_page.login.captcha.val('');
        }, function () {
            // TODO: Sad modal (or maybe attempt it again, error page?)
        }));
    }
}