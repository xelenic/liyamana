<?php

namespace App\Http\Controllers;

use App\Models\FlipBook;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FlipBookController extends Controller
{
    /**
     * Display a listing of the user's flip books
     */
    public function index()
    {
        $flipBooks = FlipBook::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('flipbooks.index', compact('flipBooks'));
    }

    /**
     * Show the flip book creation options
     */
    public function create()
    {
        return view('flipbooks.create');
    }

    /**
     * Show the flip book wizard
     */
    public function wizard()
    {
        return view('flipbooks.wizard');
    }

    /**
     * Store wizard step 1: Basic Information
     */
    public function storeStep1(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            // Store in session for wizard
            $request->session()->put('flipbook_wizard.title', $validated['title']);
            $request->session()->put('flipbook_wizard.description', $validated['description'] ?? '');

            return response()->json(['success' => true, 'message' => 'Step 1 completed']);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Store wizard step 2: Upload Pages
     */
    public function storeStep2(Request $request)
    {
        try {
            $validated = $request->validate([
                'pages.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max per image
            ]);

            $uploadedPages = [];

            if ($request->hasFile('pages')) {
                foreach ($request->file('pages') as $index => $file) {
                    $path = $file->store('flipbooks/pages', 'public');
                    $uploadedPages[] = [
                        'path' => $path,
                        'order' => $index,
                        'original_name' => $file->getClientOriginalName(),
                    ];
                }
            }

            // Store in session
            $existingPages = $request->session()->get('flipbook_wizard.pages', []);
            $request->session()->put('flipbook_wizard.pages', array_merge($existingPages, $uploadedPages));

            return response()->json([
                'success' => true,
                'message' => 'Pages uploaded successfully',
                'pages' => $uploadedPages,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Store wizard step 3: Settings
     */
    public function storeStep3(Request $request)
    {
        try {
            $validated = $request->validate([
                'transition_effect' => 'required|in:slide,flip,fade',
                'auto_play' => 'nullable',
                'auto_play_interval' => 'nullable|integer|min:1|max:60',
                'show_controls' => 'nullable',
                'show_thumbnails' => 'nullable',
                'background_color' => 'nullable|string|max:7',
                'is_public' => 'nullable',
            ]);

            $settings = [
                'transition_effect' => $validated['transition_effect'],
                'auto_play' => $request->has('auto_play'),
                'auto_play_interval' => $validated['auto_play_interval'] ?? 5,
                'show_controls' => $request->has('show_controls'),
                'show_thumbnails' => $request->has('show_thumbnails'),
                'background_color' => $validated['background_color'] ?? '#ffffff',
                'is_public' => $request->has('is_public'),
            ];

            $request->session()->put('flipbook_wizard.settings', $settings);

            return response()->json(['success' => true, 'message' => 'Settings saved']);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Store wizard step 4: Cover Image
     */
    public function storeStep4(Request $request)
    {
        try {
            $validated = $request->validate([
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            ]);

            if ($request->hasFile('cover_image')) {
                $path = $request->file('cover_image')->store('flipbooks/covers', 'public');
                $request->session()->put('flipbook_wizard.cover_image', $path);
            }

            return response()->json(['success' => true, 'message' => 'Cover image uploaded']);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Complete wizard and create flip book
     */
    public function complete(Request $request)
    {
        $wizardData = $request->session()->get('flipbook_wizard');

        if (! $wizardData || ! isset($wizardData['title']) || empty($wizardData['pages'])) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete all required steps',
            ], 400);
        }

        // Generate unique slug
        $slug = Str::slug($wizardData['title']);
        $originalSlug = $slug;
        $counter = 1;
        while (FlipBook::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        // Create flip book
        $flipBook = FlipBook::create([
            'user_id' => auth()->id(),
            'title' => $wizardData['title'],
            'description' => $wizardData['description'] ?? null,
            'slug' => $slug,
            'status' => 'draft',
            'settings' => $wizardData['settings'] ?? [],
            'pages' => $wizardData['pages'],
            'cover_image' => $wizardData['cover_image'] ?? null,
            'is_public' => $wizardData['settings']['is_public'] ?? false,
        ]);

        // Clear wizard session
        $request->session()->forget('flipbook_wizard');

        return response()->json([
            'success' => true,
            'message' => 'Flip book created successfully!',
            'flipbook_id' => $flipBook->id,
            'redirect' => route('flipbooks.show', $flipBook->id),
        ]);
    }

    /**
     * Clear wizard session
     */
    public function clearWizard(Request $request)
    {
        // Delete uploaded files from storage
        $pages = $request->session()->get('flipbook_wizard.pages', []);
        foreach ($pages as $page) {
            if (isset($page['path']) && Storage::disk('public')->exists($page['path'])) {
                Storage::disk('public')->delete($page['path']);
            }
        }

        $coverImage = $request->session()->get('flipbook_wizard.cover_image');
        if ($coverImage && Storage::disk('public')->exists($coverImage)) {
            Storage::disk('public')->delete($coverImage);
        }

        $request->session()->forget('flipbook_wizard');

        return response()->json(['success' => true, 'message' => 'Wizard cleared']);
    }

    /**
     * Create flipbook from design editor
     */
    public function createFromDesign(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'status' => 'nullable|in:draft,published,archived',
                'is_public' => 'nullable|boolean',
                'pages' => 'required|array|min:1',
                'pages.*' => 'required|string', // Base64 image data
                'design_data' => 'nullable|array', // Design pages data (Fabric.js JSON)
                'print_sheet_type' => 'nullable|string|max:100',
                'print_size' => 'nullable|string|max:50',
                'print_custom_width' => 'nullable|numeric',
                'print_custom_height' => 'nullable|numeric',
                'print_quality' => 'nullable|string|max:50',
                'binding_type' => 'nullable|string|max:50',
                'bundle_quantity' => 'nullable|integer|min:1|max:999',
            ]);

            // Generate unique slug
            $slug = Str::slug($validated['title']);
            $originalSlug = $slug;
            $counter = 1;
            while (FlipBook::where('slug', $slug)->exists()) {
                $slug = $originalSlug.'-'.$counter;
                $counter++;
            }

            // Create storage directory for this flipbook
            $storagePath = 'flipbooks/'.auth()->id().'/'.time();

            // Process and save page images
            $savedPages = [];
            $coverImage = null;

            foreach ($validated['pages'] as $index => $base64Image) {
                try {
                    // Extract base64 data (remove data:image/png;base64, prefix if present)
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                        $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
                    }

                    // Decode base64 image
                    $imageData = base64_decode($base64Image);

                    if ($imageData === false) {
                        throw new \Exception('Failed to decode image data for page '.($index + 1));
                    }

                    // Generate filename
                    $filename = 'page-'.($index + 1).'.png';
                    $filePath = $storagePath.'/'.$filename;

                    // Save image to storage
                    Storage::disk('public')->put($filePath, $imageData);

                    $savedPages[] = [
                        'path' => $filePath,
                        'order' => $index,
                        'page_number' => $index + 1,
                    ];

                    // Use first page as cover image
                    if ($index === 0) {
                        $coverImage = $filePath;
                    }
                } catch (\Exception $e) {
                    \Log::error('Error saving page image: '.$e->getMessage());
                    // Continue with other pages even if one fails
                }
            }

            if (empty($savedPages)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save any pages. Please try again.',
                ], 400);
            }

            // Build print settings
            $printSettings = [
                'print_sheet_type' => $validated['print_sheet_type'] ?? null,
                'print_size' => $validated['print_size'] ?? null,
                'print_custom_width' => $validated['print_custom_width'] ?? null,
                'print_custom_height' => $validated['print_custom_height'] ?? null,
                'print_quality' => $validated['print_quality'] ?? null,
                'binding_type' => $validated['binding_type'] ?? null,
                'bundle_quantity' => $validated['bundle_quantity'] ?? 1,
            ];

            // Create flipbook record
            $flipBook = FlipBook::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'slug' => $slug,
                'status' => $validated['status'] ?? 'draft',
                'pages' => $savedPages,
                'cover_image' => $coverImage,
                'is_public' => $validated['is_public'] ?? false,
                'settings' => [
                    'created_from_design' => true,
                    'page_count' => count($savedPages),
                    'design_data' => $validated['design_data'] ?? null, // Store original design data for editing
                    'print_settings' => $printSettings,
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Flipbook created successfully!',
                'flipbook_id' => $flipBook->id,
                'redirect' => route('flipbooks.show', $flipBook->id),
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating flipbook from design: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error creating flipbook: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update flipbook from design editor
     */
    public function updateFromDesign(Request $request, $id)
    {
        try {
            $flipBook = FlipBook::where('user_id', auth()->id())->findOrFail($id);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'status' => 'nullable|in:draft,published,archived',
                'is_public' => 'nullable|boolean',
                'pages' => 'required|array|min:1',
                'pages.*' => 'required|string', // Base64 image data
                'design_data' => 'nullable|array', // Design pages data (Fabric.js JSON)
                'print_sheet_type' => 'nullable|string|max:100',
                'print_size' => 'nullable|string|max:50',
                'print_custom_width' => 'nullable|numeric',
                'print_custom_height' => 'nullable|numeric',
                'print_quality' => 'nullable|string|max:50',
                'binding_type' => 'nullable|string|max:50',
                'bundle_quantity' => 'nullable|integer|min:1|max:999',
            ]);

            // Generate unique slug if title changed
            $slug = $flipBook->slug;
            if ($flipBook->title !== $validated['title']) {
                $slug = Str::slug($validated['title']);
                $originalSlug = $slug;
                $counter = 1;
                while (FlipBook::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = $originalSlug.'-'.$counter;
                    $counter++;
                }
            }

            // Delete old page images
            if ($flipBook->pages && is_array($flipBook->pages)) {
                foreach ($flipBook->pages as $page) {
                    if (isset($page['path']) && Storage::disk('public')->exists($page['path'])) {
                        Storage::disk('public')->delete($page['path']);
                    }
                }
            }

            // Delete old cover image
            if ($flipBook->cover_image && Storage::disk('public')->exists($flipBook->cover_image)) {
                Storage::disk('public')->delete($flipBook->cover_image);
            }

            // Use existing storage directory or create new one
            $storagePath = 'flipbooks/'.auth()->id().'/'.time();

            // Process and save page images
            $savedPages = [];
            $coverImage = null;

            foreach ($validated['pages'] as $index => $base64Image) {
                try {
                    // Extract base64 data (remove data:image/png;base64, prefix if present)
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                        $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
                    }

                    // Decode base64 image
                    $imageData = base64_decode($base64Image);

                    if ($imageData === false) {
                        throw new \Exception('Failed to decode image data for page '.($index + 1));
                    }

                    // Generate filename
                    $filename = 'page-'.($index + 1).'.png';
                    $filePath = $storagePath.'/'.$filename;

                    // Save image to storage
                    Storage::disk('public')->put($filePath, $imageData);

                    $savedPages[] = [
                        'path' => $filePath,
                        'order' => $index,
                        'page_number' => $index + 1,
                    ];

                    // Use first page as cover image
                    if ($index === 0) {
                        $coverImage = $filePath;
                    }
                } catch (\Exception $e) {
                    \Log::error('Error saving page image: '.$e->getMessage());
                    // Continue with other pages even if one fails
                }
            }

            if (empty($savedPages)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save any pages. Please try again.',
                ], 400);
            }

            // Build print settings
            $printSettings = [
                'print_sheet_type' => $validated['print_sheet_type'] ?? null,
                'print_size' => $validated['print_size'] ?? null,
                'print_custom_width' => $validated['print_custom_width'] ?? null,
                'print_custom_height' => $validated['print_custom_height'] ?? null,
                'print_quality' => $validated['print_quality'] ?? null,
                'binding_type' => $validated['binding_type'] ?? null,
                'bundle_quantity' => $validated['bundle_quantity'] ?? 1,
            ];

            // Update flipbook record
            $flipBook->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'slug' => $slug,
                'status' => $validated['status'] ?? 'draft',
                'pages' => $savedPages,
                'cover_image' => $coverImage,
                'is_public' => $validated['is_public'] ?? false,
                'settings' => array_merge($flipBook->settings ?? [], [
                    'created_from_design' => true,
                    'page_count' => count($savedPages),
                    'updated_at' => now()->toDateTimeString(),
                    'design_data' => $validated['design_data'] ?? ($flipBook->settings['design_data'] ?? null), // Preserve or update design data
                    'print_settings' => $printSettings,
                ]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Flipbook updated successfully!',
                'flipbook_id' => $flipBook->id,
                'redirect' => route('flipbooks.show', $flipBook->id),
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating flipbook from design: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error updating flipbook: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update flipbook basic info (title, description, status) - for inline edit
     */
    public function updateBasicInfo(Request $request, $id)
    {
        try {
            $flipBook = FlipBook::where('user_id', auth()->id())->findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'status' => 'sometimes|in:draft,published,archived',
                'is_public' => 'sometimes|boolean',
                'print_settings' => 'sometimes|array',
                'print_settings.print_sheet_type' => 'nullable|string|max:100',
                'print_settings.print_size' => 'nullable|string|max:50',
                'print_settings.print_custom_width' => 'nullable|numeric',
                'print_settings.print_custom_height' => 'nullable|numeric',
                'print_settings.print_quality' => 'nullable|string|max:50',
                'print_settings.binding_type' => 'nullable|string|max:50',
                'print_settings.bundle_quantity' => 'nullable|integer|min:1|max:999',
                'settings' => 'sometimes|array',
                'settings.transition_effect' => 'nullable|in:slide,flip,fade',
                'settings.auto_play' => 'nullable|boolean',
                'settings.auto_play_interval' => 'nullable|integer|min:1|max:60',
                'settings.show_controls' => 'nullable|boolean',
                'settings.show_thumbnails' => 'nullable|boolean',
            ]);

            $updateData = [];
            if (array_key_exists('title', $validated)) {
                $updateData['title'] = $validated['title'];
            }
            if (array_key_exists('description', $validated)) {
                $updateData['description'] = $validated['description'];
            }
            if (array_key_exists('status', $validated)) {
                $updateData['status'] = $validated['status'];
            }
            if (array_key_exists('is_public', $validated)) {
                $updateData['is_public'] = $validated['is_public'];
            }

            $settings = $flipBook->settings ?? [];
            if (array_key_exists('print_settings', $validated) && is_array($validated['print_settings'])) {
                $settings['print_settings'] = array_merge($settings['print_settings'] ?? [], $validated['print_settings']);
            }
            if (array_key_exists('settings', $validated) && is_array($validated['settings'])) {
                foreach ($validated['settings'] as $k => $v) {
                    if ($v !== null) {
                        $settings[$k] = $v;
                    }
                }
            }
            if (! empty($settings)) {
                $updateData['settings'] = $settings;
            }

            if (! empty($updateData)) {
                $flipBook->update($updateData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Updated successfully',
                'flipbook' => [
                    'title' => $flipBook->title,
                    'description' => $flipBook->description ?? '',
                    'status' => $flipBook->status,
                    'is_public' => $flipBook->is_public,
                    'settings' => $flipBook->settings,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating flipbook: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update',
            ], 500);
        }
    }

    /**
     * Show flip book
     */
    public function show($id)
    {
        $flipBook = FlipBook::where('user_id', auth()->id())->findOrFail($id);
        $sheetType = null;
        $sheetTypes = \App\Models\SheetType::active()->ordered()->inStock()->get();
        $printSettings = $flipBook->settings['print_settings'] ?? null;
        if (! empty($printSettings['print_sheet_type'])) {
            $sheetType = \App\Models\SheetType::where('slug', $printSettings['print_sheet_type'])->first();
        }

        // Calculate pricing based on print settings
        $pricing = $this->calculateFlipbookPricing($flipBook, $printSettings, $sheetTypes);

        return view('flipbooks.show', compact('flipBook', 'sheetType', 'sheetTypes', 'pricing'));
    }

    /**
     * Calculate flipbook pricing based on print settings
     */
    private function calculateFlipbookPricing($flipBook, $printSettings, $sheetTypes)
    {
        $pageCount = count($flipBook->pages ?? []);
        $sizeRates = [
            'A5' => 0.50, 'A4' => 0.75, 'A3' => 1.25,
            'Letter' => 0.80, 'Legal' => 0.90, 'Custom' => 1.00,
        ];
        $qualityRates = ['standard' => 1.0, 'high' => 1.3, 'premium' => 1.6];
        $bindingRates = ['none' => 0, 'spiral' => 2.50, 'perfect' => 3.00, 'saddle' => 1.50, 'wire' => 2.75];
        $shipping = 5.00;

        if (! $printSettings || $pageCount === 0) {
            return [
                'per_page' => 0,
                'subtotal' => 0,
                'binding_cost' => 0,
                'shipping' => 0,
                'bundle_quantity' => 1,
                'total' => 0,
                'formatted' => format_price(0),
                'formatted_per_page' => format_price(0),
                'formatted_subtotal' => format_price(0),
                'formatted_binding' => format_price(0),
                'formatted_shipping' => format_price(0),
            ];
        }

        $sheetTypeSlug = $printSettings['print_sheet_type'] ?? '';
        $printSize = $printSettings['print_size'] ?? '';
        $printQuality = $printSettings['print_quality'] ?? 'standard';
        $bindingType = $printSettings['binding_type'] ?? 'none';
        $bundleQuantity = (int) ($printSettings['bundle_quantity'] ?? 1) ?: 1;

        $sheetCostPerPage = 0;
        $st = $sheetTypes->firstWhere('slug', $sheetTypeSlug);
        if ($st) {
            $sheetCostPerPage = (float) ($st->price_per_sheet ?? 0);
        } else {
            $baseCost = $sizeRates[$printSize] ?? $sizeRates['Custom'];
            $sheetCostPerPage = $baseCost;
        }
        $sheetCostPerPage *= $qualityRates[$printQuality] ?? 1.0;

        $subtotal = $sheetCostPerPage * $pageCount;
        $bindingCost = $bindingRates[$bindingType] ?? 0;
        $baseTotal = $subtotal + $bindingCost + $shipping;
        $total = $baseTotal * $bundleQuantity;

        $currencySymbol = \App\Models\Setting::get('currency_symbol') ?: '$';
        $decimals = (int) (\App\Models\Setting::get('price_decimal_places') ?: 2);

        return [
            'per_page' => $sheetCostPerPage,
            'subtotal' => $subtotal,
            'binding_cost' => $bindingCost,
            'shipping' => $shipping,
            'bundle_quantity' => $bundleQuantity,
            'total' => $total,
            'formatted' => $currencySymbol.number_format($total, $decimals),
            'formatted_per_page' => $currencySymbol.number_format($sheetCostPerPage, $decimals),
            'formatted_subtotal' => $currencySymbol.number_format($subtotal, $decimals),
            'formatted_binding' => $currencySymbol.number_format($bindingCost, $decimals),
            'formatted_shipping' => $currencySymbol.number_format($shipping, $decimals),
        ];
    }

    /**
     * Load flipbook design data for editing
     */
    public function loadDesignForEdit($id)
    {
        try {
            $flipBook = FlipBook::where('user_id', auth()->id())->findOrFail($id);

            // Check if flipbook was created from design and has design data
            $settings = $flipBook->settings ?? [];
            if (! isset($settings['created_from_design']) || ! $settings['created_from_design']) {
                return response()->json([
                    'success' => false,
                    'message' => 'This flipbook was not created from a design and cannot be edited.',
                ], 400);
            }

            if (! isset($settings['design_data']) || empty($settings['design_data'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Design data not available for this flipbook.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'design_data' => $settings['design_data'],
                'flipbook_id' => $flipBook->id,
                'flipbook_title' => $flipBook->title,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading flipbook design: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error loading design: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview flip book with animated viewer
     */
    public function preview($id)
    {
        $flipBook = FlipBook::where('user_id', auth()->id())->findOrFail($id);
        $publicUrl = route('flipbooks.public', $flipBook->slug);

        return view('flipbooks.preview', compact('flipBook', 'publicUrl'));
    }

    /**
     * Public view of flip book (no authentication required)
     */
    public function publicView($slug)
    {
        $flipBook = FlipBook::where('slug', $slug)
            ->where('is_public', true)
            ->where('status', 'published')
            ->firstOrFail();

        return view('flipbooks.preview', compact('flipBook'));
    }

    /**
     * Show design page for flip book
     */
    public function design($id)
    {
        $flipBook = FlipBook::where('user_id', auth()->id())->findOrFail($id);

        return view('flipbooks.design', compact('flipBook'));
    }

    /**
     * Update flip book design settings
     */
    public function updateDesign(Request $request, $id)
    {
        $flipBook = FlipBook::where('user_id', auth()->id())->findOrFail($id);

        try {
            $validated = $request->validate([
                'background_color' => 'nullable|string|max:7',
                'text_color' => 'nullable|string|max:7',
                'primary_color' => 'nullable|string|max:7',
                'font_family' => 'nullable|string|max:100',
                'font_size' => 'nullable|integer|min:10|max:24',
                'page_width' => 'nullable|integer|min:400|max:2000',
                'page_height' => 'nullable|integer|min:400|max:2000',
                'border_radius' => 'nullable|integer|min:0|max:50',
                'shadow_effect' => 'nullable|in:none,small,medium,large',
                'transition_effect' => 'nullable|in:slide,flip,fade',
                'animation_speed' => 'nullable|in:slow,normal,fast',
            ]);

            $settings = $flipBook->settings ?? [];

            // Update design settings
            if (isset($validated['background_color'])) {
                $settings['background_color'] = $validated['background_color'];
            }
            if (isset($validated['text_color'])) {
                $settings['text_color'] = $validated['text_color'];
            }
            if (isset($validated['primary_color'])) {
                $settings['primary_color'] = $validated['primary_color'];
            }
            if (isset($validated['font_family'])) {
                $settings['font_family'] = $validated['font_family'];
            }
            if (isset($validated['font_size'])) {
                $settings['font_size'] = $validated['font_size'];
            }
            if (isset($validated['page_width'])) {
                $settings['page_width'] = $validated['page_width'];
            }
            if (isset($validated['page_height'])) {
                $settings['page_height'] = $validated['page_height'];
            }
            if (isset($validated['border_radius'])) {
                $settings['border_radius'] = $validated['border_radius'];
            }
            if (isset($validated['shadow_effect'])) {
                $settings['shadow_effect'] = $validated['shadow_effect'];
            }
            if (isset($validated['transition_effect'])) {
                $settings['transition_effect'] = $validated['transition_effect'];
            }
            if (isset($validated['animation_speed'])) {
                $settings['animation_speed'] = $validated['animation_speed'];
            }

            $flipBook->update(['settings' => $settings]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Design settings updated successfully',
                ]);
            }

            return redirect()->route('flipbooks.design', $flipBook->id)
                ->with('success', 'Design settings updated successfully');
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    /**
     * Generate flipbook description using AI (OpenAI)
     */
    public function generateDescription(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'page_count' => 'nullable|integer|min:1|max:500',
            ]);

            $apiKey = Setting::get('openai_api_key') ?: env('OPENAI_API_KEY');
            if (! $apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'OpenAI API key is not configured. Please add it in Admin → Settings.',
                ], 500);
            }

            $title = $validated['title'] ?? 'Flipbook';
            $pageCount = $validated['page_count'] ?? 1;

            $systemPrompt = 'You are a professional copywriter. Generate a concise, engaging description for a digital flipbook/publication. The description should be 1-3 sentences, professional, and suitable for product listings or portfolios. Do not use markdown or special formatting. Return only the plain text description.';

            $userPrompt = "Generate a description for a flipbook titled \"{$title}\" with {$pageCount} page(s).";

            $model = Setting::get('openai_model') ?: env('OPENAI_MODEL', 'gpt-4o-mini');
            $baseUrl = Setting::get('openai_base_url') ?: env('OPENAI_BASE_URL', 'https://api.openai.com/v1');
            $apiUrl = rtrim($baseUrl, '/').'/chat/completions';

            set_time_limit(35); // Allow enough time for OpenAI API (HTTP timeout 20s)
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
                    'max_tokens' => 200,
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
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error generating flipbook description: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a flip book
     */
    public function destroy($id)
    {
        $flipBook = FlipBook::where('user_id', auth()->id())->findOrFail($id);

        try {
            // Delete associated files
            if ($flipBook->cover_image && Storage::disk('public')->exists($flipBook->cover_image)) {
                Storage::disk('public')->delete($flipBook->cover_image);
            }

            // Delete page images
            if ($flipBook->pages && is_array($flipBook->pages)) {
                foreach ($flipBook->pages as $page) {
                    if (isset($page['path']) && Storage::disk('public')->exists($page['path'])) {
                        Storage::disk('public')->delete($page['path']);
                    }
                }
            }

            // Delete the flipbook record
            $flipBook->delete();

            return response()->json([
                'success' => true,
                'message' => 'Flip book deleted successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting flipbook: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error deleting flip book: '.$e->getMessage(),
            ], 500);
        }
    }
}
