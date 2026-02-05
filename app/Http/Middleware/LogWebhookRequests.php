<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogWebhookRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (str_starts_with($request->path(), 'webhooks/')) {
            $logData = [
                'timestamp' => now()->toDateTimeString(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'path' => $request->path(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'headers' => $request->headers->all(),
                'query_params' => $request->query(),
                'body' => $request->all(),
                'raw_body' => $request->getContent(),
            ];

            // Log to custom webhook debug file
            $logMessage = "\n" . str_repeat('=', 80) . "\n";
            $logMessage .= "WEBHOOK REQUEST - " . $logData['timestamp'] . "\n";
            $logMessage .= str_repeat('=', 80) . "\n";
            $logMessage .= "Method: " . $logData['method'] . "\n";
            $logMessage .= "URL: " . $logData['url'] . "\n";
            $logMessage .= "Path: " . $logData['path'] . "\n";
            $logMessage .= "IP: " . $logData['ip'] . "\n";
            $logMessage .= "User Agent: " . $logData['user_agent'] . "\n";
            $logMessage .= "\nHeaders:\n" . json_encode($logData['headers'], JSON_PRETTY_PRINT) . "\n";
            $logMessage .= "\nQuery Params:\n" . json_encode($logData['query_params'], JSON_PRETTY_PRINT) . "\n";
            $logMessage .= "\nBody (Parsed):\n" . json_encode($logData['body'], JSON_PRETTY_PRINT) . "\n";
            $logMessage .= "\nRaw Body:\n" . $logData['raw_body'] . "\n";
            $logMessage .= str_repeat('=', 80) . "\n";

            file_put_contents(
                storage_path('logs/webhook_debug.log'),
                $logMessage,
                FILE_APPEND
            );
        }

        return $next($request);
    }
}
