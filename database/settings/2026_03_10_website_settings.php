<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Hero
        $this->migrator->add('website.hero_title', 'Welcome to Our Clinic');
        $this->migrator->add('website.hero_subtitle', 'Professional medical care for you and your family.');
        $this->migrator->add('website.hero_cta_text', 'Book an Appointment');

        // About
        $this->migrator->add('website.about_title', 'About Us');
        $this->migrator->add('website.about_description', 'We are dedicated to providing the highest quality healthcare services.');

        // Contact
        $this->migrator->add('website.contact_phone', '+213 000 000 000');
        $this->migrator->add('website.contact_email', 'contact@clinic.com');
        $this->migrator->add('website.contact_address', '123 Main Street, Algiers, Algeria');
        $this->migrator->add('website.contact_map_url', '');

        // Social
        $this->migrator->add('website.social_facebook', '');
        $this->migrator->add('website.social_instagram', '');
        $this->migrator->add('website.social_youtube', '');
    }
};
