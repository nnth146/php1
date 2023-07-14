import { readFileAsUrl, formatPrice, resolveSuffixPrice, loadModal, submitPOSTModal } from "./support.js";

$(function () {
    ready();

    document.addEventListener('ready', ready);
});

async function ready() {
    $('#addproduct-btn').on('click', resolveProductModal);

    $('a[id=editproduct-btn]').on('click', resolveProductModal);
}

function config() {
    $('.ui.dropdown').dropdown();

    $('#feature_image').on("change", previewFeatureImage);

    $('#gallery').on("change", previewGallery);

    $("#price").on("input", formatPrice);

    $("#price").on("change", resolveSuffixPrice);

    live();
}

function live() {
    $("#product-form").on("submit", async function (e) {
        e.preventDefault();

        submitPOSTModal($(this).attr('action'), new FormData(this), '#products-modal', config);
    });

    $('#cancel-btn').on('click', function (e) {
        e.preventDefault();
        $('#products-modal').modal('hide');
    });
}

async function resolveProductModal(e) {
    e.preventDefault();

    await loadModal($(this).attr('href'), '#products-modal');

    config();

    $('#products-modal').modal({
        detachable: false, onHidden: function () {
            $('#filter-form').trigger('submit'); //update screen
            $('#loader-modal').modal('hide');
        },
    }).modal('show');
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