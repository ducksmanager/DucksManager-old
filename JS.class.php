<?php
require_once('Util.class.php');
if (isset($_POST['js']))
	new JS($_POST['js']);
class JS {
	function __construct() {
		$scripts=array();
		$noms=func_get_args();
		foreach($noms as $nom) {
            $prefixe=substr($nom,0,strrpos($nom,'.'));
            if (isset($_GET['debug']) || strpos($nom,'scriptaculous')!==false || in_array($nom,array('prototype-1.7.2.js','js/json/json2.js','js/swfobject.js'))) {
                ?><script type="text/javascript" src="<?=$nom?>"></script><?php
                continue;
            }
            $creer_c=false;
            if (file_exists($prefixe.'_c.js') && file_exists($prefixe.'_c.txt')) {
                $inF = fopen($prefixe.'_c.txt',"r");
                if ($inF) {
                    $date_modif= fgets($inF, 4096);
                    $stat = stat($nom);
                    if ($stat['mtime'] != $date_modif)
                        $creer_c=true;
                }
                else echo 'Erreur sur '.$prefixe.'_c.txt';
            }
            else $creer_c=true;
            if ($creer_c) {
                $inF = fopen($prefixe.'.js',"r");
                if ($inF) {
                    $js='';
                    while (!feof($inF)) {
                            $js.= fgets($inF, 4096);
                    }
//                    $js_c=JSMin::minify($js);
                    $inF = fopen($prefixe.'_c.js',"w");
//                    fwrite($inF,$js_c);
//                    fclose($inF);
                    $stat = stat($nom);
                    $inF = fopen($prefixe.'_c.txt',"w");
                    fwrite($inF,$stat['mtime']);
                }
                else echo 'Erreur sur '.$prefixe.'_c.js';
            }
            $scripts[]=str_replace('/','__',$prefixe.'_c');
		}
		if (count($scripts) == 0)
			return;
		?><script type="text/javascript" src="JS.class.php?srcs=<?=implode(',',$scripts)?>"></script><?php
	}
}

class JSMin {
  const ORD_LF	= 10;
  const ORD_SPACE = 32;

  protected $a		   = '';
  protected $b		   = '';
  protected $input	   = '';
  protected $inputIndex  = 0;
  protected $inputLength = 0;
  protected $lookAhead   = null;
  protected $output	  = '';

  // -- Public Static Methods --------------------------------------------------

  public static function minify($js) {
	$jsmin = new JSMin($js);
	return $jsmin->min();
  }

  // -- Public Instance Methods ------------------------------------------------

  public function __construct($input) {
	$this->input	   = str_replace("\r\n", "\n", $input);
	$this->inputLength = strlen($this->input);
  }

  // -- Protected Instance Methods ---------------------------------------------

  protected function action($d) {
	switch($d) {
	  case 1:
		$this->output .= $this->a;
	  break;
	  case 2:
		$this->a = $this->b;

		if ($this->a === "'" || $this->a === '"') {
		  for (;;) {
			$this->output .= $this->a;
			$this->a	   = $this->get();

			if ($this->a === $this->b) {
			  break;
			}

			if (ord($this->a) <= self::ORD_LF) {
			  throw new JSMinException('Unterminated string literal.');
			}

			if ($this->a === '\\') {
			  $this->output .= $this->a;
			  $this->a	   = $this->get();
			}
		  }
		}

	  break;
	  case 3:
		$this->b = $this->next();

		if ($this->b === '/' && (
			$this->a === '(' || $this->a === ',' || $this->a === '=' ||
			$this->a === ':' || $this->a === '[' || $this->a === '!' ||
			$this->a === '&' || $this->a === '|' || $this->a === '?')) {

		  $this->output .= $this->a . $this->b;

		  for (;;) {
			$this->a = $this->get();

			if ($this->a === '/') {
			  break;
			} elseif ($this->a === '\\') {
			  $this->output .= $this->a;
			  $this->a	   = $this->get();
			} elseif (ord($this->a) <= self::ORD_LF) {
			  throw new JSMinException('Unterminated regular expression '.
				  'literal.');
			}

			$this->output .= $this->a;
		  }

		  $this->b = $this->next();
		}
	}
  }

  protected function get() {
	$c = $this->lookAhead;
	$this->lookAhead = null;

	if ($c === null) {
	  if ($this->inputIndex < $this->inputLength) {
		$c = substr($this->input, $this->inputIndex, 1);
		$this->inputIndex += 1;
	  } else {
		$c = null;
	  }
	}

	if ($c === "\r") {
	  return "\n";
	}

	if ($c === null || $c === "\n" || ord($c) >= self::ORD_SPACE) {
	  return $c;
	}

	return ' ';
  }

  protected function isAlphaNum($c) {
	return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
  }

  protected function min() {
	$this->a = "\n";
	$this->action(3);

	while ($this->a !== null) {
	  switch ($this->a) {
		case ' ':
		  if ($this->isAlphaNum($this->b)) {
			$this->action(1);
		  } else {
			$this->action(2);
		  }
		  break;

		case "\n":
		  switch ($this->b) {
			case '{':
			case '[':
			case '(':
			case '+':
			case '-':
			  $this->action(1);
			  break;

			case ' ':
			  $this->action(3);
			  break;

			default:
			  if ($this->isAlphaNum($this->b)) {
				$this->action(1);
			  }
			  else {
				$this->action(2);
			  }
		  }
		  break;

		default:
		  switch ($this->b) {
			case ' ':
			  if ($this->isAlphaNum($this->a)) {
				$this->action(1);
				break;
			  }

			  $this->action(3);
			  break;

			case "\n":
			  switch ($this->a) {
				case '}':
				case ']':
				case ')':
				case '+':
				case '-':
				case '"':
				case "'":
				  $this->action(1);
				  break;

				default:
				  if ($this->isAlphaNum($this->a)) {
					$this->action(1);
				  }
				  else {
					$this->action(3);
				  }
			  }
			  break;

			default:
			  $this->action(1);
			  break;
		  }
	  }
	}

	return $this->output;
  }

  protected function next() {
	$c = $this->get();

	if ($c === '/') {
	  switch($this->peek()) {
		case '/':
		  for (;;) {
			$c = $this->get();

			if (ord($c) <= self::ORD_LF) {
			  return $c;
			}
		  }

		break;
		case '*':
		  $this->get();

		  for (;;) {
			switch($this->get()) {
			  case '*':
				if ($this->peek() === '/') {
				  $this->get();
				  return ' ';
				}
				break;

			  case null:
				throw new JSMinException('Unterminated comment.');
			}
		  }

		break;
		default:
		  return $c;
	  }
	}

	return $c;
  }

  protected function peek() {
	$this->lookAhead = $this->get();
	return $this->lookAhead;
  }
}

// -- Exceptions ---------------------------------------------------------------
class JSMinException extends Exception {}


if (isset($_GET['srcs'])) {
	foreach(explode(',',$_GET['srcs']) as $src) {
		echo Util::lire_depuis_fichier(str_replace('__','/',$src).'.js')."\n\n\n";
	}
}
?>