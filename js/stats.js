function afficher_diagramme_secteurs(type) {
	new Ajax.Request('Stats.class.php', {
		method: 'post',
		parameters: type+'=true',
		onSuccess:function(transport) {
			var data = transport.responseJSON;
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
			var data = transport.responseJSON;

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

function afficher_histogramme_achats() {
	new Ajax.Request('Stats.class.php', {
		method: 'post',
		parameters : 'achats=true',
		onSuccess : function(transport) {
			var data = transport.responseJSON;
			var achats = data.datasets;
			achats.tot = {};

			var dates = [];
			var date_achat = moment(data.premier_achat['Mois']+'-01');
			while (date_achat < new Date()) {
				dates.push(date_achat.format('YYYY-MM'));
				date_achat.add(1, 'month');
			}

			var achats_pour_graph = {'nouv': [], 'tot': []};

			for (var publicationcode in achats.nouv) {
				var achats_publication = {'nouv': achats.nouv[publicationcode], 'tot': {}};
				var achats_publication_arr = {'nouv': [], 'tot': []};

				var date_achat_precedente = null;
				for (var i=0; i<dates.length; i++) {
					date_achat = dates[i];
					if (!achats_publication.nouv[date_achat]) {
						achats_publication.nouv[date_achat] = 0;
					}
					achats_publication.tot[date_achat] =
						(achats_publication.tot[date_achat_precedente] || achats_publication.nouv[''])
					  + achats_publication.nouv[date_achat];

					achats_publication_arr.nouv.push(achats_publication.nouv[date_achat]);
					achats_publication_arr.tot.push(achats_publication.tot[date_achat]);

					date_achat_precedente = date_achat;
				}

				delete achats_publication[''];

				achats_pour_graph.nouv.push({
					label: data.labels_magazines_longs[publicationcode],
					backgroundColor: getRandomColor(),
					data: achats_publication_arr.nouv
				});

				achats_pour_graph.tot.push({
					label: data.labels_magazines_longs[publicationcode],
					backgroundColor: getRandomColor(),
					data: achats_publication_arr.tot
				});
			}

			Chart.defaults.global.maintainAspectRatio = false;
			Chart.defaults.global.legendCallback = function() { return ''; };
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
					legend: {
						display: false
					},
					tooltips: {
						enabled: true,
						mode: 'single',
						callbacks: {
							title: function(tooltipItem, data) {
								return data.datasets[tooltipItem[0].datasetIndex].label;
							},
							label: function(tooltipItem) {
								return tooltipItem.yLabel;
							}
						}
					}
				}
			};

			var config_nouv = Object.clone(config);
			config_nouv.data = { datasets: achats_pour_graph.nouv, labels: dates };
			new Chart($$('.graph_achats.nouv')[0].getContext('2d'), config_nouv);

			var config_tot = Object.clone(config);
			config_tot.data = { datasets: achats_pour_graph.tot, labels: dates };
			new Chart($$('.graph_achats.tot')[0].getContext('2d'), config_tot);

			$('fin_achats').removeClassName('hidden');
			$('message_achats').addClassName('hidden');

			$('canvas-holder')
				.setStyle({width: 30*dates.length + 'px'})
				.removeClassName('hidden');

		}
	});
}

function getRandomColor() {
	var letters = '0123456789ABCDEF'.split('');
	var color = '#';
	for (var i = 0; i < 6; i++ ) {
		color += letters[Math.floor(Math.random() * 16)];
	}
	return color;
}

function toggleClass(selector, className) {
	$$(selector).invoke('toggleClassName', className)
}

function toggleGraphs(type) {
	toggleClass('.graph_'+type, 'hidden');
	toggleClass('.graph_type', 'bold');
}