@persist('toast')
    <flux:toast />
@endpersist

@if (session()->has('success') || session()->has('status') || session()->has('error'))
    <div x-data x-init="$nextTick(() => {
        @if (session()->has('success') || session()->has('status'))
            Flux.toast({
                heading: 'Success',
                text: '{{ addslashes(session('success') ?? session('status')) }}',
                variant: 'success',
            });
        @endif
        @if (session()->has('error'))
            Flux.toast({
                heading: 'Error',
                text: '{{ addslashes(session('error')) }}',
                variant: 'danger',
            });
        @endif
    })"></div>
@endif
