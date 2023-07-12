$(function() {
    ready();

    document.addEventListener('ready', ready);
});

function ready() {
    $('.ui.dropdown').dropdown();
}