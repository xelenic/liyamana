<?php

namespace App\Http\Controllers;

use App\Models\DesignerApplication;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class DesignerApplicationController extends Controller
{
    /**
     * Show the designer application form (multi-step)
     */
    public function index()
    {
        $user = auth()->user();
        $isApprovedDesigner = $user && $user->hasRole(['admin', 'designer']);
        $pendingApplication = $user ? DesignerApplication::where('user_id', $user->id)->where('status', 'pending')->first() : null;

        return view('designer-application.index', compact('isApprovedDesigner', 'pendingApplication'));
    }

    /**
     * Generate design experience description using OpenAI
     */
    public function generateExperience(Request $request)
    {
        try {
            $validated = $request->validate([
                'skills' => 'required|string|max:1000',
            ]);

            $apiKey = Setting::get('openai_api_key') ?: env('OPENAI_API_KEY');
            if (! $apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'OpenAI API key is not configured. Please contact the administrator.',
                ], 500);
            }

            $skills = trim($validated['skills']);

            $systemPrompt = 'You are a professional copywriter helping designers write their experience section for a designer partnership application. Generate a clear, professional, and compelling design experience description (2-4 paragraphs) based on the skills and information provided. Include: years of experience, design tools (Figma, Canva, Adobe, etc.), types of projects, specializations, and strengths. Write in first person. Do not use markdown or bullet points—use flowing paragraphs. Keep it concise but informative (150-300 words).';

            $userPrompt = "Generate a design experience description for a designer with these skills and background:\n\n".$skills;

            $model = Setting::get('openai_model') ?: env('OPENAI_MODEL', 'gpt-4o-mini');
            $baseUrl = Setting::get('openai_base_url') ?: env('OPENAI_BASE_URL', 'https://api.openai.com/v1');
            $apiUrl = rtrim($baseUrl, '/').'/chat/completions';

            set_time_limit(45); // Allow enough time for OpenAI API (HTTP timeout 30s)
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($apiUrl, [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 500,
                ]);

            if (! $response->successful()) {
                throw new \Exception('API request failed: '.$response->body());
            }

            $responseData = $response->json();
            if (! isset($responseData['choices'][0]['message']['content'])) {
                throw new \Exception('Invalid response from API');
            }

            $description = trim($responseData['choices'][0]['message']['content']);

            return response()->json([
                'success' => true,
                'description' => $description,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Please mention your skills (e.g., tools, years of experience, project types).',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error generating design experience: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store the designer application
     */
    public function store(Request $request)
    {
        // Prevent duplicate submissions - user already has pending application
        $pendingApplication = DesignerApplication::where('user_id', auth()->id())->where('status', 'pending')->first();
        if ($pendingApplication) {
            return redirect()->route('designer-application.index')
                ->with('info', 'You already have a designer application pending review. Please wait for our team to get back to you.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:50',
            'experience' => 'nullable|string|max:5000',
            'certifications' => 'nullable|string|max:5000',
            'agreement_accepted' => 'required|accepted',
            'identity_card_number' => 'nullable|string|max:100',
            'identity_card' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'account_holder_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'routing_number' => 'nullable|string|max:100',
            'swift_code' => 'nullable|string|max:50',
        ]);

        $identityCardPath = null;
        if ($request->hasFile('identity_card')) {
            $file = $request->file('identity_card');
            $identityCardPath = $file->store('designer-applications/identity-cards', 'public');
        }

        DesignerApplication::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'experience' => $validated['experience'] ?? null,
            'certifications' => $validated['certifications'] ?? null,
            'agreement_accepted' => true,
            'identity_card_number' => $validated['identity_card_number'] ?? null,
            'identity_card_path' => $identityCardPath,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'country' => $validated['country'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'account_holder_name' => $validated['account_holder_name'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'routing_number' => $validated['routing_number'] ?? null,
            'swift_code' => $validated['swift_code'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('designer-application.index')
            ->with('success', 'Your designer application has been submitted successfully! We will review it and get back to you soon.');
    }
}
