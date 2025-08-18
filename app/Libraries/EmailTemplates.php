<?php
namespace App\Libraries;

class EmailTemplates
{
    /**
     * Template email reset password
     *
     * @param string $userEmail
     * @param string $resetLink
     * @return string
     */
    public static function resetPassword(string $userEmail, string $resetLink): string
    {
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Reset Password</title>
        </head>
        <body style='font-family: Montserrat, sans-serif; background-color: #f8fafc; padding: 20px;'>
            <div style='max-width: 600px; margin: auto; background-color: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>
                <h2 style='color: #1e40af; text-align:center;'>Reset Password</h2>
                <p>Halo <strong>{$userEmail}</strong>,</p>
                <p>Kami menerima permintaan reset password akun Anda. Silakan klik tombol di bawah untuk membuat password baru:</p>
                <div style='text-align:center; margin: 20px 0;'>
                    <a href='{$resetLink}' 
                       style='display:inline-block; padding:12px 24px; background-color:#1e40af; color:#fff; border-radius:8px; text-decoration:none; font-weight:bold;'>
                        Reset Password
                    </a>
                </div>
                <p>Jika bukan Anda yang meminta, abaikan email ini.</p>
                <hr style='margin-top:30px; border-color:#e5e7eb;'>
                <p style='font-size:12px; color:#6b7280;'>Vendor Partnership & SEO Performance System</p>
            </div>
        </body>
        </html>
        ";
    }
}
