<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Testimonial\ListTestimonials;
use App\Actions\Testimonial\ShowTestimonial;
use App\Http\Controllers\Api\BaseController;
use App\Models\Testimonial;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;

#[Group('(content) Testimonials', weight: 3)]
class TestimonialController extends BaseController
{
    /**
     * List all published testimonials with pagination.
     *
     * Returns a paginated list of all published testimonials from patients.
     * Each testimonial includes patient name, rating, review content, and timestamp.
     */
    #[QueryParameter('page', description: 'Page number for pagination.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('per_page', description: 'Number of testimonials per page.', type: 'int', default: 10, example: 15)]
    public function listTestimonials(Request $request, ListTestimonials $listTestimonials)
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', default: 10);
        $data = $listTestimonials->handle($page, $perPage);
        return $this->sendResponse($data);
    }

    /**
     * Get testimonial details.
     *
     * Retrieves detailed information about a specific testimonial including patient name,
     * rating (1-5 stars), full review content, and associated patient information.
     */
    public function showTestimonial(Testimonial $testimonial, ShowTestimonial $showTestimonial)
    {
        try {
            $data = $showTestimonial->handle($testimonial);
            return $this->sendResponse($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 422);
        }
    }
}
