import { readFileAsUrl, formatPrice, resolveSuffixPrice } from "./support.js";
import { redirect, submitPOST } from "./live.js";

$(function () {
    ready();

    document.addEventListener('ready', ready);
});

function ready() {
    $('#feature_image').on("change", previewFeatureImage);

    $('#gallery').on("change", previewGallery);

    $("#price").on("input", formatPrice);

    $("#price").on("change", resolveSuffixPrice);

    live();
}

function live() {
    $("#product-form").on("submit", submitPOST);
}

async function previewFeatureImage() {
    $('#feature_image-preview').empty();

    if (this.files[0] && this.files) {
        $("#feature_image-name").val(this.files[0].name);

        let urlBase64 = await readFileAsUrl(this.files[0]);

        let img = document.createElement("img");
        $(img).attr("src", urlBase64);
        $(img).attr("class", "ui medium image");

        $('#feature_image-preview').append(img);
    } else {
        $("#feature_image-name").val("");
    }
}

async function previewGallery() {
    $('#gallery-preview').empty();

    let name = [];
    if (this.files[0] && this.files) {
        for (let file of this.files) {
            name.push(file.name);

            let urlBase64 = await readFileAsUrl(file);

            let img = document.createElement("img");
            $(img).attr("src", urlBase64);
            $(img).attr("class", "ui small image");

            $('#gallery-preview').append(img);
        }
        $("#gallery-name").val(name.join(", "));
    } else {
        $("#gallery-name").val("");
    }
}