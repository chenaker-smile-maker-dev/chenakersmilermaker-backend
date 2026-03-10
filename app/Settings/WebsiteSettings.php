<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WebsiteSettings extends Settings
{
    // Hero
    public string $hero_title;
    public string $hero_subtitle;
    public string $hero_cta_text;

    // About
    public string $about_title;
    public string $about_description;

    // Contact
    public string $contact_phone;
    public string $contact_email;
    public string $contact_address;
    public string $contact_map_url;

    // Social
    public string $social_facebook;
    public string $social_instagram;
    public string $social_youtube;

    public static function group(): string
    {
        return 'website';
    }
}
