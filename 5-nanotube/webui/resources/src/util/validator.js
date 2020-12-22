function validator(formRoot) {
    this.formRoot = formRoot;

    this.isValid = function () {
        if (!this.formRoot instanceof jQuery) return false;

        let isValid = true;
        this.formRoot.find('input').each(function () {
            input = $(this);
            if (input.attr('data-validation') === undefined) return;

            input.removeClass('is-valid is-invalid');
            if (input.val().match(input.attr('data-validation')) === null) {
                input.addClass('is-invalid');
                isValid = false;
            } else {
                input.addClass('is-valid');
            }

            if (input.attr('data-validation-auto') !== undefined) return;
            input.on('change keyup', function () {
                field = $(this);
                field.removeClass('is-invalid is-valid');
                if (field.val().match(field.attr('data-validation')) === null) {
                    field.addClass('is-invalid');
                } else {
                    field.addClass('is-valid');
                }
            });
            input.attr('data-validation-auto', '1');
        });
        return isValid;
    }
}