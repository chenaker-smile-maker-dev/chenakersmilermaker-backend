<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Event\ListEvents;
use App\Actions\Event\ShowEvent;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\EventResource;
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
     * Returns a paginated list of events. Filter by type: archive, happening, future.
     * Each event includes name (multilang), description, date, time, location,
     * speakers, about_event, what_to_expect, gallery pictures, and status.
     */
    #[QueryParameter('page', description: 'Page number for pagination.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('per_page', description: 'Number of events per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('type', description: 'Filter by event type: archive, happening, future. Omit for all.', type: 'string', example: 'future')]
    public function listEvents(Request $request, ListEvents $listEvents)
    {
        $paginator = $listEvents->handle(
            $request->integer('page', 1),
            $request->integer('per_page', 10),
            $request->query('type'),
        );

        return $this->sendResponse([
            'data'       => EventResource::collection($paginator),
            'pagination' => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ]);
    }

    /**
     * Get event details.
     *
     * Retrieves full details of a specific event including all multilang fields,
     * gallery pictures, and current status.
     */
    public function showEvent(Event $event, ShowEvent $showEvent)
    {
        return $this->sendResponse(EventResource::make($showEvent->handle($event)));
    }
}
