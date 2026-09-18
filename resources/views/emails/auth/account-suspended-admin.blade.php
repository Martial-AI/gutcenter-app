<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Alerte Administrateur : Compte utilisateur suspendu') }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; line-height: 1.6; }
        .container { max-width: 580px; margin: 30px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); padding: 28px 24px; text-align: center; }
        .badge { display: inline-block; background-color: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.4); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 10px; border-radius: 9999px; margin-bottom: 8px; }
        .title { color: #ffffff; font-size: 20px; font-weight: 800; margin: 0; }
        .content { padding: 32px 28px; }
        .greeting { font-size: 16px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 12px; }
        .text { font-size: 14px; color: #475569; margin-bottom: 20px; }
        .details-box { background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 18px; margin: 20px 0; }
        .btn-wrapper { text-align: center; margin: 28px 0 10px; }
        .btn { display: inline-block; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 10px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25); }
        .footer { background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="badge">🛡️ {{ __('Sécurité Administrateur') }}</div>
            <h1 class="title">{{ __('Compte utilisateur suspendu automatiquement') }}</h1>
        </div>

        <div class="content">
            <p class="greeting">{{ __('Bonjour Administrateur,') }}</p>
            <p class="text">
                {{ __("Le système de sécurité anti-brute force a automatiquement suspendu le compte d'un utilisateur suite à l'atteinte du seuil maximal de tentatives infructueuses (10 tentatives / 2 blocages).") }}
            </p>

            <div class="details-box">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 6px 0; color: #475569; font-weight: 600; font-size: 13px;">{{ __('Utilisateur :') }}</td>
                        <td style="padding: 6px 0; color: #0f172a; font-weight: 700; font-size: 13px; text-align: right;">{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #475569; font-weight: 600; font-size: 13px;">{{ __('Email :') }}</td>
                        <td style="padding: 6px 0; color: #0f172a; font-weight: 700; font-size: 13px; text-align: right;">{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #475569; font-weight: 600; font-size: 13px;">{{ __('Poste / Rôle :') }}</td>
                        <td style="padding: 6px 0; color: #0f172a; font-weight: 700; font-size: 13px; text-align: right;">{{ $user->localizedRoleLabel() }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #475569; font-weight: 600; font-size: 13px;">{{ __('Nouveau statut :') }}</td>
                        <td style="padding: 6px 0; color: #dc2626; font-weight: 800; font-size: 13px; text-align: right;">{{ __('Suspendu / Inactif') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #475569; font-weight: 600; font-size: 13px;">{{ __('Adresse IP source :') }}</td>
                        <td style="padding: 6px 0; color: #0f172a; font-weight: 700; font-family: monospace; font-size: 13px; text-align: right;">{{ $ip }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #475569; font-weight: 600; font-size: 13px;">{{ __('Date et heure :') }}</td>
                        <td style="padding: 6px 0; color: #0f172a; font-weight: 700; font-size: 13px; text-align: right;">{{ $date }}</td>
                    </tr>
                </table>
            </div>

            <p class="text">
                {{ __("Toutes les sessions actives de cet utilisateur ont été révoquées. Vous pouvez consulter ce profil et réactiver le compte à tout moment depuis le module de gestion des utilisateurs.") }}
            </p>

            <div class="btn-wrapper">
                <a href="{{ $adminUsersUrl ?? 'https://gutcenter.taila5e2fd.ts.net/admin/users' }}" class="btn">
                    {{ __('Gérer les utilisateurs dans l’Admin') }}
                </a>
            </div>
        </div>

        <div class="footer">
            <p style="margin: 0;">{{ config('app.name', 'GUT Center') }} • {{ __('Centre de Sécurité') }}</p>
            <p style="margin: 4px 0 0; font-size: 11px;">{{ __('Notification automatique transmise aux administrateurs.') }}</p>
        </div>
    </div>
</body>
</html>
