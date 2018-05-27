var l10n_calculs_auteurs=['calcul_en_cours','calcul_termine'];
var prevent_click=false;

function toggle_item_menu(element_clic) {
	element_clic = jQuery(element_clic);

	var tagName = element_clic.prop('tagName').toUpperCase();
    if (tagName==='LI') {
	    element_clic = element_clic.parent();
    }
    element_clic.parent().find('li.active').removeClass('active');
    element_clic.toggleClass('active');
    element_clic.parent().find('li a').each(function(i, element) {
        jQuery('contenu_'+element.attr('name')).css({'display':'none'});
    });
    jQuery('contenu_'+element_clic.children().eq(0).attr('name')).css({'display':'block'});
}

function init_autocompleter_auteurs() {
    l10n_action('fillArray',l10n_calculs_auteurs,'l10n_calculs_auteurs');
    if (jQuery('auteur_cherche')) {
	    new Ajax.Autocompleter ('auteur_cherche',
		    'liste_auteurs',
		    'auteurs_choix.php',
		    {
			    method: 'post',
			    indicator:'loading_auteurs',
			    paramName: 'value',
			    afterUpdateElement: ac_return
		    });
    }
}

function ac_return(field, item){
	var regex_nettoyage_nom=/(?:^[\t ]*)|(?:[\t ]*jQuery)/g;
	jQuery('#auteur_nom').val(field.val().replace(regex_nettoyage_nom,''));
    jQuery('#auteur_id').val(item.find('[name="nom_auteur"]').attr('title'));
    jQuery('#auteur_cherche').val(jQuery('#auteur_cherche').val().replace(regex_nettoyage_nom,''));
}
