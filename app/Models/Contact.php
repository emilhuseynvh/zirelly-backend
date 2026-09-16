<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;

    public const CHANNELS = ['website', 'instagram', 'whatsapp', 'phone', 'other'];

    protected $fillable = [
        'user_id',
        'name',
        'surname',
        'phone',
        'email',
        'birth_date',
        'address',
        'address_city',
        'address_district',
        'address_street',
        'address_building',
        'address_apartment',
        'address_note',
        'channel',
        'created_via',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ContactNote::class)->latest('id');
    }

    public static function syncFromUser(User $user): self
    {
        $contact = static::query()
            ->where('user_id', $user->id)
            ->orWhere(function ($q) use ($user) {
                $q->whereNull('user_id')->where(function ($q) use ($user) {
                    if (filled($user->phone)) {
                        $q->orWhere('phone', $user->phone);
                    }
                    if (filled($user->email)) {
                        $q->orWhere('email', $user->email);
                    }
                });
            })
            ->first();

        $data = [
            'user_id' => $user->id,
            'name' => $user->name,
            'surname' => $user->surname,
            'phone' => $user->phone,
            'email' => $user->email,
            'birth_date' => $user->birth_date,
            'address' => $user->address,
            'address_city' => $user->address_city ?? null,
            'address_district' => $user->address_district ?? null,
            'address_street' => $user->address_street ?? null,
            'address_building' => $user->address_building ?? null,
            'address_apartment' => $user->address_apartment ?? null,
            'address_note' => $user->address_note ?? null,
        ];

        if ($contact === null) {
            return static::query()->create([
                ...$data,
                'channel' => 'website',
                'created_via' => 'site',
            ]);
        }

        $contact->fill([
            ...$data,
            'phone' => $contact->phone ?? $user->phone,
            'email' => $contact->email ?? $user->email,
            'birth_date' => $contact->birth_date ?? $user->birth_date,
            'address' => $contact->address ?? $user->address,
        ])->save();

        return $contact;
    }
}
