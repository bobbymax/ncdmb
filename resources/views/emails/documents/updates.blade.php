<x-mail::message>
# Document Update!!

{{ $user->firstname . " " . $user->surname }} has just made an update on your document with Reference No.: {{ $document->ref }}. The document status has now been updated to {{ $documentAction->label }} with the message {{ $documentUpdate->comment }}.

<x-mail::button :url="'http://localhost:3000/documents/{{ $document->ref }}'">
View Document
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
