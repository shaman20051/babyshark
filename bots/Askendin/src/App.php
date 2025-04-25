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
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

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
            $this->madeline->start();

            if (!$this->madeline->getSelf()) {
                $this->madeline->phoneLogin($_ENV['PHONE_NUMBER']);
                echo "Введите код авторизации: ";
                $code = trim(fgets(STDIN));
                $this->madeline->completePhoneLogin($code);
            }

            $this->extractComments();
        } catch (\Exception $e) {
            error_log("Ошибка: " . $e->getMessage(), 3, __DIR__ . '/../error.log');
            echo "Произошла ошибка: " . $e->getMessage() . PHP_EOL;
        }
    }

    private function extractComments(): void
    {
        $comments = [];

        // Получаем информацию о посте и связанной группе обсуждений
        $discussion = $this->madeline->messages->getDiscussionMessage([
            'peer' => $this->channelUsername,
            'msg_id' => $this->postId
        ]);

        if (!isset($discussion['chats'][0]['id'])) {
            echo "Группа обсуждений не найдена или комментарии отключены." . PHP_EOL;
            return;
        }

        // ID группы обсуждений
        $groupId = '-' . $discussion['chats'][0]['id']; // Формат peer для групп
        $discussionMessageId = $discussion['messages'][0]['id'] ?? null;

        if (!$discussionMessageId) {
            echo "Не удалось определить ID сообщения для комментариев." . PHP_EOL;
            return;
        }

        // Получаем сообщения из группы обсуждений
        $messages = $this->madeline->messages->getHistory([
            'peer' => $groupId,
            'limit' => 100, // Максимальное количество сообщений для извлечения
            'offset_id' => 0,
            'max_id' => 0,
            'min_id' => 0,
            'add_offset' => 0,
            'hash' => 0
        ]);

        // Фильтруем сообщения, которые являются ответами на пост
        foreach ($messages['messages'] as $message) {
            if (isset($message['reply_to']) && $message['reply_to']['reply_to_msg_id'] == $discussionMessageId && !empty($message['message'])) {
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
        if (empty($comments)) {
            echo "Комментариев к посту не найдено." . PHP_EOL;
        } else {
            file_put_contents(
                __DIR__ . '/../comments.json',
                json_encode($comments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
            echo "Извлечено " . count($comments) . " комментариев. Сохранено в comments.json" . PHP_EOL;
        }
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