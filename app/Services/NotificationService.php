<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private $webPush;

    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => config('app.url'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ]);
    }

    /**
     * Envia uma notificação push para um usuário
     */
    public function sendPushNotification(User $user, string $title, string $message, array $data = [])
    {
        if (!$user->push_subscription) {
            return false;
        }

        try {
            $subscription = Subscription::create(json_decode($user->push_subscription, true));
            
            $notification = [
                'title' => $title,
                'body' => $message,
                'icon' => '/images/icon-192x192.png',
                'data' => $data
            ];

            $this->webPush->queueNotification(
                $subscription,
                json_encode($notification)
            );

            $results = $this->webPush->flush();
            foreach ($results as $result) {
                if (!$result->isSuccess()) {
                    Log::error('Falha ao enviar notificação push:', [
                        'reason' => $result->getReason(),
                        'user_id' => $user->id
                    ]);
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação push:', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            return false;
        }
    }

    /**
     * Cria uma notificação no banco de dados
     */
    public function createNotification($babyId, string $title, string $message, string $type = 'info')
    {
        return Notification::create([
            'baby_id' => $babyId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => false
        ]);
    }

    /**
     * Envia notificação de alarme
     */
    public function sendAlarmNotification($baby, $alarm)
    {
        try {
            $title = "Hora da Amamentação!";
            $message = "É hora de amamentar o(a) {$baby->name}";
            
            // Cria notificação no banco
            $notification = $this->createNotification($baby->id, $title, $message, 'alarm');
            
            // Envia notificação push
            if ($baby->user->push_subscription) {
                $pushSent = $this->sendPushNotification(
                    $baby->user,
                    $title,
                    $message,
                    [
                        'type' => 'alarm',
                        'alarm_id' => $alarm->id,
                        'notification_id' => $notification->id
                    ]
                );

                if (!$pushSent) {
                    Log::warning('Falha ao enviar notificação push', [
                        'user_id' => $baby->user->id,
                        'alarm_id' => $alarm->id
                    ]);
                }
            } else {
                Log::info('Usuário não tem inscrição push', [
                    'user_id' => $baby->user->id,
                    'alarm_id' => $alarm->id
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação de alarme:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'baby_id' => $baby->id,
                'alarm_id' => $alarm->id
            ]);
            return false;
        }
    }
} 