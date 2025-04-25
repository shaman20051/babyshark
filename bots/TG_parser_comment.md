Для публичных каналов
Не обязательно добавлять бота как администратора, если:

Канал действительно публичный (нет замка 🔒 в описании)

Вам нужно только читать сообщения и комментарии

Достаточно:

Указать username канала (например, @durov)

Или использовать ID канала в формате -1001234567890

Но есть нюансы:

Для доступа к комментариям бот должен быть подписан на канал

Для некоторых старых публичных каналов может потребоваться добавление бота

Для приватных каналов
Обязательно:

Добавить бота как администратора

Дать права:

Минимум: "Отправка сообщений"

Для комментариев: "Управление обсуждениями"

Как проверить доступ бота
PHP-код для проверки:

php
$response = file_get_contents("https://api.telegram.org/botВАШ_ТОКЕН/getChatMember?chat_id=@username_канала&user_id=ID_бота");

$data = json_decode($response, true);
if ($data['ok'] && $data['result']['status'] != 'left') {
    echo "Бот имеет доступ к каналу";
} else {
    echo "Бот НЕ имеет доступа!";
}
Практические рекомендации
Для публичных каналов:

php
// Достаточно просто указать @username
$chat_id = '@bbc_news'; 
Если бот не видит канал:

php
// Попробуйте сначала добавить бота в канал
$add_url = "https://t.me/ваш_бот?startchannel=@username_канала&admin=post_messages";
echo "Добавьте бота через ссылку: $add_url";
Для supergroup (чаще всего):

php
// Используйте ID в формате -100...
$chat_id = '-1001234567890';
Важно: Даже для публичных каналов, если бот не может получить доступ к комментариям, попробуйте:

Добавить бота в канал как обычного участника

Подождать 2-3 минуты после добавления

Проверить права через getChatMember (код выше)


### Создаем бота

Отличный план! Давайте создадим полноценного Telegram-бота для сбора комментариев. Вот детали по ограничениям и пошаговая реализация:

Ограничения Telegram Bot API (2024)
Общие лимиты: ~30 сообщений/секунду для бота

Конкретно для getDiscussionMessages: ~20 запросов/минуту

Рекомендуемые задержки: 1-2 секунды между запросами

Пошаговое создание бота
1. Создаем бота через @BotFather
Напишите @BotFather в Telegram

Команда /newbot

Укажите имя бота (например, CommentParserBot)

Получите токен вида 123456789:AAFm2eJZx...

2. PHP-код для сбора комментариев
php
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
3. Запуск и особенности работы
Требования:

PHP 7.4+

Включенное расширение cURL

Доступ к файловой системе для записи

Запуск:

bash
php main.php
Что делает бот:

Получает комментарии пачками по 100 штук

Сохраняет в формате: [дата] @username (ID): текст

Соблюдает лимиты API (не более 20 запросов/минуту)

Пишет логи ошибок в error.log

4. Дополнительные улучшения
Для более стабильной работы добавьте:

Обработку медиа-контента:

php
if (isset($message['photo'])) {
    $text = '[Фото] '.($message['caption'] ?? '');
}
Парсинг форматирования:

php
$text = preg_replace('/<[^>]+>/', '', $message['text']); // Удаляем HTML-теги
Конфигурацию через аргументы CLI:

php
if ($argc > 1) {
    define('CHANNEL', $argv[1]);
    define('POST_ID', $argv[2]);
}
Для приватных каналов не забудьте добавить бота как администратора!


*** Как получить  ID бота:
Откройте в браузере (подставив свой токен):

https://api.telegram.org/bot7337016333:AAHh_MoIJ9mwBE39k4Pggz8eQScG3-l5UgA/getMe
Ответ будет содержать ID:

ВАШ_ТОКЕН - 7337016333:AAHh_MoIJ9mwBE39k4Pggz8eQScG3-l5UgA
"id":7337016333

{"ok":true,"result":{
	"id":7337016333,
	"is_bot":true,"first_name":"BShParsComm","username":"BShParsComm_bot","can_join_groups":true,"can_read_all_group_messages":false,"supports_inline_queries":false,"can_connect_to_business":false,"has_main_web_app":false}}