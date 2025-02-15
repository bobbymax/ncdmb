<x-mail::message>
# Document Tracking Notification

The document with ref {{ $draft->document?->ref }} and title {{ $draft->document?->title }} is now at {{ $progressTracker->stage?->name }} Stage.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
