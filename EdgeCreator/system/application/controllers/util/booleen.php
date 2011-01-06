<?php
class Booleen {
    var $v;
    function __toString() {
        return $this->v ? 'Oui':'Non';
    }
}

?>
