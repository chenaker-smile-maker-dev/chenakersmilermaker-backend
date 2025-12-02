<?php

namespace App\Actions\Testimonial;

class ListTestimonials
{
    public function handle(int $page = 1, int $perPage = 10)
    {
        $testimonials = \App\Models\Testimonial::where('is_published', true)
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $testimonials->items(),
            'pagination' => [
                'total' => $testimonials->total(),
                'per_page' => $testimonials->perPage(),
                'current_page' => $testimonials->currentPage(),
                'last_page' => $testimonials->lastPage(),
                'from' => $testimonials->firstItem(),
                'to' => $testimonials->lastItem(),
            ],
        ];
    }
}
