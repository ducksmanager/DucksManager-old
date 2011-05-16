<?php
if (isset($options))
	header("X-JSON: " . json_encode($options));
if (isset($etapes))
	header("X-JSON: " . json_encode($etapes));
?>