const MAIN = document.querySelector('main');

window.addEventListener('hashchange', function() {
    var filename = document.location.hash.substring(1);
    content = window.localStorage.getItem(filename);
    if (content !== null) {
        MAIN.innerHTML = `<h3>&gt;&gt; ${filename}</h3><pre>${content}</pre>`;
        console.log(`Loaded ${filename} from cache...`);
        return;
    }

    fetchUrl = `${(new URL(document.location)).origin}/index.php?fetch=${filename}`;
    fetch(fetchUrl).then((resp) => resp.text()).then(function(content) {
        MAIN.innerHTML = `<h3>&gt;&gt; ${filename}</h3><pre>${content}</pre>`;
        window.localStorage.setItem(filename, content);
        console.log(`Loaded ${filename} from server...`);
    });
}, false);

if (MAIN.textContent.trim().length === 0) {
    if (document.location.hash.length > 0) {
        window.dispatchEvent(new HashChangeEvent("hashchange"));
    } else {
        document.location.hash = new URL(document.querySelector('nav a').href).hash;
    }
}