<?php
@session_start();
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

if (isset($_POST['index'])) {
	if (strpos($_POST['index'],'~')===false)
		echo constant(strtoupper ($_POST['index']));
	else {
		$arr_l10n=explode('~',$_POST['index']);
		foreach ($arr_l10n as $str)
                    echo constant(strtoupper ($str)).'~';
	}
		
}