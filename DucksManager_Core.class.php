<?php
/**
 * Description of DucksManager_Core
 *
 * @author Bruno
 */
error_reporting(  E_ALL & ~E_NOTICE & ~E_DEPRECATED );
@session_start();
ini_set('session.lifetime', 0);
if (isset($_GET['lang'])) {
    $_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
include_once('Util.class.php');
include_once('Database.class.php');

class DM_Core {
    static $d;
    static $corresp=array();

    function  __construct() {
    }

    static function multipleConstructFromIsv($type_o,InducksISV $isv) {
        $page=Util::get_page($isv->url);
        $tab=str_getcsv($page, '^');
        //echo '<pre>';print_r($tab);echo '</pre>';
        $liste=array();
        $champs_inducks=array();
        for ($i=count($isv->champs);$i<count($tab)-1;$i+=count($isv->champs)) {
            $o=new $type_o;
            $args=array();
            $j=0;
            foreach ($isv->champs as $champ_inducks=>$champ_DM) {
                if (!is_null($champ_DM)) {
                    $valeur=$tab[$i+$j];
                    if ($champ_DM==='FUNC') {
                        $o->inducksToDM($champ_inducks,$valeur);
                    }
                    else
                        $o->$champ_DM=$valeur;
                }
                $j++;
            }
            $liste[implode('/',$o->get_cle())]=$o;
        }
        return $liste;
    }
}
DM_Core::$d=new Database();
if (!DM_Core::$d) {
    echo PROBLEME_BD;
    exit(-1);
}
DM_Core::$d->requete('SET NAMES UTF8');
class InducksISV {
    var $nom;
    var $url;
    var $champs;
    function __construct() {}
}

global $isv_publication;
global $isv_publicationcategory;

$isv_publication=new InducksISV();
$isv_publication->nom='publication';
$isv_publication->url='http://coa.inducks.org/inducks/isv/inducks_publication.isv';
$isv_publication->champs=array('publicationcode'=>'FUNC','countrycode'=>'pays_abrege','languagecode'=>null,'title'=>'nom_complet','size'=>null,'publicationcomment'=>null,'errormessage'=>null);

$isv_publicationcategory=new InducksISV();
$isv_publicationcategory->nom='publicationcategory';
$isv_publicationcategory->url='http://coa.inducks.org/inducks/isv/inducks_publicationcategory.isv';
$isv_publicationcategory->champs=array('publicationcode'=>'FUNC','category'=>'FUNC');

$isv_redirection_magazine=new InducksISV();
$isv_redirection_magazine->nom='issuerange';
$isv_redirection_magazine->url='http://coa.inducks.org/inducks/isv/inducks_issuerange.isv';
$isv_redirection_magazine->champs=array('issuerangecode'=>'FUNC','publicationcode'=>'FUNC','title'=>null,'issuerangecomment'=>null,'errormessage'=>null);

?>
