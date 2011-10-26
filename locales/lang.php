<?php

@session_start();
$lang = array('fr' => 'Fran&ccedil;ais', 'en' => 'English');

class Lang {
	static $codes_inducks = array('en' => 'en_US', 'fr' => 'fr_FR');
}

if (!isset($_SESSION['lang'])) {

	if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER))
		$str_lang = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
	else
		$str_lang=array('fr-FR');
	$str_lang = explode('-', $str_lang[0]);
	if (array_key_exists($str_lang[0], $lang))
		$_SESSION['lang'] = $str_lang[0];
	else
		$_SESSION['lang'] = 'en';
}
@include_once ('locales/' . $_SESSION['lang'] . '.php');
@include_once ($_SESSION['lang'] . '.php');

if (isset($_POST['index'])) {
	if (strpos($_POST['index'], '~') === false)
		echo get_constant($_POST['index']);
	else {
		$arr_l10n = explode('~', $_POST['index']);
		foreach ($arr_l10n as $str)
			echo get_constant($str) . '~';
	}
}

function get_constant($nom_constante) {
	$nom_constante=strtoupper($nom_constante);
	if (is_null(@constant($nom_constante)))
		return L10N_INTROUVABLE . $nom_constante;
	return constant($nom_constante);
}