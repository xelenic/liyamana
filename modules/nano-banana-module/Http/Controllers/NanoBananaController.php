<?php

namespace Modules\NanoBananaModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CreditTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\NanoBananaModule\Models\NanoBananaTemplate;
use Modules\NanoBananaModule\Services\GeminiApiService;

class NanoBananaController extends Controller
{
    public function __construct(
        protected GeminiApiService $api
    ) {}

    public function useTemplate(int $id)
    {
        $template = NanoBananaTemplate::active()->findOrFail($id);
        $cost = (float) \App\Models\Setting::get('gemini_image_cost', 1);
        $balance = (float) (auth()->user()->balance ?? 0);
        return view('nano-banana-module::use-template', compact('template', 'cost', 'balance'));
    }

    public function generate(Request $request)
    {
        try {
            set_time_limit(120);

            $validated = $request->validate([
            'template_id' => 'required|exists:nano_banana_templates,id',
            'fields' => 'nullable|array',
            'fields.*' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
        ]);

        $fields = $validated['fields'] ?? [];

        $template = NanoBananaTemplate::active()->findOrFail($validated['template_id']);

        $prompt = $template->prompt;
        foreach ($fields as $name => $value) {
            $prompt = str_replace('{{' . $name . '}}', (string) $value, $prompt);
        }

        $aspectRatio = \App\Models\Setting::get('nanobanana_image_size', '1:1');
        $resolution = \App\Models\Setting::get('nanobanana_resolution', '2K');

        $cost = (float) \App\Models\Setting::get('gemini_image_cost', 1);
        $user = auth()->user();
        $balance = (float) ($user->balance ?? 0);

        if ($cost > 0 && $balance < $cost) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient credits. You need ' . format_price($cost) . ' (balance: ' . format_price($balance) . '). Please top up.',
            ], 400);
        }

        if ($template->upload_image && $request->hasFile('image')) {
            $path = $request->file('image')->store('nano-banana-temp', 'public');
            $url = url(Storage::disk('public')->url($path));
            $result = $this->api->editImage($prompt, [$url], $aspectRatio, $resolution);
        } else {
            $result = $this->api->generateImage($prompt, $aspectRatio, $resolution);
        }

        if (! $result['success']) {
            $error = $result['error'] ?? 'Generation failed';
            if (stripos($error, 'API key') !== false || stripos($error, '403') !== false || stripos($error, 'quota') !== false) {
                $error = 'Gemini API key is invalid or quota exceeded. Add a valid API key in Admin → Settings → Gemini Image. Get your key at https://aistudio.google.com/apikey';
            }
            return response()->json([
                'success' => false,
                'message' => $error,
            ], 400);
        }

        if ($cost > 0) {
            $user->decrement('balance', $cost);
            CreditTransaction::create([
                'user_id' => $user->id,
                'type' => 'deduct',
                'amount' => -$cost,
                'balance_after' => $user->fresh()->balance,
                'payment_method' => 'platform_credit',
                'reference' => 'gemini_image_generation',
                'description' => 'AI image generation - ' . $template->name,
                'status' => 'completed',
            ]);
        }

        return response()->json([
            'success' => true,
            'image_url' => $result['image_url'],
        ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('NanoBanana generate error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
