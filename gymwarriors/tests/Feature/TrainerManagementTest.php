<?php

namespace Tests\Feature;

use App\Models\Trainer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainerManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role = 'user'): User
    {
        $user = User::create([
            'first_name' => 'Alex',
            'last_name' => 'User',
            'email' => uniqid('trainer-test-', true).'@example.com',
            'password' => bcrypt('Password123!'),
        ]);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    private function trainerData(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Anna',
            'last_name' => 'Kalniņa',
            'specialty' => 'Strength training',
            'experience' => 'Five years of coaching',
            'description' => 'A trainer profile description.',
            'email' => 'anna@example.com',
            'phone' => '+371 2000 0000',
            'image' => '/images/trainers/trainer-1.jpg',
        ], $overrides);
    }

    public function test_authenticated_users_can_view_trainers(): void
    {
        $user = $this->createUser();
        $trainer = Trainer::create($this->trainerData());

        $this->actingAs($user)
            ->getJson('/api/trainers')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $trainer->id,
                'image' => '/images/trainers/trainer-1.jpg',
            ]);
    }

    public function test_admin_can_create_update_and_delete_a_trainer(): void
    {
        $admin = $this->createUser('admin');

        $createdTrainer = $this->actingAs($admin)
            ->postJson('/api/admin/trainers', $this->trainerData())
            ->assertCreated()
            ->assertJsonPath('trainer.first_name', 'Anna');

        $trainer = Trainer::findOrFail($createdTrainer->json('trainer.id'));

        $this->actingAs($admin)
            ->putJson("/api/admin/trainers/{$trainer->id}", $this->trainerData([
                'first_name' => 'Aija',
                'image' => 'https://example.com/trainer.jpg',
            ]))
            ->assertOk()
            ->assertJsonPath('trainer.first_name', 'Aija')
            ->assertJsonPath('trainer.image', 'https://example.com/trainer.jpg');

        $this->actingAs($admin)
            ->deleteJson("/api/admin/trainers/{$trainer->id}")
            ->assertOk();

        $this->assertDatabaseMissing('trainers', ['id' => $trainer->id]);
    }

    public function test_non_admin_cannot_manage_trainers(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->postJson('/api/admin/trainers', $this->trainerData())
            ->assertForbidden();
    }

    public function test_guest_cannot_manage_trainers(): void
    {
        $this->postJson('/api/admin/trainers', $this->trainerData())
            ->assertUnauthorized();
    }

    public function test_trainer_image_rejects_unsafe_urls(): void
    {
        $admin = $this->createUser('admin');

        $this->actingAs($admin)
            ->postJson('/api/admin/trainers', $this->trainerData([
                'image' => 'javascript:alert(1)',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);
    }
}
