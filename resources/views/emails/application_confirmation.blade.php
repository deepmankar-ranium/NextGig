<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Confirmation</title>
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
    </style>
</head>
<body>
    <div class="container">
        <p>Hi {{ $application->jobSeeker->user->name }},</p>

        <p>This email confirms that we have successfully received your application for the position of <strong>{{ $application->jobListing->title }}</strong>.</p>

        <p>The employer has been notified and will review your application. You will receive another email as soon as the status of your application is updated.</p>

        <p>Thank you for using NextGig!</p>

        <p>Best regards,<br>The NextGig Team</p>
    </div>
</body>
</html>
