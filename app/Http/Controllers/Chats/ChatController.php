<?php

namespace App\Http\Controllers\Chats;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\Repositories\Contracts\IChat;
use App\Repositories\Contracts\IMessage;
use App\Repositories\Eloquent\Criteria\WithTrashed;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected $chats;
    protected $messages;

    public function __construct(IChat $chats, IMessage $messages)
    {
        $this->chats = $chats;
        $this->messages = $messages;
    }

    // Send message to user
    public function sendMessage(Request $request)
    {
        // validate the request
        $this->validate($request, [
           'recipient' => ['required'],
           'body' => ['required']
        ]);

        $recipient = $request->recipient;
        $user = auth()->user();
        $body = $request->body;

        // check if there is an existing chat between
        // the auth user and the recipient
        $chat = $user->getChatWithUser($recipient);

        if(! $chat) {
            $chat = $this->chats->create([]);
            $this->chats->createParticipants($chat->id, [$user->id, $recipient]);
        }

        // add the message to the chat
        $message = $this->messages->create([
           'user_id' => $user->id,
           'chat_id' => $chat->id,
           'body' => $body,
           'last_read' => null
        ]);

        return new MessageResource($message);
    }

    // Get chats for user
    public function getUserChats()
    {
        $chats = $this->chats->getUserChats();
        return ChatResource::collection($chats);
    }

    // Get messages for chat
    public function getChatMessages($id)
    {
        $messages = $this->messages->withCriteria([
                        new WithTrashed()
                    ])->findWhere('chat_id', $id);

        return MessageResource::collection($messages);
    }

    // Mark chat as read
    public function markAsRead($id)
    {
        $chat = $this->chats->find($id);
        $chat->markAsReadForUser(auth()->id());
        return response()->json(['message' => 'Successful'], 200);
    }

    // destroy message
    public function destroyMessage($id)
    {
        $message = $this->messages->find($id);
        $this->authorize('delete', $message);
        $message->delete();
    }
}
