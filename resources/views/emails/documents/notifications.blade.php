{{-- resources/views/mail/document/dynamic.blade.php --}}
@component('mail::message')
    @foreach($lines as $line)
        - {!! $line !!}
    @endforeach

    @component('mail::button', ['url' => $ctaUrl])
        Open Document
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
