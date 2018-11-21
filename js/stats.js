function afficher_diagramme_secteurs(type) {
	var parameters = {};
	parameters[type] = 'true';
	jQuery.post('Stats.class.php', jQuery.extend({}, parameters, {
		graph: 'true'
	}),
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

		var ctx = jQuery('#graph_'+type)[0].getContext('2d');
		new Chart(ctx, config);
	});
}

function afficher_histogramme_possessions(data) {
	jQuery.post('Stats.class.php', {
		graph: 'true',
		possessions: 'true',
		fin: 'true',
		ids: JSON.stringify(data.chargements),
		infos: JSON.stringify(data.infos)
	}, function(data) {
		var labels_magazines_longs = data.labels_magazines_longs;
		var labels_pays_longs = data.labels_pays_longs;

		jQuery('#canvas-holder').css({height: 100 + 30*data.labels.length + 'px'});

		Chart.defaults.global.maintainAspectRatio = false;
		var config = {
            type: 'horizontalBar',
			options: {
				title:{
					display:true,
					text: data.title
				},
				responsive: true,
				scales: {
					xAxes: [{
                        ticks: {
                            stepSize: 1
                        }
					}],
					yAxes: [{
						stacked: true
					}]
				},
				tooltips: {
					enabled: true,
					position: 'nearest',
                    mode: 'index',
                    axis: 'y',
                    intersect: false,
					callbacks: {
						title: function(tooltipItems) {
							var publicationcode = tooltipItems[0].yLabel;
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
	});
}

function afficher_histogramme_stats_auteurs() {
	jQuery.post('Stats.class.php', {
		graph: 'true',
		auteurs: 'true'
	}, function(data) {
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
	});
}

function afficher_histogramme_achats() {
	jQuery.post('Stats.class.php', {
		graph: 'true',
		achats: 'true'
	}, function(data) {
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

function toggleGraphs(element, type) {
	if (!jQuery(element).hasClass('active')) {
		jQuery('.graph_'+type).toggleClass('hidden');
    }
}

function recharger_stats_auteurs() {
	var pays=jQuery('#liste_pays').find('option:selected').attr('id');
	location.replace(location.href.replace(/&pays=[^&jQuery]+/, '') + '&pays='+pays);
}

function init_autocompleter_auteurs() {
    jQuery('#auteur_nom').typeahead({
        source: function(inputText, callback) {
            jQuery.post('Database.class.php', {
                database: 'true',
                liste_auteurs: 'true',
                value: inputText
            }, callback)
        },
        afterSelect: function(item) {
            jQuery('#auteur_id').val(item.id);
        }
    });
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
					supprimer_auteur(jQuery(this).attr('id').replace(/^.*_([^_]+)$/,'$1'));
				});
				liste_notations.append(el_li);

				// TODO use https://github.com/nashio/star-rating-svg
				el_li.find('>.notation_auteur').starRating({
                    initialRating: notation.Notation || 5,
                    starSize: 15,
					totalStars: 10,
                    useFullStars: true,
					callback: function(notation, element) {
						var auteur = element.attr('id').replace(/^.+_(.+)$/,'$1');
						jQuery.post('Database.class.php', {
							database: 'true',
							changer_notation: 'true',
							auteur: auteur,
							notation: notation
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
