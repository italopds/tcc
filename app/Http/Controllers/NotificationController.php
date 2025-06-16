<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function subscribe(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $subscription = $request->subscription;
        
        $user->push_subscription = json_encode($subscription);
        $user->save();

        return response()->json(['message' => 'Subscription saved successfully']);
    }

    public function sendNotification(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $subscription = json_decode($user->push_subscription, true);
        
        if (!is_array($subscription) || !isset($subscription['endpoint'])) {
            return response()->json(['error' => 'Invalid subscription format'], 400);
        }

        if (!$subscription) {
            return response()->json(['error' => 'No subscription found'], 404);
        }

        $message = $request->message ?? 'Hora da amamentação!';
        
        try {
            // Configurar o WebPush com suas chaves VAPID
            $auth = [
                'VAPID' => [
                    'subject' => 'mailto:' . config('mail.from.address'),
                    'publicKey' => config('services.vapid.public_key'),
                    'privateKey' => config('services.vapid.private_key'),
                ],
            ];
            
            /** @var WebPush $webPush */
            $webPush = new WebPush($auth);
            
            // Criar a subscription
            /** @var Subscription $sub */
            $sub = Subscription::create($subscription);
            
            // Enviar a notificação
            $webPush->queueNotification(
                $sub,
                json_encode([
                    'title' => 'Hora da Amamentação',
                    'body' => $message,
                    'icon' => '/images/icon-192x192.png',
                    'badge' => '/images/badge-72x72.png',
                    'data' => [
                        'url' => route('dashboard')
                    ]
                ])
            );
            
            // Verificar o resultado
            foreach ($webPush->flush() as $report) {
                if (!$report->isSuccess()) {
                    Log::error('Falha ao enviar notificação', [
                        'reason' => $report->getReason()
                    ]);
                    return response()->json(['error' => 'Failed to send notification: ' . $report->getReason()], 500);
                }
            }
            
            return response()->json(['message' => 'Notification sent successfully']);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error sending notification: ' . $e->getMessage()], 500);
        }
    }

    public function markAsRead(Notification $notification)
    {
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }
} 