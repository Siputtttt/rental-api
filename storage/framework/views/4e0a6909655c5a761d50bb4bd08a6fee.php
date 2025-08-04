<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Account Activation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f6f6f6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333333;
        }

        p {
            color: #555555;
            line-height: 1.6;
        }

        .button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background-color: #007bff;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #999999;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Hello, <?php echo e($user->first_name); ?></h2>
        <p>Thank you for registering an account with us.</p>
        <p>To complete your registration, please click the button below to activate your account:</p>
        <p>
            <a class="button" href="<?php echo e(env('FRONTEND_URL') . '/activate/' . $user->id); ?>">
                Activate My Account
            </a>
        </p>
        <p>If you did not register for an account, please disregard this email.</p>
        <div class="footer">
            <p>Best regards,<br>
                The <?php echo e(config('app.name')); ?> Team</p>
        </div>
    </div>
</body>

</html>
<?php /**PATH C:\laragon\www\SximoV7\Sximo-7-BE\resources\views/emails/ActivationEmail.blade.php ENDPATH**/ ?>