<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BasePage;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

final class Login extends BasePage
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => 'admin@admin.com',
            'password' => 'password',
            'remember' => true,
        ]);
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Log::warning('Login rate limited', ['ip' => request()->ip()]);

            throw ValidationException::withMessages([
                'data.email' => __('filament-panels::pages/auth/login.messages.throttled', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]),
            ]);
        }

        $data = $this->form->getState();
        $email = $data['email'];
        $password = $data['password'];

        try {
            // Debug logging
            Log::info('Login attempt', [
                'email' => $email,
                'password_length' => mb_strlen((string) $password),
            ]);

            // Check if user exists
            $user = User::query()->where('email', $email)->first();

            if (! $user) {
                Log::warning('Login failed: user not found', ['email' => $email]);

                throw ValidationException::withMessages([
                    'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
                ]);
            }

            Log::info('User found', [
                'email' => $user->email,
                'password_hash_prefix' => mb_substr((string) $user->password, 0, 20) . '...',
                'hash_check_result' => Hash::check($password, $user->password) ? 'MATCH' : 'NO_MATCH',
            ]);
            Log::info('Password hash details', [
                'email' => $user->email,
                'current_password_hash' => $user->password,
                'password' => $data['password'],
                'password_hash' => Hash::make($data['password']),
            ]);

            // Try authentication
            $authResult = Filament::auth()->attempt([
                'email' => $email,
                'password' => $password,
            ], $data['remember'] ?? false);

            Log::info('Auth attempt result', ['success' => $authResult]);

            if (! $authResult) {
                Log::warning('Login failed: invalid credentials', ['email' => $email]);

                throw ValidationException::withMessages([
                    'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
                ]);
            }

            $authUser = Filament::auth()->user();

            // Check panel access
            $canAccess = $authUser->canAccessPanel(Filament::getCurrentPanel());
            Log::info('Panel access check', [
                'email' => $authUser->email,
                'can_access' => $canAccess,
            ]);

            if (! $canAccess) {
                Filament::auth()->logout();
                Log::warning('Login failed: no panel access', ['email' => $email]);

                throw ValidationException::withMessages([
                    'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
                ]);
            }

            session()->regenerate();
            Log::info('Login successful', ['email' => $email]);

            return resolve(LoginResponse::class);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Login failed: unexpected error', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }
    }
}
