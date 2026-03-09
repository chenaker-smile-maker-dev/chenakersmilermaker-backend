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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('(content) Trainings', weight: 2)]
class TrainingController extends BaseController
{
    /**
     * List all trainings with pagination.
     *
     * Returns a paginated list of all available trainings in the system.
     * Each training includes title, description, trainer name, duration, price, images, and reviews summary.
     */
    #[QueryParameter('page', description: 'Page number for pagination.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('per_page', description: 'Number of trainings per page.', type: 'int', default: 10, example: 20)]
    public function listTrainings(Request $request, ListTrainings $listTrainings): JsonResponse
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', default: 10);
        $data = $listTrainings->handle($page, $perPage);
        return $this->sendResponse($data);
    }

    /**
     * Get training details.
     *
     * Retrieves detailed information about a specific training including title, description,
     * trainer information, duration, video URL, images, price, and approved reviews.
     */
    public function showTraining(Training $training, ShowTraining $showTraining): JsonResponse
    {
        try {
            $data = $showTraining->handle($training);
            return $this->sendResponse($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    /**
     * Submit a review for a training.
     *
     * Allows authenticated patients to submit a review for a training.
     * The review will be pending admin approval before being publicly visible.
     */
    #[BodyParameter('content', description: 'Review content.', type: 'string', required: true)]
    #[BodyParameter('rating', description: 'Rating from 1 to 5.', type: 'integer', required: true, example: 5)]
    public function submitReview(Request $request, Training $training, SubmitTrainingReview $action): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|min:10|max:2000',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $patient = $request->user()->patient;
        $review = $action->handle($training, $patient, $validated);

        return $this->sendResponse([
            'id' => $review->id,
            'content' => $review->content,
            'rating' => $review->rating,
            'is_approved' => $review->is_approved,
        ], __('api.review_submitted'), 201);
    }
}

