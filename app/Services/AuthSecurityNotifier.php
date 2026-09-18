<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AuthSecurityNotifier
{
    /**
     * Send email alert and in-app notification when an account (admin, teacher, secretary, etc.)
     * reaches 5 failed login attempts.
     */
    public static function notifyFailedAttempts(User $user, string $ip, ?string $browser = null): void
    {
        $date = now()->format('d/m/Y à H:i:s');
        $roleLabel = $user->localizedRoleLabel();

        // 1. In-app admin notification
        try {
            SensitiveActivityNotifier::send(
                __('Alerte Sécurité Connexion'),
                __('5 tentatives de connexion échouées ont été détectées sur le compte :role :name (:email). IP : :ip.', [
                    'role' => $roleLabel,
                    'name' => $user->name,
                    'email' => $user->email,
                    'ip' => $ip,
                ])
            );
        } catch (Throwable $e) {
            Log::warning("Could not send in-app notification for failed attempts: " . $e->getMessage());
        }

        // 2. Activity history logging
        try {
            activity('connexion')
                ->performedOn($user)
                ->log("5 tentatives de connexion échouées sur le compte {$roleLabel} {$user->name}");
        } catch (Throwable $e) {
            Log::warning("Could not log activity for failed login: " . $e->getMessage());
        }

        // 3. Email alert to the account owner
        try {
            Mail::send('emails.auth.admin-failed-attempts', [
                'admin' => $user,
                'user' => $user,
                'roleLabel' => $roleLabel,
                'ip' => $ip,
                'date' => $date,
                'browser' => $browser,
                'resetUrl' => 'https://gutcenter.taila5e2fd.ts.net/forgot-password',
            ], function ($message) use ($user): void {
                $message->to($user->email)
                    ->subject(__('[GUT Center] Alerte de sécurité : 5 tentatives de connexion échouées'));
            });
        } catch (Throwable $e) {
            Log::warning("Could not send security email to {$user->email}: " . $e->getMessage());
        }
    }

    /**
     * Backward-compatible alias for admin accounts.
     */
    public static function notifyAdminFailedAttempts(User $admin, string $ip, ?string $browser = null): void
    {
        self::notifyFailedAttempts($admin, $ip, $browser);
    }

    /**
     * Send email to user, email to all admins, and in-app admin notification
     * when a non-admin account is automatically suspended.
     */
    public static function notifyAccountSuspended(User $suspendedUser, string $ip, ?string $browser = null): void
    {
        $date = now()->format('d/m/Y à H:i:s');

        $roleLabel = $suspendedUser->localizedRoleLabel();

        // 1. In-app notification to all admins
        try {
            SensitiveActivityNotifier::send(
                __('Compte suspendu automatiquement'),
                __("Le compte :role de :name (:email) a été suspendu automatiquement suite à plusieurs tentatives échouées. IP : :ip.", [
                    'role' => $roleLabel,
                    'name' => $suspendedUser->name,
                    'email' => $suspendedUser->email,
                    'ip' => $ip,
                ])
            );
        } catch (Throwable $e) {
            Log::warning("Could not send in-app notification for suspended account: " . $e->getMessage());
        }

        // 2. Activity history logging
        try {
            activity('connexion')
                ->performedOn($suspendedUser)
                ->log("Compte {$roleLabel} {$suspendedUser->name} suspendu automatiquement");
        } catch (Throwable $e) {
            Log::warning("Could not log activity for suspended account: " . $e->getMessage());
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
