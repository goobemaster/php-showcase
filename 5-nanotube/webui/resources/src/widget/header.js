function header_widget() {
    LAYOUT.echo(APP.LAYOUT.HEADER, APP.BODY, LAYOUT.POSITION.FRONT);
    this.content = $('header #header_content');

    this.add_login_section = function () {
        LAYOUT.echo(APP.LAYOUT.HEADER_LOGIN, this.content, LAYOUT.POSITION.BACK);
        this.content.find('#header_login_button').on('click', function () {
            if (!APP.current_page.hasOwnProperty('login')) return;
            APP.current_page.login.show();
        });
    }
}