<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Alerte Sécurité Administrateur') }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; line-height: 1.6; }
        .container { max-width: 580px; margin: 30px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 28px 24px; text-align: center; }
        .badge { display: inline-block; background-color: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.4); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 10px; border-radius: 9999px; margin-bottom: 8px; }
        .title { color: #ffffff; font-size: 20px; font-weight: 800; margin: 0; }
        .content { padding: 32px 28px; }
        .greeting { font-size: 16px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 12px; }
        .text { font-size: 14px; color: #475569; margin-bottom: 20px; }
        .details-box { background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 18px; margin: 20px 0; }
        .detail-row { display: flex; justify-content: space-between; font-size: 13px; padding: 6px 0; border-bottom: 1px dashed #fcd34d; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #92400e; font-weight: 600; }
        .detail-value { color: #78350f; font-weight: 700; font-family: monospace; }
        .warning-box { background-color: #fef2f2; border-left: 4px solid #ef4444; border-radius: 0 8px 8px 0; padding: 12px 16px; margin: 20px 0; font-size: 13px; color: #991b1b; }
        .btn-wrapper { text-align: center; margin: 28px 0 10px; }
        .btn { display: inline-block; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 10px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 10px rgba(217, 119, 6, 0.25); }
        .footer { background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="badge">⚠️ {{ __('Alerte de Sécurité') }}</div>
            <h1 class="title">{{ __('5 tentatives de connexion échouées') }}</h1>
        </div>

        <div class="content">
            <p class="greeting">{{ __('Bonjour :name,', ['name' => $admin->name]) }}</p>
            <p class="text">
                {{ __('Nous vous informons que cinq (5) tentatives consécutives de connexion avec un mot de passe incorrect ont été détectées sur votre compte administrateur.') }}
            </p>

            <div class="details-box">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 6px 0; color: #92400e; font-weight: 600; font-size: 13px;">{{ __('Compte concerné :') }}</td>
                        <td style="padding: 6px 0; color: #78350f; font-weight: 700; font-size: 13px; text-align: right;">{{ $admin->email }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #92400e; font-weight: 600; font-size: 13px;">{{ __('Rôle :') }}</td>
                        <td style="padding: 6px 0; color: #78350f; font-weight: 700; font-size: 13px; text-align: right;">{{ __('Administrateur') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #92400e; font-weight: 600; font-size: 13px;">{{ __('Adresse IP :') }}</td>
                        <td style="padding: 6px 0; color: #78350f; font-weight: 700; font-family: monospace; font-size: 13px; text-align: right;">{{ $ip }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #92400e; font-weight: 600; font-size: 13px;">{{ __('Date et heure :') }}</td>
                        <td style="padding: 6px 0; color: #78350f; font-weight: 700; font-size: 13px; text-align: right;">{{ $date }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #92400e; font-weight: 600; font-size: 13px;">{{ __('Mesure automatique :') }}</td>
                        <td style="padding: 6px 0; color: #b45309; font-weight: 800; font-size: 13px; text-align: right;">{{ __('Blocage temporaire (60 secondes)') }}</td>
                    </tr>
                </table>
            </div>

            <div class="warning-box">
                <strong>{{ __('Action recommandée :') }}</strong><br>
                {{ __("Si vous n'êtes pas à l'origine de ces tentatives de connexion, nous vous conseillons vivement de réinitialiser votre mot de passe dès maintenant pour sécuriser l'accès à l'application.") }}
            </div>

            @if (Route::has('password.request'))
                <div class="btn-wrapper">
                    <a href="{{ route('password.request') }}" class="btn">
                        {{ __('Réinitialiser mon mot de passe') }}
                    </a>
                </div>
            @endif
        </div>

        <div class="footer">
            <p style="margin: 0;">{{ config('app.name', 'GUT Center') }} • {{ __('Protection de la sécurité des données') }}</p>
            <p style="margin: 4px 0 0; font-size: 11px;">{{ __('Ceci est un message de sécurité automatique, merci de ne pas y répondre directement.') }}</p>
        </div>
    </div>
</body>
</html>
