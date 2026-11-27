{{-- Pilha de avisos — porte de components/ui/ToastStack.tsx. --}}
<div class="pointer-events-none fixed top-4 right-4 z-[100] flex flex-col gap-2">
    <template x-for="toast in $store.ui.toasts" :key="toast.id">
        <div class="pointer-events-auto flex w-80 max-w-[calc(100vw-2rem)] items-start gap-3 rounded-lg border bg-white p-4 shadow-theme-md dark:bg-gray-dark"
            :class="({
                success: 'border-success-200 dark:border-success-500/30',
                error: 'border-error-200 dark:border-error-500/30',
                warning: 'border-warning-200 dark:border-warning-500/30',
                info: 'border-blue-light-200 dark:border-blue-light-500/30',
            })[toast.type]">
            <span :class="({
                    success: 'text-success-500',
                    error: 'text-error-500',
                    warning: 'text-warning-500',
                    info: 'text-blue-light-500',
                })[toast.type]">
                <x-ui.icon-dyn expr="({ success: 'check', error: 'x', warning: 'bell', info: 'bell' })[toast.type]"
                    :names="['check', 'x', 'bell']" :size="20" />
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-heading" x-text="toast.message"></p>
            </div>
            <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                @click="$store.ui.dismissToast(toast.id)">
                <x-icon name="x" :size="16" />
            </button>
        </div>
    </template>
</div>
