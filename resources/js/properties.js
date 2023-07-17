import { submitPOSTModal } from "./live.js";
import { getHtml, body } from "./support.js";

$(async function () {
    ready();

    document.addEventListener('ready', ready);
});

class AddPropertiesModal {
    static html;
}

function ready() {
    $('#addproperty-btn').on('click', openPropertyModal);
}

function config() {
    live();
}

function live() {
    $('#properties-form').on('submit', function (e) {
        e.preventDefault();

        submitPOSTModal($(this).attr('action'), new FormData(this), '#properties-modal', config);
    }); //live

    $('#back-btn').on('click', function (e) {
        e.preventDefault();
        $('#properties-modal').modal('hide');
    });
}

async function openPropertyModal(e) {
    e.preventDefault();

    if(!AddPropertiesModal.html) {
        AddPropertiesModal.html = await getHtml($(this).attr('href'));
    }

    $('#properties-modal').html(body(AddPropertiesModal.html));

    config();

    $('#properties-modal').modal('show');
}
