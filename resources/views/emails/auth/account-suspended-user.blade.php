<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Suspension automatique de votre compte') }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; line-height: 1.6; }
        .container { max-width: 580px; margin: 30px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: linear-gradient(135deg, #450a0a 0%, #881337 100%); padding: 28px 24px; text-align: center; }
        .badge { display: inline-block; background-color: rgba(239, 68, 68, 0.25); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 10px; border-radius: 9999px; margin-bottom: 8px; }
        .title { color: #ffffff; font-size: 20px; font-weight: 800; margin: 0; }
        .content { padding: 32px 28px; }
        .greeting { font-size: 16px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 12px; }
        .text { font-size: 14px; color: #475569; margin-bottom: 20px; }
        .details-box { background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 18px; margin: 20px 0; }
        .info-box { background-color: #f0fdf4; border-left: 4px solid #22c55e; border-radius: 0 8px 8px 0; padding: 14px 16px; margin: 20px 0; font-size: 13px; color: #166534; }
        .footer { background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="badge">🔒 {{ __('Sécurité du Compte') }}</div>
            <h1 class="title">{{ __('Votre compte a été suspendu automatiquement') }}</h1>
        </div>

        <div class="content">
            <p class="greeting">{{ __('Bonjour :name,', ['name' => $user->name]) }}</p>
            <p class="text">
                {{ __('Pour des raisons de sécurité, votre compte a été automatiquement suspendu suite à des tentatives répétées de connexion infructueuses avec un mot de passe incorrect.') }}
            </p>

            <div class="details-box">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 6px 0; color: #991b1b; font-weight: 600; font-size: 13px;">{{ __('Compte concerné :') }}</td>
                        <td style="padding: 6px 0; color: #7f1d1d; font-weight: 700; font-size: 13px; text-align: right;">{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #991b1b; font-weight: 600; font-size: 13px;">{{ __('Statut actuel :') }}</td>
                        <td style="padding: 6px 0; color: #dc2626; font-weight: 800; font-size: 13px; text-align: right;">{{ __('Suspendu / Inactif') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #991b1b; font-weight: 600; font-size: 13px;">{{ __('Date et heure :') }}</td>
                        <td style="padding: 6px 0; color: #7f1d1d; font-weight: 700; font-size: 13px; text-align: right;">{{ $date }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #991b1b; font-weight: 600; font-size: 13px;">{{ __('Adresse IP :') }}</td>
                        <td style="padding: 6px 0; color: #7f1d1d; font-weight: 700; font-family: monospace; font-size: 13px; text-align: right;">{{ $ip }}</td>
                    </tr>
                </table>
            </div>

            <div class="info-box">
                <strong>{{ __('Comment réactiver votre compte ?') }}</strong><br>
                {{ __("Pour débloquer et réactiver votre compte, veuillez contacter directement l'administrateur ou la direction de l'établissement. Un administrateur pourra vérifier votre identité et rétablir votre accès.") }}
            </div>
        </div>

        <div class="footer">
            <p style="margin: 0;">{{ config('app.name', 'GUT Center') }} • {{ __('Gestion Scolaire Sécurisée') }}</p>
            <p style="margin: 4px 0 0; font-size: 11px;">{{ __('Notification automatique de sécurité.') }}</p>
        </div>
    </div>
</body>
</html>
