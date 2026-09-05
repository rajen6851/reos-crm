<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to REOS CRM</title>
    <style>
        body {
            font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #FFFFFF;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid #E2E8F0;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }
        .header {
            background: #0F172A;
            padding: 32px;
            text-align: center;
        }
        .header h1 {
            color: #FFFFFF;
            font-size: 22px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .header p {
            color: #10B981;
            font-size: 12px;
            font-weight: 700;
            margin-top: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .content {
            padding: 32px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 800;
            color: #0F172A;
            margin-bottom: 12px;
        }
        .intro {
            font-size: 14px;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .credentials-card {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .cred-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px dashed #E2E8F0;
        }
        .cred-item:last-child {
            border-bottom: none;
        }
        .cred-label {
            font-size: 12px;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .cred-value {
            font-size: 14px;
            font-weight: 700;
            color: #0F172A;
            font-family: 'Courier New', Courier, monospace;
        }
        .password-badge {
            background: #D1FAE5;
            color: #047857;
            padding: 4px 10px;
            border-radius: 8px;
            font-weight: 800;
        }
        .cta-container {
            text-align: center;
            margin: 32px 0 16px 0;
        }
        .cta-button {
            display: inline-block;
            background: #059669;
            color: #FFFFFF !important;
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.2);
        }
        .security-notice {
            background: #FFFBEB;
            border: 1px solid #FDE68A;
            border-radius: 12px;
            padding: 14px;
            font-size: 12px;
            color: #92400E;
            line-height: 1.5;
            margin-top: 24px;
        }
        .footer {
            background: #F8FAFC;
            padding: 20px 32px;
            text-align: center;
            border-top: 1px solid #E2E8F0;
            font-size: 12px;
            color: #94A3B8;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>REOS CRM</h1>
            <p>Real Estate Operating System</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">Welcome aboard, {{ $user->name }}!</div>
            <p class="intro">
                Your staff account has been created successfully for <strong>{{ $user->company->name ?? 'REOS CRM' }}</strong>. 
                You can now log in to access your CRM dashboard and manage your real estate operations.
            </p>

            <!-- Account Credentials -->
            <div class="credentials-card">
                <div class="cred-item">
                    <span class="cred-label">Login URL</span>
                    <span class="cred-value"><a href="{{ $loginUrl }}" style="color: #059669; text-decoration: none;">{{ $loginUrl }}</a></span>
                </div>
                <div class="cred-item">
                    <span class="cred-label">Email Address</span>
                    <span class="cred-value">{{ $user->email }}</span>
                </div>
                <div class="cred-item">
                    <span class="cred-label">Temporary Password</span>
                    <span class="cred-value password-badge">{{ $rawPassword }}</span>
                </div>
                <div class="cred-item">
                    <span class="cred-label">Assigned Role</span>
                    <span class="cred-value">{{ $user->role->name ?? 'Staff Member' }}</span>
                </div>
            </div>

            <!-- Login CTA -->
            <div class="cta-container">
                <a href="{{ $loginUrl }}" class="cta-button">Login to REOS CRM &rarr;</a>
            </div>

            <!-- Security Notice -->
            <div class="security-notice">
                <strong>Security Tip:</strong> For your security, please log in with your temporary password and immediately update your password under your Profile settings.
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name', 'REOS CRM') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
