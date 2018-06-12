/**
 * @description		prototype.js based context menu
 * @author        Juriy Zaytsev; kangax [at] gmail [dot] com; http://thinkweb2.com/projects/prototype/
 * @version       0.6
 * @date          12/03/07
 * @requires      prototype.js 1.6
*/
var achats_affiches=false;
var l10n=['date_question','date_invalide','description_question','selectionner_date_achat',
    'description','description_invalide','acquisition_existante','mise_a_jour'];
var protos=[];
var l10n_items;

var Proto = { };

Proto.Menu = Class.create({
    initialize: function() {
        l10n_items=[];
        var body = jQuery('#body');

        var e = Prototype.emptyFunction;
        this.options = Object.extend({
            selector: '.contextmenu',
            className: 'protoMenu',
            pageOffset: 25,
            fade: false,
            zIndex: 100,
            beforeShow: e,
            beforeHide: e,
            beforeSelect: e
        }, arguments[0] || { });

        this.options.fade = this.options.fade && !Object.isUndefined(Effect);

        this.container = jQuery('<div>', { id:this.getId('menu_contextuel') })
            .addClass(this.options.className)
            .css({display:'none'});

        var entete=jQuery('<div>',{ id:this.getId('entete') })
            .css({'text-align': 'center', 'font-size':'11px'})
            .addClass('entete');

        switch (this.options.type) {
            case 'gestion_numeros' :
                var span_nb_selectionnes=jQuery('<span>',{ id:'nb_selection' }).text('0');
                var texte_span_nb_selectionnes=jQuery('<span>',{ id:'numero_selectionne' });
                var texte_span_nb_selectionnes_pl=jQuery('<span>',{ id:'numeros_selectionnes'})
                    .css({dispay:'none'});
                entete
                    .append(span_nb_selectionnes)
                    .append(' ')
                    .append(texte_span_nb_selectionnes)
                    .append(texte_span_nb_selectionnes_pl);
                break;
        }
        var list = jQuery('<ul>');
        jQuery.each(this.options.menuItems, function(index, item) {
            l10n_items[index]=item.groupName;
            if (!(item.separator)) {
                var contenu_lien=item.groupName+(item.subMenu?'&nbsp;&gt;&gt;':'');
                switch (this.options.type) {
                    case 'gestion_numeros' :
                        var name= item.groupName;
                        var groupname_name=item.groupName.split(new RegExp("_", "g"));
                        contenu_lien=jQuery('<span>',{
                            name:item.groupName.substring(groupname_name[0].length+1,item.groupName.length)
                        });
                        item.groupName=groupname_name[0];
                        break;
                }
            }
            var lien=jQuery('<a>', {
	            id: 'item' + index,
	            href: 'javascript:return false;',
	            name: name
            }).css({ display: item.className==="date2"||item.className==="n_date"?'none':''})
                .addClass(
                    (item.subMenu?'sous_menu ':'')
                    +(item.className || '')
                    +(item.disabled ? ' disabled' : (item.selected?' enabled selected':' enabled'))
                );

            list
                .append(jQuery('<li>').addClass(item.separator ? 'separator' : ''))
                .append(
                    item.separator
                    ? ''
                    : Object.extend(lien, { _callback: item.callback })
                .on('mouseover', this.onMouseOver.bind(this))
                .on('click', this.onClick.bind(this))
                .on('contextmenu', Event.stop)
                .html(contenu_lien.html()
                .append(typeof (item.nextSpanName) !== 'undefined'
                    ? jQuery('<span>',{ name:item.nextSpanName })
                    : '')));
        });
        l10n_action('remplirSpanName',l10n_items);

        jQuery('body')
            .append(this.container.append(entete).append(list).on('contextmenu', Event.stop))
            .on('click', function(e) {
                var target = jQuery(this);
            if (target.prop('tagName') ==='BUTTON' || (typeof prevent_click !== 'undefined' && prevent_click)) {
                prevent_click=false;
                return;
            }
            switch (this.options.type) {
                case 'gestion_numeros' :
                    if (this.container.visible() && e.which !== 3 && target.attr('name') !=='achat' && target.prop('tagName') !== 'INPUT') {
                        this.cacher_tous_menus(e);

                    }
                    else if (target.attr('name')==='achat') {
	                    target.addClass('selected');
                        jQuery('.date,.pas_date,.non_date').each(function() {
                            jQuery(this).removeClass('selected');
                        });
	                    jQuery('.sous_menu.date').addClass('selected');
                    }
                break;
            }
        });

        switch (this.options.type) {
            case 'gestion_numeros':
	            jQuery(this.options.selector).on('contextmenu', function(e){
                    this.show(e);
                });
            break;
        }

        /** Sous-menu **/
        switch (this.options.type) {
            case 'gestion_numeros' :
                var sous_menu=jQuery('<div>',{ id:'sous_menu_achat_associer_date_achat' })
                .addClass('menu desktop')
                .css({ display:'none' });

                var liste_achats=jQuery('<ul>');
                var section_achats_existants=jQuery('<div>',{
                    id:'dates_existantes',
                    align:'center'
                });
                tab_achats.each(function(achat) {
                    liste_achats
                        .append(jQuery('<li>', {
                            id: 'achat_' + achat.id
                        }).addClass('editer_date')
                        )
                        .append(
                            jQuery('<img>', {
                                src:'images/supprimer.png',
                                title:'Supprimer cette date d\'achat'})
                                .css({position: 'absolute', right: 0,width: '10px',cursor:'pointer'})
                                .on('click', function () {
                                    if (confirm('Confirmez-vous la suppression de cette date d\'achat ?')) {
                                        var id_achat=jQuery(this).closest('li').attr('id').split(new RegExp(/_/g))[1];
                                        jQuery.post('Database.class.php', {
                                             data: {
                                                 database: 'true',
                                                 supprimer_acquisition: id_achat
                                             },
                                            done:function() {
                                                 jQuery('#achat_'+id_achat).remove();
                                                jQuery('.bloc_details .achat_'+id_achat+' img').remove();
                                                 alert('La date d\'achat a ete supprimee');
                                             }
                                         });
                                    }
                                })
                        )
	                    .append(
		                    jQuery('<a>', {
			                    href: 'javascript:return false;',
			                    name: achat.groupName
		                    }).addClass('item_sous_menu enabled supprimer_date')
	                    )
	                    .on('click', function (e) {
		                    jQuery('a[name="' + jQuery(this).attr('name') + '"]').each(function (item) {
			                    item.removeClass('selected');
		                    });

	                    })
                        .html(achat.name+(achat.subMenu?'&nbsp;&gt;&gt;':''));
                });
                var section_nouvel_achat=jQuery('<div>', {
	                id: 'nouvel_achat'
                }).css({'text-align':'center'});

                var nouvel_achat_o = {
                    name: 'nouvelle_date_achat',
                    className: 'n_date',
                    groupName: 'achat',
                    selected: false
                };
                var nouvel_achat=jQuery('<li>').html(
                    jQuery('<a>', {
	                    id: 'creer_date_achat',
	                    href: 'javascript:return false;',
	                    name: nouvel_achat_o.groupName
                    }).addClass('enabled')
                        .html())
                .on('click', function () {
                    if (jQuery('#nouvelle_description').length)
                        return;
                    var nouvelle_date_li=jQuery('<li>');
                    var nouvelle_date_input1=jQuery('<input>',{
                        id:'nouvelle_description',
                        type:'text',
                        size:30,
                        maxlength:30
                    });
                    var nouvelle_date_input2=jQuery('<input>',{
                        id: 'nouvelle_date',
                        type:'text',
                        size:30,
                        readonly:'readonly',
                        maxlength:10
                    }).val(today());
                    var nouvelle_date_ok=jQuery('<input>', {
	                    id: 'nouvelle_date_ok',
	                    type: 'submit'
                    }).val('OK');

                    nouvelle_date_input1.val(l10n['description']);
                    nouvelle_date_li
                        .html(nouvelle_date_input1.html())
                        .append(jQuery('<br />').html())
                        .append(nouvelle_date_input2)
                        .append(nouvelle_date_ok);

                    var dates_existantes = jQuery('#dates_existantes');
                    if (dates_existantes.next().find(':last-child').length)
	                    dates_existantes.next().find(':last-child').after(nouvelle_date_li);
                    else
	                    dates_existantes.next().append(nouvelle_date_li);

                    jQuery('#nouvelle_date').datepicker({
                        format: "yyyy-mm-dd",
                        keyboardNavigation: false,
                        maxViewMode: 2,
                        autoclose: true,
                        language: locale === 'fr' ? 'fr' : 'en-GB'
                    });

                    nouvelle_date_ok.on('click', function () {
                        var date_valide = true;
                        var nouvelleDate = jQuery('#nouvelle_date');
                        var nouvelleDescription = jQuery('#nouvelle_description');

                        var date_entree = nouvelleDate.val();
                        if (!(moment(date_entree).isValid()) || !date_entree) {
                            date_valide = false;
                            nouvelleDate.css({
                                'font-style': 'italic',
                                color: 'red'
                            });
                            nouvelleDate.value = l10n['date_invalide'];
                            nouvelleDescription.trigger('focus');
                            setTimeout(function () {
                                jQuery('#nouvelle_date').css({
                                    'font-style': '',
                                    color: ''
                                })
                                .val(date_entree);
                            }, 2000);
                        }
                        var description_entree = jQuery('#nouvelle_description').val();

                        var description_valide = true;
                        if (description_entree.text().length > 30 || description_entree === '')
                            description_valide = false;
                        if (!description_valide) {
                            nouvelleDescription
                                .css({
                                    'font-style': 'italic',
                                    color: 'red'
                                })
                                .val(l10n['description_question']);
                            setTimeout(function () {
                                jQuery('#nouvelle_description')
                                    .css({
                                        'font-Style': '',
                                        color: ''
                                    })
                                    .val(date_entree);
                            }, 2000);
                        }
                        if (!description_valide || !date_valide)
                            return;
                        var reg_date = new RegExp("-", "g");
                        var date = date_entree.split(reg_date);
                        jQuery.post('Database.class.php', {
	                        data: {
		                        database: 'true',
		                        acquisition: 'true',
		                        afficher_non_defini: 'false',
		                        date_annee: date[0],
		                        date_mois: date[1],
		                        date_jour: date[2],
		                        description: description_entree
	                        }
                        }).done(function (response) {
                            if (response === 'Date') {
                                var nouvelleDescription = jQuery('#nouvelle_description');

                                nouvelleDescription.css({
                                    'font-style': 'italic',
                                    color: 'red'
                                });
                                nouvelleDescription.value = l10n['acquisition_existante'];
                                setTimeout(function () {
                                    jQuery('#nouvelle_description')
                                        .css({
                                            'font-style': '',
                                            color: ''
                                        })
                                        .val(description_entree);
                                }, 2000);
                            }
                            else {
                                var nouvelle_date_a = jQuery('<a>', {
                                    'href': 'javascript:;',
                                    'name': 'achat'
                                })
                                    .addClass('enabled')
                                    .css({
                                        display: 'block'
                                    });
                                nouvelle_date_a.html('Achat "' + description_entree + '"<br />' + date[0] + '-' + date[1] + '-' + date[2]);
                                nouvelle_date_li.html(nouvelle_date_a.html())
                                    .on('click', function (e) {
                                        jQuery('#a[name="' + jQuery(this).attr('name') + '"]').each(function (item) {
                                            item.removeClass('selected');
                                        });
                                    });
                                var ajoute = false;
                                jQuery('.date2').each(function () {
                                    var date_courante = jQuery(this).find(':last-child').text();
                                    if (!ajoute && est_superieure_a(date_courante, date[2] + '-' + date[1] + '-' + date[0])) {
                                        jQuery(this).parent().before(nouvelle_date_li);
                                        ajoute = true;
                                    }
                                });
                            }
                        });
                    });

                })
                .text(nouvel_achat_o.name);

                sous_menu
                    .append(section_achats_existants)
                    .append(liste_achats)
                    .append(section_nouvel_achat)
                    .append(nouvel_achat);
                body.append(sous_menu);
                l10n_action('fillArray',l10n,'l10n');
                l10n_action('remplirSpan',['creer_date_achat','nouvel_achat','dates_existantes',
                    'numero_selectionne','numeros_selectionnes']);
                break;
        }
        protos[this.options.type]=this;
    },
    show: function(e) {
        e.stop();
        switch (this.options.type) {
            case 'gestion_numeros' :
                if (jQuery('#nb_selection').html==="0") {
                    jQuery('.enabled').each(function(item) {
                        item.removeClass('enabled');
                        item.addClass('disabled');
                    });
                    break;
                }
                else {
                    jQuery('.disabled').each(function(item) {
                        item.removeClass('disabled');
                        item.addClass('enabled');
                    });
                }
            break;
        }
        this.options.beforeShow(e);
        var x = event.pageX,
            y = event.pageY,
        vpDim = document.viewport.getDimensions(),
        vpOff = document.viewport.getScrollOffsets(),
        elDim = this.container.getDimensions(),
        elOff = {
            left: ((x + elDim.width + this.options.pageOffset) > vpDim.width
                ? (vpDim.width - elDim.width - this.options.pageOffset) : x) + 'px',
            top: ((y - vpOff.top + elDim.height) > vpDim.height && (y - vpOff.top) > elDim.height
                ? (y - elDim.height) : y) + 'px'
        };
        this.container.css(elOff).css({
            zIndex: this.options.zIndex
        });
        this.container.show();
        this.event = e;
    },
    onMouseOver: function(e) {
        action_onmouseover(this,e);
    },
    onClick: function(e) {
        action_onclick(this,e);
    },
    getId: function(id){
        switch (this.options.type) {
            case 'gestion_numeros':
                return id;
            default:
                return id+'_'+this.options.type;
        }
    },
    cacher_tous_menus: function(e) {
        this.options.beforeHide(e);
        jQuery('.menu').hide();
    }
});

function action_onmouseover(proto,e) {
    var target=jQuery(this).prop('tagName')==='SPAN' ? jQuery(this).parent() : jQuery(this);
    e.stop();
    jQuery('.menu').each(function(sous_menu) {
        if (!(target.hasClass('item_sous_menu')) && jQuery(sous_menu).attr('id').indexOf('sous_menu') !== -1)
	        jQuery(sous_menu).hide();
    });
    if (target.hasClass('sous_menu')) {
        if (jQuery('#sous_menu_'+target.name).css('display')==='none') { // Afficher le sous-menu
	        var menu = jQuery('#' + proto.getId('menu_contextuel'));
	        var val_left=menu.offset().left+target.width()+2;
            var extremite_droite=val_left+target.width();
            if (extremite_droite>=jQuery('#body').width()) {
                val_left=menu.offset().left-target.width()-2;
            }
            jQuery('#sous_menu_'+target.name)
                .css({
                    left  :val_left+'px',
                    top   :menu.offset().top +target.offset().top+'px',
                    zIndex:100,
                    display:'block'
                });
        }
    }
}

function action_onclick(proto,e) {
	var target=jQuery(this).prop('tagName')==='SPAN' ? jQuery(this).parent() : jQuery(this);
    switch (proto.options.type) {
        case 'gestion_numeros' :
            e.stop();
            var nom_groupe=target.attr('name').substring(0, target.attr('name').indexOf('_'));
            if (target.hasClass('disabled'))
                return;
            if (target.hasClass('save')) {
                if (jQuery('#creer_date_achat').hasClass('selected')) {
                    alert(l10n['selectionner_date_achat']);
                    return;
                }
                var etat;
                var date_achat;
                var av;
                var liste_sel_num=[];
                jQuery('.selected').each(function(item) {
                    var nom_groupe_selected=jQuery(item).attr('name').substring(0, item.attr('name').indexOf('_'));
                    var classes=jQuery(item).attr('class').split();
                    switch(nom_groupe_selected) {
                        case 'etat':
                            etat=classes[0];
                            break;
                        case 'achat':
                            if (classes[0]==='non_date')
                                date_achat=-2;
                            else if (classes[0]==='pas_date')
                                date_achat=-1;
                            else {
                                jQuery('[name="achat"]').each (function (element) {
                                    if (jQuery(element).hasClass('selected') && element.children().length>2)
                                        date_achat=jQuery(element.children()[2]).text();
                                });
                            }
                            break;
                        case 'vente':
                            av=(classes[0]==="non_marque_a_vendre"?-1:classes[0]==="a_vendre");
                    }
                });
                jQuery('.num_checked').each(function(item) {
                    liste_sel_num.push(jQuery(item).attr('title'));
                });
                proto.options.beforeSelect(e);
                proto.container.hide();
                update_numeros(liste_sel_num,etat,date_achat,av);
            }
            else if(target.hasClass("date")) {
                if (!achats_affiches) {
                    jQuery('.date2','.n_date').each(function(item) {
                        jQuery(item).css({display: 'block'});
                    });
                }
                else {
                    jQuery('.date2','.n_date').each(function(item) {
	                    jQuery(item).css({display: 'none'});
                    });
                }
                achats_affiches=!achats_affiches;
            }
            else if (target.hasClass('non_possede')) {
                jQuery('a[name^="'+nom_groupe+'"]').each(function(item) {
                    jQuery(item).removeClass('selected');
                });
                jQuery('a:not([name^=etat])').each(function(item) {
                    if (!(jQuery(item).hasClass('save'))) {
	                    jQuery(item)
                            .removeClass('enabled')
                            .addClass('disabled');
                    }
                });
                target.addClass('selected');
            }
            else if (nom_groupe==="etat") {
                jQuery('a[name^="'+nom_groupe+'"]').each(function(item) {
	                jQuery(item).removeClass('selected');
                });
                jQuery('a:not([name^=etat])').each(function(item) {
	                jQuery(item)
                        .removeClass('disabled')
                        .addClass('enabled');
                });
                target.addClass('selected');
            }
            else {
                jQuery('a[name^="'+nom_groupe+'"]').each(function(item) {
	                jQuery(item).removeClass('selected');
                });
                target.addClass('selected');
            }

            break;
    }
}

function update_numeros(liste,etat,date_achat,av) {
    var liste_serialized=liste.join();
    var pays=jQuery('#pays').text();
    var magazine=jQuery('#magazine').text();
    jQuery.post('Database.class.php', {
	    data: {
		    database: true,
		    update: true,
		    pays: pays,
		    magazine: magazine,
		    list_to_update: liste_serialized,
		    etat: etat,
		    date_acquisition: date_achat,
		    av: av
	    }
    }).done(function() {
        window.location.replace("?action=gerer&onglet=ajout_suppr&onglet_magazine="+pays+"/"+magazine);
    });
}

function est_superieure_a (date1, date2) {
    var reg_date=new RegExp("-","g");
    date1=date1.split(reg_date);
    date2=date2.split(reg_date);
    if (date1[0]>date2[0])
        return true;
    if (date2[0]>date1[0])
        return false;

    if (date1[1]>date2[1])
        return true;
    if (date2[1]>date1[1])
        return false;

    if (date1[2]>date2[2])
        return true;
    if (date2[2]>date1[2])
        return false;

    return true;
}

function today() {
    var la_date=new Date();
    var jour=la_date.getDate();
    if (jour<10) jour='0'+jour;
    var mois=la_date.getMonth()+1;
    if (mois<10) mois='0'+mois;
    var annee=la_date.getFullYear();
    return annee+'-'+mois+'-'+jour;
}
