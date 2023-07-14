import { loadModal, submitPOSTModal } from "./support.js";

$(async function () {
    ready();

    document.addEventListener('ready', ready);
});

function ready() {
    $('#addproperty-btn').on('click', resolvePropertyModal);
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

async function resolvePropertyModal(e) {
    e.preventDefault();

    await loadModal($(this).attr('href'), '#properties-modal');

    config();

    $('#properties-modal').modal({
        detachable: false, onHidden: function () {
            $('#filter-form').trigger('submit'); //update screen
            $('#loader-modal').modal('hide');
        }
    }).modal('show');
}
