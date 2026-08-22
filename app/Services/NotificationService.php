<?php

namespace App\Services;

use App\Models\NotificationSetting;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function dispatch(string $event, array $data): void
    {
        $setting = NotificationSetting::where('event', $event)->first();
        if (!$setting) return;

        $recipients = is_array($setting->recipients) ? $setting->recipients : json_decode($setting->recipients, true) ?? [];

        // Channel 1: In-App Database
        if ($setting->notify_in_app) {
            $this->sendInApp($setting, $recipients, $data);
        }

        // Channel 2: WhatsApp
        if ($setting->notify_whatsapp) {
            $this->sendWhatsApp($setting, $recipients, $data);
        }

        // Channel 3: SMS Gateway
        if ($setting->notify_sms) {
            $this->sendSMS($setting, $recipients, $data);
        }

        // Channel 4: Automated Email Setup
        if ($setting->notify_mail) {
            $this->sendEmail($setting, $recipients, $data);
        }
    }

    private function sendInApp(NotificationSetting $setting, array $recipients, array $data): void
    {
        $rawMessage = $setting->whatsapp_message ?? $setting->sms_message ?? "Event: " . $setting->event;
        $message = $this->replacePlaceholders($rawMessage, $data);

        if (in_array('admin', $recipients) && isset($data['admin_users'])) {
            // We pass an array matching the keys inside SendNotification to keep DB records organized
            $payload = [
                'sender_id'     => auth()->id() ?? null,
                'reminder_date' => date('Y-m-d'),
                'document_name' => $data['reference'] ?? null,
                'message'       => $message
            ];

            Notification::send($data['admin_users'], new SendNotification($payload));
        }
    }

    private function sendWhatsApp(NotificationSetting $setting, array $recipients, array $data): void
    {
        if (empty($setting->whatsapp_message)) return;
        $message = $this->replacePlaceholders($setting->whatsapp_message, $data);

        if (in_array('customer', $recipients) && !empty($data['customer_wa'])) {
            $this->triggerWhatsAppApi($data['customer_wa'], $message);
        }

        if (in_array('supplier', $recipients) && !empty($data['supplier_wa'])) {
            $this->triggerWhatsAppApi($data['supplier_wa'], $message);
        }

        if (in_array('admin', $recipients) && isset($data['admin_users'])) {
            foreach ($data['admin_users'] as $admin) {
                if (!empty($admin->phone)) {
                    $this->triggerWhatsAppApi($admin->phone, $message);
                }
            }
        }
    }

    private function sendSMS(NotificationSetting $setting, array $recipients, array $data): void
    {
        if (empty($setting->sms_message)) return;
        $message = $this->replacePlaceholders($setting->sms_message, $data);

        if (in_array('customer', $recipients) && !empty($data['customer_phone'])) {
            $this->triggerSmsGateway($data['customer_phone'], $message);
        }

        if (in_array('supplier', $recipients) && !empty($data['supplier_phone'])) {
            $this->triggerSmsGateway($data['supplier_phone'], $message);
        }

        if (in_array('admin', $recipients) && isset($data['admin_users'])) {
            foreach ($data['admin_users'] as $admin) {
                if (!empty($admin->phone)) {
                    $this->triggerSmsGateway($admin->phone, $message);
                }
            }
        }
    }

    private function sendEmail(NotificationSetting $setting, array $recipients, array $data): void
    {
        if (empty($setting->mail_message)) return;
        $messageBody = $this->replacePlaceholders($setting->mail_message, $data);
        $subject = ucwords(str_replace('_', ' ', $setting->event)) . " - " . ($data['reference'] ?? 'Update');

        if (in_array('customer', $recipients) && !empty($data['customer_email'])) {
            $this->triggerMailDriver($data['customer_email'], $subject, $messageBody);
        }

        if (in_array('supplier', $recipients) && !empty($data['supplier_email'])) {
            $this->triggerMailDriver($data['supplier_email'], $subject, $messageBody);
        }

        if (in_array('admin', $recipients) && isset($data['admin_users'])) {
            foreach ($data['admin_users'] as $admin) {
                if (!empty($admin->email)) {
                    $this->triggerMailDriver($admin->email, $subject, $messageBody);
                }
            }
        }
    }

    private function triggerWhatsAppApi(string $phone, string $message): void
    {
        try {
            $whatsapp = \App\Models\WhatsappSetting::latest()->first();
            if ($whatsapp) {
                $whatsapp->sendMessage($phone, $message);
            }
        } catch (\Exception $e) {
            Log::error("WhatsApp Framework failure to {$phone}: " . $e->getMessage());
        }
    }

    private function triggerSmsGateway(string $phone, string $message): void
    {
        try {
            if (class_exists('\App\Services\SmsService')) {
                app('\App\Services\SmsService')->send($phone, $message);
            } else {
                Log::warning("SMS configuration toggled on, but core SmsService could not be located.");
            }
        } catch (\Exception $e) {
            Log::error("SMS Gateway execution failure to {$phone}: " . $e->getMessage());
        }
    }

    private function triggerMailDriver(string $email, string $subject, string $body): void
    {
        try {
            Mail::raw($body, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });
        } catch (\Exception $e) {
            Log::error("Mail delivery driver failure to {$email}: " . $e->getMessage());
        }
    }

    private function replacePlaceholders(string $template, array $data): string
    {
        $placeholders = [
            '[customer]'  => $data['customer_name'] ?? '',
            '[reference]' => $data['reference'] ?? '',
            '[amount]'    => $data['amount'] ?? '',
            '[product]'   => $data['product'] ?? '',
            '[qty]'       => $data['qty'] ?? '',
        ];
        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }
}
