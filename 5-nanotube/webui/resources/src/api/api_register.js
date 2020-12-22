function api_register(email, password, username, captchaId, captcha, onSuccess, onError) {
    this.type = API.SERVICE.COMMAND;
    this.name = 'register';
    this.data = {
        'email': email,
        'password': password,
        'username': username,
        'captchaId': captchaId,
        'captcha': captcha
    };
    this.onSuccess = function () {};
    this.onError = function () {};

    if (onSuccess instanceof Function) {
        this.onSuccess = onSuccess;
    }

    if (onError instanceof Function) {
        this.onError = onError;
    }    
}