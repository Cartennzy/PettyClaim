<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FCMService
{
    protected $messaging;

    public function __construct()
    {
        $credentialsPath = storage_path('app/firebase_credentials.json');

        if (file_exists($credentialsPath)) {
            try {
                $factory = (new Factory())->withServiceAccount($credentialsPath);
                $this->messaging = $factory->createMessaging();
            } catch (\Throwable $e) {
                Log::error("FCM Init Error: " . $e->getMessage());
            }
        }
    }

    /**
     * Mengirim Push Notification ke Token FCM pengguna.
     */
    public function sendNotification(?string $fcmToken, string $title, string $body, array $data = []): bool
    {
        if (!$this->messaging || empty($fcmToken)) {
            return false;
        }

        try {
            $notification = Notification::create($title, $body);
            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($message);
            return true;
        } catch (\Throwable $e) {
            Log::error("Gagal Kirim Push Notification: " . $e->getMessage());
            return false;
        }
    }
}