<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\CrmUser;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CrmSetupSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('CRM_SUPERADMIN_EMAIL', 'superadmin@zirelly.az');

        if (! CrmUser::query()->where('email', $email)->exists()) {
            $password = env('CRM_SUPERADMIN_PASSWORD') ?: Str::password(16);

            CrmUser::query()->create([
                'name' => 'Superadmin',
                'email' => $email,
                'password' => $password,
                'role' => CrmUser::ROLE_SUPERADMIN,
                'permissions' => CrmUser::SECTIONS,
                'is_active' => true,
            ]);

            $this->command?->warn("CRM superadmin yaradıldı: {$email}");

            if (! env('CRM_SUPERADMIN_PASSWORD')) {
                $this->command?->warn("Müvəqqəti şifrə (dəyişin!): {$password}");
            }
        }

        User::query()
            ->where('role', UserRole::User->value)
            ->whereNotNull('email_verified_at')
            ->each(fn (User $user) => Contact::syncFromUser($user));

        Order::query()
            ->whereNull('contact_id')
            ->whereNotNull('user_id')
            ->with('user')
            ->each(function (Order $order) {
                if ($order->user !== null) {
                    $order->update(['contact_id' => Contact::syncFromUser($order->user)->id]);
                }
            });

        $this->command?->info('Kontaktlar sinxronlaşdırıldı.');
    }
}
