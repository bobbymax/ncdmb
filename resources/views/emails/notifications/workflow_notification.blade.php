<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Workflow Notification</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        img {
            width: 100%;
        }

        .mail-wrapper {
            display: flex;
            margin: 0 auto;
            max-width: 65%;
            justify-content: flex-start;
            align-items: flex-start;
            height: auto;
            flex-direction: column;
            text-wrap: wrap;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div id="mail-wrapper">
        <div class="jumbotron">
            <small>Desk Update!!</small>
            <h2>{{ $document->ref }}</h2>
            <small class="status-badge">{{ $documentAction->action_status }}</small>
        </div>
        <div class="action-report">
            Updated by: {{ $user->firstname . " " . $user->surname }} on {{ $document->updated_at->format('d F, Y') }}
        </div>
        <div class="message">
            <p>The {{ $lastDraft->documentType->name }} draft has been sent to {{ $progressTracker->stage->name }} in {{ $progressTracker->department->abv }}, only staff in {{ $progressTracker->group->name }} group can handle this document.</p>
        </div>
    </div>
</body>
</html>
