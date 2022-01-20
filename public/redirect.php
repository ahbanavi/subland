<?php

use SubLand\Utilities\Helpers;

require_once '../bootstrap/boot.php';

$telegramIPs = [
    // based on https://ipinfo.io/AS62041

    // 149.154.160.0/22
    [
        'lower' => (float) sprintf("%u", ip2long('149.154.160.0')),
        'upper' => (float) sprintf("%u", ip2long('149.154.163.255'))
    ],

    // 149.154.164.0/22
    [
        'lower' => (float) sprintf("%u", ip2long('149.154.164.0')),
        'upper' => (float) sprintf("%u", ip2long('149.154.167.255'))
    ],

    // 91.108.4.0/22
    [
        'lower' => (float) sprintf("%u", ip2long('91.108.4.0')),
        'upper' => (float) sprintf("%u", ip2long('91.108.7.255'))
    ],

    // 91.108.56.0/22
    [
        'lower' => (float) sprintf("%u", ip2long('91.108.56.0')),
        'upper' => (float) sprintf("%u", ip2long('91.108.59.255'))
    ],

    // 91.108.8.0/22
    [
        'lower' => (float) sprintf("%u", ip2long('91.108.8.0')),
        'upper' => (float) sprintf("%u", ip2long('91.108.11.255'))
    ],

    // 95.161.64.0/20
    [
        'lower' => (float) sprintf("%u", ip2long('95.161.64.0')),
        'upper' => (float) sprintf("%u", ip2long('95.161.79.255'))
    ]
];

$ip_dec = (float) sprintf("%u", ip2long($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR']));
$isTelegram = false;
foreach ($telegramIPs as $telegramIP) {
    if (!$isTelegram) {
        // Make sure the IP is from telegram.
        if ($ip_dec >= $telegramIP['lower'] and $ip_dec <= $telegramIP['upper']) {
            $isTelegram = true;
        }
    }
}
if (!$isTelegram) {
    header("Location: https://t.me/$_ENV[BOT_USER_NAME]");
} else {
    $array = explode('/', $_SERVER['REQUEST_URI']);
    $postID = (int) base64_decode(Helpers::base64UrlDecode(end($array)));
    header("Location: https://t.me/$_ENV[UPLOAD_CHANNEL]/$postID");
}
