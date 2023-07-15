import { loadModal } from "./support.js";
import { submitPOSTModal } from "./live.js";

$(async function () {
    ready();

    document.addEventListener('ready', ready);
});

function ready() {
    $('#addproperty-btn').on('click', openPropertyModal);
}

function config() {
    live();
}

function live() {
    $('form[name=properties-form]').on('submit', async function (e) {
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

    if ($('#properties-modal').is(':empty')) {
        await loadModal($(this).attr('href'), '#properties-modal');

        config();
    }

    $('#properties-modal').modal('show');
}
