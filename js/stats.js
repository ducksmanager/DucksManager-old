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
                maintainAspectRatio: false,
                legend: {
                    labels: {
                        fontColor: '#fff'
                    }
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var dataset = data.datasets[tooltipItem.datasetIndex];
                            var meta = dataset._meta[Object.keys(dataset._meta)[0]];
                            var total = meta.total;
                            var currentValue = dataset.data[tooltipItem.index];
                            var percentage = parseFloat((currentValue/total*100).toFixed(1));
                            return currentValue + ' (' + percentage + '%)';
                        },
                        title: function(tooltipItem, data) {
                            return data.labels[tooltipItem[0].index];
                        }
                    }
                }
            }
        };

        var ctx = jQuery('#graph_'+type)[0].getContext('2d');
        new Chart(ctx, config);
    });
}

function afficher_histogramme_possessions() {
    jQuery.post('Stats.class.php', {
        graph: 'true',
        possessions: 'true'
    }, function(data) {
        var labels_magazines_longs = data.labels_magazines_longs;
        var labels_pays_longs = data.labels_pays_longs;

        var vertical = data.labels.length > 25;

        var canvasHolderStyle = {};
        canvasHolderStyle[vertical ? 'height' : 'width'] = 100 + 30*data.labels.length + 'px';
        canvasHolderStyle[vertical ? 'width' : 'height'] = '500px';

        Chart.defaults.global.maintainAspectRatio = false;
        var config = {
            type: vertical ? 'horizontalBar' : 'bar',
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
                    axis: vertical ? 'y' : 'x',
                    intersect: false,
                    callbacks: {
                        title: function(tooltipItems) {
                            var publicationcode = tooltipItems[0][vertical ? 'yLabel' : 'xLabel'];
                            if (!labels_magazines_longs[publicationcode]) {
                                labels_magazines_longs[publicationcode] = '?';
                            }
                            return labels_magazines_longs[publicationcode]+
                                   ' ('+labels_pays_longs[publicationcode.split('/')[0]]+')';
                        }
                    }
                }
            }
        };

        var config_abs = jQuery.extend({}, config);
        config_abs.data = jQuery.extend({}, data, { datasets: [data.datasets.possedes, data.datasets.totaux]});
        new Chart(jQuery('.graph_possessions.abs')[0].getContext('2d'), config_abs);

        var config_cpt = jQuery.extend({}, config);
        config_cpt.data = jQuery.extend({}, data, {datasets: [data.datasets.possedes_cpt, data.datasets.totaux_cpt]});
        config_cpt.options.tooltips.callbacks.label = function(tooltipItems, data) {
            return data.legend[tooltipItems.datasetIndex] + ' : '+tooltipItems[vertical ? 'xLabel' : 'yLabel'] + '%';
        };
        new Chart(jQuery('.graph_possessions.cpt')[0].getContext('2d'), config_cpt);

        jQuery('#message_possessions').addClass('hidden');
        jQuery('#canvas-holder, #canvas-controls').removeClass('hidden');
        jQuery('#canvas-holder').css(canvasHolderStyle);
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
                return data.legend[tooltipItems.datasetIndex] + ' : '+tooltipItems.yLabel + '%';
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
    function get_total_until_now(dateCourante, totauxDates) {
        var total_date_until_now = 0;
        $.each(totauxDates, function(date, total_date) {
            if ((dateCourante !== '?' && date <= dateCourante) || date === '?') {
                total_date_until_now += total_date;
            }
        });
        return total_date_until_now;
    }

    jQuery.post('Stats.class.php', {
        graph: 'true',
        achats: 'true'
    }, function(data) {
        var achats = data.datasets;
        if (Object.keys(achats.nouv).length) {
            var achats_pour_graph = {'nouv': [], 'tot': []};
            var totauxMagazines = {};
            var totauxDates = {};

            jQuery.each(achats.nouv, function(publicationcode, achats_publication) {
                totauxMagazines[publicationcode] = 0;
                var achats_publication_nouv = [];
                var achats_publication_tot = [];

                for (var i=0; i<data.dates.length; i++) {
                    var date_achat = data.dates[i];
                    totauxMagazines[publicationcode] += (achats_publication[date_achat] || 0);

                    totauxDates[date_achat] = totauxDates[date_achat] || 0;
                    totauxDates[date_achat] += (achats_publication[date_achat] || 0);

                    achats_publication_nouv.push(achats_publication[date_achat]);
                    achats_publication_tot.push(totauxMagazines[publicationcode]);
                }

                achats_pour_graph.nouv.push({
                    label: data.labels_magazines_longs[publicationcode],
                    backgroundColor: getRandomColor(),
                    data: achats_publication_nouv
                });

                achats_pour_graph.tot.push({
                    label: data.labels_magazines_longs[publicationcode],
                    backgroundColor: getRandomColor(),
                    data: achats_publication_tot
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
                            beforeTitle: function(tooltipItem) {
                                var est_graph_tot = !$('.graph_achats.tot').is('.hidden');
                                return (tooltipItem[0].xLabel === '?'
                                    ? data.nouv_hors_date_l10n
                                    : (est_graph_tot ? data.total_date_l10n : data.nouv_date_l10n) + ' ' + tooltipItem[0].xLabel)
                                    + "\n";
                            },
                            title: function(tooltipItem, data) {
                                return data.datasets[tooltipItem[0].datasetIndex].label;
                            },
                            label: function(tooltipItem) {
                                return tooltipItem.yLabel;
                            },
                            footer: function(tooltipItem) {
                                var est_graph_tot = !$('.graph_achats.tot').is('.hidden');
                                return [
                                    data.tous_magazines_l10n,
                                    est_graph_tot
                                        ? get_total_until_now(tooltipItem[0].xLabel, totauxDates)
                                        : totauxDates[tooltipItem[0].xLabel]
                                ].join('\n');
                            }
                        }
                    }
                }
            };

            var height = Math.min(
                $(window).height(),
                Math.max(
                    300,
                    Object.values(totauxMagazines).reduce(function(total, num) { return total + num;}) / 4
                )
            );

            jQuery('#canvas-holder')
                .css({width: 250 + 30*data.dates.length + 'px', height: height})
                .removeClass('hidden');

            var config_nouv = jQuery.extend({}, config);
            config_nouv.data = { datasets: achats_pour_graph.nouv, labels: data.dates };
            new Chart(jQuery('.graph_achats.nouv')[0].getContext('2d'), config_nouv);

            var config_tot = jQuery.extend({}, config);
            config_tot.data = { datasets: achats_pour_graph.tot, labels: data.dates };
            new Chart(jQuery('.graph_achats.tot')[0].getContext('2d'), config_tot);
        }
        else {
            jQuery('#message_achats_vide').removeClass('hidden');
        }

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
    location.replace(location.href.replace(/&pays=[^&$]+/, '') + '&pays='+pays);
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
                el_li.find('>.notation_auteur').data({auteur: notation.NomAuteurAbrege});
                el_li.find('>.supprimer_auteur>a').data({auteur: notation.NomAuteurAbrege}).on('click', function() {
                    supprimer_auteur(jQuery(this).data().auteur);
                });
                liste_notations.append(el_li);

                // TODO use https://github.com/nashio/star-rating-svg
                el_li.find('>.notation_auteur').starRating({
                    initialRating: notation.Notation || 5,
                    starSize: 15,
                    totalStars: 10,
                    useFullStars: true,
                    callback: function(notation, element) {
                        jQuery.post('Database.class.php', {
                            database: 'true',
                            changer_notation: 'true',
                            auteur: jQuery(element).data().auteur,
                            notation: notation
                        });
                    }
                });
            });
        }
    );
}

function supprimer_auteur (auteur) {
    jQuery.post('Database.class.php',
        {database: 'true', supprimer_auteur: 'true', auteur: auteur},
        function() {
            location.reload();
        }
    );
}
