<?php

namespace App\Http\Controllers;

use App\Models\AddressBook;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;

class UserController extends Controller
{
    /**
     * Show user settings page (profile + password).
     */
    public function settings()
    {
        $user = Auth::user();

        return view('user.settings', compact('user'));
    }

    /**
     * Update user profile and optionally password.
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:2000'],
        ];

        if ($request->filled('current_password') || $request->filled('password')) {
            $rules['current_password'] = ['required', 'current_password'];
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->address = $validated['address'] ?? null;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('user.settings')->with('success', 'Settings saved successfully.');
    }

    /**
     * Save required phone + address from the user-panel modal (cannot skip).
     */
    public function saveRequiredContact(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:40'],
            'address' => ['required', 'string', 'max:2000'],
        ]);

        $user = Auth::user();
        $user->phone = $validated['phone'];
        $user->address = $validated['address'];
        $user->save();

        return redirect()->back()->with('success', 'Your phone number and address have been saved.');
    }

    /**
     * Address book – list saved addresses.
     */
    public function addressBook()
    {
        $addresses = AddressBook::where('user_id', auth()->id())->latest()->get();

        return view('user.address-book', compact('addresses'));
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

        return redirect()->route('user.address-book')->with('success', 'Address added.');
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

        return redirect()->route('user.address-book')->with('success', 'Address updated.');
    }

    public function addressBookDestroy($id)
    {
        $entry = AddressBook::where('user_id', auth()->id())->findOrFail($id);
        $entry->delete();

        return redirect()->route('user.address-book')->with('success', 'Address removed.');
    }

    public function addressBookExportCsv()
    {
        $addresses = AddressBook::where('user_id', auth()->id())->orderBy('contact_name')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="address-book-'.date('Y-m-d').'.csv"',
        ];

        $callback = function () use ($addresses) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
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
     * Redirect to Google OAuth to import contacts (People API). Callback is handled in AuthController.
     */
    public function redirectToGoogleContacts()
    {
        $clientId = Setting::get('google_client_id') ?: config('services.google.client_id');
        $clientSecret = Setting::get('google_client_secret') ?: config('services.google.client_secret');
        $redirect = Setting::get('google_redirect_uri') ?: config('services.google.redirect');
        if ($clientId && $clientSecret) {
            config([
                'services.google.client_id' => $clientId,
                'services.google.client_secret' => $clientSecret,
                'services.google.redirect' => $redirect ?: (config('app.url').'/auth/google/callback'),
            ]);
        }
        session()->put('address_book_google_import', true);

        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/contacts.readonly'])
            ->redirect();
    }
}
