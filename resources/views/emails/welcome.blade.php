<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to NextGig</title>
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
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff !important;
            text-decoration: none;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Welcome to NextGig!</div>

        <p>Hi {{ $user->name }},</p>

        @if($user->role->name === 'Job Seeker')
            <p>Thank you for joining NextGig, your new platform for discovering exciting job opportunities and connecting with top employers.</p>

            <p>To get started, we recommend you:</p>

            <ul>
                <li><a href="{{ url('/profile') }}">Complete your profile</a> to attract the best opportunities.</li>
                <li><a href="{{ url('/jobs') }}">Start browsing jobs</a> to find your perfect match.</li>
            </ul>

            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ url('/jobs') }}" class="button">Browse Jobs Now</a>
            </div>

        @elseif($user->role->name === 'Employer')
            <p>Thank you for joining NextGig, the best place to find qualified candidates for your open positions.</p>

            <p>To get started, we recommend you:</p>

            <ul>
                <li><a href="{{ url('/employer/profile') }}">Complete your company profile</a> to attract top talent.</li>
                <li><a href="{{ url('/jobs/create') }}">Post a new job</a> and start receiving applications.</li>
            </ul>

            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ url('/jobs/create') }}" class="button">Post a Job Now</a>
            </div>

        @else
             <p>Thank you for joining NextGig!</p>
        @endif

        <p>We're excited to have you on board!</p>

        <p>Best regards,<br>The NextGig Team</p>

    </div>
</body>
</html>
