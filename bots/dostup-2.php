<?php
$token = '7337016333:AAHh_MoIJ9mwBE39k4Pggz8eQScG3-l5UgA';
$bot_id = '7337016333'; // Полученный ID
// $channel = '@babyshark4';
// $channel_id = '-1001103182277'; // @babyshark4 Используем числовой ID из getChat

// https://t.me/sharkparty1kk/209 
$channel = '-1001264975707';
$channel_id = '-1001264975707';


// Альтернативный способ проверки через getChat
$url = "https://api.telegram.org/bot{$token}/getChat?chat_id={$channel_id}";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15
]);

$response = curl_exec($ch);
$data = json_decode($response, true);

if (!$data['ok']) {
    die("Ошибка при проверке канала: " . $data['description']);
}

// Проверяем тип чата и доступ
if ($data['result']['type'] === 'channel') {
    if (isset($data['result']['permissions'])) {
        // Для каналов, где бот админ
        echo "Бот является администратором канала";
    } else {
        // Если бот не админ, но канал публичный
        $invite_link = $data['result']['invite_link'] ?? null;
        if ($invite_link) {
            echo "Канал публичный. Попробуйте получить доступ через invite: {$invite_link}";
        } else {
            echo "Канал приватный. Бота нужно добавить как администратора!";
        }
    }
} else {
    echo "Это не канал, а " . $data['result']['type'];
}
