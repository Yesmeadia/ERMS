<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Admin Account Created</title>
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
            color: #94a3b8;
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

        .button-wrapper {
            text-align: center;
            margin: 28px 0;
        }

        .btn-primary {
            display: inline-block;
            padding: 13px 32px;
            background: #4f46e5;
            color: #ffffff !important;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            border-radius: 10px;
            letter-spacing: 0.01em;
        }

        .link-fallback {
            font-size: 12px;
            color: #94a3b8;
            word-break: break-all;
            margin-top: 8px;
            text-align: center;
        }

        .link-fallback a {
            color: #4f46e5;
            text-decoration: none;
        }

        .info-box {
            background-color: #f8fafc;
            border-left: 3px solid #94a3b8;
            padding: 14px 16px;
            border-radius: 0 10px 10px 0;
            margin-top: 24px;
            margin-bottom: 24px;
        }

        .info-box p {
            margin: 0;
            font-size: 13px;
            color: #64748b;
            line-height: 1.55;
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

        /* ===== Mobile styles ===== */
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

            .button-wrapper {
                margin: 22px 0;
            }

            .btn-primary {
                display: block;
                width: 100%;
                box-sizing: border-box;
                padding: 14px 0;
                font-size: 15px;
            }

            .info-box p {
                font-size: 12.5px;
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
            @if(file_exists(public_path('icon.png')))
                <img src="{{ $message->embed(public_path('icon.png')) }}" alt="Yes Genius">
            @endif
            <div class="banner-text">
                <h1>Account Created</h1>
                <p>Welcome to Portal</p>
            </div>
        </div>

        <div class="card">
            <p>Dear <strong>{{ $school->contact_person }}</strong>,</p>
            <p>We are pleased to inform you that your school admin account for <strong>{{ $school->name }}</strong> has
                been successfully created. You can now access the portal to register students, download hall tickets,
                and manage examination reports.</p>

            <div class="info-box">
                <p><strong>School Information:</strong></p>
                <p>&bull; <strong>School Name:</strong> {{ $school->name }}</p>
                <p>&bull; <strong>School Code:</strong> {{ $school->code }}</p>

                <p style="margin-top: 10px;"><strong>Login Credentials:</strong></p>
                <p>&bull; <strong>Username / Email:</strong> <code>{{ $user->email }}</code></p>
                <p>&bull; <strong>Temporary Password:</strong> <code>{{ $password }}</code></p>
            </div>

            <div class="button-wrapper">
                <a href="{{ route('login') }}" class="btn-primary" target="_blank">Access Login Portal</a>
            </div>

            <p class="link-fallback">If the button above does not work, copy and paste this link into your
                browser:<br><a href="{{ route('login') }}">{{ route('login') }}</a></p>

            <div class="info-box" style="border-left: 3px solid #f59e0b;">
                <p style="color: #d97706;"><strong>Security Notice:</strong> This is a temporary password. You are
                    required to log in and change your password immediately upon your first login under <strong>My
                        Profile > Update Password</strong>.</p>
            </div>

            <p class="signature">Warm regards,<br><strong>Yes Genius Exam Board</strong></p>
        </div>

        <div class="footer">
            <p>This is an automated notification. Please do not reply to this email.</p>
            <p class="legal">&copy; {{ date('Y') }} YES INDIA FOUNDATION &middot; All rights reserved.</p>
        </div>
    </div>
</body>

</html>