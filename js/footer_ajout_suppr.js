function init_footer_ajout_suppr() {
	$('footer_ajout_suppr')
		.select('div.liste ul')
			.invoke('observe','mouseover',function() {
				this.addClassName('open');
			})
			.invoke('observe','mouseout',function() {
				this.removeClassName('open');
			});
}

function toggle_footer_ajout_suppr(show) {

	$('footer_ajout_suppr').toggleClassName('cache', !show);
	$('main').toggleClassName('avec_footer_ajout_suppr', show);
}