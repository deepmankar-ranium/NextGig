<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status Update</title>
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

        <p>There's an update on your application for the <strong>{{ $application->jobListing->title }}</strong> position.</p>

        @if ($application->application_status === 'approved')
            <p><strong>Congratulations!</strong> Your application has been approved. The employer will be in touch with you soon regarding the next steps.</p>
        @elseif ($application->application_status === 'rejected')
            <p>We regret to inform you that the employer has decided not to move forward with your application at this time. We encourage you to keep applying for other positions.</p>
        @else
            <p>Your application status has been updated to: <strong>{{ ucfirst($application->application_status) }}</strong>.</p>
        @endif

        <p>Thank you for using NextGig!</p>

        <p>Best regards,<br>The NextGig Team</p>
    </div>
</body>
</html>
