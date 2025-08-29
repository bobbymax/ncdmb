
@component('mail::message')
    # {{ $title }}

    Hello {{ $recipientName }},

    {{ $summary }}

    @isset($payload['context']['url'])
        @component('mail::button', ['url' => $payload['context']['url']])
            Open {{ ucfirst($payload['service'] ?? 'document') }}
        @endcomponent
    @endisset

    @component('mail::panel')
        **Category:** {{ $documentCategory }}
        **Action:** {{ ucfirst($payload['action'] ?? '') }}
        **Reference:** {{ $payload['context']['documentRef'] ?? ($payload['resource']['type'].' #'.$payload['resource']['id']) }}
    @endcomponent

    Thanks,
    {{ config('app.name') }}
@endcomponent
