<?php

namespace App\Domains\Api\Common\Controllers;

use App\Http\Controllers\APIController;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class NotificationController extends APIController
{

    public function index(Request $request)
    {
        try{
            $authUser = JWTAuth::user();
            $type = $request->get('type'); // Optional: filter by type like 'message', 'review'
            $notifications = $authUser->notifications()
            ->when($type, fn($q) => $q->where('data->type', $type))
            ->latest()
            ->paginate(15);
            $notifications->getCollection()->transform(function ($notification) {
                $locale = app()->getLocale() ?? 'en';
                return [
                    'id'         => $notification->id,
                    'title'      => $notification->data['title'][$locale] ?? $notification->data['title']['en'],
                    'message'    => $notification->data['message'][$locale] ?? $notification->data['message']['en'],
                    'type'       => $notification->data['type'][$locale] ?? $notification->data['type'],
                    'read_at'    => $notification->read_at,
                    'created_at' => $notification->created_at->toDateTimeString(),
                ];
            });                
    
            return $this->apiSuccess(['notifications' => $notifications]);
        }
        catch(\Throwable $th) {
            DB::rollBack();
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function markAllAsRead()
    {
        try{
            $authUser = JWTAuth::user();
            $unread = $authUser->unreadNotifications;

            if ($unread->isEmpty()) {
                return $this->apiSuccess(trans('messages.notifications.all_notifications_already_read')); // "All notifications already read."
            }

            $unread->markAsRead();

            return $this->apiSuccess(trans('messages.notifications.notification_all_read'));
        }
        catch (\Throwable $th) {
            DB::rollBack();
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function markAsRead($id)
    {
        try{
            $authUser = JWTAuth::user();
            $notification = $authUser->notifications()->findOrFail($id);

            if ($notification->read_at === null) {
                $notification->markAsRead();
                return $this->apiSuccess(trans('messages.notifications.notification_read')); //  "Notification marked as read."
            } else {
                return $this->apiSuccess(trans('messages.notifications.notification_already_read')); // "Notification already read."
            }
        }
        catch (\Throwable $th) {
            DB::rollBack();
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function unreadList(Request $request){
        try{
            $authUser = JWTAuth::user();
            $type = $request->get('type'); // Optional: filter by type like 'message', 'review'
            $notifications = $authUser->unreadNotifications()
            ->when($type, fn($q) => $q->where('data->type', $type))
            ->latest()
            ->paginate(15);
            $notifications->getCollection()->transform(function ($notification) {
                $locale = app()->getLocale() ?? 'en';
                return [
                    'id'         => $notification->id,
                    'title'      => $notification->data['title'][$locale] ?? $notification->data['title']['en'],
                    'message'    => $notification->data['message'][$locale] ?? $notification->data['message']['en'],
                    'read_at'    => $notification->read_at,
                    'created_at' => $notification->created_at->toDateTimeString(),
                ];
            });                
    
            return $this->apiSuccess(['unread_notifications' => $notifications]);
        }
        catch(\Throwable $th) {
            DB::rollBack();
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }
}
