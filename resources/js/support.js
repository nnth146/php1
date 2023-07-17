export {
    formatPrice,
    resolveSuffixPrice,
    readFileAsUrl,
    body,
    send,
    isJson,
    resolveJson,
    getHtml
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

function send(method, url, data = null, useFormData = false) {
    return new Promise((resolve, reject) => {
        let settings = {
            type: method,
            url: url,
            data: data,
            success: function (response) {
                resolve(response);
            },
            error: function () {
                reject(-1);
            }
        };

        if(useFormData) {
            settings.processData = false;
            settings.contentType = false;
            settings.enctype = 'multipart/form-data';
        }

        $.ajax(settings);
    })
}

function getHtml(url) {
    return new Promise(async (resolve) => {
        let json = await send("GET", url);

        let html = isJson(json) ? JSON.parse(json).html : '';

        resolve(html);
    });
}

function isJson(str) {
    try {
        JSON.parse(str);
        return true;
    } catch (e) {
        return false;
    }
}

function resolveJson(result) {
    if (isJson(result)) {
        return JSON.parse(result).html;
    }

    return result;
}