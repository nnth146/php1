$(function () {
    ready();

    document.addEventListener('ready', ready);
});

function ready() {
    submitFilter();
    selectionPage();
    nextPage();
    prevPage();
}

function submitFilter() {
    $('form').on('submit', async function (e) {
        e.preventDefault();
        let datas = new FormData(this);
        let query = $(this).serialize();
        let html = await send("get", "/php1?" + query, datas);

        $('body').html(body(html));
        $('body').off('submit', 'form');

        document.dispatchEvent(new Event('ready'));
    });
}

function selectionPage() {

}

function nextPage() {

}

function prevPage() {

}

function send(method, url, data = null) {
    return new Promise((resolve) => {
        let xhttp = new XMLHttpRequest();
        xhttp.onload = function (e) {
            resolve(xhttp.responseText);
        }
        xhttp.open(method, url);
        xhttp.send(data);
    });
}

function body(html) {
    const pattern = /<body>[\n\s\S]+<\/body>/;
    const found = html.match(pattern);
    return found[0];
}