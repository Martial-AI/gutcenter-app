<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

    #[Fillable(['name', 'first_name', 'last_name', 'nickname', 'professional_number', 'teaching_subjects', 'email', 'phone', 'photo_path', 'cin', 'birth_date', 'birth_place', 'address', 'emergency_contact', 'contract_type', 'employment_type', 'contract_start_date', 'contract_end_date', 'leave_start_date', 'leave_end_date', 'monthly_salary_amount', 'salary_payment_day', 'salary_payment_method', 'salary_payment_phone', 'salary_payment_transaction_hint', 'salary_payment_bank_details', 'password', 'is_active', 'failed_login_lockouts', 'activity_notifications_read_at', 'activity_notifications_read_ids'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'failed_login_lockouts' => 'integer',
            'birth_date' => 'date',
            'contract_start_date' => 'date',
            'contract_end_date' => 'date',
            'leave_start_date' => 'date',
            'leave_end_date' => 'date',
            'activity_notifications_read_at' => 'datetime',
            'activity_notifications_read_ids' => 'array',
            'monthly_salary_amount' => 'decimal:2',
        ];
    }

    /** Whether this staff member follows a fixed daily work schedule. */
    public function isPermanent(): bool
    {
        return $this->employment_type === 'permanent';
    }

    /** Whether this staff member (e.g. teacher) works per course schedule only. */
    public function isNonPermanent(): bool
    {
        return $this->employment_type === 'non_permanent';
    }

    public function localizedRoleLabel(?string $role = null): string
    {
        $role ??= $this->getRoleNames()->first();

        return match ($role) {
            'Admin' => __('Admin'),
            'Prof' => __('Professor'),
            'Secrétaire' => __('Secretary'),
            'Trésorier' => __('Treasurer'),
            default => $role ? __($role) : __('Account'),
        };
    }

    public function localizedFunctionLabel(): string
    {
        $role = $this->getRoleNames()->first();
        if (! $role) {
            return $this->nickname ?: $this->name;
        }

        if ($role === 'Admin') {
            return __('Admin');
        }

        $firstName = $this->first_name ?: explode(' ', $this->name)[0];

        return trim($this->localizedRoleLabel($role).' '.$firstName);
    }

    public function teacherAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id');
    }
}
