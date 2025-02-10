<?php

namespace App\Services;

use App\Models\Subscription;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class TelegramService
{
    public function __construct(protected Api $telegram)
    {
    }

    /**
     * Handle subscription logic.
     *
     * @throws TelegramSDKException
     */

    /**
     * Get the Telegram API instance.
     */
    public function getTelegram(): Api
    {
        return $this->telegram;
    }

    /**
     * @throws TelegramSDKException
     */
    public function processSubscription(int $chat_id, string $text): void
    {
        $parts = explode(' ', $text);
        if (count($parts) !== 2) {
            $this->sendMessage($chat_id, "Неверный формат данных. Введите unit_id и api_key через пробел.");
            return;
        }

        [$unitId, $apiKey] = $parts;

        if (!is_numeric($unitId)) {
            $this->sendMessage($chat_id, "Неверный формат unit_id. Unit ID должен быть числом.");
            return;
        }

        if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $apiKey)) {
    $this->sendMessage($chat_id, "Неверный формат API Key. API Key должен быть строкой из 32 буквенно-цифровых символов.");
    return;
}

        $existingSubscription = Subscription::where('user_id', $chat_id)
            ->where('unit_id', $unitId)
            ->first();

        if ($existingSubscription) {
            $this->sendMessage($chat_id, "Вы уже подписаны на это заведение с ID: $unitId");
            return;
        }

        \App\Models\User::updateOrCreate(
            ['id' => $chat_id],
            [
                'name' => "Telegram User $chat_id",
                'email' => "user{$chat_id}_" . uniqid() . "@example.com",
                'password' => bcrypt('placeholder_password'),
                'email_verified_at' => now(),
            ]
        );

        Subscription::updateOrCreate(
            ['user_id' => $chat_id, 'unit_id' => $unitId],
            ['api_key' => $apiKey]
        );

        $this->sendMessage($chat_id, "Вы успешно подписались на заведение с ID: $unitId");
    }

    /**
     * Handle unsubscription logic.
     *
     * @throws TelegramSDKException
     */
    public function processUnsubscribe(int $chat_id, string $text): void
    {
        $unitId = trim($text);
        if (!is_numeric($unitId)) {
            $this->sendMessage($chat_id, "Неверный формат данных. Введите числовой ID заведения.");
            return;
        }

        $deleted = Subscription::where('user_id', $chat_id)
            ->where('unit_id', $unitId)
            ->delete();

        if ($deleted) {
            $this->sendMessage($chat_id, "Вы успешно отписались от заведения с ID: $unitId");
        } else {
            $this->sendMessage($chat_id, "Заведение с ID: $unitId не найдено в ваших подписках.");
        }
    }

    /**
     * Send a message via Telegram.
     *
     * @throws TelegramSDKException
     */
    public function sendMessage(int $chat_id, string $message): void
    {
        $this->telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => $message,
        ]);
    }
}
