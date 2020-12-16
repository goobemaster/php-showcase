const APP = new function() {
    this.body = document.querySelector('body');
    this.header = document.querySelector('header');
    this.navProgress = document.querySelector('header nav ul');
    this.footer = document.querySelector('footer');
    this.main = document.querySelector('main#setup');
    this.alert = document.querySelector('main#alert');
    this.step;
    this.options;
    this.config;

    window.addEventListener('resize', function () { APP.resize(); });

    this.init = function () {
        if (this.config === undefined) {
            this.main.style.display = 'none';
            this.resize();
            return;
        }
        this.close_alert();
        for (const i in this.config.steps) {
            APP.navProgress.innerHTML += `<li>${this.config.steps[i].name}</li>`;
        }
        this.main.querySelector('#title button').style.display = this.config.options.app_info_button ? 'block' : 'none';
        this.header.querySelector('h1 strong').textContent = this.config.app.name;
        this.resize();
        this.nextStep();
    }

    this.resize = function () {
        this.body.style.paddingTop = this.header.clientHeight.toString() + 'px';
        this.body.style.paddingBottom = this.footer.clientHeight.toString() + 'px';    
        this.mainCenter();  
    }

    this.mainCenter = function () {
        centerX = window.innerWidth / 2;
        centerY = window.innerHeight / 2;
        this.main.style.left = (centerX - (parseInt(this.main.style.width) / 2)).toString() + 'px';
        this.main.style.top = (centerY - (parseInt(this.main.style.height) / 2)).toString() + 'px';

        centerX = window.innerWidth / 2;
        centerY = window.innerHeight / 2;
        this.alert.style.left = (centerX - (parseInt(this.alert.style.width) / 2)).toString() + 'px';
        this.alert.style.top = (centerY - (parseInt(this.alert.style.height) / 2)).toString() + 'px';
    }

    this.show_alert = function (title, message, okButton) {
        this.alert.querySelector('#title h2').textContent = title;
        this.alert.querySelector('#contents p').innerHTML = message;
        this.alert.style.width = '480px';
        this.alert.style.height = '200px';
        this.alert.style.display = 'block';
        this.alert.querySelector('.buttons').style.display = okButton ? 'block' : 'none';
        this.mainCenter();
    }

    this.close_alert = function () {
        this.alert.style.display = 'none';
    }

    this.show_app_info = function () {
        this.show_alert('About', `<strong><a href="${this.config.app.homepage}" target="_blank">${this.config.app.name}</a></strong><br><br>
        <strong>Version:</strong> ${this.config.app.version}<br>
        <strong>Author:</strong> <a href="${this.config.app.author_url}" target="_blank">${this.config.app.author}</a><br>
        <strong>License:</strong> <a href="${this.config.app.license_url}" target="_blank">${this.config.app.license}</a><br><br>
        For any inquiries please contact our <a href="${this.config.app.support_url}" target="_blank">support here</a>.<br><br>
        Thank you for using our software!`, false);
    }

    this.nextStep = async function () {
        this.step = this.step === undefined ? 0 : this.step + 1;
        stepConfig = this.config.steps[this.step.toString()];

        // Next Step
        if (this.options === undefined || this.options.callback === undefined) {
            this.setupNextStep();
            return;
        }
        // Callback
        formFields = [];
        this.main.querySelectorAll('input,select').forEach(function (field) {
            if (field.tagName.toLowerCase() === 'select') {
                console.log(field.value);
                formFields.push({"id": field.id, "selected": field.value});
            } else if (field.type === 'radio') {
                formFields.push({"id": field.id, "checked": field.checked, "value": field.value, "name": field.name});
            } else {
                formFields.push({"id": field.id, "value": field.value});
            }
        });
        const fetchOptions = {
            method: 'POST',
            body: JSON.stringify({"form": formFields}),
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        }
        fetchUrl = `${(new URL(document.location)).origin}/?callback=${this.options.callback}`;
        const response = await fetch(fetchUrl, fetchOptions);
        headers = await response.headers;
        locationHeader = headers.get('Location');
        if (locationHeader !== null && locationHeader.length > 0) {
            window.location = locationHeader;
            return;
        }
        content = JSON.parse(await response.text());
        if (content.valid === false) {
            APP.show_alert('Error', content.message, true);
        } else {
            APP.setupNextStep();
        }
    }

    this.setupNextStep = function () {
        // Nav Progress
        if (this.step !== 0) {
            prevStep = this.navProgress.querySelector(`li:nth-child(${this.step})`);
            prevStep.classList.add('pass');
            prevStep.classList.remove('active');
        }
        this.navProgress.querySelector(`li:nth-child(${this.step + 1})`).classList.add('active');

        // Main
        windowConfig = stepConfig.window;
        this.main.style.width = windowConfig.width + 'px';
        this.main.style.height = windowConfig.height + 'px';
        this.mainCenter();
        this.options = windowConfig.options;
        this.main.querySelector('#title h2').textContent = windowConfig.title;

        fetchUrl = `${(new URL(document.location)).origin}/resources/template/${windowConfig.template}.html`;
        fetch(fetchUrl).then((resp) => resp.text()).then(function(content) {
            APP.main.querySelector('section#contents').innerHTML = content;
            (new Function(APP.main.querySelector('script').textContent))();
        });
    }
}