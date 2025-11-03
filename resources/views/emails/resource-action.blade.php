<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $resourceType }} {{ $action }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #137547 0%, #0d5233 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2d3748;
        }
        .message {
            margin-bottom: 20px;
            line-height: 1.8;
        }
        .details {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .details h3 {
            margin: 0 0 15px 0;
            color: #137547;
            font-size: 16px;
        }
        .details-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .details-row:last-child {
            border-bottom: none;
        }
        .details-label {
            font-weight: 600;
            color: #6b7280;
            min-width: 140px;
        }
        .details-value {
            color: #2d3748;
            flex: 1;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #137547;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .button:hover {
            background: #0d5233;
        }
        .footer {
            background: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $resourceType }} {{ $action }}</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                Hello {{ $userName }},
            </div>
            
            <div class="message">
                <p>A <strong>{{ $resourceType }}</strong> has been <strong>{{ strtolower($action) }}</strong> and requires your attention.</p>
            </div>
            
            <div class="details">
                <h3>Details:</h3>
                @foreach($resourceData as $key => $value)
                    @if(!is_array($value) && !is_object($value))
                    <div class="details-row">
                        <div class="details-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</div>
                        <div class="details-value">{{ $value }}</div>
                    </div>
                    @endif
                @endforeach
                
                @if(!empty($metadata))
                    @foreach($metadata as $key => $value)
                        @if(!is_array($value) && !is_object($value))
                        <div class="details-row">
                            <div class="details-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</div>
                            <div class="details-value">{{ $value }}</div>
                        </div>
                        @endif
                    @endforeach
                @endif
            </div>
            
            <div class="button-container">
                <a href="{{ $resourceUrl }}" class="button">View {{ $resourceType }}</a>
            </div>
            
            <div class="message">
                <p>Please log in to the portal to take action on this {{ strtolower($resourceType) }}.</p>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>This is an automated notification. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>

