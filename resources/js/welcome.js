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

    live();
}

function live() {
    $('a').on('click', redirect);

    $('form[name=deleteproduct-form]').on('submit', submitPOST);

    $('#filter-form').on('submit', submitGET);

    resolveSync();
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

async function updateScreen() {
    let html = await send("GET", window.location.href);

    $(document.body).html(body(html));

    document.dispatchEvent(new Event('ready'));
}

async function getProductLinks() {
    return new Promise(async (resolve) => {
        let json = await send("GET", "?action=fetchLinks");
        let links = JSON.parse(json).result;
        resolve(links);
    });
}

async function getProductFromLink(link) {
    return new Promise(async (resolve) => {
        let data = new FormData();
        data.set('link', link);

        let json = await send('POST', '?action=syncData', data);
        let product = JSON.parse(json).result;
        resolve(product);
    });
}

function resolveSync() {
    let sync;

    //restore state of sync
    if(typeof SyncVillatheme.instance === 'object') {
        sync = SyncVillatheme.instance;
        sync.restore();
    }else {
        sync = new SyncVillatheme();
        SyncVillatheme.instance = sync;
    }

    $('#sync-btn').on('click', async function (event) {
        event.preventDefault();

        $('#sync-modal').modal({
            closable: false,
            onApprove: function () {
                return false;
            },
            detachable: false,
        }).modal('show');

        if (!sync.links && sync.enable) {
            sync.finding();

            sync.links = await getProductLinks();

            sync.donefind();
        }
    });

    $('#modal-sync-btn').on('click', async function () {
        if(sync.isCompleted) {
            $('#sync-modal').modal('hide');
        }

        if (sync.links && sync.links.length > 0 && sync.enable && !sync.isCompleted) {
            sync.syncing();

            for (let link of sync.links) {
                if (sync.stop) {
                    sync.stopped();
                    return;
                }

                let product = await getProductFromLink(link);

                if (product) {
                    sync.increment();
                }
            }

            sync.donesync();
        }

        if (sync.status == 'syncing') {
            sync.stopping();
        }
    });

    $('#modal-reset-btn').on('click', async function () {
        if (sync.enable) {
            sync.reset();
        }

        if (!sync.links && sync.enable) {
            sync.finding();

            sync.links = await getProductLinks();

            sync.donefind();
        }
    });
}

class SyncVillatheme {
    static instance;
    constructor(status = 'rest', links = null, current = 0, stop = false) {
        this.status = status; // finding, donefind, syncing, donesync, stopping, stopped, rest
        this.links = links;
        this.current = current; // Số lượng product đã lấy được
        this.stop = stop;
        this.ui = new SyncUI();
    }
    get total() {
        return this.links == undefined ? null : this.links.length;
    }
    get enable() {
        return ['donefind', 'donesync', 'rest', 'stopped'].some((value) => value == this.status);
    }
    get isCompleted() {
        return this.total === this.current;
    }
    finding() {
        this.status = 'finding';
        this.ui.setState('finding');
    }
    donefind() {
        this.status = 'donefind';
        this.ui.setState('donefind', { total: this.total });
    }
    syncing() {
        this.status = 'syncing';
        this.ui.setState('syncing');
    }
    donesync() {
        this.status = 'donesync';
        this.ui.setState('donesync', { total: this.total });
    }
    stopping() {
        this.status = 'stopping';
        this.stop = true;
        this.ui.setState('stopping');
    }
    stopped() {
        this.status = 'stopped';
        this.stop = false;
        this.ui.setState('stopped');
    }
    reset() {
        this.status = 'rest';
        this.links = null;
        this.current = 0;
        this.stop = false;
        this.ui.setState('rest');
    }
    increment() {
        this.current++;
        this.ui.incrementSyncProgress(this.current);
    }
    restore() {
        this.finding();
        this.donefind();

        switch(this.status) {
            case 'syncing': this.syncing(); this.ui.incrementSyncProgress(this.current); break;
            case 'donesync': this.syncing(); this.donesync(); this.ui.incrementSyncProgress(this.current); break;
            case 'stopping': this.stopping(); this.ui.incrementSyncProgress(this.current); break;
            case 'stopped' : this.stopping(); this.stopped(); this.ui.incrementSyncProgress(this.current); break;
        }
    }
}

class SyncUI {
    find = {
        progress: '#find-progress',
        loader: '#find-loader',
        label: '#find-label'
    };
    sync = {
        progress: '#sync-progress',
        loader: '#sync-loader',
        label: '#sync-label'
    };
    button = {
        main: '#sync-btn',
        reset: '#modal-reset-btn',
        sync: '#modal-sync-btn',
        cancel: '#modal-cancel-btn'
    }
    setState(state, sync = null) {
        switch (state) {
            case 'finding': this.finding(); break;
            case 'donefind': this.donefind(sync); break;
            case 'syncing': this.syncing(); break;
            case 'donesync': this.donesync(sync); break;
            case 'stopping': this.stopping(); break;
            case 'stopped': this.stopped(); break;
            default: this.rest(); break;
        }
    }
    rest() {
        this._deactiveLoader([this.find.loader, this.sync.loader]);

        this._resetProgress([this.find.progress, this.sync.progress]);
        this._setProgressState([this.find.progress, this.sync.progress], 'active');
        this._setProgressLabel([this.find.progress], ' ');
        this._setProgressLabel([this.sync.progress], ' ');

        this._setStateButton(this.button.sync, 'default');

        $(this.button.main).text('Syncfrom Villatheme');
    }
    finding() {
        this._activeLoader([this.find.loader]);
        this._setProgressLabel([this.find.progress], 'Please wait to find products...');

        $(this.button.reset).hide();
    }
    donefind(sync) {
        this._deactiveLoader([this.find.loader]);
        this._setProgressLabel([this.find.progress], `${sync.total} products found`)

        $(this.find.progress).progress({ percent: '100' });
        $(this.sync.progress).progress({ total: sync.total, value: 0 });

        $(this.button.reset).show();
    }
    syncing() {
        this._activeLoader([this.sync.loader]);
        this._setProgressLabel([this.sync.progress], 'Please wait to sync products...');
        this._setStateButton(this.button.sync, 'running');

        $(this.button.reset).hide();

        $(this.button.main).addClass('loading');
    }
    donesync(sync) {
        this._deactiveLoader([this.sync.loader]);
        this._setProgressLabel([this.sync.progress], `${sync.total} products synced`)
        this._setStateButton(this.button.sync, 'done');

        $(this.button.reset).show();

        $(this.button.main).removeClass('loading');
    }
    stopping() {
        this._activeLoader([this.sync.loader]);
        this._setProgressLabel([this.sync.progress], 'Stopping sync...');
    }
    stopped() {
        this._deactiveLoader([this.sync.loader]);
        this._setProgressLabel([this.sync.progress], 'Sync stopped');
        this._setStateButton(this.button.sync, 'stopping');

        $(this.button.reset).show();

        $(this.button.main).removeClass('loading');
    }
    incrementSyncProgress(value) {
        $(this.sync.progress).progress('set progress', value);
        $(this.button.main).text($(this.sync.progress).progress('get percent') + '%');
    }
    _deactiveLoader(arr) {
        for (let selector of arr) {
            $(selector).removeClass('active');
        }
    }
    _activeLoader(arr) {
        for (let selector of arr) {
            $(selector).addClass('active');
        }
    }
    _resetProgress(arr) {
        for (let progress of arr) {
            $(progress).progress({ percent: '0' });
            $(progress).progress('set progress', '0');
        }
    }
    _setProgressLabel(arr, value) {
        for (let selector of arr) {
            $(selector).progress('set label', value);
        }
    }
    _setProgressState(arr, state) {
        for (let selector of arr) {
            $(selector).progress(`set ${state}`);
        }
    }
    _setStateButton(button, state, color = 'red', defaultText = 'Sync') {
        switch (state) {
            case 'running':
                $(button).text('Stop');
                $(button).addClass(color);
                break;
            case 'stopping':
                $(button).text('Resume');
                $(button).removeClass(color);
                break;
            case 'done':
                $(button).text('Done');
                $(button).removeClass(color);
                break;
            default:
                $(button).text(defaultText);
                $(button).removeClass(color);
                break;
        }
    }

}