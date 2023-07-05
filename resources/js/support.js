export {
    formatPrice, 
    resolveSuffixPrice,
    readFileAsUrl
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
    console.log(str);

    if (length > 0) {
        let lastChar = str.charAt(length - 1);

        if(lastChar === ".") {
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