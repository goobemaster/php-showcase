const SECTION = {
    REQ: 0,
    RES: 1
}
const NEW_HEADER_ROW = function (section) { return `<tr role="new"><td><input type="text"></td><td><input type="text"></td><td><button onclick="APP.new_resource_add_header(SECTION.${section});"><span>+</span></button></td></tr>`; }
const WEB_APP_URL = new URL(document.location);
const MOCK_SERVER_URL = `${WEB_APP_URL.protocol}//${WEB_APP_URL.hostname}:8010/`;

const APP = new function() {
    this.navResources = document.querySelector('main nav ul');
    this.registeredForm = document.querySelector('main form#registered');
    this.resourceForm = document.querySelector('main form#resource');
    this.reqMethod = this.resourceForm.querySelector('#req_method');
    this.reqPath = this.resourceForm.querySelector('#req_path');
    this.reqBody = this.resourceForm.querySelector('#req_body');
    this.reqHeaders = this.resourceForm.querySelector('#req_headers');            
    this.resCode = this.resourceForm.querySelector('#res_code');
    this.resBody = this.resourceForm.querySelector('#res_body');
    this.resHeaders = this.resourceForm.querySelector('#res_headers'); 
    
    this.registeredForm.style.display = 'none';
    this.resourceForm.style.display = 'none';
    savedResources = window.localStorage.getItem('resources');
    if (savedResources !== null) {
        resources = JSON.parse(savedResources);
        for (const hash in resources) {
            if (!resources.hasOwnProperty(hash)) continue;
            this.navResources.innerHTML += `<li><a href="javascript:;" onclick="APP.show_registered_form(this);">${hash}</li>`;
        }
    }

    this.show_new_request_form = function () {
        this.resourceForm.style.display = 'block';
        this.registeredForm.style.display = 'none';

        this.reqMethod.value = 'Get';
        this.reqPath.value = '';
        this.reqBody.value = '';
        this.reqHeaders.querySelectorAll('tbody tr').forEach(e => e.parentNode.removeChild(e));
        this.reqHeaders.querySelector('tbody').innerHTML = NEW_HEADER_ROW('REQ');

        this.resCode.value = '200';
        this.resBody.value = '';
        this.resHeaders.querySelectorAll('tbody tr').forEach(e => e.parentNode.removeChild(e));
        this.resHeaders.querySelector('tbody').innerHTML = NEW_HEADER_ROW('RES');
    }

    this.new_resource_add_header = function (section) {
        rows = (section === SECTION.REQ ? this.reqHeaders : this.resHeaders).querySelectorAll('tbody tr');
        new_row = rows[rows.length - 1];
        headerName = new_row.querySelector('td:nth-child(1) input');
        headerValue = new_row.querySelector('td:nth-child(2) input');
        if (headerName.value.length === 0 || headerValue.value.length === 0) return;
        tbody = (section === SECTION.REQ ? this.reqHeaders : this.resHeaders).querySelector('tbody');
        tbody.innerHTML = `<tr role="header"><td><input type="text" value="${headerName.value}"></td><td><input type="text" value="${headerValue.value}"></td><td><button onclick="this.parentNode.parentNode.remove();"><span>x</span></button></td></tr>` + tbody.innerHTML;
    }

    this.hide_forms = function () {
        this.registeredForm.style.display = 'none';
        this.resourceForm.style.display = 'none';
    }

    this.save_resource = function () {
        requestMethod = this.reqMethod.value;
        requestPath = this.reqPath.value;
        requestBody = this.reqBody.value;
        var requestHeaders = {};
        var mandatoryHeaders = {
            'Host': false,
            'Accept': false
        };
        this.reqHeaders.querySelectorAll('tbody tr[role=header]').forEach(function (e) {
            headerName = e.querySelector('td:nth-child(1) input').value;
            requestHeaders[headerName] = e.querySelector('td:nth-child(2) input').value;
            if (Object.keys(mandatoryHeaders).includes(headerName)) {
                mandatoryHeaders[headerName] = true;
            }
        });
        console.log(mandatoryHeaders);
        for (const m in mandatoryHeaders) {
            if (mandatoryHeaders[m] === false) {
                alert('One or more mandatory request header is missing!');
                return;
            }
        }

        responseCode = this.resCode.value;
        responseBody = this.resBody.value;
        var responseHeaders = {};
        this.resHeaders.querySelectorAll('tbody tr[role=header]').forEach(function (e) {
            responseHeaders[e.querySelector('td:nth-child(1) input').value] = e.querySelector('td:nth-child(2) input').value;
        });

        if (requestPath.length === 0) {
            alert('The shortest acceptable request path is the root (forward slash)!')
            return;
        }
        if (requestPath.charAt(0) !== '/') {
            alert('The request path must start at the root (forward slash)!');
            return;
        }
        
        var bodyObject = {
            req_headers: requestHeaders,
            req_body: requestBody,
            req_path: requestPath,
            res_code: parseInt(responseCode),
            res_headers: responseHeaders,
            res_body: responseBody
        };
        const options = {
            method: 'POST',
            body: JSON.stringify(bodyObject),
            headers: {
                'Content-Type': 'text/plain',
                'Accept': 'application/json'
            }
        }
    
        fetch(`${MOCK_SERVER_URL}Add/${requestMethod}Request`, options).then((resp) => resp.text()).then(function(content) {
            const response = JSON.parse(content);
            if (!response.hasOwnProperty('hash')) alert('Bad response from the Mock Server! Is it running?');
            savedResources = window.localStorage.getItem('resources');
            if (savedResources === null) {
                resources = {};
            } else {
                resources = JSON.parse(savedResources);
            }
            if (!resources.hasOwnProperty(response.hash)) {
                resources[response.hash] = bodyObject;
                window.localStorage.setItem('resources', JSON.stringify(resources));
                APP.navResources.innerHTML += `<li><a href="javascript:;" onclick="APP.show_registered_form(this);">${response.hash}</a></li>`;
            }
            APP.registeredForm.style.display = 'none';
            APP.resourceForm.style.display = 'none';
        });        
    }

    this.show_registered_form = function (navLink) {
        this.resourceForm.style.display = 'none';
        savedResources = window.localStorage.getItem('resources');
        if (savedResources === null) {
            alert('Cannot find the saved resources in the storage!');
            return;
        }
        savedResources = JSON.parse(savedResources);
        if (!savedResources.hasOwnProperty(navLink.text)) {
            alert('Cannot find this particular resource in the storage!');
            return;
        }
        blockquote = this.registeredForm.querySelector('blockquote');
        blockquote.innerHTML = JSON.stringify(savedResources[navLink.text]);
        blockquote.setAttribute('data-hash', navLink.text);
        this.registeredForm.style.display = 'block';
    }

    this.delete_resource = function () {
        blockquote = this.registeredForm.querySelector('blockquote');
        var hash = blockquote.getAttribute('data-hash');
        if (hash === null || hash.length === 0) return;

        const options = {
            method: 'POST',
            body: JSON.stringify({req_hash: hash}),
            headers: {
                'Content-Type': 'text/plain',
                'Accept': 'application/json'
            }
        }

        fetch(`${MOCK_SERVER_URL}Remove`, options).then((resp) => resp.text()).then(function(content) {
            const response = JSON.parse(content);
            if (!response.hasOwnProperty('message')) alert('Bad response from the Mock Server! Is it running?');

            savedResources = window.localStorage.getItem('resources');
            if (savedResources !== null) {
                resources = JSON.parse(savedResources);
                if (resources.hasOwnProperty(hash)) {
                    delete resources[hash];
                    window.localStorage.setItem('resources', JSON.stringify(resources));
                    location.reload(true);
                }
            }
        });
    }
}