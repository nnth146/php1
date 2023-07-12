import { body, formatPrice, resolveSuffixPrice, send } from "./support.js";
import { redirect, submitPOST, submitGET } from "./live.js";

$(function () {
    ready();

    document.addEventListener('ready', ready);
});

function ready() {
    restoreDefaultIfSelectedAgain("#category");
    restoreDefaultIfSelectedAgain("#tag");

    resolvePrice();

    $(".mini.modal").modal({
        detachable: false,
    });

    $("div[name=delete-btn]").on("click", function () {
        $(this).siblings(".mini.modal").modal("show");
    });

    $('#search-btn').on("click", function () {
        $(this).parents("form").trigger("submit");
    });

    sync();

    live();
}

function live() {
    $('a').on('click', redirect);

    $('form[name=deleteproduct-form]').on('submit', submitPOST);

    $('#filter-form').on('submit', submitGET);
}

function sync() {
    let sync = {
        enable: false,
        links: null,
        status: 'done',
        count: 0,
        isStop: false,
        isComplete: false,
        isCanceling: false,
        find() {
            this.enable = false;
            this.status = 'finding';
        },
        load() {
            this.enable = false;
            this.status = 'loading';
            syncStateChange('loading');
        },
        done(isStateChange = true) {
            this.enable = true;
            this.status = 'done';

            if(isStateChange) {
                syncStateChange('done');
            }
        },
        increment() {
            this.count++;
        },
        reset() {
            syncStateChange('start');
            this.links = null;
            this.count = 0;
        },
        stopStarted() {
            this.isStop = true;
            progressChange('stopping');
        },
        stopDone() {
            this.done();
            this.resume();
            this.isCanceling = false;
            syncStateChange('stop');
        },
        resume() {
            this.isStop = false;
        },
        complete() {
            this.isComplete = true;
        },
        cancel() {
            this.isCanceling = true;
            this.stopStarted();
        },
        get total() {
            return this.links == null ? -1 : this.links.length;
        }
    };

    $('#sync-btn').on("click", async function (e) {
        e.preventDefault();

        sync.reset();

        sync.find();

        $('#sync-modal')
            .modal({
                closable: false,
                onApprove: function () {
                    if (sync.isComplete) {
                        return true;
                    }
                    return false;
                }
            }).modal('show');

        sync.links = await findProducts();

        $('#sync-progress').progress({ total: sync.total });

        sync.done(false);
    });

    $('#modal-sync-btn').on("click", async function () {
        if (sync.count == sync.total) {
            sync.complete();
            return;
        }

        console.log(sync.enable);

        if (sync.enable) {
            sync.load();

            for (let i = sync.count; i < sync.total; i++) {
                if (sync.isStop) {
                    sync.stopDone();
                    return;
                }

                let data = new FormData();
                data.set('link', sync.links[i]);

                let json = await send('POST', '?action=syncData', data);
                let product = JSON.parse(json).result;
                console.log(product);

                if (product) {
                    sync.increment();
                    $('#sync-progress').progress('increment');
                }
            }

            sync.done();

            updateScreen();
        }

        if (sync.status == 'loading') {
            sync.stopStarted();
        }
    });

    $('#modal-cancel-btn').on("click", function (e) {
        sync.cancel();

        updateScreen();
    });
}

async function updateScreen() {
    let html = await send("GET", window.location.href);

    $(document.body).html(body(html));

    document.dispatchEvent(new Event('ready'));
}

function syncStateChange(state) {
    syncButtonChange(state);
    progressChange(state);
}

function syncButtonChange(state) {
    let button = $('#modal-sync-btn');
    let stopColor = 'yellow';

    switch (state) {
        case 'loading':
            button.text('Stop');
            button.removeClass('positive');
            button.addClass(stopColor);
            break;
        case 'stop':
            button.text('Resume');
            button.removeClass(stopColor);
            button.addClass('positive');
            break;
        case 'done':
            button.removeClass(stopColor);
            button.addClass('positive');
            button.text('Done');
            break;
        default:
            button.text('Sync');
            button.removeClass(stopColor);
            if (!button.hasClass('positive')) {
                button.addClass('positive')
            };
            break;
    }
}

function progressChange(state) {
    switch (state) {
        case 'loading':
            $('#sync-loader').addClass('active');
            $('#sync-progress').progress('set label', 'loading');
            break;
        case 'stop':
            $('#sync-loader').removeClass('active');
            $('#sync-progress').progress('set label', 'stoped');
            break;
        case 'stopping':
            $('#sync-progress').progress('set label', 'stopping');
            break;
        case 'done':
            $('#sync-loader').removeClass('active');
            $('#sync-progress').progress('set label', 'done');
            break;
        default:
            $('#sync-progress').progress('set progress', '0');
            $('#sync-progress').progress('set label', null);
            $('#sync-loader').removeClass('active');
            break;

    }
}

function findProducts() {
    return new Promise(async (resolve) => {
        let progress = $('#find-progress');

        $('#find-loader').addClass('active');

        //reset state
        progress.progress({ percent: '0' });
        progress.progress('set label', 'finding products');
        progress.progress('set active');

        let json = await send("GET", "?action=fetchLinks");
        let links = JSON.parse(json).result;

        progress.progress({ percent: '100' });
        progress.progress('set label', links.length + ' products finded');

        $('#find-loader').removeClass('active');

        resolve(links);
    });
}

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