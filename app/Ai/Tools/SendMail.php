<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Mail;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SendMail implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Send an email to a recipient. Use this when the user wants to send or compose an email. Requires a valid recipient email address, subject, and body.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $to = $request['to'] ?? '';
        $subject = $request['subject'] ?? '(No subject)';
        $body = $request['body'] ?? '';

        $to = is_string($to) ? trim($to) : '';
        if ($to === '' || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return 'Error: A valid recipient email address is required.';
        }

        $subject = is_string($subject) ? trim($subject) : '(No subject)';
        $body = is_string($body) ? trim($body) : '';

        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });

            return "Email sent successfully to {$to} with subject: {$subject}";
        } catch (\Throwable $e) {
            return 'Failed to send email: '.$e->getMessage();
        }
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'to' => $schema->string()->required(),
            'subject' => $schema->string()->required(),
            'body' => $schema->string()->required(),
        ];
    }
}
