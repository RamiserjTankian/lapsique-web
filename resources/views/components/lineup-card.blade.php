@props([
    'entry',
    'tagConfig' => [],
    'badgeLabel' => '',
    'badgeClass' => 'pill border-white text-white bg-black/50 backdrop-blur text-xs',
    'highlight' => false,
])

@php
    $djs = collect($entry['djs'] ?? []);
    $isB2b = ($entry['type'] ?? 'single') === 'b2b' && $djs->count() === 2;
    $primaryDj = $djs->first();
    $displayName = $djs->pluck('name')->implode(' b2b ');
    $handles = $djs->pluck('instagram_handle')->filter()->map(fn ($handle) => '@' . $handle)->implode(' · ');
    $timeSlot = $entry['time_slot'] ?? null;
    $tags = $djs
        ->flatMap(fn ($dj) => $dj->tags ?? [])
        ->unique()
        ->take(3)
        ->values();
    $href = ! $isB2b && $primaryDj && Route::has('djs.show')
        ? route('djs.show', $primaryDj)
        : null;
    $wrapperTag = $href ? 'a' : 'div';
    $cardClass = 'card card-animated overflow-hidden group relative';

    if ($highlight) {
        $cardClass .= ' shadow-lg shadow-yellow-500/20 hover:shadow-yellow-500/40 transition-shadow duration-300';
    }
@endphp

<{{ $wrapperTag }}
    @if ($href)
        href="{{ $href }}"
    @endif
    class="{{ $cardClass }}"
>
    @if ($highlight)
        <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/10 via-orange-500/10 to-yellow-500/10 rounded-lg blur-xl"></div>
    @endif

    <div class="relative h-64 w-full bg-gradient-to-br from-black to-zinc-900">
        @if ($isB2b)
            <div class="grid h-full w-full grid-cols-2">
                @foreach ($djs as $dj)
                    @php
                        $profile = $dj->getFirstMediaUrl('profile', 'card') ?: $dj->getFirstMediaUrl('profile', 'thumb') ?: $dj->getFirstMediaUrl('profile');
                    @endphp
                    <div class="relative h-full w-full overflow-hidden">
                        @if ($profile)
                            <img src="{{ $profile }}" alt="{{ $dj->name }}" class="h-full w-full object-cover object-top scale-[0.9] transition duration-300 group-hover:scale-[0.94]">
                        @else
                            <div class="flex h-full items-center justify-center bg-gradient-to-br from-zinc-900 to-black px-4 text-center text-sm font-semibold tracking-[0.2em] text-white/70">
                                {{ $dj->name }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="absolute inset-y-0 left-1/2 w-px -translate-x-1/2 bg-white/20"></div>
        @elseif ($primaryDj)
            @php
                $profile = $primaryDj->getFirstMediaUrl('profile', 'card') ?: $primaryDj->getFirstMediaUrl('profile', 'thumb') ?: $primaryDj->getFirstMediaUrl('profile');
            @endphp
            @if ($profile)
                <img src="{{ $profile }}" alt="{{ $primaryDj->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
            @endif
        @endif

        @if ($tags->isNotEmpty())
            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                @foreach ($tags as $tag)
                    @php
                        $config = $tagConfig[$tag] ?? ['emoji' => '', 'label' => strtoupper($tag), 'class' => 'bg-white/90 text-black border-white'];
                    @endphp
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wider border backdrop-blur-sm {{ $config['class'] }} shadow-lg">
                        @if ($config['emoji'])
                            <span>{{ $config['emoji'] }}</span>
                        @endif
                        <span>{{ $config['label'] }}</span>
                    </span>
                @endforeach
            </div>
        @endif

        @if ($badgeLabel)
            <div class="absolute top-3 right-3">
                <span class="{{ $badgeClass }}">{{ $badgeLabel }}</span>
            </div>
        @endif
    </div>

    <div class="relative px-4 py-3 space-y-1.5">
        <h3 class="text-base font-bold text-white">{{ $displayName }}</h3>
        @if ($timeSlot)
            <div class="flex items-center gap-1.5">
                <span class="text-xs">🕒</span>
                <p class="text-xs font-medium text-gray-300">{{ $timeSlot }}</p>
            </div>
        @endif
        @if ($handles)
            <p class="text-xs uppercase tracking-[0.18em] text-gray-400">{{ $handles }}</p>
        @endif
    </div>
</{{ $wrapperTag }}>
