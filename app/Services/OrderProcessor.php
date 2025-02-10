<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class OrderProcessor
{
    /**
     * Process new orders from the API and notify subscribed users.
     */
    public function processNewOrders(): void
    {
        $subscriptions = Subscription::all();

        foreach ($subscriptions as $subscription) {
            $unit_id = $subscription->unit_id;
            $api_key = $subscription->api_key;

            $response = OrderService::fetchNewOrders($unit_id, $api_key);
            if (isset($response['success']) && isset($response['data']['orders'])) {
                foreach ($response['data']['orders'] as $orderData) {
                    $orderId = $orderData['id'];

                    if (!Order::where('unit_id', $unit_id)->where('order_data->id', $orderId)->exists()) {
                        Order::create([
                            'unit_id' => $unit_id,
                            'order_data' => json_encode($orderData),
                            'notified' => false,
                        ]);

                        $subscribedUsers = Subscription::where('unit_id', $unit_id)->get();

                        foreach ($subscribedUsers as $subscribedUser) {
                            $this->sendOrderNotification($subscribedUser->user_id, $orderData);
                        }

                        Order::where('unit_id', $unit_id)->where('order_data->id', $orderId)->update(['notified' => true]);
                    }
                }
            } else {
                Log::error('Invalid API response or no orders found:', ['response' => $response]);
            }
        }
    }

    /**
     * Send an order notification to a user via Telegram bot.
     */
    private function sendOrderNotification($userId, $orderData): void
    {
        $message = "Название заведения: {$orderData['unit']}\n"
            . "Заказ No: {$orderData['number']}\n"
            . "Тип заказа: {$orderData['delivery_method']}\n"
            . "Заказ создан: {$orderData['date']}\n"
            . "Заказ ко времени: {$orderData['order_time_at']}\n"
            . "Телефон: {$orderData['payload']['client']['phone']}\n"
            . "Адрес получения: {$orderData['payload']['address']['address']}\n"
            . "Состав заказа:\n";

        foreach ($orderData['payload']['products'] as $product) {
            $message .= "- {$product['name']} x {$product['count']} - {$product['price']} руб.\n";
        }

        $message .= "Сумма заказа: {$orderData['amount']} руб.\n"
            . "Доставка: {$orderData['delivery_cost']} руб.\n"
            . "ИТОГО: {$orderData['total_amount']} руб.\n"
            . "Способ оплаты: {$orderData['payment']['name']}\n"
            . "Комментарий повару: {$orderData['payload']['comments']['kitchen']}\n"
            . "Комментарий курьеру: {$orderData['payload']['comments']['courier']}";

        Telegram::sendMessage([
            'chat_id' => $userId,
            'text' => $message,
        ]);
    }
}
