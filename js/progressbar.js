
function initProgressBar(id_event) {
	var statAjax = new Ajax.PeriodicalUpdater($('infos_maj'),'getProgress.php', {
		frequency:0.5,
		method : 'post',
		parameters : 'event='+id_event,
		onSuccess : function(transport, json) {
			var pct=parseInt(transport.responseText);
			if (pct>100) pct=100;
			$('progressbar').setStyle({'width':transport.responseText+'%'});
			if (parseInt(transport.responseText)==100) {
				statAjax.stop();
			}
		}
	});
}