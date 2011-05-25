<?php
switch($format) {
	case 'json':
		header("X-JSON: " . json_encode($liste));
	break;
	default:
		?><select><?php
		foreach($liste as $option) {
			?><option name="<?=$option?>"><?=$option?></option><?php
		}
		?></select><?php
	break;
}

?>