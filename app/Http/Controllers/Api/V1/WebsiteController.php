<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use App\Settings\WebsiteSettings;
use Dedoc\Scramble\Attributes\Group;

#[Group('(content) Website', weight: 0)]
class WebsiteController extends BaseController
{
    /**
     * Get website settings.
     *
     * Returns all website content settings in one endpoint: hero section,
     * about section, contact information, and social media links.
     */
    public function show(WebsiteSettings $settings)
    {
        return $this->sendResponse([
            'hero' => [
                'title'    => $settings->hero_title,
                'subtitle' => $settings->hero_subtitle,
                'cta_text' => $settings->hero_cta_text,
            ],
            'about' => [
                'title'       => $settings->about_title,
                'description' => $settings->about_description,
            ],
            'contact' => [
                'phone'   => $settings->contact_phone,
                'email'   => $settings->contact_email,
                'address' => $settings->contact_address,
                'map_url' => $settings->contact_map_url,
            ],
            'social' => [
                'facebook'  => $settings->social_facebook,
                'instagram' => $settings->social_instagram,
                'youtube'   => $settings->social_youtube,
            ],
        ]);
    }
}
