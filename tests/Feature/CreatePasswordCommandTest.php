<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreatePasswordCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_password_via_cli_argument(): void
    {
        $this->artisan('password:create', ['password' => 'rahasia123'])
            ->expectsOutputToContain('Password berhasil disimpan ke database!')
            ->assertSuccessful();

        $this->assertDatabaseCount('passwords', 1);

        $user = User::first();
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_can_replace_existing_password_with_flag(): void
    {
        User::create(['password' => 'lama123']);

        $this->artisan('password:create', [
            'password' => 'baru456',
            '--replace' => true,
        ])
            ->expectsOutputToContain('Password lama telah dihapus')
            ->assertSuccessful();

        $this->assertDatabaseCount('passwords', 1);

        $user = User::first();
        $this->assertTrue(Hash::check('baru456', $user->password));
    }

    public function test_interactive_password_creation_matches_confirmation(): void
    {
        $this->artisan('password:create')
            ->expectsQuestion('Masukkan password: ', 'passwordInteraktif')
            ->expectsQuestion('Konfirmasi password: ', 'passwordInteraktif')
            ->assertSuccessful();

        $this->assertDatabaseCount('passwords', 1);
        $this->assertTrue(Hash::check('passwordInteraktif', User::first()->password));
    }
}
