var currentPosition = {};
var showPurchaseList = false;

var menuItems;

var selectedItems = {
    condition_do_not_change: true,
    purchase_do_not_change: true,
    sale_do_not_change: true
};

function init_menu_contextuel() {
    menuItems = {
        condition_do_not_change: {name: l10n_gerer.etat_conserver_etat_actuel},
        condition__non_possede: {name: l10n_gerer.etat_marquer_non_possede, icon: "condition_missing"},
        condition_indefini: {name: l10n_gerer.etat_marquer_possede, icon: "condition_possessed"},
        condition_mauvais: {name: l10n_gerer.etat_marquer_mauvais_etat, icon: "condition_bad"},
        condition_moyen: {name: l10n_gerer.etat_marquer_etat_moyen, icon: "condition_notsogood"},
        condition_bon: {name: l10n_gerer.etat_marquer_bon_etat, icon: "condition_good"},
        sep1: "---------",
        purchase_do_not_change: {name: l10n_gerer.achat_conserver_date_achat},
        purchase_link: {
            name: l10n_gerer.achat_associer_date_achat,
            icon: "purchase_link"
        },
        'purchase_-1': {name: l10n_gerer.achat_desassocier_date_achat, icon: "purchase_unlink"},
        sep2: "---------",
        sale_do_not_change: {name: l10n_gerer.vente_conserver_volonte_vente},
        sale_1: { name: l10n_gerer.vente_marquer_a_vendre, icon: "sale_for_sale" },
        sale_0: {name: l10n_gerer.vente_marquer_pas_a_vendre, icon: "sale_not_for_sale"},
        sep3: "---------",
        save: {name: l10n_gerer.enregistrer_changements, icon: "save", className: "save", callback: updateSelectedIssues}
    };

    jQuery('body').mousemove(function(e) {
        currentPosition = {x: e.pageX, y: e.pageY};
    });

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
                position: function(opt){
                    var top = currentPosition.y - 10
                            - Math.max(0, (currentPosition.y + opt.$menu.height()) - (jQuery(window).scrollTop() + jQuery(window).height()));
                    opt.$menu.css({top: top, left: currentPosition.x});
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
                        jQuery('<h5>').text(l10n_gerer.achat_nouvelle_date_achat)
                    )
                    .append(
                        jQuery('<form>').addClass('new_purchase cache')
                            .append(jQuery('<input>', {name: 'title', type: 'text', size: 30, maxlength: 30, placeholder: l10n_gerer.achat_description}).prop('required', true).addClass('form-control'))
                            .append(jQuery('<input>', {name: 'date', type: 'text', size: 30, maxlength: 10, placeholder: l10n_gerer.achat_date_achat, readonly: 'readonly'}).prop('required', true).addClass('form-control'))
                            .append(jQuery('<input>', {type: 'submit'}).addClass('btn btn-default').val(l10n_gerer.creer))
                            .append(jQuery('<button>').addClass('btn btn-default cancel').text('Annuler'))
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

    menuItems.purchase_link.items = purchase_items;
    jQuery.each(menuItems, function(key, item) {
        item.className = (item.className || '').replace('selected', '');
        if (selectedItems[key]) {
            item.className+=' selected';
        }
        item.disabled = function() { return get_nb_numeros_selectionnes() === 0 };
    });
    return menuItems;
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
        alert(l10n_gerer.achat_specifiez_date);
    }
    else {
        jQuery.post('Database.class.php', {
            database: 'true',
            acquisition: 'true',
            date: values.date,
            description: values.title
        }, function (data) {
            if (data === 'Date') {
                alert(l10n_gerer.achat_date_achat_existe);
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