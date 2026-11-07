<x-guest-layout>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.css') }}">

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <p class="mb-4 text-sm text-gray-600">
        Portal Jurnal KBM — gunakan URL
        <strong>jurnal-kbm.man3jember.sch.id</strong>
        (bukan situs utama WordPress).
        Password guru hanya dari reset admin; reset baru mengganti password lama.
    </p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <div class="relative mt-1">
                <x-text-input id="password" class="block w-full pe-10"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />

                <button type="button"
                        id="toggle-password"
                        class="absolute inset-y-0 end-0 flex items-center pe-3 text-gray-500 hover:text-gray-700 cursor-pointer border-0 bg-transparent p-0"
                        title="Tampilkan Password"
                        aria-label="Tampilkan Password">
                    <i class="fas fa-eye" id="toggle-password-icon" aria-hidden="true"></i>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        (function () {
            var input = document.getElementById('password');
            var toggle = document.getElementById('toggle-password');
            var icon = document.getElementById('toggle-password-icon');

            if (!input || !toggle || !icon) {
                return;
            }

            toggle.addEventListener('click', function () {
                var visible = input.type === 'text';

                input.type = visible ? 'password' : 'text';
                icon.classList.toggle('fa-eye', visible);
                icon.classList.toggle('fa-eye-slash', !visible);
                toggle.title = visible ? 'Tampilkan Password' : 'Sembunyikan Password';
                toggle.setAttribute('aria-label', toggle.title);
            });
        })();
    </script>
</x-guest-layout>
