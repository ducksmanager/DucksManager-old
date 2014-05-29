var elements_listes_params_numeros;
var elements_params_numeros;
var associer_date_achat;

function init_footer_ajout_suppr() {
	var footer = $('footer_ajout_suppr');
	elements_listes_params_numeros = footer.select('div.conteneur_liste ul');
	elements_params_numeros = footer.select('div.conteneur_liste li');
	associer_date_achat = footer.select('.achat.date')[0];

	elements_listes_params_numeros
		.invoke('observe','mouseover',function() {
			this.addClassName('open');
		})
		.invoke('observe','mouseout',function() {
			this.removeClassName('open');
			$('liste_achats').removeClassName('open');
		});

	elements_params_numeros
		.invoke('observe','click',function() {
			this.up().select('li').invoke('removeClassName', 'selected');
			this.addClassName('selected');
		});

	footer.select('.achat.date, #liste_achats').invoke('observe', 'mouseover', function() {
		$$('#liste_achats, #parametrage_achat ul').invoke('addClassName', 'open');
	});

	$$('#liste_achats li a').invoke('observe', 'click', function() {
		associer_date_achat.click();
	});
}

function toggle_footer_ajout_suppr(show) {

	$('footer_ajout_suppr').toggleClassName('cache', !show);
	$('main').toggleClassName('avec_footer_ajout_suppr', show);
}