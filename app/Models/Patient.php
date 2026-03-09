<?php

namespace App\Models;

use App\Enums\Patient\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravolt\Avatar\Avatar;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Patient extends Authenticatable implements HasMedia
{
    use HasApiTokens;
    use HasFactory, Notifiable, SoftDeletes;
    use InteractsWithMedia;

    protected $fillable = [
        'email',
        'phone',
        'first_name',
        'last_name',
        'age',
        'gender',
        'password',
        'email_verified_at',
        'email_verification_token',
        'email_verification_sent_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'email_verified_at' => 'datetime',
            'email_verification_sent_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_photo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg'])
            ->useDisk('public');

        $this->addMediaCollection('documents')
            ->useDisk('local');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media && $media->collection_name === 'profile_photo') {
            $this->addMediaConversion('thumb')
                ->width(width: 100)
                ->height(100);
        }
    }

    public function getImageAttribute()
    {
        return $this->hasMedia('profile_photo') ? $this->getFirstMediaUrl('profile_photo') : null;
    }

    public function getThumbImageAttribute()
    {
        return $this->hasMedia('profile_photo')
            ? $this->getFirstMediaUrl('profile_photo', 'thumb')
            : (new Avatar)->create($this->full_name)
            ->setDimension(100)
            ->setBackground('#25703e')
            ->setForeground('#ffffff')
            ->toBase64();
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function urgentBookings(): HasMany
    {
        return $this->hasMany(UrgentBooking::class);
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => now(),
            'email_verification_token' => null,
        ])->save();
    }

    public function generateVerificationToken(): string
    {
        $token = \Illuminate\Support\Str::random(64);
        $this->update([
            'email_verification_token' => $token,
            'email_verification_sent_at' => now(),
        ]);
        return $token;
    }
}
