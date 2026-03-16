<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class OtpMailer
{
    /* =========================
       CUSTOMER REGISTRATION OTP
    ========================== */
    public static function sendRegister(string $email, string $otp, int $minutes = 5): void
    {
        self::sendMail(
            $email,
            'Verify Your CleanTech Account',
            self::buildTemplate(
                'Account Verification',
                'Thank you for registering with CleanTech. Please use the verification code below to activate your account.',
                $otp,
                $minutes
            )
        );
    }

    /* =========================
       PROVIDER REGISTRATION OTP
    ========================== */
    public static function sendProviderOtp(string $email, string $otp, int $minutes = 10): void
    {
        self::sendMail(
            $email,
            'Verify Your CleanTech Provider Account',
            self::buildTemplate(
                'Provider Account Verification',
                'Thank you for registering as a service provider with CleanTech. Please verify your provider account using the code below.',
                $otp,
                $minutes
            )
        );
    }

    /* =========================
       CUSTOMER FORGOT PASSWORD OTP
    ========================== */
    public static function sendForgotPassword(string $email, string $otp, int $minutes = 10): void
    {
        self::sendMail(
            $email,
            'CleanTech Password Reset',
            self::buildTemplate(
                'Password Reset Request',
                'We received a request to reset your account password. Please use the code below to proceed.',
                $otp,
                $minutes
            )
        );
    }

    /* =========================
       ✅ PROVIDER RESET PASSWORD OTP (FIX)
    ========================== */
    public static function sendProviderResetOtp(string $email, string $otp, int $minutes = 10): void
    {
        self::sendMail(
            $email,
            'CleanTech Provider Password Reset',
            self::buildTemplate(
                'Provider Password Reset',
                'We received a request to reset your provider account password. Please use the code below to proceed.',
                $otp,
                $minutes
            )
        );
    }

    /* =========================
       SHARED EMAIL TEMPLATE
    ========================== */
    private static function buildTemplate(string $title, string $message, string $otp, int $minutes): string
    {
        return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>{$title}</title>
</head>
<body style='margin:0; padding:0; background-color:#f4f6f8; font-family:Arial, Helvetica, sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0'>
<tr>
<td align='center' style='padding:40px 0;'>
<table width='600' cellpadding='0' cellspacing='0'
       style='background:#ffffff; border-radius:8px; overflow:hidden;
              box-shadow:0 4px 10px rgba(0,0,0,0.08);'>

<tr>
<td style='background:#1677f2; padding:20px; text-align:center; color:#ffffff;'>
<h2 style='margin:0;'>CleanTech</h2>
<p style='margin:5px 0 0; font-size:14px;'>Secure Account Services</p>
</td>
</tr>

<tr>
<td style='padding:30px; color:#333333;'>
<h3 style='margin-top:0;'>{$title}</h3>

<p style='font-size:15px; line-height:1.6;'>
{$message}
</p>

<div style='margin:30px 0; text-align:center;'>
<div style='display:inline-block; background:#f1f3f5; padding:15px 30px;
            font-size:28px; letter-spacing:6px; font-weight:bold;
            border-radius:6px; color:#1677f2;'>
{$otp}
</div>
</div>

<p style='font-size:14px; color:#555555;'>
This verification code will expire in <strong>{$minutes} minutes</strong>.
</p>

<p style='font-size:13px; color:#777777; margin-top:30px;'>
If you did not request this, please ignore this email.
For security reasons, do not share this code with anyone.
</p>
</td>
</tr>

<tr>
<td style='background:#f8f9fa; padding:15px; text-align:center;
           font-size:12px; color:#888888;'>
© " . date('Y') . " CleanTech. All rights reserved.
</td>
</tr>

</table>
</td>
</tr>
</table>
</body>
</html>";
    }

    /* =========================
       SMTP SENDER
    ========================== */
    private static function sendMail(string $email, string $subject, string $body): void
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME');
            $mail->Password   = env('MAIL_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = env('MAIL_PORT', 587);

            $mail->setFrom(
                env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME')),
                env('MAIL_FROM_NAME', 'CleanTech Solutions')
            );

            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
        } catch (Exception $e) {
            logger()->error('OTP Mail Error: ' . $mail->ErrorInfo);
        }
    }
}
