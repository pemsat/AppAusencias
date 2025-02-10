<!DOCTYPE html>
<html>
<head>
    <title>New Absence Created</title>
</head>
<body>
    <h1>New Absence Notification</h1>
    <p>A new absence has been registered.</p>

    <p><strong>Professor:</strong> {{ $absence->user->name ?? 'Unknown' }}</p>
    <p><strong>Reason:</strong> {{ $absence->comment ?? 'No comment provided' }}</p>
    <p><strong>Date:</strong> {{ $absence->starts_at ? $absence->starts_at->format('Y-m-d') : 'N/A' }}</p>
    <p><strong>Time:</strong> {{ $absence->starts_at ? $absence->starts_at->format('H:i') : 'N/A' }} -
        {{ $absence->ends_at ? $absence->ends_at->format('H:i') : 'N/A' }}</p>

</body>
</html>


