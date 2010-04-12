<?php
$auteur='DR';
$adresse_auteur='http://coa.inducks.org/comp2.php?code=&keyw=&keywt=i&exactpg=&pg1=&pg2=&bro2=&bro3=&kind=0&rowsperpage=0&columnsperpage=0&hero=&xapp=&univ=&xa2=&creat='.$auteur.'&creat2=&plot=&plot2=&writ=&writ2=&art=&art2=&ink=&ink2=&pub1=&pub2=&part=&ser=&xref=&mref=&xrefd=&repabb=&repabbc=al&imgmode=0&vdesc2=on&vdesc=en&vfr=on&sort1=auto';
$regex_histoire_code_personnages='#<tr([^>]+>.<td[^>]+><[^>]+><[^>]+><br>.<A[^>]+><[^>]+>[^<]+</font></A> </td>.<td>[ ]*(<[^>]+>)?(<A [^<]+</A>[, ]*)*(</small>)?((<i>[^<]*</i>)?<br>)?.(.<i>[^<]*</i>)?(<br>.)?</td>.<td>[^<]*<br>.<small>[^<]*<br>..</small></td>.<td>)#is';//([^:]*: <A[^<]*</A>,?[ ]+)*</td>
$handle = @fopen($adresse_auteur, "r");
if ($handle) {
	$buffer="";
   	while (!feof($handle)) {
     	$buffer.= fgets($handle, 4096);
   	}
   	fclose($handle);
}
else {
	echo 'Erreur de connexion &agrave; Inducks!';
}
echo preg_replace($regex_histoire_code_personnages,'<tr style="background-color:red;"$1',$buffer);
//preg_match_all($regex_histoire_code_personnages,$buffer,$matches);
//echo '<pre>';print_r($matches[0]);echo '</pre>';

?>