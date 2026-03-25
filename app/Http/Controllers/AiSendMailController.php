<?php

namespace App\Http\Controllers;

use App\Ai\Agents\SendMailAgent;
use Illuminate\Http\Request;

class AiSendMailController extends Controller
{
    /**
     * Send mail using the AI agent from a natural-language prompt.
     * Example: "Send an email to john@example.com saying we'll meet tomorrow at 3pm"
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:4000',
        ]);

        $prompt = $request->input('prompt');

        try {
            $response = (new SendMailAgent)->prompt($prompt);

            return response()->json([
                'success' => true,
                'message' => (string) $response,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process request: '.$e->getMessage(),
            ], 500);
        }
    }
}
