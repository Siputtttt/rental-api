<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Password Reset Code</title>
</head> 

<body style="font-family: Arial, sans-serif; background-color: #f5f7fa; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background-color: #ffffff; margin-top: 30px; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background-color: #4a90e2; padding: 20px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0;">{{ config('app.name') }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px;">
                            <h2 style="color: #333;">Hello {{ $user->first_name }},</h2>
                            <p style="color: #555;">You recently requested to reset your password.</p>
                            <p style="color: #555;">Use the code below to reset it:</p>

                            <div style="text-align: center; margin: 30px 0;">
                                <span
                                    style="display: inline-block; background-color: #f0f4f8; color: #4a90e2; font-size: 32px; font-weight: bold; padding: 20px 40px; border-radius: 8px; border: 2px dashed #4a90e2;">
                                    {{ $otp }}
                                </span>
                            </div>

                            <p style="color: #555;">This code will expire in <strong>10 minutes</strong>.</p>
                            <p style="color: #555;">If you did not request a password reset, please ignore this email.
                            </p>

                            <br>
                            <p style="color: #555;">Best regards,<br><strong>{{ config('app.name') }} Team</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="background-color: #f0f4f8; padding: 15px; text-align: center; color: #999; font-size: 12px;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>