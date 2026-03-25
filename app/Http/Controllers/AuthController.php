<?php

namespace App\Http\Controllers;

use App\Models\AddressBook;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('design.templates.explore'));
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Show the registration form
     */
    public function showRegisterForm()
    {
        if (! filter_var(Setting::get('allow_registration', '1'), FILTER_VALIDATE_BOOLEAN)) {
            return redirect()->route('login')->with('error', 'Registration is currently disabled.');
        }

        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        if (! filter_var(Setting::get('allow_registration', '1'), FILTER_VALIDATE_BOOLEAN)) {
            return redirect()->route('login')->with('error', 'Registration is currently disabled.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign default 'user' role
        $user->assignRole('user');

        Auth::login($user);

        return redirect()->route('design.templates.explore');
    }

    /**
     * Apply Google OAuth config from settings (overrides config/env)
     */
    protected function applyGoogleOAuthConfig(): void
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
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        $this->applyGoogleOAuthConfig();

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback (login or sign up)
     */
    public function handleGoogleCallback(Request $request)
    {
        $request->validate(['state' => 'nullable|string']);

        $this->applyGoogleOAuthConfig();

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            return redirect()->route('login')->with('error', 'Invalid state. Please try again.');
        } catch (\Throwable $e) {
            return redirect()->route('login')->with('error', 'Could not sign in with Google. Please try again.');
        }

        // Address book import flow: fetch Google contacts and redirect back to address-book
        if (session()->pull('address_book_google_import')) {
            $userId = auth()->id();
            if (! $userId) {
                return redirect()->route('user.address-book')->with('error', 'Please log in first.');
            }
            $imported = $this->importGoogleContactsToAddressBook($googleUser->token, $userId);

            return redirect()->route('user.address-book')->with('success', $imported > 0
                ? "Imported {$imported} contact(s) from your Google account."
                : 'No contacts to import from your Google account.');
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()->intended(route('design.templates.explore'));
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);
            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()->intended(route('design.templates.explore'));
        }

        if (! filter_var(Setting::get('allow_registration', '1'), FILTER_VALIDATE_BOOLEAN)) {
            return redirect()->route('login')->with('error', 'Registration is disabled. This email is not linked to an account.');
        }

        $user = User::create([
            'name' => $googleUser->getName() ?: $googleUser->getEmail(),
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
            'password' => Hash::make(Str::random(32)),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('user');
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('design.templates.explore');
    }

    /**
     * Fetch Google People API connections and create AddressBook entries for the user.
     *
     * @return int Number of contacts imported (created)
     */
    protected function importGoogleContactsToAddressBook(string $accessToken, int $userId): int
    {
        $personFields = 'names,emailAddresses,phoneNumbers,addresses';
        $url = 'https://people.googleapis.com/v1/people/me/connections?'.http_build_query([
            'personFields' => $personFields,
            'pageSize' => 100,
        ]);
        $created = 0;
        $pageToken = null;

        do {
            $requestUrl = $url;
            if ($pageToken) {
                $requestUrl .= '&'.http_build_query(['pageToken' => $pageToken]);
            }
            $response = Http::withToken($accessToken)->get($requestUrl);
            if (! $response->successful()) {
                \Illuminate\Support\Facades\Log::warning('Google People API error: '.$response->body());
                break;
            }
            $data = $response->json();
            $connections = $data['connections'] ?? [];
            foreach ($connections as $person) {
                $contactName = $person['names'][0]['displayName'] ?? '';
                $email = $person['emailAddresses'][0]['value'] ?? null;
                $phone = $person['phoneNumbers'][0]['value'] ?? null;
                if ($contactName === '' && empty($email)) {
                    continue;
                }
                $contactName = $contactName ?: ($email ?? 'Unknown');
                $addr = $person['addresses'][0] ?? null;
                $addressLine1 = $addr['streetAddress'] ?? null;
                $addressLine2 = $addr['extendedAddress'] ?? null;
                $city = $addr['city'] ?? null;
                $state = $addr['region'] ?? null;
                $postalCode = $addr['postalCode'] ?? null;
                $country = isset($addr['countryCode']) ? strtoupper(substr($addr['countryCode'], 0, 2)) : null;
                if ($phone !== null) {
                    $phone = preg_replace('/[^\d+\-\s]/', '', $phone);
                    $phone = strlen($phone) > 32 ? substr($phone, 0, 32) : $phone;
                }
                $existing = AddressBook::where('user_id', $userId)
                    ->where('contact_name', $contactName)
                    ->when($email, fn ($q) => $q->where('email', $email))
                    ->when(! $email, fn ($q) => $q->whereNull('email'))
                    ->exists();
                if ($existing) {
                    continue;
                }
                AddressBook::create([
                    'user_id' => $userId,
                    'contact_name' => $contactName,
                    'email' => $email,
                    'phone' => $phone ?: null,
                    'address_line1' => $addressLine1,
                    'address_line2' => $addressLine2,
                    'city' => $city,
                    'state' => $state,
                    'postal_code' => $postalCode,
                    'country' => $country,
                ]);
                $created++;
            }
            $pageToken = $data['nextPageToken'] ?? null;
        } while ($pageToken);

        return $created;
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Show the forgot password form.
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send the password reset link.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }

    /**
     * Show the reset password form (with token from email link).
     */
    public function showResetPasswordForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
