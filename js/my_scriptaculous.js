var l10n_calculs_auteurs=['calcul_en_cours','calcul_termine'];
var prevent_click=false;

function toggle_item_menu(element_clic) {
    element_clic=element_clic.tagName=='LI' ? element_clic : element_clic.parentNode;
    element_clic.up().select('li.active').invoke('removeClassName','active');
    $(element_clic).toggleClassName('active');
    element_clic.up().select('li a').pluck('name').each(function(nom) {
        $('contenu_'+nom).setStyle({'display':'none'});
    });
    $('contenu_'+element_clic.down().name).setStyle({'display':'block'});
}

function init_autocompleter_auteurs() {
    l10n_action('fillArray',l10n_calculs_auteurs,'l10n_calculs_auteurs');
    if (!($('auteur_cherche'))) return;
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

function ac_return(field, item){
	var regex_nettoyage_nom=/(?:^[\t ]*)|(?:[\t ]*$)/g;
	$('auteur_nom').value=field.value.replace(regex_nettoyage_nom,'');
    $('auteur_id').value=item.down('[name="nom_auteur"]').readAttribute('title');
    $('auteur_cherche').value=$('auteur_cherche').value.replace(regex_nettoyage_nom,'');
}