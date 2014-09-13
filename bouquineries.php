<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
		<style type="text/css">
			html { height: 100% }
			body { height: 100%; margin: 0; padding: 0; }
			#map_canvas { height: 100% }
		</style>
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
		<?php
			require_once('JS.class.php');
			new JS('prototype.js', 'js/bouquineries.js');
		?>
	</head>
	<body onload="initialize()">
		<div id="map_canvas" style="width:100%; height:100%"></div>
	</body>
</html>