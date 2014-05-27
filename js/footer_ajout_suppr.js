var elements_listes_params_numeros;
var elements_params_numeros;

function init_footer_ajout_suppr() {
	elements_listes_params_numeros = $('footer_ajout_suppr').select('div.liste ul');
	elements_params_numeros = $('footer_ajout_suppr').select('div.liste li');

	elements_listes_params_numeros
		.invoke('observe','mouseover',function() {
			this.addClassName('open');
		})
		.invoke('observe','mouseout',function() {
			this.removeClassName('open');
		});

	elements_params_numeros
		.invoke('observe','click',function() {
			this.up().select('li').invoke('removeClassName', 'selected');
			this.addClassName('selected');
		})
}

function toggle_footer_ajout_suppr(show) {

	$('footer_ajout_suppr').toggleClassName('cache', !show);
	$('main').toggleClassName('avec_footer_ajout_suppr', show);
}