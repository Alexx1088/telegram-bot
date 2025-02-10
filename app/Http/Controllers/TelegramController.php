<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\UserState;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function __construct(protected TelegramService $telegramService)
    {}

    /**
     * Handle incoming Telegram updates.
     *
     * @throws TelegramSDKException
     */
    public function handle(Request $request): void
    {
        $update = $this->telegramService->getTelegram()->commandsHandler(true);

        if ($message = $update->getMessage()) {
            $chat_id = $message->getChat()->getId();
            $text = $message->getText();
            Log::info($message);

            switch ($text) {
                case '/start':
                    $this->telegramService->sendMessage(
                        $chat_id,
                        "Привет! Используйте команды:\n/subscribe - подписаться на заведение\n/list - список заведений\n/unsubscribe - удалить заведение"
                    );
                    break;

                case '/subscribe':
                    UserState::updateOrCreate(
                        ['user_id' => $chat_id],
                        ['command' => 'subscribe']
                    );
                    $this->telegramService->sendMessage(
                        $chat_id,
                        "Введите данные в формате: unit_id api_key"
                    );
                    break;

                case '/list':
                    $subscriptions = Subscription::where('user_id', $chat_id)->get();
                    if ($subscriptions->isEmpty()) {
                        $this->telegramService->sendMessage(
                            $chat_id,
                            "У вас нет подписок на заведения"
                        );
                    } else {
                        $message = "Ваши подписки:\n";
                        foreach ($subscriptions as $subscription) {
                            $message .= "- Заведение ID: {$subscription->unit_id}\n";
                        }
                        $this->telegramService->sendMessage($chat_id, $message);
                    }
                    break;

                case '/unsubscribe':
                    UserState::updateOrCreate(
                        ['user_id' => $chat_id],
                        ['command' => 'unsubscribe']
                    );
                    $this->telegramService->sendMessage(
                        $chat_id,
                        "Введите ID заведения, от которого хотите отписаться."
                    );
                    break;

                default:
                    if (strpos($text, '/') !== 0) {
                        $state = UserState::where('user_id', $chat_id)->first();
                        $command = $state?->command;

                        if ($command === 'subscribe') {
                            $this->telegramService->processSubscription($chat_id, $text);
                        } elseif ($command === 'unsubscribe') {
                            $this->telegramService->processUnsubscribe($chat_id, $text);
                        }

                        UserState::where('user_id', $chat_id)->delete();
                    }
            }
        }
    }
}
