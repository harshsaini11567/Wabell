<?php

namespace App\Domains\Api\Conversation\Controllers;

use App\Http\Controllers\APIController;
use Illuminate\Http\Request;
use App\Domains\Core\Conversation\Models\Conversation;
use App\Domains\Core\Conversation\Models\ConversationParticipant;
use App\Domains\Core\Conversation\Models\Message;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use App\Domains\Core\User\Models\User;
use Illuminate\Support\Facades\Log;
class ConversationController extends APIController
{
    public function index(){
        Log::info("📥 [Conversations:IN] Fetching conversations list", [
            'user_id' => JWTAuth::user()->id ?? null,
        ]);
        try {
            $user = JWTAuth::user();
            $currentUserId = $user->id;
            $conversations = $user->conversations()
                ->where('is_blocked', 0)
                ->whereHas('messages')
                ->whereHas('participants')
                ->with(['participants', 'messages' => function ($query) {
                    $query->latest('created_at')->limit(1);
                }])
                ->withMax('messages', 'created_at') 
                ->orderByDesc('messages_max_created_at')
                ->paginate(config('constant.api_page_limit.conversation'));

                // Process the conversation data
                $conversationData = $conversations->through(function ($conversation) use ($currentUserId) {
                    $participant = $conversation->participants()->where('user_id', '!=', $currentUserId)->first();

                    $lastMessage = $conversation->messages()->latest('created_at')->first();
                    $messageType = optional($lastMessage)->content_type;
                    $content = (($messageType == 'image') ? "Image" : (($messageType == 'video') ? 'Video' : (($messageType == 'document') ? 'Document' : Str::limit(optional($lastMessage)->content, 50))));

                    $today = Carbon::now();
                    $sevenDaysAgo = $today->copy()->subDays(6);
                    $messageDate = optional($lastMessage)->created_at;
                    $isBetween = $messageDate->between($today, $sevenDaysAgo);
                    if ($isBetween) {
                        $lastMessageTime = $messageDate->format('D');
                        if ($messageDate->isToday()) {
                            $lastMessageTime = $messageDate->format(config('constant.date_format.time'));
                        }
                    } else {
                        $lastMessageTime = $messageDate->format(config('constant.date_format.date'));
                    }
                    $lastRead = $conversation->participants()->where('user_id', $currentUserId)->value('last_read');
                    $unreadCount = $conversation->messages()
                        ->where('sender_id', '!=', $currentUserId)
                        ->when($lastRead, function ($query) use ($lastRead) {
                            $query->where('created_at', '>', $lastRead);
                        })
                        ->count();

                    // $unreadCount = $conversation->messages()
                    // ->where('sender_id', '!=', $currentUserId)
                    // ->whereNull('read_at') // only count messages that are truly unread
                    // ->count();
                    return [
                        'conversation_id'           => $conversation->id,
                        'receiver_id'               => optional($participant)->id,
                        'receiver_name'             => optional($participant)->name,
                        'receiver_profile_image'    => optional($participant)->profile_image_url,                        
                        'last_message'              => $content, // Latest message content
                        'last_message_time'         => $lastMessageTime, // Latest message Time
                        'message_created_at'        => optional($messageDate)->format(config('constant.date_format.date')), // Latest message Time
                        'unread_count'              => $unreadCount,
                        'is_read'                   => $unreadCount == 0,
                        'is_available'  => (bool) optional($participant)->is_available,
                        ] + (!(optional($participant)->is_available) ? ['till_offline' => optional($participant)->till_offline] : []);
                });

                Log::info("📤 [Conversations:OUT] Success", [
                    'user_id' => $currentUserId,
                    'total_conversations' => count($conversationData),
                ]);

                return $this->apiSuccess(['conversations' => $conversationData], trans('messages.success'));
        } catch (\Exception $e) {
            // dd($e);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function blockConversation(Request $request){
        $request->validate([
            'conversation_id' => ['required', 'exists:conversations,id'],
        ],[],[
            'conversation_id' => trans('cruds.api.conversation_id'),
        ]);
        DB::beginTransaction();
        try {
            $conversation = Conversation::find($request->conversation_id);
            if (!$conversation) {
                return $this->apiError(trans('messages.conversation_not_found'));
            }
            $conversation->is_blocked = !$conversation->is_blocked;
            $conversation->save();

            DB::commit();
            if($conversation->is_blocked) { 
                return $this->apiSuccess(trans('messages.block_conversation'));
            }
            else{
                return $this->apiSuccess(trans('messages.unblock_conversation'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function deleteConversation($id){
        DB::beginTransaction();
        try {
            $conversation = Conversation::find($id);

            if (!$conversation) {
                return $this->apiError(trans('messages.error_message'));
            }
            if ($conversation) {
                $conversation->delete();
            }

            DB::commit();
            return $this->apiSuccess(trans('messages.delete_conversation'));
        } catch (\Exception $e) {
            DB::rollBack();
            
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function singleConversationMessages(Request $request){
        Log::info("📥 [singleConversationMessages:IN] Request received", [
            'receiver_id' => $request->receiver_id,
            'user_id' => JWTAuth::user()->id ?? null,
        ]);
        $request->validate([
            'receiver_id' => ['required', 'exists:users,id']
        ],[],[
            'receiver_id' => trans('cruds.api.receiver_id'),
        ]);
        DB::beginTransaction();
        try {
            $currentUser = JWTAuth::user();
            $currentUserId = $currentUser->id;
            $isProfessional = $currentUser->roles()
            ->where('id', config('constant.roles.master'))
            ->exists() && $currentUser->is_ban == 0;
            
            $isCustomer = !$isProfessional;
            $receiver = User::find($request->receiver_id);
            $receiverIsProfessional = $receiver->roles()
                ->where('id', config('constant.roles.master'))
                ->exists();
            
            // $professionalId = $isProfessional ? $currentUserId : $receiver->id;
            $professional = $isProfessional ? $currentUser : $receiver;
            /* $activePlan = UserSubscription::where('user_id', $professionalId)
                        ->where('end_date', '>=', now())   // still valid
                        ->where('status', 'active')              // active
                        ->latest()
                        ->exists(); */

            // $activePlan = $professional->activeSubscription;
         
            $chatStatus = getSetting('tutor_chat_status_cb');

            $canSendMessage = false;

            if ($chatStatus == 1 || $professional->activeSubscription) {
                $canSendMessage = true;
            }
            $reqUserId = $request->receiver_id;
            $conversation = $this->getPrivateConversation($currentUserId, $reqUserId);
            $participant = $conversation->participants()->where('user_id', '!=', $currentUserId)->first();

            $messages = $conversation
                        ->messages()
                        ->whereDoesntHave('deletedByUsers', function ($q) use ($currentUserId) {
                            $q->where('user_id', $currentUserId);
                        })
                        ->orderBy('created_at', 'desc')
                        ->paginate(config('constant.api_page_limit.message'));
                        
            ConversationParticipant::where('conversation_id', $conversation->id)
                ->where('user_id', $currentUserId)
                ->update(['last_read' => now()]);

            $conversation->messages()
                        ->where('sender_id', '!=', $currentUserId)
                        ->whereNull('read_at')
                        ->update(['read_at' => now()]);    

            $messageData = $this->getConversationMessages($messages);

            $receiverName = $participant->name;
            $receiverProfileImage = $participant->profile_image_url;
            $receiverAvailable = $participant->is_available;           
            DB::commit();
            
            Log::info("📤 [singleConversationMessages:OUT] Success", [
                'conversation_id' => $conversation->id,
                'messages_count' => count($messageData),
                'receiver_id' => $receiver->id,
                'can_send_message' => $canSendMessage,
            ]);

            $data   = [
                'conversation_id' => $conversation->id ?? null,
                'receiver_name' => $receiverName ?? null,
                'receiver_profile_image' => $receiverProfileImage ?? null,
                'is_chat_initiate' => $canSendMessage,
                'message_next_page_url' => $messages->nextPageUrl(),
                'messages' => $messageData,
                'is_available'  => (bool) $participant->is_available,
            ] + (!$participant->is_available ? ['till_offline' => $participant->till_offline] : []);

            return $this->apiSuccess($data,trans('messages.success'));
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function SendMessage(Request $request){
        // dd($request->receiver_id);
        Log::info("📥 [SendMessage:IN] Request received", [
            'receiver_id' => $request->receiver_id,
            'has_message' => $request->filled('message'),
            'has_files'   => $request->hasFile('files'),
        ]);
        $request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'message' => ['nullable'],
            'files.*' => ['nullable', 'max:5120'], // 5MB max
        ],[],[
            'receiver_id' => trans('cruds.api.receiver_id'),
            'message' => trans('cruds.api.message'),
            'files' => trans('cruds.api.files'),
        ]);
        DB::beginTransaction();

        try {
            $sender = JWTAuth::user();
            if ($sender->is_ban == 1) {
                return $this->apiError(trans('messages.account_ban'));
            }
            $receiverId = $request->receiver_id;
            $receiverUser = User::find($receiverId);
            Log::info("👥 [SendMessage] Sender {$sender->id} → Receiver {$receiverId}");
            // Step 1: Get or create private conversation
            $conversation = $this->getPrivateConversation($sender->id, $receiverId);
            // dd($conversation);
            if ($conversation->is_blocked) {
                return $this->apiError(trans('messages.block_conversation'));
            }
            // Step 2: Update sender's last_read
            ConversationParticipant::where('conversation_id', $conversation->id)
                ->where('user_id', $sender->id)
                ->update(['last_read' => now()]);
            Log::info("✅ [SendMessage] Updated last_read for user {$sender->id} in conversation {$conversation->id}");

            $videoExtensions = ['mp4', 'mkv', 'webm', 'flv', 'avi', 'mov', 'wmv', 'mpeg', 'mpg', 'm4v', '3gp', '3g2', 'f4v', 'f4p', 'f4a', 'f4b'];
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'tiff', 'tif', 'ico', 'heic', 'heif'];

            // Step 3: Handle text message
            if ($request->filled('message')) {
               $message= Message::create([
                    'sender_id' => $sender->id,
                    'conversation_id' => $conversation->id,
                    'content' => $request->message,
                    'content_type' => 'text',
                ]);
            }

            // Step 4: Handle file attachments
            if ($request->hasFile('files')) {
                $files = is_array($request->file('files')) ? $request->file('files') : [$request->file('files')];
                foreach ($files as $file) {
                    $extension = strtolower($file->getClientOriginalExtension());
                    if (in_array($extension, $imageExtensions)) {
                        $fileType = 'message_image';
                        $contentType = 'image';
                    } elseif (in_array($extension, $videoExtensions)) {
                        $fileType = 'message_video';
                        $contentType = 'video';
                    } else {
                        $fileType = 'message_document';
                        $contentType = 'document';
                    }

                    $message = Message::create([
                        'sender_id' => $sender->id,
                        'conversation_id' => $conversation->id,
                        'content_type' => $contentType,
                    ]);

                    // Upload file
                    uploadImage($message, $file, 'conversation/' . $conversation->id, $fileType, 'original', 'save',null);
                }
            }

            sendUserNotification(
                $receiverUser->id,
                'message_notification_title',
                'message_notification_body',
                'message',
                null,
                false,
                ['sender' => $sender->name],
            );
            DB::commit();

            Log::info("📤 [SendMessage:OUT] Success", [
                'conversation_id' => $conversation->id,
                'message_id' => $message?->id,
                'sender_id' => $sender->id,
                'receiver_id' => $receiverId,
            ]);

            $responceData = $message;
            return $this->apiSuccess([$responceData], trans('messages.message_send'));
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function deleteMessage($id){
        DB::beginTransaction();
        try {
            $user = JWTAuth::user();
            $message = Message::find($id);
            if (!$message) {
                return $this->apiError(trans('messages.not_found'));
            }
            if ($message->deletedByUsers()->where('user_id', $user->id)->exists()) {
                return $this->apiError(trans('messages.message_already_deleted'));
            }
            $message->deletedByUsers()->attach($user->id);

            DB::commit();
            return $this->apiSuccess(trans('messages.message_delete'));
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    protected function getPrivateConversation($userId, $otherUserId)
    {
        $conversation = Conversation::where('conversation_type', 'private')
            ->whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereHas('participants', function ($query) use ($otherUserId) {
                $query->where('user_id', $otherUserId);
            })
            ->whereDoesntHave('participants', function ($q) use ($userId, $otherUserId) {
                $q->whereNotIn('user_id', [$userId, $otherUserId]);
            })
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create(['created_by' => $userId]);
            if ($conversation) {
                $conversation->participants()->sync([$userId, $otherUserId]);
            }
        } else {
            // Mark all messages read
            $conversation->messages()->whereNull('read_at')->update(['read_at' => now()]);
        }

        return $conversation;
    }

    protected function getConversationMessages($messages)
    {
        $reverseMessages = $messages->reverse();

        $groupedMessages  = $reverseMessages->groupBy(function ($message) {
            $today = Carbon::now();
            $sevenDaysAgo = $today->copy()->subDays(6);

            $messageDate = $message->created_at;
            $isBetween = $messageDate->between($today, $sevenDaysAgo);
            if ($isBetween) {
                $dayOfWeek = $messageDate->format('l');
                if ($messageDate->isToday()) {
                    return trans('global.today');
                }
                if ($messageDate->isYesterday()) {
                    return trans('global.yesterday');
                }
                return $dayOfWeek;
            }
            return $message->created_at->translatedFormat(config('constant.date_format.date'));
        });

        $messageData = $groupedMessages->map(function ($group) {
            return $group->map(function ($message) {
                $user = JWTAuth::user();
                $currentUserId = $user->id;
                $remainingTime = $message->created_at->diffForHumans(now());

                $messageType = $message->content_type;
                if ($messageType == 'image') {
                    $content = $message->message_image_urls;
                } else if ($messageType == 'video') {
                    $content = $message->message_video_urls;
                } else if ($messageType == 'document') {
                    $content = $message->message_document_urls;
                } else {
                    $content = $message->content;
                }

                $isSender = false;
                if($message->sender_id == $currentUserId){
                    $isSender = true;
                }

                return [
                    'id'            => $message->id,
                    'message_type'  => $messageType ?? '',
                    'content'       => $content ?? '',
                    'message_time'  => str_replace(['before'], ['ago'], $remainingTime),
                    'created_date'  => $message->created_at->translatedFormat(config('constant.date_format.date')),
                    'created_time'  => $message->created_at->translatedFormat(config('constant.date_format.time')),

                    'is_sender'     => $isSender,
                    'is_read'       => !is_null($message->read_at),
                ];
            });
        });

        return $messageData;
    }

    public function hasRead(){
        $user = JWTAuth::user();
        // dd($user);
        $hasRead = Message::whereHas('conversation.participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->exists();
        Log::info("📥 [hasRead:IN] Checking unread messages for user_id={$user->id}");
        Log::info("📤 [hasRead:OUT] User {$user->id} unread status = " . ($hasRead ? 'UNREAD EXISTS' : 'ALL READ'));
        return $this->apiSuccess([
            'is_read' => !$hasRead,
        ]);
    }

    public function markAsRead($id)
    {
        try {
            $user = JWTAuth::user();
            $userId = $user['id'];

            Log::info("📥 [markAsRead:IN] User {$userId} is marking conversation {$id} as read.");

            $conversation = Conversation::findOrFail($id);

            ConversationParticipant::where('conversation_id', $conversation->id)
                ->where('user_id', $userId)
                ->update(['last_read' => now()]);

            $updatedCount  = $conversation->messages()
                        ->where('sender_id', '!=', $userId)
                        ->whereNull('read_at')
                        ->update(['read_at' => now()]);

            Log::info("✅ [markAsRead] User {$userId} marked {$updatedCount} messages as read in conversation {$id}.");

            Log::info("📤 [markAsRead:OUT] Finished processing for user {$userId} in conversation {$id}.");

            return $this->apiSuccess([
                'status' => true,
                'message' => 'Conversation and messages marked as read.'
            ]);
        } catch (\Exception $e) {
            return $this->apiError([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
