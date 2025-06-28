<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = auth()->user()->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('User/Addresses/Index', [
            'addresses' => $addresses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address_line_1' => 'required|string',
            'address_line_2' => 'nullable|string',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'country' => 'required|string|max:255',
            'delivery_instructions' => 'nullable|string',
            'address_type' => 'required|in:residential,commercial,apartment',
            'is_default' => 'boolean',
        ]);

        // If this is set as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            auth()->user()->addresses()->update(['is_default' => false]);
        }

        // If this is the first address, make it default
        if (auth()->user()->addresses()->count() === 0) {
            $validated['is_default'] = true;
        }

        auth()->user()->addresses()->create($validated);

        return back()->with('success', 'Alamat berhasil ditambahkan!');
    }

    public function update(Request $request, UserAddress $address)
    {
        // Check if user owns this address
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to address');
        }

        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address_line_1' => 'required|string',
            'address_line_2' => 'nullable|string',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'country' => 'required|string|max:255',
            'delivery_instructions' => 'nullable|string',
            'address_type' => 'required|in:residential,commercial,apartment',
            'is_default' => 'boolean',
        ]);

        // If this is set as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            auth()->user()->addresses()
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($validated);

        return back()->with('success', 'Alamat berhasil diperbarui!');
    }

    public function destroy(UserAddress $address)
    {
        // Check if user owns this address
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to address');
        }

        // Prevent deletion if it's the only address
        if (auth()->user()->addresses()->count() === 1) {
            return back()->withErrors(['message' => 'Tidak dapat menghapus alamat terakhir. Anda harus memiliki minimal satu alamat.']);
        }

        // If deleting default address, set another as default
        if ($address->is_default) {
            $nextAddress = auth()->user()->addresses()
                ->where('id', '!=', $address->id)
                ->first();

            if ($nextAddress) {
                $nextAddress->update(['is_default' => true]);
            }
        }

        $address->delete();

        return back()->with('success', 'Alamat berhasil dihapus!');
    }

    public function setDefault(UserAddress $address)
    {
        // Check if user owns this address
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to address');
        }

        // Unset all defaults for this user
        auth()->user()->addresses()->update(['is_default' => false]);

        // Set this address as default
        $address->update(['is_default' => true]);

        return back()->with('success', 'Alamat utama berhasil diperbarui!');
    }
}
