<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Event\ListEvents;
use App\Actions\Event\ShowEvent;
use App\Http\Controllers\Api\BaseController;
use App\Models\Event;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;

#[Group('(content) Events', weight: 1)]
class EventController extends BaseController
{
    /**
     * List all events with pagination.
     *
     * Returns a paginated list of all active (non-archived) events in the system.
     * Each event includes title, description, location, and event date.
     */
    #[QueryParameter('page', description: 'Page number for pagination.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('per_page', description: 'Number of events per page.', type: 'int', default: 10, example: 20)]
    public function listEvents(Request $request, ListEvents $listEvents)
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', default: 10);
        $data = $listEvents->handle($page, $perPage);
        return $this->sendResponse($data);
    }

    /**
     * Get event details.
     *
     * Retrieves detailed information about a specific event including title, description,
     * location, event date, and archival status.
     */
    public function showEvent(Event $event, ShowEvent $showEvent)
    {
        try {
            $data = $showEvent->handle($event);
            return $this->sendResponse($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 422);
        }
    }
}
