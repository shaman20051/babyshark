# Инструкция по созданию Telegram-бота для извлечения комментариев из поста

## Шаг 1: Подготовка окружения

1. **Убедитесь, что у вас установлен PHP 8.2 и Composer**:
   - Проверьте версию PHP: `php -v`
   - Установите Composer, если он отсутствует: https://getcomposer.org/download/

2. **Создайте новый проект**:
   ```bash
   mkdir telegram-comments-bot
   cd telegram-comments-bot
   composer init --require="php:^8.2" --no-interaction
   ```

3. **Установите библиотеку `longman/telegram-bot`**:
   ```bash
   composer require longman/telegram-bot
   ```

4. **Создайте структуру проекта**:
   ```
   telegram-comments-bot/
   ├── src/
   │   └── Bot.php
   ├── vendor/
   ├── .env
   └── composer.json
   ```

## Шаг 2: Создание Telegram-бота

1. **Создайте бота через @BotFather**:
   - Откройте Telegram, найдите @BotFather.
   - Отправьте команду `/start`, затем `/newbot`.
   - Следуйте инструкциям, задайте имя и username бота (например, `@CommentsExtractorBot`).
   - Сохраните полученный **API-токен** (например, `123456789:ABCDEF...`).

2. **Настройте окружение**:
   - Создайте файл `.env` в корне проекта:
     ```env
     BOT_TOKEN=123456789:ABCDEF...
     CHANNEL_ID=@YourChannelName
     ```
   - Установите библиотеку для работы с `.env`:
     ```bash
     composer require vlucas/phpdotenv
     ```

## Шаг 3: Настройка бота для доступа к комментариям

1. **Добавьте бота в канал и группу обсуждений**:
   - Убедитесь, что ваш канал имеет связанную группу обсуждений (настройка в разделе "Обсуждение" в управлении каналом).
   - Добавьте бота в группу обсуждений как администратора:
     - Откройте группу → Управление → Администраторы → Добавить администратора → Найдите бота.
     - Дайте боту права на чтение сообщений.

2. **Отключите режим приватности бота**:
   - В @BotFather отправьте `/setprivacy` для вашего бота.
   - Выберите "Disable", чтобы бот мог видеть все сообщения в группе.

## Шаг 4: Реализация бота

Создайте файл `src/Bot.php` с кодом для обработки комментариев:

```php
<?php

namespace TelegramCommentsBot;

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\Update;
use Dotenv\Dotenv;

class Bot
{
    private Telegram $telegram;
    private string $channelId;

    public function __construct()
    {
        // Загружаем переменные окружения
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        // Инициализируем Telegram-бот
        $this->telegram = new Telegram($_ENV['BOT_TOKEN'], '@CommentsExtractorBot');
        $this->channelId = $_ENV['CHANNEL_ID'];
    }

    public function handle(): void
    {
        // Получаем обновления через getUpdates
        $response = Request::getUpdates(['timeout' => 30]);

        if ($response->isOk()) {
            foreach ($response->getResult() as $update) {
                $this->processUpdate($update);
            }
        } else {
            echo "Ошибка получения обновлений: " . $response->getDescription() . PHP_EOL;
        }
    }

    private function processUpdate(Update $update): void
    {
        // Проверяем, является ли обновление сообщением из группы обсуждений
        $message = $update->getMessage();
        if (!$message) {
            return;
        }

        $chatId = $message->getChat()->getId();
        $groupChatId = $this->getGroupChatId();

        // Проверяем, что сообщение из группы обсуждений
        if ($chatId != $groupChatId) {
            return;
        }

        // Проверяем, является ли сообщение комментарием к посту в канале
        $replyToMessage = $message->getReplyToMessage();
        if (!$replyToMessage) {
            return;
        }

        // Извлекаем данные комментария
        $comment = [
            'user' => $message->getFrom()->getUsername() ?? $message->getFrom()->getFirstName(),
            'text' => $message->getText(),
            'date' => $message->getDate(),
            'message_id' => $message->getMessageId(),
            'post_id' => $replyToMessage->getMessageId()
        ];

        // Сохраняем комментарий в JSON
        $this->saveComment($comment);
    }

    private function getGroupChatId(): ?string
    {
        // Получаем chat_id группы обсуждений
        $response = Request::getChat(['chat_id' => $this->channelId]);
        if ($response->isOk()) {
            $chat = $response->getResult();
            $linkedChatId = $chat->getLinkedChatId();
            return $linkedChatId ? (string)$linkedChatId : null;
        }
        return null;
    }

    private function saveComment(array $comment): void
    {
        $file = __DIR__ . '/../comments.json';
        $comments = [];

        // Читаем существующие комментарии
        if (file_exists($file)) {
            $comments = json_decode(file_get_contents($file), true) ?? [];
        }

        // Добавляем новый комментарий
        $comments[] = $comment;

        // Сохраняем в файл
        file_put_contents($file, json_encode($comments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
```

## Шаг 5: Создание точки входа

Создайте файл `index.php` в корне проекта:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use TelegramCommentsBot\Bot;

try {
    $bot = new Bot();
    // Запускаем цикл обработки обновлений
    while (true) {
        $bot->handle();
    }
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . PHP_EOL;
}
```

## Шаг 6: Запуск бота

1. **Запустите бота**:
   ```bash
   php index.php
   ```

2. **Проверьте работу**:
   - Опубликуйте пост в канале с включенными комментариями.
   - Добавьте комментарий в группе обсуждений.
   - Проверьте, появился ли файл `comments.json` в корне проекта. Пример содержимого:
     ```json
     [
         {
             "user": "@UserName",
             "text": "Это тестовый комментарий!",
             "date": 1698765432,
             "message_id": 123,
             "post_id": 456
         }
     ]
     ```

## Шаг 7: Дополнительные улучшения

1. **Фильтрация по конкретному посту**:
   - Добавьте параметр `POST_ID` в `.env` и фильтруйте комментарии по `post_id` в методе `processUpdate`.

2. **Сохранение в HTML**:
   - Модифицируйте метод `saveComment` для генерации HTML:
     ```php
     private function saveComment(array $comment): void
     {
         $html = "<div><strong>{$comment['user']}</strong>: {$comment['text']}<br><small>" . date('Y-m-d H:i:s', $comment['date']) . "</small></div>\n";
         file_put_contents(__DIR__ . '/../comments.html', $html, FILE_APPEND);
     }
     ```

3. **Обработка ошибок**:
   - Добавьте логирование ошибок в файл:
     ```php
     error_log($e->getMessage(), 3, __DIR__ . '/../error.log');
     ```

## Шаг 8: Деплой

1. **Разместите бота на сервере**:
   - Настройте веб-сервер (например, Nginx) для запуска PHP.
   - Используйте Supervisor или systemd для постоянной работы скрипта.

2. **Настройте Webhook (опционально)**:
   - Вместо `getUpdates` настройте webhook для получения обновлений:
     ```php
     $this->telegram->setWebhook('https://your-server.com/bot.php');
     ```
   - Создайте файл `bot.php` для обработки webhook-запросов.

## Замечания

- **Ограничения Telegram API**: Бот должен быть администратором группы обсуждений, чтобы видеть все комментарии.
- **Безопасность**: Храните `.env` вне публичного доступа и используйте HTTPS для webhook.
- **Производительность**: Для больших каналов используйте базу данных вместо файла для хранения комментариев.