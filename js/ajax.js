var nom_magazine_old="";
var pays_sel=null;
var magazine_sel=null;
var liste_achats=[];
var l10n_gerer;

var isMobile = window.matchMedia("only screen and (max-width: 767px)");

function charger_menu() {
    if (!isMobile) {
        jQuery('#menu-content').removeClass('collapse');
    }
}

function init_observers_gerer_numeros() {

    l10n_gerer = [
        'numero_selectionne', 'numeros_selectionnes',
        'achat', 'achat_date_achat', 'achat_description', 'creer', 'annuler',
        'etat', 'etat_conserver_etat_actuel', 'etat_marquer_non_possede', 'etat_marquer_possede',
        'etat_marquer_mauvais_etat', 'etat_marquer_etat_moyen', 'etat_marquer_bon_etat',
        'achat_conserver_date_achat', 'achat_desassocier_date_achat', 'achat_associer_date_achat', 'achat_nouvelle_date_achat',
        'a_vendre_titre', 'vente_conserver_volonte_vente', 'vente_marquer_a_vendre', 'vente_marquer_pas_a_vendre',
        'enregistrer_changements'];
    l10n_action('fillArray',l10n_gerer, 'l10n_gerer', function() {
        get_achats(function() {
            init_menu_contextuel();
            init_observers_numeros();
            init_observers_previews();
        })
    });
}

function init_observers_numeros() {
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
}

function init_observers_previews() {
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

function get_achats(callback) {
    jQuery.post('Database.class.php',
        {database: 'true', liste_achats: 'true'},
        function (achats_courants) {
            liste_achats = achats_courants;
            callback && callback();
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
           jQuery('#evenements')
               .html(response)
               .find('a.has_tooltip.edge_tooltip').each(function(i, element) {
                   element_conteneur_bibliotheque = jQuery(element).next('.tooltip_content');
                   charger_tranche(element_conteneur_bibliotheque.find('.tranche:eq(0)'));
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
            trigger: 'manual',
            container: 'body'
        })
            .on('mouseenter', function () {
                var _this = this;
                $(this).popover('show');
                $('.popover').on('mouseleave', function () {
                    $(_this).popover('hide');
                });
            }).on('mouseleave', function (e) {
                var _this = this;
                setTimeout(function () {
                    if (!$('.popover:hover').length) {
                        $(_this).popover('hide');
                    }
                }, 300);
            });
    });
}

function callback_tranches_chargees(tooltip_content) {
    tooltip_content.prev('a.has_tooltip.edge_tooltip')
        .popover({
            content: tooltip_content.html(),
            placement: 'auto top',
            html: true,
            trigger: 'hover'
        });
}

function initPays(inclure_tous_pays, selected) {
    if (jQuery('#liste_pays').length) {
	    jQuery.post('Inducks.class.php',
		    {get_pays: 'true', inclure_tous_pays: inclure_tous_pays, selected: selected},
            function(response) {
			    jQuery('#liste_pays').html(response);
			    if (jQuery('#liste_magazines').length)
				    select_magazine();
		    }
	    );
    }
}

jQuery.fn.setTextureThumbnail = function(texture) {
    this.css({'background-image': 'url("edges/textures/bois/' + texture + '.jpg")'});
    return this;
};

function setCurrentTexture(textureId, value) {
    jQuery('#select_' + textureId + ' button')
        .setTextureThumbnail(value)
        .find('.selected').text(value);
    jQuery('#' + textureId).val(value);
}

function initPublicationSorting(sorts, publicationNames) {
    jQuery('.sortable-wrapper ol li:not(.template)').remove();

    jQuery.each(sorts, function(unused, sort) {
        var row = jQuery('.form-group ol li.template').clone(true).removeClass('template');
        row.find('.flag').attr({src: 'images/flags/'+(sort.split('/')[0])+'.png'});
        row.find('input').val(sort);
        row.find('span').text(publicationNames[sort]);
        jQuery('.sortable-wrapper ol').append(row);
    });

    jQuery('.sortable-wrapper ol').sortable();
    jQuery('.sortable-wrapper .reset-sortable').click(function() {
        initPublicationSorting(sorts.sort(), publicationNames)
    });

    jQuery('#message_options')
        .addClass('hidden')
        .next().removeClass('hidden');
}

function initTextures() {
    if (jQuery('#texture1').length) {
        jQuery.post('Edge.class.php',
            {get_sous_textures: 'true'},
            function (response) {
                var selects = jQuery('.select_sous_texture .dropdown-menu');
                jQuery.each(response.textures, function(unused, sous_texture) {
                    selects.append(jQuery('<li>')
                        .setTextureThumbnail(sous_texture)
                        .append(jQuery('<a>', { href: 'javascript:void(0)' }).text(sous_texture)));
                });
                selects.find('li').click(function() {
                    var select = jQuery(this).closest('.select_sous_texture');
                    var textureId = select.attr('id').match(/select_(.+)$/)[1];
                    setCurrentTexture(textureId, jQuery(this).text());
                });
                setCurrentTexture('sous_texture1', response.current[0]);
                setCurrentTexture('sous_texture2', response.current[1]);

                initPublicationSorting(response.sorts, response.publicationNames);

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

        jQuery.post('Inducks.class.php',
            {get_magazines: 'true', pays: id_pays},
            function(response) {
                jQuery('#liste_magazines').html(response);
                if (jQuery('#liste_numeros').length)
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

	var liste_numeros = jQuery('#liste_numeros');
    if (liste_numeros.length) {
        jQuery.post('Database.class.php',
            {database: 'true', affichage: 'true', pays: pays, magazine: magazine},
            function(response) {
                liste_numeros.html(response);
                init_observers_gerer_numeros();
                numero = numero || location.hash;
                if (numero) {
                    indiquer_numero(liste_numeros.find('[name="'+numero.replace(/#/,'')+'"]').parent(), ['gauche']);
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
