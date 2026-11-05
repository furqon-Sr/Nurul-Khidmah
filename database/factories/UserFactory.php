<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     * Role default aman: role terendah (santri / wali santri).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => User::ROLE_SANTRI,
            'must_change_password' => false,
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => [
            'role' => User::ROLE_SUPER_ADMIN,
        ]);
    }

    public function pimpinan(): static
    {
        return $this->state(fn () => [
            'role' => User::ROLE_PIMPINAN,
        ]);
    }

    public function kepalaSekolah(): static
    {
        return $this->state(fn () => [
            'role' => User::ROLE_KEPALA_SEKOLAH,
        ]);
    }

    public function pengurusAkademik(): static
    {
        return $this->state(fn () => [
            'role' => User::ROLE_PENGURUS_AKADEMIK,
        ]);
    }

    public function guru(): static
    {
        return $this->state(fn () => [
            'role' => User::ROLE_TENAGA_PENGAJAR,
        ]);
    }

    public function wali(): static
    {
        return $this->state(fn () => [
            'role' => User::ROLE_SANTRI,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
