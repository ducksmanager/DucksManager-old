var footer;
var elements_listes_params_numeros;
var elements_choix_params_numeros;
var liste_achats;
var btn_creer_achat;
var form_nouvel_achat;
var nouvelle_date_achat;
var nouvelle_date_achat_ok;
var nouvelle_date_achat_annuler;
var associer_date_achat;

function init_footer_ajout_suppr() {
	footer = $('footer_ajout_suppr');
	elements_listes_params_numeros = footer.select('div.conteneur_liste ul.liste_parametrage');
	elements_choix_params_numeros = footer.select('div.conteneur_liste ul li');
	liste_achats = $('liste_achats');

	btn_creer_achat = liste_achats.down('.btn_creer_achat');
	form_nouvel_achat = liste_achats.down('.form_nouvel_achat');
	nouvelle_date_achat = form_nouvel_achat.down('#calendarview_input');
	nouvelle_date_achat_ok = form_nouvel_achat.down('#nouvelle_date_ok');
	nouvelle_date_achat_annuler = form_nouvel_achat.down('#nouvelle_date_annuler');

	associer_date_achat = footer.down('#parametrage_ID_Acquisition .date');

	elements_listes_params_numeros
		.invoke('observe','mouseover',function() {
			if (!this.hasClassName('open')) {
				this.addClassName('open');
				var conteneur_liste = this.up('.conteneur_liste');
				if (conteneur_liste.id === 'parametrage_achat'
				 && conteneur_liste.select('li.selected a.achat')[0].hasClassName('date')) {
					liste_achats
						.addClassName('open')
						.scrollTop = liste_achats.select('li.selected')[0].offsetTop;

				}
			}
			return false;
		})
		.invoke('observe','mouseout',function() {
			this.removeClassName('open');
			liste_achats.removeClassName('open');
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

	liste_achats.observe('mouseout',function() {
		this.removeClassName('open');
		elements_listes_params_numeros.invoke('removeClassName','open');
	});

	[btn_creer_achat,nouvelle_date_achat_annuler].invoke('observe','click', function() {
		liste_achats.down('.form_nouvel_achat').toggleClassName('template');
		liste_achats.down('.creer_date_achat').toggleClassName('cache');
	});

	nouvelle_date_achat.calendarviewable({
		'dateFormat' : '%d/%m/%Y'
	});

	liste_achats.select('li .supprimer_date_achat').invoke('observe', 'click', function(e) {
		if (confirm(l10n_acquisitions['suppression_date_achat_confirmation'])) {
			var element_achat = Event.element(e).next('a.achat');
			new Ajax.Request('Database.class.php', {
				method: 'post',
				parameters:'database=true&supprimer_acquisition='+element_achat.readAttribute('name'),
				onSuccess:function() {
					element_achat.up('li').remove();
				}
			});
		}
	});

	liste_achats.select('li a.achat').invoke('observe', 'click', function() {
		associer_date_achat.click();
		return false;
	});


	$('enregistrer_parametrage_numeros').observe('click', function() {
		var liste = $$('.num_checked').pluck('title');
		var prefixe_parametrage='parametrage_';
		var parametrage = {};
		$$('.conteneur_liste').each(function(conteneur) {
			var nom_parametrage = conteneur.id.replace(prefixe_parametrage,'');
			var valeur = conteneur.down('ul.liste_parametrage li.selected a').name;
			if (nom_parametrage === 'ID_Acquisition' && valeur === '-2') {
				parametrage[nom_parametrage] = liste_achats.select('li.selected a.achat')[0].name;
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

	footer.toggleClassName('cache', !show);
	$('main').toggleClassName('avec_footer_ajout_suppr', show);
}