function afficher_diagramme_secteurs(type) {
	new Ajax.Request('Stats.class.php', {
		method: 'post',
		parameters: type+'=true',
		onSuccess:function(transport,data) {
			var config = {
				type: 'pie',
				data: {
					datasets: [{
						data: data.values,
						backgroundColor: data.colors
					}],
					labels: data.labels
				},
				options: {
					responsive: true,
					maintainAspectRatio: false
				}
			};

			var ctx = $("graph_"+type).getContext("2d");
			new Chart(ctx, config);
		}
	});
}

function afficher_histogramme_possessions(data) {
	new Ajax.Request('Stats.class.php', {
		method: 'post',
		parameters : 'possessions=true&fin=true&ids='+JSON.stringify(data.chargements)+'&infos='+JSON.stringify(data.infos),
		onSuccess : function(transport) {
			var data = transport.headerJSON;

			var labels_magazines_longs = data.labels_magazines_longs;
			var labels_pays_longs = data.labels_pays_longs;

			$('canvas-holder').setStyle({width: 30*data.labels.length + 'px'});

			Chart.defaults.global.maintainAspectRatio = false;
			var config = {
				type: 'bar',
				options: {
					title:{
						display:true,
						text: data.title
					},
					responsive: true,
					scales: {
						xAxes: [{
							stacked: true,
							ticks: {
								autoSkip: false
							}
						}],
						yAxes: [{
							stacked: true
						}]
					},
					tooltips: {
						enabled: true,
						mode: 'label',
						callbacks: {
							title: function(tooltipItems) {
								var publicationcode = tooltipItems[0].xLabel;
								return labels_magazines_longs[publicationcode]+
									   ' ('+labels_pays_longs[publicationcode.split('/')[0]]+')';
							}
						}
					}
				}
			};

			var config_abs = Object.clone(config);
			config_abs.data = Object.clone(data);
			config_abs.data.datasets = [data.datasets.possedes, data.datasets.totaux];
			new Chart($$('.graph_possessions.abs')[0].getContext('2d'), config_abs);

			var config_cpt = Object.clone(config);
			config_cpt.data = Object.clone(data);
			config_cpt.data.datasets = [data.datasets.possedes_cpt, data.datasets.totaux_cpt];
			config_cpt.options.tooltips.callbacks.label = function(tooltipItems, data) {
				return data.legend[tooltipItems.datasetIndex] + ' : '+tooltipItems.yLabel + ' %';
			};
			new Chart($$('.graph_possessions.cpt')[0].getContext('2d'), config_cpt);

			$$('#fin_classement, #canvas-holder').invoke('removeClassName', 'hidden');
			$('barre_pct_classement').addClassName('hidden');
			$$('#barre_pct_classement, #chargement_classement_termine, #prefixe_message_classement, #message_classement')
				.invoke('update')
		}
	});
}

function toggleClass(selector, className) {
	$$(selector).invoke('toggleClassName', className)
}

function toggleGraphs() {
	toggleClass('.graph_possessions', 'hidden');
	toggleClass('.graph_type', 'bold');
}