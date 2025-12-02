<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Training\ListTrainings;
use App\Actions\Training\ShowTraining;
use App\Http\Controllers\Api\BaseController;
use App\Models\Training;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;

#[Group('(content) Trainings', weight: 2)]
class TrainingController extends BaseController
{
    /**
     * List all trainings with pagination.
     *
     * Returns a paginated list of all available trainings in the system.
     * Each training includes title, description, trainer name, duration, and media files.
     */
    #[QueryParameter('page', description: 'Page number for pagination.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('per_page', description: 'Number of trainings per page.', type: 'int', default: 10, example: 20)]
    public function listTrainings(Request $request, ListTrainings $listTrainings)
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
     * trainer information, duration, video URL, and associated documents.
     */
    public function showTraining(Training $training, ShowTraining $showTraining)
    {
        try {
            $data = $showTraining->handle($training);
            return $this->sendResponse($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 422);
        }
    }
}
