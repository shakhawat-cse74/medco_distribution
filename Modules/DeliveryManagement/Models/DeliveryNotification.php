<?php

namespace Modules\DeliveryManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeliveryNotification extends Model
{
    protected $fillable = [
        'delivery_man_id', 'type', 'title', 'body', 'related_type', 'related_id',
        'is_read', 'read_at', 'channel', 'payload', 'sms_sent', 'sms_sent_at',
        'email_sent', 'email_sent_at', 'push_sent', 'push_sent_at', 'retry_count'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'sms_sent' => 'boolean',
        'email_sent' => 'boolean',
        'push_sent' => 'boolean',
        'payload' => 'array',
        'read_at' => 'datetime',
        'sms_sent_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'push_sent_at' => 'datetime',
    ];

    public function deliveryMan()
    {
        return $this->belongsTo(DeliveryMan::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function sendSms($message, $phone)
    {
        // Implement SMS sending logic here
        // This would integrate with an SMS service like Twilio, AWS SNS, etc.
        try {
            // Example: 
            // $smsService = new SmsService();
            // $result = $smsService->send($phone, $message);
            
            $this->sms_sent = true;
            $this->sms_sent_at = now();
            $this->save();
            
            return true;
        } catch (\Exception $e) {
            $this->retry_count = ($this->retry_count ?? 0) + 1;
            $this->save();
            return false;
        }
    }

    public function sendEmail($to, $subject, $body, $template = null)
    {
        // Implement email sending logic here
        // This would integrate with PHPMAILER or other email service
        try {
            // Example:
            // Mail::to($to)->send(new NotificationMail($subject, $body, $template));
            
            $this->email_sent = true;
            $this->email_sent_at = now();
            $this->save();
            
            return true;
        } catch (\Exception $e) {
            $this->retry_count = ($this->retry_count ?? 0) + 1;
            $this->save();
            return false;
        }
    }

    public function sendPush($tokens, $title, $body, $data = null)
    {
        // Implement push notification logic here
        // This would integrate with Firebase, OneSignal, etc.
        try {
            // Example:
            // $pushService = new PushNotificationService();
            // $pushService->send($tokens, $title, $body, $data);
            
            $this->push_sent = true;
            $this->push_sent_at = now();
            $this->save();
            
            return true;
        } catch (\Exception $e) {
            $this->retry_count = ($this->retry_count ?? 0) + 1;
            $this->save();
            return false;
        }
    }

    public function generateSmsMessage($event, $data = [])
    {
        $messages = [
            'order_placed' => 'Your order ' . $data['order_id'] . ' has been placed successfully. We will notify you when it is assigned to a delivery man.',
            'out_for_delivery' => 'Your order ' . $data['order_id'] . ' is out for delivery. Delivery man ' . $data['delivery_man_name'] . ' is on the way.',
            'delivered' => 'Your order ' . $data['order_id'] . ' has been delivered successfully. Please rate your delivery experience.',
            'payment_received' => 'Payment received for order ' . $data['order_id'] . ' of amount ' . $data['amount'] . '. Thank you!',
        ];

        return $messages[$event] ?? 'Notification: ' . $event;
    }

    public function generatePushMessage($event, $data = [])
    {
        $messages = [
            'new_order' => 'New order ' . $data['order_id'] . ' assigned to you.',
            'route_change' => 'Route updated for order ' . $data['order_id'] . '.',
            'payment_reminder' => 'Payment reminder for order ' . $data['order_id'] . ' of amount ' . $data['amount'] . '.',
        ];

        return $messages[$event] ?? 'New notification';
    }
}
