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

			$('canvas-holder').setStyle({width: 30*data.labels.length + 'px'});

			Chart.defaults.global.maintainAspectRatio = false;
			var config = {
				type: 'bar',
				data: data,
				options: {
					responsive: true,
					// barShowStroke : false
				}
			};

			var ctx = $("graph_possessions").getContext("2d");
			new Chart(ctx, config);


			$('chargement_classement_termine').update();
			$('message_classement').update('Termin&eacute;');
		}
	});
}