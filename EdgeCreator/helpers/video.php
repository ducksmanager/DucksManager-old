<script type='text/javascript' src='mediaplayer/swfobject.js'></script>

<div id='mediaspace'>This text will be replaced</div>

<script type='text/javascript'>
  var so = new SWFObject('mediaplayer/player.swf','ply','320','260','9','#000000');
  so.addParam('allowfullscreen','true');
  so.addParam('allowscriptaccess','always');
  so.addParam('wmode','opaque');
  so.addVariable('file','../<?=$_GET['file']?>');
  so.write('mediaspace');
</script>