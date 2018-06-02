var debut_selection=jQuery();
var now_selecting=false;
var liste;

function disableselect(){
	return false
}

function reEnable(){
	return true
}

function start_selection(sel) {
	var liste_numeros = jQuery('#liste_numeros')[0];

	//if IE4+
    liste_numeros.onselectstart=new Function ("return false");

	//if NS6
	if (window.sidebar){
        liste_numeros.onmousedown=disableselect;
        liste_numeros.onclick=reEnable;
	}
	jQuery('#menu_contextuel').hide();
	debut_selection=sel;
	now_selecting=true;
}

function stop_selection(sel) {
	now_selecting=false;

	var fin_selection=sel;

	if (debut_selection>fin_selection) {
		var tmp=debut_selection;
		debut_selection=fin_selection;
		fin_selection=tmp;
	}

	var num_elements = jQuery('.num_wrapper');

	var not_selected = num_elements.filter(debut_selection.prevAll()).add(fin_selection.nextAll());
	var selected = num_elements.not(not_selected);

	selected.toggleClass('num_checked').removeClass('half_transparent');

	var nb_numeros_sel = num_elements.filter('.num_checked').length;
    jQuery('#nb_selection').text(nb_numeros_sel);
    jQuery('#numero_selectionne'  ).toggle(nb_numeros_sel<=1);
    jQuery('#numeros_selectionnes').toggle(nb_numeros_sel> 1);
    jQuery('#update_menu').toggleClass('shown', nb_numeros_sel > 0);

    debut_selection=null;
}

function changer_affichage(type_numeros) {
	jQuery('#menu_contextuel').hide();
    jQuery('#liste_numeros').toggleClass(type_numeros, jQuery('#sel_numeros_'+type_numeros).is(':checked'));
}

function pre_select(selection_courante) {
	if (now_selecting) {
		var debut_selection_temp=debut_selection;
		var fin_selection=selection_courante;
		if (debut_selection !== -1) {
            if (debut_selection>selection_courante) {
                fin_selection=debut_selection;
                debut_selection_temp=selection_courante;
            }
            var not_selected = debut_selection_temp.prevAll().add(fin_selection.nextAll());
            var selected = jQuery('.num_wrapper').not(not_selected);

            selected.addClass('half_transparent');
		}
	}
}

function lighten (element) {
	if (!now_selecting) {
		element.addClass('survole');
	}
}

function unlighten (element) {
	if (!now_selecting) {
		element.removeClass('survole');
	}
}
