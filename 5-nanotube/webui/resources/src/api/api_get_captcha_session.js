function api_get_captcha_session(onSuccess, onError) {
    this.type = API.SERVICE.QUERY;
    this.name = 'getCaptchaSession';
    this.data = {};
    this.onSuccess = function () {};
    this.onError = function () {};

    if (onSuccess instanceof Function &&
        onSuccess.prototype.constructor.length === 1) {
        this.onSuccess = onSuccess;
    }

    if (onError instanceof Function) {
        this.onError = onError;
    }    
}