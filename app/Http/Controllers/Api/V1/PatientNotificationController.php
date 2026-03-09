<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use App\Models\PatientNotification;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;

#[Group('(patient) Notifications', weight: 6)]
class PatientNotificationController extends BaseController
{
    /**
     * List patient's notifications.
     *
     * Returns paginated list of the authenticated patient's notifications.
     */
    #[QueryParameter('page', description: 'Page number', type: 'int', default: 1)]
    #[QueryParameter('per_page', description: 'Items per page', type: 'int', default: 15)]
    #[QueryParameter('unread_only', description: 'Only show unread notifications', type: 'boolean', default: false)]
    public function index(Request $request)
    {
        $patient = $request->user();
        $perPage = $request->integer('per_page', 15);
        $unreadOnly = $request->boolean('unread_only', false);

        $query = PatientNotification::where('patient_id', $patient->id);

        if ($unreadOnly) {
            $query->unread();
        }

        $notifications = $query->orderByDesc('created_at')->paginate($perPage, ['*'], 'page', $request->integer('page', 1));

        $locale = app()->getLocale();

        return $this->sendResponse([
            'data' => $notifications->map(function (PatientNotification $n) use ($locale) {
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title[$locale] ?? $n->title['en'] ?? '',
                    'body' => $n->body[$locale] ?? $n->body['en'] ?? '',
                    'data' => $n->data,
                    'action_url' => $n->action_url,
                    'is_read' => !is_null($n->read_at),
                    'created_at' => $n->created_at->toIso8601String(),
                ];
            }),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'last_page' => $notifications->lastPage(),
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
        $count = PatientNotification::where('patient_id', $request->user()->id)->unread()->count();
        return $this->sendResponse(['unread_count' => $count]);
    }

    /**
     * Mark notification as read.
     *
     * Marks a specific notification as read.
     */
    public function markAsRead(PatientNotification $notification, Request $request)
    {
        if ($notification->patient_id !== $request->user()->id) {
            return $this->sendError(__('api.unauthorized'), [], 403);
        }

        $notification->markAsRead();
        return $this->sendResponse([], __('api.notification_marked_read'));
    }

    /**
     * Mark all notifications as read.
     *
     * Marks all notifications for the authenticated patient as read.
     */
    public function markAllAsRead(Request $request)
    {
        PatientNotification::where('patient_id', $request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return $this->sendResponse([], __('api.all_notifications_marked_read'));
    }

    /**
     * Delete a notification.
     *
     * Deletes a specific notification for the authenticated patient.
     */
    public function destroy(PatientNotification $notification, Request $request)
    {
        if ($notification->patient_id !== $request->user()->id) {
            return $this->sendError(__('api.unauthorized'), [], 403);
        }

        $notification->delete();
        return $this->sendResponse([], __('api.notification_deleted'));
    }
}
