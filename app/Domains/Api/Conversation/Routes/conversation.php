<?php

use Illuminate\Support\Facades\Route;
use App\Domains\Api\Conversation\Controllers\ConversationController;

    Route::get('conversations', [ConversationController::class, 'index']);
	Route::post('block-conversation', [ConversationController::class, 'blockConversation']);
	Route::delete('delete-conversation/{conversation_id}', [ConversationController::class, 'deleteConversation']);
	Route::post('conversations/messages', [ConversationController::class, 'singleConversationMessages']);
	Route::post('conversations/send-message', [ConversationController::class, 'SendMessage']);
	Route::delete('conversations/delete-message/{message_id}', [ConversationController::class, 'deleteMessage']);
	Route::get('/conversations/has-read', [ConversationController::class, 'hasRead']);
	Route::post('/conversations/{id}/read', [ConversationController::class, 'markAsRead']);
?>