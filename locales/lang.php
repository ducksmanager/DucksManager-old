<?php
$lang=array('fr'=>'Fran&ccedil;ais','en'=>'English');
global $codes_inducks;$codes_inducks=array('en'=>0,'fr'=>4);
if (!isset($_SESSION['lang'])) {
	
	if (array_key_exists('HTTP_ACCEPT_LANGUAGE',$_SERVER))
		$str_lang=explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
	else
		$str_lang=array('fr-FR');
	$str_lang=explode('-',$str_lang[0]);
	if (array_key_exists($str_lang[0],$lang))
		$_SESSION['lang']=$str_lang[0];
	else
		$_SESSION['lang']='en';
}
@include_once ('locales/'.$_SESSION['lang'].'.php');
@include_once ($_SESSION['lang'].'.php');
L::setL10n($l10n);
if (isset($_POST['index'])) {
	if (strpos($_POST['index'],'~')===false)
		echo L::_($_POST['index']);
	else {
		$arr_l10n=explode('~',$_POST['index']);
		foreach ($arr_l10n as $str)
			echo L::_($str).'~';
	}
		
}
class L {
	static $l10n;
	static function _($index) {
		if (array_key_exists($index, self::$l10n))
			return self::$l10n[$index];
		else
			return self::$l10n['l10n_introuvable'].' '.$index;
	}
	
	static function setL10n($l10n) {
		self::$l10n=$l10n;
	}
}