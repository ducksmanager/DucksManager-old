function afficher_histogramme(type) {
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