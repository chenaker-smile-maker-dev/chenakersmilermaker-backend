<?php

namespace App\Actions\Testimonial;

use App\Models\Testimonial;

class ShowTestimonial
{
    public function handle(Testimonial $testimonial)
    {
        $testimonial->load('patient');
        return \App\Http\Resources\TestimonialResource::make($testimonial)->resolve();
    }
}
