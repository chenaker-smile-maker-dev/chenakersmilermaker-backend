<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

#[Group('(patient) Notifications', weight: 6)]
class PatientNotificationController extends BaseController
{
    /**
     * List patient's notifications.
     *
     * Returns paginated list of the authenticated patient's notifications.
     * Title and body are returned with all three translations (en/ar/fr).
     */
    #[QueryParameter('page', description: 'Page number', type: 'int', default: 1)]
    #[QueryParameter('per_page', description: 'Items per page', type: 'int', default: 15)]
    #[QueryParameter('unread_only', description: 'Only show unread notifications', type: 'boolean', default: false)]
    public function index(Request $request)
    {
        $patient  = $request->user();
        $perPage  = $request->integer('per_page', 15);
        $unreadOnly = $request->boolean('unread_only', false);

        $query = $patient->notifications();

        if ($unreadOnly) {
            $query->whereNull('read_at');
        }

        $paginator = $query->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $request->integer('page', 1));

        return $this->sendResponse([
            'data' => $paginator->map(fn (DatabaseNotification $n) => $this->formatNotification($n)),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Get unread notification count.
     *
     * Returns the count of unread notifications for the authenticated patient.
     */
    public function unreadCount(Request $request)
    {
        $count = $request->user()->unreadNotifications()->count();
        return $this->sendResponse(['unread_count' => $count]);
    }

    /**
     * Mark notification as read.
     *
     * Marks a specific notification as read.
     */
    public function markAsRead(DatabaseNotification $notification, Request $request)
    {
        if (
            $notification->notifiable_type !== get_class($request->user()) ||
            $notification->notifiable_id   !== $request->user()->getKey()
        ) {
            return $this->sendError('api.unauthorized', [], 403);
        }

        $notification->markAsRead();
        return $this->sendResponse([], 'api.notification_marked_read');
    }

    /**
     * Mark all notifications as read.
     *
     * Marks all notifications for the authenticated patient as read.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        return $this->sendResponse([], 'api.all_notifications_marked_read');
    }

    /**
     * Delete a notification.
     *
     * Deletes a specific notification for the authenticated patient.
     */
    public function destroy(DatabaseNotification $notification, Request $request)
    {
        if (
            $notification->notifiable_type !== get_class($request->user()) ||
            $notification->notifiable_id   !== $request->user()->getKey()
        ) {
            return $this->sendError('api.unauthorized', [], 403);
        }

        $notification->delete();
        return $this->sendResponse([], 'api.notification_deleted');
    }

    // ──────────────────────────────────────────────────────────────────────────

    private function formatNotification(DatabaseNotification $n): array
    {
        $data = $n->data;
        return [
            'id'         => $n->id,
            'type'       => $data['type'] ?? null,
            'title'      => $data['title'] ?? [],      // ['en', 'ar', 'fr']
            'body'       => $data['body']  ?? [],      // ['en', 'ar', 'fr']
            'data'       => $data['data']  ?? null,
            'action_url' => $data['action_url'] ?? null,
            'is_read'    => !is_null($n->read_at),
            'created_at' => $n->created_at?->toIso8601String(),
        ];
    }
}
