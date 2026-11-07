<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /** Max percobaan per kombinasi email+IP. */
    private const MAX_ATTEMPTS_PER_EMAIL = 5;

    /** Max percobaan per IP (antisipasi spray banyak akun). */
    private const MAX_ATTEMPTS_PER_IP = 30;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            $this->hitThrottles();

            throw ValidationException::withMessages([
                // Pakai terjemahan ID; fallback teks agar tidak tampil raw key "auth.failed".
                'email' => __('auth.failed') === 'auth.failed'
                    ? 'Email atau password salah. Pastikan URL https://jurnal-kbm.man3jember.sch.id/login dan password sesuai reset terakhir dari admin.'
                    : __('auth.failed'),
            ]);
        }

        if (! Auth::user()->is_active) {
            Auth::logout();
            $this->hitThrottles();

            throw ValidationException::withMessages([
                'email' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        RateLimiter::clear($this->ipThrottleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $emailLimited = RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS_PER_EMAIL);
        $ipLimited = RateLimiter::tooManyAttempts($this->ipThrottleKey(), self::MAX_ATTEMPTS_PER_IP);

        if (! $emailLimited && ! $ipLimited) {
            return;
        }

        event(new Lockout($this));

        $seconds = max(
            RateLimiter::availableIn($this->throttleKey()),
            RateLimiter::availableIn($this->ipThrottleKey())
        );

        $throttle = __('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => ceil($seconds / 60),
        ]);

        throw ValidationException::withMessages([
            'email' => $throttle === 'auth.throttle'
                ? "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik."
                : $throttle,
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    public function ipThrottleKey(): string
    {
        return 'login-ip|'.$this->ip();
    }

    private function hitThrottles(): void
    {
        RateLimiter::hit($this->throttleKey());
        RateLimiter::hit($this->ipThrottleKey());
    }
}
