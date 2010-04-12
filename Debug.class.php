<?php
class Debug {
	static $fichier_log='logDM.txt';
	
	static function log($str) {
		$inF = fopen($fichier_log,"w");
		fwrite($inF,date ("d-m-Y H:i:s").$str."\n");
		fclose($inF);
	}
}