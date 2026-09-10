<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AuthSecurityNotifier
{
    /**
     * Send email alert and in-app notification when an administrator account
     * reaches 5 failed login attempts.
     */
    public static function notifyAdminFailedAttempts(User $admin, string $ip, ?string $browser = null): void
    {
        $date = now()->format('d/m/Y à H:i:s');

        // 1. In-app admin notification
        try {
            SensitiveActivityNotifier::send(
                'Alerte Sécurité Administrateur',
                "5 tentatives de connexion échouées ont été détectées sur le compte administrateur {$admin->name} ({$admin->email}). IP : {$ip}."
            );
        } catch (Throwable $e) {
            Log::warning("Could not send in-app notification for admin failed attempts: " . $e->getMessage());
        }

        // 2. Email alert to the administrator
        try {
            Mail::send('emails.auth.admin-failed-attempts', [
                'admin' => $admin,
                'ip' => $ip,
                'date' => $date,
                'browser' => $browser,
                'resetUrl' => 'https://gutcenter.taila5e2fd.ts.net/forgot-password',
            ], function ($message) use ($admin): void {
                $message->to($admin->email)
                    ->subject(__('[GUT Center] Alerte de sécurité : 5 tentatives de connexion échouées'));
            });
        } catch (Throwable $e) {
            Log::warning("Could not send security email to admin {$admin->email}: " . $e->getMessage());
        }
    }

    /**
     * Send email to user, email to all admins, and in-app admin notification
     * when a non-admin account is automatically suspended.
     */
    public static function notifyAccountSuspended(User $suspendedUser, string $ip, ?string $browser = null): void
    {
        $date = now()->format('d/m/Y à H:i:s');

        // 1. In-app notification to all admins
        try {
            SensitiveActivityNotifier::send(
                'Compte suspendu automatiquement',
                "Le compte de {$suspendedUser->name} ({$suspendedUser->email}) a été suspendu automatiquement suite à plusieurs tentatives échouées. IP : {$ip}."
            );
        } catch (Throwable $e) {
            Log::warning("Could not send in-app notification for suspended account: " . $e->getMessage());
        }

        // 2. Email to the suspended user
        try {
            Mail::send('emails.auth.account-suspended-user', [
                'user' => $suspendedUser,
                'ip' => $ip,
                'date' => $date,
                'browser' => $browser,
            ], function ($message) use ($suspendedUser): void {
                $message->to($suspendedUser->email)
                    ->subject(__('[GUT Center] Suspension automatique de votre compte'));
            });
        } catch (Throwable $e) {
            Log::warning("Could not send suspension email to user {$suspendedUser->email}: " . $e->getMessage());
        }

        // 3. Email to all active administrators
        try {
            $admins = User::role('Admin')->where('is_active', true)->get();
            foreach ($admins as $admin) {
                Mail::send('emails.auth.account-suspended-admin', [
                    'admin' => $admin,
                    'user' => $suspendedUser,
                    'ip' => $ip,
                    'date' => $date,
                    'browser' => $browser,
                    'adminUsersUrl' => 'https://gutcenter.taila5e2fd.ts.net/admin/users',
                ], function ($message) use ($admin, $suspendedUser): void {
                    $message->to($admin->email)
                        ->subject(__('[GUT Center] Alerte Sécurité : Compte :name suspendu automatiquement', ['name' => $suspendedUser->name]));
                });
            }
        } catch (Throwable $e) {
            Log::warning("Could not send suspension notice to admins: " . $e->getMessage());
        }
    }
}
