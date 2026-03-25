<?php

namespace App\Http\Controllers;

use App\Models\AiContentGeneration;
use App\Models\AiContentTemplate;
use App\Models\CreditTransaction;
use App\Models\Order;
use App\Models\ScheduledMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportsController extends Controller
{
    /**
     * Reports hub: quick KPIs for the last 30 days.
     */
    public function index()
    {
        $periodDays = 30;
        $from = now()->subDays($periodDays - 1)->startOfDay();
        $to = now()->endOfDay();

        $stats = [
            'orders_count' => Order::whereBetween('created_at', [$from, $to])->count(),
            'orders_revenue_completed' => (float) Order::whereBetween('created_at', [$from, $to])->where('status', 'completed')->sum('total_amount'),
            'new_users' => User::whereBetween('created_at', [$from, $to])->count(),
            'ai_generations' => AiContentGeneration::whereBetween('created_at', [$from, $to])->count(),
            'credit_topups' => (float) CreditTransaction::whereBetween('created_at', [$from, $to])->where('amount', '>', 0)->sum('amount'),
            'scheduled_mail_pending' => ScheduledMail::where('status', 'pending')->count(),
        ];

        return view('admin.reports.index', compact('stats', 'periodDays', 'from', 'to'));
    }

    /**
     * Orders and revenue with date range and breakdowns.
     */
    public function orders(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : now()->endOfDay();
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : (clone $to)->copy()->subDays(29)->startOfDay();

        if ($from->gt($to)) {
            $from = (clone $to)->copy()->subDays(29)->startOfDay();
        }

        $base = Order::query()->whereBetween('created_at', [$from, $to]);

        $summary = [
            'count' => (clone $base)->count(),
            'revenue_completed' => (float) (clone $base)->where('status', 'completed')->sum('total_amount'),
            'revenue_all_statuses' => (float) (clone $base)->sum('total_amount'),
        ];

        $avgOrder = $summary['count'] > 0 ? $summary['revenue_all_statuses'] / $summary['count'] : 0.0;

        $byStatus = (clone $base)
            ->select('status', DB::raw('count(*) as cnt'), DB::raw('coalesce(sum(total_amount),0) as total'))
            ->groupBy('status')
            ->orderByDesc('cnt')
            ->get();

        $byPayment = (clone $base)
            ->select('payment_method', DB::raw('count(*) as cnt'), DB::raw('coalesce(sum(total_amount),0) as total'))
            ->groupBy('payment_method')
            ->orderByDesc('cnt')
            ->get();

        $daily = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn (Order $o) => $o->created_at->format('Y-m-d'))
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'date' => $first->created_at->format('Y-m-d'),
                    'label' => $first->created_at->format('M j, Y'),
                    'count' => $group->count(),
                    'revenue' => (float) $group->sum('total_amount'),
                ];
            })
            ->values()
            ->sortBy('date')
            ->values();

        $topTemplates = (clone $base)
            ->select('template_id', DB::raw('max(template_name) as template_name'), DB::raw('count(*) as cnt'), DB::raw('coalesce(sum(total_amount),0) as total'))
            ->groupBy('template_id')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        return view('admin.reports.orders', compact(
            'from',
            'to',
            'summary',
            'avgOrder',
            'byStatus',
            'byPayment',
            'daily',
            'topTemplates'
        ));
    }

    /**
     * Credit transactions: top-ups vs spend, by type and day.
     */
    public function credits(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : now()->endOfDay();
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : (clone $to)->copy()->subDays(29)->startOfDay();

        if ($from->gt($to)) {
            $from = (clone $to)->copy()->subDays(29)->startOfDay();
        }

        $base = CreditTransaction::query()->whereBetween('created_at', [$from, $to]);

        $summary = [
            'topups' => (float) (clone $base)->where('amount', '>', 0)->sum('amount'),
            'spend' => abs((float) (clone $base)->where('amount', '<', 0)->sum('amount')),
            'net' => (float) (clone $base)->sum('amount'),
            'count' => (clone $base)->count(),
        ];

        $byType = (clone $base)
            ->select('type', DB::raw('count(*) as cnt'), DB::raw('coalesce(sum(amount),0) as total'))
            ->groupBy('type')
            ->orderByDesc('cnt')
            ->get();

        $byPaymentRef = (clone $base)
            ->select('payment_method', DB::raw('count(*) as cnt'), DB::raw('coalesce(sum(amount),0) as total'))
            ->groupBy('payment_method')
            ->orderByDesc('cnt')
            ->get();

        $daily = CreditTransaction::query()
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn (CreditTransaction $t) => $t->created_at->format('Y-m-d'))
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'date' => $first->created_at->format('Y-m-d'),
                    'label' => $first->created_at->format('M j, Y'),
                    'net' => (float) $group->sum('amount'),
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->sortBy('date')
            ->values();

        $recent = CreditTransaction::query()
            ->with('user:id,name,email')
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('admin.reports.credits', compact(
            'from',
            'to',
            'summary',
            'byType',
            'byPaymentRef',
            'daily',
            'recent'
        ));
    }

    /**
     * AI generations, scheduled mail, signups in range.
     */
    public function activity(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : now()->endOfDay();
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : (clone $to)->copy()->subDays(29)->startOfDay();

        if ($from->gt($to)) {
            $from = (clone $to)->copy()->subDays(29)->startOfDay();
        }

        $newUsers = User::whereBetween('created_at', [$from, $to])->count();

        $aiByDay = AiContentGeneration::query()
            ->whereBetween('created_at', [$from, $to])
            ->with('aiContentTemplate:id,name')
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn (AiContentGeneration $g) => $g->created_at->format('Y-m-d'))
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'date' => $first->created_at->format('Y-m-d'),
                    'label' => $first->created_at->format('M j, Y'),
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->sortBy('date')
            ->values();

        $aiTotal = AiContentGeneration::whereBetween('created_at', [$from, $to])->count();

        $scheduledCreated = ScheduledMail::whereBetween('created_at', [$from, $to])->count();
        $scheduledByStatus = ScheduledMail::query()
            ->whereBetween('created_at', [$from, $to])
            ->select('status', DB::raw('count(*) as cnt'))
            ->groupBy('status')
            ->get();

        $topAiTemplates = AiContentGeneration::query()
            ->whereBetween('created_at', [$from, $to])
            ->select('ai_content_template_id', DB::raw('count(*) as cnt'))
            ->groupBy('ai_content_template_id')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get();

        $templateNames = AiContentTemplate::whereIn('id', $topAiTemplates->pluck('ai_content_template_id')->filter())->pluck('name', 'id');

        return view('admin.reports.activity', compact(
            'from',
            'to',
            'newUsers',
            'aiByDay',
            'aiTotal',
            'scheduledCreated',
            'scheduledByStatus',
            'topAiTemplates',
            'templateNames'
        ));
    }
}
