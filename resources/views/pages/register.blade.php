<x-layout.blank :pageTitle="$pageTitle ?? null">
    <div x-data="{ showPassword: false, accepted: false, loading: false }">
        <x-ui.auth-shell title="$t('auth.registerTitle')" subtitle="$t('auth.registerSubtitle')">
            <form method="POST" action="{{ route('register.submit') }}" class="space-y-5" @submit="loading = true">
                @csrf

                <div>
                    <label class="mb-2 block text-sm font-medium text-heading" for="name" x-text="$t('auth.name')"></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-gray-400">
                            <x-icon name="user" :size="18" />
                        </span>
                        <input id="name" name="name" type="text" autocomplete="name" required value="{{ old('name') }}" placeholder="Camila Barreto"
                            class="focus-ring h-11 w-full rounded-lg border border-themed bg-gray-50 pr-3 pl-10 text-sm text-body outline-none focus:border-brand-300 dark:bg-gray-900" />
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-heading" for="email" x-text="$t('auth.email')"></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-gray-400">
                            <x-icon name="mail" :size="18" />
                        </span>
                        <input id="email" name="email" type="email" autocomplete="username" required value="{{ old('email') }}" placeholder="voce@garagem.com.br"
                            class="focus-ring h-11 w-full rounded-lg border border-themed bg-gray-50 pr-3 pl-10 text-sm text-body outline-none focus:border-brand-300 dark:bg-gray-900" />
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-heading" for="password" x-text="$t('auth.password')"></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-gray-400">
                            <x-icon name="lock" :size="18" />
                        </span>
                        <input id="password" name="password" autocomplete="new-password" required :type="showPassword ? 'text' : 'password'" placeholder="••••••••"
                            class="focus-ring h-11 w-full rounded-lg border border-themed bg-gray-50 pr-10 pl-10 text-sm text-body outline-none focus:border-brand-300 dark:bg-gray-900" />
                        <button type="button" @click="showPassword = !showPassword" :aria-label="$t('auth.password')"
                            class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <x-ui.icon-dyn expr="showPassword ? 'eye-off' : 'eye'" :names="['eye-off', 'eye']" :size="18" />
                        </button>
                    </div>
                </div>

                <label class="flex items-start gap-2 text-sm text-body">
                    <input type="checkbox" required x-model="accepted" class="mt-0.5 h-4 w-4 rounded border-themed accent-brand-500" />
                    <span class="min-w-0" x-text="$t('auth.terms')"></span>
                </label>

                <button type="submit" :disabled="loading"
                    class="focus-ring flex h-11 w-full items-center justify-center rounded-lg bg-brand-500 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60">
                    <template x-if="loading">
                        <span class="flex items-center gap-2">
                            <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                            <span x-text="$t('auth.registering')"></span>
                        </span>
                    </template>
                    <template x-if="!loading">
                        <span x-text="$t('auth.registerCta')"></span>
                    </template>
                </button>

                @if ($errors->any())
                    <p class="rounded-lg bg-error-50 px-4 py-2 text-center text-sm text-error-600 dark:bg-error-500/15 dark:text-error-500"
                        x-text="$t('auth.loginError')"></p>
                @endif
            </form>

            <x-slot:footer>
                <span x-text="$t('auth.hasAccount')"></span>
                <a href="{{ route('login') }}" class="font-medium text-brand-500 hover:text-brand-600" x-text="$t('auth.loginLink')"></a>
            </x-slot:footer>
        </x-ui.auth-shell>
    </div>
</x-layout.blank>
