var nom_pays_old="";
var nom_magazine_old="";
var pays_sel=null;
var magazine_sel=null;
var etats_charges=false;
var tab_achats=[];
var couverture_preview;

function get_achats(callback) {
    new Ajax.Request('Database.class.php', {
        method: 'post',
        parameters: 'database=true&liste_achats=true',
        onSuccess: function (transport) {
            var achats = JSON.parse(transport.responseText);

            var purchase_dates_wrapper = jQuery('#update_options .purchase_dates');
            var template = purchase_dates_wrapper.find('.alternative.date.template');

            for (var i = 0; i < achats.length; i++) {
                var item = template.clone(true).removeClass('template').data(achats[i]);
                item.find('.description').text(achats[i].Description);
                item.find('.day').text(achats[i].Date);

                purchase_dates_wrapper.append(item);
            }

            callback();
        }
    });
}

function maj_image(element, image, numero_wrapper) {
    var largeur_image=jQuery('#colonne_gauche').prop('scrollWidth');
	element.css({
        width: largeur_image+'px',
        top: numero_wrapper.offset().top +'px'
	});

    element.find('>.fermer').removeClass('cache');
    element.append(jQuery('<img>').attr({'src':image}));
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
            trigger: 'hover'
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

function init_liste_numeros() {
    jQuery('.num_wrapper')
        .mouseover(
            function () {
                jQuery('.survole').removeClass('survole');

                lighten(jQuery(this).closest('.num_wrapper'));
            })
        .mouseout(
            function () {
                unlighten(jQuery(this).closest('.num_wrapper'));
            })
        .mouseup(
            function (event) {
                if (isLeftClick(event) && !jQuery(event.target).hasClass('preview')) {

                    stop_selection(jQuery(this).closest('.num_wrapper'));
                    var nb_selectionnes = jQuery('#liste_numeros .num_checked').length;
                    jQuery('#update_menu')
                        .toggleClass('shown', nb_selectionnes > 0)
                        .find('.navbar .nb_selectionnes').text(nb_selectionnes);
                    event.stopPropagation();
                }
            })
        .mousedown(
            function (event) {
                if (isLeftClick(event) && !jQuery(event.target).hasClass('preview')) {

                    start_selection(jQuery(this).closest('.num_wrapper'));
                }
            })
        .mousemove(
            function () {
                pre_select(jQuery(this).closest('.num_wrapper'));
            });


    jQuery('.preview').click(function (event) {
        var element = jQuery(this);
        element.attr({src: 'loading.gif'});

        var numero_wrapper = element.closest('.num_wrapper');

        couverture_preview.find('img').remove();

        jQuery.post('Inducks.class.php', {
            get_cover: 'true',
            debug: debug,
            pays: jQuery('#pays').text(),
            magazine: jQuery('#magazine').text(),
            numero: numero_wrapper.attr('title')
        })
            .done(function (data) {
                maj_image(jQuery('#couverture_preview'), (data && data.cover) || 'images/cover_not_found.png', numero_wrapper);
            })
            .fail(function () {
                maj_image(jQuery('#couverture_preview'), 'images/cover_not_found.png', numero_wrapper);
            })
            .always(function () {
                element.attr({src: 'images/icones/view.png'});
            });
        event.stopPropagation();

    });

    couverture_preview = jQuery('#couverture_preview');

    couverture_preview.find('.fermer')
        .click(function () {
            jQuery(this).addClass('cache');
            couverture_preview.find('img').remove();
        });

    if (location.hash) {
        $('liste_numeros').select('[name="' + location.hash.replace(/#/, '') + '"]')[0].scrollIntoView(true);
    }
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

function position_nav() {
    var footer = jQuery('#footer');
    var borderBeforeFooter = 3;

    var heightOfFooterSeen = jQuery(window).height() + jQuery('body').scrollTop() - footer.offset().top + borderBeforeFooter;

    jQuery('#update_menu')
		.css({ bottom: Math.max(0, heightOfFooterSeen) + 'px' });
}

function init_nav() {
    var navbar = jQuery('#update_menu')
        .find('.navbar');

    navbar.css({paddingLeft: jQuery('#menu_gauche').width() + 2});

    var valeurs = navbar.find('.option');

    valeurs
        .mouseleave(function() {
            jQuery(this).find('.option_nom>.alternatives').addClass('hidden invisible');
        })
        .mouseover(function() {
            jQuery(this).find('.option_nom>.alternatives:not(.submenu)').removeClass('hidden invisible animated');
        });

    valeurs
		.find('.alternative')
			.click(function() {
                var alternative_element = jQuery(this);

                alternative_element.closest('.option_nom>.alternatives')
                    .addClass('invisible animated')
                    .find('.alternative')
                        .removeClass('checked');

                alternative_element
                    .addClass('checked')
                    .closest('.option').find('.option_valeur')
                        .changer_valeur(
                            alternative_element.attr('name'),
                            alternative_element.attr('value-short')
                        );
			})
            .filter(':not(.day-row)')
                .mouseover(function() {
                    jQuery(this).closest('.option_nom').find('.alternatives.submenu')
                        .toggleClass('hidden invisible animated', !jQuery(this).hasClass('open_submenu'))
                });

    valeurs.find('.option_nom>.alternatives>.alternative[name="ne_pas_changer"]').trigger('click');

    valeurs.find('.purchase_search').keyup(function() {
    	var searchValue = jQuery(this).val().toLowerCase();
    	jQuery('.purchase_dates .date.day-row').each(function() {
    		var el = jQuery(this);
            el.toggleClass('hidden', el.text().trim().toLowerCase().indexOf(searchValue) === -1);
		});
	});

    navbar.find('#save').click(function() {
    	var numeros = jQuery.map(jQuery('#liste_numeros .num_checked'), function(element) {
    		return jQuery(element).attr('title');
    	});
        var valeurs_options = {};
    	jQuery.each(jQuery('#update_options').find('.option'), function(i, section_option) {
    		valeurs_options[jQuery(section_option).attr('name')] = jQuery(section_option).find('.valeur').attr('name');
		});

        update_numeros(numeros, valeurs_options.condition, valeurs_options.purchase_id, valeurs_options.for_sale);
	});

    position_nav();

    jQuery(window)
		.resize(position_nav)
		.scroll(position_nav);
}

jQuery.fn.changer_valeur = function(nom, valeur) {
    this.each(function(){
        jQuery(this).find('.valeur').attr({ name: nom }).text(valeur);
    });
};

function afficher_numeros(pays,magazine) {

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
        parameters: 'database=true&affichage=true&pays=' + pays + '&magazine=' + magazine,
        onSuccess: function (transport) {
            $('liste_numeros').update(transport.responseText);
            init_liste_numeros();
            get_achats(init_nav);
        }
    });
}

function isLeftClick(event) {
	return event.which === 1;
}