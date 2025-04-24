<?php
// config.php - настройки
define('BOT_TOKEN', 'ВАШ_ТОКЕН');
define('CHANNEL', '@babyshark4'); // или ID канала
define('POST_ID', 13402);
define('OUTPUT_FILE', 'comments.txt');

// functions.php - основные функции
function telegramRequest($method, $params = []) {
    $url = "https://api.telegram.org/bot".BOT_TOKEN."/$method";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    if(curl_errno($ch)) {
        file_put_contents('error.log', curl_error($ch), FILE_APPEND);
    }
    curl_close($ch);
    
    return json_decode($response, true);
}

function saveComment($data) {
    $output = sprintf(
        "[%s] @%s (%s): %s\n",
        date('Y-m-d H:i:s', $data['date']),
        $data['user']['username'] ?? 'no_username',
        $data['user']['id'],
        str_replace("\n", " ", $data['text'])
    );
    
    file_put_contents(OUTPUT_FILE, $output, FILE_APPEND);
    echo $output; // Вывод в консоль
}

// main.php - основная логика
$offset = 0;
$processed = 0;
$limit_per_minute = 20; // Лимит Telegram
$start_time = time();

while (true) {
    $response = telegramRequest('getDiscussionMessages', [
        'chat_id' => CHANNEL,
        'message_id' => POST_ID,
        'offset' => $offset,
        'limit' => 100
    ]);
    
    if (!$response['ok'] || empty($response['result'])) {
        file_put_contents('error.log', "Ошибка или нет данных: ".print_r($response, true), FILE_APPEND);
        break;
    }
    
    foreach ($response['result'] as $message) {
        // Пропускаем оригинальный пост
        if ($message['message_id'] == POST_ID) continue;
        
        saveComment([
            'date' => $message['date'],
            'user' => $message['from'],
            'text' => $message['text'] ?? '[медиа-контент]'
        ]);
        
        $processed++;
    }
    
    $offset += count($response['result']);
    
    // Соблюдаем лимиты API
    if ($processed >= $limit_per_minute) {
        $elapsed = time() - $start_time;
        if ($elapsed < 60) {
            sleep(60 - $elapsed); // Ждем до конца минуты
        }
        $processed = 0;
        $start_time = time();
    }
    
    // Небольшая пауза между запросами
    usleep(500000); // 0.5 секунды
}