<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
include_once '../Database.class.php';
include_once '../authentification.php';

function call_online_api($token, $http_method, $endpoint, $get = [], $post = [])
{
    if (!empty($get)) {
        $endpoint .= '?' . http_build_query($get);
    }

    $call = curl_init();
    curl_setopt($call, CURLOPT_URL, 'https://api.online.net/api/v1' . $endpoint);
    curl_setopt($call, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'X-Pretty-JSON: 1']);
    curl_setopt($call, CURLOPT_RETURNTRANSFER, true);

    if ($http_method === 'POST') {
        curl_setopt($call, CURLOPT_POST, true);
        curl_setopt($call, CURLOPT_POSTFIELDS, http_build_query($post));
    }

    return curl_exec($call);
}

$coaMachine = ServeurCoa::getCoaServer('dedibox2');

echo call_online_api(
    $coaMachine->restart_token,
    'POST',
    '/server/reboot/'.$coaMachine->machine_id,
    null,
    ['reason' => 'Server unresponsive']
);