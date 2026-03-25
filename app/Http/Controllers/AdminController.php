<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateTemplateThumbnailJob;
use App\Models\AiContentTemplate;
use App\Models\Currency;
use App\Models\DesignerApplication;
use App\Models\Documentation;
use App\Models\DocumentationCategory;
use App\Models\EnvelopeType;
use App\Models\ExploreSlide;
use App\Models\FlipBook;
use App\Models\IntroTourStep;
use App\Models\License;
use App\Models\Order;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SheetType;
use App\Models\Template;
use App\Models\TemplateCategory;
use App\Models\Testimonial;
use App\Models\ThumbnailPrompt;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard
     */
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_flipbooks' => FlipBook::count(),
            'active_users' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'recent_flipbooks' => FlipBook::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        $recent_users = User::latest()->take(5)->get();
        $recent_flipbooks = FlipBook::with('user')->latest()->take(5)->get();

        // Chart data: last 30 days for orders and templates
        $startDate = now()->subDays(29)->startOfDay();

        $ordersByDate = Order::where('created_at', '>=', $startDate)
            ->get()
            ->groupBy(fn ($o) => $o->created_at->format('Y-m-d'))
            ->map->count();

        $templatesByDate = Template::where('created_at', '>=', $startDate)
            ->get()
            ->groupBy(fn ($t) => $t->created_at->format('Y-m-d'))
            ->map->count();

        $chartLabels = [];
        $ordersChartData = [];
        $templatesChartData = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $chartLabels[] = $date->format('M d');
            $ordersChartData[] = $ordersByDate[$dateStr] ?? 0;
            $templatesChartData[] = $templatesByDate[$dateStr] ?? 0;
        }

        $chartData = [
            'labels' => $chartLabels,
            'orders' => $ordersChartData,
            'templates' => $templatesChartData,
        ];

        return view('admin.dashboard', compact('stats', 'recent_users', 'recent_flipbooks', 'chartData'));
    }

    /**
     * Show users management page
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->role($request->role);
        }

        $users = $query->with('roles')->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show user details with stats and line charts
     */
    public function showUser($id)
    {
        $user = User::with('roles', 'flipBooks')->findOrFail($id);

        $ordersCount = Order::where('user_id', $user->id)->count();
        $totalSpent = (float) Order::where('user_id', $user->id)->sum('total_amount');
        $templatesCount = Template::where('created_by', $user->id)->count();
        $flipBooksCount = $user->flipBooks->count();
        $reviewsCount = \App\Models\TemplateReview::where('user_id', $user->id)->count();
        $balance = (float) ($user->balance ?? 0);

        $chartDays = 30;
        $chartStart = now()->subDays($chartDays - 1)->startOfDay();
        $ordersByDate = Order::where('user_id', $user->id)
            ->where('created_at', '>=', $chartStart)
            ->get()
            ->groupBy(fn ($o) => $o->created_at->format('Y-m-d'))
            ->map->count();
        $templatesByDate = Template::where('created_by', $user->id)
            ->where('created_at', '>=', $chartStart)
            ->get()
            ->groupBy(fn ($t) => $t->created_at->format('Y-m-d'))
            ->map->count();
        $chartLabels = [];
        $chartOrders = [];
        $chartTemplates = [];
        for ($i = $chartDays - 1; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $key = $d->format('Y-m-d');
            $chartLabels[] = $d->format('M j');
            $chartOrders[] = $ordersByDate[$key] ?? 0;
            $chartTemplates[] = $templatesByDate[$key] ?? 0;
        }

        return view('admin.users.show', compact(
            'user',
            'ordersCount',
            'totalSpent',
            'templatesCount',
            'flipBooksCount',
            'reviewsCount',
            'balance',
            'chartLabels',
            'chartOrders',
            'chartTemplates'
        ));
    }

    /**
     * Show edit user form
     */
    public function editUser($id)
    {
        $user = User::with('roles')->findOrFail($id);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|string|in:admin,user,designer',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Sync roles
        $user->syncRoles([$request->role]);

        return redirect()->route('admin.users')->with('success', 'User updated successfully!');
    }

    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'You cannot delete your own account!');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully!');
    }

    /**
     * Login as another user (admin only). Redirects to explore/dashboard as that user.
     */
    public function loginAsUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('info', 'You are already logged in as this user.');
        }

        Auth::login($user, true);

        return redirect()->route('design.templates.explore')->with('success', 'Logged in as '.$user->name.'.');
    }

    /**
     * Show designer applications management page
     */
    public function designerApplications(Request $request)
    {
        $query = DesignerApplication::with(['user', 'reviewer']);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $applications = $query->latest()->paginate(15);

        return view('admin.designer-applications.index', compact('applications'));
    }

    /**
     * Show designer application details
     */
    public function showDesignerApplication($id)
    {
        $application = DesignerApplication::with(['user', 'reviewer'])->findOrFail($id);

        return view('admin.designer-applications.show', compact('application'));
    }

    /**
     * Approve designer application - assign designer role to user
     */
    public function approveDesignerApplication($id)
    {
        $application = DesignerApplication::findOrFail($id);

        if ($application->status === 'approved') {
            return redirect()->route('admin.designer-applications')->with('error', 'This application is already approved.');
        }

        if (! $application->user_id) {
            return redirect()->route('admin.designer-applications.show', $id)->with('error', 'Cannot approve: Application has no linked user account. User must register/login first.');
        }

        $user = User::find($application->user_id);
        if (! $user) {
            return redirect()->route('admin.designer-applications.show', $id)->with('error', 'User account not found.');
        }

        $application->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        $user->assignRole('designer');

        return redirect()->route('admin.designer-applications')->with('success', 'Designer application approved! User can now save public templates.');
    }

    /**
     * Reject designer application
     */
    public function rejectDesignerApplication(Request $request, $id)
    {
        $application = DesignerApplication::findOrFail($id);

        if ($application->status === 'rejected') {
            return redirect()->route('admin.designer-applications')->with('error', 'This application is already rejected.');
        }

        $application->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        return redirect()->route('admin.designer-applications')->with('success', 'Designer application rejected.');
    }

    /**
     * Show flipbooks management page
     */
    public function flipbooks(Request $request)
    {
        $query = FlipBook::with('user');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                    ->orWhere('slug', 'like', '%'.$request->search.'%');
            });
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $flipbooks = $query->latest()->paginate(15);
        $users = User::orderBy('name')->get();

        return view('admin.flipbooks.index', compact('flipbooks', 'users'));
    }

    /**
     * Delete flipbook
     */
    public function deleteFlipbook($id)
    {
        $flipbook = FlipBook::findOrFail($id);
        $flipbook->delete();

        return redirect()->route('admin.flipbooks')->with('success', 'Flip book deleted successfully!');
    }

    /**
     * Show templates management page (public templates only)
     */
    public function templates(Request $request)
    {
        $query = Template::with('creator')->where('is_public', true);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('category', 'like', '%'.$search.'%');
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $templates = $query->with('products')->latest()->paginate(15)->withQueryString();
        $categories = Template::where('is_public', true)->distinct()->pluck('category')->filter()->sort()->values();
        $thumbnailPrompts = ThumbnailPrompt::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.templates.index', compact('templates', 'categories', 'thumbnailPrompts'));
    }

    /**
     * Show full details for a public template (orders count, template details, revenue, etc.)
     */
    public function templateManage($id)
    {
        $template = Template::with('creator')->withCount('orders')->where('is_public', true)->findOrFail($id);

        $ordersCount = $template->orders_count ?? 0;
        $reviewsCount = $template->reviews()->count();
        $averageRating = $template->reviews()->avg('rating') ?? 0;
        $totalRevenue = (float) \App\Models\Order::where('template_id', $template->id)->sum('total_amount');
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

        return view('admin.templates.manage', compact(
            'template', 'ordersCount', 'reviewsCount', 'averageRating', 'totalRevenue',
            'sheetTypes', 'pageDimensions', 'overviewChartLabels', 'overviewChartOrders',
            'overviewChartReviews', 'revenueChartLabels', 'revenueChartData', 'assignedProducts'
        ));
    }

    /**
     * Delete template
     */
    public function deleteTemplate($id)
    {
        $template = Template::findOrFail($id);

        // Delete thumbnail if exists
        if ($template->thumbnail_path && \Storage::disk('public')->exists($template->thumbnail_path)) {
            \Storage::disk('public')->delete($template->thumbnail_path);
        }

        $template->delete();

        return redirect()->route('admin.templates')->with('success', 'Template deleted successfully!');
    }

    /**
     * Queue template thumbnail generation using Gemini. Returns JSON immediately.
     */
    public function generateTemplateThumbnail(Request $request, $id)
    {
        $template = Template::findOrFail($id);

        $request->validate([
            'prompt_id' => 'required|exists:thumbnail_prompts,id',
            'product_id' => 'nullable|integer|exists:products,id',
        ]);

        $apiKey = Setting::get('gemini_api_key') ?: config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        if (! $apiKey) {
            return response()->json(['success' => false, 'message' => 'Gemini API key is not configured.'], 400);
        }

        $productId = $request->filled('product_id') ? (int) $request->product_id : null;
        if ($productId && ! $template->products()->where('products.id', $productId)->exists()) {
            return response()->json(['success' => false, 'message' => 'Selected product is not assigned to this template.'], 400);
        }

        $thumbnailPrompt = ThumbnailPrompt::findOrFail($request->input('prompt_id'));
        GenerateTemplateThumbnailJob::dispatch($template->id, $thumbnailPrompt->prompt, $productId);

        return response()->json([
            'success' => true,
            'message' => 'Thumbnail generation has been queued. Refresh the page in a few seconds to see the new thumbnail.',
            'queued' => true,
        ]);
    }

    /**
     * Toggle template featured status
     */
    public function toggleTemplateFeatured($id)
    {
        $template = Template::findOrFail($id);
        $template->is_featured = ! $template->is_featured;
        if ($template->is_featured) {
            $maxOrder = Template::where('is_featured', true)->max('featured_sort_order') ?? 0;
            $template->featured_sort_order = $maxOrder + 1;
        }
        $template->save();

        return redirect()->back()->with('success', $template->is_featured ? 'Template marked as featured!' : 'Template removed from featured.');
    }

    /**
     * Show orders management page
     */
    public function orders(Request $request)
    {
        $query = Order::with('user', 'template');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('template_name', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('delivery_status')) {
            $query->where('delivery_status', $request->delivery_status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Show order details
     */
    public function showOrder($id)
    {
        $order = Order::with('user', 'template')->findOrFail($id);
        $orderProduct = null;
        if (! empty($order->checkout_data['product_id'])) {
            $orderProduct = Product::find($order->checkout_data['product_id']);
        }

        return view('admin.orders.show', compact('order', 'orderProduct'));
    }

    /**
     * Update order delivery status
     */
    public function updateOrderDeliveryStatus($id, Request $request)
    {
        $order = Order::with('user')->findOrFail($id);
        $request->validate([
            'delivery_status' => 'required|in:pending,hold,processing,sending,complete',
        ]);
        $order->update(['delivery_status' => $request->delivery_status]);

        // Notify the customer who placed the order
        if ($order->user) {
            $statusLabel = ucfirst($request->delivery_status);
            $title = 'Order status updated';
            $message = 'Your order #'.$order->id.' is now: '.$statusLabel.'.';
            $url = route('orders.show', $order->id);
            $type = $request->delivery_status === 'complete' ? 'success' : 'info';
            push_notification($order->user, $title, $message, $url, $type);
        }

        return redirect()->route('admin.orders.show', $id)->with('success', 'Delivery status updated.');
    }

    /**
     * Export order as PDF
     */
    public function exportOrderPdf($id)
    {
        $order = Order::with('user', 'template')->findOrFail($id);
        $orderProduct = null;
        if (! empty($order->checkout_data['product_id'])) {
            $orderProduct = Product::find($order->checkout_data['product_id']);
        }
        $pdf = Pdf::loadView('pdf.order', compact('order', 'orderProduct'));

        return $pdf->download('order-'.$order->id.'.pdf');
    }

    /**
     * Export a single order item as design PDF (opens page that runs client-side export)
     */
    public function exportOrderItemPdf($id, $itemIndex)
    {
        $order = Order::with('user', 'template')->findOrFail($id);
        $checkoutData = $order->checkout_data ?? [];
        $items = $checkoutData['items'] ?? [];
        $designs = $checkoutData['designs'] ?? [];
        $displayItems = ! empty($items) ? $items : $designs;

        if (empty($displayItems)) {
            return redirect()->route('admin.orders.show', $id)->with('error', 'No designs found in this order.');
        }

        $idx = (int) $itemIndex;
        if ($idx < 0 || $idx >= count($displayItems)) {
            $idx = 0;
        }

        $template = $order->template;
        if (! $template || ! $template->pages) {
            return redirect()->route('admin.orders.show', $id)->with('error', 'Template no longer available for export.');
        }

        $design = $displayItems[$idx];

        return view('admin.orders.export-item-pdf', compact('order', 'template', 'design', 'idx'));
    }

    /**
     * Preview ordered design
     */
    public function previewOrderDesign($id, Request $request)
    {
        $order = Order::with('user', 'template')->findOrFail($id);
        $checkoutData = $order->checkout_data ?? [];
        $items = $checkoutData['items'] ?? [];
        $designs = $checkoutData['designs'] ?? [];
        $displayItems = ! empty($items) ? $items : $designs;

        if (empty($displayItems)) {
            return redirect()->route('admin.orders.show', $id)->with('error', 'No designs found in this order.');
        }

        $designIndex = (int) $request->get('design', 0);
        if ($designIndex < 0 || $designIndex >= count($displayItems)) {
            $designIndex = 0;
        }

        $template = $order->template;
        if (! $template || ! $template->pages) {
            return redirect()->route('admin.orders.show', $id)->with('error', 'Template no longer available for preview.');
        }

        $design = $displayItems[$designIndex];
        $designs = $displayItems;

        return view('admin.orders.preview-design', compact('order', 'template', 'design', 'designIndex', 'designs'));
    }

    /**
     * Show sheet types management page
     */
    public function sheetTypes(Request $request)
    {
        $query = SheetType::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('slug', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status == 'active');
        }

        $sheetTypes = $query->orderBy('sort_order')->orderBy('name')->paginate(15);

        return view('admin.sheet-types.index', compact('sheetTypes'));
    }

    /**
     * Show create sheet type form
     */
    public function createSheetType()
    {
        return view('admin.sheet-types.form');
    }

    /**
     * Generate sheet type description using AI (OpenAI)
     */
    public function generateSheetTypeDescription(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'slug' => 'nullable|string|max:255',
            ]);

            $apiKey = Setting::get('openai_api_key') ?: env('OPENAI_API_KEY');
            if (! $apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'OpenAI API key is not configured. Please add it in Admin → Settings.',
                ], 500);
            }

            $name = $validated['name'] ?? 'Sheet type';
            $slug = $validated['slug'] ?? '';

            $systemPrompt = 'You are a professional copywriter for a print/flipbook business. Generate a concise, informative description for a paper/print sheet type. The description should be 1-3 sentences, explain the characteristics and benefits of the sheet type (e.g., finish, durability, use cases), and be suitable for product listings. Do not use markdown or special formatting. Return only the plain text description.';

            $userPrompt = "Generate a description for a print sheet type named \"{$name}\"".($slug ? " (slug: {$slug})" : '').'.';

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
            \Log::error('Error generating sheet type description: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store new sheet type
     */
    public function storeSheetType(Request $request)
    {
        // Convert checkbox value to boolean (checkbox sends "1" when checked, "0" when unchecked via hidden input)
        $isActive = filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN);
        $request->merge(['is_active' => $isActive]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:sheet_types,slug',
            'multiplier' => 'required|numeric|min:0',
            'price_per_sheet' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'video' => 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg|max:10240',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = [
            'name' => $request->name,
            'slug' => $request->slug,
            'multiplier' => $request->multiplier,
            'price_per_sheet' => $request->price_per_sheet,
            'description' => $request->description,
            'is_active' => $request->is_active,
            'sort_order' => $request->sort_order ?? 0,
        ];

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('sheet-types', 'public');
        }
        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('sheet-types', 'public');
        }

        SheetType::create($data);

        return redirect()->route('admin.sheet-types')->with('success', 'Sheet type created successfully!');
    }

    /**
     * Show edit sheet type form
     */
    public function editSheetType($id)
    {
        $sheetType = SheetType::findOrFail($id);

        return view('admin.sheet-types.form', compact('sheetType'));
    }

    /**
     * Update sheet type
     */
    public function updateSheetType(Request $request, $id)
    {
        $sheetType = SheetType::findOrFail($id);

        // Convert checkbox value to boolean (checkbox sends "1" when checked, "0" when unchecked via hidden input)
        $isActive = filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN);
        $request->merge(['is_active' => $isActive]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:sheet_types,slug,'.$id,
            'multiplier' => 'required|numeric|min:0',
            'price_per_sheet' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'video' => 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg|max:10240',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = [
            'name' => $request->name,
            'slug' => $request->slug,
            'multiplier' => $request->multiplier,
            'price_per_sheet' => $request->price_per_sheet,
            'description' => $request->description,
            'is_active' => $request->is_active,
            'sort_order' => $request->sort_order ?? 0,
        ];

        if ($request->hasFile('image')) {
            if ($sheetType->image_path && Storage::disk('public')->exists($sheetType->image_path)) {
                Storage::disk('public')->delete($sheetType->image_path);
            }
            $data['image_path'] = $request->file('image')->store('sheet-types', 'public');
        }
        if ($request->hasFile('video')) {
            if ($sheetType->video_path && Storage::disk('public')->exists($sheetType->video_path)) {
                Storage::disk('public')->delete($sheetType->video_path);
            }
            $data['video_path'] = $request->file('video')->store('sheet-types', 'public');
        }

        $sheetType->update($data);

        return redirect()->route('admin.sheet-types')->with('success', 'Sheet type updated successfully!');
    }

    /**
     * Delete sheet type
     */
    public function deleteSheetType($id)
    {
        $sheetType = SheetType::findOrFail($id);

        if ($sheetType->image_path && Storage::disk('public')->exists($sheetType->image_path)) {
            Storage::disk('public')->delete($sheetType->image_path);
        }
        if ($sheetType->video_path && Storage::disk('public')->exists($sheetType->video_path)) {
            Storage::disk('public')->delete($sheetType->video_path);
        }

        $sheetType->delete();

        return redirect()->route('admin.sheet-types')->with('success', 'Sheet type deleted successfully!');
    }

    public function envelopeTypes(Request $request)
    {
        $query = EnvelopeType::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('slug', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        $envelopeTypes = $query->orderBy('sort_order')->orderBy('name')->paginate(15);

        return view('admin.envelope-types.index', compact('envelopeTypes'));
    }

    public function createEnvelopeType()
    {
        return view('admin.envelope-types.form');
    }

    public function storeEnvelopeType(Request $request)
    {
        $isActive = filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN);
        $request->merge(['is_active' => $isActive]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:envelope_types,slug',
            'price_per_letter' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        EnvelopeType::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'price_per_letter' => $request->price_per_letter,
            'description' => $request->description,
            'is_active' => $request->is_active,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.envelope-types')->with('success', 'Envelope type created successfully!');
    }

    public function editEnvelopeType($id)
    {
        $envelopeType = EnvelopeType::findOrFail($id);

        return view('admin.envelope-types.form', compact('envelopeType'));
    }

    public function updateEnvelopeType(Request $request, $id)
    {
        $envelopeType = EnvelopeType::findOrFail($id);

        $isActive = filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN);
        $request->merge(['is_active' => $isActive]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:envelope_types,slug,'.$id,
            'price_per_letter' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $envelopeType->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'price_per_letter' => $request->price_per_letter,
            'description' => $request->description,
            'is_active' => $request->is_active,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.envelope-types')->with('success', 'Envelope type updated successfully!');
    }

    public function deleteEnvelopeType($id)
    {
        EnvelopeType::findOrFail($id)->delete();

        return redirect()->route('admin.envelope-types')->with('success', 'Envelope type deleted successfully!');
    }

    /**
     * AI Content Templates CRUD
     */
    public function aiContentTemplates(Request $request)
    {
        $query = AiContentTemplate::query();

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%')
                    ->orWhere('prompt', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status == 'active');
        }

        $templates = $query->ordered()->paginate(15);

        return view('admin.ai-content-templates.index', compact('templates'));
    }

    public function createAiContentTemplate()
    {
        return view('admin.ai-content-templates.form');
    }

    public function storeAiContentTemplate(Request $request)
    {
        $request->merge(['is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN)]);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prompt' => 'required|string',
            'fields' => ['nullable', 'string', function ($attr, $value, $fail) {
                if (filled($value)) {
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail('Fields must be valid JSON.');
                    }
                }
            }],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'editor_json' => ['nullable', 'string', function ($attr, $value, $fail) {
                if (filled($value)) {
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail('Editor JSON must be valid JSON.');
                    }
                }
            }],
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'prompt' => $request->prompt,
            'fields' => $request->filled('fields') ? json_decode($request->fields, true) : null,
            'editor_json' => $request->filled('editor_json') ? json_decode($request->editor_json, true) : null,
            'is_active' => $request->is_active,
            'sort_order' => $request->sort_order ?? 0,
        ];

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('ai-content-templates', 'public');
        }

        AiContentTemplate::create($data);

        return redirect()->route('admin.ai-content-templates')->with('success', 'AI content template created successfully!');
    }

    public function editAiContentTemplate($id)
    {
        $template = AiContentTemplate::findOrFail($id);

        return view('admin.ai-content-templates.form', compact('template'));
    }

    public function updateAiContentTemplate(Request $request, $id)
    {
        $template = AiContentTemplate::findOrFail($id);
        $request->merge(['is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN)]);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prompt' => 'required|string',
            'fields' => ['nullable', 'string', function ($attr, $value, $fail) {
                if (filled($value)) {
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail('Fields must be valid JSON.');
                    }
                }
            }],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'editor_json' => ['nullable', 'string', function ($attr, $value, $fail) {
                if (filled($value)) {
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail('Editor JSON must be valid JSON.');
                    }
                }
            }],
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'prompt' => $request->prompt,
            'fields' => $request->filled('fields') ? json_decode($request->fields, true) : null,
            'editor_json' => $request->filled('editor_json') ? json_decode($request->editor_json, true) : null,
            'is_active' => $request->is_active,
            'sort_order' => $request->sort_order ?? 0,
        ];

        if ($request->hasFile('image')) {
            if ($template->image_path && Storage::disk('public')->exists($template->image_path)) {
                Storage::disk('public')->delete($template->image_path);
            }
            $data['image_path'] = $request->file('image')->store('ai-content-templates', 'public');
        }

        $template->update($data);

        return redirect()->route('admin.ai-content-templates')->with('success', 'AI content template updated successfully!');
    }

    public function deleteAiContentTemplate($id)
    {
        $template = AiContentTemplate::findOrFail($id);

        if ($template->image_path && Storage::disk('public')->exists($template->image_path)) {
            Storage::disk('public')->delete($template->image_path);
        }

        $template->delete();

        return redirect()->route('admin.ai-content-templates')->with('success', 'AI content template deleted successfully!');
    }

    /**
     * Testimonials CRUD (home page)
     */
    public function testimonials(Request $request)
    {
        $query = Testimonial::query();
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('role', 'like', '%'.$request->search.'%')
                    ->orWhere('content', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        $testimonials = $query->ordered()->paginate(15)->withQueryString();

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function createTestimonial()
    {
        return view('admin.testimonials.form');
    }

    public function storeTestimonial(Request $request)
    {
        $request->merge([
            'is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN),
        ]);
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'content' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'avatar_url' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);
        $data = $request->only(['name', 'role', 'content', 'avatar_url', 'sort_order', 'is_active']);
        $data['rating'] = (int) ($request->input('rating', 5));
        $data['sort_order'] = (int) ($request->input('sort_order', 0));
        Testimonial::create($data);

        return redirect()->route('admin.testimonials')->with('success', 'Testimonial created successfully.');
    }

    public function editTestimonial($id)
    {
        $testimonial = Testimonial::findOrFail($id);

        return view('admin.testimonials.form', compact('testimonial'));
    }

    public function updateTestimonial(Request $request, $id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $request->merge([
            'is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN),
        ]);
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'content' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'avatar_url' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);
        $data = $request->only(['name', 'role', 'content', 'avatar_url', 'sort_order', 'is_active']);
        $data['rating'] = (int) ($request->input('rating', 5));
        $data['sort_order'] = (int) ($request->input('sort_order', 0));
        $testimonial->update($data);

        return redirect()->route('admin.testimonials')->with('success', 'Testimonial updated successfully.');
    }

    public function deleteTestimonial($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->delete();

        return redirect()->route('admin.testimonials')->with('success', 'Testimonial deleted successfully.');
    }

    /**
     * Documentation CRUD
     */
    public function documentation(Request $request)
    {
        $query = Documentation::query()->with('categories');
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                    ->orWhere('slug', 'like', '%'.$request->search.'%')
                    ->orWhere('content', 'like', '%'.$request->search.'%')
                    ->orWhereHas('categories', function ($cq) use ($request) {
                        $cq->where('name', 'like', '%'.$request->search.'%')
                            ->orWhere('slug', 'like', '%'.$request->search.'%');
                    });
            });
        }
        if ($request->filled('status')) {
            $query->where('is_published', $request->status === 'published');
        }
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('documentation_categories.id', $request->category_id);
            });
        }
        $documentations = $query->ordered()->paginate(15)->withQueryString();
        $categories = DocumentationCategory::ordered()->get();

        return view('admin.documentation.index', compact('documentations', 'categories'));
    }

    public function createDocumentation()
    {
        $categories = DocumentationCategory::active()->ordered()->get();

        return view('admin.documentation.form', compact('categories'));
    }

    public function storeDocumentation(Request $request)
    {
        $request->merge([
            'is_published' => filter_var($request->input('is_published', false), FILTER_VALIDATE_BOOLEAN),
        ]);
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:documentations,slug',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:documentation_categories,id',
            'content' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
        ]);
        $slug = $request->filled('slug')
            ? \Illuminate\Support\Str::slug($request->slug)
            : Documentation::generateSlug($request->title);
        $data = $request->only(['title', 'content', 'sort_order', 'is_published']);
        $data['slug'] = $slug;
        $data['sort_order'] = (int) ($request->input('sort_order', 0));
        $doc = Documentation::create($data);
        $doc->categories()->sync($request->input('category_ids', []));

        return redirect()->route('admin.documentation')->with('success', 'Documentation created successfully.');
    }

    public function editDocumentation($id)
    {
        $doc = Documentation::findOrFail($id);
        $categories = DocumentationCategory::active()->ordered()->get();

        return view('admin.documentation.form', ['documentation' => $doc, 'categories' => $categories]);
    }

    public function updateDocumentation(Request $request, $id)
    {
        $doc = Documentation::findOrFail($id);
        $request->merge([
            'is_published' => filter_var($request->input('is_published', false), FILTER_VALIDATE_BOOLEAN),
        ]);
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:documentations,slug,'.$id,
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:documentation_categories,id',
            'content' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
        ]);
        $slug = $request->filled('slug')
            ? \Illuminate\Support\Str::slug($request->slug)
            : Documentation::generateSlug($request->title, (int) $id);
        $data = $request->only(['title', 'content', 'sort_order', 'is_published']);
        $data['slug'] = $slug;
        $data['sort_order'] = (int) ($request->input('sort_order', 0));
        $doc->update($data);
        $doc->categories()->sync($request->input('category_ids', []));

        return redirect()->route('admin.documentation')->with('success', 'Documentation updated successfully.');
    }

    public function deleteDocumentation($id)
    {
        $doc = Documentation::findOrFail($id);
        $doc->delete();

        return redirect()->route('admin.documentation')->with('success', 'Documentation deleted successfully.');
    }

    /**
     * Documentation Categories CRUD
     */
    public function documentationCategories(Request $request)
    {
        $query = DocumentationCategory::query();
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('slug', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        $categories = $query->withCount('documentations')->ordered()->paginate(15)->withQueryString();

        return view('admin.documentation-categories.index', compact('categories'));
    }

    public function createDocumentationCategory()
    {
        return view('admin.documentation-categories.form');
    }

    public function storeDocumentationCategory(Request $request)
    {
        $request->merge([
            'is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN),
        ]);
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:documentation_categories,slug',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);
        $data = $request->only(['name', 'slug', 'description', 'sort_order', 'is_active']);
        $data['sort_order'] = (int) ($request->input('sort_order', 0));
        DocumentationCategory::create($data);

        return redirect()->route('admin.documentation-categories')->with('success', 'Documentation category created successfully.');
    }

    public function editDocumentationCategory($id)
    {
        $category = DocumentationCategory::findOrFail($id);

        return view('admin.documentation-categories.form', compact('category'));
    }

    public function updateDocumentationCategory(Request $request, $id)
    {
        $category = DocumentationCategory::findOrFail($id);
        $request->merge([
            'is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN),
        ]);
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:documentation_categories,slug,'.$id,
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);
        $data = $request->only(['name', 'slug', 'description', 'sort_order', 'is_active']);
        $data['sort_order'] = (int) ($request->input('sort_order', 0));
        $category->update($data);

        return redirect()->route('admin.documentation-categories')->with('success', 'Documentation category updated successfully.');
    }

    public function deleteDocumentationCategory($id)
    {
        $category = DocumentationCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.documentation-categories')->with('success', 'Documentation category deleted successfully.');
    }

    /**
     * Products CRUD
     */
    public function products(Request $request)
    {
        $query = Product::query();

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('slug', 'like', '%'.$request->search.'%')
                    ->orWhere('sku', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status == 'active');
        }

        $products = $query->ordered()->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    public function createProduct()
    {
        return view('admin.products.form');
    }

    public function storeProduct(Request $request)
    {
        $request->merge(['is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN)]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->only(['name', 'slug', 'description', 'price', 'sku', 'is_active', 'sort_order']);
        $data['faqs'] = $this->buildFaqsFromRequest($request);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        } else {
            $data['image'] = null;
        }

        Product::create($data);

        return redirect()->route('admin.products')->with('success', 'Product created successfully.');
    }

    public function editProduct($id)
    {
        $product = Product::findOrFail($id);

        return view('admin.products.form', compact('product'));
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $request->merge(['is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN)]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,'.$id,
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->only(['name', 'slug', 'description', 'price', 'sku', 'is_active', 'sort_order']);
        $data['faqs'] = $this->buildFaqsFromRequest($request);
        if ($request->hasFile('image')) {
            if ($product->image && ! str_starts_with($product->image, 'http') && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('admin.products')->with('success', 'Product updated successfully.');
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        if ($product->image && ! str_starts_with($product->image, 'http') && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return redirect()->route('admin.products')->with('success', 'Product deleted successfully.');
    }

    /**
     * Generate product description and FAQ in one call using OpenAI.
     */
    public function generateProductDescriptionAndFaq(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:500',
                'description' => 'nullable|string|max:5000',
            ]);

            $apiKey = Setting::get('openai_api_key') ?: env('OPENAI_API_KEY');
            if (! $apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'OpenAI API key is not configured. Please add it in Admin → Settings.',
                ], 400);
            }

            $name = $validated['name'];
            $existingDesc = $validated['description'] ?? '';

            $systemPrompt = 'You are a professional copywriter for a product catalog. Given a product name (and optional existing description), return a JSON object with exactly two keys: "description" (string) and "faqs" (array). For "description": write a clear, engaging product description in 2-4 sentences, suitable for a product page. For "faqs": provide 4 to 6 realistic FAQ items; each item is an object with "question" and "answer" (strings). Return ONLY valid JSON, no markdown, no code fences, no extra text. Example: {"description":"...","faqs":[{"question":"...","answer":"..."}]}';

            $userPrompt = "Product name: {$name}.";
            if ($existingDesc !== '') {
                $userPrompt .= ' Optional context (you may use to refine): '.substr($existingDesc, 0, 1500).'.';
            }
            $userPrompt .= ' Generate description and faqs as one JSON object.';

            $model = Setting::get('openai_model') ?: env('OPENAI_MODEL', 'gpt-4o-mini');
            $baseUrl = Setting::get('openai_base_url') ?: env('OPENAI_BASE_URL', 'https://api.openai.com/v1');
            $apiUrl = rtrim($baseUrl, '/').'/chat/completions';

            set_time_limit(45);
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
                    'temperature' => 0.6,
                    'max_tokens' => 1600,
                ]);

            if (! $response->successful()) {
                throw new \Exception('API request failed: '.$response->body());
            }

            $responseData = $response->json();
            if (! isset($responseData['choices'][0]['message']['content'])) {
                throw new \Exception('Invalid response from API');
            }

            $content = trim($responseData['choices'][0]['message']['content']);
            $content = preg_replace('/^```\w*\s*|\s*```$/m', '', $content);
            $decoded = json_decode($content, true);
            if (! is_array($decoded)) {
                throw new \Exception('AI did not return valid JSON.');
            }

            $description = isset($decoded['description']) ? trim((string) $decoded['description']) : '';
            $faqs = [];
            if (isset($decoded['faqs']) && is_array($decoded['faqs'])) {
                foreach ($decoded['faqs'] as $item) {
                    if (is_array($item) && (isset($item['question']) || isset($item['answer']))) {
                        $faqs[] = [
                            'question' => isset($item['question']) ? trim((string) $item['question']) : '',
                            'answer' => isset($item['answer']) ? trim((string) $item['answer']) : '',
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'description' => $description,
                'faqs' => $faqs,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Generate product description and FAQ error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate product FAQ using OpenAI.
     */
    public function generateProductFaq(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:500',
                'description' => 'nullable|string|max:5000',
            ]);

            $apiKey = Setting::get('openai_api_key') ?: env('OPENAI_API_KEY');
            if (! $apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'OpenAI API key is not configured. Please add it in Admin → Settings.',
                ], 400);
            }

            $name = $validated['name'];
            $description = $validated['description'] ?? '';

            $systemPrompt = 'You are a helpful assistant for a product catalog. Generate 4 to 6 realistic frequently asked questions (FAQ) and short answers for the given product. Return ONLY a valid JSON array of objects, each with exactly two keys: "question" and "answer". No markdown, no code fences, no extra text. Example: [{"question":"What is the size?","answer":"It comes in A4 and Letter."}]';

            $userPrompt = "Product name: {$name}.";
            if ($description !== '') {
                $userPrompt .= ' Product description: '.substr($description, 0, 2000).'.';
            }
            $userPrompt .= ' Generate FAQ as a JSON array of {question, answer} objects.';

            $model = Setting::get('openai_model') ?: env('OPENAI_MODEL', 'gpt-4o-mini');
            $baseUrl = Setting::get('openai_base_url') ?: env('OPENAI_BASE_URL', 'https://api.openai.com/v1');
            $apiUrl = rtrim($baseUrl, '/').'/chat/completions';

            set_time_limit(40);
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
                    'temperature' => 0.6,
                    'max_tokens' => 1200,
                ]);

            if (! $response->successful()) {
                throw new \Exception('API request failed: '.$response->body());
            }

            $responseData = $response->json();
            if (! isset($responseData['choices'][0]['message']['content'])) {
                throw new \Exception('Invalid response from API');
            }

            $content = trim($responseData['choices'][0]['message']['content']);
            $content = preg_replace('/^```\w*\s*|\s*```$/m', '', $content);
            $decoded = json_decode($content, true);
            if (! is_array($decoded)) {
                throw new \Exception('AI did not return valid JSON.');
            }

            $faqs = [];
            foreach ($decoded as $item) {
                if (is_array($item) && (isset($item['question']) || isset($item['answer']))) {
                    $faqs[] = [
                        'question' => isset($item['question']) ? trim((string) $item['question']) : '',
                        'answer' => isset($item['answer']) ? trim((string) $item['answer']) : '',
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'faqs' => $faqs,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Generate product FAQ error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build faqs array from request faq_questions[] and faq_answers[].
     */
    private function buildFaqsFromRequest(Request $request): array
    {
        $questions = $request->input('faq_questions', []);
        $answers = $request->input('faq_answers', []);
        $faqs = [];
        foreach ($questions as $i => $q) {
            $q = is_string($q) ? trim($q) : '';
            $a = isset($answers[$i]) ? (is_string($answers[$i]) ? trim($answers[$i]) : '') : '';
            if ($q !== '' || $a !== '') {
                $faqs[] = ['question' => $q, 'answer' => $a];
            }
        }

        return $faqs;
    }

    /**
     * Template Licenses CRUD
     */
    public function licenses(Request $request)
    {
        $query = License::query();

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('slug', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status == 'active');
        }

        $licenses = $query->orderBy('sort_order')->orderBy('name')->paginate(15);

        return view('admin.licenses.index', compact('licenses'));
    }

    public function createLicense()
    {
        return view('admin.licenses.form');
    }

    public function storeLicense(Request $request)
    {
        $request->merge(['is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN)]);

        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:licenses,slug',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        License::create($request->only(['name', 'slug', 'description', 'is_active', 'sort_order']));

        return redirect()->route('admin.licenses')->with('success', 'License created successfully!');
    }

    public function editLicense($id)
    {
        $license = License::findOrFail($id);

        return view('admin.licenses.form', compact('license'));
    }

    public function updateLicense(Request $request, $id)
    {
        $license = License::findOrFail($id);
        $request->merge(['is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN)]);

        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:licenses,slug,'.$id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $license->update($request->only(['name', 'slug', 'description', 'is_active', 'sort_order']));

        return redirect()->route('admin.licenses')->with('success', 'License updated successfully!');
    }

    public function deleteLicense($id)
    {
        $license = License::findOrFail($id);
        $license->delete();

        return redirect()->route('admin.licenses')->with('success', 'License deleted successfully!');
    }

    /**
     * Thumbnail Prompts CRUD (for AI thumbnail generation dropdown)
     */
    public function thumbnailPrompts(Request $request)
    {
        $prompts = ThumbnailPrompt::orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('admin.thumbnail-prompts.index', compact('prompts'));
    }

    public function createThumbnailPrompt()
    {
        return view('admin.thumbnail-prompts.form');
    }

    public function storeThumbnailPrompt(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'prompt' => 'required|string|max:5000',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        ThumbnailPrompt::create($request->only(['name', 'prompt', 'sort_order']));

        return redirect()->route('admin.thumbnail-prompts')->with('success', 'Thumbnail prompt created successfully!');
    }

    public function editThumbnailPrompt($id)
    {
        $prompt = ThumbnailPrompt::findOrFail($id);

        return view('admin.thumbnail-prompts.form', compact('prompt'));
    }

    public function updateThumbnailPrompt(Request $request, $id)
    {
        $prompt = ThumbnailPrompt::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'prompt' => 'required|string|max:5000',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $prompt->update($request->only(['name', 'prompt', 'sort_order']));

        return redirect()->route('admin.thumbnail-prompts')->with('success', 'Thumbnail prompt updated successfully!');
    }

    public function deleteThumbnailPrompt($id)
    {
        ThumbnailPrompt::findOrFail($id)->delete();

        return redirect()->route('admin.thumbnail-prompts')->with('success', 'Thumbnail prompt deleted successfully!');
    }

    /**
     * Design Intro (Intro.js): Multi-Page Design Tool + Explore page – show modes + steps CRUD
     */
    public function designIntro(Request $request)
    {
        $showMode = Setting::get('design_intro_show_mode', 'first_time');
        $exploreShowMode = Setting::get('design_intro_explore_show_mode', 'first_time');
        $steps = IntroTourStep::where('tour_slug', 'multi_page_editor')->orderBy('sort_order')->orderBy('id')->get();
        $exploreSteps = IntroTourStep::where('tour_slug', 'templates_explore')->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.design-intro.index', compact('showMode', 'exploreShowMode', 'steps', 'exploreSteps'));
    }

    public function updateDesignIntroSettings(Request $request)
    {
        $request->validate([
            'design_intro_show_mode' => 'required|in:first_time,first_time_account,always,never',
        ]);
        Setting::set('design_intro_show_mode', $request->design_intro_show_mode, 'editor');

        return redirect()->route('admin.design-intro')->with('success', 'Design tool intro option saved.');
    }

    public function updateDesignIntroExploreSettings(Request $request)
    {
        $request->validate([
            'design_intro_explore_show_mode' => 'required|in:first_time,first_time_account,always,never',
        ]);
        Setting::set('design_intro_explore_show_mode', $request->design_intro_explore_show_mode, 'editor');

        return redirect()->route('admin.design-intro')->with('success', 'Explore page intro option saved.');
    }

    public function createIntroTourStep(Request $request)
    {
        $tour = $request->get('tour', 'multi_page_editor');
        if (! in_array($tour, ['multi_page_editor', 'templates_explore'], true)) {
            $tour = 'multi_page_editor';
        }

        return view('admin.design-intro.step-form', ['tour' => $tour]);
    }

    public function storeIntroTourStep(Request $request)
    {
        $request->validate([
            'tour_slug' => 'required|in:multi_page_editor,templates_explore',
            'element_selector' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'intro_text' => 'required|string|max:10000',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $tourSlug = $request->tour_slug;
        $maxOrder = IntroTourStep::where('tour_slug', $tourSlug)->max('sort_order') ?? 0;
        IntroTourStep::create([
            'tour_slug' => $tourSlug,
            'sort_order' => (int) ($request->sort_order ?? $maxOrder + 1),
            'element_selector' => $request->filled('element_selector') ? trim($request->element_selector) : null,
            'title' => $request->filled('title') ? trim($request->title) : null,
            'intro_text' => $request->intro_text,
            'is_active' => true,
        ]);

        return redirect()->route('admin.design-intro')->with('success', 'Intro step added.');
    }

    public function editIntroTourStep($id)
    {
        $step = IntroTourStep::findOrFail($id);

        return view('admin.design-intro.step-form', compact('step'));
    }

    public function updateIntroTourStep(Request $request, $id)
    {
        $step = IntroTourStep::findOrFail($id);
        $request->validate([
            'element_selector' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'intro_text' => 'required|string|max:10000',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $step->update([
            'sort_order' => (int) ($request->sort_order ?? $step->sort_order),
            'element_selector' => $request->filled('element_selector') ? trim($request->element_selector) : null,
            'title' => $request->filled('title') ? trim($request->title) : null,
            'intro_text' => $request->intro_text,
        ]);

        return redirect()->route('admin.design-intro')->with('success', 'Intro step updated.');
    }

    public function deleteIntroTourStep($id)
    {
        IntroTourStep::findOrFail($id)->delete();

        return redirect()->route('admin.design-intro')->with('success', 'Intro step deleted.');
    }

    /**
     * Template Categories CRUD
     */
    public function templateCategories(Request $request)
    {
        $query = TemplateCategory::query();

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('slug', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status == 'active');
        }

        $categories = $query->withCount('templates')->ordered()->paginate(15);

        return view('admin.template-categories.index', compact('categories'));
    }

    public function createTemplateCategory()
    {
        return view('admin.template-categories.form');
    }

    public function storeTemplateCategory(Request $request)
    {
        $request->merge(['is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN)]);

        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:template_categories,slug',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        TemplateCategory::create($request->only(['name', 'slug', 'description', 'is_active', 'sort_order']));

        return redirect()->route('admin.template-categories')->with('success', 'Template category created successfully!');
    }

    public function editTemplateCategory($id)
    {
        $category = TemplateCategory::findOrFail($id);

        return view('admin.template-categories.form', compact('category'));
    }

    public function updateTemplateCategory(Request $request, $id)
    {
        $category = TemplateCategory::findOrFail($id);
        $request->merge(['is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN)]);

        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:template_categories,slug,'.$id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category->update($request->only(['name', 'slug', 'description', 'is_active', 'sort_order']));

        return redirect()->route('admin.template-categories')->with('success', 'Template category updated successfully!');
    }

    public function deleteTemplateCategory($id)
    {
        $category = TemplateCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.template-categories')->with('success', 'Template category deleted successfully!');
    }

    /**
     * Currencies CRUD (Settings)
     */
    public function currencies(Request $request)
    {
        $query = Currency::query();
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', '%'.$request->search.'%')
                    ->orWhere('name', 'like', '%'.$request->search.'%')
                    ->orWhere('symbol', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        $currencies = $query->ordered()->paginate(15)->withQueryString();

        return view('admin.currencies.index', compact('currencies'));
    }

    public function createCurrency()
    {
        return view('admin.currencies.form');
    }

    public function storeCurrency(Request $request)
    {
        $request->merge([
            'is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN),
            'is_default' => filter_var($request->input('is_default', false), FILTER_VALIDATE_BOOLEAN),
        ]);
        $request->validate([
            'code' => 'required|string|max:10|unique:currencies,code',
            'name' => 'required|string|max:64',
            'symbol' => 'nullable|string|max:16',
            'decimal_places' => 'nullable|integer|min:0|max:6',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        if ($request->boolean('is_default')) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }
        Currency::create($request->only(['code', 'name', 'symbol', 'decimal_places', 'is_default', 'is_active', 'sort_order']));

        return redirect()->route('admin.currencies')->with('success', 'Currency created successfully.');
    }

    public function editCurrency($id)
    {
        $currency = Currency::findOrFail($id);

        return view('admin.currencies.form', compact('currency'));
    }

    public function updateCurrency(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);
        $request->merge([
            'is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN),
            'is_default' => filter_var($request->input('is_default', false), FILTER_VALIDATE_BOOLEAN),
        ]);
        $request->validate([
            'code' => 'required|string|max:10|unique:currencies,code,'.$id,
            'name' => 'required|string|max:64',
            'symbol' => 'nullable|string|max:16',
            'decimal_places' => 'nullable|integer|min:0|max:6',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        if ($request->boolean('is_default')) {
            Currency::where('is_default', true)->where('id', '!=', $id)->update(['is_default' => false]);
        }
        $currency->update($request->only(['code', 'name', 'symbol', 'decimal_places', 'is_default', 'is_active', 'sort_order']));

        return redirect()->route('admin.currencies')->with('success', 'Currency updated successfully.');
    }

    public function deleteCurrency($id)
    {
        $currency = Currency::findOrFail($id);
        $currency->delete();

        return redirect()->route('admin.currencies')->with('success', 'Currency deleted successfully.');
    }

    /**
     * Explore Page Slider CRUD
     */
    public function exploreSlides(Request $request)
    {
        $slides = ExploreSlide::ordered()->paginate(15);

        return view('admin.explore-slides.index', compact('slides'));
    }

    public function createExploreSlide()
    {
        return view('admin.explore-slides.form');
    }

    public function storeExploreSlide(Request $request)
    {
        $request->merge(['is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN)]);
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'link_url' => 'nullable|string|max:500',
            'link_text' => 'nullable|string|max:100',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $path = $request->file('image')->store('explore-slides', 'public');
        ExploreSlide::create([
            'image_path' => $path,
            'title' => $request->title,
            'description' => $request->description,
            'link_url' => $request->link_url,
            'link_text' => $request->link_text,
            'is_active' => $request->is_active,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.explore-slides')->with('success', 'Slide created successfully!');
    }

    public function editExploreSlide($id)
    {
        $slide = ExploreSlide::findOrFail($id);

        return view('admin.explore-slides.form', compact('slide'));
    }

    public function updateExploreSlide(Request $request, $id)
    {
        $slide = ExploreSlide::findOrFail($id);
        $request->merge(['is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN)]);

        $rules = [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'link_url' => 'nullable|string|max:500',
            'link_text' => 'nullable|string|max:100',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
        if ($request->hasFile('image')) {
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048';
        }
        $request->validate($rules);

        $data = $request->only(['title', 'description', 'link_url', 'link_text', 'is_active', 'sort_order']);
        if ($request->hasFile('image')) {
            if ($slide->image_path && Storage::disk('public')->exists($slide->image_path)) {
                Storage::disk('public')->delete($slide->image_path);
            }
            $data['image_path'] = $request->file('image')->store('explore-slides', 'public');
        }
        $slide->update($data);

        return redirect()->route('admin.explore-slides')->with('success', 'Slide updated successfully!');
    }

    public function deleteExploreSlide($id)
    {
        $slide = ExploreSlide::findOrFail($id);
        if ($slide->image_path && Storage::disk('public')->exists($slide->image_path)) {
            Storage::disk('public')->delete($slide->image_path);
        }
        $slide->delete();

        return redirect()->route('admin.explore-slides')->with('success', 'Slide deleted successfully!');
    }

    /**
     * Pricing rules management
     */
    public function pricingRules(Request $request)
    {
        $query = PricingRule::query();

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('sheet_type_slug', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status == 'active');
        }

        $rules = $query->ordered()->paginate(15);

        return view('admin.pricing-rules.index', compact('rules'));
    }

    public function createPricingRule()
    {
        $sheetTypes = SheetType::active()->ordered()->get();

        return view('admin.pricing-rules.form', compact('sheetTypes'));
    }

    public function storePricingRule(Request $request)
    {
        $request->merge(['is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN)]);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'sheet_type_slug' => 'nullable|string|max:255',
            'min_quantity' => 'required|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'applies_to_design' => 'required|in:any,same_design,mixed_designs',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        PricingRule::create($request->only([
            'name', 'sheet_type_slug', 'min_quantity', 'max_quantity',
            'discount_percent', 'applies_to_design', 'is_active', 'sort_order',
        ]));

        return redirect()->route('admin.pricing-rules')->with('success', 'Pricing rule created successfully!');
    }

    public function editPricingRule($id)
    {
        $rule = PricingRule::findOrFail($id);
        $sheetTypes = SheetType::active()->ordered()->get();

        return view('admin.pricing-rules.form', compact('rule', 'sheetTypes'));
    }

    public function updatePricingRule(Request $request, $id)
    {
        $rule = PricingRule::findOrFail($id);
        $request->merge(['is_active' => filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN)]);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'sheet_type_slug' => 'nullable|string|max:255',
            'min_quantity' => 'required|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'applies_to_design' => 'required|in:any,same_design,mixed_designs',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $rule->update($request->only([
            'name', 'sheet_type_slug', 'min_quantity', 'max_quantity',
            'discount_percent', 'applies_to_design', 'is_active', 'sort_order',
        ]));

        return redirect()->route('admin.pricing-rules')->with('success', 'Pricing rule updated successfully!');
    }

    public function deletePricingRule($id)
    {
        PricingRule::findOrFail($id)->delete();

        return redirect()->route('admin.pricing-rules')->with('success', 'Pricing rule deleted successfully!');
    }

    /**
     * Default settings configuration
     */
    protected function getDefaultSettings(): array
    {
        return [
            'general' => [
                'site_name' => ['label' => 'Site Name', 'type' => 'text', 'default' => config('app.name') ?: 'FlipBook'],
                'site_description' => ['label' => 'Site Description', 'type' => 'textarea', 'default' => ''],
                'contact_email' => ['label' => 'Contact Email', 'type' => 'email', 'default' => ''],
                'items_per_page' => ['label' => 'Items Per Page', 'type' => 'number', 'default' => '15'],
            ],
            'features' => [
                'allow_registration' => ['label' => 'Allow New Registrations', 'type' => 'boolean', 'default' => '1'],
                'maintenance_mode' => ['label' => 'Maintenance Mode', 'type' => 'boolean', 'default' => '0'],
            ],
            'ai' => [
                'ai_design_enabled' => ['label' => 'Enable AI Design Generation', 'type' => 'boolean', 'default' => '1'],
                'openai_api_key' => ['label' => 'OpenAI API Key', 'type' => 'password', 'default' => ''],
                'openai_model' => ['label' => 'OpenAI Model', 'type' => 'text', 'default' => 'gpt-4o-mini'],
                'openai_base_url' => ['label' => 'OpenAI Base URL (optional)', 'type' => 'text', 'default' => ''],
                'gemini_api_key' => ['label' => 'Gemini API Key', 'type' => 'password', 'default' => ''],
                'gemini_model' => ['label' => 'Gemini Model', 'type' => 'text', 'default' => 'gemini-2.5-flash'],
                'ai_content_template_credit_cost' => ['label' => 'AI Content Flat Credit Cost (fallback per generation)', 'type' => 'text', 'default' => '0.5'],
                'ai_content_use_token_cost' => ['label' => 'Use token-based billing when available', 'type' => 'boolean', 'default' => '1'],
                'ai_content_input_token_cost_per_1000' => ['label' => 'Credit cost per 1000 input tokens', 'type' => 'text', 'default' => '0.01'],
                'ai_content_output_token_cost_per_1000' => ['label' => 'Credit cost per 1000 output tokens', 'type' => 'text', 'default' => '0.02'],
            ],
            'currency' => [
                'default_currency' => ['label' => 'Default Currency', 'type' => 'select', 'default' => ''],
                'currency_symbol' => ['label' => 'Currency Symbol', 'type' => 'text', 'default' => '$'],
                'price_decimal_places' => ['label' => 'Decimal Places', 'type' => 'number', 'default' => '2'],
            ],
            'explore' => [
                'explore_page_title' => ['label' => 'Explore Page Title', 'type' => 'text', 'default' => 'Explore Templates'],
                'explore_show_featured' => ['label' => 'Show Featured Templates Section', 'type' => 'boolean', 'default' => '1'],
                'explore_show_categories' => ['label' => 'Show Category Filter', 'type' => 'boolean', 'default' => '1'],
                'explore_tooltip_enabled' => ['label' => 'Enable Template Card Tooltip', 'type' => 'boolean', 'default' => '1'],
                'explore_tooltip_delay_ms' => ['label' => 'Card Tooltip Delay (ms)', 'type' => 'number', 'default' => '700'],
            ],
        ];
    }

    /**
     * Payment gateway settings configuration
     */
    protected function getPaymentSettings(): array
    {
        $defaults = [
            'payment_stripe_enabled' => ['label' => 'Enable Stripe (Card Payments)', 'type' => 'boolean', 'default' => '0'],
            'payment_stripe_publishable_key' => ['label' => 'Stripe Publishable Key', 'type' => 'text', 'default' => ''],
            'payment_stripe_secret_key' => ['label' => 'Stripe Secret Key', 'type' => 'password', 'default' => ''],
            'payment_paypal_enabled' => ['label' => 'Enable PayPal', 'type' => 'boolean', 'default' => '1'],
            'payment_bank_transfer_enabled' => ['label' => 'Enable Bank Transfer', 'type' => 'boolean', 'default' => '1'],
        ];

        return array_merge($defaults, config('payment.extra_settings', []));
    }

    /**
     * Show payment gateway settings page
     */
    public function paymentGatewaySettings()
    {
        $defaults = $this->getPaymentSettings();
        $saved = Setting::getAll();

        $settings = [];
        foreach ($defaults as $key => $config) {
            $settings[$key] = [
                'label' => $config['label'],
                'type' => $config['type'],
                'value' => $saved[$key] ?? $config['default'],
            ];
        }

        return view('admin.settings.payment', compact('settings'));
    }

    /**
     * Update payment gateway settings
     */
    public function updatePaymentGatewaySettings(Request $request)
    {
        $defaults = $this->getPaymentSettings();

        foreach ($defaults as $key => $config) {
            if ($request->has($key)) {
                $value = $request->input($key);
                if ($config['type'] === 'boolean') {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                }
                if ($config['type'] === 'password' && empty($value)) {
                    continue;
                }
                Setting::set($key, $value, 'payment');
            }
        }

        return redirect()->route('admin.settings.payment')->with('success', 'Payment gateway settings updated successfully!');
    }

    /**
     * OAuth settings (Google login/signup)
     */
    protected function getOauthSettings(): array
    {
        $defaultRedirect = config('app.url').'/auth/google/callback';

        return [
            'google_client_id' => ['label' => 'Google Client ID', 'type' => 'text', 'default' => ''],
            'google_client_secret' => ['label' => 'Google Client Secret', 'type' => 'password', 'default' => ''],
            'google_redirect_uri' => ['label' => 'Google Redirect URI', 'type' => 'text', 'default' => $defaultRedirect],
        ];
    }

    /**
     * Show OAuth management settings page
     */
    public function oauthSettings()
    {
        $defaults = $this->getOauthSettings();
        $saved = Setting::getAll();

        $settings = [];
        foreach ($defaults as $key => $config) {
            $settings[$key] = [
                'label' => $config['label'],
                'type' => $config['type'],
                'value' => $saved[$key] ?? $config['default'],
            ];
        }

        return view('admin.settings.oauth', compact('settings'));
    }

    /**
     * Update OAuth settings
     */
    public function updateOauthSettings(Request $request)
    {
        $defaults = $this->getOauthSettings();

        foreach ($defaults as $key => $config) {
            if ($request->has($key)) {
                $value = $request->input($key);
                if ($config['type'] === 'password' && $value === '') {
                    continue;
                }
                Setting::set($key, $value, 'oauth');
            }
        }

        return redirect()->route('admin.settings.oauth')->with('success', 'OAuth settings updated successfully!');
    }

    /**
     * Session recording (rrweb) and user heatmap — meta for admin settings page
     */
    protected function getSessionRecordingSettingsMeta(): array
    {
        $sessionEnvOn = filter_var(config('session_recording.enabled'), FILTER_VALIDATE_BOOLEAN);
        $heatmapEnvOn = filter_var(config('user_heatmap.enabled'), FILTER_VALIDATE_BOOLEAN);

        return [
            'session_recording_enabled' => [
                'label' => 'Record user sessions',
                'type' => 'boolean',
                'default' => $sessionEnvOn ? '1' : '0',
                'setting_group' => 'session_recording',
                'help' => 'When enabled, signed-in non-admin users send anonymized DOM replay data. Replays are listed under Users statistics → Session recordings.',
            ],
            'session_recording_max_bytes_per_session' => [
                'label' => 'Max bytes stored per session (ingest)',
                'type' => 'text',
                'default' => '',
                'setting_group' => 'session_recording',
                'config_default' => (string) (int) config('session_recording.max_bytes_per_session', 15 * 1024 * 1024),
                'help' => 'Leave empty to use the value from config / .env (SESSION_RECORDING_MAX_BYTES).',
            ],
            'session_recording_max_replay_bytes' => [
                'label' => 'Max bytes loaded in admin replay',
                'type' => 'text',
                'default' => '',
                'setting_group' => 'session_recording',
                'config_default' => (string) (int) config('session_recording.max_replay_bytes', 40 * 1024 * 1024),
                'help' => 'Leave empty to use SESSION_RECORDING_MAX_REPLAY_BYTES from config.',
            ],
            'session_recording_json_max_depth' => [
                'label' => 'PHP JSON depth (encode / decode)',
                'type' => 'text',
                'default' => '',
                'setting_group' => 'session_recording',
                'config_default' => (string) (int) config('session_recording.json_max_depth', 1_000_000),
                'help' => 'Must be high for deep DOM snapshots. Leave empty for config default.',
            ],
            'session_recording_cdn_rrweb' => [
                'label' => 'rrweb.js CDN URL',
                'type' => 'text',
                'default' => '',
                'setting_group' => 'session_recording',
                'config_default' => (string) config('session_recording.cdn.rrweb_js'),
                'help' => 'Recorder script for the user app. Leave empty for default CDN.',
            ],
            'session_recording_cdn_player' => [
                'label' => 'rrweb-player.js CDN URL',
                'type' => 'text',
                'default' => '',
                'setting_group' => 'session_recording',
                'config_default' => (string) config('session_recording.cdn.player_js'),
                'help' => 'Admin replay player script. Leave empty for default.',
            ],
            'session_recording_cdn_player_css' => [
                'label' => 'rrweb-player CSS CDN URL',
                'type' => 'text',
                'default' => '',
                'setting_group' => 'session_recording',
                'config_default' => (string) config('session_recording.cdn.player_css'),
                'help' => 'Styles for the replay player. Leave empty for default.',
            ],
            'user_heatmap_enabled' => [
                'label' => 'Collect click heatmaps',
                'type' => 'boolean',
                'default' => $heatmapEnvOn ? '1' : '0',
                'setting_group' => 'user_heatmap',
                'help' => 'When enabled, signed-in non-admin users send viewport-normalized click coordinates. View under Users statistics → User heatmaps.',
            ],
            'user_heatmap_max_clicks_per_ingest' => [
                'label' => 'Max clicks per ingest request',
                'type' => 'text',
                'default' => '',
                'setting_group' => 'user_heatmap',
                'config_default' => (string) (int) config('user_heatmap.max_clicks_per_ingest', 40),
                'help' => 'Batch size limit for each POST to the heatmap endpoint. Leave empty for USER_HEATMAP_MAX_CLICKS_PER_INGEST / config default.',
            ],
            'user_heatmap_admin_max_points_per_response' => [
                'label' => 'Max points per admin heatmap response',
                'type' => 'text',
                'default' => '',
                'setting_group' => 'user_heatmap',
                'config_default' => (string) (int) config('user_heatmap.admin_max_points_per_response', 8000),
                'help' => 'Caps how many clicks are returned when viewing a heatmap in admin. Leave empty for USER_HEATMAP_ADMIN_MAX_POINTS / config default.',
            ],
        ];
    }

    /**
     * Show session recording settings page
     */
    public function sessionRecordingSettings(Request $request)
    {
        $meta = $this->getSessionRecordingSettingsMeta();
        $saved = Setting::getAll();

        $settings = [];
        foreach ($meta as $key => $config) {
            $settings[$key] = [
                'label' => $config['label'],
                'type' => $config['type'],
                'value' => $saved[$key] ?? $config['default'],
                'config_default' => $config['config_default'] ?? null,
                'help' => $config['help'] ?? null,
                'setting_group' => $config['setting_group'] ?? 'session_recording',
            ];
        }

        $tab = (string) $request->query('tab', 'session');
        if (! in_array($tab, ['session', 'heatmap'], true)) {
            $tab = 'session';
        }
        $activeTab = $tab;

        return view('admin.settings.session-recording', compact('settings', 'activeTab'));
    }

    /**
     * Update session recording settings
     */
    public function updateSessionRecordingSettings(Request $request)
    {
        $meta = $this->getSessionRecordingSettingsMeta();

        $request->validate([
            'session_recording_max_bytes_per_session' => ['nullable', 'string', 'max:32'],
            'session_recording_max_replay_bytes' => ['nullable', 'string', 'max:32'],
            'session_recording_json_max_depth' => ['nullable', 'string', 'max:16'],
            'session_recording_cdn_rrweb' => ['nullable', 'string', 'max:2048'],
            'session_recording_cdn_player' => ['nullable', 'string', 'max:2048'],
            'session_recording_cdn_player_css' => ['nullable', 'string', 'max:2048'],
            'user_heatmap_max_clicks_per_ingest' => ['nullable', 'string', 'max:16'],
            'user_heatmap_admin_max_points_per_response' => ['nullable', 'string', 'max:16'],
        ]);

        $maxIn = $request->input('session_recording_max_bytes_per_session');
        if ($maxIn !== null && $maxIn !== '') {
            $maxIn = (int) $maxIn;
            if ($maxIn < 1_048_576 || $maxIn > 536_870_912) {
                return redirect()->back()->withInput()->with('error', 'Max bytes per session must be between 1 MB and 512 MB.');
            }
        }

        $maxReplay = $request->input('session_recording_max_replay_bytes');
        if ($maxReplay !== null && $maxReplay !== '') {
            $maxReplay = (int) $maxReplay;
            if ($maxReplay < 1_048_576 || $maxReplay > 536_870_912) {
                return redirect()->back()->withInput()->with('error', 'Max replay bytes must be between 1 MB and 512 MB.');
            }
        }

        $depth = $request->input('session_recording_json_max_depth');
        if ($depth !== null && $depth !== '') {
            $depth = (int) $depth;
            if ($depth < 512 || $depth > 2_000_000) {
                return redirect()->back()->withInput()->with('error', 'JSON depth must be between 512 and 2,000,000.');
            }
        }

        foreach (['session_recording_cdn_rrweb', 'session_recording_cdn_player', 'session_recording_cdn_player_css'] as $urlKey) {
            $u = $request->input($urlKey);
            if ($u !== null && trim((string) $u) !== '') {
                $validator = \Illuminate\Support\Facades\Validator::make([$urlKey => trim((string) $u)], [$urlKey => 'url']);
                if ($validator->fails()) {
                    return redirect()->back()->withInput()->with('error', 'Invalid URL for '.$meta[$urlKey]['label'].'.');
                }
            }
        }

        $intFieldKeys = [
            'session_recording_max_bytes_per_session',
            'session_recording_max_replay_bytes',
            'session_recording_json_max_depth',
            'user_heatmap_max_clicks_per_ingest',
            'user_heatmap_admin_max_points_per_response',
        ];
        foreach ($intFieldKeys as $numKey) {
            $raw = $request->input($numKey);
            $s = trim((string) ($raw ?? ''));
            if ($s !== '' && ! ctype_digit($s)) {
                return redirect()->back()->withInput()->with('error', 'Use whole numbers only for '.$meta[$numKey]['label'].'.');
            }
        }

        $heatmapBatch = $request->input('user_heatmap_max_clicks_per_ingest');
        if ($heatmapBatch !== null && trim((string) $heatmapBatch) !== '') {
            $heatmapBatch = (int) $heatmapBatch;
            if ($heatmapBatch < 1 || $heatmapBatch > 500) {
                return redirect()->back()->withInput()->with('error', 'Max clicks per ingest must be between 1 and 500.');
            }
        }

        $heatmapPoints = $request->input('user_heatmap_admin_max_points_per_response');
        if ($heatmapPoints !== null && trim((string) $heatmapPoints) !== '') {
            $heatmapPoints = (int) $heatmapPoints;
            if ($heatmapPoints < 100 || $heatmapPoints > 100_000) {
                return redirect()->back()->withInput()->with('error', 'Max points per admin heatmap response must be between 100 and 100,000.');
            }
        }

        foreach ($meta as $key => $config) {
            $group = $config['setting_group'] ?? 'session_recording';

            if ($config['type'] === 'boolean') {
                $value = $request->boolean($key) ? '1' : '0';
                Setting::set($key, $value, $group);

                continue;
            }

            if (! $request->has($key)) {
                continue;
            }

            $value = $request->input($key);
            if (is_string($value)) {
                $value = trim($value);
            }
            Setting::set($key, $value === null ? '' : (string) $value, $group);
        }

        $returnTab = (string) $request->input('settings_active_tab', 'session');
        if (! in_array($returnTab, ['session', 'heatmap'], true)) {
            $returnTab = 'session';
        }

        return redirect()->route('admin.settings.session-recording', ['tab' => $returnTab])->with('success', 'Session recording and heatmap settings updated.');
    }

    /**
     * Credit top-up settings configuration
     */
    protected function getCreditTopupSettings(): array
    {
        return [
            'credit_topup_enabled' => ['label' => 'Enable credit top-up', 'type' => 'boolean', 'default' => '1'],
            'credit_topup_min_amount' => ['label' => 'Minimum top-up amount', 'type' => 'text', 'default' => '5'],
            'credit_topup_max_amount' => ['label' => 'Maximum top-up amount', 'type' => 'text', 'default' => '10000'],
            'credit_topup_stripe_enabled' => ['label' => 'Allow Stripe (card) for credit top-up', 'type' => 'boolean', 'default' => '1'],
            'credit_topup_payhere_enabled' => ['label' => 'Allow PayHere for credit top-up', 'type' => 'boolean', 'default' => '0'],
        ];
    }

    /**
     * Show credit top-up settings page
     */
    public function creditTopupSettings()
    {
        $defaults = $this->getCreditTopupSettings();
        $saved = Setting::getAll();

        $settings = [];
        foreach ($defaults as $key => $config) {
            $settings[$key] = [
                'label' => $config['label'],
                'type' => $config['type'],
                'value' => $saved[$key] ?? $config['default'],
            ];
        }

        return view('admin.settings.credit-topup', compact('settings'));
    }

    /**
     * Update credit top-up settings
     */
    public function updateCreditTopupSettings(Request $request)
    {
        $defaults = $this->getCreditTopupSettings();

        $request->validate([
            'credit_topup_min_amount' => 'required|numeric|min:0',
            'credit_topup_max_amount' => 'required|numeric|min:0',
        ]);

        $min = (float) $request->input('credit_topup_min_amount');
        $max = (float) $request->input('credit_topup_max_amount');
        if ($max < $min) {
            return redirect()->back()->withInput()->with('error', 'Maximum amount must be greater than or equal to minimum amount.');
        }

        foreach ($defaults as $key => $config) {
            if ($request->has($key)) {
                $value = $request->input($key);
                if ($config['type'] === 'boolean') {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                }
                Setting::set($key, $value, 'credit_topup');
            }
        }

        return redirect()->route('admin.settings.credit-topup')->with('success', 'Credit top-up settings updated successfully!');
    }

    /**
     * Special Offers Modal settings (image + when to show)
     */
    public function specialOffersModalSettings()
    {
        $imagePath = Setting::get('special_offers_modal_image', '');
        $frequency = Setting::get('special_offers_modal_frequency', 'once');
        $imageUrl = $imagePath && Storage::disk('public')->exists($imagePath)
            ? Storage::disk('public')->url($imagePath)
            : null;

        return view('admin.settings.special-offers-modal', compact('imagePath', 'imageUrl', 'frequency'));
    }

    /**
     * Update Special Offers Modal settings
     */
    public function updateSpecialOffersModalSettings(Request $request)
    {
        $request->validate([
            'special_offers_modal_frequency' => 'required|in:once,daily,on_login,always',
            'special_offers_modal_image' => 'nullable|image|max:2048',
        ]);

        $frequency = $request->input('special_offers_modal_frequency');
        Setting::set('special_offers_modal_frequency', $frequency, 'promo');

        if ($request->hasFile('special_offers_modal_image')) {
            $oldPath = Setting::get('special_offers_modal_image');
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
            $file = $request->file('special_offers_modal_image');
            $path = $file->store('promo_modal', 'public');
            Setting::set('special_offers_modal_image', $path, 'promo');
        }

        if ($request->input('special_offers_modal_remove_image') === '1') {
            $oldPath = Setting::get('special_offers_modal_image');
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
            Setting::set('special_offers_modal_image', '', 'promo');
        }

        return redirect()->route('admin.settings.special-offers-modal')->with('success', 'Special offers modal settings updated.');
    }

    /**
     * Show settings page
     */
    public function settings(Request $request)
    {
        $defaults = $this->getDefaultSettings();
        $saved = Setting::getAll();

        $settings = [];
        foreach ($defaults as $group => $items) {
            foreach ($items as $key => $config) {
                $settings[$group][$key] = [
                    'label' => $config['label'],
                    'type' => $config['type'],
                    'value' => $saved[$key] ?? $config['default'],
                    'options' => $config['options'] ?? [],
                ];
            }
        }

        // Default Currency dropdown: options from currencies table
        $currencies = Currency::active()->ordered()->get();
        $settings['currency']['default_currency']['options'] = $currencies->mapWithKeys(function ($c) {
            $label = $c->code.($c->symbol ? ' ('.$c->symbol.')' : '');

            return [$c->code => $label];
        })->all();
        if (empty($settings['currency']['default_currency']['value']) && $currencies->isNotEmpty()) {
            $defaultCurrency = Currency::getDefault();
            $settings['currency']['default_currency']['value'] = $defaultCurrency ? $defaultCurrency->code : $currencies->first()->code;
        }

        $activeTab = $request->get('tab', 'general');
        if (! in_array($activeTab, ['general', 'features', 'ai', 'currency', 'explore'])) {
            $activeTab = 'general';
        }

        return view('admin.settings.index', compact('settings', 'activeTab'));
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        $defaults = $this->getDefaultSettings();

        foreach ($defaults as $group => $items) {
            foreach ($items as $key => $config) {
                if (is_array($config) && isset($config['label'])) {
                    if ($request->has($key)) {
                        $value = $request->input($key);
                        if ($config['type'] === 'boolean') {
                            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                        }
                        // Don't overwrite password-type fields when empty (keep existing value)
                        if ($config['type'] === 'password' && empty($value)) {
                            continue;
                        }
                        Setting::set($key, $value, $group);
                    }
                }
            }
        }

        $tab = $request->input('tab', 'general');
        if (! in_array($tab, ['general', 'features', 'ai', 'currency', 'explore'])) {
            $tab = 'general';
        }

        return redirect()->route('admin.settings', ['tab' => $tab])->with('success', 'Settings updated successfully!');
    }

    /**
     * Editor settings configuration
     */
    protected function getEditorSettings(): array
    {
        return [
            'editor_default_canvas_width' => ['label' => 'Default Canvas Width', 'type' => 'number', 'default' => '800'],
            'editor_default_canvas_height' => ['label' => 'Default Canvas Height', 'type' => 'number', 'default' => '1000'],
            'editor_default_bg_color' => ['label' => 'Default Background Color', 'type' => 'text', 'default' => '#ffffff'],
            'editor_show_menu_bar' => ['label' => 'Show Menu Bar', 'type' => 'boolean', 'default' => '1'],
            'editor_show_context_menu' => ['label' => 'Show Context Menu', 'type' => 'boolean', 'default' => '1'],
            'editor_show_rulers' => ['label' => 'Show Rulers by Default', 'type' => 'boolean', 'default' => '1'],
            'editor_grid_snap' => ['label' => 'Enable Grid Snap', 'type' => 'boolean', 'default' => '1'],
            'editor_auto_save' => ['label' => 'Enable Auto-Save', 'type' => 'boolean', 'default' => '1'],
            // Element panel items
            'editor_element_heading' => ['label' => 'Heading (Text)', 'type' => 'boolean', 'default' => '1'],
            'editor_element_subheading' => ['label' => 'Subheading (Text)', 'type' => 'boolean', 'default' => '1'],
            'editor_element_body' => ['label' => 'Body Text', 'type' => 'boolean', 'default' => '1'],
            'editor_element_rectangle' => ['label' => 'Rectangle (Shape)', 'type' => 'boolean', 'default' => '1'],
            'editor_element_circle' => ['label' => 'Circle (Shape)', 'type' => 'boolean', 'default' => '1'],
            'editor_element_line' => ['label' => 'Line (Shape)', 'type' => 'boolean', 'default' => '1'],
            'editor_element_triangle' => ['label' => 'Triangle (Shape)', 'type' => 'boolean', 'default' => '1'],
            'editor_element_table' => ['label' => 'Table', 'type' => 'boolean', 'default' => '1'],
            'editor_element_upload_image' => ['label' => 'Upload Image', 'type' => 'boolean', 'default' => '1'],
            // Image convert/reduce
            'editor_image_reduce_on_add' => ['label' => 'Reduce Image Size on Add', 'type' => 'boolean', 'default' => '0'],
            'editor_image_max_dimension' => ['label' => 'Max Image Dimension (px)', 'type' => 'number', 'default' => '2000'],
            'editor_image_quality' => ['label' => 'Image Quality (0.1-1)', 'type' => 'number', 'default' => '0.8'],
        ];
    }

    /**
     * Show editor settings page
     */
    public function editorSettings(Request $request)
    {
        $defaults = $this->getEditorSettings();
        $saved = Setting::getAll();

        $settingsByTab = [
            'canvas' => ['editor_default_canvas_width', 'editor_default_canvas_height', 'editor_default_bg_color'],
            'options' => ['editor_show_menu_bar', 'editor_show_context_menu', 'editor_show_rulers', 'editor_grid_snap', 'editor_auto_save'],
            'image' => ['editor_image_reduce_on_add', 'editor_image_max_dimension', 'editor_image_quality'],
            'elements' => [
                'editor_element_heading', 'editor_element_subheading', 'editor_element_body',
                'editor_element_rectangle', 'editor_element_circle', 'editor_element_line', 'editor_element_triangle',
                'editor_element_table', 'editor_element_upload_image',
            ],
        ];

        $settings = [];
        foreach ($defaults as $key => $config) {
            $settings[$key] = [
                'label' => $config['label'],
                'type' => $config['type'],
                'value' => $saved[$key] ?? $config['default'],
            ];
        }

        $settingsGrouped = [];
        foreach ($settingsByTab as $tab => $keys) {
            $settingsGrouped[$tab] = array_intersect_key($settings, array_flip($keys));
        }

        $contextMenuLinks = json_decode($saved['editor_context_menu_links'] ?? '[]', true) ?: [];
        $menuBarLinks = json_decode($saved['editor_menu_bar_links'] ?? '[]', true) ?: [];

        $activeTab = $request->get('tab', 'canvas');

        return view('admin.settings.editor', [
            'settings' => $settingsGrouped,
            'activeTab' => $activeTab,
            'contextMenuLinks' => $contextMenuLinks,
            'menuBarLinks' => $menuBarLinks,
        ]);
    }

    /**
     * Update editor settings
     */
    public function updateEditorSettings(Request $request)
    {
        $defaults = $this->getEditorSettings();

        foreach ($defaults as $key => $config) {
            if ($request->has($key)) {
                $value = $request->input($key);
                if ($config['type'] === 'boolean') {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                }
                Setting::set($key, $value, 'editor');
            }
        }

        // Handle custom context menu links (JSON array)
        if ($request->has('editor_context_menu_links')) {
            $linksJson = $request->input('editor_context_menu_links');
            $links = json_decode($linksJson, true);
            Setting::set('editor_context_menu_links', is_array($links) ? json_encode($links) : '[]', 'editor');
        }

        // Handle custom menu bar links (JSON array)
        if ($request->has('editor_menu_bar_links')) {
            $linksJson = $request->input('editor_menu_bar_links');
            $links = json_decode($linksJson, true);
            Setting::set('editor_menu_bar_links', is_array($links) ? json_encode($links) : '[]', 'editor');
        }

        $tab = $request->input('tab', 'canvas');
        $tabParam = in_array($tab, ['canvas', 'options', 'image', 'elements']) ? $tab : 'canvas';

        return redirect()->route('admin.settings.editor', ['tab' => $tabParam])->with('success', 'Editor settings updated successfully!');
    }

    /**
     * UI Theme settings configuration
     */
    protected function getThemeSettings(): array
    {
        return [
            'theme_primary_color' => ['label' => 'Primary Color', 'type' => 'color', 'default' => '#6366f1'],
            'theme_secondary_color' => ['label' => 'Secondary Color', 'type' => 'color', 'default' => '#8b5cf6'],
            'theme_navbar_bg_start' => ['label' => 'Navbar Gradient Start', 'type' => 'color', 'default' => '#6366f1'],
            'theme_navbar_bg_end' => ['label' => 'Navbar Gradient End', 'type' => 'color', 'default' => '#8b5cf6'],
            'theme_navbar_padding_y' => ['label' => 'Navbar Padding (vertical)', 'type' => 'select', 'default' => '0.5rem', 'options' => ['0.35rem' => 'Small', '0.5rem' => 'Medium', '0.75rem' => 'Large', '1rem' => 'Extra Large']],
            'theme_navbar_font_size' => ['label' => 'Navbar Font Size', 'type' => 'select', 'default' => '0.9375rem', 'options' => ['0.8125rem' => 'Small', '0.9375rem' => 'Medium', '1rem' => 'Large']],
            'theme_border_radius' => ['label' => 'Border Radius', 'type' => 'select', 'default' => '6px', 'options' => ['4px' => 'Small (4px)', '6px' => 'Medium (6px)', '8px' => 'Large (8px)', '12px' => 'Extra Large (12px)']],
            'theme_btn_border_radius' => ['label' => 'Button Border Radius', 'type' => 'select', 'default' => '4px', 'options' => ['4px' => 'Small', '6px' => 'Medium', '8px' => 'Large', '12px' => 'Pill']],
            'theme_sidebar_width' => ['label' => 'Sidebar Width', 'type' => 'select', 'default' => '250px', 'options' => ['220px' => 'Narrow (220px)', '250px' => 'Medium (250px)', '280px' => 'Wide (280px)', '300px' => 'Extra Wide (300px)']],
        ];
    }

    /**
     * Show UI theme settings page
     */
    public function themeSettings()
    {
        $defaults = $this->getThemeSettings();
        $saved = Setting::getAll();

        $settings = [];
        foreach ($defaults as $key => $config) {
            $settings[$key] = [
                'label' => $config['label'],
                'type' => $config['type'],
                'value' => $saved[$key] ?? $config['default'],
                'options' => $config['options'] ?? [],
            ];
        }

        return view('admin.settings.theme', compact('settings'));
    }

    /**
     * Update UI theme settings
     */
    public function updateThemeSettings(Request $request)
    {
        $defaults = $this->getThemeSettings();

        foreach ($defaults as $key => $config) {
            if ($request->has($key)) {
                $value = $request->input($key);
                Setting::set($key, $value, 'theme');
            }
        }

        return redirect()->route('admin.settings.theme')->with('success', 'UI theme settings updated successfully!');
    }
}
