function afficher_diagramme_secteurs(type) {
	new Ajax.Request('Stats.class.php', {
		method: 'post',
		parameters: 'graph=true&'+type+'=true',
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
		parameters : 'graph=true&possessions=true&fin=true&ids='+JSON.stringify(data.chargements)+'&infos='+JSON.stringify(data.infos),
		onSuccess : function(transport) {
			var data = transport.responseJSON;

			var labels_magazines_longs = data.labels_magazines_longs;
			var labels_pays_longs = data.labels_pays_longs;

			$('canvas-holder').setStyle({width: 100 + 30*data.labels.length + 'px'});

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

			$$('#fin_classement, #canvas-holder').invoke('removeClassName', 'hidden');
			$('barre_pct_classement').addClassName('hidden');
			$$('#barre_pct_classement, #chargement_classement_termine, #prefixe_message_classement, #message_classement')
				.invoke('update');

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
		}
	});
}

function afficher_histogramme_stats_auteurs() {
	new Ajax.Request('Stats.class.php', {
		method: 'post',
		parameters : 'graph=true&auteurs=true',
		onSuccess : function(transport) {
			var data = transport.responseJSON;

			if (data.datasets.possedees.data.length) {
				var noms_complets_auteurs = data.labels;

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
									return noms_complets_auteurs[tooltipItems[0].xLabel];
								}
							}
						}
					}
				};

				var config_abs = Object.clone(config);
				config_abs.data = Object.clone(data);
				config_abs.data.datasets = [data.datasets.possedees, data.datasets.manquantes];
				new Chart($$('.graph_auteurs.abs')[0].getContext('2d'), config_abs);

				var config_cpt = Object.clone(config);
				config_cpt.data = Object.clone(data);
				config_cpt.data.datasets = [data.datasets.possedees_pct, data.datasets.manquantes_pct];
				config_cpt.options.tooltips.callbacks.label = function(tooltipItems, data) {
					return data.legend[tooltipItems.datasetIndex] + ' : '+tooltipItems.yLabel + ' %';
				};
				new Chart($$('.graph_auteurs.pct')[0].getContext('2d'), config_cpt);

				$('canvas-holder')
					.setStyle({width: 250 + 50*data.labels.length + 'px'});

				$$('#canvas-holder, #fin_stats_auteur').invoke('removeClassName', 'hidden');
			}
			else {
				$('aucun_resultat_stats_auteur').removeClassName('hidden');
			}

			$('chargement_stats_auteur').addClassName('hidden');
		}
	});
}

function afficher_histogramme_achats() {
	new Ajax.Request('Stats.class.php', {
		method: 'post',
		parameters : 'graph=true&achats=true',
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

			$('canvas-holder')
				.setStyle({width: 30*dates.length + 'px'})
				.removeClassName('hidden');

			var config_nouv = Object.clone(config);
			config_nouv.data = { datasets: achats_pour_graph.nouv, labels: dates };
			new Chart($$('.graph_achats.nouv')[0].getContext('2d'), config_nouv);

			var config_tot = Object.clone(config);
			config_tot.data = { datasets: achats_pour_graph.tot, labels: dates };
			new Chart($$('.graph_achats.tot')[0].getContext('2d'), config_tot);

			$('fin_achats').removeClassName('hidden');
			$('message_achats').addClassName('hidden');

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

function recharger_stats_auteurs() {
	var el_select=$('liste_pays');
	var pays=el_select.options[el_select.options.selectedIndex].id;
	location.replace(location.href.replace(/&pays=[^&$]+/, '') + '&pays='+pays);
}

function init_notations() {
	new Ajax.Request('Database.class.php', {
		method: 'post',
		parameters:'database=true&liste_notations=true',
		onSuccess:function(transport) {
			var notations = transport.responseJSON;

			var liste_notations = $('liste_notations');
			var template = liste_notations.select('.template')[0];
			for (var i=0;i<notations.length;i++) {
				var notation = notations[i];

				var el_li = template.clone(true).removeClassName('template');
				el_li.down('.nom_auteur').update(notation.NomAuteur);
				el_li.down('.notation_auteur').writeAttribute('id', 'notation_auteur_' + notation.NomAuteurAbrege);
				el_li.down('.supprimer_auteur').down('a').writeAttribute('id', 'supprimer_auteur_' + notation.NomAuteurAbrege).observe('click', function() {
					supprimer_auteur(this.id.replace(/^.*_([^_]+)$/,'$1'));
				});
				liste_notations.insert(el_li);

				new Starbox(el_li.down('.notation_auteur'), notation.Notation || 5, {
					buttons: 10,
					max: 10,
					stars: 10,
					rerate: true,
					onRate: function(element, datum) {
						var auteur = datum.identity.replace(/^.+_(.+)$/,'$1');
						var notation = datum.rated;
						new Ajax.Request('Database.class.php', {
							method: 'post',
							parameters:'database=true&changer_notation=true&auteur='+auteur+'&notation='+notation,
							onSuccess:function() {

							}
						});
					}
				});
			}
		}
	});
}

function supprimer_auteur (nom_auteur) {
    new Ajax.Request('Database.class.php', {
        method: 'post',
        parameters:'database=true&supprimer_auteur=true&nom_auteur='+nom_auteur,
        onSuccess:function() {
            location.reload();
        }
    });
}