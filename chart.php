<?php
include 'OpenFlashChart/php-ofc-library/open-flash-chart.php';

$title = new title( date("D M d Y") );

$bar = new bar();
$bar->set_values( array(9,8,7,6,5,4,3,2,1) );

$chart_1 = new open_flash_chart();
$chart_1->set_title( $title );
$chart_1->add_element( $bar );


// generate some random data
srand((double)microtime()*1000000);

$tmp = array();
for( $i=0; $i<9; $i++ )
  $tmp[] = rand(1,10);

$bar_2 = new bar();
$bar_2->set_values( $tmp );

$chart_2 = new open_flash_chart();
$chart_2->set_title( new title( "Chart 2 :-)" ) );
$chart_2->add_element( $bar_2 );


//
// This is the VIEW section:
//

?>

<html>
<head>

<script type="text/javascript" src="js/json/json2.js"></script>
<script type="text/javascript" src="js/swfobject.js"></script>
<script type="text/javascript">
swfobject.embedSWF("open-flash-chart.swf", "my_chart", "350", "200", "9.0.0");
</script>

<script type="text/javascript">

function ofc_ready()
{
    //alert('ofc_ready');
}

function open_flash_chart_data()
{
    //alert( 'reading data' );
    return JSON.stringify(data_1);
}

function load_1()
{
  tmp = findSWF("my_chart");
  x = tmp.load( JSON.stringify(data_1) );
}

function load_2()
{
  //alert("loading data_2");
  tmp = findSWF("my_chart");
  x = tmp.load( JSON.stringify(data_2) );
}

function findSWF(movieName) {
  if (navigator.appName.indexOf("Microsoft")!= -1) {
    return window[movieName];
  } else {
    return document[movieName];
  }
}
    
var data_1 = <?php echo $chart_1->toPrettyString(); ?>;
var data_2 = <?php echo $chart_2->toPrettyString(); ?>;

</script>


</head>
<body>

<div id="my_chart"></div>
<br>
<a href="javascript:load_1()">display data_1</a> || <a href="javascript:load_2()">display data_2</a>
</body>
</html>