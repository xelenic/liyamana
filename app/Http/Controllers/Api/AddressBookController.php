<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AddressBook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressBookController extends Controller
{
    /**
     * List address book entries for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $addresses = AddressBook::where('user_id', auth()->id())
            ->latest()
            ->get()
            ->map(fn ($a) => $this->formatAddress($a));

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }

    /**
     * Store a new address book entry.
     */
    public function store(Request $request): JsonResponse
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
        $entry = AddressBook::create($valid);

        return response()->json([
            'success' => true,
            'message' => 'Address added.',
            'data' => $this->formatAddress($entry),
        ], 201);
    }

    /**
     * Update an address book entry.
     */
    public function update(Request $request, string $id): JsonResponse
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

        return response()->json([
            'success' => true,
            'message' => 'Address updated.',
            'data' => $this->formatAddress($entry->fresh()),
        ]);
    }

    /**
     * Delete an address book entry.
     */
    public function destroy(string $id): JsonResponse
    {
        $entry = AddressBook::where('user_id', auth()->id())->findOrFail($id);
        $entry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address removed.',
        ]);
    }

    private function formatAddress(AddressBook $a): array
    {
        return [
            'id' => $a->id,
            'label' => $a->label,
            'contact_name' => $a->contact_name,
            'email' => $a->email,
            'phone' => $a->phone,
            'address_line1' => $a->address_line1,
            'address_line2' => $a->address_line2,
            'city' => $a->city,
            'state' => $a->state,
            'postal_code' => $a->postal_code,
            'country' => $a->country,
            'full_address' => $a->full_address,
            'created_at' => $a->created_at->toIso8601String(),
        ];
    }
}
