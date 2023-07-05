import { readFileAsUrl, formatPrice, resolveSuffixPrice } from "./support.js";

$(function () {
    $('#feature_image').on("change", async function () {
        $('#feature_image-preview').empty();

        if (this.files[0] && this.files) {
            $("#feature_image-name").val(this.files[0].name);

            let urlBase64 = await readFileAsUrl(this.files[0]);

            let img = document.createElement("img");
            $(img).attr("src", urlBase64);
            $(img).attr("class", "ui medium image");

            $('#feature_image-preview').append(img);
        }else{
            $("#feature_image-name").val("");
        }
    });

    $('#gallery').on("change", async function () {
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
        }else {
            $("#gallery-name").val("");
        }
    });

    $("#price").on("input", formatPrice);
    $("#price").on("change", resolveSuffixPrice);
});