<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Your Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        /* Basic styling consistent with Laravel's default mail templates */
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            background-color: #f8fafc;
            -webkit-text-size-adjust: none;
            font-family: Avenir, Helvetica, sans-serif;
            color: #74787E;
        }
        a {
            color: #3869D4;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        /* Container for the email body */
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8fafc;
            padding: 25px 0;
            text-align: center;
        }
        /* Inner container for the "card" */
        .content {
            width: 100%;
            max-width: 570px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #E8E5EF;
            border-radius: 2px;
            overflow: hidden;
        }
        .header {
            padding: 25px 0;
            text-align: center;
        }
        .body {
            padding: 35px;
            text-align: left;
        }
        .body h1 {
            margin-top: 0;
            color: #3d4852;
            font-size: 19px;
            font-weight: bold;
        }
        .body p {
            margin-top: 0;
            line-height: 1.5em;
            color: #3d4852;
        }
        .action {
            width: 100%;
            margin: 30px auto;
            text-align: center;
        }
        .button {
            display: inline-block;
            padding: 10px 18px;
            background-color: #3490dc;
            color: #ffffff !important;
            border-radius: 4px;
            font-weight: bold;
            text-decoration: none;
        }
        .sub {
            font-size: 12px;
            color: #999999;
            line-height: 1.5em;
            word-break: break-all;
        }
        .footer {
            width: 100%;
            margin: 0 auto;
            text-align: center;
            padding: 35px;
            font-size: 12px;
            color: #AEAEAE;
        }
        .footer .address {
            margin-top: 10px;
            color: #555555;
            line-height: 1.4;
        }
        /* Logo image (optional) */
        .logo {
            height: 64px;
        }
        @media only screen and (max-width: 600px) {
            .body {
                padding: 20px !important;
            }
        }
    </style>
</head>
<body>
    <table class="wrapper" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                
                <!-- Header / Logo (optional) -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="header">
                            {{-- If you have a logo, place it here: --}}
                            {{-- <img src="{{ asset('images/your-logo.png') }}" alt="Logo" class="logo"> --}}
                        </td>
                    </tr>
                </table>
                
                <!-- Email Body -->
                <table class="content" align="center" width="570" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="body">
                            <h1>Hello {{ $user->name }},</h1>
                            <p>
                                An administrator has created an account for you with a 
                                <strong>temporary password</strong>. To secure your account and finalize your setup, 
                                please click the button below to set your new password:
                            </p>

                            <!-- Action Button -->
                            <table class="action" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $resetUrl }}" class="button">Reset Password</a>
                                    </td>
                                </tr>
                            </table>

                            <!-- No-reply & Additional Info -->
                            <p>
                                Please note that this email is being sent from a no-reply email address, 
                                and replies to this message will not be received or monitored. 
                                If you have any questions or concerns, please contact your system administrator.
                            </p>

                            <!-- Fallback URL -->
                            <p class="sub">
                                If you're having trouble clicking the "Reset Password" button, copy and paste the URL 
                                below into your web browser:
                                <br>
                                <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
                            </p>

                            <p>Regards,<br>{{ config('app.name') }}</p>
                        </td>
                    </tr>
                </table>

                <!-- Footer -->
                <table width="570" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="footer" align="center">
                            <p>
                                © {{ date('Y') }} Pamantasan ng Cabuyao. All rights reserved.
                            </p>
                            <div class="address">
                                <strong>Pamantasan ng Cabuyao</strong><br>
                                Katapatan Mutual Homes, Brgy. Banay-banay<br>
                                City of Cabuyao, Laguna 4025
                            </div>
                            <p style="margin-top:10px;">
                                This is a computer-generated email. Please do not reply to this message. 
                                If you have inquiries or concerns, kindly message the 
                                <a href="https://facebook.com/pnc.misd" target="_blank">PnC-MISD Facebook Page</a>.
                            </p>
                        </td>
                    </tr>
                </table>
                <!-- End Footer -->
                
            </td>
        </tr>
    </table>
</body>
</html>
