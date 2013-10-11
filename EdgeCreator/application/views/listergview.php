<?php
switch($format) {
	case 'json':
		echo json_encode($liste);
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