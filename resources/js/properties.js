import { submitPOST } from "./live.js";

$(function() {
    ready();

    document.addEventListener('ready', ready);
});

function ready() {
    $('form[name=properties-form]').on('submit', submitPOST); //live
}