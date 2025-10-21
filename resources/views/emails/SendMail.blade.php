<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #e5e7eb;
            color: #111827;
            line-height: 1.6;
            display: flex;
            justify-content: flex-start;
            align-items: flex-start;
        }

        @media (prefers-color-scheme: dark) {
            body {
                background-color: #111827;
                color: #e5e7eb;
            }
        }

        .email-container {
            background-color: #f9fafb;
            border-radius: 12px;
            padding: 32px 24px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 0;
        }

        h1 {
            font-size: 22px;
            margin-bottom: 12px;
        }

        p {
            font-size: 16px;
            margin: 12px 0;
        }

        .forgot-password {
            display: inline-block;
            background-color: #005580;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .forgot-password:hover {
            cursor: pointer;
            background-color: #003354;
        }

    </style>
</head>
<body>
    <div class="email-container">
        <h1>Password Reset Request</h1>
        <p>You requested to reset your password. Click the button below to proceed:</p>

        <a href="{{ env('FRONTEND_URL') }}/reset-password/{{ $token }}" class="forgot-password" style="margin: 0; color: white">Reset Password</a>

        <p>This link will expire in <strong>5 minutes</strong>.</p>
        <p>If you didn’t request this, you can safely ignore this email.</p>
    </div>
</body>
</html>
