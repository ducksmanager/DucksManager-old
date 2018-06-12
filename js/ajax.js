var nom_magazine_old="";
var pays_sel=null;
var magazine_sel=null;
var myMenuItems;
var tab_achats=[];

var isMobile = window.matchMedia("only screen and (max-width: 767px)");

function charger_menu() {
    if (!isMobile) {
        jQuery('#menu-content').removeClass('collapse');
    }
}

function init_observers_gerer_numeros() {
	get_achats(-1);
}

function get_achats(continue_id) {
    jQuery.post(
        'Database.class.php',
        {database: 'true', liste_achats: 'true', continue: continue_id},
        function (achats_courants) {
	        var arr_l10n = ['conserver_etat_actuel', 'marquer_non_possede', 'marquer_possede',
		        'marquer_mauvais_etat', 'marquer_etat_moyen', 'marquer_bon_etat',
		        'conserver_date_achat', 'desassocier_date_achat', 'associer_date_achat', 'nouvelle_date_achat',
		        'conserver_volonte_vente', 'marquer_a_vendre', 'marquer_pas_a_vendre',
		        'enregistrer_changements'];
	        l10n_action('remplirSpanName', arr_l10n);

	        var l10n_achats = ['achat'];
	        l10n_action('fillArray',l10n_achats, 'l10n_achats');

	        for (var i = 0; i < achats_courants.length; i++) {
                if (achats_courants[i]['continue']) {
                    get_achats(achat['id']);
                    return;
                }
                var achat = jQuery.extend({}, achats_courants[i], {
	                name: l10n_achats.achat + ' "' + achats_courants[i].description + '"<br />' + achats_courants[i].date,
	                className: 'date2',
	                groupName: 'achat',
	                selected: false
                });
                tab_achats.push(achat);
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

            if (!jQuery('#menu_contextuel')) {
                new Proto.Menu({
                    type: 'gestion_numeros',
                    selector: '#liste_numeros',
                    className: 'menu desktop',
                    menuItems: myMenuItems
                });
            }

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
    );
}

function maj_image(numero_wrapper, image) {
    var cover_not_found_image = 'images/cover_not_found.png';
    numero_wrapper.data('cover', image || cover_not_found_image);

    var img = jQuery('<img>')
	    .on('load', function() {
	        charger_tooltip_couverture(numero_wrapper.find('.num'), numero_wrapper.data('cover'));
	    })
	    .on('error', function() {
	        img
		        .on('load', function() {
		            charger_tooltip_couverture(numero_wrapper.find('.num'), cover_not_found_image);
		        })
		        .attr({src: cover_not_found_image});
	    });
    img.attr({src: numero_wrapper.data('cover')});
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
	jQuery.post(
	    'Database.class.php',
        { database: 'true', evenements_recents: 'true'},
        function(response) {
           jQuery('#evenements').html(response);
           jQuery('#evenements a.has_tooltip.edge_tooltip').each(function(i, element) {
               element_conteneur_bibliotheque = jQuery(element).next('.tooltip_content');
               charger_tranche(element_conteneur_bibliotheque.find('>.tranche'));
           });
           charger_tooltips_utilisateurs();
       }
	);
}

function charger_tooltips_utilisateurs() {
    jQuery('a.has_tooltip.user_tooltip').each(function(i, element) {
        var tooltip_content = jQuery(element).next('.tooltip_content');
        jQuery(element).popover({
            content: tooltip_content.find('>div').html(),
            title: tooltip_content.find('>h4').html(),
            placement: 'top',
            html: true,
            trigger: 'hover',
            container: 'body'
        });
    });
}

function callback_tranches_chargees(tooltip_content) {
	var element_texte_hover = tooltip_content.prev('a.has_tooltip.edge_tooltip');
    element_texte_hover.popover({
        content: tooltip_content.html(),
        placement: 'top',
        html: true,
        trigger: 'hover'
    });
}

function initPays(inclure_tous_pays, selected) {
    if (jQuery('#liste_pays').length) {
	    jQuery.post(
	        'Inducks.class.php',
		    {get_pays: 'true', inclure_tous_pays: inclure_tous_pays, selected: selected},
            function(response) {
			    jQuery('#liste_pays').html(respponse);
			    if (jQuery('#liste_magazines').length)
				    select_magazine();
		    }
	    );
    }
}

function initTextures() {
    if (jQuery('#texture1').length) {
	    jQuery.each([1, 2], function (i, n) {
		    jQuery.post(
		        'Edge.class.php',
                {get_texture: 'true', n: n},
                function (response) {
				    jQuery('#texture' + n).html(response);
				    setTimeout(function () {
					    select_sous_texture(n);
				    }, 1000);
			    }
		    );
        });
    }
}

function select_sous_texture (n) {
	if (jQuery('#sous_texture'+n).length) {
		jQuery.post(
		    'Edge.class.php',
            {get_sous_texture: 'true', texture: jQuery('#texture' + n).val(), n: n},
            function (response) {
				jQuery('#sous_texture' + n).html(response);
			}
		);
	}
}
function select_magazine(valeur_magazine) {
    var el_select=jQuery('#liste_pays');
    jQuery('#form_pays').val(el_select.val());
    if (el_select.find('>option:eq(0)').attr('id') !=='chargement_pays') {
        var id_pays=el_select.find('option:selected').attr('id');
        pays_sel=id_pays;
        var option_chargement=jQuery('<option>',{id: 'chargement_magazines'})
	        .text('Chargement des magazines');
        jQuery('#liste_magazines').html(option_chargement.html());

        jQuery.post(
            'Inducks.class.php',
            {get_magazines: 'true', pays: id_pays},
            function(response) {
                jQuery('#liste_magazines').html(response);
                if (jQuery('#liste_numeros'))
                    select_numero();
                if (typeof (valeur_magazine) !== 'undefined') {
                    var trouve=false;
                    for (var i=valeur_magazine.length;i>=1;i--) {
                        var val=valeur_magazine.substring(0, i);
                        jQuery('#liste_magazines option').each(function (i, option) {
                            if (option.attr('id') === val) {
                                jQuery('#liste_magazines').prop('selectedIndex', option.index);
                                trouve=true;
                            }
                        });
                        if (trouve)
                            break;
                    }
                }
                magazine_selected();
           }
        );
    }
}

function magazine_selected() {
	var el_select_pays=jQuery('#liste_pays');
	var el_select_magazine=jQuery('#liste_magazines');
	var value_pays = el_select_pays.find('option:selected').attr('id');
	var value_magazine = el_select_magazine.find('option:selected').attr('id');
	jQuery('#form_magazine').val(value_magazine);
	jQuery('#onglet_magazine').val([value_pays, value_magazine].join('/'));

}

function select_numero() {
	var el_select=jQuery('#liste_magazines');
	var el_select_pays=jQuery('#liste_pays');
	if (el_select.find('>option:eq(0)').attr('id') !=='chargement_magazines') {
		var nom_magazine=el_select.find('option:selected').text();
		if (nom_magazine!==nom_magazine_old) {
			nom_magazine_old = nom_magazine;
			var id_magazine = el_select.find('>option:eq(0)').attr('id');
			var id_pays = el_select_pays.find('>option:eq(0)').attr('id');
			magazine_sel = id_magazine;

			// TODO use l10n
			var option_chargement = jQuery('<option>', {id: 'chargement_numeros'})
				.text("Chargement des numéros");
			jQuery('#liste_numeros').html(option_chargement);
			jQuery.post(
			    'Inducks.class.php',
				{get_numeros: 'true', pays: id_pays, magazine: id_magazine},
                function (response) {
					jQuery('#liste_numeros').html(response);
				}
			);
		}
	}
}

function afficher_numeros(pays,magazine, numero) {
	if (pays === null || magazine === null) {
		var el_select=jQuery('#liste_magazines');
		if (el_select.find('option:eq(0)').attr('id') === 'vide') {
			l10n_action('alert','selectionner_magazine');
			return;
		}
		magazine_sel=el_select.find('option:selected').attr('id');
		pays=pays_sel;
		magazine=magazine_sel;
		if (!pays || !magazine) {
            l10n_action('alert','remplir_pays_et_magazine');
            return;
		}
	}

	if (jQuery('#liste_numeros').length) {
        jQuery.post(
            'Database.class.php',
            {database: 'true', affichage: 'true', pays: pays, magazine: magazine},
            function(response) {
                jQuery('#liste_numeros').html(response);
                init_observers_gerer_numeros();
                numero = numero || location.hash;
                if (numero) {
                    indiquer_numero(jQuery('#liste_numeros').find('[name="'+numero.replace(/#/,'')+'"]')[0].parent(), ['gauche']);
                }
            }
        );
    }
    else if (numero) {
		location.replace('?action=gerer&onglet=ajout_suppr&onglet_magazine=' + pays + '/' + magazine+'&numero=' + numero);
	}
}

function isLeftClick(event) {
	return event.which === 1;
}
