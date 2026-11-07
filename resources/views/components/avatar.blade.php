@props(['src' => null, 'name' => '', 'size' => 'md', 'tone' => 'primary'])
@php
    $words = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);
    $initials = mb_strtoupper(mb_substr($words[0] ?? '', 0, 1).(count($words) > 1 ? mb_substr(end($words), 0, 1) : mb_substr($words[0] ?? '', 1, 1)));
    $sizeClass = ['xs' => 'avatar-xs', 'sm' => 'avatar-sm', 'md' => '', 'lg' => 'avatar-lg', 'xl' => 'avatar-xl'][$size] ?? '';
@endphp
{{-- Avatar (style guide): foto bila ada, jika tidak inisial dengan latar tint. tone: primary | secondary | error | neutral --}}
<span {{ $attributes->class(['avatar', $sizeClass, 'avatar-'.$tone => ! $src]) }}>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $name }}" loading="lazy">
    @else
        {{ $initials ?: '?' }}
    @endif
</span>
