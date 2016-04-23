function afficher_histogramme_magazines() {
	new Ajax.Request('magazines_camembert.php', {
		method: 'post',
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

			var ctx = $("my_chart").getContext("2d");
			var chart = new Chart(ctx, config);
		}
	});
}