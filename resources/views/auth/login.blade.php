<x-guest-layout>

    {{-- Heading --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Selamat datang kembali</h1>
        <p class="text-sm text-gray-500 mt-1.5">Masuk untuk mengakses HRIS Yayasan Fatahillah.</p>
    </div>

    {{-- Session Status --}}
    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- Email Address --}}
        <div>
            <label for="email" class="form-label">Email</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    autocomplete="username" placeholder="nama@yayasanfatahillah.sch.id"
                    class="input pl-9 {{ $errors->has('email') ? 'input-error' : '' }}">
            </div>
            <x-input-error :messages="$errors->get('email')" class="form-error" />
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="form-label">Kata Sandi</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    placeholder="••••••••" x-data
                    class="input pl-9 pr-10 {{ $errors->has('password') ? 'input-error' : '' }}">

                {{-- Toggle show/hide password --}}
                <button type="button"
                    onclick="const i=document.getElementById('password'); i.type = i.type==='password' ? 'text' : 'password'; this.querySelector('.eye-open').classList.toggle('hidden'); this.querySelector('.eye-closed').classList.toggle('hidden');"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    aria-label="Tampilkan kata sandi">
                    <svg class="eye-open w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <svg class="eye-closed hidden w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="form-error" />
        </div>

        {{-- Remember Me + Forgot Password --}}
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember"
                    class="rounded border-gray-300 focus:ring-2 focus:ring-offset-0" style="color: var(--c-accent);">
                <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-medium hover:underline"
                    style="color: var(--c-accent-text);">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn-primary w-full justify-center py-2.5 text-sm font-semibold">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H3" />
            </svg>
            Masuk
        </button>
    </form>

    {{-- Footer kecil --}}
    <p class="text-center text-xs text-gray-400 mt-8">
        Lupa akun atau butuh bantuan? Hubungi admin SDM yayasan.
    </p>

</x-guest-layout>
