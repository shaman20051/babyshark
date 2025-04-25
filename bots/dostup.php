<?php
$token = '7337016333:AAHh_MoIJ9mwBE39k4Pggz8eQScG3-l5UgA';
$bot_id = '7337016333'; // Полученный ID
// $channel = '@babyshark4';
// $channel_id = '-1001103182277'; // @babyshark4 Используем числовой ID из getChat

// https://t.me/sharkparty1kk/209
// $channel = '@sharkparty1kk';
// $channel_id = '-1002654363165';

// https://t.me/c/1264975707/13 
$channel = '-1001264975707';
$channel_id = '-1001264975707';

$chatInfo = file_get_contents("https://api.telegram.org/bot{$token}/getChat?chat_id={$channel}");
echo $chatInfo;

// // Лучше использовать cURL
// $ch = curl_init();
// curl_setopt_array($ch, [
//     CURLOPT_URL => "https://api.telegram.org/bot{$token}/getChatMember",
//     CURLOPT_POST => true,
//     CURLOPT_POSTFIELDS => http_build_query([
//         'chat_id' => $channel_id, // Теперь используем числовой ID
//         'user_id' => $bot_id
//     ]),
//     CURLOPT_RETURNTRANSFER => true,
//     CURLOPT_TIMEOUT => 15
// ]);

// $response = curl_exec($ch);
// $data = json_decode($response, true);

// if (!$data['ok']) {
//     // Конкретная диагностика ошибки
//     $error = $data['description'] ?? 'Unknown error';
    
//     if (strpos($error, 'user not found') !== false) {
//         echo "Бота нужно добавить в канал! Ссылка: https://t.me/" . substr($channel_id, 4) . "?start=bot";
//     } elseif (strpos($error, 'not enough rights') !== false) {
//         echo "Требуются права администратора для бота!";
//     } else {
//         echo "Ошибка: " . $error;
//     }
//     exit;
// }

// // Проверяем статус
// $status = $data['result']['status'];
// $allowed_statuses = ['creator', 'administrator', 'member'];

// echo in_array($status, $allowed_statuses)
//     ? "Доступ есть! Статус: {$status}"
//     : "Нет доступа. Текущий статус: {$status}";