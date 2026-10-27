<?php

namespace Tests\Feature;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_page_includes_csrf_token_for_authenticated_form_posts(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('name="csrf-token"', false)
            ->assertSee('content="'.csrf_token().'"', false);
    }

    public function test_user_can_start_a_support_conversation(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->postJson('/api/support/messages', ['body' => 'I need help'])
            ->assertCreated()
            ->assertJsonPath('message.body', 'I need help')
            ->assertJsonPath('message.is_support', false);

        $this->assertDatabaseHas('support_conversations', [
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('support_messages', [
            'sender_id' => $user->id,
            'body' => 'I need help',
            'is_support' => false,
        ]);
    }

    public function test_opening_chat_does_not_create_an_empty_conversation(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->getJson('/api/support/messages')
            ->assertOk()
            ->assertExactJson(['messages' => []]);

        $this->assertDatabaseMissing('support_conversations', [
            'user_id' => $user->id,
        ]);
    }

    public function test_admin_can_read_and_reply_to_a_user_conversation(): void
    {
        $user = $this->createUser();
        $admin = $this->createUser(role: 'admin');
        $conversation = SupportConversation::create(['user_id' => $user->id]);
        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => 'Can you help me?',
            'is_support' => false,
        ]);

        $this->actingAs($admin)
            ->getJson('/api/admin/support-conversations')
            ->assertOk()
            ->assertJsonPath('conversations.0.unread_count', 1)
            ->assertJsonPath('conversations.0.user.email', $user->email);

        $this->actingAs($admin)
            ->getJson("/api/admin/support-conversations/{$conversation->id}/messages")
            ->assertOk()
            ->assertJsonPath('messages.0.body', 'Can you help me?');

        $this->assertNotNull($message->fresh()->read_at);

        $this->actingAs($admin)
            ->postJson("/api/admin/support-conversations/{$conversation->id}/messages", [
                'body' => 'Of course, how can we help?',
            ])
            ->assertCreated()
            ->assertJsonPath('message.is_support', true);

        $this->actingAs($user)
            ->getJson('/api/support/messages')
            ->assertOk()
            ->assertJsonPath('messages.1.body', 'Of course, how can we help?');

        $this->assertNotNull(
            SupportMessage::where('support_conversation_id', $conversation->id)
                ->where('is_support', true)
                ->firstOrFail()
                ->read_at
        );
    }

    public function test_admin_can_close_a_conversation_and_user_can_view_history_and_start_another(): void
    {
        $user = $this->createUser();
        $admin = $this->createUser(role: 'admin');

        $this->actingAs($user)
            ->postJson('/api/support/conversations')
            ->assertCreated()
            ->assertJsonPath('conversation.status', 'open');

        $this->postJson('/api/support/messages', ['body' => 'First request'])
            ->assertCreated();

        $conversation = SupportConversation::firstOrFail();

        $this->actingAs($admin)
            ->postJson("/api/admin/support-conversations/{$conversation->id}/close")
            ->assertOk()
            ->assertJsonPath('conversation.status', 'closed');

        $this->assertDatabaseHas('support_conversations', [
            'id' => $conversation->id,
            'status' => 'closed',
            'closed_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('support_messages', [
            'support_conversation_id' => $conversation->id,
            'body' => 'First request',
        ]);

        $this->actingAs($user)
            ->getJson('/api/support/conversations')
            ->assertOk()
            ->assertJsonPath('conversations.0.status', 'closed');

        $this->getJson("/api/support/conversations/{$conversation->id}/messages")
            ->assertOk()
            ->assertJsonPath('messages.0.body', 'First request');

        $this->postJson("/api/support/conversations/{$conversation->id}/messages", [
            'body' => 'Reply to closed conversation',
        ])->assertStatus(409);

        $this->postJson('/api/support/conversations')
            ->assertCreated()
            ->assertJsonPath('conversation.status', 'open');

        $this->assertCount(2, SupportConversation::where('user_id', $user->id)->get());
    }

    public function test_user_cannot_read_another_users_conversation_history(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();
        $conversation = SupportConversation::create(['user_id' => $owner->id]);

        $this->actingAs($otherUser)
            ->getJson("/api/support/conversations/{$conversation->id}/messages")
            ->assertNotFound();
    }

    public function test_only_admins_can_access_the_support_inbox(): void
    {
        $user = $this->createUser();
        $conversation = SupportConversation::create(['user_id' => $user->id]);

        $this->getJson('/api/admin/support-conversations')->assertUnauthorized();

        $this->actingAs($user)
            ->getJson('/api/admin/support-conversations')
            ->assertForbidden();

        $this->actingAs($user)
            ->postJson("/api/admin/support-conversations/{$conversation->id}/messages", [
                'body' => 'Not allowed',
            ])
            ->assertForbidden();
    }

    public function test_support_message_requires_a_non_empty_body(): void
    {
        $this->actingAs($this->createUser())
            ->postJson('/api/support/messages', ['body' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['body']);
    }

    private function createUser(string $role = 'user'): User
    {
        $user = User::create([
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'Password123!',
        ]);

        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
