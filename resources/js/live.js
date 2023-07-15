import { body, send, resolveJson } from "./support.js";

export { redirect, submitPOST, submitGET, submitPOSTModal };

async function redirect(e) {
    e.preventDefault();

    if ($(this).attr('href')) {
        let result = await send('GET', $(this).attr('href'));

        $('body').html(body(resolveJson(result)));

        document.dispatchEvent(new Event('ready'));
    }
}

async function submitPOST(e) {
    e.preventDefault();
    let data = new FormData(this);

    let result = await send("POST", $(this).attr('action'), data, true);

    $('body').html(body(resolveJson(result)));

    document.dispatchEvent(new Event('ready'));
}

async function submitPOSTModal(url, data, modal, callback = null) {
    let result = await send("POST", url, data, true);

    let obj = JSON.parse(result);

    if (obj.result == 'success') {
        await new Promise((resolve) => {
            $(modal).modal({
                onHidden: function () {
                    resolve('hide');
                }
            }).modal('hide');
        });

        $('body').html(obj.html);

        document.dispatchEvent(new Event('ready'));
        return;
    } else {
        $(modal).html(body(obj.html));
    }

    if (callback) {
        callback();
    }
}

async function submitGET(e) {
    e.preventDefault();
    let data = $(this).serialize();

    let action = $(this).attr('action');
    if (!action) {
        action = '?';
    }

    let result = await send("GET", action + data);

    $('body').html(body(resolveJson(result)));

    document.dispatchEvent(new Event('ready'));
}
