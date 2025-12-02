<?php

namespace App\Actions\Testimonial;

use App\Models\Testimonial;

class ShowTestimonial
{
    public function handle(Testimonial $testimonial)
    {
        return $testimonial;
    }
}
