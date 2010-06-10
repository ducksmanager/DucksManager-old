<?php
@session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
require('Liste.class.php');
$i=2;
$cookie=$_POST['cookie'];

while (isset($_POST['cookie'.$i])) {
	$cookie.='; '.$_POST['cookie'.$i];
	$i++;
}
$cookie=str_replace(':','%3A',$cookie);
$data=urldecode($_POST['data']);
$url=$_POST['url'];
if ($_POST['data']=='rawOutput')
	$data='rawOutput=1';
if ($_POST['url']=='rawOutput')
	$url='http://coa.inducks.org/collection.php?rawOutput=1';
$message  = $_POST['type']." ".$url." HTTP/1.0\r\n";
$message .= "Content-type: application/x-www-form-urlencoded\r\n";
$message .= "User-agent: Mozilla/5.0 (Windows; U; Windows NT 6.0; fr; rv:1.9.0.7) Gecko/2009021910 Firefox/3.0.7 (.NET CLR 3.5.30729)\r\n";
$message .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
$message .= "Accept-Language: fr,fr-fr;q=0.8,en-us;q=0.5,en;q=0.3\r\n";
$message .= "Accept-Encoding: gzip,deflate\r\n";
$message .= "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n";
$message .= "Keep-Alive: 300\r\n";
$message .= "Connection: keep-alive\r\n";
$message .= "Cookie: ".$cookie."\r\n";
$message .= "Content-length: ".strlen( $data )."\r\n";
$message .= "\r\n";
$message .= $data."\r\n";
$fd = fsockopen( gethostbyname($_POST['host']), 80 ); 
fputs($fd,$message);
if (!feof($fd)) {
	$contenu=fgets($fd, 128);
	while (!feof($fd)) {
		$contenu.=fgets($fd, 128);
	}
}
if (isset($_POST['ecrire']) && $_POST['ecrire']=='true') {
	$regex_retrieve_numeros='#<b>[^<]+</b><p><pre>.country\^entrycode\^collectiontype\^comment.(.*)</pre><hr>#is';
	$found=(preg_match($regex_retrieve_numeros,$contenu,$liste)>0);
	if (!$found) {
		echo htmlentities(ERREUR_LECTURE_NUMEROS);
		exit(-1);
	}
	//$regex_split_numeros='#[^^]*#'
	$texte=preg_replace($regex_retrieve_numeros,'$1',$liste[0]);
	$rand=rand();
	$Fnm = "list".$rand.".txt";
	$inF = fopen($Fnm,"w");
	fwrite($inF,$texte);
	echo $Fnm;
}
else
	echo $contenu;
?>