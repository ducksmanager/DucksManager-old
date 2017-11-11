var nom_pays_old="";
var nom_magazine_old="";
var pays_sel=null;
var magazine_sel=null;
var myMenuItems;
var etats_charges=false;
var tab_achats=[];

function init_observers_gerer_numeros() {
	l10n_action('fillArray',l10n_acquisitions,'l10n_acquisitions');
	get_achats(-1);
}

function get_achats(continue_id) {
    new Ajax.Request('Database.class.php', {
        method: 'post',
        parameters: 'database=true&liste_achats=true&continue=' + continue_id,
        onSuccess: function (transport) {
            var achats_courants = JSON.parse(transport.responseText);
            for (var i = 0; i < achats_courants.length; i++) {
                if (achats_courants[i]['continue']) {
                    get_achats(achat['id']);
                    return;
                }
                var achat = achats_courants[i];
                achat['name'] = 'Achat "' + achat.description + '"<br />' + achat.date;
                achat['className'] = 'date2';
                achat['groupName'] = 'achat';
                achat['selected'] = false;
                achat['id'] = achat.id;
                tab_achats[tab_achats.length] = achat;
            }
            myMenuItems = [
                {
                    separator: true
                }, {
                    className: 'non_marque',
                    groupName: 'etat_conserver_etat_actuel',
                    selected: true
                }, {
                    className: 'non_possede',
                    groupName: 'etat_marquer_non_possede'
                }, {
                    className: 'possede',
                    groupName: 'etat_marquer_possede'
                }, {
                    className: 'mauvais',
                    groupName: 'etat_marquer_mauvais_etat'
                }, {
                    className: 'moyen',
                    groupName: 'etat_marquer_etat_moyen'
                }, {
                    className: 'bon',
                    groupName: 'etat_marquer_bon_etat'
                }, {
                    separator: true
                }, {
                    className: 'non_date',
                    groupName: 'achat_conserver_date_achat',
                    selected: true
                }, {
                    className: 'pas_date',
                    groupName: 'achat_desassocier_date_achat'
                }, {
                    className: 'date',
                    groupName: 'achat_associer_date_achat',
                    subMenu: true
                }
            ];
            var myMenuItems2 = [
                {
                    separator: true
                }, {
                    className: 'non_marque_a_vendre',
                    groupName: 'vente_conserver_volonte_vente',
                    selected: true
                }, {
                    className: 'a_vendre',
                    groupName: 'vente_marquer_a_vendre'
                }, {
                    className: 'pas_a_vendre',
                    groupName: 'vente_marquer_pas_a_vendre'
                }, {
                    separator: true
                }, {
                    className: 'save',
                    groupName: 'save_enregistrer_changements'
                }];
            myMenuItems = myMenuItems.concat(myMenuItems2);

            if (!$('menu_contextuel')) {
                new Proto.Menu({
                    type: 'gestion_numeros',
                    selector: '#liste_numeros',
                    className: 'menu desktop',
                    menuItems: myMenuItems
                });
            }

            var arr_l10n = ['conserver_etat_actuel', 'marquer_non_possede', 'marquer_possede',
                'marquer_mauvais_etat', 'marquer_etat_moyen', 'marquer_bon_etat',
                'conserver_date_achat', 'desassocier_date_achat', 'associer_date_achat', 'nouvelle_date_achat',
                'conserver_volonte_vente', 'marquer_a_vendre', 'marquer_pas_a_vendre',
                'enregistrer_changements'];
            l10n_action('remplirSpanName', arr_l10n);

            jQuery('.num_wrapper')
                .mouseover(function () {
                    jQuery('.survole').removeClass('survole');
                    lighten(jQuery(this).closest('.num_wrapper'));
                })
                .mouseout(function () {
                    unlighten(jQuery(this).closest('.num_wrapper'));
                    jQuery('.num').popover('hide');
                })
                .mouseup(function (event) {
                    if (isLeftClick(event) && !jQuery(event.target).hasClass('preview')) {
                        stop_selection(jQuery(this).closest('.num_wrapper'));
                        event.stopPropagation();
                    }
                })
                .mousedown(function (event) {
                    if (isLeftClick(event) && !jQuery(event.target).hasClass('preview')) {
                        start_selection(jQuery(this).closest('.num_wrapper'));
                    }
                })
                .mousemove(function () {
                    pre_select(jQuery(this).closest('.num_wrapper'));
                });

            jQuery('.preview').click(function(event) {
                var element = jQuery(this);
                element.attr({src: 'loading.gif'});

                var numero_wrapper = element.closest('.num_wrapper');
                if (numero_wrapper.data('cover')) {
                    maj_image(numero_wrapper, numero_wrapper.data('cover'));
                    element.attr({src: 'images/icones/view.png'});
                }
                else {
                    jQuery.post('Inducks.class.php', {
                        get_cover: 'true',
                        debug: debug,
                        pays: jQuery('#pays').text(),
                        magazine: jQuery('#magazine').text(),
                        numero: numero_wrapper.attr('title')
                    })
                        .done(function (data) {
                            maj_image(numero_wrapper, data && data.cover);
                        })
                        .fail(function () {
                            maj_image(numero_wrapper, null);
                        })
                        .always(function() {
                            element.attr({src: 'images/icones/view.png'});
                        });
                }
                event.stopPropagation();
            });
        }
    });
}

function maj_image(numero_wrapper, image) {
    var cover_not_found_image = 'images/cover_not_found.png';
    numero_wrapper.data('cover', image || cover_not_found_image);

    img = new Image();
    img.onload = function() {
        charger_tooltip_couverture(numero_wrapper.find('.num'), numero_wrapper.data('cover'));
    };
    img.onerror = function() {
        img.onload = function() {
            charger_tooltip_couverture(numero_wrapper.find('.num'), cover_not_found_image);
        };
        img.src = cover_not_found_image;
    };
    img.src = numero_wrapper.data('cover');
}

function charger_tooltip_couverture(target, image) {
    target
        .popover({
            content: function() {
                return '<img src="' + image + '" />';
            },
            html: true,
            viewport: { selector: 'body' },
            placement: 'right'
        })
        .popover('show');
}

function charger_evenements() {
	new Ajax.Request('Database.class.php', {
		   method: 'post',
		   parameters:'database=true&evenements_recents=true',
		   onSuccess:function(transport) {
			   $('evenements').innerHTML = transport.responseText;
			   $$('#evenements a.has_tooltip.edge_tooltip').each(function(element) {
				   element_conteneur_bibliotheque = element.next('.tooltip_content');
				   charger_tranche(element_conteneur_bibliotheque.down('.tranche'));
			   });
			   charger_tooltips_utilisateurs();

		   }
	});
}

function charger_tooltips_utilisateurs() {
    $$('a.has_tooltip.user_tooltip').each(function(element) {
        var tooltip_content = element.next('.tooltip_content');
        jQuery(element).popover({
            content: tooltip_content.down('div').innerHTML,
            title: tooltip_content.down('h4').innerHTML,
            placement: 'top',
            html: true,
            trigger: 'hover',
            container: 'body'
        });
    });
}

function callback_tranches_chargees(tooltip_content) {
	var element_texte_hover = tooltip_content.previous('a.has_tooltip.edge_tooltip');
    jQuery(element_texte_hover).popover({
        content: tooltip_content.innerHTML,
        placement: 'top',
        html: true,
        trigger: 'hover'
    });
}

function initPays(inclure_tous_pays, selected) {
    if (!$('liste_pays')) return;
    new Ajax.Request('Inducks.class.php', {
           method: 'post',
           parameters:'get_pays=true&inclure_tous_pays='+inclure_tous_pays+'&selected='+selected,
           onSuccess:function(transport) {
                $('liste_pays').update(transport.responseText);
                if ($('liste_magazines'))
                    select_magazine();
           }
    });
}

function initTextures() {
    if (!$('texture1')) return;
    [1,2].each (function (n) {
        new Ajax.Request('Edge.class.php', {
               method: 'post',
               parameters:'get_texture=true&n='+n,
               onSuccess:function(transport) {
                    $('texture'+n).update(transport.responseText);
                    setTimeout(function() {
                        select_sous_texture(n);
                    },1000);
               }
        });
    });
}

function select_sous_texture (n) {
    if (!$('sous_texture'+n)) return;
    new Ajax.Request('Edge.class.php', {
	   method: 'post',
	   parameters:'get_sous_texture=true&texture='+$('texture'+n).options[$('texture'+n).options.selectedIndex].value+'&n='+n,
	   onSuccess:function(transport) {
			$('sous_texture'+n).update(transport.responseText);
	   }
    });
}
function select_magazine(valeur_magazine) {
    var el_select=$('liste_pays');
    $('form_pays').value=el_select.options[el_select.options.selectedIndex].id;
    if (el_select.options[0].id!='chargement_pays') {
        var id_pays=el_select.options[el_select.options.selectedIndex].id;
        pays_sel=id_pays;
        var option_chargement=new Element('option',{'id':'chargement_magazines'})
                                                        .update("Chargement des magazines");
        $('liste_magazines').update(option_chargement);
        new Ajax.Request('Inducks.class.php', {
           method: 'post',
           parameters:'get_magazines=true&pays='+id_pays,
           onSuccess:function(transport) {
                $('liste_magazines').update(transport.responseText);
                if ($('liste_numeros'))
                    select_numero();
                if (typeof (valeur_magazine) != 'undefined') {
                    var trouve=false;
                    for (var i=valeur_magazine.length;i>=1;i--) {
                        var val=valeur_magazine.substring(0, i);
                        $$('#liste_magazines option').each(function (option) {
                            if (option.readAttribute('id') == val) {
                                $('liste_magazines').selectedIndex=option.index;
                                trouve=true;
                            }
                        });
                        if (trouve)
                            break;
                    }
                }
                magazine_selected();
           }
        });
    }
}

function magazine_selected() {
	var el_select_pays=$('liste_pays');
	var el_select_magazine=$('liste_magazines');
	var value_pays = el_select_pays.options[el_select_pays.options.selectedIndex].id;
	var value_magazine = el_select_magazine.options[el_select_magazine.options.selectedIndex].id;
	$('form_magazine').value=value_magazine;
	$('onglet_magazine').value = [value_pays, value_magazine].join('/');

}

function select_numero() {
	var el_select=$('liste_magazines');
	var el_select_pays=$('liste_pays');
	if (el_select.options[0].id!='chargement_magazines') {
		var nom_magazine=el_select.options[el_select.options.selectedIndex].text;
		if (nom_magazine==nom_magazine_old)
			return;
		nom_magazine_old=nom_magazine;
  		var id_magazine=el_select.options[el_select.options.selectedIndex].id;
  		var id_pays=el_select_pays.options[el_select_pays.options.selectedIndex].id;
  		magazine_sel=id_magazine;
		var option_chargement=new Element('option',{'id':'chargement_numeros'})
								.update("Chargement des num&eacute;ros");
		$('liste_numeros').update(option_chargement);
		new Ajax.Request('Inducks.class.php', {
		   method: 'post',
		   parameters:'get_numeros=true&pays='+id_pays+'&magazine='+id_magazine,
		   onSuccess:function(transport) {
		   		$('liste_numeros').update(transport.responseText);
		   		if ($('liste_etats'))
		   			select_etats(); 
		   }
		});
	}
}

function select_etats() {
	new Ajax.Request('Database.class.php', {
	   method: 'post',
	   parameters:'database=true&liste_etats=true',
	   onSuccess:function(transport) {
			$('liste_etats').update();
			var reg=new RegExp("~", "g");
	    	var etats=transport.responseText.split(reg);
			for (var i=0;i<etats.length;i++) {
				var option=new Element('option').insert(etats[i]);
				$('liste_etats').insert(option);
				etats_charges=true;
				nom_pays_old="";
				nom_magazine_old="";
			}
	   	
	   }
	});
}

function afficher_numeros(pays,magazine, numero) {
	if (pays == null || magazine == null) {
		var el_select=$('liste_magazines');
		if (el_select.options[0].id=='vide') {
			l10n_action('alert','selectionner_magazine');
			return;
		}
		magazine_sel=el_select.options[el_select.options.selectedIndex].id;
		pays=pays_sel;
		magazine=magazine_sel;
		if (!pays || !magazine) {
				l10n_action('alert','remplir_pays_et_magazine');
				return;
		}
	}
	new Ajax.Request('Database.class.php', {
           method: 'post',
           parameters:'database=true&affichage=true&pays='+pays+'&magazine='+magazine,
           onSuccess:function(transport) {
                $('liste_numeros').update(transport.responseText);
                init_observers_gerer_numeros();
                numero = numero || location.hash;
	            if (numero) {
                    indiquer_numero($('liste_numeros').select('[name="'+numero.replace(/#/,'')+'"]')[0].parentNode, ['gauche']);
	            }
           }
	});
}

function isLeftClick(event) {
	return event.which === 1;
}