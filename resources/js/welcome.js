import { formatPrice, resolveSuffixPrice } from "./support.js";

$(function () {
    restoreDefaultIfSelectedAgain("#category");
    restoreDefaultIfSelectedAgain("#tag");

    resolvePrice();

    $("div[name=delete-btn]").on("click", function () {

    });

    $('#search-btn').on("click", function () {
        $(this).parents("form").trigger("submit");
    });
});

function restoreDefaultIfSelectedAgain(dropdownSelector) {
    $(dropdownSelector).dropdown({
        action: function (text, value) {
            if ($(this).find('input').first().val() == value) {
                $(this).dropdown('remove selected', value);
                $(this).dropdown('restore placeholder text');
            } else {
                $(this).dropdown('set selected', value);
            }
            $(this).dropdown('hide');
        }
    })
}

function resolvePrice() {
    $("input[name=pricefrom]").on("input", formatPrice);
    $("input[name=pricefrom]").on("change", resolveSuffixPrice);

    $("input[name=priceto]").on("input", formatPrice);
    $("input[name=priceto]").on("change", resolveSuffixPrice);
}