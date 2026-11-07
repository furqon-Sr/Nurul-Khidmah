@props(['type' => 'neutral'])
{{-- Chip status (style guide). type: success | warning | error | info | purple | neutral | muted | solid-success | solid-orange | solid-info --}}
<span {{ $attributes->class(['chip', 'chip-'.$type]) }}>{{ $slot }}</span>
