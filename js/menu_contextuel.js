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
                jQuery('<span>')
                    .append(
                        jQuery('<h5>').text('Create purchase')
                    )
                    .append(
                        jQuery('<form>').addClass('new_purchase cache')
                            .append(jQuery('<input>', {name: 'title', type: 'text', size: 30, maxlength: 30, placeholder: 'Purchase title'}).addClass('form-control'))
                            .append(jQuery('<input>', {name: 'date', type: 'text', size: 30, maxlength: 10, placeholder: 'Purchase date', readonly: 'readonly'}).addClass('form-control'))
                            .append(jQuery('<input>', {type: 'submit'}).addClass('btn').val('OK'))
                            .append(jQuery('<button>').addClass('btn cancel').text('Annuler'))
                ).html(),
            callback: onCreatePurchaseClick
        }
    };
    jQuery.each(liste_achats, function(i, purchase) {
        purchase_items['date_' + purchase.id] = {
            isHtmlName: true,
            icon: 'date_link_small',
            name: '<div>'+(['<b>'+purchase.description+'</b>', purchase.date].join('<br />')) + '</div>'
        };
    });

    var items = {
        condition_do_not_change: {name: "Do not change condition", className: "selected"},
        condition_possessed: {name: "Possessed", icon: "condition_possessed"},
        condition_bad: {name: "Bad condition", icon: "condition_bad"},
        condition_notsogood: {name: "Not-so-good condition", icon: "condition_notsogood"},
        condition_good: {name: "Good condition", icon: "condition_good"},
        sep1: "---------",
        date_do_not_change: {name: "Do not change purchase date", className: "selected"},
        date_link: {
            name: "Link with a purchase date",
            icon: "date_link",
            items: purchase_items
        },
        date_unlink: {name: "Unlink", icon: "date_unlink"},
        sep2: "---------",
        sale_do_not_change: {name: "Do not change sale status", className: "selected"},
        sale_for_sale: { name: "Mark as \"For sale\"", icon: "sale_for_sale" },
        sale_not_for_sale: {name: "Remove \"For sale\"", icon: "sale_not_for_sale"},
        sep3: "---------",
        save: {name: "Save changes", icon: "save", className: "save"}
    };
    jQuery.each(items, function(i, item) {
        item.disabled = function() { return get_nb_numeros_selectionnes() === 0 }
    });

    jQuery.contextMenu({
        selector: '#liste_numeros',
        className: 'data-context-menu-title',
        callback: onItemClick,
        items: items
    });

    var new_purchase_container = jQuery('form.new_purchase');

    new_purchase_container.on('submit', onSubmitNewPurchaseClick);
    new_purchase_container.find('.cancel').on('click', onCancelNewPurchaseClick);
    new_purchase_container.find('[name="date"]').datepicker({
        format: "yyyy-mm-dd",
        keyboardNavigation: false,
        maxViewMode: 2,
        autoclose: true,
        language: locale === 'fr' ? 'fr' : 'en-GB'
    });

    update_nb_numeros_selectionnes();
}
