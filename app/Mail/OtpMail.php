<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $name;

    /**
     * Create a new message instance.
     *
     * @param string|int $otp
     * @param string $name
     */
    public function __construct($otp, $name = 'User')
    {
        $this->otp = $otp;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your OTP Verification Code')
                    ->html("
                        <div style='font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                            <h2 style='color: #1e293b; text-align: center;'>OTP Verification</h2>
                            <p>Hi <strong>" . htmlspecialchars($this->name) . "</strong>,</p>
                            <p>Your OTP verification code is:</p>
                            <div style='text-align: center; margin: 25px 0;'>
                                <span style='display: inline-block; font-size: 28px; font-weight: bold; letter-spacing: 5px; color: #16a34a; background: #f0fdf4; padding: 10px 25px; border-radius: 6px; border: 1px dashed #16a34a;'>" . htmlspecialchars($this->otp) . "</span>
                            </div>
                            <p style='color: #64748b; font-size: 13px;'>This OTP will expire in 15 minutes. Please do not share this code with anyone.</p>
                            <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
                            <p style='color: #94a3b8; font-size: 12px; text-align: center;'>Thank you for choosing our service.</p>
                        </div>
                    ");
    }
}
