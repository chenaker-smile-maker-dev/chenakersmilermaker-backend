<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Patient\Booking\ListServices;
use App\Actions\Patient\Booking\ShowService;
use App\Http\Controllers\Api\BaseController;
use App\Models\Service;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;

#[Group('(content) Services', weight: 2)]
class ServiceController extends BaseController
{
    /**
     * List all services with pagination.
     *
     * Returns a paginated list of all active medical services available in the system.
     * Each service includes pricing, duration, and availability status.
     */
    #[QueryParameter('page', description: 'Page number for pagination.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('per_page', description: 'Number of services per page.', type: 'int', default: 10, example: 15)]
    public function listServices(Request $request, ListServices $listServices)
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 10);
        $data = $listServices->handle($page, $perPage);
        return $this->sendResponse($data);
    }

    /**
     * Get service details.
     *
     * Retrieves detailed information about a specific service including description,
     * pricing, duration, and list of doctors who provide this service.
     */
    public function showService(Service $service, ShowService $showService)
    {
        try {
            $data = $showService->handle($service);
            return $this->sendResponse($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 422);
        }
    }
}
