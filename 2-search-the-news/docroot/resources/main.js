var searchBox = document.querySelector('nav input');
var searchButton = document.querySelector('nav button');
var main = document.querySelector('main');

function redirectToResultsPage() {
    if (searchBox.value.length === 0) return;
    document.location = `${(new URL(document.location)).origin}/index.php?q=${searchBox.value}`;
}

searchBox.addEventListener('keypress', function (e) {
    if (e.keyCode == 13) redirectToResultsPage();
});

searchButton.addEventListener('click', event => { redirectToResultsPage(); });

searchBox.addEventListener('keyup', event => {
    const fetchUrl = `${(new URL(document.location)).origin}/index.php`;
    const options = {
        method: 'POST',
        body: `qt=${searchBox.value}`,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }

    fetch(fetchUrl, options).then((resp) => resp.text()).then(function(content) {
        const results = JSON.parse(content);
        if (Object.keys(results).length === 0) {
            main.style.display === 'none';
            return;
        }

        if (!main.classList.contains('top-results')) main.classList.add('top-results');
        topResultsContent = '';
        index = 0;
        for (const guid in results) {
            if (index === 3) break;
            title = results[guid].title;
            link = results[guid].link;
            published = (new Date(parseInt(results[guid].published) * 1000)).toLocaleString();
            topResultsContent += `<p><a href="${link}" target="_blank">${title}</a><br><small>${link}</small><small>${published}</small></p>`;
            index++;
        }
        main.querySelector('.col').innerHTML = topResultsContent;
        main.style.display === 'block';
    });
});