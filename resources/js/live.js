import { body, send } from "./support.js";

export {redirect, submitPOST, submitGET};

async function redirect(e) {
    e.preventDefault();
    let html = await send('GET', $(this).attr('href'));

    $('body').html(body(html));

    document.dispatchEvent(new Event('ready'));
}

async function submitPOST(e) {
    e.preventDefault();
    let data = new FormData(this);

    let html = await send("POST", $(this).attr('action'), data);

    $('body').html(body(html));

    document.dispatchEvent(new Event('ready'));
}

async function submitGET(e) {
    e.preventDefault();
    let data = $(this).serialize();

    let action = $(this).attr('action');
    if(!action) {
        action = '?';
    }

    let html = await send("GET", action + data);

    $('body').html(body(html));

    document.dispatchEvent(new Event('ready'));
}
