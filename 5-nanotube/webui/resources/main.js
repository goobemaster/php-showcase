const SCRIPT = new function () {
    this.loaded = [];

    this.require = function (fqname) {
        if (this.loaded.includes(fqname)) {
            console.log('Script already loaded: ' + fqname);
            return;
        }

        let success = false;
        $.ajax({
            async: false,
            url: fqname,
            dataType: 'script'
        })
        .fail(function () {
            console.log('Failed to load required file: ' + fqname);
        })
        .done(function (data, status, jqXHR) {
            if (jqXHR.status !== 200) return;
            SCRIPT.loaded.push(fqname);
            console.log('Required file was loaded: ' + fqname);
            success = true;
        });
        return success;
    }
}

const LAYOUT = new function () {
    this.POSITION = {
        FRONT: 0,
        BACK: 1
    }
    this.cache = {};

    this.echo = function (name, target, position) {
        if (!target instanceof jQuery) return false;

        if (Object.keys(this.cache).includes(name)) {
            console.log('Layout already loaded: ' + name);
            if (position === this.POSITION.BEFORE) {
                target.prepend(this.cache[name]);
            } else {
                target.append(this.cache[name]);
            }
            return true;
        }

        let success = false;
        $.ajax({
            async: false,
            url: `resources/src/layout/${name}.html`,
            dataType: 'html'
        })
        .fail(function () {
            console.log('Failed to load required layout: ' + name);
        })
        .done(function (data, status, jqXHR) {
            if (jqXHR.status !== 200) return;
            LAYOUT.cache[name] = data;
            if (position === LAYOUT.POSITION.BEFORE) {
                target.prepend(LAYOUT.cache[name]);
            } else {
                target.append(LAYOUT.cache[name]);
            }
            console.log('Required layout was loaded: ' + name);
            success = true;
        });
        return success;
    }
}

const SESSION = new function () {
    this.KEY = {
        USER_TOKEN: 'user_token',
        CAPTCHA_SESSION: 'captcha_sess',
    }

    this.exists = function (key) {
        return window.localStorage.getItem(key) !== null;
    }

    this.read = function (key) {
        return window.localStorage.getItem(key);
    }

    this.write = function (key, value) {
        window.localStorage.setItem(key, value);
    }

    this.delete = function (key) {
        window.localStorage.removeItem(key);
    }
}

const APP = new function () {
    this.BASE_URL = 'http://localhost:8000';
    this.PAGE = {
        LOGIN: 'login',
        FEED: 'feed'
    }
    this.LAYOUT = {
        HEADER: 'header',
        HEADER_LOGIN: 'header_login',
        FOOTER: 'footer',
        MODAL_LOGIN: 'modal_login',
        MODAL_ALERT: 'modal_alert'
    }
    this.ALERT = {
        SUCCESS: '<i class="fas fa-check-circle text-success"></i>',
        DANGER: '<i class="fas fa-times-circle text-danger"></i>',
        WARNING: '<i class="fas fa-exclamation-circle text-warning"></i>',
        INFO: '<i class="fas fa-info-circle text-info"></i>',
        GENERIC: '<i class="far fa-comment"></i>'
    }
    this.BODY = $('body');
    this.current_page;

    this.boot = function () {
        if (!SESSION.exists(SESSION.KEY.USER_TOKEN)) {
            this.redirect(this.PAGE.LOGIN);
        } else {
            this.redirect(this.PAGE.FEED);
        }
    }

    this.redirect = function (to) {
        if (!SCRIPT.require('resources/src/page/' + to + '.js')) {
            console.log('Page implementation does not exist!');
            return;
        }
        this.current_page = new window[to + '_page'];
        this.current_page.index();
    }

    this.alert = function (type, title, message, button = 'OK') {
        if ($('#modal_alert').length !== 0) {
            console.log('An alert is already on the screen!');
            return;
        }
        if (!Object.values(this.ALERT).includes(type)) {
            type = this.ALERT.GENERIC;
        }
        if (title.length === 0) title = 'Alert';
        if (message.length === 0) message = 'This poor alert has no message associated with it :(<br><small>Please contact us to find out why!</small>';
        LAYOUT.echo(APP.LAYOUT.MODAL_ALERT, APP.BODY, LAYOUT.POSITION.BACK);
        modal = $('#modal_alert');
        modal.find('.modal-title').text(title);
        modal.find('.modal-body .col-2').html(type);
        modal.find('.modal-body p').html(message);
        modal.find('.modal-footer button').text(button);
        modal.on('hidden.bs.modal', function (e) { modal.remove(); })
        modal.modal('show');
    }
}

const API = new function () {
    this.SERVICE = {
        COMMAND: 0,
        QUERY: 1
    }

    this.call = function (definition) {
        if (!definition.hasOwnProperty('name') ||
            !definition.hasOwnProperty('data') ||
            !definition.hasOwnProperty('onSuccess') ||
            !definition.hasOwnProperty('onError') ||
            !Object.values(this.SERVICE).includes(definition.type)) {
            console.log('Invalid API call definition!');
            return;
        }

        if (definition.type === this.SERVICE.QUERY) {
            $.ajax({
                type: 'POST',
                url: `${APP.BASE_URL}/${definition.name}`,
                data: JSON.stringify(definition.data),
                contentType: 'application/json',
                dataType: 'json',
                headers: { 'Query': '1' },
                success: definition.onSuccess,
                error: definition.onError
            });
            return;
        }

        request = $.post(`${APP.BASE_URL}/${definition.name}`,
            JSON.stringify(definition.data), definition.onSuccess, "text");
        request.fail(definition.onError);
    }
}

APP.boot();