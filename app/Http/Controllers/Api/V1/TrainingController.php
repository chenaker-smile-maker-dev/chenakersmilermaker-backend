<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Training\ListTrainings;
use App\Actions\Training\ShowTraining;
use App\Actions\Training\SubmitTrainingReview;
use App\Http\Controllers\Api\BaseController;
use App\Models\Training;
use Dedoc\Scramble\Attributes\BodyParameter;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;

#[Group('(content) Trainings', weight: 2)]
class TrainingController extends BaseController
{
    /**
     * List all trainings with pagination.
     *
     * Returns a paginated list of trainings with name (multilang), description,
     * price, trainer info, main image, gallery images, average rating and review count.
     */
    #[QueryParameter('page', description: 'Page number for pagination.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('per_page', description: 'Number of trainings per page.', type: 'int', default: 10, example: 20)]
    public function listTrainings(Request $request, ListTrainings $listTrainings)
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 10);
        $data = $listTrainings->handle($page, $perPage);
        return $this->sendResponse($data);
    }

    /**
     * Get training details.
     *
     * Retrieves full details of a specific training including all multilang fields,
     * media, approved reviews, and video URL.
     */
    public function showTraining(Training $training, ShowTraining $showTraining)
    {
        try {
            $data = $showTraining->handle($training);
            return $this->sendResponse($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    /**
     * Submit a review for a training (auth required).
     *
     * Submits a new review for the given training. The review is pending admin approval
     * before it appears publicly. Requires authentication.
     */
    #[BodyParameter('content', description: 'Review text (min 10 characters).', type: 'string', required: true)]
    #[BodyParameter('rating', description: 'Rating from 1 to 5.', type: 'integer', required: true)]
    public function submitReview(Request $request, Training $training, SubmitTrainingReview $action)
    {
        $validated = $request->validate([
            'content' => 'required|string|min:10',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $action->handle($training, $request->user(), $validated);

        return $this->sendResponse([], __('api.review_submitted'), 201);
    }
}
