@php
    use Illuminate\Support\Str;
    use Marcelodelgado\Announcements\Enums\AnnouncementType;

    $pollingInterval = $this->getPollingInterval();
    $announcements   = $this->getAnnouncements();
@endphp

<x-filament-widgets::widget
    :attributes="
        (new \Illuminate\View\ComponentAttributeBag)
            ->merge([
                'wire:poll.' . $pollingInterval => $pollingInterval ? true : null,
            ], escape: false)
            ->class(['fi-wi-announcements'])
    "
>

    {{-- ── OUTER CARD ───────────────────────────────────────────────────── --}}
    <x-filament::section
        heading="Announcements"
        icon="heroicon-o-megaphone"
        collapsible
    >

        @if ($announcements->isEmpty())

            {{-- ── Empty state ─────────────────────────────────────────── --}}
            <div
                style="
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    gap: 0.5rem;
                    padding-block: 2rem;
                    text-align: center;
                "
            >
                <x-filament::icon
                    icon="heroicon-o-megaphone"
                    style="
                        width: 2.25rem;
                        height: 2.25rem;
                        color: light-dark(#9ca3af, #6b7280);
                    "
                />

                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ __('announcements::widget.empty') }}
                </p>

                <p class="text-xs text-gray-400 dark:text-gray-500">
                    No active announcements at the moment.
                </p>
            </div>

        @else

            {{-- ── Header summary ─────────────────────────────────────── --}}
            @if ($announcements->count() > 1)
                <div
                    style="
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                        margin-bottom: 1rem;
                    "
                >
                    <span
                        class="
                            inline-flex
                            items-center
                            rounded-full
                            bg-primary-50
                            px-2.5
                            py-1
                            text-xs
                            font-medium
                            text-primary-700
                            dark:bg-primary-500/10
                            dark:text-primary-300
                        "
                    >
                        {{ $announcements->count() }}
                        active
                        {{ str('announcement')->plural($announcements->count()) }}
                    </span>
                </div>
            @endif

            {{-- ── Announcement list ─────────────────────────────────── --}}
            <ul
                style="
                    margin: 0;
                    padding: 0;
                    list-style: none;
                    display: flex;
                    flex-direction: column;
                    gap: 0.875rem;
                "
            >
                @foreach ($announcements as $announcement)

                    @php
                        // ── Type styles ─────────────────────────────────
                        $borderClass = match ($announcement->type) {
                            AnnouncementType::Danger  => 'border-danger-500 dark:border-danger-400',
                            AnnouncementType::Warning => 'border-warning-500 dark:border-warning-400',
                            AnnouncementType::Info    => 'border-info-500 dark:border-info-400',
                            AnnouncementType::Success => 'border-success-500 dark:border-success-400',
                        };

                        $bgClass = match ($announcement->type) {
                            AnnouncementType::Danger  => 'bg-danger-50/80 dark:bg-danger-950/20',
                            AnnouncementType::Warning => 'bg-warning-50/80 dark:bg-warning-950/20',
                            AnnouncementType::Info    => 'bg-info-50/80 dark:bg-info-950/20',
                            AnnouncementType::Success => 'bg-success-50/80 dark:bg-success-950/20',
                        };

                        $icon = match ($announcement->type) {
                            AnnouncementType::Danger  => 'heroicon-s-exclamation-circle',
                            AnnouncementType::Warning => 'heroicon-s-exclamation-triangle',
                            AnnouncementType::Info    => 'heroicon-s-information-circle',
                            AnnouncementType::Success => 'heroicon-s-check-circle',
                        };

                        $iconColor = match ($announcement->type) {
                            AnnouncementType::Danger  => 'light-dark(#dc2626, #f87171)',
                            AnnouncementType::Warning => 'light-dark(#d97706, #fbbf24)',
                            AnnouncementType::Info    => 'light-dark(#0284c7, #38bdf8)',
                            AnnouncementType::Success => 'light-dark(#16a34a, #4ade80)',
                        };

                        $badgeStyle = match ($announcement->type) {
                            AnnouncementType::Danger  => 'background: light-dark(#fee2e2, #450a0a); color: light-dark(#b91c1c, #fca5a5);',
                            AnnouncementType::Warning => 'background: light-dark(#fef3c7, #451a03); color: light-dark(#92400e, #fcd34d);',
                            AnnouncementType::Info    => 'background: light-dark(#e0f2fe, #082f49); color: light-dark(#0369a1, #7dd3fc);',
                            AnnouncementType::Success => 'background: light-dark(#dcfce7, #052e16); color: light-dark(#15803d, #86efac);',
                        };

                        $typeLabel = match ($announcement->type) {
                            AnnouncementType::Danger  => 'Critical',
                            AnnouncementType::Warning => 'Warning',
                            AnnouncementType::Info    => 'Info',
                            AnnouncementType::Success => 'Update',
                        };

                        $isLongBody = Str::length($announcement->body) > 220;
                    @endphp

                    {{-- ── Announcement Card ─────────────────────────── --}}
                    <li
                        wire:key="announcement-{{ $announcement->id }}"
                        x-data="{
                            expanded: false,
                            visible: true,
                            shouldCollapse: {{ $isLongBody ? 'true' : 'false' }},

                            dismiss() {
                                this.visible = false

                                setTimeout(() => {
                                    $wire.dismiss({{ $announcement->id }})
                                }, 250)
                            }
                        }"
                        x-show="visible"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-250"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-3 scale-[0.98]"
                        class="
                            overflow-hidden
                            rounded-2xl
                            border-gray-200/70
                            dark:border-white/10
                            shadow-sm
                            backdrop-blur-sm
                            transition-all
                            duration-300
                            hover:shadow-lg
                            hover:-translate-y-0.5
                            {{ $bgClass }}
                        "
                        style="list-style: none;"
                    >
                        <div
                            class="border-s-4 {{ $borderClass }}"
                            style="
                                display: flex;
                                align-items: flex-start;
                                gap: 0.875rem;
                                padding: 1rem;
                            "
                        >

                            {{-- Icon --}}
                            <div
                                style="
                                    flex-shrink: 0;
                                    padding-top: 0.1rem;
                                    line-height: 0;
                                "
                            >
                                <x-filament::icon
                                    :icon="$icon"
                                    style="
                                        width: 1.35rem;
                                        height: 1.35rem;
                                        color: {{ $iconColor }};
                                    "
                                />
                            </div>

                            {{-- Content --}}
                            <div style="min-width: 0; flex: 1 1 0%;">

                                {{-- Header --}}
                                <div
                                    style="
                                        display: flex;
                                        align-items: center;
                                        flex-wrap: wrap;
                                        gap: 0.5rem;
                                        margin-bottom: 0.35rem;
                                    "
                                >
                                    <span
                                        style="
                                            display: inline-flex;
                                            align-items: center;
                                            border-radius: 9999px;
                                            padding: 0.2rem 0.55rem;
                                            font-size: 0.625rem;
                                            font-weight: 700;
                                            letter-spacing: 0.08em;
                                            text-transform: uppercase;
                                            {{ $badgeStyle }}
                                        "
                                    >
                                        {{ $typeLabel }}
                                    </span>

                                    <h3
                                        style="
                                            margin: 0;
                                            font-size: 0.975rem;
                                            font-weight: 700;
                                            line-height: 1.35;
                                            color: light-dark(#030712, #f9fafb);
                                            word-break: break-word;
                                        "
                                    >
                                        {{ $announcement->title }}
                                    </h3>
                                </div>

                                {{-- Body --}}
                                <div
                                    class="text-sm text-gray-600 dark:text-gray-300"
                                    style="
                                        line-height: 1.7;
                                        word-break: break-word;
                                    "
                                >
                                    <div
                                        x-bind:style="
                                            !expanded && shouldCollapse
                                                ? `
                                                    display: -webkit-box;
                                                    -webkit-line-clamp: 3;
                                                    -webkit-box-orient: vertical;
                                                    overflow: hidden;
                                                `
                                                : ''
                                        "
                                        class="transition-all duration-300"
                                    >
                                        <p
                                            class="whitespace-pre-wrap"
                                            style="margin: 0;"
                                        >
                                            {{ $announcement->body }}
                                        </p>
                                    </div>

                                    {{-- Toggle --}}
                                    <button
                                        x-show="shouldCollapse"
                                        x-on:click="expanded = !expanded"
                                        type="button"
                                        class="
                                            mt-2
                                            inline-flex
                                            items-center
                                            gap-1
                                            text-xs
                                            font-medium
                                            text-primary-600
                                            hover:text-primary-500
                                            dark:text-primary-400
                                            dark:hover:text-primary-300
                                            transition-colors
                                        "
                                    >
                                        <span
                                            x-text="
                                                expanded
                                                    ? 'Show less'
                                                    : 'Show more'
                                            "
                                        ></span>

                                        <x-filament::icon
                                            icon="heroicon-m-chevron-down"
                                            style="
                                                width: 0.8rem;
                                                height: 0.8rem;
                                            "
                                            x-bind:class="
                                                expanded
                                                    ? 'rotate-180'
                                                    : ''
                                            "
                                        />
                                    </button>
                                </div>

                                {{-- Footer --}}
                                @if (!empty($announcement->created_at))
                                    <div
                                        style="
                                            display: flex;
                                            align-items: center;
                                            gap: 0.35rem;
                                            margin-top: 0.65rem;
                                        "
                                    >
                                        <x-filament::icon
                                            icon="heroicon-m-clock"
                                            style="
                                                width: 0.8rem;
                                                height: 0.8rem;
                                                color: light-dark(#9ca3af, #6b7280);
                                            "
                                        />

                                        <p
                                            style="
                                                margin: 0;
                                                font-size: 0.72rem;
                                                color: light-dark(#9ca3af, #6b7280);
                                            "
                                        >
                                            {{ $announcement->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            {{-- Dismiss --}}
                            @if ($announcement->is_dismissible)
                                <div
                                    style="
                                        flex-shrink: 0;
                                        align-self: flex-start;
                                    "
                                >
                                    <x-filament::icon-button
                                        color="gray"
                                        icon="heroicon-o-x-mark"
                                        size="sm"
                                        :label="__('announcements::widget.dismiss')"
                                        x-on:click="dismiss()"
                                    />
                                </div>
                            @endif

                        </div>
                    </li>

                @endforeach
            </ul>

        @endif

    </x-filament::section>

</x-filament-widgets::widget>
