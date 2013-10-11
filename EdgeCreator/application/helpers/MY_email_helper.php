<?php

function get_admin_email() {
	$properties=parse_ini_file('/home/ducksmanager/ducksmanager.properties');
	
	return $properties['dm_email'];
} 