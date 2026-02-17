<?php

use App\Models\User;

test('api user can register and receive token', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'API User',
        'username' => 'api-user',
        'email' => 'api-user@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'username', 'email'],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'api-user@example.com',
        'username' => 'api-user',
    ]);
});

test('api user can login and receive token', function () {
    $user = User::factory()->create([
        'email' => 'login-user@example.com',
        'password' => 'Password123!',
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'username', 'email'],
        ]);
});

test('api login fails with invalid credentials', function () {
    User::factory()->create([
        'email' => 'wrong-login@example.com',
        'password' => 'Password123!',
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'wrong-login@example.com',
        'password' => 'invalid-password',
    ]);

    $response->assertUnauthorized()
        ->assertJson([
            'message' => 'Invalid credentials',
        ]);
});
