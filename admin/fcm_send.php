<?php
require_once 'config.php';
function sendPush($tokens, $title, $body) {
    $url = 'https://fcm.googleapis.com/fcm/send';
    $fields = [
        'registration_ids' => $tokens,
        'notification' => [
            'title' => $title,
            'body' => $body
        ]
    ];
    $headers = [
        'Authorization: key=' . FCM_SERVER_KEY,
        'Content-Type: application/json'
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
?>