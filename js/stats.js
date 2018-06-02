function afficher_diagramme_secteurs(type) {
	jQuery.post('Stats.class.php', {
			graph: 'true',
			type: 'true'
		},
		function(data) {
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

			var ctx = jQuery('#graph_'+type).getContext('2d');
			new Chart(ctx, config);
		}
	);
}

function afficher_histogramme_possessions(data) {
	jQuery.post('Stats.class.php', {
		data : {
			graph: 'true',
			possessions: 'true',
			fin: 'true',
			ids: JSON.stringify(data.chargements),
			infos: JSON.stringify(data.infos)
		},
		uccess : function(data) {
			var labels_magazines_longs = data.labels_magazines_longs;
			var labels_pays_longs = data.labels_pays_longs;

			jQuery('#canvas-holder').css({width: 100 + 30*data.labels.length + 'px'});

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

			jQuery('#fin_classement, #canvas-holder').removeClass('hidden');
			jQuery('#barre_pct_classement').addClass('hidden');
			jQuery('#barre_pct_classement, #chargement_classement_termine, #prefixe_message_classement, #message_classement')
				.text('');

			var config_abs = jQuery.extend({}, config);
			config_abs.data = jQuery.extend({}, data, { datasets: [data.datasets.possedes, data.datasets.totaux]});
			new Chart(jQuery('.graph_possessions.abs')[0].getContext('2d'), config_abs);

			var config_cpt = jQuery.extend({}, config);
			config_cpt.data = jQuery.extend({}, data, {datasets: [data.datasets.possedes_cpt, data.datasets.totaux_cpt]});
			config_cpt.options.tooltips.callbacks.label = function(tooltipItems, data) {
				return data.legend[tooltipItems.datasetIndex] + ' : '+tooltipItems.yLabel + ' %';
			};
			new Chart(jQuery('.graph_possessions.cpt')[0].getContext('2d'), config_cpt);
		}
	});
}

function afficher_histogramme_stats_auteurs() {
	jQuery.post('Stats.class.php', {
		success : function(data) {
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

				var config_abs = jQuery.extend({}, config);
				config_abs.data = jQuery.extend({}, data, { datasets: [data.datasets.possedees, data.datasets.manquantes]});
				new Chart(jQuery('.graph_auteurs.abs')[0].getContext('2d'), config_abs);

				var config_cpt = jQuery.extend({}, config);
				config_cpt.data = jQuery.extend({}, data, {datasets: [data.datasets.possedees_pct, data.datasets.manquantes_pct]});
				config_cpt.options.tooltips.callbacks.label = function(tooltipItems, data) {
					return data.legend[tooltipItems.datasetIndex] + ' : '+tooltipItems.yLabel + ' %';
				};
				new Chart(jQuery('.graph_auteurs.pct')[0].getContext('2d'), config_cpt);

				jQuery('#canvas-holder').css({width: 250 + 50*data.labels.length + 'px'});
				jQuery('#canvas-holder, #fin_stats_auteur').removeClass('hidden');
			}
			else {
				jQuery('#aucun_resultat_stats_auteur').removeClass('hidden');
			}

			jQuery('#chargement_stats_auteur').addClass('hidden');
		}
	});
}

function afficher_histogramme_achats() {
	jQuery.post('Stats.class.php', {
		data : {graph: 'true', achats: 'true'},
		uccess : function(data) {
			var achats = data.datasets;
			achats.tot = {};

			var dates = [];
			var date_achat = moment(data.premier_achat['Mois']+'-01');
			while (date_achat < new Date()) {
				dates.push(date_achat.format('YYYY-MM'));
				date_achat.add(1, 'month');
			}

			var achats_pour_graph = {'nouv': [], 'tot': []};

			jQuery.each(achats.nouv, function(publicationcode, achat) {
				var achats_publication = {'nouv': achat, 'tot': {}};
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
			});

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

			jQuery('#canvas-holder')
				.css({width: 30*dates.length + 'px'})
				.removeClass('hidden');

			var config_nouv = jQuery.extend({}, config);
			config_nouv.data = { datasets: achats_pour_graph.nouv, labels: dates };
			new Chart(jQuery('.graph_achats.nouv')[0].getContext('2d'), config_nouv);

			var config_tot = jQuery.extend({}, config);
			config_tot.data = { datasets: achats_pour_graph.tot, labels: dates };
			new Chart(jQuery('.graph_achats.tot')[0].getContext('2d'), config_tot);

			jQuery('#fin_achats').removeClass('hidden');
			jQuery('#message_achats').addClass('hidden');

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

function toggleGraphs(type) {
	jQuery('.graph_'+type).toggleClass('hidden');
	jQuery('.graph_type').toggleClass('bold');
}

function recharger_stats_auteurs() {
	var el_select=jQuery('#liste_pays');
	var pays=el_select[0].options[el_select.options.selectedIndex].id;
	location.replace(location.href.replace(/&pays=[^&jQuery]+/, '') + '&pays='+pays);
}

function init_notations() {
	jQuery.post('Database.class.php',
		{database: 'true', liste_notations: 'true'},
		function(notations) {
			var liste_notations = jQuery('#liste_notations');
			var template = liste_notations.find('.template');

			jQuery.each(notations, function(i, notation) {
				var el_li = template.clone(true).removeClass('template');
				el_li.find('>.nom_auteur').text(notation.NomAuteur);
				el_li.find('>.notation_auteur').attr({id: 'notation_auteur_' + notation.NomAuteurAbrege});
				el_li.find('>.supprimer_auteur>a').attr({id: 'supprimer_auteur_' + notation.NomAuteurAbrege}).on('click', function() {
					supprimer_auteur(jQuery(this).attr('id').replace(/^.*_([^_]+)jQuery/,'jQuery1'));
				});
				liste_notations.append(el_li);

				new Starbox(el_li.find('>.notation_auteur')[0], notation.Notation || 5, {
					buttons: 10,
					max: 10,
					stars: 10,
					rerate: true,
					onRate: function(element, datum) {
						var auteur = datum.identity.replace(/^.+_(.+)jQuery/,'jQuery1');
						var notation = datum.rated;
						jQuery.post('Database.class.php', {
							url: {database: 'true', changer_notation: 'true', auteur: auteur, notation: notation}
						});
					}
				});
			});
		}
	);
}

function supprimer_auteur (nom_auteur) {
    jQuery.post('Database.class.php',
        {database: 'true', supprimer_auteur: 'true', nom_auteur: nom_auteur},
	    function() {
            location.reload();
        }
    );
}
