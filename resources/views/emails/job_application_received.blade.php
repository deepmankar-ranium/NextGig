<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Job Application</title>
    <style>
        body {
            font-family: sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">New Application Received</div>

        <p>Hello {{ $application->jobListing->employer->user->name }},</p>

        <p>You have received a new application for your job listing: <strong>{{ $application->jobListing->title }}</strong> from {{ $application->jobSeeker->user->name }}.</p>

        <p>You can review the full application by visiting your dashboard.</p>

        <p>Best regards,<br>The NextGig Team</p>
    </div>
</body>
</html>
