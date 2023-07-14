export {
    formatPrice,
    resolveSuffixPrice,
    readFileAsUrl,
    body,
    send,
    loadModal,
    submitPOSTModal
};

function formatPrice() {
    $(this).val($(this).val().replace(/[^0-9\.]/g, ''));

    let input = $(this).val();
    let countDot = 0;

    for (let i = 0; i < input.length; i++) {
        if (input.charAt(i) === ".") {
            countDot++;
            if (countDot > 1 || i === 0) {
                input = input.slice(0, i) + input.slice(i + 1);
            }
        }
    }

    $(this).val(input);
}

function resolveSuffixPrice() {
    let str = $(this).val();
    let length = str.length;

    if (length > 0) {
        let lastChar = str.charAt(length - 1);

        if (lastChar === ".") {
            str = str.slice(0, length - 1);
        }

        $(this).val(str);
    }
}

function readFileAsUrl(file) {
    return new Promise((resolve) => {
        let reader = new FileReader();

        reader.onload = function (event) {
            resolve(event.target.result);
        }

        reader.readAsDataURL(file);
    });
}

function body(html) {
    const pattern = /<body>[\n\s\S]+<\/body>/;
    const found = html.match(pattern);
    return found[0];
}

function send(method, url, data = null) {
    return new Promise((resolve, reject) => {
        let xhttp = new XMLHttpRequest();

        xhttp.onload = function () {
            resolve(xhttp.responseText);
        };

        xhttp.onerror = function () {
            reject(-1);
        };

        xhttp.open(method, url);

        if (data) {
            xhttp.send(data);
        } else {
            xhttp.send();
        }
    });
}

async function loadModal(url, modal) {
    return new Promise(async (resolve) => {
        let html = await send('GET', url);
        $(modal).html(body(html));
        resolve(html);
    });
}


async function submitPOSTModal(url, data, modal, callback = null) {
    let html = await send("POST", url, data);

    if (html == 1) {
        $(modal).modal('hide');
        $('#loader-modal').modal('setting', 'closable', 'false').modal('show');
        return;
    }

    $(modal).html(body(html));

    if(callback) {
        callback();
    }
}