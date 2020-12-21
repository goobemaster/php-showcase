SCRIPT.require('resources/src/widget/header.js');
SCRIPT.require('resources/src/widget/footer.js');
SCRIPT.require('resources/src/widget/login.js');

function login_page() {
    APP.BODY.empty();
    this.header = new header_widget();
    this.login = new login_widget();
    this.footer = new footer_widget();
    
    this.index = function () {
        this.header.add_login_section();
        this.login.show();
    }
}