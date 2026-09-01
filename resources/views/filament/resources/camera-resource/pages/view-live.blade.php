<x-filament-panels::page>
    @php($currentUrl = $playbackUrl ?? $liveUrl)

    <div class="flex flex-col gap-4">
        {{-- Player --}}
        <div class="w-full">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        @if ($playbackUrl)
                            <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5 text-info-500" />
                            <span>Архив</span>
                        @else
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-rose-500"></span>
                            </span>
                            <span>Live</span>
                        @endif
                    </div>
                </x-slot>

                @if ($error)
                    <div class="rounded-lg bg-danger-50 p-4 text-sm text-danger-700 ring-1 ring-danger-200 dark:bg-danger-500/10 dark:text-danger-400 dark:ring-danger-500/30">
                        <div class="flex items-start gap-2">
                            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5 flex-shrink-0" />
                            <div>
                                <div class="font-medium">Ошибка</div>
                                <div class="mt-1 break-all">{{ $error }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($currentUrl)
                    {{-- aspect-video нет в собранном CSS Filament: без пропорции высота блока нулевая. --}}
                    <div
                        class="relative w-full overflow-hidden rounded-lg bg-black ring-1 ring-gray-950/5 dark:ring-white/10"
                        style="aspect-ratio: 16 / 9; max-height: 70vh; max-width: calc(70vh * 16 / 9); margin-inline: auto;"
                    >
                        <div id="ezviz-player" class="absolute inset-0 h-full w-full"></div>
                    </div>

                    @if ($playbackUrl)
                        <div class="mt-3 flex justify-end">
                            <x-filament::button
                                size="sm"
                                color="gray"
                                icon="heroicon-m-arrow-left"
                                wire:click="backToLive"
                            >
                                Вернуться к Live
                            </x-filament::button>
                        </div>
                    @endif
                @else
                    <div
                        class="flex w-full items-center justify-center rounded-lg bg-gray-100 ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
                        style="aspect-ratio: 16 / 9; max-height: 70vh; max-width: calc(70vh * 16 / 9); margin-inline: auto;"
                    >
                        <div class="flex flex-col items-center gap-2 text-gray-500">
                            <x-filament::icon icon="heroicon-o-video-camera-slash" class="h-12 w-12" />
                            <span class="text-sm">Поток недоступен</span>
                        </div>
                    </div>
                @endif
            </x-filament::section>
        </div>

        {{-- Sidebar: archive + info --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5 text-primary-500" />
                        <span>Архив</span>
                    </div>
                </x-slot>
                <x-slot name="description">
                    Запись из облака Ezviz за выбранный период
                </x-slot>

                <form wire:submit="loadPlayback" class="space-y-4">
                    {{ $this->form }}

                    <div class="flex gap-2">
                        <x-filament::button
                            type="submit"
                            icon="heroicon-m-play"
                            class="w-full"
                        >
                            Воспроизвести
                        </x-filament::button>
                    </div>
                </form>

                <div class="mt-3 grid gap-2" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
                    <x-filament::button
                        size="xs"
                        color="gray"
                        wire:click="$set('data.from', '{{ now()->subHour()->format('Y-m-d H:i:s') }}')"
                    >
                        −1ч
                    </x-filament::button>
                    <x-filament::button
                        size="xs"
                        color="gray"
                        wire:click="$set('data.from', '{{ now()->subHours(6)->format('Y-m-d H:i:s') }}')"
                    >
                        −6ч
                    </x-filament::button>
                    <x-filament::button
                        size="xs"
                        color="gray"
                        wire:click="$set('data.from', '{{ now()->subDay()->format('Y-m-d H:i:s') }}')"
                    >
                        −24ч
                    </x-filament::button>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-information-circle" class="h-5 w-5 text-gray-500" />
                        <span>Информация</span>
                    </div>
                </x-slot>

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">Serial</dt>
                        <dd class="font-mono text-gray-950 dark:text-white">{{ $record->serial }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">Канал</dt>
                        <dd class="text-gray-950 dark:text-white">{{ $record->channel_no }}</dd>
                    </div>
                    @if ($record->fridge)
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500">Микромаркет</dt>
                            <dd class="text-gray-950 dark:text-white">{{ $record->fridge->name }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">Статус</dt>
                        <dd>
                            @if ($record->is_active)
                                <span class="inline-flex items-center gap-1 rounded-md bg-success-50 px-2 py-0.5 text-xs font-medium text-success-700 ring-1 ring-success-600/20 dark:bg-success-500/10 dark:text-success-400">
                                    <x-filament::icon icon="heroicon-m-bolt" class="h-3 w-3" />
                                    Активна
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    Отключена
                                </span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </x-filament::section>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/ezuikit-js@7.7.6/ezuikit.js"></script>

    <script>
        (function () {
            let player = null;
            let currentUrl = @json($currentUrl);
            let accessToken = @json($accessToken);
            let ezvizDomain = @json($ezvizDomain);

            function waitForKit(cb, tries = 50) {
                if (window.EZUIKit) return cb();
                if (tries <= 0) {
                    console.error('[ezviz] EZUIKit не загрузился');
                    return;
                }
                setTimeout(() => waitForKit(cb, tries - 1), 100);
            }

            function attach(url) {
                const container = document.getElementById('ezviz-player');
                if (!container || !url || !accessToken) return;

                console.log('[ezviz] attach', url);

                waitForKit(() => {
                    if (player) {
                        try { player.stop(); } catch (e) {}
                        container.innerHTML = '';
                        player = null;
                    }

                    const rect = container.getBoundingClientRect();
                    const width = Math.round(rect.width) || 640;
                    // Пропорцию держим сами: жёсткие 360 растягивали картинку на широком экране.
                    const height = Math.round(rect.height) || Math.round(width * 9 / 16);

                    player = new window.EZUIKit.EZUIKitPlayer({
                        id: 'ezviz-player',
                        accessToken: accessToken,
                        url: url,
                        template: 'simple',
                        env: { domain: ezvizDomain },
                        width: width,
                        height: height,
                    });
                });
            }

            function init() {
                if (currentUrl) attach(currentUrl);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }

            window.addEventListener('player-source-changed', (e) => {
                const url = e?.detail?.url ?? e?.detail?.[0]?.url;
                if (url) attach(url);
            });

            if (window.Livewire) {
                window.Livewire.on('player-source-changed', (payload) => {
                    const url = Array.isArray(payload) ? payload[0]?.url : payload?.url;
                    if (url) attach(url);
                });
            }
        })();
    </script>
</x-filament-panels::page>
