<?php
@error_reporting(  E_ALL & ~E_NOTICE & ~E_DEPRECATED );
@session_start();
ini_set('session.lifetime', 0);
if (isset($_GET['lang'])) {
    $_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
include_once('Util.class.php');
include_once('Database.class.php');

class DM_Core {
    /**
     * @var Database
     */
    static $d;

    function  __construct() {
    }
}
if (!isset(DM_Core::$d))
DM_Core::$d=new Database();
if (!DM_Core::$d) {
    echo PROBLEME_BD;
    exit(-1);
}
DM_Core::$d->requete('SET NAMES UTF8');