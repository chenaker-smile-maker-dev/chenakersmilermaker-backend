<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Event\ListEvents;
use App\Actions\Event\ShowEvent;
use App\Http\Controllers\Api\BaseController;
use App\Models\Event;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('(content) Events', weight: 1)]
class EventController extends BaseController
{
    /**
     * List all events with pagination.
     *
     * Returns a paginated list of events. Filter by type: archive, happening, or future.
     * Each event includes name, date, time, location, speakers, about, what_to_expect, pictures, and status.
     */
    #[QueryParameter('page', description: 'Page number for pagination.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('per_page', description: 'Number of events per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('type', description: 'Filter events by status: archive, happening, future. Omit for all.', type: 'string', example: 'future')]
    public function listEvents(Request $request, ListEvents $listEvents): JsonResponse
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', default: 10);
        $type = $request->query('type');
        $data = $listEvents->handle($page, $perPage, $type);
        return $this->sendResponse($data);
    }

    /**
     * Get event details.
     *
     * Retrieves detailed information about a specific event including name, description,
     * date, time, location, speakers, about_event, what_to_expect, pictures, and status.
     */
    public function showEvent(Event $event, ShowEvent $showEvent): JsonResponse
    {
        try {
            $data = $showEvent->handle($event);
            return $this->sendResponse($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }
}

