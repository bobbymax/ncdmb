<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Workflow Notification</title>
</head>
<body>
    <h2>Workflow Update</h2>
    <p> {{ $user->firstname . " " . $user->surname }} has just performed an action "{{ $documentAction->name  }}" on the {{ $document->title }} document with ref {{ $document->ref }}.</p>
</body>
</html>
