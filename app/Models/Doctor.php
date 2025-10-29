<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravolt\Avatar\Avatar;
use Locale;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Doctor extends Model implements HasMedia
{
    use HasFactory, SoftDeletes;
    use InteractsWithMedia, HasTranslations;


    protected $fillable = ['name', 'specialty', 'diplomas', 'email', 'phone', 'address', 'metadata'];
    public array $translatable = ['name', 'specialty'];
    protected $hidden = [];

    protected function casts(): array
    {
        return [
            // 'name' => 'array',
            // 'specialty' => 'array',
            'diplomas' => 'array',
            'metadata' => 'array',
        ];
    }


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('doctor_photo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg'])
            ->useDisk('public');
    }
    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media && $media->collection_name === 'doctor_photo') {
            $this->addMediaConversion('thumb')
                ->width(width: 100)
                ->height(100);
        }
    }

    public function getImageAttribute()
    {
        return $this->hasMedia('doctor_photo')
            ? $this->getFirstMediaUrl('doctor_photo')
            : null;
    }

    public function getThumbImageAttribute()
    {
        return $this->hasMedia('doctor_photo')
            ? $this->getFirstMediaUrl('doctor_photo', 'thumb')
            : (new Avatar())->create($this->getTranslation('name', app()->getLocale()))
            ->setDimension(100)
            ->setBackground('#25703e')
            ->setForeground('#ffffff')
            ->toBase64();
    }

    public function getDiplomasCountAttribute()
    {
        return is_array($this->diplomas) ? count($this->diplomas) : 0;
    }

    public function getDisplayNameAttribute()
    {
        return 'Dr.' . $this->name;
    }
}
