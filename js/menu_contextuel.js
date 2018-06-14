function init_menu_contextuel() {
    var onItemClick = function(itemId, context, e) {
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
    };

    var onCreatePurchaseClick = function() {
        jQuery('form.new_purchase').removeClass('cache');
        return false;
    };

    var onSubmitNewPurchaseClick = function(e) {
        alert('Submit new purchase');
        e.preventDefault();
    };

    var onCancelNewPurchaseClick = function() {
        jQuery('form.new_purchase').addClass('cache');
        return false;
    };

    var purchase_items = {
        'date_new': {
            className: 'new_purchase',
            isHtmlName: true,
            name:
            'New purchase<span>' +
            jQuery('<span>').append(
                jQuery('<form>').addClass('new_purchase cache')
                    .append(jQuery('<input>', {type: 'text', size: 30, maxlength: 30, placeholder: 'Purchase title'}))
                    .append(jQuery('<input>', {type: 'text', size: 30, maxlength: 10}).val(moment().format('YYYY-MM-DD')))
                    .append(jQuery('<input>', {type: 'submit'}).val('OK'))
                    .append(jQuery('<button>').addClass('cancel').text('Annuler'))
            ).html()
            +'</span>',
            callback: onCreatePurchaseClick
        }
    };
    jQuery.each(liste_achats, function(i, purchase) {
        purchase_items['date_' + purchase.id] = {
            isHtmlName: true,
            name: '<span>'+([purchase.title, purchase.date].join('<br />')) + '</span>'
        };
    });

    var items = {
        "condition_do_not_change": {"name": "Do not change", "icon": "edit"},
        "condition_bad": {"name": "Bad condition", "icon": "edit"},
        "sep1": "---------",
        "date_link": {
            "name": "Link with a purchase date",
            "items": purchase_items
        },
        "date_unlink": {"name": "Unlink", "icon": "quit"},
        "sep2": "---------",
        "save": {"name": "Save changes", "icon": "quit", "className": "save"}
    };

    jQuery.contextMenu({
        selector: '#liste_numeros',
        className: 'data-context-menu-title',
        callback: onItemClick,
        items: items
    });

    jQuery('form.new_purchase')
        .on('submit', onSubmitNewPurchaseClick)
        .find('.cancel').on('click', onCancelNewPurchaseClick);

    update_nb_numeros_selectionnes();
}
