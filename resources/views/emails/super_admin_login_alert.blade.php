<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login Alert</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        .wrapper {
            max-width: 600px;
            margin: 40px auto;
        }

        .banner {
            margin: 0 20px;
            padding: 20px 28px;
            background-color: #0f172a;
            border-radius: 16px 16px 0 0;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .banner img {
            height: 38px;
            width: auto;
            object-fit: contain;
            flex-shrink: 0;
        }

        .banner-text h1 {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.2px;
        }

        .banner-text p {
            margin: 2px 0 0;
            font-size: 11px;
            font-weight: 600;
            color: #f59e0b;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .card {
            background: #ffffff;
            border-radius: 0 0 16px 16px;
            margin: 0 20px;
            padding: 36px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
            border-top: none;
        }

        .card p {
            font-size: 15px;
            color: #475569;
            line-height: 1.65;
            margin: 0 0 20px;
        }

        .info-box {
            background-color: #f8fafc;
            border-left: 3px solid #f59e0b;
            padding: 18px 20px;
            border-radius: 0 10px 10px 0;
            margin-top: 24px;
            margin-bottom: 24px;
        }

        .info-box p {
            margin: 0;
            font-size: 13px;
            color: #475569;
            line-height: 1.6;
        }

        .info-box p strong {
            color: #0f172a;
        }

        .signature {
            margin-top: 36px;
            margin-bottom: 0;
        }

        .footer {
            padding: 28px 36px;
            text-align: center;
        }

        .footer p {
            font-size: 12px;
            color: #94a3b8;
            margin: 0;
            line-height: 1.6;
        }

        .footer p.legal {
            margin-top: 6px;
            font-size: 11px;
            color: #cbd5e1;
        }

        @media only screen and (max-width: 600px) {
            .wrapper {
                width: 100% !important;
                margin: 0 auto !important;
            }

            .banner {
                margin: 0 12px;
                padding: 16px 20px;
                border-radius: 14px 14px 0 0;
                gap: 12px;
            }

            .banner img {
                height: 30px;
            }

            .banner-text h1 {
                font-size: 15px;
            }

            .banner-text p {
                font-size: 10px;
            }

            .card {
                margin: 0 12px;
                padding: 22px 18px;
                border-radius: 0 0 14px 14px;
            }

            .card p {
                font-size: 14px;
                line-height: 1.6;
            }

            .info-box {
                padding: 14px;
            }

            .footer {
                padding: 22px 16px;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="banner">
            @if(file_exists(public_path('images/icon.png')))
                <img src="{{ $message->embed(public_path('images/icon.png')) }}" alt="Yes Genius">
            @endif
            <div class="banner-text">
                <h1>Security Notification</h1>
                <p>Super Admin Login Detected</p>
            </div>
        </div>

        <div class="card">
            <p>Dear <strong>{{ $user->name }}</strong>,</p>
            <p>We detected a new successful login to your Super Admin account. Below are the details of the login session:</p>

            <div class="info-box">
                <p style="margin-bottom: 8px;"><strong>Session Information:</strong></p>
                <p style="margin-bottom: 4px;">&bull; <strong>Account:</strong> {{ $user->email }}</p>
                <p style="margin-bottom: 4px;">&bull; <strong>Time:</strong> {{ $time }}</p>
                <p style="margin-bottom: 4px;">&bull; <strong>IP Address:</strong> <code>{{ $ip }}</code></p>
                <p>&bull; <strong>Device / Browser:</strong> <code>{{ $userAgent }}</code></p>
            </div>

            <p>If this login was authorized by you, no further action is required. If you do not recognize this activity, please change your password immediately or contact the system administrator to secure your account.</p>

            <p class="signature">Warm regards,<br><strong>Yes Genius Exam Board</strong></p>
        </div>

        <div class="footer">
            <p>This is an automated security alert. Please do not reply to this email.</p>
            <p class="legal">&copy; {{ date('Y') }} YES INDIA FOUNDATION &middot; All rights reserved.</p>
        </div>
    </div>
</body>

</html>
