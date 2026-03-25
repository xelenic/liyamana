<?php

namespace App\Http\Controllers;

use App\Models\AddressBook;
use App\Models\Order;
use App\Models\ScheduledMail;
use App\Models\Setting;
use App\Models\Template;
use Illuminate\Http\Request;

class EnterpriseController extends Controller
{
    /**
     * Enterprise dashboard – overview, stats, shortcuts.
     */
    public function dashboard()
    {
        $userId = auth()->id();

        $stats = [
            'pending_orders' => Order::where('user_id', $userId)->where('status', '!=', 'completed')->count(),
            'completed_orders' => Order::where('user_id', $userId)->where('status', 'completed')->count(),
            'addresses' => AddressBook::where('user_id', $userId)->count(),
            'scheduled_pending' => ScheduledMail::where('user_id', $userId)->where('status', 'pending')->count(),
        ];

        $balance = (float) (auth()->user()->balance ?? 0);

        $recentOrders = Order::where('user_id', $userId)
            ->with('template')
            ->latest()
            ->limit(6)
            ->get();

        $upcomingScheduled = ScheduledMail::where('user_id', $userId)
            ->where('status', 'pending')
            ->where('send_at', '>', now())
            ->orderBy('send_at')
            ->limit(5)
            ->get(['id', 'template_name', 'send_at', 'status', 'credit_amount']);

        $failedScheduledCount = ScheduledMail::where('user_id', $userId)->where('status', 'failed')->count();

        $defaultCreditCost = (float) Setting::get('scheduled_mail_credit_cost', '1.00');

        return view('enterprise.dashboard', compact(
            'stats',
            'balance',
            'recentOrders',
            'upcomingScheduled',
            'failedScheduledCount',
            'defaultCreditCost'
        ));
    }

    /**
     * Mail box – paginated orders (letter / template purchases).
     * filter: completed | pending (default pending = non-completed).
     */
    public function mailbox(Request $request)
    {
        $filter = $request->get('filter', 'pending');

        $query = Order::where('user_id', auth()->id())->with('template')->latest();

        if ($filter === 'completed') {
            $query->where('status', 'completed');
        } elseif ($filter === 'pending') {
            $query->where('status', '!=', 'completed');
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('enterprise.index', compact('orders', 'filter'));
    }

    /**
     * Address book page – list and manage saved addresses.
     */
    public function addressBook()
    {
        $addresses = AddressBook::where('user_id', auth()->id())->latest()->get();

        return view('enterprise.address-book', compact('addresses'));
    }

    public function addressBookStore(Request $request)
    {
        $valid = $request->validate([
            'label' => ['nullable', 'string', 'max:64'],
            'contact_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:64'],
            'state' => ['nullable', 'string', 'max:64'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'size:2'],
        ]);

        $valid['user_id'] = auth()->id();
        AddressBook::create($valid);

        return redirect()->route('enterprise.address-book')->with('success', 'Address added.');
    }

    public function addressBookUpdate(Request $request, $id)
    {
        $entry = AddressBook::where('user_id', auth()->id())->findOrFail($id);

        $valid = $request->validate([
            'label' => ['nullable', 'string', 'max:64'],
            'contact_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:64'],
            'state' => ['nullable', 'string', 'max:64'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'size:2'],
        ]);

        $entry->update($valid);

        return redirect()->route('enterprise.address-book')->with('success', 'Address updated.');
    }

    public function addressBookDestroy($id)
    {
        $entry = AddressBook::where('user_id', auth()->id())->findOrFail($id);
        $entry->delete();

        return redirect()->route('enterprise.address-book')->with('success', 'Address removed.');
    }

    /**
     * Export address book as CSV download.
     */
    public function addressBookExportCsv()
    {
        $addresses = AddressBook::where('user_id', auth()->id())->orderBy('contact_name')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="address-book-'.date('Y-m-d').'.csv"',
        ];

        $callback = function () use ($addresses) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($out, ['label', 'contact_name', 'email', 'phone', 'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country']);
            foreach ($addresses as $a) {
                fputcsv($out, [
                    $a->label,
                    $a->contact_name,
                    $a->email,
                    $a->phone,
                    $a->address_line1,
                    $a->address_line2,
                    $a->city,
                    $a->state,
                    $a->postal_code,
                    $a->country,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import addresses from uploaded CSV.
     * Expected columns: label, contact_name, email, phone, address_line1, address_line2, city, state, postal_code, country
     * First row can be header (skipped if column names match).
     */
    public function addressBookImportCsv(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $userId = auth()->id();
        $imported = 0;
        $errors = [];

        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle);
            if ($header === false) {
                fclose($handle);

                return redirect()->route('enterprise.address-book')->with('error', 'CSV file is empty.');
            }
            $header = array_map('trim', array_map(function ($c) {
                return str_replace("\xEF\xBB\xBF", '', $c);
            }, $header));
            $colMap = array_flip($header);

            $expected = ['label', 'contact_name', 'email', 'phone', 'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country'];
            $hasContactName = isset($colMap['contact_name']);
            $rowNum = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                if (count($row) < 2) {
                    continue;
                }
                $data = [];
                foreach ($expected as $key) {
                    $data[$key] = isset($colMap[$key]) && isset($row[$colMap[$key]]) ? trim($row[$colMap[$key]]) : null;
                }
                if (empty($data['contact_name']) && ! $hasContactName) {
                    $data['contact_name'] = trim($row[0] ?? '') ?: 'Unknown';
                }
                if (empty($data['contact_name'])) {
                    $errors[] = "Row {$rowNum}: contact name is required.";

                    continue;
                }
                $data['contact_name'] = mb_substr($data['contact_name'], 0, 255);
                $data['user_id'] = $userId;
                try {
                    AddressBook::create($data);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNum}: ".$e->getMessage();
                }
            }
            fclose($handle);
        }

        $msg = $imported.' address(es) imported.';
        if (count($errors) > 0) {
            $msg .= ' '.count($errors).' row(s) skipped: '.implode(' ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $msg .= ' ...';
            }
        }

        return redirect()->route('enterprise.address-book')->with('success', $msg);
    }

    /**
     * Schedule mail page – list scheduled mails and form to add new.
     */
    public function scheduleMail()
    {
        $scheduled = ScheduledMail::where('user_id', auth()->id())
            ->with('template', 'addressBook')
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('send_at')
            ->get();

        $templates = Template::where('is_active', true)
            ->where(function ($q) {
                $q->where('is_public', true)->orWhere('created_by', auth()->id());
            })
            ->orderBy('name')
            ->get(['id', 'name', 'variables']);

        $addresses = AddressBook::where('user_id', auth()->id())->orderBy('contact_name')->get();

        $defaultCreditCost = (float) Setting::get('scheduled_mail_credit_cost', '1.00');

        return view('enterprise.schedule-mail', compact('scheduled', 'templates', 'addresses', 'defaultCreditCost'));
    }

    public function scheduleMailStore(Request $request)
    {
        $valid = $request->validate([
            'template_id' => ['required', 'exists:templates,id'],
            'address_book_id' => ['required', 'exists:address_books,id'],
            'send_at' => ['required', 'date', 'after:now'],
            'credit_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $template = Template::findOrFail($valid['template_id']);
        if (! $template->is_active || (! $template->is_public && (int) $template->created_by !== (int) auth()->id())) {
            return redirect()->back()->with('error', 'Template is not available.');
        }

        $address = AddressBook::where('user_id', auth()->id())->findOrFail($valid['address_book_id']);
        $creditAmount = isset($valid['credit_amount']) && $valid['credit_amount'] > 0
            ? (float) $valid['credit_amount']
            : (float) Setting::get('scheduled_mail_credit_cost', '1.00');

        $userBalance = (float) (auth()->user()->balance ?? 0);
        if ($userBalance < $creditAmount) {
            return redirect()->back()->with('error', 'Insufficient balance. Required: '.format_price($creditAmount).', available: '.format_price($userBalance));
        }

        $recipientSnapshot = [
            'contact_name' => $address->contact_name,
            'email' => $address->email,
            'phone' => $address->phone,
            'address_line1' => $address->address_line1,
            'address_line2' => $address->address_line2,
            'city' => $address->city,
            'state' => $address->state,
            'postal_code' => $address->postal_code,
            'country' => $address->country,
        ];

        $variables = $request->input('variables', []);
        if (! is_array($variables)) {
            $variables = [];
        }

        $checkoutData = [
            'quantity' => 1,
            'total_cost' => (string) $creditAmount,
            'is_letter' => true,
            'items' => [
                [
                    'address' => $recipientSnapshot,
                    'variables' => $variables,
                ],
            ],
        ];

        ScheduledMail::create([
            'user_id' => auth()->id(),
            'template_id' => $template->id,
            'template_name' => $template->name,
            'address_book_id' => $address->id,
            'recipient_snapshot' => $recipientSnapshot,
            'send_at' => $valid['send_at'],
            'credit_amount' => $creditAmount,
            'checkout_data' => $checkoutData,
            'quantity' => 1,
            'status' => 'pending',
        ]);

        return redirect()->route('enterprise.schedule-mail')->with('success', 'Mail scheduled. Credit will be deducted automatically when it is sent.');
    }

    public function scheduleMailDestroy($id)
    {
        $scheduled = ScheduledMail::where('user_id', auth()->id())->findOrFail($id);
        if ($scheduled->status !== 'pending') {
            return redirect()->route('enterprise.schedule-mail')->with('error', 'Only pending scheduled mails can be cancelled.');
        }
        $scheduled->update(['status' => 'cancelled']);

        return redirect()->route('enterprise.schedule-mail')->with('success', 'Scheduled mail cancelled.');
    }
}
