<?php

namespace App\Http\Controllers;

use App\Models\EnvelopeType;
use App\Models\Product;
use App\Models\SheetType;
use App\Models\StockPurchase;
use App\Models\Supplier;
use App\Services\StockPurchaseService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class AdminStockController extends Controller
{
    public function index()
    {
        $products = Product::query()->orderBy('name')->get(['id', 'name', 'sku', 'stock_quantity']);
        $sheetTypes = SheetType::query()->orderBy('name')->get(['id', 'name', 'slug', 'stock_quantity']);
        $envelopeTypes = EnvelopeType::query()->orderBy('name')->get(['id', 'name', 'slug', 'stock_quantity']);

        return view('admin.stock.index', compact('products', 'sheetTypes', 'envelopeTypes'));
    }

    public function purchasesIndex(Request $request)
    {
        $query = StockPurchase::query()->with(['user', 'supplier'])->withCount('items');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', '%'.$s.'%')
                    ->orWhere('supplier_name', 'like', '%'.$s.'%')
                    ->orWhere('notes', 'like', '%'.$s.'%')
                    ->orWhereHas('supplier', function ($q) use ($s) {
                        $q->where('name', 'like', '%'.$s.'%')
                            ->orWhere('code', 'like', '%'.$s.'%');
                    });
            });
        }

        $purchases = $query->orderByDesc('purchased_at')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.stock.purchases.index', compact('purchases'));
    }

    public function createPurchase()
    {
        $products = Product::query()->orderBy('name')->get(['id', 'name']);
        $sheetTypes = SheetType::query()->orderBy('name')->get(['id', 'name', 'slug']);
        $envelopeTypes = EnvelopeType::query()->orderBy('name')->get(['id', 'name', 'slug']);
        $suppliers = Supplier::query()->active()->ordered()->get(['id', 'name', 'code']);

        return view('admin.stock.purchases.create', compact('products', 'sheetTypes', 'envelopeTypes', 'suppliers'));
    }

    public function storePurchase(Request $request, StockPurchaseService $service)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->where('is_active', true)],
            'notes' => 'nullable|string|max:5000',
            'purchased_at' => 'required|date',
            'payment_method' => 'required|string|in:cash,cheque,credit',
            'discount' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'additional_charges' => 'nullable|numeric|min:0',
            'lines' => 'required|array|min:1',
            'lines.*.item_type' => 'required|in:product,sheet_type,envelope_type',
            'lines.*.item_id' => 'required|integer|min:1',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        $lines = [];
        foreach ($validated['lines'] as $line) {
            $lines[] = [
                'item_type' => $line['item_type'],
                'item_id' => (int) $line['item_id'],
                'quantity' => (int) $line['quantity'],
                'unit_cost' => isset($line['unit_cost']) && $line['unit_cost'] !== '' && $line['unit_cost'] !== null
                    ? (float) $line['unit_cost']
                    : null,
            ];
        }

        try {
            $service->recordPurchase([
                'supplier_id' => (int) $validated['supplier_id'],
                'notes' => $validated['notes'] ?? null,
                'purchased_at' => $validated['purchased_at'],
                'payment_method' => $validated['payment_method'],
                'discount' => isset($validated['discount']) && $validated['discount'] !== '' ? (float) $validated['discount'] : 0,
                'deduction' => isset($validated['deduction']) && $validated['deduction'] !== '' ? (float) $validated['deduction'] : 0,
                'additional_charges' => isset($validated['additional_charges']) && $validated['additional_charges'] !== '' ? (float) $validated['additional_charges'] : 0,
            ], $lines);
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Could not record purchase: '.$e->getMessage());
        }

        return redirect()->route('admin.stock.purchases.index')->with('success', 'Purchase recorded and stock updated.');
    }

    public function showPurchase(StockPurchase $purchase)
    {
        $purchase->load(['items.purchasable', 'user', 'supplier']);

        return view('admin.stock.purchases.show', compact('purchase'));
    }
}
