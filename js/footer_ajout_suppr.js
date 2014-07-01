var elements_listes_params_numeros;
var elements_choix_params_numeros;
var associer_date_achat;

function init_footer_ajout_suppr() {
	var footer = $('footer_ajout_suppr');
	elements_listes_params_numeros = footer.select('div.conteneur_liste ul.liste_parametrage');
	elements_choix_params_numeros = footer.select('div.conteneur_liste ul li');
	associer_date_achat = footer.select('#parametrage_ID_Acquisition .date')[0];

	elements_listes_params_numeros
		.invoke('observe','mouseover',function() {
			if (!this.hasClassName('open')) {
				this.addClassName('open');
				var conteneur_liste = this.up('.conteneur_liste');
				if (conteneur_liste.id === 'parametrage_achat'
				 && conteneur_liste.select('li.selected a')[0].hasClassName('date')) {
					var liste_achats = $('liste_achats');
					liste_achats
						.addClassName('open')
						.scrollTop = liste_achats.select('li.selected')[0].offsetTop;

				}
			}
			return false;
		})
		.invoke('observe','mouseout',function() {
			this.removeClassName('open');
			$('liste_achats').removeClassName('open');
		});

	elements_choix_params_numeros
		.invoke('observe','click',function() {
			this.up().select('li').invoke('removeClassName', 'selected');
			this.addClassName('selected');
		});

	footer.select('#parametrage_ID_Acquisition .date, #liste_achats').invoke('observe', 'mouseover', function() {
		$$('#liste_achats, #parametrage_ID_Acquisition ul').invoke('addClassName', 'open');
		return false;
	});

	$$('#liste_achats li a').invoke('observe', 'click', function() {
		associer_date_achat.click();
	});

	$('enregistrer_parametrage_numeros').observe('click', function() {
		var liste = $$('.num_checked').pluck('title');
		var prefixe_parametrage='parametrage_';
		var parametrage = {};
		$$('.conteneur_liste').each(function(conteneur) {
			var nom_parametrage = conteneur.id.replace(prefixe_parametrage,'');
			var valeur = conteneur.down('ul.liste_parametrage li.selected a').name;
			if (nom_parametrage === 'ID_Acquisition' && valeur === '-2') {
				parametrage[nom_parametrage] = conteneur.down('ul#liste_achats li.selected a').name;
			}
			else {
				parametrage[nom_parametrage] = valeur;
			}
		});
		update_numeros(liste,parametrage.Etat,parametrage.ID_Acquisition,parametrage.AV);
		console.log(parametrage);
	});
}

function toggle_footer_ajout_suppr(show) {

	$('footer_ajout_suppr').toggleClassName('cache', !show);
	$('main').toggleClassName('avec_footer_ajout_suppr', show);
}