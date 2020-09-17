<?php
@error_reporting(  E_ALL & ~E_NOTICE & ~E_DEPRECATED );
include_once 'locales/lang.php';
include_once 'Util.class.php';
include_once 'Database.class.php';

class DM_Core {
    /** @var Database */
    static $d;
}
if (!isset(DM_Core::$d)) {
    DM_Core::$d = new Database();
}
