var showPurchaseList = false;

var items =  {
    condition_do_not_change: {name: "Do not change condition"},
    condition__non_possede: {name: "Missing", icon: "condition_missing"},
    condition_indefini: {name: "Possessed", icon: "condition_possessed"},
    condition_mauvais: {name: "Bad condition", icon: "condition_bad"},
    condition_moyen: {name: "Not-so-good condition", icon: "condition_notsogood"},
    condition_bon: {name: "Good condition", icon: "condition_good"},
    sep1: "---------",
    purchase_do_not_change: {name: "Do not change purchase date"},
    purchase_link: {
        name: "Link with a purchase date",
        icon: "purchase_link"
    },
    'purchase_-1': {name: "Unlink", icon: "purchase_unlink"},
    sep2: "---------",
    sale_do_not_change: {name: "Do not change sale status"},
    sale_1: { name: "Mark as \"For sale\"", icon: "sale_for_sale" },
    sale_0: {name: "Remove \"For sale\"", icon: "sale_not_for_sale"},
    sep3: "---------",
    save: {name: "Save changes", icon: "save", className: "save", callback: updateSelectedIssues}
};

var selectedItems = {
    condition_do_not_change: true,
    purchase_do_not_change: true,
    sale_do_not_change: true
};

function init_menu_contextuel() {
    jQuery.contextMenu({
        selector: '#liste_numeros',
        build: function() {
            update_nb_numeros_selectionnes();
            return {
                events: {
                    hide: function(contextMenu) {
                        selectedItems = {};
                        jQuery.each(contextMenu.items, function(key, item) {
                            selectedItems[key] = jQuery(item.$node).hasClass('selected');
                        })
                    },
                    show: function(contextMenu) {
                        update_nb_numeros_selectionnes();
                        if (showPurchaseList) {
                            contextMenu.items.purchase_link.$node.trigger('mouseover');
                            showPurchaseList = false;
                        }
                    }
                },
                className: 'data-context-menu-title',
                callback: onItemClick,
                items: getMenuItems()
            }
        }
    });
}

function reloadContextMenu(selector) {
    var bottom = selector.offset().top + selector.outerHeight(),
        left = selector.offset().left;
    selector.trigger('contextmenu:hide');
    jQuery('#liste_numeros').trigger(jQuery.Event('contextmenu', {pageX: left, pageY: bottom}));
}

function updateSelectedIssues(itemKey, context) {
    var options = {},
        pays = jQuery('#pays').text(),
        magazine = jQuery('#magazine').text();
    context.$menu.find('.selected').map(function() {
        var key_value = /([^_]+)_(.+)/.exec(jQuery(this).data().contextMenuKey).slice(1);
        options[key_value[0]] = key_value[1];
    });
    jQuery.post('Database.class.php', {
        database: 'true',
        update: 'true',
        pays: pays,
        magazine: magazine,
        list_to_update: context.$trigger.find('.num_checked').map(function() {
            return jQuery(this).attr('title')
        }).get().join(),
        etat: options.condition,
        id_acquisition: options.purchase,
        av: options.sale
    }, function() {
        window.location.replace("?action=gerer&onglet=ajout_suppr&onglet_magazine="+pays+"/"+magazine);
    });
}

function getMenuItems() {
    var purchase_items = {
        'purchase_new': {
            className: 'new_purchase',
            isHtmlName: true,
            name:
                jQuery('<span>')
                    .append(
                        jQuery('<h5>').text('Create purchase')
                    )
                    .append(
                        jQuery('<form>').addClass('new_purchase cache')
                            .append(jQuery('<input>', {name: 'title', type: 'text', size: 30, maxlength: 30, placeholder: 'Purchase title'}).prop('required', true).addClass('form-control'))
                            .append(jQuery('<input>', {name: 'date', type: 'text', size: 30, maxlength: 10, placeholder: 'Purchase date', readonly: 'readonly'}).prop('required', true).addClass('form-control'))
                            .append(jQuery('<input>', {type: 'submit'}).addClass('btn').val('OK'))
                            .append(jQuery('<button>').addClass('btn cancel').text('Annuler'))
                    ).html(),
            callback: onCreatePurchaseClick
        }
    };
    jQuery.each(liste_achats, function(i, purchase) {
        purchase_items['purchase_' + purchase.id] = {
            isHtmlName: true,
            icon: 'purchase_link_small',
            name: '<div>'+(['<b>'+purchase.description+'</b>', purchase.date].join('<br />')) + '</div>'
        };
    });

    items.purchase_link.items = purchase_items;
    jQuery.each(items, function(key, item) {
        item.className = (item.className || '').replace('selected', '');
        if (selectedItems[key]) {
            item.className+=' selected';
        }
        item.disabled = function() { return get_nb_numeros_selectionnes() === 0 };
    });
    return items;
}

function onItemClick(itemId, context, e) {
    var target = jQuery(e.currentTarget).closest('li.context-menu-item');
    var group = itemId.substr(0, itemId.indexOf('_'));
    target.closest('.context-menu-root')
        .find('li.context-menu-item')
        .filter(function() {
            return jQuery(this).data().contextMenuKey.indexOf(group + '_') !== -1
        })
        .removeClass('selected');

    target.addClass('selected');

    return false;
}

function onCreatePurchaseClick() {
    var new_purchase_container = jQuery('form.new_purchase');
    new_purchase_container
        .removeClass('cache')
        .off('submit').on('submit', onSubmitNewPurchaseClick);
    new_purchase_container.find('.cancel').off('click').on('click', onCancelNewPurchaseClick);
    new_purchase_container.find('[name="date"]').datepicker({
        format: "yyyy-mm-dd",
        keyboardNavigation: false,
        maxViewMode: 2,
        autoclose: true,
        language: locale === 'fr' ? 'fr' : 'en-GB'
    });

    return false;
}

function onSubmitNewPurchaseClick(e) {
    var values = jQuery('.new_purchase').serializeArray().reduce(function(m,o){ m[o.name] = o.value; return m;}, {});
    if (values.date === '') {
        alert('Spécifiez une date');
    }
    else {
        jQuery.post('Database.class.php', {
            database: 'true',
            acquisition: 'true',
            date: values.date,
            description: values.title
        }, function (data) {
            if (data === 'Date') {
                alert('Cette date d\'achat existe déjà! Changez la date d\'achat ou son titre');
            }
            else {
                showPurchaseList = true;
                get_achats(function() {
                    reloadContextMenu(jQuery('.context-menu-list'));
                })
            }
        });
    }
    e.preventDefault();
}

function onCancelNewPurchaseClick() {
    jQuery('form.new_purchase').addClass('cache');
    return false;
}