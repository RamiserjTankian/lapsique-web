@props(['entry'])

@php
    $djs = collect($entry['djs'] ?? []);
    $isB2b = ($entry['type'] ?? 'single') === 'b2b' && $djs->count() === 2;
    $roleLabels = ['headliner' => 'Headliner', 'warmup' => 'Warm Up', 'local' => 'Local'];
    $roleLabel = $roleLabels[$entry['role'] ?? 'warmup'] ?? 'Warm Up';
    $instagramUrl = fn ($dj) => $dj?->instagram_handle
        ? 'https://instagram.com/' . ltrim($dj->instagram_handle, '@')
        : null;
    $soundcloudUrl = fn ($dj) => $dj?->soundcloud_url ?: null;

    if (! empty($entry['time_slot'])) {
        $roleLabel .= ' · ' . $entry['time_slot'];
    }

    $displayName = $isB2b
        ? $djs->pluck('name')->implode(' × ')
        : $djs->first()?->name;
@endphp

<div class="flex gap-5 sm:gap-6 items-start">
    @if ($isB2b)
        <div class="relative flex-shrink-0 w-24 h-24 sm:w-28 sm:h-28">
            <div class="relative h-full w-full overflow-hidden rounded-xl border border-white/70 bg-[var(--beige-200)] shadow-md">
                <div class="grid h-full w-full grid-cols-2">
                    @foreach ($djs as $dj)
                        @php
                            $djPhoto = $dj->getFirstMediaUrl('profile', 'thumb') ?: $dj->getFirstMediaUrl('profile');
                            $djInstagramUrl = $instagramUrl($dj);
                            if ($djPhoto && ! str_starts_with($djPhoto, 'http')) {
                                $djPhoto = url($djPhoto);
                            }
                        @endphp
                        <div class="relative h-full w-full overflow-hidden">
                            @if ($djPhoto)
                                @if ($djInstagramUrl)
                                    <a href="{{ $djInstagramUrl }}" target="_blank" rel="noopener noreferrer" class="block h-full w-full transition-opacity duration-200 hover:opacity-85">
                                        <img src="{{ $djPhoto }}" alt="{{ $dj->name }}" class="h-full w-full object-cover object-top scale-[0.9]" loading="lazy">
                                    </a>
                                @else
                                <img src="{{ $djPhoto }}" alt="{{ $dj->name }}" class="h-full w-full object-cover object-top scale-[0.9]" loading="lazy">
                                @endif
                            @else
                                <div class="flex h-full items-center justify-center bg-[#C8D8E4] px-2 text-center text-[11px] font-semibold uppercase tracking-[0.18em] text-[#1A2D3D]">
                                    {{ $dj->name }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="absolute inset-y-0 left-1/2 w-px -translate-x-1/2 bg-white/80"></div>
            </div>
        </div>
    @else
        @php
            $dj = $djs->first();
            $djPhoto = $dj?->getFirstMediaUrl('profile', 'thumb') ?: $dj?->getFirstMediaUrl('profile');
            $djInstagramUrl = $instagramUrl($dj);
            if ($djPhoto && ! str_starts_with($djPhoto, 'http')) {
                $djPhoto = url($djPhoto);
            }
        @endphp

        @if ($djPhoto)
            @if ($djInstagramUrl)
                <a href="{{ $djInstagramUrl }}" target="_blank" rel="noopener noreferrer" class="flex-shrink-0 w-24 h-24 sm:w-28 sm:h-28 rounded-xl overflow-hidden bg-[var(--beige-200)] shadow-md transition-transform duration-300 hover:scale-[1.02]">
                    <img src="{{ $djPhoto }}" alt="{{ $dj?->name }}" class="w-full h-full object-cover" loading="lazy">
                </a>
            @else
                <div class="flex-shrink-0 w-24 h-24 sm:w-28 sm:h-28 rounded-xl overflow-hidden bg-[var(--beige-200)] shadow-md">
                    <img src="{{ $djPhoto }}" alt="{{ $dj?->name }}" class="w-full h-full object-cover" loading="lazy">
                </div>
            @endif
        @endif
    @endif

    <div class="min-w-0 flex-1 pt-1">
        <p class="label-small text-[var(--marine-500)]">{{ $roleLabel }}</p>
        <h3 class="display font-semibold text-[#1A2D3D] mt-0.5 leading-tight" style="font-size: 1.4rem;">
            @if ($isB2b)
                @foreach ($djs as $dj)
                    @php
                        $djInstagramUrl = $instagramUrl($dj);
                    @endphp
                    @if ($djInstagramUrl)
                        <a href="{{ $djInstagramUrl }}" target="_blank" rel="noopener noreferrer" class="transition-colors duration-200 hover:text-[var(--marine-500)]">
                            {{ $dj->name }}
                        </a>
                    @else
                        <span>{{ $dj->name }}</span>
                    @endif
                    @if (! $loop->last)
                        <span class="text-[#6B7F8E]"> × </span>
                    @endif
                @endforeach
            @else
                @if (! empty($djInstagramUrl))
                    <a href="{{ $djInstagramUrl }}" target="_blank" rel="noopener noreferrer" class="transition-colors duration-200 hover:text-[var(--marine-500)]">
                        {{ $displayName }}
                    </a>
                @else
                    {{ $displayName }}
                @endif
            @endif
        </h3>

        @if ($isB2b)
            <div class="mt-2 space-y-1.5">
                @foreach ($djs as $dj)
                    @php
                        $djInstagramUrl = $instagramUrl($dj);
                        $djSoundcloudUrl = $soundcloudUrl($dj);
                    @endphp
                    @if ($djInstagramUrl || $djSoundcloudUrl)
                        <div class="flex items-center gap-2 text-[#6B7F8E]">
                            <span class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#1A2D3D]">{{ $dj->name }}</span>
                            @if ($djInstagramUrl)
                                <a href="{{ $djInstagramUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-[#D6E5ED] bg-white hover:border-[var(--marine-500)] hover:text-[var(--marine-500)] transition-colors" aria-label="Instagram de {{ $dj->name }}">
                                    <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 fill-current" aria-hidden="true">
                                        <path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2Zm0 1.5A4.25 4.25 0 0 0 3.5 7.75v8.5A4.25 4.25 0 0 0 7.75 20.5h8.5a4.25 4.25 0 0 0 4.25-4.25v-8.5A4.25 4.25 0 0 0 16.25 3.5h-8.5Zm8.875 1.125a1.125 1.125 0 1 1 0 2.25 1.125 1.125 0 0 1 0-2.25ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 1.5A3.5 3.5 0 1 0 12 15.5 3.5 3.5 0 0 0 12 8.5Z"/>
                                    </svg>
                                </a>
                            @endif
                            @if ($djSoundcloudUrl)
                                <a href="{{ $djSoundcloudUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-[#D6E5ED] bg-white hover:border-[#F26F23] hover:text-[#F26F23] transition-colors" aria-label="SoundCloud de {{ $dj->name }}">
                                    <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 fill-current" aria-hidden="true">
                                        <path d="M9.39 8.62a.75.75 0 0 1 .75.75v6.26a.75.75 0 0 1-1.5 0V9.37a.75.75 0 0 1 .75-.75Zm-2.48 1.5a.75.75 0 0 1 .75.75v4.76a.75.75 0 1 1-1.5 0v-4.76a.75.75 0 0 1 .75-.75Zm-2.48 1.24a.75.75 0 0 1 .75.75v3.52a.75.75 0 1 1-1.5 0v-3.52a.75.75 0 0 1 .75-.75Zm7.44-3.22a.75.75 0 0 1 .75.75v6.74a.75.75 0 1 1-1.5 0V8.89a.75.75 0 0 1 .75-.75Zm2.48.98a.75.75 0 0 1 .75.75v5.76a.75.75 0 0 1-1.5 0v-5.76a.75.75 0 0 1 .75-.75Zm2.48 1.74a3.68 3.68 0 0 1 .82 7.27H4.43a.75.75 0 0 1 0-1.5h12.4a2.18 2.18 0 1 0-.35-4.33 3.6 3.6 0 0 0-2.66-1.55.75.75 0 0 1-.67-.83 3.95 3.95 0 0 1 3.68-3.48 4.02 4.02 0 0 1 4.07 3.78.75.75 0 0 1-1.5.08 2.52 2.52 0 0 0-2.57-2.36 2.44 2.44 0 0 0-2.25 1.53 5.15 5.15 0 0 1 2.25.94Z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            @if (! empty($djInstagramUrl) || ! empty($dj?->soundcloud_url))
                <div class="mt-2 flex items-center gap-2 text-[#6B7F8E]">
                    @if (! empty($djInstagramUrl))
                        <a href="{{ $djInstagramUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-[#D6E5ED] bg-white hover:border-[var(--marine-500)] hover:text-[var(--marine-500)] transition-colors" aria-label="Instagram de {{ $dj?->name }}">
                            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 fill-current" aria-hidden="true">
                                <path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2Zm0 1.5A4.25 4.25 0 0 0 3.5 7.75v8.5A4.25 4.25 0 0 0 7.75 20.5h8.5a4.25 4.25 0 0 0 4.25-4.25v-8.5A4.25 4.25 0 0 0 16.25 3.5h-8.5Zm8.875 1.125a1.125 1.125 0 1 1 0 2.25 1.125 1.125 0 0 1 0-2.25ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 1.5A3.5 3.5 0 1 0 12 15.5 3.5 3.5 0 0 0 12 8.5Z"/>
                            </svg>
                        </a>
                    @endif
                    @if (! empty($dj?->soundcloud_url))
                        <a href="{{ $dj?->soundcloud_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-[#D6E5ED] bg-white hover:border-[#F26F23] hover:text-[#F26F23] transition-colors" aria-label="SoundCloud de {{ $dj?->name }}">
                            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 fill-current" aria-hidden="true">
                                <path d="M9.39 8.62a.75.75 0 0 1 .75.75v6.26a.75.75 0 0 1-1.5 0V9.37a.75.75 0 0 1 .75-.75Zm-2.48 1.5a.75.75 0 0 1 .75.75v4.76a.75.75 0 1 1-1.5 0v-4.76a.75.75 0 0 1 .75-.75Zm-2.48 1.24a.75.75 0 0 1 .75.75v3.52a.75.75 0 1 1-1.5 0v-3.52a.75.75 0 0 1 .75-.75Zm7.44-3.22a.75.75 0 0 1 .75.75v6.74a.75.75 0 1 1-1.5 0V8.89a.75.75 0 0 1 .75-.75Zm2.48.98a.75.75 0 0 1 .75.75v5.76a.75.75 0 0 1-1.5 0v-5.76a.75.75 0 0 1 .75-.75Zm2.48 1.74a3.68 3.68 0 0 1 .82 7.27H4.43a.75.75 0 0 1 0-1.5h12.4a2.18 2.18 0 1 0-.35-4.33 3.6 3.6 0 0 0-2.66-1.55.75.75 0 0 1-.67-.83 3.95 3.95 0 0 1 3.68-3.48 4.02 4.02 0 0 1 4.07 3.78.75.75 0 0 1-1.5.08 2.52 2.52 0 0 0-2.57-2.36 2.44 2.44 0 0 0-2.25 1.53 5.15 5.15 0 0 1 2.25.94Z"/>
                            </svg>
                        </a>
                    @endif
                </div>
            @endif
        @endif
    </div>
</div>
