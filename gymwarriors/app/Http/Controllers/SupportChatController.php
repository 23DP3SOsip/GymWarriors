<?php

namespace App\Http\Controllers;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Http\Request;

class SupportChatController extends Controller
{
    public function messages(Request $request)
    {
        $conversation = $request->user()
            ->supportConversations()
            ->latest('id')
            ->first();

        if (! $conversation) {
            return response()->json(['messages' => []]);
        }

        return $this->userMessages($request, $conversation);
    }

    public function storeMessage(Request $request)
    {
        $conversation = $request->user()->supportConversations()
            ->where('status', 'open')
            ->latest('id')
            ->first();

        if (! $conversation) {
            $conversation = $request->user()->supportConversations()->create([
                'status' => 'open',
            ]);
        }

        return $this->createUserMessage($request, $conversation);
    }

    public function userConversations(Request $request)
    {
        return response()->json([
            'conversations' => $request->user()
                ->supportConversations()
                ->with([
                    'latestMessage' => fn ($query) => $query->select([
                        'support_messages.id',
                        'support_messages.support_conversation_id',
                        'support_messages.body',
                        'support_messages.is_support',
                        'support_messages.created_at',
                    ]),
                ])
                ->orderByDesc('id')
                ->get()
                ->map(fn (SupportConversation $conversation) => $this->conversationItem($conversation))
                ->values(),
        ]);
    }

    public function startConversation(Request $request)
    {
        $conversation = $request->user()->supportConversations()
            ->where('status', 'open')
            ->latest('id')
            ->first();

        if (! $conversation) {
            $conversation = $request->user()->supportConversations()->create([
                'status' => 'open',
            ]);
        }

        return response()->json([
            'conversation' => $this->conversationItem($conversation->load('latestMessage')),
        ], $conversation->wasRecentlyCreated ? 201 : 200);
    }

    public function userConversationMessages(Request $request, SupportConversation $conversation)
    {
        $this->ensureConversationOwner($request, $conversation);

        return $this->userMessages($request, $conversation);
    }

    public function storeUserConversationMessage(Request $request, SupportConversation $conversation)
    {
        $this->ensureConversationOwner($request, $conversation);

        return $this->createUserMessage($request, $conversation);
    }

    public function close(Request $request, SupportConversation $conversation)
    {
        if ($conversation->status === 'open') {
            $conversation->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => $request->user()->id,
            ]);
        }

        return response()->json([
            'conversation' => $this->conversationItem($conversation->fresh(['user', 'latestMessage'])),
        ]);
    }

    private function userMessages(Request $request, SupportConversation $conversation)
    {
        $conversation->messages()
            ->where('is_support', true)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'conversation' => $this->conversationItem($conversation->load('latestMessage')),
            'messages' => $this->messagePayload($conversation),
        ]);
    }

    private function createUserMessage(Request $request, SupportConversation $conversation)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        if ($conversation->status !== 'open') {
            return response()->json([
                'message' => 'Šī saruna ir slēgta. Sāc jaunu sarunu, lai sazinātos ar atbalstu.',
            ], 409);
        }

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => trim($validated['body']),
            'is_support' => false,
        ]);

        $conversation->update(['last_message_at' => $message->created_at]);

        return response()->json([
            'message' => $this->messageItem($message),
            'conversation' => $this->conversationItem($conversation->fresh(['latestMessage'])),
        ], 201);
    }

    public function conversations()
    {
        $conversations = SupportConversation::query()
            ->with([
                'user:id,first_name,last_name,email',
                'latestMessage' => fn ($query) => $query->select([
                    'support_messages.id',
                    'support_messages.support_conversation_id',
                    'support_messages.sender_id',
                    'support_messages.body',
                    'support_messages.is_support',
                    'support_messages.created_at',
                ]),
            ])
            ->withCount([
                'messages as unread_count' => fn ($query) => $query
                    ->whereColumn('sender_id', 'support_conversations.user_id')
                    ->whereNull('read_at'),
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (SupportConversation $conversation) => [
                'id' => $conversation->id,
                'user' => [
                    'id' => $conversation->user->id,
                    'name' => trim($conversation->user->first_name.' '.$conversation->user->last_name),
                    'email' => $conversation->user->email,
                ],
                'last_message' => $conversation->latestMessage
                    ? $this->messageItem($conversation->latestMessage)
                    : null,
                'last_message_at' => $conversation->last_message_at,
                'unread_count' => $conversation->unread_count,
                'status' => $conversation->status,
                'closed_at' => $conversation->closed_at,
            ])
            ->values();

        return response()->json(['conversations' => $conversations]);
    }

    public function adminMessages(SupportConversation $conversation)
    {
        $conversation->messages()
            ->where('is_support', false)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'messages' => $this->messagePayload($conversation),
        ]);
    }

    public function reply(Request $request, SupportConversation $conversation)
    {
        if ($conversation->status !== 'open') {
            return response()->json([
                'message' => 'Šī saruna ir slēgta, un uz to vairs nevar atbildēt.',
            ], 409);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => trim($validated['body']),
            'is_support' => true,
        ]);

        $conversation->update(['last_message_at' => $message->created_at]);

        return response()->json([
            'message' => $this->messageItem($message),
        ], 201);
    }

    private function ensureConversationOwner(Request $request, SupportConversation $conversation): void
    {
        abort_unless($conversation->user_id === $request->user()->id, 404);
    }

    private function conversationItem(SupportConversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'status' => $conversation->status,
            'last_message' => $conversation->latestMessage
                ? $this->messageItem($conversation->latestMessage)
                : null,
            'last_message_at' => $conversation->last_message_at,
            'closed_at' => $conversation->closed_at,
        ];
    }

    private function messagePayload(SupportConversation $conversation): array
    {
        return $conversation->messages()
            ->orderBy('id')
            ->get(['id', 'sender_id', 'body', 'is_support', 'read_at', 'created_at'])
            ->map(fn (SupportMessage $message) => $this->messageItem($message))
            ->all();
    }

    private function messageItem(SupportMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'body' => $message->body,
            'is_support' => $message->is_support,
            'read_at' => $message->read_at,
            'created_at' => $message->created_at,
        ];
    }
}
