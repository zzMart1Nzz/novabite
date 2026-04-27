@props(['status'])

@if ($status)
    <div x-data x-init="$dispatch('toaster:received', { type: 'success', message: {{ \Illuminate\Support\Js::from($status) }} })"></div>
@endif
