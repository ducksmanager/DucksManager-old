<?php
$noAuth = true;
date_default_timezone_set('Europe/Paris');
include_once('dedibox_api.php');

if (DediboxApi::isDediboxAlive()) {
    echo 'Server is up<br />';
}
else {
    echo 'Server is down<br />';
    $lastRebootAttemptFilename = 'last_reboot_attempt';
    $lastRebootAttempt = DateTime::createFromFormat('Y-m-d H:i:s', file_get_contents($lastRebootAttemptFilename));
    $secondsSinceLastReboot = time() - $lastRebootAttempt->getTimestamp();

    if ($secondsSinceLastReboot > 20 * 60) {
        DediboxApi::reboot(ServeurCoa::getCoaServer('dedibox'));
        file_put_contents($lastRebootAttemptFilename, date_format(new DateTime('now'), 'Y-m-d H:i:s'));
        echo 'Rebooting<br />';
    }
    else {
        echo 'Server is already rebooting<br />';
    }
}