<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Jobs\GenerateAiContentFromTemplateJob;
use App\Models\AddressBook;
use App\Models\AiContentGeneration;
use App\Models\AiContentTemplate;
use App\Models\CreditTransaction;
use App\Models\DesignFont;
use App\Models\EnvelopeType;
use App\Models\IntroTourStep;
use App\Models\Order;
use App\Models\Product;
use App\Models\ScheduledMail;
use App\Models\Setting;
use App\Models\Template;
use App\Models\TemplateReview;
use App\Models\Testimonial;
use App\Services\AiContentCreditService;
use App\Services\AIDocumentGenerator;
use App\Services\DesignCheckoutStockService;
use App\Services\Payment\PaymentGatewayRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DesignController extends Controller
{
    /**
     * Show the design tool (Canva-like interface)
     */
    public function index()
    {
        $aiContentTemplates = AiContentTemplate::active()->ordered()->get();
        $aiTemplateRecentGenerations = $this->recentAiGenerationsGroupedByTemplate($aiContentTemplates);

        return view('design.index', compact('aiContentTemplates', 'aiTemplateRecentGenerations'));
    }

    /**
     * Create a new design
     */
    public function create(Request $request)
    {
        // Get active sheet types and licenses
        $sheetTypes = \App\Models\SheetType::active()->ordered()->get();
        $licenses = \App\Models\License::active()->ordered()->get();

        // Check if editing a flipbook design
        if ($request->has('edit_flipbook')) {
            $flipbookId = $request->get('edit_flipbook');
            try {
                $flipBook = \App\Models\FlipBook::where('user_id', auth()->id())->findOrFail($flipbookId);

                // Check if flipbook was created from design and has design data
                $settings = $flipBook->settings ?? [];
                if (isset($settings['created_from_design']) && $settings['created_from_design'] && isset($settings['design_data']) && ! empty($settings['design_data'])) {
                    $designType = $request->get('type', $request->get('design_type', 'flipbook'));

                    return view('design.multi-page-editor', array_merge([
                        'editFlipbookId' => $flipbookId,
                        'fromFlipbook' => true, // Show flipbook buttons in toolbar
                        'designType' => $designType,
                        'flipbookData' => [
                            'id' => $flipBook->id,
                            'title' => $flipBook->title,
                            'description' => $flipBook->description ?? '',
                            'status' => $flipBook->status ?? 'draft',
                            'is_public' => $flipBook->is_public ?? false,
                            'design_data' => $settings['design_data'],
                            'print_settings' => $settings['print_settings'] ?? null,
                        ],
                        'sheetTypes' => $sheetTypes,
                        'licenses' => $licenses,
                        'canExportWatermark' => auth()->check() && auth()->user() && auth()->user()->hasRole('admin'),
                        'canSavePublicTemplate' => auth()->check() && auth()->user() && auth()->user()->hasRole(['admin', 'designer']),
                    ], $this->multiPageEditorIntroData()));
                }
            } catch (\Exception $e) {
                \Log::error('Error loading flipbook for editing: '.$e->getMessage());
            }
        }

        // Check if loading an existing design
        if ($request->has('load')) {
            $designs = $request->session()->get('user_designs', []);
            $designId = $request->get('load');

            if (isset($designs[$designId])) {
                $design = $designs[$designId];
                // Check if it's a multi-page design
                if (isset($design['is_multi_page']) && $design['is_multi_page']) {
                    $designType = $design['type'] ?? $request->get('type', $request->get('design_type', ''));

                    return view('design.multi-page-editor', array_merge([
                        'loadId' => $designId,
                        'designType' => $designType,
                        'sheetTypes' => $sheetTypes,
                        'licenses' => $licenses,
                        'canExportWatermark' => auth()->check() && auth()->user() && auth()->user()->hasRole('admin'),
                        'canSavePublicTemplate' => auth()->check() && auth()->user() && auth()->user()->hasRole(['admin', 'designer']),
                    ], $this->multiPageEditorIntroData()));
                }
            }
        }

        // Check if multi-page mode is requested
        if ($request->has('multi') || $request->get('multi') === 'true') {
            $fromFlipbook = $request->has('from_flipbook') && $request->get('from_flipbook') === 'true';
            $designType = $request->get('type', $request->get('design_type', ''));
            $generatedImage = $request->get('generated_image'); // URL for Nano Banana generated image
            $aiTitle = $request->get('ai_title', '');
            $aiSubtitle = $request->get('ai_subtitle', '');
            $aiContentTemplate = null;
            if ($request->has('ai_template') && $request->ai_template) {
                $aiContentTemplate = AiContentTemplate::active()->find($request->ai_template);
            }

            return view('design.multi-page-editor', array_merge([
                'fromFlipbook' => $fromFlipbook,
                'designType' => $designType,
                'sheetTypes' => $sheetTypes,
                'licenses' => $licenses,
                'generatedImage' => $generatedImage,
                'aiTitle' => $aiTitle,
                'aiSubtitle' => $aiSubtitle,
                'aiContentTemplate' => $aiContentTemplate,
                'canExportWatermark' => auth()->check() && auth()->user() && auth()->user()->hasRole('admin'),
                'canSavePublicTemplate' => auth()->check() && auth()->user() && auth()->user()->hasRole(['admin', 'designer']),
            ], $this->multiPageEditorIntroData()));
        }

        return view('design.editor');
    }

    /**
     * Save design
     */
    public function store(Request $request)
    {
        try {
            // Check if this is a multi-page design
            if ($request->has('pages') && is_array($request->input('pages'))) {
                $validated = $request->validate([
                    'name' => 'required|string|max:255',
                    'pages' => 'required|array|min:1',
                    'pages.*' => 'required|string', // Each page is a Fabric.js JSON string
                    'thumbnail' => 'nullable|string', // base64 image
                    'design_id' => 'nullable|string', // Optional: for updates
                    'type' => 'nullable|string|max:50', // Design type: letter, document, flipbook, etc.
                ]);

                // Store multi-page design in session
                $designs = $request->session()->get('user_designs', []);

                // Check if updating existing design
                $designId = $validated['design_id'] ?? uniqid('design_');
                $isUpdate = isset($designs[$designId]);

                // Store pages array (preserve type when updating if not sent)
                $storedType = $validated['type'] ?? '';
                if ($isUpdate && $storedType === '' && isset($designs[$designId]['type'])) {
                    $storedType = $designs[$designId]['type'];
                }
                $designData = [
                    'id' => $designId,
                    'name' => $validated['name'],
                    'pages' => $validated['pages'], // Array of page data
                    'is_multi_page' => true,
                    'page_count' => count($validated['pages']),
                    'thumbnail' => $validated['thumbnail'] ?? null,
                    'type' => $storedType, // Design type for Send Letter, etc.
                ];
                // Preserve send_letter_template_id when updating (so we update same template on next Send Letter)
                if ($isUpdate && isset($designs[$designId]['send_letter_template_id'])) {
                    $designData['send_letter_template_id'] = $designs[$designId]['send_letter_template_id'];
                }
                // Preserve thumbnail_path when updating (until we save a new thumbnail)
                if ($isUpdate && isset($designs[$designId]['thumbnail_path'])) {
                    $designData['thumbnail_path'] = $designs[$designId]['thumbnail_path'];
                }

                // Preserve created_at when updating
                if ($isUpdate && isset($designs[$designId]['created_at'])) {
                    $designData['created_at'] = $designs[$designId]['created_at'];
                    $designData['updated_at'] = now()->toDateTimeString();
                } else {
                    $designData['created_at'] = now()->toDateTimeString();
                }

                $designs[$designId] = $designData;
                $request->session()->put('user_designs', $designs);

                // If thumbnail is provided, save it
                if ($validated['thumbnail']) {
                    $thumbnailPath = $this->saveBase64Image($validated['thumbnail'], $designId);
                    $designs[$designId]['thumbnail_path'] = $thumbnailPath;
                    $request->session()->put('user_designs', $designs);
                }

                return response()->json([
                    'success' => true,
                    'message' => $isUpdate ? 'Design updated successfully' : 'Multi-page design saved successfully',
                    'design_id' => $designId,
                ]);
            } else {
                // Single page design (original behavior)
                $validated = $request->validate([
                    'name' => 'required|string|max:255',
                    'design_data' => 'required|string', // Fabric.js JSON string
                    'thumbnail' => 'nullable|string', // base64 image
                    'design_id' => 'nullable|string', // Optional: for updates
                ]);

                // Store design in session for now (can be saved to database later)
                $designs = $request->session()->get('user_designs', []);

                // Check if updating existing design
                $designId = $validated['design_id'] ?? uniqid('design_');
                $isUpdate = isset($designs[$designId]);

                // Store design_data as string (Fabric.js JSON)
                $designData = [
                    'id' => $designId,
                    'name' => $validated['name'],
                    'design_data' => $validated['design_data'], // Keep as JSON string for Fabric.js
                    'is_multi_page' => false,
                    'thumbnail' => $validated['thumbnail'] ?? null,
                ];

                // Preserve created_at if updating
                if ($isUpdate && isset($designs[$designId]['created_at'])) {
                    $designData['created_at'] = $designs[$designId]['created_at'];
                    $designData['updated_at'] = now()->toDateTimeString();
                } else {
                    $designData['created_at'] = now()->toDateTimeString();
                }

                $designs[$designId] = $designData;
                $request->session()->put('user_designs', $designs);

                // If thumbnail is provided, save it
                if ($validated['thumbnail']) {
                    $thumbnailPath = $this->saveBase64Image($validated['thumbnail'], $designId);
                    $designs[$designId]['thumbnail_path'] = $thumbnailPath;
                    $request->session()->put('user_designs', $designs);
                }

                return response()->json([
                    'success' => true,
                    'message' => $isUpdate ? 'Design updated successfully' : 'Design saved successfully',
                    'design_id' => $designId,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save design: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export design as image
     */
    public function export(Request $request)
    {
        try {
            $validated = $request->validate([
                'design_data' => 'required|json',
                'format' => 'nullable|in:png,jpg,jpeg',
                'width' => 'nullable|integer|min:100|max:4000',
                'height' => 'nullable|integer|min:100|max:4000',
            ]);

            // This would typically use a library like Intervention Image or similar
            // For now, return the design data for client-side export
            return response()->json([
                'success' => true,
                'message' => 'Export ready',
                'design_data' => json_decode($validated['design_data'], true),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's saved designs and flipbooks (flipbooks with design data)
     */
    public function designs(Request $request)
    {
        $sessionDesigns = $request->session()->get('user_designs', []);
        $designs = array_values($sessionDesigns);

        // Add flipbooks that have design data (created from design tool)
        $flipBooks = \App\Models\FlipBook::where('user_id', auth()->id())
            ->whereNotNull('settings->design_data')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($flipBooks as $flipBook) {
            $settings = $flipBook->settings ?? [];
            if (! empty($settings['design_data'])) {
                $designs[] = [
                    'id' => 'flipbook_'.$flipBook->id,
                    'name' => $flipBook->title,
                    'thumbnail' => $flipBook->cover_image ? Storage::disk('public')->url($flipBook->cover_image) : null,
                    'created_at' => $flipBook->created_at->toDateTimeString(),
                    'is_flipbook' => true,
                    'flipbook_id' => $flipBook->id,
                ];
            }
        }

        // Sort by created_at descending
        usort($designs, function ($a, $b) {
            return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
        });

        return response()->json([
            'success' => true,
            'designs' => $designs,
        ]);
    }

    /**
     * Show letter design details page (for letter type designs)
     */
    public function show($id, Request $request)
    {
        $designs = $request->session()->get('user_designs', []);

        if (! isset($designs[$id])) {
            abort(404, 'Design not found');
        }

        $design = $designs[$id];

        // Letter type: show letter details page
        if (($design['type'] ?? '') === 'letter') {
            return view('design.letter-show', compact('design'));
        }

        // Other types: redirect to editor
        return redirect()->route('design.create', ['load' => $id]);
    }

    /**
     * Prepare letter for send - create or update template from design and redirect to send letter checkout
     */
    public function prepareSendLetter($id, Request $request)
    {
        $designs = $request->session()->get('user_designs', []);

        if (! isset($designs[$id])) {
            return redirect()->route('design.index')->with('error', 'Design not found');
        }

        $design = $designs[$id];
        if (($design['type'] ?? '') !== 'letter') {
            return redirect()->route('design.index')->with('error', 'This design is not a letter');
        }

        $pages = $design['pages'] ?? [];
        if (empty($pages)) {
            return redirect()->route('design.index')->with('error', 'Letter has no pages');
        }

        try {
            $fonts = $this->extractFontsFromPages($pages);
            $templateName = ($design['name'] ?? 'Letter').' - '.now()->format('M d, Y H:i');

            // Check if we already have a template for this design (from previous Send Letter click)
            $existingTemplateId = $design['send_letter_template_id'] ?? null;
            if ($existingTemplateId) {
                $template = Template::where('id', $existingTemplateId)
                    ->where('created_by', auth()->id())
                    ->where('is_public', false)
                    ->first();

                if ($template) {
                    // Update existing template instead of creating new one
                    $template->update([
                        'name' => $templateName,
                        'pages' => $pages,
                        'page_count' => count($pages),
                        'fonts' => $fonts,
                    ]);

                    // Update thumbnail if we have a new one
                    if (! empty($design['thumbnail']) && preg_match('/^data:image\/(\w+);base64,/', $design['thumbnail'])) {
                        $thumbnailPath = $this->saveTemplateThumbnail($design['thumbnail'], 'template_'.$template->id);
                        if ($thumbnailPath) {
                            $template->update(['thumbnail_path' => $thumbnailPath]);
                        }
                    } elseif (! empty($design['thumbnail_path']) && Storage::disk('public')->exists($design['thumbnail_path'])) {
                        $newPath = 'templates/template_'.$template->id.'_thumbnail.png';
                        Storage::disk('public')->copy($design['thumbnail_path'], $newPath);
                        $template->update(['thumbnail_path' => $newPath]);
                    }

                    return redirect()->route('design.templates.sendLetter', $template->id);
                }
                // Template not found or not owned - fall through to create new one
            }

            // Create new template (first time or existing template no longer valid)
            $templateData = [
                'name' => $templateName,
                'is_public' => false,
                'category' => 'general',
                'pages' => $pages,
                'page_count' => count($pages),
                'thumbnail_path' => null,
                'variables' => [],
                'fonts' => $fonts,
                'is_active' => true,
                'created_by' => auth()->id(),
            ];

            $template = Template::create($templateData);

            if (! empty($design['thumbnail']) && preg_match('/^data:image\/(\w+);base64,/', $design['thumbnail'])) {
                $thumbnailPath = $this->saveTemplateThumbnail($design['thumbnail'], 'template_'.$template->id);
                if ($thumbnailPath) {
                    $template->update(['thumbnail_path' => $thumbnailPath]);
                }
            } elseif (! empty($design['thumbnail_path']) && Storage::disk('public')->exists($design['thumbnail_path'])) {
                $newPath = 'templates/template_'.$template->id.'_thumbnail.png';
                Storage::disk('public')->copy($design['thumbnail_path'], $newPath);
                $template->update(['thumbnail_path' => $newPath]);
            }

            // Store template ID in design so next Send Letter click will update instead of create
            $designs[$id]['send_letter_template_id'] = $template->id;
            $request->session()->put('user_designs', $designs);

            return redirect()->route('design.templates.sendLetter', $template->id);
        } catch (\Exception $e) {
            \Log::error('Error preparing letter for send: '.$e->getMessage());

            return redirect()->route('design.show', $id)->with('error', 'Failed to prepare letter: '.$e->getMessage());
        }
    }

    /**
     * Prepare letter from editor (API) - create or update template, return template_id
     */
    public function prepareSendLetterFromEditor(Request $request)
    {
        try {
            $validated = $request->validate([
                'design_id' => 'nullable|string',
                'name' => 'required|string|max:255',
                'pages' => 'required|array|min:1',
                'pages.*' => 'required|string',
                'thumbnail' => 'nullable|string',
                'variables' => 'nullable|array',
                'fonts' => 'nullable|array',
            ]);

            $designId = $validated['design_id'] ?? null;
            $pages = $validated['pages'];
            $fonts = $validated['fonts'] ?? $this->extractFontsFromPages($pages);
            $templateName = $validated['name'];
            $variables = $validated['variables'] ?? [];

            // Check if we have an existing template for this design (from previous Send Letter)
            $existingTemplateId = null;
            if ($designId) {
                $designs = $request->session()->get('user_designs', []);
                $design = $designs[$designId] ?? null;
                $existingTemplateId = $design['send_letter_template_id'] ?? null;
            }

            if ($existingTemplateId) {
                $template = Template::where('id', $existingTemplateId)
                    ->where('created_by', auth()->id())
                    ->where('is_public', false)
                    ->first();

                if ($template) {
                    // Update existing template
                    $template->update([
                        'name' => $templateName,
                        'pages' => $pages,
                        'page_count' => count($pages),
                        'variables' => $variables,
                        'fonts' => $fonts,
                    ]);

                    if (! empty($validated['thumbnail']) && preg_match('/^data:image\/(\w+);base64,/', $validated['thumbnail'])) {
                        $thumbnailPath = $this->saveTemplateThumbnail($validated['thumbnail'], 'template_'.$template->id);
                        if ($thumbnailPath) {
                            $template->update(['thumbnail_path' => $thumbnailPath]);
                        }
                    }

                    return response()->json(['success' => true, 'template_id' => $template->id]);
                }
            }

            // Create new template
            $templateData = [
                'name' => $templateName,
                'is_public' => false,
                'category' => 'general',
                'pages' => $pages,
                'page_count' => count($pages),
                'thumbnail_path' => null,
                'variables' => $variables,
                'fonts' => $fonts,
                'is_active' => true,
                'created_by' => auth()->id(),
            ];

            $template = Template::create($templateData);

            if (! empty($validated['thumbnail']) && preg_match('/^data:image\/(\w+);base64,/', $validated['thumbnail'])) {
                $thumbnailPath = $this->saveTemplateThumbnail($validated['thumbnail'], 'template_'.$template->id);
                if ($thumbnailPath) {
                    $template->update(['thumbnail_path' => $thumbnailPath]);
                }
            }

            // Store template ID in design (session) so next Send Letter will update instead of create
            if ($designId) {
                $designs = $request->session()->get('user_designs', []);
                if (isset($designs[$designId])) {
                    $designs[$designId]['send_letter_template_id'] = $template->id;
                    $designs[$designId]['pages'] = $pages;
                    $designs[$designId]['name'] = $templateName;
                    $request->session()->put('user_designs', $designs);
                }
            }

            return response()->json(['success' => true, 'template_id' => $template->id]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: '.implode(', ', array_map(fn ($err) => implode(', ', $err), $e->errors())),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error preparing letter from editor: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Load a specific design
     */
    public function load($id, Request $request)
    {
        $designs = $request->session()->get('user_designs', []);

        if (! isset($designs[$id])) {
            return response()->json([
                'success' => false,
                'message' => 'Design not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'design' => $designs[$id],
        ]);
    }

    /**
     * Update design name (session designs only) - for inline edit
     */
    public function updateDesignName(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $designs = $request->session()->get('user_designs', []);

        if (! isset($designs[$id])) {
            return response()->json([
                'success' => false,
                'message' => 'Design not found',
            ], 404);
        }

        $designs[$id]['name'] = $request->name;
        $request->session()->put('user_designs', $designs);

        return response()->json([
            'success' => true,
            'message' => 'Name updated',
            'name' => $request->name,
        ]);
    }

    /**
     * Delete a design
     */
    public function destroy($id, Request $request)
    {
        $designs = $request->session()->get('user_designs', []);

        if (isset($designs[$id])) {
            // Delete thumbnail if exists
            if (isset($designs[$id]['thumbnail_path'])) {
                Storage::disk('public')->delete($designs[$id]['thumbnail_path']);
            }

            unset($designs[$id]);
            $request->session()->put('user_designs', $designs);
        }

        return response()->json([
            'success' => true,
            'message' => 'Design deleted successfully',
        ]);
    }

    /**
     * Save base64 image to storage
     */
    private function saveBase64Image($base64, $designId)
    {
        // Remove data:image/png;base64, prefix if present
        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $imageData = base64_decode($base64);

        $filename = 'designs/'.$designId.'_thumbnail.png';
        Storage::disk('public')->put($filename, $imageData);

        return $filename;
    }

    /**
     * Get image library images
     */
    public function imageLibraryIndex(Request $request)
    {
        try {
            $userId = auth()->id();
            $libraryPath = 'image-library/'.$userId;

            // Create directory if it doesn't exist
            if (! Storage::disk('public')->exists($libraryPath)) {
                Storage::disk('public')->makeDirectory($libraryPath);
            }

            // Get all images from user's library directory
            $files = Storage::disk('public')->files($libraryPath);
            $images = [];

            foreach ($files as $file) {
                // Only include image files
                if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
                    $images[] = [
                        'id' => $file,
                        'path' => $file,
                        'url' => Storage::disk('public')->url($file),
                        'name' => basename($file),
                    ];
                }
            }

            // Sort by name
            usort($images, function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            return response()->json([
                'success' => true,
                'images' => $images,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading image library: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error loading image library: '.$e->getMessage(),
                'images' => [],
            ], 500);
        }
    }

    /**
     * Upload images to library
     */
    public function imageLibraryUpload(Request $request)
    {
        try {
            $validated = $request->validate([
                'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max per image
            ]);

            $userId = auth()->id();
            $libraryPath = 'image-library/'.$userId;

            // Create directory if it doesn't exist
            if (! Storage::disk('public')->exists($libraryPath)) {
                Storage::disk('public')->makeDirectory($libraryPath);
            }

            $uploadedImages = [];

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    // Generate unique filename
                    $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                    $path = $file->storeAs($libraryPath, $filename, 'public');

                    $uploadedImages[] = [
                        'id' => $path,
                        'path' => $path,
                        'url' => Storage::disk('public')->url($path),
                        'name' => $filename,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($uploadedImages).' image(s) uploaded successfully',
                'images' => $uploadedImages,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error uploading images to library: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error uploading images: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete image from library
     */
    public function imageLibraryDelete(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|string',
            ]);

            $userId = auth()->id();
            $imagePath = $validated['id'];

            // Security check: ensure the image belongs to the current user
            if (! str_starts_with($imagePath, 'image-library/'.$userId.'/')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You can only delete your own images',
                ], 403);
            }

            // Delete the image file
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting image from library: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error deleting image: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get global image library (image parts) - shared across all users, category-wise
     */
    public function globalImageLibraryIndex(Request $request)
    {
        try {
            $categories = \App\Models\GlobalImageCategory::with('images')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            $result = $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'images' => $category->images->map(function ($img) {
                        return [
                            'id' => $img->id,
                            'path' => $img->path,
                            'url' => $img->url,
                            'name' => $img->name ?? basename($img->path),
                        ];
                    })->values()->all(),
                ];
            });

            return response()->json([
                'success' => true,
                'categories' => $result,
                'images' => $result->flatMap(fn ($c) => $c['images'])->values()->all(), // backward compat
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading global image library: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error loading global image library: '.$e->getMessage(),
                'categories' => [],
                'images' => [],
            ], 500);
        }
    }

    /**
     * Get font library fonts
     */
    public function fontLibraryIndex(Request $request)
    {
        try {
            $adminFonts = DesignFont::query()
                ->active()
                ->ordered()
                ->get()
                ->map(fn (DesignFont $f) => $f->toLibraryPayload())
                ->values()
                ->all();

            $userId = auth()->id();
            $libraryPath = 'font-library/'.$userId;

            // Create directory if it doesn't exist
            if (! Storage::disk('public')->exists($libraryPath)) {
                Storage::disk('public')->makeDirectory($libraryPath);
            }

            // Get all font files from user's library directory
            $files = Storage::disk('public')->files($libraryPath);
            $userFonts = [];

            foreach ($files as $file) {
                // Only include font files
                if (preg_match('/\.(ttf|otf|woff|woff2|eot)$/i', $file)) {
                    $fontName = pathinfo(basename($file), PATHINFO_FILENAME);
                    // Clean up font name (remove numbers, underscores, etc.)
                    $fontName = preg_replace('/[_\d-]+/', ' ', $fontName);
                    $fontName = ucwords(trim($fontName));

                    $userFonts[] = [
                        'id' => $file,
                        'path' => $file,
                        'url' => Storage::disk('public')->url($file),
                        'name' => $fontName,
                        'filename' => basename($file),
                        'extension' => pathinfo($file, PATHINFO_EXTENSION),
                        'source' => 'user',
                        'deletable' => true,
                    ];
                }
            }

            usort($userFonts, function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            $fonts = array_merge($adminFonts, $userFonts);

            return response()->json([
                'success' => true,
                'fonts' => $fonts,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading font library: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error loading font library: '.$e->getMessage(),
                'fonts' => [],
            ], 500);
        }
    }

    /**
     * Upload fonts to library
     */
    public function fontLibraryUpload(Request $request)
    {
        try {
            // Validate that fonts array exists and has files
            $request->validate([
                'fonts' => 'required|array|min:1',
                'fonts.*' => 'required|file|max:10240', // 10MB max per font
            ]);

            $userId = auth()->id();
            $libraryPath = 'font-library/'.$userId;

            // Create directory if it doesn't exist
            if (! Storage::disk('public')->exists($libraryPath)) {
                Storage::disk('public')->makeDirectory($libraryPath);
            }

            $uploadedFonts = [];
            $allowedExtensions = ['ttf', 'otf', 'woff', 'woff2', 'eot'];
            $errors = [];

            if ($request->hasFile('fonts')) {
                foreach ($request->file('fonts') as $index => $file) {
                    // Check file extension manually
                    $extension = strtolower($file->getClientOriginalExtension());

                    if (! in_array($extension, $allowedExtensions)) {
                        $errors[] = "File '{$file->getClientOriginalName()}' has invalid extension. Allowed: ".implode(', ', $allowedExtensions);

                        continue;
                    }

                    // Check file size (10MB = 10485760 bytes)
                    if ($file->getSize() > 10485760) {
                        $errors[] = "File '{$file->getClientOriginalName()}' is too large. Maximum size is 10MB.";

                        continue;
                    }

                    try {
                        // Generate unique filename
                        $filename = time().'_'.uniqid().'.'.$extension;
                        $path = $file->storeAs($libraryPath, $filename, 'public');

                        $fontName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $fontName = preg_replace('/[_\d-]+/', ' ', $fontName);
                        $fontName = ucwords(trim($fontName));

                        $uploadedFonts[] = [
                            'id' => $path,
                            'path' => $path,
                            'url' => Storage::disk('public')->url($path),
                            'name' => $fontName,
                            'filename' => $filename,
                            'extension' => $extension,
                            'source' => 'user',
                            'deletable' => true,
                        ];
                    } catch (\Exception $e) {
                        $errors[] = "Failed to upload '{$file->getClientOriginalName()}': ".$e->getMessage();
                        \Log::error('Error uploading font file: '.$e->getMessage());
                    }
                }
            }

            // If there are errors but some files were uploaded, return partial success
            if (count($errors) > 0 && count($uploadedFonts) > 0) {
                return response()->json([
                    'success' => true,
                    'message' => count($uploadedFonts).' font(s) uploaded successfully, but some files failed',
                    'fonts' => $uploadedFonts,
                    'errors' => $errors,
                    'partial' => true,
                ]);
            }

            // If there are errors and no files uploaded, return error
            if (count($errors) > 0 && count($uploadedFonts) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload fonts',
                    'errors' => $errors,
                ], 422);
            }

            // If no files were uploaded at all
            if (count($uploadedFonts) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid font files were uploaded',
                    'errors' => ['Please select valid font files (TTF, OTF, WOFF, WOFF2, EOT)'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => count($uploadedFonts).' font(s) uploaded successfully',
                'fonts' => $uploadedFonts,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: '.implode(', ', $e->validator->errors()->all()),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error uploading fonts to library: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error uploading fonts: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete font from library
     */
    public function fontLibraryDelete(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|string',
            ]);

            $userId = auth()->id();
            $fontPath = $validated['id'];

            // Security check: ensure the font belongs to the current user
            if (! str_starts_with($fontPath, 'font-library/'.$userId.'/')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You can only delete your own fonts',
                ], 403);
            }

            // Delete the font file
            if (Storage::disk('public')->exists($fontPath)) {
                Storage::disk('public')->delete($fontPath);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Font not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Font deleted successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting font from library: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error deleting font: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show templates page
     */
    public function templatesPage()
    {
        $aiContentTemplates = AiContentTemplate::active()->ordered()->get();
        $aiTemplateRecentGenerations = $this->recentAiGenerationsGroupedByTemplate($aiContentTemplates);
        $orderReviewPrompt = session('order_review_prompt');

        return view('design.templates.index', compact('aiContentTemplates', 'aiTemplateRecentGenerations', 'orderReviewPrompt'));
    }

    /**
     * Show public templates explore page
     */
    public function exploreTemplates(Request $request)
    {
        $licenses = \App\Models\License::active()->ordered()->get();
        $slides = \App\Models\ExploreSlide::active()->ordered()->get();
        $featuredTemplates = Template::where('is_active', true)
            ->where('is_public', true)
            ->featured()
            ->with('creator', 'products')
            ->withCount('orders')
            ->limit(12)
            ->get()
            ->map(function ($template) {
                $averageRating = \App\Models\TemplateReview::where('template_id', $template->id)
                    ->where('is_approved', true)->avg('rating') ?? 0;
                $totalReviews = \App\Models\TemplateReview::where('template_id', $template->id)
                    ->where('is_approved', true)->count();
                $templatePrice = (float) ($template->price ?? 0);
                $displayPrice = $templatePrice;
                if ($template->products->isNotEmpty()) {
                    $minProductPrice = (float) $template->products->min('price');
                    $displayPrice = $templatePrice + $minProductPrice;
                }

                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'short_description' => $template->short_description,
                    'description' => $template->description,
                    'category' => $template->category,
                    'type' => $template->type ?? '',
                    'price' => $template->price,
                    'display_price' => $displayPrice,
                    'licence' => $template->licence,
                    'page_count' => $template->page_count,
                    'thumbnail_url' => $template->thumbnail_url,
                    'thumbnail_path' => $template->thumbnail_path,
                    'images' => $this->getTemplateImagesUrls($template->images ?? []),
                    'average_rating' => round($averageRating, 1),
                    'total_reviews' => $totalReviews,
                    'orders_count' => $template->orders_count ?? 0,
                    'created_at' => $template->created_at->toDateTimeString(),
                ];
            })
            ->values()
            ->toArray();
        $aiContentTemplates = AiContentTemplate::active()->ordered()->get();
        $aiTemplateRecentGenerations = $this->recentAiGenerationsGroupedByTemplate($aiContentTemplates);
        $categories = \App\Models\TemplateCategory::active()->ordered()->get(['slug', 'name']);
        $explorePageTitle = Setting::get('explore_page_title', 'Explore Templates');
        $exploreShowFeatured = filter_var(Setting::get('explore_show_featured', '1'), FILTER_VALIDATE_BOOLEAN);
        $exploreShowCategories = filter_var(Setting::get('explore_show_categories', '1'), FILTER_VALIDATE_BOOLEAN);
        $exploreTooltipEnabled = filter_var(Setting::get('explore_tooltip_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
        $exploreTooltipDelayMs = (int) Setting::get('explore_tooltip_delay_ms', '700');
        if ($exploreTooltipDelayMs < 200) {
            $exploreTooltipDelayMs = 200;
        }
        if ($exploreTooltipDelayMs > 2000) {
            $exploreTooltipDelayMs = 2000;
        }

        $showSpecialOffersModal = false;
        $specialOffersModalImageUrl = null;
        $specialOffersModalFrequency = Setting::get('special_offers_modal_frequency', 'once');
        if (auth()->check()) {
            $imagePath = Setting::get('special_offers_modal_image', '');
            if ($imagePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($imagePath)) {
                $specialOffersModalImageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
            }
            $user = auth()->user();
            switch ($specialOffersModalFrequency) {
                case 'once':
                    $showSpecialOffersModal = $user->special_offers_modal_shown_at === null;
                    break;
                case 'daily':
                    $showSpecialOffersModal = $user->special_offers_modal_shown_at === null
                        || $user->special_offers_modal_shown_at->format('Y-m-d') < now()->format('Y-m-d');
                    break;
                case 'on_login':
                    $showSpecialOffersModal = ! $request->session()->get('special_offers_modal_shown_this_session', false);
                    break;
                case 'always':
                    $showSpecialOffersModal = true;
                    break;
                default:
                    $showSpecialOffersModal = $user->special_offers_modal_shown_at === null;
            }
        }

        $introShowMode = Setting::get('design_intro_explore_show_mode', 'first_time');
        $introAlreadySeenForAccount = auth()->user() ? auth()->user()->intro_tour_explore_seen_at !== null : true;
        $introExploreSteps = IntroTourStep::forTour('templates_explore')->get()->map(fn ($s) => [
            'element_selector' => $s->element_selector,
            'title' => $s->title,
            'intro_text' => $s->intro_text,
        ])->values()->toArray();

        return view('design.templates.explore', compact('licenses', 'slides', 'featuredTemplates', 'aiContentTemplates', 'aiTemplateRecentGenerations', 'categories', 'explorePageTitle', 'exploreShowFeatured', 'exploreShowCategories', 'exploreTooltipEnabled', 'exploreTooltipDelayMs', 'showSpecialOffersModal', 'specialOffersModalImageUrl', 'specialOffersModalFrequency', 'introShowMode', 'introAlreadySeenForAccount', 'introExploreSteps'));
    }

    /**
     * Recent AI generations for the current user, grouped by AI content template (max 3 per template).
     *
     * @param  Collection<int, AiContentTemplate>  $templates
     * @return Collection<int, Collection<int, AiContentGeneration>>
     */
    protected function recentAiGenerationsGroupedByTemplate(Collection $templates): Collection
    {
        if (! auth()->check() || $templates->isEmpty()) {
            return collect();
        }

        $ids = $templates->pluck('id')->filter()->values()->all();
        if ($ids === []) {
            return collect();
        }

        return AiContentGeneration::query()
            ->where('user_id', auth()->id())
            ->whereIn('ai_content_template_id', $ids)
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn (AiContentGeneration $g) => (int) $g->ai_content_template_id)
            ->map(fn (Collection $group) => $group->take(3)->values());
    }

    /**
     * Mark special offers modal as dismissed
     */
    public function dismissSpecialOffersModal(Request $request)
    {
        if (! auth()->check()) {
            return response()->json(['success' => false], 401);
        }
        $frequency = Setting::get('special_offers_modal_frequency', 'once');
        if (in_array($frequency, ['once', 'daily'], true)) {
            auth()->user()->update(['special_offers_modal_shown_at' => now()]);
        }
        if ($frequency === 'on_login') {
            $request->session()->put('special_offers_modal_shown_this_session', true);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get active licenses (for template form - public save)
     */
    public function licensesIndex()
    {
        $licenses = \App\Models\License::active()->ordered()->get(['id', 'name', 'slug']);

        return response()->json([
            'success' => true,
            'licenses' => $licenses,
        ]);
    }

    /**
     * Get template categories from database (template_categories table, active only)
     */
    public function templateCategoriesIndex()
    {
        $categories = \App\Models\TemplateCategory::active()
            ->ordered()
            ->get(['slug', 'name'])
            ->map(fn ($cat) => [
                'value' => $cat->slug,
                'label' => $cat->name,
            ])
            ->values()
            ->toArray();

        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }

    /**
     * Generate short and long template descriptions using AI (OpenAI)
     */
    public function generateTemplateDescriptions(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'nullable|string|max:50',
                'category' => 'nullable|string|max:50',
                'page_count' => 'nullable|integer|min:1|max:500',
            ]);

            $apiKey = Setting::get('openai_api_key') ?: env('OPENAI_API_KEY');
            if (! $apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'OpenAI API key is not configured. Please add it in Admin → Settings.',
                ], 500);
            }

            $name = $validated['name'];
            $type = $validated['type'] ?? 'template';
            $category = $validated['category'] ?? '';
            $pageCount = $validated['page_count'] ?? 1;

            $context = "Template name: \"{$name}\". Type: {$type}.";
            if ($category) {
                $context .= ' Category: '.str_replace('-', ' ', $category).'.';
            }
            $context .= " Page count: {$pageCount}.";

            $systemPrompt = "You are a professional copywriter for a template/design marketplace. Generate two descriptions for a design template.\n\n".
                "Reply with exactly two parts separated by a blank line:\n".
                "1) SHORT: One sentence, maximum 200 characters, suitable for listings and previews. No label or prefix—just the short text.\n".
                "2) FULL: A longer description (2–4 sentences) with details, use cases, and benefits. No label or prefix—just the full text.\n\n".
                'Do not use markdown, bullet points, or the words SHORT/FULL in the output. Output only the two text blocks separated by one blank line.';

            $userPrompt = "Generate short and full descriptions for this template. {$context}";

            $model = Setting::get('openai_model') ?: env('OPENAI_MODEL', 'gpt-4o-mini');
            $baseUrl = Setting::get('openai_base_url') ?: env('OPENAI_BASE_URL', 'https://api.openai.com/v1');
            $apiUrl = rtrim($baseUrl, '/').'/chat/completions';

            set_time_limit(35);
            $response = Http::timeout(20)
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
                    'max_tokens' => 400,
                ]);

            if (! $response->successful()) {
                throw new \Exception('API request failed: '.$response->body());
            }

            $responseData = $response->json();
            if (! isset($responseData['choices'][0]['message']['content'])) {
                throw new \Exception('Invalid response from API');
            }

            $content = trim($responseData['choices'][0]['message']['content']);
            $parts = preg_split('/\n\s*\n/', $content, 2);
            $shortDescription = isset($parts[0]) ? trim($parts[0]) : '';
            $fullDescription = isset($parts[1]) ? trim($parts[1]) : $shortDescription;

            if (mb_strlen($shortDescription) > 200) {
                $shortDescription = mb_substr($shortDescription, 0, 197).'...';
            }

            return response()->json([
                'success' => true,
                'short_description' => $shortDescription,
                'description' => $fullDescription,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Generate template descriptions: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's templates
     */
    public function templatesIndex(Request $request)
    {
        try {
            // Default to showing only user's templates, unless 'all' parameter is provided
            $showAll = $request->has('all') && $request->all == '1';

            if ($showAll) {
                // Show only active public templates for explore page
                $query = Template::where('is_active', true)
                    ->where('is_public', true)
                    ->with('creator', 'products')
                    ->withCount('orders');
            } else {
                // Show only user's templates (default) - both public and private
                $query = Template::where('created_by', auth()->id())
                    ->with('creator')
                    ->withCount('orders');
            }

            // Filter by category if provided
            if ($request->has('category') && $request->category) {
                $query->where('category', $request->category);
            }

            $templates = $query->latest()->get();

            // Format templates for response
            $formattedTemplates = $templates->map(function ($template) {
                $averageRating = \App\Models\TemplateReview::where('template_id', $template->id)
                    ->where('is_approved', true)
                    ->avg('rating') ?? 0;
                $totalReviews = \App\Models\TemplateReview::where('template_id', $template->id)
                    ->where('is_approved', true)
                    ->count();

                $templatePrice = (float) ($template->price ?? 0);
                $displayPrice = $templatePrice;
                if ($template->relationLoaded('products') && $template->products->isNotEmpty()) {
                    $minProductPrice = (float) $template->products->min('price');
                    $displayPrice = $templatePrice + $minProductPrice;
                }

                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'short_description' => $template->short_description,
                    'description' => $template->description,
                    'category' => $template->category,
                    'type' => $template->type ?? '',
                    'price' => $template->price,
                    'display_price' => $displayPrice,
                    'licence' => $template->licence,
                    'page_count' => $template->page_count,
                    'thumbnail_url' => $template->thumbnail_url,
                    'thumbnail_path' => $template->thumbnail_path,
                    'images' => $this->getTemplateImagesUrls($template->images ?? []),
                    'is_active' => $template->is_active,
                    'is_public' => $template->is_public,
                    'created_by' => $template->created_by,
                    'creator_name' => $template->creator->name ?? 'System',
                    'created_at' => $template->created_at->toDateTimeString(),
                    'variables' => $template->variables ?? [],
                    'average_rating' => round($averageRating, 1),
                    'total_reviews' => $totalReviews,
                    'orders_count' => $template->orders_count ?? 0,
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'templates' => $formattedTemplates,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading templates: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error loading templates: '.$e->getMessage(),
                'templates' => [],
            ], 500);
        }
    }

    /**
     * Save template
     */
    public function templatesStore(Request $request)
    {
        try {
            // Only approved designers (or admins) can save public templates
            if ($request->boolean('is_public') && ! auth()->user()->hasRole(['admin', 'designer'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to save public templates. Please apply to become a designer first.',
                ], 403);
            }

            // Base validation rules (always required)
            $rules = [
                'name' => 'required|string|max:255',
                'is_public' => 'required|boolean',
                'pages' => 'required|array|min:1',
                'pages.*' => 'required|string', // Each page is a Fabric.js JSON string
                'thumbnail' => 'nullable|string', // base64 image
                'variables' => 'nullable|array', // Array of variables found in template
                'fonts' => 'nullable|array', // Array of custom fonts used in template
                'images' => 'nullable|array', // Array of base64 images for public templates
                'type' => 'nullable|string|max:50', // Template type: letter, document, flipbook, etc.
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50',
                'short_description' => 'nullable|string|max:200',
                'description' => 'nullable|string|max:1000',
                'category' => 'nullable|string|max:50',
                'price' => 'nullable|numeric|min:0',
                'licence' => 'nullable|string|max:50',
                'is_featured' => 'nullable|boolean',
                'is_product' => 'nullable|boolean',
                'stock_enabled' => 'nullable|boolean',
                'stock_qty' => 'nullable|integer|min:0',
                'selling_price' => 'nullable|numeric|min:0',
                'cost' => 'nullable|numeric|min:0',
                'product_description' => 'nullable|string|max:2000',
                'disable_sheet_selection' => 'nullable|boolean',
                'disable_material_selection' => 'nullable|boolean',
                'disable_envelope_option' => 'nullable|boolean',
            ];

            // Conditional validation for public templates
            if ($request->is_public) {
                $rules['short_description'] = 'required|string|max:200';
                $rules['description'] = 'nullable|string|max:1000';
                $rules['category'] = 'required|string|max:50';
                $rules['price'] = 'required|numeric|min:0';
                $rules['licence'] = 'required|string|max:50';
            }

            // When template as product: price and licence required
            if ($request->boolean('is_product')) {
                $rules['selling_price'] = 'required|numeric|min:0';
                $rules['licence'] = 'required|string|max:50';
            }

            $validated = $request->validate($rules);

            // Extract fonts from pages if not provided
            $fonts = $validated['fonts'] ?? [];
            if (empty($fonts)) {
                $fonts = $this->extractFontsFromPages($validated['pages']);
            }

            // Prepare template data — store all form fields to template table
            // category is NOT NULL in DB; use default when not provided (e.g. checkout design flow)
            $templateData = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'short_description' => $validated['short_description'] ?? null,
                'category' => $validated['category'] ?? 'general',
                'tags' => $validated['tags'] ?? [],
                'type' => $validated['type'] ?? null,
                'price' => isset($validated['price']) ? (float) $validated['price'] : null,
                'licence' => $validated['licence'] ?? null,
                'is_public' => $validated['is_public'],
                'pages' => $validated['pages'],
                'page_count' => count($validated['pages']),
                'thumbnail_path' => null,
                'images' => null, // set below for public, or updated after save
                'variables' => $validated['variables'] ?? [],
                'fonts' => $fonts,
                'is_active' => true,
                'created_by' => auth()->id(),
                'is_product' => $validated['is_product'] ?? false,
                'stock_enabled' => $validated['stock_enabled'] ?? false,
                'stock_qty' => $validated['stock_qty'] ?? null,
                'selling_price' => isset($validated['selling_price']) ? (float) $validated['selling_price'] : null,
                'cost' => isset($validated['cost']) ? (float) $validated['cost'] : null,
                'product_description' => $validated['product_description'] ?? null,
                'disable_sheet_selection' => $validated['disable_sheet_selection'] ?? false,
                'disable_material_selection' => $validated['disable_material_selection'] ?? false,
                'disable_envelope_option' => $validated['disable_envelope_option'] ?? false,
                'is_featured' => $validated['is_featured'] ?? false,
            ];

            // Override with public-only required values when public
            if ($validated['is_public']) {
                $templateData['short_description'] = $validated['short_description'];
                $templateData['description'] = $validated['description'] ?? null;
                $templateData['category'] = $validated['category'];
                $templateData['price'] = (float) $validated['price'];
                $templateData['licence'] = $validated['licence'];
                $templateData['is_featured'] = $validated['is_featured'] ?? false;
            }

            // When template as product is enabled, set price and licence from product section (even if private)
            if ($validated['is_product'] ?? false) {
                if (isset($validated['selling_price']) && $validated['selling_price'] !== null) {
                    $templateData['price'] = (float) $validated['selling_price'];
                }
                if (! empty($validated['licence'])) {
                    $templateData['licence'] = $validated['licence'];
                }
            }

            // Create template in database first (to get the ID)
            $template = Template::create($templateData);

            // Save thumbnail if provided (using template ID)
            if (! empty($validated['thumbnail'])) {
                $thumbnailPath = $this->saveTemplateThumbnail($validated['thumbnail'], 'template_'.$template->id);
                $template->update(['thumbnail_path' => $thumbnailPath]);
            }

            // Save images if provided (for public templates only, using template ID)
            if ($validated['is_public'] && ! empty($validated['images'])) {
                $savedImages = $this->saveTemplateImages($validated['images'], 'template_'.$template->id);
                $template->update(['images' => $savedImages]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Template saved successfully',
                'template_id' => $template->id,
                'template_type' => $template->type ?? '',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error saving template: '.json_encode($e->errors()));

            return response()->json([
                'success' => false,
                'message' => 'Validation failed: '.implode(', ', array_map(function ($errors) {
                    return implode(', ', $errors);
                }, $e->errors())),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error saving template: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save template: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Load a specific template
     */
    public function templatesShow($id, Request $request)
    {
        try {
            // Find template in database
            $template = Template::where(function ($q) use ($id) {
                // Try to find by ID (numeric) or check if it's a legacy session ID
                if (is_numeric($id)) {
                    $q->where('id', $id);
                } else {
                    // For backward compatibility, check if it's a session-based ID
                    // In this case, we'll just try to find by ID first
                    $q->where('id', $id);
                }
            })->with('creator', 'products')->withCount('orders')->first();

            // If not found by ID, try to decode and search (for backward compatibility with session IDs)
            if (! $template && ! is_numeric($id)) {
                $templateId = urldecode($id);
                // Check if it's a legacy session template ID
                // For now, we'll just return not found since we're migrating to database
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Template not found. Please use templates from the database.',
                    ], 404);
                }
                abort(404, 'Template not found');
            }

            if (! $template) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Template not found',
                    ], 404);
                }
                abort(404, 'Template not found');
            }

            // Check if user can access this template
            // For public templates: must be active and public
            // For private templates: must be created by user
            if ($template->is_public) {
                if (! $template->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This template is not available',
                    ], 403);
                }
            } else {
                // Private template - only creator can access
                if ($template->created_by !== auth()->id()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to access this template',
                    ], 403);
                }
            }

            // If not an AJAX/JSON request, return the details view
            if (! $request->expectsJson()) {
                // Load sheet types
                $sheetTypes = \App\Models\SheetType::active()->ordered()->get();

                // Load reviews with users
                $reviews = \App\Models\TemplateReview::where('template_id', $template->id)
                    ->where('is_approved', true)
                    ->with('user')
                    ->latest()
                    ->paginate(5);

                // Load comments with users and replies
                $comments = \App\Models\TemplateComment::where('template_id', $template->id)
                    ->where('is_approved', true)
                    ->whereNull('parent_id')
                    ->with(['user', 'replies.user'])
                    ->latest()
                    ->get();

                // Calculate average rating
                $averageRating = $template->reviews()->avg('rating') ?? 0;
                $totalReviews = $template->reviews()->count();

                $assignedProducts = $template->products;

                return view('design.templates.show', compact('template', 'sheetTypes', 'reviews', 'comments', 'averageRating', 'totalReviews', 'assignedProducts'));
            }

            // Ensure pages are properly formatted
            $pages = $template->pages;
            if (is_array($pages)) {
                // Ensure each page has width and height
                $pages = array_map(function ($pageData) {
                    if (is_string($pageData)) {
                        try {
                            $parsed = json_decode($pageData, true);
                            if (! isset($parsed['width'])) {
                                $parsed['width'] = 800;
                            }
                            if (! isset($parsed['height'])) {
                                $parsed['height'] = 1000;
                            }
                            if (! isset($parsed['backgroundColor']) && isset($parsed['background'])) {
                                $parsed['backgroundColor'] = $parsed['background'];
                            } elseif (! isset($parsed['backgroundColor'])) {
                                $parsed['backgroundColor'] = '#ffffff';
                                $parsed['background'] = '#ffffff';
                            }

                            return json_encode($parsed);
                        } catch (\Exception $e) {
                            return $pageData;
                        }
                    }

                    return $pageData;
                }, $pages);
            }

            return response()->json([
                'success' => true,
                'template' => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'description' => $template->description,
                    'short_description' => $template->short_description,
                    'category' => $template->category,
                    'type' => $template->type ?? '',
                    'price' => $template->price,
                    'licence' => $template->licence,
                    'pages' => $pages,
                    'page_count' => $template->page_count,
                    'thumbnail_url' => $template->thumbnail_url,
                    'thumbnail_path' => $template->thumbnail_path,
                    'images' => $this->getTemplateImagesUrls($template->images ?? []),
                    'is_public' => $template->is_public,
                    'variables' => $template->variables ?? [],
                    'fonts' => $template->fonts ?? [],
                    'created_at' => $template->created_at->toDateTimeString(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading template: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error loading template: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Manage template (owner dashboard for a public template)
     */
    public function templatesManage($id)
    {
        $template = Template::with('creator')->withCount('orders')->findOrFail($id);

        if ((int) $template->created_by !== (int) auth()->id()) {
            abort(403, 'You can only manage your own templates.');
        }

        if (! $template->is_public) {
            return redirect()->route('design.templates.index')->with('info', 'Manage is available for public templates. Use Edit or Use from My Templates.');
        }

        $ordersCount = $template->orders_count ?? 0;
        $reviewsCount = $template->reviews()->count();
        $averageRating = $template->reviews()->avg('rating') ?? 0;
        $sheetTypes = \App\Models\SheetType::active()->ordered()->get();

        $chartDays = 30;
        $chartStart = now()->subDays($chartDays - 1)->startOfDay();
        $ordersByDate = \App\Models\Order::where('template_id', $template->id)
            ->where('created_at', '>=', $chartStart)
            ->get()
            ->groupBy(fn ($o) => $o->created_at->format('Y-m-d'))
            ->map->count();
        $reviewsByDate = $template->reviews()
            ->where('created_at', '>=', $chartStart)
            ->get()
            ->groupBy(fn ($r) => $r->created_at->format('Y-m-d'))
            ->map->count();
        $overviewChartLabels = [];
        $overviewChartOrders = [];
        $overviewChartReviews = [];
        for ($i = $chartDays - 1; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $key = $d->format('Y-m-d');
            $overviewChartLabels[] = $d->format('M j');
            $overviewChartOrders[] = $ordersByDate[$key] ?? 0;
            $overviewChartReviews[] = $reviewsByDate[$key] ?? 0;
        }

        $totalRevenue = (float) \App\Models\Order::where('template_id', $template->id)->sum('total_amount');
        $revenueByDate = \App\Models\Order::where('template_id', $template->id)
            ->where('created_at', '>=', $chartStart)
            ->get()
            ->groupBy(fn ($o) => $o->created_at->format('Y-m-d'))
            ->map(fn ($group) => (float) $group->sum('total_amount'));
        $revenueChartLabels = $overviewChartLabels;
        $revenueChartData = [];
        for ($i = $chartDays - 1; $i >= 0; $i--) {
            $key = now()->subDays($i)->format('Y-m-d');
            $revenueChartData[] = $revenueByDate[$key] ?? 0;
        }

        $pageDimensions = null;
        if ($template->pages && is_array($template->pages) && count($template->pages) > 0) {
            $first = $template->pages[0];
            $decoded = is_string($first) ? json_decode($first, true) : $first;
            if (is_array($decoded) && (isset($decoded['width']) || isset($decoded['height']))) {
                $pageDimensions = [
                    'width' => $decoded['width'] ?? null,
                    'height' => $decoded['height'] ?? null,
                ];
            }
        }

        $assignedProducts = $template->products;
        $allProducts = \App\Models\Product::active()->ordered()->get();

        return view('design.templates.manage', compact('template', 'ordersCount', 'reviewsCount', 'averageRating', 'sheetTypes', 'pageDimensions', 'overviewChartLabels', 'overviewChartOrders', 'overviewChartReviews', 'totalRevenue', 'revenueChartLabels', 'revenueChartData', 'assignedProducts', 'allProducts'));
    }

    /**
     * Assign a product to a template (owner only).
     */
    public function templatesAssignProduct(Request $request, $id)
    {
        $template = Template::findOrFail($id);
        if ((int) $template->created_by !== (int) auth()->id()) {
            abort(403, 'You can only manage your own templates.');
        }
        $request->validate(['product_id' => 'required|exists:products,id']);
        $productId = (int) $request->product_id;
        if (! $template->products()->where('product_id', $productId)->exists()) {
            $nextSort = (int) \DB::table('product_template')->where('template_id', $template->id)->max('sort_order') + 1;
            $template->products()->attach($productId, ['sort_order' => $nextSort]);
        }

        return redirect()->route('design.templates.manage', $id)->with('success', 'Product assigned.');
    }

    /**
     * Unassign a product from a template (owner only).
     */
    public function templatesUnassignProduct($id, $productId)
    {
        $template = Template::findOrFail($id);
        if ((int) $template->created_by !== (int) auth()->id()) {
            abort(403, 'You can only manage your own templates.');
        }
        $template->products()->detach((int) $productId);

        return redirect()->route('design.templates.manage', $id)->with('success', 'Product unassigned.');
    }

    /**
     * Delete a template
     */
    public function templatesDestroy($id, Request $request)
    {
        try {
            // Find template in database
            $template = Template::find($id);

            if (! $template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found',
                ], 404);
            }

            // Check if user can delete this template (only creator or admin)
            if ($template->created_by !== auth()->id() && ! auth()->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this template',
                ], 403);
            }

            // Delete thumbnail if exists
            if ($template->thumbnail_path && Storage::disk('public')->exists($template->thumbnail_path)) {
                Storage::disk('public')->delete($template->thumbnail_path);
            }

            // Delete template from database
            $template->delete();

            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting template: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error deleting template: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show quick use page for template
     */
    public function quickUse($id)
    {
        try {
            $template = Template::findOrFail($id);

            // Check if user can access this template
            // For public templates: must be active and public
            // For private templates: must be created by user
            if ($template->is_public) {
                if (! $template->is_active) {
                    abort(403, 'This template is not available');
                }
            } else {
                // Private template - only creator can access
                if ($template->created_by !== auth()->id()) {
                    abort(403, 'You do not have permission to access this template');
                }
            }

            // Legacy check for backward compatibility
            if (! $template->is_active && $template->created_by !== auth()->id()) {
                abort(403, 'You do not have permission to access this template');
            }

            // Get active sheet types and pricing rules
            $sheetTypes = \App\Models\SheetType::active()->ordered()->inStock()->get();
            $pricingRules = \App\Models\PricingRule::active()->ordered()->get();
            $addresses = AddressBook::where('user_id', auth()->id())->orderBy('contact_name')->get();

            return view('design.templates.quick-use', compact('template', 'sheetTypes', 'pricingRules', 'addresses'));
        } catch (\Exception $e) {
            \Log::error('Error loading template for quick use: '.$e->getMessage());

            return redirect()->route('design.templates.page')->with('error', 'Template not found.');
        }
    }

    /**
     * Show send letter checkout page for template (letter-specific with envelope & addresses)
     */
    public function sendLetterCheckout($id)
    {
        try {
            $template = Template::with('products')->findOrFail($id);

            if ($template->is_public) {
                if (! $template->is_active) {
                    abort(403, 'This template is not available');
                }
            } else {
                if ($template->created_by !== auth()->id()) {
                    abort(403, 'You do not have permission to access this template');
                }
            }

            if (! $template->is_active && $template->created_by !== auth()->id()) {
                abort(403, 'You do not have permission to access this template');
            }

            $sheetTypes = \App\Models\SheetType::active()->ordered()->inStock()->get();
            $envelopeTypes = EnvelopeType::active()->ordered()->inStock()->get();
            $pricingRules = \App\Models\PricingRule::active()->ordered()->get();
            $addressBook = auth()->check()
                ? \App\Models\AddressBook::where('user_id', auth()->id())->orderBy('contact_name')->get()
                : collect();

            $assignedProducts = $template->products->filter(fn ($p) => (int) ($p->stock_quantity ?? 0) > 0)->values();

            return view('design.templates.send-letter-checkout', compact('template', 'sheetTypes', 'envelopeTypes', 'pricingRules', 'addressBook', 'assignedProducts'));
        } catch (\Exception $e) {
            \Log::error('Error loading template for send letter: '.$e->getMessage());

            return redirect()->route('design.index')->with('error', 'Template not found.');
        }
    }

    /**
     * Initiate checkout - store data in session, redirect to payment options
     */
    public function checkoutInit(Request $request)
    {
        $rules = [
            'template_id' => 'required|exists:templates,id',
            'checkout_data' => 'required|string',
            'checkout_from' => 'nullable|string|in:quick-use,send-letter',
        ];

        // Delivery (phone + address) required only for quick-use
        $checkoutFromInput = $request->input('checkout_from');
        if ($checkoutFromInput !== 'send-letter') {
            $rules['phone'] = 'required|string|max:32';
            $rules['address_source'] = 'required|in:saved,manual';
            if ($request->input('address_source') === 'saved') {
                $rules['address_book_id'] = 'required|exists:address_books,id';
            } else {
                $rules['contact_name'] = 'nullable|string|max:255';
                $rules['email'] = 'nullable|email|max:255';
                $rules['address_line1'] = 'nullable|string|max:255';
                $rules['address_line2'] = 'nullable|string|max:255';
                $rules['city'] = 'nullable|string|max:64';
                $rules['state'] = 'nullable|string|max:64';
                $rules['postal_code'] = 'nullable|string|max:20';
                $rules['country'] = 'nullable|string|max:2';
            }
        }

        $validated = $request->validate($rules);

        $template = Template::findOrFail($request->template_id);

        // Verify user can access template
        if (! $template->is_public && $template->created_by !== auth()->id()) {
            return redirect()->route('design.templates.page')->with('error', 'You do not have permission to use this template.');
        }

        $checkoutData = json_decode($request->checkout_data, true);
        if (! $checkoutData) {
            return redirect()->back()->with('error', 'Invalid checkout data.');
        }

        $quantity = (int) ($checkoutData['quantity'] ?? 1) ?: 1;
        $pageCount = (int) ($template->page_count ?? 0);
        $sheetsNeeded = max(0, $pageCount * $quantity);

        $sheetTypeSlug = $checkoutData['sheet_type'] ?? null;
        if ($sheetsNeeded > 0 && ! empty($sheetTypeSlug) && is_string($sheetTypeSlug)) {
            if (! \App\Models\SheetType::query()->active()->where('slug', $sheetTypeSlug)->where('stock_quantity', '>=', $sheetsNeeded)->exists()) {
                return redirect()->back()->with('error', 'The selected sheet type is unavailable or there is not enough stock for this order.');
            }
        }

        if (! empty($checkoutData['is_letter'])) {
            $envSlug = $checkoutData['envelope_cover'] ?? null;
            if (! empty($envSlug) && is_string($envSlug)) {
                if (! EnvelopeType::query()->active()->where('slug', $envSlug)->where('stock_quantity', '>=', $quantity)->exists()) {
                    return redirect()->back()->with('error', 'The selected envelope is unavailable or there is not enough stock for this quantity.');
                }
            }
        }

        $productId = isset($checkoutData['product_id']) ? (int) $checkoutData['product_id'] : null;
        if ($productId > 0) {
            if (! $template->products()->where('products.id', $productId)->exists()) {
                return redirect()->back()->with('error', 'The selected product is not available for this template.');
            }
            if (! Product::query()->whereKey($productId)->where('is_active', true)->where('stock_quantity', '>=', $quantity)->exists()) {
                return redirect()->back()->with('error', 'The selected product is unavailable or there is not enough stock for this quantity.');
            }
        }

        $templatePriceOnce = (float) ($template->price ?? 0);
        $sheetCost = (float) preg_replace('/[^0-9.]/', '', $checkoutData['sheet_cost'] ?? '0');
        $materialCost = (float) preg_replace('/[^0-9.]/', '', $checkoutData['material_cost'] ?? '0');
        $envelopeCost = (float) preg_replace('/[^0-9.]/', '', $checkoutData['envelope_cost'] ?? '0');

        // When send-letter has attached product: template once + (product × quantity)
        $productPrice = isset($checkoutData['product_price']) ? (float) $checkoutData['product_price'] : null;
        if (! empty($checkoutData['is_letter']) && $productId && $productPrice !== null && $productPrice >= 0) {
            $letterCost = $templatePriceOnce + ($productPrice * $quantity);
            $checkoutData['template_cost'] = format_price($letterCost);
            $totalAmount = $letterCost + $sheetCost + $materialCost + $envelopeCost;
        } else {
            $letterCost = $templatePriceOnce;
            $checkoutData['template_cost'] = format_price($letterCost);
            $totalAmount = $letterCost + $sheetCost + $materialCost;
            if (! empty($checkoutData['is_letter'])) {
                $totalAmount += $envelopeCost;
            }
        }
        $checkoutData['total_cost'] = format_price($totalAmount);

        if (! empty($checkoutData['schedule_letter'])) {
            if (! auth()->check()) {
                return redirect()->route('login')->with('error', 'Please sign in to schedule a letter for later.');
            }
            if (empty($checkoutData['schedule_send_at'])) {
                return redirect()->back()->with('error', 'Please choose a date and time to schedule your letter.');
            }
            try {
                $sendAt = Carbon::parse($checkoutData['schedule_send_at']);
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', 'Invalid schedule date.');
            }
            if ($sendAt->lte(now()->addMinutes(5))) {
                return redirect()->back()->with('error', 'Schedule time must be at least 5 minutes from now.');
            }
            if ($sendAt->gt(now()->addMonths(12))) {
                return redirect()->back()->with('error', 'You can only schedule up to 12 months ahead.');
            }
            $checkoutData['schedule_send_at'] = $sendAt->toIso8601String();
        } else {
            unset($checkoutData['schedule_send_at'], $checkoutData['schedule_letter']);
        }

        // For letter orders: ensure items array has address and variables per item
        if (! empty($checkoutData['is_letter']) && isset($checkoutData['items']) && is_array($checkoutData['items'])) {
            $checkoutData['items'] = array_values(array_map(function ($item) {
                return [
                    'address' => $item['address'] ?? [],
                    'variables' => $item['variables'] ?? [],
                ] + $item;
            }, $checkoutData['items']));
        }

        $checkoutFrom = $request->input('checkout_from');
        if (! $checkoutFrom && ! empty($checkoutData['is_letter'])) {
            $checkoutFrom = 'send-letter';
        }
        if (! $checkoutFrom) {
            $checkoutFrom = 'quick-use';
        }

        $delivery = null;
        if ($checkoutFrom !== 'send-letter') {
            $delivery = [
                'phone' => $validated['phone'] ?? null,
                'contact_name' => null,
                'email' => null,
                'address_line1' => null,
                'address_line2' => null,
                'city' => null,
                'state' => null,
                'postal_code' => null,
                'country' => null,
            ];
            if (($validated['address_source'] ?? '') === 'saved' && ! empty($validated['address_book_id'])) {
                $addr = AddressBook::where('user_id', auth()->id())->find($validated['address_book_id']);
                if ($addr) {
                    $delivery['contact_name'] = $addr->contact_name;
                    $delivery['email'] = $addr->email;
                    $delivery['address_line1'] = $addr->address_line1;
                    $delivery['address_line2'] = $addr->address_line2;
                    $delivery['city'] = $addr->city;
                    $delivery['state'] = $addr->state;
                    $delivery['postal_code'] = $addr->postal_code;
                    $delivery['country'] = $addr->country;
                }
            } else {
                $delivery['contact_name'] = $validated['contact_name'] ?? null;
                $delivery['email'] = $validated['email'] ?? null;
                $delivery['address_line1'] = $validated['address_line1'] ?? null;
                $delivery['address_line2'] = $validated['address_line2'] ?? null;
                $delivery['city'] = $validated['city'] ?? null;
                $delivery['state'] = $validated['state'] ?? null;
                $delivery['postal_code'] = $validated['postal_code'] ?? null;
                $delivery['country'] = $validated['country'] ?? null;
            }
        }

        $sessionCheckout = [
            'template_id' => $template->id,
            'template_name' => $template->name,
            'template_page_count' => $template->page_count ?? 0,
            'checkout_data' => $checkoutData,
            'checkout_from' => $checkoutFrom,
        ];
        if ($delivery !== null) {
            $sessionCheckout['delivery'] = $delivery;
        }
        session(['checkout' => $sessionCheckout]);

        return redirect()->route('design.checkout.paymentOptions');
    }

    /**
     * Payment options selection page
     */
    public function paymentOptions(Request $request)
    {
        $checkout = session('checkout');
        if (! $checkout) {
            return redirect()->route('design.templates.page')->with('error', 'Checkout session expired. Please start again.');
        }

        $template = Template::find($checkout['template_id']);
        if (! $template) {
            session()->forget('checkout');

            return redirect()->route('design.templates.page')->with('error', 'Template not found.');
        }

        $paymentMethods = app(PaymentGatewayRepository::class)->getPaymentMethodsForCheckout();

        $checkoutDataOpts = $checkout['checkout_data'] ?? [];
        $isScheduledSendLetter = ! empty($checkoutDataOpts['schedule_letter']) && ! empty($checkoutDataOpts['is_letter']);
        if ($isScheduledSendLetter) {
            $blockedForSchedule = ['payhere', 'paypal', 'bank_transfer'];
            $paymentMethods = array_values(array_filter($paymentMethods, function ($pm) use ($blockedForSchedule) {
                return ! in_array($pm['id'] ?? '', $blockedForSchedule, true);
            }));
        }

        $totalCost = $checkout['checkout_data']['total_cost'] ?? '0';
        $totalAmount = (float) preg_replace('/[^0-9.]/', '', $totalCost);
        $userBalance = (float) (auth()->user()->balance ?? 0);

        if ($totalAmount > 0) {
            $canUseCredit = $userBalance >= $totalAmount;
            array_unshift($paymentMethods, [
                'id' => 'platform_credit',
                'name' => 'Deduct from Credit',
                'icon' => 'fa-wallet',
                'description' => $canUseCredit
                    ? 'Pay with your balance ('.format_price($userBalance).' available)'
                    : 'Insufficient balance. You have '.format_price($userBalance).' (need '.format_price($totalAmount).')',
                'disabled' => ! $canUseCredit,
            ]);
        }

        if (empty($paymentMethods)) {
            return redirect()->route('design.templates.page')->with('error', 'No payment methods are currently available. Please contact support.');
        }

        $checkoutFrom = $checkout['checkout_from'] ?? 'quick-use';
        $checkoutBackUrl = $checkoutFrom === 'send-letter'
            ? route('design.templates.sendLetter', $template->id)
            : route('design.templates.quickUse', $template->id);

        return view('design.checkout.payment-options', compact('checkout', 'template', 'paymentMethods', 'checkoutBackUrl'));
    }

    /**
     * Payment gateway page (selected method)
     */
    public function paymentGateway(Request $request)
    {
        $checkout = session('checkout');
        if (! $checkout) {
            return redirect()->route('design.templates.page')->with('error', 'Checkout session expired. Please start again.');
        }

        $repository = app(PaymentGatewayRepository::class);
        $paymentMethods = $repository->getPaymentMethodsForCheckout();

        $totalCost = $checkout['checkout_data']['total_cost'] ?? '0';
        $totalAmount = (float) preg_replace('/[^0-9.]/', '', $totalCost);
        $userBalance = (float) (auth()->user()->balance ?? 0);
        if ($userBalance >= $totalAmount && $totalAmount > 0) {
            array_unshift($paymentMethods, ['id' => 'platform_credit', 'name' => 'Deduct from Credit', 'icon' => 'fa-wallet', 'description' => '']);
        }

        $checkoutDataGw = $checkout['checkout_data'] ?? [];
        if (! empty($checkoutDataGw['schedule_letter']) && ! empty($checkoutDataGw['is_letter'])) {
            $blockedForSchedule = ['payhere', 'paypal', 'bank_transfer'];
            $paymentMethods = array_values(array_filter($paymentMethods, function ($pm) use ($blockedForSchedule) {
                return ! in_array($pm['id'] ?? '', $blockedForSchedule, true);
            }));
        }

        $allowedIds = array_column($paymentMethods, 'id');

        $method = $request->get('method', $allowedIds[0] ?? 'stripe');
        if (! in_array($method, $allowedIds)) {
            $method = $allowedIds[0] ?? 'stripe';
        }

        $template = Template::find($checkout['template_id']);
        if (! $template) {
            session()->forget('checkout');

            return redirect()->route('design.templates.page')->with('error', 'Template not found.');
        }

        $stripePublishableKey = '';
        if ($method === 'stripe') {
            $gateway = $repository->get('stripe');
            if ($gateway && $gateway->isEnabled()) {
                $stripePublishableKey = $gateway->getPublishableKey();
            }
        }

        return view('design.checkout.payment-gateway', compact('checkout', 'template', 'method', 'stripePublishableKey'));
    }

    /**
     * Create Stripe PaymentIntent (AJAX)
     */
    public function createPaymentIntent(Request $request)
    {
        $checkout = session('checkout');
        if (! $checkout) {
            return response()->json(['success' => false, 'error' => 'Session expired'], 400);
        }

        $checkoutData = $checkout['checkout_data'] ?? [];
        $totalCost = $checkoutData['total_cost'] ?? '0';
        $amount = (float) preg_replace('/[^0-9.]/', '', $totalCost);
        $amountInCents = (int) round($amount * 100);

        if ($amountInCents < 50) {
            return response()->json(['success' => false, 'error' => 'Minimum amount is 0.50'], 400);
        }

        $currency = strtolower(Setting::get('default_currency', 'usd'));
        $repository = app(PaymentGatewayRepository::class);
        $gateway = $repository->get('stripe');

        if (! $gateway || ! $gateway->isEnabled()) {
            return response()->json(['success' => false, 'error' => 'Stripe is not configured'], 400);
        }

        $result = $gateway->createPaymentIntent($amountInCents, $currency, [
            'user_id' => (string) auth()->id(),
            'template_id' => (string) ($checkout['template_id'] ?? ''),
        ]);

        if (! $result['success']) {
            return response()->json(['success' => false, 'error' => $result['error'] ?? 'Failed to create payment'], 400);
        }

        return response()->json([
            'success' => true,
            'clientSecret' => $result['client_secret'],
            'paymentIntentId' => $result['payment_intent_id'],
        ]);
    }

    /**
     * Process payment
     */
    public function processPayment(Request $request)
    {
        $checkout = session('checkout');
        if (! $checkout) {
            return redirect()->route('design.templates.page')->with('error', 'Checkout session expired.');
        }

        $repository = app(PaymentGatewayRepository::class);
        $paymentMethods = $repository->getPaymentMethodsForCheckout();
        $allowedIds = array_column($paymentMethods, 'id');
        $totalCost = $checkout['checkout_data']['total_cost'] ?? '0';
        $totalAmount = (float) preg_replace('/[^0-9.]/', '', $totalCost);
        $userBalance = (float) (auth()->user()->balance ?? 0);
        if ($userBalance >= $totalAmount && $totalAmount > 0) {
            $allowedIds[] = 'platform_credit';
        }

        $request->validate([
            'payment_method' => 'required|in:'.implode(',', $allowedIds),
        ]);

        $checkoutData = $checkout['checkout_data'] ?? [];
        $quantity = (int) ($checkoutData['quantity'] ?? 1);

        $stockService = app(DesignCheckoutStockService::class);
        $stockContext = [
            'template_id' => $checkout['template_id'] ?? null,
            'template_page_count' => $checkout['template_page_count'] ?? null,
            'checkout_data' => $checkoutData,
        ];
        try {
            $stockService->assertAvailable($stockContext);
        } catch (InsufficientStockException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $isScheduledLetter = ! empty($checkoutData['schedule_letter'])
            && ! empty($checkoutData['is_letter'])
            && ! empty($checkoutData['schedule_send_at']);

        $paymentMethod = $request->payment_method;

        if ($isScheduledLetter && ! in_array($paymentMethod, ['platform_credit', 'stripe'], true)) {
            return redirect()->back()->with('error', 'Scheduled letters can only be paid with platform credits or card (Stripe).');
        }

        $scheduleSendAt = null;
        if ($isScheduledLetter) {
            try {
                $scheduleSendAt = Carbon::parse($checkoutData['schedule_send_at']);
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', 'Invalid schedule date. Please return to send letter and try again.');
            }
            if ($scheduleSendAt->lte(now()->addMinutes(5))) {
                return redirect()->back()->with('error', 'Your schedule time is no longer valid. Please start checkout again from send letter.');
            }
        }

        if ($paymentMethod === 'platform_credit') {
            if ($userBalance < $totalAmount) {
                return redirect()->back()->with('error', 'Insufficient credits. Your balance is '.format_price($userBalance).'. Please top up or choose another payment method.');
            }
            auth()->user()->decrement('balance', $totalAmount);
            \App\Models\CreditTransaction::create([
                'user_id' => auth()->id(),
                'amount' => -$totalAmount,
                'type' => 'purchase',
                'balance_after' => auth()->user()->fresh()->balance,
                'payment_method' => 'platform_credit',
                'reference' => $isScheduledLetter ? 'scheduled_letter_prepayment' : 'order_checkout',
                'description' => ($isScheduledLetter ? 'Scheduled letter prepayment — ' : 'Design checkout - ').($checkout['template_name'] ?? 'Order'),
            ]);
        } elseif ($paymentMethod === 'stripe') {
            $request->validate([
                'payment_intent_id' => 'required|string',
            ]);

            $gateway = $repository->get('stripe');
            if (! $gateway || ! $gateway->isEnabled()) {
                return redirect()->back()->with('error', 'Stripe payment is not available.');
            }

            $result = $gateway->confirmPayment($request->payment_intent_id);
            if (! $result['success'] || ($result['status'] ?? '') !== 'succeeded') {
                return redirect()->back()->with('error', $result['error'] ?? 'Payment could not be confirmed. Please try again.');
            }
        }

        // Merge delivery address and phone into checkout_data for the order
        if (! empty($checkout['delivery'])) {
            $checkoutData['delivery'] = $checkout['delivery'];
        }

        $stockContext['checkout_data'] = $checkoutData;

        if ($isScheduledLetter) {
            $checkoutDataForSchedule = $checkoutData;
            $checkoutDataForSchedule['scheduled_prepaid'] = true;
            $checkoutDataForSchedule['scheduled_payment_method'] = $paymentMethod;

            $firstAddr = [];
            if (! empty($checkoutDataForSchedule['items'][0]) && is_array($checkoutDataForSchedule['items'][0])) {
                $firstAddr = $checkoutDataForSchedule['items'][0]['address'] ?? [];
            }
            if (! is_array($firstAddr)) {
                $firstAddr = [];
            }
            $recipientSnapshot = [
                'contact_name' => $firstAddr['name'] ?? '',
                'email' => $firstAddr['email'] ?? null,
                'phone' => $firstAddr['phone'] ?? null,
                'address_line1' => $firstAddr['line1'] ?? '',
                'address_line2' => $firstAddr['line2'] ?? '',
                'city' => $firstAddr['city'] ?? '',
                'state' => $firstAddr['state'] ?? '',
                'postal_code' => $firstAddr['zip'] ?? '',
                'country' => $firstAddr['country'] ?? '',
            ];

            $scheduled = ScheduledMail::create([
                'user_id' => auth()->id(),
                'template_id' => $checkout['template_id'],
                'template_name' => $checkout['template_name'],
                'address_book_id' => null,
                'recipient_snapshot' => $recipientSnapshot,
                'send_at' => $scheduleSendAt,
                'credit_amount' => $totalAmount,
                'checkout_data' => $checkoutDataForSchedule,
                'quantity' => $quantity,
                'status' => 'pending',
            ]);

            if (auth()->check() && ! empty($checkoutData['items']) && is_array($checkoutData['items'])) {
                try {
                    $this->saveOrderAddressesToAddressBook(auth()->id(), $checkoutData['items'], null, 'Scheduled letter #'.$scheduled->id);
                } catch (\Throwable $e) {
                    \Log::warning('Save scheduled letter addresses to address book failed', ['scheduled_mail_id' => $scheduled->id, 'message' => $e->getMessage()]);
                }
            }

            session()->forget('checkout');

            $when = $scheduleSendAt->clone()->timezone(config('app.timezone'))->format('M j, Y g:i A');

            return redirect()->route('enterprise.schedule-mail')
                ->with('success', 'Payment received. Your letter is scheduled for '.$when.' ('.config('app.timezone').'). Inventory is allocated when it is sent.');
        }

        try {
            $order = DB::transaction(function () use ($stockService, $stockContext, $checkout, $checkoutData, $quantity, $totalAmount, $paymentMethod) {
                $stockService->deduct($stockContext);

                return Order::create([
                    'user_id' => auth()->id(),
                    'template_id' => $checkout['template_id'],
                    'template_name' => $checkout['template_name'],
                    'quantity' => $quantity,
                    'total_amount' => $totalAmount,
                    'payment_method' => $paymentMethod,
                    'status' => 'completed',
                    'delivery_status' => 'pending',
                    'checkout_data' => $checkoutData,
                ]);
            });
        } catch (InsufficientStockException $e) {
            if ($paymentMethod === 'platform_credit') {
                auth()->user()->increment('balance', $totalAmount);
                CreditTransaction::create([
                    'user_id' => auth()->id(),
                    'amount' => $totalAmount,
                    'type' => 'refund',
                    'balance_after' => auth()->user()->fresh()->balance,
                    'payment_method' => 'platform_credit',
                    'reference' => 'order_checkout_stock_reversal',
                    'description' => 'Credits restored — inventory unavailable: '.($checkout['template_name'] ?? 'Order'),
                ]);
            } else {
                \Log::critical('Design checkout: payment succeeded but stock deduction failed', [
                    'user_id' => auth()->id(),
                    'payment_method' => $paymentMethod,
                    'message' => $e->getMessage(),
                ]);
            }

            return redirect()->route('design.templates.page')->with('error', $e->getMessage());
        }

        if (auth()->check() && ! empty($checkoutData['is_letter']) && isset($checkoutData['items']) && is_array($checkoutData['items'])) {
            try {
                $this->saveOrderAddressesToAddressBook(auth()->id(), $checkoutData['items'], $order->id);
            } catch (\Throwable $e) {
                \Log::warning('Save order addresses to address book failed', ['order_id' => $order->id, 'message' => $e->getMessage()]);
            }
        }

        session()->forget('checkout');

        $redirect = redirect()->route('design.templates.page')
            ->with('success', 'Order received! Thank you for your purchase.');
        if ($order->template_id && $order->template_name) {
            $redirect->with('order_review_prompt', [
                'order_id' => $order->id,
                'template_id' => $order->template_id,
                'template_name' => $order->template_name,
            ]);
        }

        return $redirect;
    }

    /**
     * Save unique recipient addresses from a completed letter order to the user's address book.
     */
    private function saveOrderAddressesToAddressBook(int $userId, array $items, ?int $orderId = null, ?string $sourceLabel = null): void
    {
        $seen = [];
        $labelPrefix = $sourceLabel ?? ($orderId !== null ? 'From order #'.$orderId : 'From checkout');
        foreach ($items as $item) {
            $addr = $item['address'] ?? null;
            if (! is_array($addr)) {
                continue;
            }
            $name = trim($addr['name'] ?? '');
            $line1 = trim($addr['line1'] ?? '');
            $line2 = trim($addr['line2'] ?? '');
            $city = trim($addr['city'] ?? '');
            $state = trim($addr['state'] ?? '');
            $zip = trim($addr['zip'] ?? '');
            $country = trim($addr['country'] ?? '');

            if ($name === '' && $line1 === '' && $city === '') {
                continue;
            }
            $fallback = $orderId !== null ? 'Recipient (Order #'.$orderId.')' : 'Recipient';
            $contactName = $name !== '' ? $name : $fallback;
            $key = $contactName.'|'.$line1.'|'.$city.'|'.$zip;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $exists = AddressBook::where('user_id', $userId)
                ->where('contact_name', $contactName)
                ->where('address_line1', $line1)
                ->where('city', $city)
                ->where('postal_code', $zip)
                ->exists();
            if ($exists) {
                continue;
            }

            AddressBook::create([
                'user_id' => $userId,
                'label' => $labelPrefix,
                'contact_name' => $contactName,
                'email' => null,
                'phone' => null,
                'address_line1' => $line1,
                'address_line2' => $line2,
                'city' => $city,
                'state' => $state,
                'postal_code' => $zip,
                'country' => $country,
            ]);
        }
    }

    /**
     * Submit a template review (after order). User must have a completed order for this template.
     */
    public function submitTemplateReview(Request $request)
    {
        $valid = $request->validate([
            'template_id' => 'required|exists:templates,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:2000',
        ]);

        $userId = auth()->id();
        $templateId = (int) $valid['template_id'];

        $hasOrder = Order::where('user_id', $userId)
            ->where('template_id', $templateId)
            ->where('status', 'completed')
            ->exists();
        if (! $hasOrder) {
            return response()->json(['success' => false, 'message' => 'You can only review templates you have ordered.'], 403);
        }

        $exists = TemplateReview::where('template_id', $templateId)->where('user_id', $userId)->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'You have already reviewed this template.'], 422);
        }

        TemplateReview::create([
            'template_id' => $templateId,
            'user_id' => $userId,
            'rating' => (int) $valid['rating'],
            'review' => $valid['review'] ?? null,
            'is_approved' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Thank you for your review!']);
    }

    /**
     * Submit a platform testimonial (user-submitted; admin can approve to show on site).
     */
    public function submitTestimonial(Request $request)
    {
        $valid = $request->validate([
            'content' => 'required|string|min:10|max:2000',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $user = auth()->user();
        Testimonial::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'role' => null,
            'content' => $valid['content'],
            'rating' => (int) $valid['rating'],
            'sort_order' => 0,
            'is_active' => false, // Admin approves to show on home
        ]);

        return response()->json(['success' => true, 'message' => 'Thank you for your testimonial! It may be featured on our site after review.']);
    }

    /**
     * Save template images
     */
    private function saveTemplateImages(array $base64Images, $prefix)
    {
        $savedImages = [];

        try {
            foreach ($base64Images as $index => $base64) {
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
                    $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));
                    $extension = $matches[1] ?? 'png';
                    $filename = 'templates/'.$prefix.'_image_'.($index + 1).'.'.$extension;

                    Storage::disk('public')->put($filename, $imageData);
                    $savedImages[] = $filename;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error saving template images: '.$e->getMessage());
        }

        return $savedImages;
    }

    /**
     * Save template thumbnail
     */
    private function saveTemplateThumbnail($base64, $templateId)
    {
        try {
            // Remove data:image/png;base64, prefix if present
            $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
            $imageData = base64_decode($base64);

            $filename = 'templates/'.$templateId.'_thumbnail.png';
            Storage::disk('public')->put($filename, $imageData);

            return $filename;
        } catch (\Exception $e) {
            \Log::error('Error saving template thumbnail: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get template images URLs
     */
    private function getTemplateImagesUrls(array $imagePaths)
    {
        $urls = [];
        foreach ($imagePaths as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                $urls[] = Storage::disk('public')->url($path);
            }
        }

        return $urls;
    }

    /**
     * Extract custom fonts from template pages
     */
    private function extractFontsFromPages($pages)
    {
        $fonts = [];
        $defaultFonts = ['Arial', 'Helvetica', 'Times New Roman', 'Courier New', 'Verdana', 'Georgia'];

        foreach ($pages as $pageData) {
            try {
                $pageJson = is_string($pageData) ? json_decode($pageData, true) : $pageData;

                if (isset($pageJson['objects']) && is_array($pageJson['objects'])) {
                    foreach ($pageJson['objects'] as $object) {
                        $textTypes = ['text', 'textbox', 'i-text'];
                        if (isset($object['type']) && in_array($object['type'], $textTypes, true) && isset($object['fontFamily'])) {
                            $fontFamily = $object['fontFamily'];

                            // Skip default fonts
                            if (! in_array($fontFamily, $defaultFonts)) {
                                // Check if font already exists in fonts array
                                $fontExists = false;
                                foreach ($fonts as $font) {
                                    if (isset($font['name']) && $font['name'] === $fontFamily) {
                                        $fontExists = true;
                                        break;
                                    }
                                }

                                if (! $fontExists) {
                                    // Try to find font in user's font library
                                    $userId = auth()->id();
                                    $libraryPath = 'font-library/'.$userId;

                                    if (Storage::disk('public')->exists($libraryPath)) {
                                        $files = Storage::disk('public')->files($libraryPath);
                                        foreach ($files as $file) {
                                            if (preg_match('/\.(ttf|otf|woff|woff2|eot)$/i', $file)) {
                                                $fontName = pathinfo(basename($file), PATHINFO_FILENAME);
                                                $fontName = preg_replace('/[_\d-]+/', ' ', $fontName);
                                                $fontName = ucwords(trim($fontName));

                                                if ($fontName === $fontFamily) {
                                                    $fonts[] = [
                                                        'name' => $fontFamily,
                                                        'path' => $file,
                                                        'url' => Storage::disk('public')->url($file),
                                                        'filename' => basename($file),
                                                        'extension' => pathinfo($file, PATHINFO_EXTENSION),
                                                    ];
                                                    $fontExists = true;
                                                    break;
                                                }
                                            }
                                        }
                                    }

                                    if (! $fontExists) {
                                        $designFont = DesignFont::query()
                                            ->active()
                                            ->where('name', $fontFamily)
                                            ->first();
                                        if ($designFont && Storage::disk('public')->exists($designFont->stored_path)) {
                                            $fonts[] = $designFont->toExportPayload();
                                            $fontExists = true;
                                        }
                                    }

                                    // If font not found in library, still add it (might be from another source)
                                    if (! $fontExists) {
                                        $fonts[] = [
                                            'name' => $fontFamily,
                                            'path' => null,
                                            'url' => null,
                                            'filename' => null,
                                            'extension' => null,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error extracting fonts from page: '.$e->getMessage());

                continue;
            }
        }

        return $fonts;
    }

    /**
     * Get AI Content Template by ID (for design editor)
     */
    public function aiContentTemplateShow($id)
    {
        $template = AiContentTemplate::active()->findOrFail($id);

        return response()->json([
            'id' => $template->id,
            'name' => $template->name,
            'description' => $template->description,
            'prompt' => $template->prompt,
            'fields' => $template->fields ?? [],
            'editor_json' => $template->editor_json ?? null,
        ]);
    }

    /**
     * Show field form for AI Content Template
     */
    public function aiContentTemplateForm($id)
    {
        $template = AiContentTemplate::active()->findOrFail($id);

        $aiGenerationsForTemplate = collect();
        $aiGenerationsRecent = collect();
        if (auth()->check()) {
            $aiGenerationsForTemplate = AiContentGeneration::query()
                ->where('user_id', auth()->id())
                ->where('ai_content_template_id', $template->id)
                ->orderByDesc('id')
                ->limit(15)
                ->get();
            $aiGenerationsRecent = AiContentGeneration::query()
                ->where('user_id', auth()->id())
                ->with('aiContentTemplate')
                ->orderByDesc('id')
                ->limit(15)
                ->get();
        }

        return view('design.ai-content-templates.form', compact(
            'template',
            'aiGenerationsForTemplate',
            'aiGenerationsRecent'
        ));
    }

    /**
     * Rehydrate a saved AI generation into session and open the multi-page editor.
     */
    public function openAiContentGeneration(Request $request, AiContentGeneration $aiContentGeneration)
    {
        abort_unless((int) $aiContentGeneration->user_id === (int) auth()->id(), 403);

        $designs = $request->session()->get('user_designs', []);
        $sid = $aiContentGeneration->design_session_id;
        $designs[$sid] = [
            'id' => $sid,
            'name' => $aiContentGeneration->name,
            'pages' => $aiContentGeneration->pages,
            'is_multi_page' => $aiContentGeneration->is_multi_page,
            'page_count' => $aiContentGeneration->page_count,
            'thumbnail' => $aiContentGeneration->thumbnail,
            'type' => $aiContentGeneration->type,
            'created_at' => $aiContentGeneration->created_at?->toDateTimeString() ?? now()->toDateTimeString(),
        ];
        $request->session()->put('user_designs', $designs);

        return redirect()->route('design.create', ['load' => $sid, 'multi' => 'true']);
    }

    /**
     * Generate content from AI Content Template using Gemini Pro
     */
    public function generateAiContentFromTemplate(Request $request, $id)
    {
        $template = AiContentTemplate::active()->findOrFail($id);
        $fields = $template->fields ?? [];

        $rules = [];
        foreach ($fields as $f) {
            $key = $f['key'] ?? '';
            if ($key) {
                $rules['field_'.$key] = 'nullable|string|max:2000';
            }
        }
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->route('design.aiContentTemplates.form', $id)
                ->withErrors($validator)
                ->withInput();
        }

        $fieldValues = [];
        foreach ($fields as $f) {
            $key = $f['key'] ?? null;
            if ($key) {
                $fieldValues[$key] = $request->input('field_'.$key, '');
            }
        }

        $prompt = $template->prompt;
        foreach ($fieldValues as $key => $value) {
            $prompt = str_replace(['{{'.$key.'}}', '{{ '.$key.' }}'], $value, $prompt);
        }

        $pageCount = $template->resolvePageCount($prompt);

        $apiKey = Setting::get('gemini_api_key') ?: config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        if (! $apiKey) {
            return redirect()->route('design.aiContentTemplates.form', $id)
                ->with('error', 'Gemini API key is not configured. Please add GEMINI_API_KEY to .env or in Admin → Settings.')
                ->withInput();
        }

        $minCreditCost = AiContentCreditService::computeCost(null);
        if ($minCreditCost > 0) {
            if (! auth()->check()) {
                return redirect()->route('login')->with('error', 'Please log in to generate AI content.');
            }
            $userBalance = (float) (auth()->user()->balance ?? 0);
            if ($userBalance < $minCreditCost) {
                return redirect()->route('design.aiContentTemplates.form', $id)
                    ->with('error', 'Insufficient credits. Required at least '.format_price($minCreditCost).'. Your balance: '.format_price($userBalance).'. Please top up in Credits.')
                    ->withInput();
            }
        }

        // Always queue: avoids gateway timeouts, matches long-running Gemini jobs, credits apply in the job.
        $token = Str::random(64);
        $userId = auth()->check() ? auth()->id() : null;
        GenerateAiContentFromTemplateJob::dispatch($id, $fieldValues, $token, $userId);

        $info = $pageCount > 1
            ? 'Your '.$pageCount.'-page design is generating in the background. Keep this tab open—you will be redirected when it is ready.'
            : 'Your design is generating in the background. Keep this tab open—you will be redirected when it is ready.';

        return redirect()->route('design.aiContentTemplates.pending', ['token' => $token])
            ->with('info', $info);
    }

    /**
     * Pending page: show "generation in queue" and poll for result
     */
    public function aiContentTemplatePending(Request $request)
    {
        $token = $request->query('token');
        if (! $token) {
            return redirect()->route('design.templates.index')->with('error', 'Missing token.');
        }

        return view('design.ai-content-templates.pending', ['token' => $token]);
    }

    /**
     * Poll endpoint: return result when job is done, or pending. When ready, add design to session and return redirect URL.
     */
    public function aiContentTemplateResult(Request $request, string $token)
    {
        $key = 'ai_content_result_'.$token;
        $result = Cache::get($key);

        if ($result === null) {
            return response()->json(['status' => 'pending']);
        }

        if (isset($result['error'])) {
            Cache::forget($key);

            return response()->json(['success' => false, 'error' => $result['error']]);
        }

        if (! isset($result['design_id'], $result['pages'])) {
            Cache::forget($key);

            return response()->json(['success' => false, 'error' => 'Invalid result.']);
        }

        $designs = $request->session()->get('user_designs', []);
        $designs[$result['design_id']] = [
            'id' => $result['design_id'],
            'name' => $result['name'] ?? 'Generated design',
            'pages' => $result['pages'],
            'is_multi_page' => $result['is_multi_page'] ?? true,
            'page_count' => $result['page_count'] ?? 1,
            'thumbnail' => $result['thumbnail'] ?? null,
            'type' => $result['type'] ?? 'document',
            'created_at' => $result['created_at'] ?? now()->toDateTimeString(),
        ];
        $request->session()->put('user_designs', $designs);
        Cache::forget($key);

        $redirectUrl = route('design.create', ['load' => $result['design_id'], 'multi' => 'true']);

        return response()->json(['success' => true, 'redirect' => $redirectUrl]);
    }

    /**
     * Call Gemini API to generate design
     */
    protected function generateDesignWithGemini(string $prompt, int $width, int $height, string $apiKey): array
    {
        $systemPrompt = $this->buildGeminiDesignPrompt($width, $height);
        $fullPrompt = 'Create an amazing design for: '.$prompt."\n\n".$systemPrompt;

        $model = Setting::get('gemini_model') ?: env('GEMINI_MODEL', 'gemini-2.5-flash');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent?key='.urlencode($apiKey);
        // Keep below typical nginx/proxy 60s gateway timeout so we return our error page, not 504
        $timeout = min(50, (int) (env('GEMINI_REQUEST_TIMEOUT') ?: 50));
        set_time_limit($timeout + 15);
        $response = Http::timeout($timeout)
            ->post($url, [
                'contents' => [
                    ['parts' => [['text' => $fullPrompt]]],
                ],
                'generationConfig' => [
                    'temperature' => 0.75,
                    'maxOutputTokens' => 16384,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if (! $response->successful()) {
            $body = $response->json();
            $msg = $body['error']['message'] ?? $response->body();
            throw new \RuntimeException('Gemini API error: '.$msg);
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (! $text) {
            throw new \RuntimeException('Invalid response from Gemini');
        }

        $usage = $this->extractGeminiUsageFromResponse($data);

        $designData = $this->extractJsonFromAiResponse($text);
        if ($designData === null) {
            throw new \RuntimeException('Failed to parse AI response as JSON. The AI may have returned invalid or unexpected format. Check storage/logs/laravel.log for details.');
        }

        $design = $this->convertGeminiDesignToFabric($designData, $width, $height);

        return ['design' => $design, 'usage' => $usage];
    }

    /**
     * Extract token usage from Gemini generateContent response.
     *
     * @return array{input_tokens: int, output_tokens: int}
     */
    protected function extractGeminiUsageFromResponse(array $data): array
    {
        $meta = $data['usageMetadata'] ?? [];
        $input = (int) ($meta['promptTokenCount'] ?? 0);
        $output = (int) ($meta['candidatesTokenCount'] ?? $meta['totalTokenCount'] ?? 0);
        if ($output === 0 && isset($meta['totalTokenCount'])) {
            $output = max(0, (int) $meta['totalTokenCount'] - $input);
        }

        return ['input_tokens' => $input, 'output_tokens' => $output];
    }

    protected function buildGeminiDesignPrompt(int $width, int $height): string
    {
        return <<<PROMPT
You are an expert graphic designer. Return ONLY valid JSON with this exact structure. No markdown, no code blocks, no explanations—just the raw JSON object. Use valid JSON only—no doubled quotes in keys.
{
  "backgroundColor": "#hexcolor",
  "objects": [
    {
      "type": "rect|circle|ellipse|triangle|textbox|image",
      "left": number,
      "top": number,
      "width": number,
      "height": number,
      "radius": number (for circle),
      "rx": number, "ry": number (for ellipse),
      "fill": "#hex or gradient object",
      "stroke": "#hex",
      "strokeWidth": number,
      "text": "string" (for textbox),
      "fontSize": number,
      "fontFamily": "Arial",
      "fontWeight": "normal|bold",
      "textAlign": "left|center|right",
      "src": "https://placehold.co/400x200/1e3a5f/ffffff?text=Image" (for image)
    }
  ]
}
Canvas: {$width}x{$height}px. Use professional colors, gradients, shadows, typography. Create a stunning design.
PROMPT;
    }

    protected function convertGeminiDesignToFabric(array $designData, int $width, int $height): array
    {
        return (new AIDocumentGenerator)->convertDesignDataToFabric($designData, $width, $height);
    }

    /**
     * Extract JSON from AI response text. Handles markdown code blocks, extra text, and common AI mistakes.
     */
    protected function extractJsonFromAiResponse(string $text): ?array
    {
        $text = trim($text);

        // Strip markdown code blocks: ```json ... ``` or ``` ... ```
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $text, $m)) {
            $text = trim($m[1]);
        }

        // Find first { and matching } to extract JSON object (skip braces inside strings)
        $start = strpos($text, '{');
        if ($start !== false) {
            $depth = 0;
            $inString = false;
            $escape = false;
            $quote = '"';
            $end = -1;
            for ($i = $start; $i < strlen($text); $i++) {
                $c = $text[$i];
                if ($escape) {
                    $escape = false;

                    continue;
                }
                if ($inString) {
                    if ($c === '\\') {
                        $escape = true;
                    } elseif ($c === $quote) {
                        $inString = false;
                    }

                    continue;
                }
                if ($c === '"') {
                    $inString = true;
                    $quote = $c;

                    continue;
                }
                if ($c === '{') {
                    $depth++;
                } elseif ($c === '}') {
                    $depth--;
                    if ($depth === 0) {
                        $end = $i;
                        break;
                    }
                }
            }
            if ($end >= 0) {
                $text = substr($text, $start, $end - $start + 1);
            }
        }

        // Fix common AI mistakes: ""key"" -> "key" (double-quoted keys)
        $text = preg_replace('/""([a-zA-Z_][a-zA-Z0-9_]*)""/', '"$1"', $text);

        // Remove trailing commas before ] or }
        $text = preg_replace('/,\s*([}\]])/u', '$1', $text);

        // Remove control characters that can break JSON
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);

        $decoded = json_decode($text, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        $err = json_last_error_msg();
        // If response was truncated (ends with comma), try to close using last complete object
        $trimmed = rtrim($text);
        if (str_ends_with($trimmed, ',') && str_contains($text, '"objects"')) {
            $lastComplete = strrpos($trimmed, '},');
            if ($lastComplete !== false) {
                $try = substr($trimmed, 0, $lastComplete + 1).']}';
                $decoded = json_decode($try, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        \Log::warning('Gemini JSON parse failed', [
            'json_error' => $err,
            'raw_preview' => substr($text, 0, 500),
            'raw_end' => strlen($text) > 200 ? substr($text, -200) : $text,
        ]);

        return null;
    }

    /**
     * Generate design using AI (OpenAI)
     */
    public function generateAIDesign(Request $request)
    {
        try {
            $validated = $request->validate([
                'prompt' => 'required|string|max:2000',
                'canvasWidth' => 'nullable|integer|min:100|max:4000',
                'canvasHeight' => 'nullable|integer|min:100|max:4000',
            ]);

            $apiKey = Setting::get('openai_api_key') ?: env('OPENAI_API_KEY');
            if (! $apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'OpenAI API key is not configured. Please add it in Admin → Settings → Features tab.',
                ], 500);
            }

            $prompt = $validated['prompt'];
            $canvasWidth = $validated['canvasWidth'] ?? 800;
            $canvasHeight = $validated['canvasHeight'] ?? 1000;

            // Create a detailed prompt for OpenAI to generate a design structure
            $systemPrompt = "You are a design assistant that creates structured design layouts. Generate a JSON structure representing design elements for a canvas.
The response must be valid JSON with this structure:
{
  \"backgroundColor\": \"#hexcolor\",
  \"objects\": [
    {
      \"type\": \"rect|circle|triangle|textbox|image\",
      \"left\": number,
      \"top\": number,
      \"width\": number (for rect/triangle/textbox),
      \"height\": number (for rect/triangle/textbox),
      \"radius\": number (for circle),
      \"fill\": \"#hexcolor\",
      \"text\": \"string\" (for textbox),
      \"fontSize\": number (for textbox),
      \"fontFamily\": \"string\" (for textbox),
      \"textAlign\": \"left|center|right\" (for textbox),
      \"stroke\": \"#hexcolor\" (optional),
      \"strokeWidth\": number (optional)
    }
  ]
}
Canvas dimensions: {$canvasWidth}x{$canvasHeight} pixels.
Generate a professional design based on the user's description. Use appropriate colors, spacing, and typography.";

            $userPrompt = "Create a design for: {$prompt}";

            $model = Setting::get('openai_model') ?: env('OPENAI_MODEL', 'gpt-4o-mini');
            $baseUrl = Setting::get('openai_base_url') ?: env('OPENAI_BASE_URL', 'https://api.openai.com/v1');
            $apiUrl = rtrim($baseUrl, '/').'/chat/completions';

            // Make API call to OpenAI using Laravel HTTP client
            set_time_limit(45); // Allow enough time for OpenAI API (HTTP timeout 30s)
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($apiUrl, [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt,
                        ],
                        [
                            'role' => 'user',
                            'content' => $userPrompt,
                        ],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 2000,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if (! $response->successful()) {
                throw new \Exception('OpenAI API request failed: '.$response->body());
            }

            $responseData = $response->json();

            if (! isset($responseData['choices'][0]['message']['content'])) {
                throw new \Exception('Invalid response from OpenAI API');
            }

            $aiResponse = $responseData['choices'][0]['message']['content'];
            $designData = $this->extractJsonFromAiResponse($aiResponse);

            if ($designData === null) {
                \Log::warning('OpenAI JSON parse failed. Raw response: '.substr($aiResponse, 0, 2000));
                throw new \Exception('Failed to parse AI response as JSON: '.json_last_error_msg());
            }

            // Validate and structure the design data for Fabric.js
            $fabricDesign = [
                'version' => '5.3.0',
                'objects' => [],
                'background' => $designData['backgroundColor'] ?? '#ffffff',
                'backgroundColor' => $designData['backgroundColor'] ?? '#ffffff',
                'width' => $canvasWidth,
                'height' => $canvasHeight,
            ];

            // Convert AI-generated objects to Fabric.js format
            if (isset($designData['objects']) && is_array($designData['objects'])) {
                foreach ($designData['objects'] as $obj) {
                    $fabricObj = [];

                    // Common properties
                    $fabricObj['left'] = $obj['left'] ?? 100;
                    $fabricObj['top'] = $obj['top'] ?? 100;
                    $fabricObj['fill'] = $obj['fill'] ?? '#000000';

                    // Type-specific properties
                    switch ($obj['type'] ?? 'rect') {
                        case 'rect':
                            $fabricObj['type'] = 'rect';
                            $fabricObj['width'] = $obj['width'] ?? 200;
                            $fabricObj['height'] = $obj['height'] ?? 100;
                            break;
                        case 'circle':
                            $fabricObj['type'] = 'circle';
                            $fabricObj['radius'] = $obj['radius'] ?? 50;
                            break;
                        case 'triangle':
                            $fabricObj['type'] = 'triangle';
                            $fabricObj['width'] = $obj['width'] ?? 100;
                            $fabricObj['height'] = $obj['height'] ?? 100;
                            break;
                        case 'textbox':
                            $fabricObj['type'] = 'textbox';
                            $fabricObj['text'] = $obj['text'] ?? 'Text';
                            $fabricObj['width'] = $obj['width'] ?? 200;
                            $fabricObj['height'] = $obj['height'] ?? 50;
                            $fabricObj['fontSize'] = $obj['fontSize'] ?? 24;
                            $fabricObj['fontFamily'] = $obj['fontFamily'] ?? 'Arial';
                            $fabricObj['textAlign'] = $obj['textAlign'] ?? 'left';
                            break;
                        default:
                            $fabricObj['type'] = 'rect';
                            $fabricObj['width'] = $obj['width'] ?? 200;
                            $fabricObj['height'] = $obj['height'] ?? 100;
                    }

                    // Optional properties
                    if (isset($obj['stroke'])) {
                        $fabricObj['stroke'] = $obj['stroke'];
                    }
                    if (isset($obj['strokeWidth'])) {
                        $fabricObj['strokeWidth'] = $obj['strokeWidth'];
                    }

                    $fabricDesign['objects'][] = $fabricObj;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Design generated successfully',
                'design_data' => $fabricDesign,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: '.implode(', ', $e->validator->errors()->all()),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error generating AI design: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate design: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate text content for selected text element using AI (OpenAI)
     */
    public function generateTextContent(Request $request)
    {
        try {
            $validated = $request->validate([
                'prompt' => 'required|string|max:2000',
                'current_text' => 'nullable|string|max:5000',
            ]);

            $apiKey = Setting::get('openai_api_key') ?: env('OPENAI_API_KEY');
            if (! $apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'OpenAI API key is not configured. Please add it in Admin → Settings → Features tab.',
                ], 500);
            }

            $prompt = $validated['prompt'];
            $currentText = $validated['current_text'] ?? '';

            $systemPrompt = 'You are a professional copywriter. Generate concise, well-written text content based on the user\'s prompt. Return ONLY the generated text, no quotes, no markdown, no explanations. Keep it suitable for design elements (headlines, body text, captions).';

            $userPrompt = $prompt;
            if ($currentText) {
                $userPrompt = "Current text in the element: \"{$currentText}\"\n\nUser request: {$prompt}";
            }

            $model = Setting::get('openai_model') ?: env('OPENAI_MODEL', 'gpt-4o-mini');
            $baseUrl = Setting::get('openai_base_url') ?: env('OPENAI_BASE_URL', 'https://api.openai.com/v1');
            $apiUrl = rtrim($baseUrl, '/').'/chat/completions';

            set_time_limit(40); // Allow enough time for OpenAI API (HTTP timeout 25s)
            $response = Http::timeout(25)
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
                throw new \Exception('OpenAI API request failed: '.$response->body());
            }

            $responseData = $response->json();
            if (! isset($responseData['choices'][0]['message']['content'])) {
                throw new \Exception('Invalid response from OpenAI API');
            }

            $content = trim($responseData['choices'][0]['message']['content']);

            return response()->json([
                'success' => true,
                'content' => $content,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Generate text AI error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate letter from AI prompt and redirect to editor (uses AIDocumentGenerator)
     */
    public function generateLetter(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:2000',
        ]);

        try {
            $generator = new AIDocumentGenerator;
            if (! $generator->isConfigured()) {
                return redirect()->route('design.index')->with('error', 'OpenAI API key is not configured. Please add it in Admin → Settings.');
            }

            $fabricDesign = $generator->generateLetter($validated['prompt'], 595, 842);

            $designId = uniqid('design_');
            $designs = $request->session()->get('user_designs', []);
            $designs[$designId] = [
                'id' => $designId,
                'name' => 'AI Letter - '.now()->format('M d, H:i'),
                'pages' => [json_encode($fabricDesign)],
                'is_multi_page' => true,
                'page_count' => 1,
                'thumbnail' => null,
                'type' => 'letter',
                'created_at' => now()->toDateTimeString(),
            ];
            $request->session()->put('user_designs', $designs);

            return redirect()->route('design.create', ['load' => $designId, 'type' => 'letter', 'multi' => 'true']);
        } catch (\Exception $e) {
            \Log::error('Generate letter error: '.$e->getMessage());

            return redirect()->route('design.index')->with('error', 'Failed to generate letter: '.$e->getMessage());
        }
    }

    /**
     * Mark intro tour as seen for the current user (per-account once).
     */
    public function markIntroTourSeen(Request $request)
    {
        $request->validate(['tour' => 'required|in:explore,multi_page']);
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false], 401);
        }
        if ($request->tour === 'explore') {
            $user->update(['intro_tour_explore_seen_at' => now()]);
        } else {
            $user->update(['intro_tour_multi_page_seen_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Intro.js tour data for multi-page editor: show mode and steps from DB.
     */
    private function multiPageEditorIntroData(): array
    {
        $showMode = Setting::get('design_intro_show_mode', 'first_time');
        $steps = IntroTourStep::forTour('multi_page_editor')->get();
        $introAlreadySeenForAccount = auth()->user() ? auth()->user()->intro_tour_multi_page_seen_at !== null : true;

        return [
            'introShowMode' => $showMode,
            'introAlreadySeenForAccount' => $introAlreadySeenForAccount,
            'introSteps' => $steps->map(fn ($s) => [
                'element_selector' => $s->element_selector,
                'title' => $s->title,
                'intro_text' => $s->intro_text,
            ])->values()->toArray(),
        ];
    }
}
