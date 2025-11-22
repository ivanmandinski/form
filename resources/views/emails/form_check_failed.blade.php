<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .details { background: #f8f9fa; padding: 15px; border-radius: 5px; }
        .label { font-weight: bold; }
        .error { color: #dc3545; font-family: monospace; background: #fff; padding: 10px; border: 1px solid #dee2e6; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Form Check Failed</h2>
            <p>A monitored form has failed its check.</p>
        </div>

        <div class="details">
            <p><span class="label">Target URL:</span> <a href="{{ $checkRun->formTarget->target->url }}">{{ $checkRun->formTarget->target->url }}</a></p>
            <p><span class="label">Form Selector:</span> {{ $checkRun->formTarget->selector_value }}</p>
            <p><span class="label">Time:</span> {{ $checkRun->created_at->format('Y-m-d H:i:s T') }}</p>
            <p><span class="label">Driver:</span> {{ $checkRun->driver }}</p>
            
            <h3>Error Details:</h3>
            <div class="error">
                {{ $checkRun->message ?? 'No specific error message provided.' }}
            </div>
            
            @if($checkRun->result_data && isset($checkRun->result_data['error_detail']))
                <h3>Debug Info:</h3>
                <pre class="error">{{ json_encode($checkRun->result_data['error_detail'], JSON_PRETTY_PRINT) }}</pre>
            @endif
        </div>
        
        <p style="margin-top: 20px; font-size: 0.9em; color: #666;">
            This is an automated notification from your Form Monitor system.
        </p>
    </div>
</body>
</html>
