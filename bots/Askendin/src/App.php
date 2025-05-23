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

            // Отладка: Проверяем статус аккаунта
            $self = $this->madeline->getSelf();
            echo "Информация об аккаунте: " . print_r($self, true) . "\n";

            $this->extractComments();
        } catch (\Exception $e) {
            error_log("Ошибка: " . $e->getMessage(), 3, __DIR__ . '/../error.log');
            echo "Произошла ошибка: " . $e->getMessage() . PHP_EOL;
        }
    }

    private function extractComments(): void
    {
        $comments = [];

        // Шаг 1: Регистрируем канал
        try {
            $channelInfo = $this->madeline->getInfo($this->channelUsername);
            echo "Результат getInfo для канала: " . print_r($channelInfo, true) . "\n";
        } catch (\Exception $e) {
            echo "Ошибка при регистрации канала: " . $e->getMessage() . "\n";
            return;
        }

        // Шаг 2: Получаем информацию о посте и группе обсуждений
        try {
            $discussion = $this->madeline->messages->getDiscussionMessage([
                'peer' => $this->channelUsername,
                'msg_id' => $this->postId
            ]);

            // Проверяем, есть ли группа обсуждений
            if (!isset($discussion['chats'][0]['id'])) {
                echo "Группа обсуждений не найдена или комментарии отключены для поста ID {$this->postId}." . PHP_EOL;
                return;
            }

            // Отладка: Выводим полную структуру $discussion['chats']
            echo "Структура discussion['chats']: " . print_r($discussion['chats'], true) . "\n";

            // ID группы обсуждений
            $groupId = $discussion['chats'][0]['id'];
            // ID сообщения в группе, связанного с постом
            $discussionMessageId = $discussion['messages'][0]['id'] ?? null;
            // Access hash группы (для приватной группы)
            $accessHash = $discussion['chats'][0]['access_hash'] ?? null;

            if (!$discussionMessageId) {
                echo "Не удалось определить ID сообщения в группе обсуждений." . PHP_EOL;
                return;
            }

            echo "Группа обсуждений: -{$groupId}, ID сообщения поста в группе: {$discussionMessageId}\n";
            echo "Информация о группе: " . print_r($discussion['chats'][0], true) . "\n";

            // Проверяем, покинул ли аккаунт группу
            if (isset($discussion['chats'][0]['left']) && $discussion['chats'][0]['left']) {
                echo "Ошибка: Аккаунт покинул группу обсуждений. Пожалуйста, присоединитесь к группе.\n";
                return;
            }

            // Шаг 3: Получаем access_hash, если отсутствует
            if (!$accessHash) {
                try {
                    $groupPeer = ['_' => 'inputPeerChannel', 'channel_id' => (int)str_replace('-100', '', "-{$groupId}")];
                    $groupInfo = $this->madeline->getInfo($groupPeer);
                    echo "Результат getInfo для группы: " . print_r($groupInfo, true) . "\n";
                    $accessHash = $groupInfo['Chat']['access_hash'] ?? null;
                    if (!$accessHash) {
                        echo "Не удалось получить access_hash через getInfo." . PHP_EOL;
                        return;
                    }
                } catch (\Exception $e) {
                    echo "Ошибка при получении access_hash для группы: " . $e->getMessage() . "\n";
                    return;
                }
            }

            // Шаг 4: Формируем peer для группы
            $peer = [
                '_' => 'inputPeerChannel',
                'channel_id' => (int)str_replace('-100', '', "-{$groupId}"),
                'access_hash' => $accessHash
            ];

            // Шаг 5: Получаем историю сообщений из группы обсуждений
            try {
                $messages = $this->madeline->messages->getHistory([
                    'peer' => $peer,
                    'limit' => 200,
                    'offset_id' => 0,
                    'max_id' => 0,
                    'min_id' => 0,
                    'add_offset' => 0,
                    'hash' => 0
                ]);

                // Шаг 6: Фильтруем сообщения, которые являются комментариями
                foreach ($messages['messages'] as $message) {
                    echo "Сообщение ID {$message['id']}, reply_to: " . print_r($message['reply_to'] ?? null, true) . "\n";
                    if ($message['id'] >= $discussionMessageId && !empty($message['message'])) {
                        $user = $this->getUserInfo($message['from_id'] ?? null);
                        $comments[] = [
                            'user' => $user['username'] ?? $user['first_name'] ?? 'Unknown',
                            'text' => $message['message'],
                            'date' => date('c', $message['date']),
                            'message_id' => $message['id']
                        ];
                    }
                }
            } catch (\Exception $e) {
                echo "Ошибка при извлечении комментариев: " . $e->getMessage() . PHP_EOL;
                error_log("Ошибка при извлечении комментариев: " . $e->getMessage(), 3, __DIR__ . '/../error.log');
                return;
            }

            // Шаг 7: Сохраняем комментарии в JSON
            if (empty($comments)) {
                echo "Комментариев к посту ID {$this->postId} не найдено." . PHP_EOL;
            } else {
                file_put_contents(
                    __DIR__ . '/../comments.json',
                    json_encode($comments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );
                echo "Извлечено " . count($comments) . " комментариев. Сохранено в comments.json" . PHP_EOL;
            }
        } catch (\Exception $e) {
            echo "Ошибка при извлечении комментариев: " . $e->getMessage() . PHP_EOL;
            error_log("Ошибка при извлечении комментариев: " . $e->getMessage(), 3, __DIR__ . '/../error.log');
            return;
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