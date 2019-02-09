<?php
@session_start();
ini_set('session.lifetime', 0);

$lang = ['fr' => 'Français', 'en' => 'English'];

class Lang {
    static $codes_inducks = ['en' => 'en_US', 'fr' => 'fr_FR'];
}

if (isset($_GET['lang'])) {
    $_SESSION['lang']=$_GET['lang'];
}

if (!isset($_SESSION['lang'])) {
    if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
        $str_lang = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    }
    else {
        $str_lang = ['fr-FR'];
    }
    $_SESSION['lang'] = explode('-', $str_lang[0])[0];
}

if (!is_string($_SESSION['lang']) || !array_key_exists($_SESSION['lang'], $lang)) {
    $_SESSION['lang'] = 'en';
}

@include __DIR__."/../locales/{$_SESSION['lang']}.php";

if (isset($_POST['keys'])) {
    header('Content-Type: application/json');
    $results = [];
    array_walk($_POST['keys'], function($l10nKey) use(&$results) {
        $results[$l10nKey] = get_constant($l10nKey);
    });
    echo json_encode($results);
}

function get_constant($nom_constante) {
    $nom_constante=strtoupper($nom_constante);
    if (is_null(@constant($nom_constante))) {
        return L10N_INTROUVABLE . $nom_constante;
    }
    return constant($nom_constante);
}