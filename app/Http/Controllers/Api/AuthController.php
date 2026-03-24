<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        return response()->json([
            'message' => 'Registration successful. Please verify your email before logging in.',
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();

            return response()->json([
                'message' => 'Please verify your email before logging in. A new verification link has been sent.',
            ], 403);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function verifyEmail(int $id, string $hash): RedirectResponse
    {
        $nextAppUrl = rtrim((string) env('NEXT_APP_URL', 'http://localhost:3000'), '/');
        $user = User::query()->findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->away($nextAppUrl.'/auth/login?verification=invalid');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->away($nextAppUrl.'/auth/login?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->away($nextAppUrl.'/auth/login?verified=1');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function destroyAccount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        /** @var User $user */
        $user = $request->user();

        if (!Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided password is incorrect.',
                'errors' => [
                    'password' => ['The provided password is incorrect.'],
                ],
            ], 422);
        }

        DB::transaction(function () use ($user): void {
            $user->tokens()->delete();

            if (!empty($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            $user->clearMediaCollection('avatar');
            $user->delete();
        });

        return response()->json(['message' => 'Account deleted successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->loadCount(['followers', 'following']);

        return response()->json($user);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'bio' => ['nullable', 'string', 'max:1000'],
            'weekly_recommendation_emails' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $imagePath = $user->image;
        $removeImage = filter_var($request->input('remove_image'), FILTER_VALIDATE_BOOLEAN);

        if ($removeImage && !$request->hasFile('image')) {
            if (!empty($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $imagePath = null;
        }

        if ($request->hasFile('image')) {
            if (!empty($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $imagePath = $request->file('image')->store('avatars', 'public');
        }

        $user->update([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'bio' => $validated['bio'] ?? null,
            'weekly_recommendation_emails' => (bool) ($validated['weekly_recommendation_emails'] ?? $user->weekly_recommendation_emails),
            'image' => $imagePath,
        ]);

        return response()->json($user->fresh());
    }
}
