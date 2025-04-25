# Инструкция по созданию Telegram-приложения для извлечения комментариев на PHP 8.2

## Шаг 1: Подготовка окружения

1. **Убедитесь, что у вас установлен PHP 8.2 и Composer**:
   - Проверьте версию PHP: `php -v`
   - Установите Composer, если он отсутствует: https://getcomposer.org/download/

2. **Создайте новый проект**:
   ```bash
   mkdir telegram-comments-app
   cd telegram-comments-app
   composer init --require="php:^8.2" --no-interaction
   ```

3. **Установите библиотеку `danog/madelineproto`**:
   ```bash
   composer require danog/madelineproto
   ```

4. **Установите библиотеку для работы с `.env`**:
   ```bash
   composer require vlucas/phpdotenv
   ```

5. **Создайте структуру проекта**:
   ```
   telegram-comments-app/
   ├── src/
   │   └── App.php
   ├── vendor/
   ├── .env
   └── composer.json
   ```

## Шаг 2: Регистрация приложения на https://my.telegram.org/apps

1. **Создайте Telegram-аккаунт**:
   - Установите Telegram на телефон или используйте веб-версию.
   - Зарегистрируйтесь с номером телефона, если у вас нет аккаунта.

2. **Перейдите на https://my.telegram.org**:
   - Откройте https://my.telegram.org и войдите, используя номер телефона, связанный с вашим Telegram-аккаунтом.
   - Введите код подтверждения, отправленный в Telegram или по SMS.

3. **Создайте приложение**:
   - На главной странице выберите **API development tools**.
   - Заполните форму:
     - **App title**: Например, `CommentsExtractorApp`
     - **Short name**: Например, `CommentsApp`
     - **URL**: Оставьте пустым или укажите `N/A`
     - **Platform**: Выберите `Desktop` или `Web`
     - **Description**: Краткое описание, например, `App for extracting comments`
   - Нажмите **Create application**.
   - Сохраните полученные **api_id** (числовой идентификатор) и **api_hash** (строка).

   **Примечание**: Если возникает ошибка "ERROR":
   - Отключите VPN и блокировщики рекламы (например, uBlock).
   - Используйте режим инкогнито в браузере.
   - Убедитесь, что номер телефона зарегистрирован в стране, где вы находитесь.

4. **Создайте файл `.env`** в корне проекта:
   ```env
   API_ID=1234567
   API_HASH=0123456789abcdef0123456789abcdef
   PHONE_NUMBER=+1234567890
   CHANNEL_USERNAME=@YourChannelName
   POST_ID=123
   ```

   Замените значения:
   - `API_ID` и `API_HASH` — из https://my.telegram.org.
   - `PHONE_NUMBER` — ваш номер в международном формате.
   - `CHANNEL_USERNAME` — username канала (например, `@MyChannel`).
   - `POST_ID` — ID поста с комментариями (можно найти в URL поста или через API).

## Шаг 3: Реализация приложения

Создайте файл `src/App.php` с кодом для извлечения комментариев:

```php
<?php

namespace TelegramCommentsApp;

use danog\MadelineProto\API;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Settings\AppInfo;
use Dotenv\Dotenv;

class App
{
    private API $madeline;
    private string $channelUsername;
    private int $postId;

    public function __construct()
    {
        // Загружаем переменные окружения
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        // Настраиваем MadelineProto
        $settings = new Settings();
        $appInfo = new AppInfo();
        $appInfo->setApiId((int)$_ENV['API_ID']);
        $appInfo->setApiHash($_ENV['API_HASH']);
        $settings->setAppInfo($appInfo);

        $this->madeline = new API('session.madeline', $settings);
        $this->channelUsername = $_ENV['CHANNEL_USERNAME'];
        $this->postId = (int)$_ENV['POST_ID'];
    }

    public function run(): void
    {
        try {
            // Запускаем клиент
            $this->madeline->start();

            // Авторизация
            if (!$this->madeline->getSelf()) {
                $this->madeline->phoneLogin($_ENV['PHONE_NUMBER']);
                echo "Введите код авторизации: ";
                $code = trim(fgets(STDIN));
                $this->madeline->completePhoneLogin($code);
            }

            // Извлекаем комментарии
            $this->extractComments();
        } catch (\Exception $e) {
            error_log("Ошибка: " . $e->getMessage(), 3, __DIR__ . '/../error.log');
            echo "Произошла ошибка: " . $e->getMessage() . PHP_EOL;
        }
    }

    private function extractComments(): void
    {
        // Получаем комментарии к посту
        $comments = [];
        $response = $this->madeline->messages->getDiscussionMessage([
            'peer' => $this->channelUsername,
            'msg_id' => $this->postId
        ]);

        // Проверяем, есть ли сообщения в обсуждении
        if (!isset($response['messages']) || empty($response['messages'])) {
            echo "Комментариев не найдено." . PHP_EOL;
            return;
        }

        foreach ($response['messages'] as $message) {
            if (!empty($message['message'])) {
                // Получаем информацию о пользователе
                $user = $this->getUserInfo($message['from_id'] ?? null);
                $comments[] = [
                    'user' => $user['username'] ?? $user['first_name'] ?? 'Unknown',
                    'text' => $message['message'],
                    'date' => date('c', $message['date']),
                    'message_id' => $message['id']
                ];
            }
        }

        // Сохраняем в JSON
        file_put_contents(
            __DIR__ . '/../comments.json',
            json_encode($comments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        echo "Извлечено " . count($comments) . " комментариев. Сохранено в comments.json" . PHP_EOL;
    }

    private function getUserInfo($fromId): array
    {
        if (!$fromId || !isset($fromId['user_id'])) {
            return ['username' => null, 'first_name' => null];
        }

        try {
            $user = $this->madeline->users->getUsers(['id' => [$fromId]]);
            $user = $user[0] ?? [];
            return [
                'username' => $user['username'] ?? null,
                'first_name' => $user['first_name'] ?? null
            ];
        } catch (\Exception $e) {
            return ['username' => null, 'first_name' => null];
        }
    }
}
```

## Шаг 4: Создание точки входа

Создайте файл `index.php` в корне проекта:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use TelegramCommentsApp\App;

try {
    $app = new App();
    $app->run();
} catch (Exception $e) {
    error_log("Ошибка: " . $e->getMessage(), 3, __DIR__ . '/error.log');
    echo "Ошибка: " . $e->getMessage() . PHP_EOL;
}
```

## Шаг 5: Запуск приложения

1. **Запустите скрипт**:
   ```bash
   php index.php
   ```

2. **Авторизация**:
   - При первом запуске скрипт запросит код авторизации, который придет в Telegram или по SMS.
   - Введите код в консоль.
   - Сессия сохранится в файл `session.madeline`, и последующие запуски не потребуют повторной авторизации.

3. **Проверка результата**:
   - После выполнения в корне проекта появится файл `comments.json`. Пример содержимого:
     ```json
     [
         {
             "user": "@UserName",
             "text": "Это тестовый комментарий!",
             "date": "2025-04-25T09:47:00+00:00",
             "message_id": 124
         }
     ]
     ```

## Шаг 6: Дополнительные улучшения

1. **Фильтрация комментариев**:
   - Добавьте фильтры в метод `extractComments` для обработки только текстовых сообщений или исключения медиа.

2. **Сохранение в HTML**:
   - Модифицируйте метод `extractComments` для создания HTML:
     ```php
     $html = "<html><body>\n";
     foreach ($comments as $comment) {
         $html .= "<div><strong>{$comment['user']}</strong>: {$comment['text']}<br><small>{$comment['date']}</small></div>\n";
     }
     $html .= "</body></html>";
     file_put_contents(__DIR__ . '/../comments.html', $html);
     ```

3. **Обработка ошибок**:
   - Логирование уже включено, но можно добавить уведомления (например, через email) при сбоях.

## Шаг 7: Деплой

1. **Разместите приложение на сервере**:
   - Установите PHP 8.2 и зависимости на сервере.
   - Настройте cron для периодического запуска:
     ```bash
     */30 * * * * /usr/bin/php /path/to/index.php >> /path/to/log.txt 2>&1
     ```

2. **Безопасность**:
   - Храните `.env` в безопасном месте, добавьте в `.gitignore`.
   - Ограничьте доступ к файлу `session.madeline`.

## Замечания

- **Ограничения Telegram API**: Ваш аккаунт должен иметь доступ к группе обсуждений канала. Если вы не участник группы или комментарии отключены, API не сможет их извлечь.
- **Производительность**: `MadelineProto` использует асинхронные операции через `amphp`. Для больших объемов комментариев может потребоваться настройка лимитов (например, `limit` в запросе).
- **Альтернативы**: Если `MadelineProto` окажется сложной, можно использовать HTTP-запросы к Telegram API напрямую через `curl`, но это потребует ручной обработки авторизации и MTProto-протокола, что значительно сложнее.