function afficher_histogramme_magazines() {
	new Ajax.Request('Stats.class.php', {
		method: 'post',
		parameters:'publications=true',
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

			var ctx = $("magazines").getContext("2d");
			new Chart(ctx, config);
		}
	});
}