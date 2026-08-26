<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_demo_accounts_when_empty(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'briel@ilinkcst.edu.ph', 'role' => 'teacher']);
        $this->assertDatabaseHas('users', ['email' => 'student@cast.test', 'role' => 'student']);
    }

    public function test_it_skips_demo_seed_outside_local_without_flag(): void
    {
        config(['cast.seed_demo' => false]);
        $this->app->detectEnvironment(fn () => 'production');

        (new DatabaseSeeder)->run();

        $this->assertDatabaseMissing('users', ['email' => 'briel@ilinkcst.edu.ph']);
        $this->assertSame(0, User::query()->count());
    }

    public function test_it_does_not_seed_when_users_already_exist(): void
    {
        User::factory()->create(['email' => 'owner@example.com']);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseMissing('users', ['email' => 'briel@ilinkcst.edu.ph']);
        $this->assertSame(1, User::query()->count());
    }
}
