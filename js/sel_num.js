var debut_selection=-1;
var now_selecting=false;
var liste;
var l10n_acquisitions=[
	'supprimer_acquisition','description',
	'date_invalide','date_invalide','suppression_acquisition_confirmation',
	'date','nouvelle_acquisition_sauvegarder','selectionner_numeros_a_marquer',
	'les','numeros_selectionnes_enregistres','avec_etat','et','avec_acquisition','confirmer'
];

function disableselect(){
	return false
}

function reEnable(){
	return true
}

function start_selection(sel) {
	
	//if IE4+
	$('liste_numeros').onselectstart=new Function ("return false");
	
	//if NS6
	if (window.sidebar){
		$('liste_numeros').onmousedown=disableselect;
		$('liste_numeros').onclick=reEnable;
	}
	$('menu_contextuel').hide();
	debut_selection=parseInt(sel.id.substring(1,sel.id.length+1));
	now_selecting=true;
}

function stop_selection(sel) {
	now_selecting=false;
	var j=0;
	
	var fin_selection=parseInt(sel.id.substring(1,sel.id.length+1));
	
	if (debut_selection==-1) return;
	if (debut_selection>fin_selection) {
		var tmp=debut_selection;
		debut_selection=fin_selection;
		fin_selection=tmp;
	}

    var nb_selection = $('nb_selection');

    for (var i=debut_selection; i<=fin_selection; i++) {
        var current = $('n'+i);
        if (current) {
			current.setOpacity(1);
			if (current.hasClassName('num_checked')) {
				current.removeClassName('num_checked');
				nb_selection.update(parseInt(nb_selection.innerHTML)-1);
			}
			else {
				current.addClassName('num_checked');
				nb_selection.update(parseInt(nb_selection.innerHTML)+1);
			}
		}
	}
	var nb_numeros_sel=parseInt(nb_selection.innerHTML);
	debut_selection=null;
	if (nb_numeros_sel>1) {
		$('numero_selectionne').setStyle({'display':'none'});
		$('numeros_selectionnes').setStyle({'display':'inline'});
	}
	else {
		$('numero_selectionne').setStyle({'display':'inline'});
		$('numeros_selectionnes').setStyle({'display':'none'});
	}
}

function changer_affichage(type_numeros) {
	$('menu_contextuel').hide();
	$('liste_numeros').toggleClassName(type_numeros, $('sel_numeros_'+type_numeros).checked);
}

function pre_select(element) {
	if (now_selecting) {
		var selection_courante=parseInt(element.id.substring(1,element.id.length+1));
		var debut_selection_temp=debut_selection;
		var fin_selection=selection_courante;
		if (debut_selection==-1) return;
		if (debut_selection>selection_courante) {
			fin_selection=debut_selection;
			debut_selection_temp=selection_courante;
		}
		for (var i=debut_selection_temp;i<=fin_selection;i++) {
			$('n'+i).setOpacity(0.5);
		}
	}
}

function lighten (element) { 
	if (!now_selecting) {
		element.addClassName('survole');
	}
}

function unlighten (element) {
	if (!now_selecting) {
		element.removeClassName('survole');
	}
}

 function isDate(d) {
	 // Cette fonction permet de v�rifier la validit� d'une date au format jj/mm/aa ou jj/mm/aaaa
	 // Par Romuald
	
	 if (d == "") // si la variable est vide on retourne faux
	 return false;
	
	 e = new RegExp("^([0-9]{4})\-[0-9]{1,2}\-[0-9]{1,2}$");
	
	 if (!e.test(d)) // On teste l'expression r�guli�re pour valider la forme de la date
	 return false; // Si pas bon, retourne faux
	
	 // On s�pare la date en 3 variables pour v�rification, parseInt() converti du texte en entier
	 a = parseInt(d.split("-")[0], 10); // ann�e
	 m = parseInt(d.split("-")[1], 10); // mois
	 j = parseInt(d.split("-")[2], 10); // jour
	
	 // D�finition du dernier jour de f�vrier
	 // Ann�e bissextile si annn�e divisible par 4 et que ce n'est pas un si�cle, ou bien si divisible par 400
	 if (a%4 == 0 && a%100 !=0 || a%400 == 0) fev = 29;
	 else fev = 28;
	
	 // Nombre de jours pour chaque mois
	 nbJours = [31,fev,31,30,31,30,31,31,30,31,30,31];
	
	 // Enfin, retourne vrai si le jour est bien entre 1 et le bon nombre de jours, idem pour les mois, sinon retourn faux
	 return ( m >= 1 && m <=12 && j >= 1 && j <= nbJours[m-1] );
 } 