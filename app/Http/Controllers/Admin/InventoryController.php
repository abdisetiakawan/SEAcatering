<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with('menuItem');

        // Search
        if ($request->filled('search')) {
            $query->whereHas('menuItem', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })->orWhere('supplier', 'like', '%' . $request->search . '%');
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low_stock':
                    $query->lowStock();
                    break;
                case 'out_of_stock':
                    $query->outOfStock();
                    break;
                case 'expiring_soon':
                    $query->expiringSoon();
                    break;
                case 'in_stock':
                    $query->whereRaw('current_stock > minimum_stock AND current_stock > 0');
                    break;
            }
        }

        // Filter by supplier
        if ($request->filled('supplier')) {
            $query->where('supplier', $request->supplier);
        }

        $inventory = $query->orderBy('current_stock', 'asc')->paginate(15);

        $stats = [
            'total_items' => Inventory::count(),
            'low_stock_items' => Inventory::lowStock()->count(),
            'out_of_stock_items' => Inventory::outOfStock()->count(),
            'expiring_soon_items' => Inventory::expiringSoon()->count(),
            'total_value' => Inventory::selectRaw('SUM(current_stock * cost_per_unit)')->value('sum') ?? 0,
        ];

        $suppliers = Inventory::distinct()->pluck('supplier')->filter()->sort()->values();

        return Inertia::render('Admin/InventoryManagement', [
            'inventory' => $inventory,
            'filters' => $request->only(['search', 'stock_status', 'supplier']),
            'stats' => $stats,
            'suppliers' => $suppliers,
            'stockStatusOptions' => [
                'in_stock' => 'In Stock',
                'low_stock' => 'Low Stock',
                'out_of_stock' => 'Out of Stock',
                'expiring_soon' => 'Expiring Soon'
            ]
        ]);
    }

    public function create()
    {
        $menuItems = MenuItem::whereDoesntHave('inventory')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/CreateInventoryItem', [
            'menuItems' => $menuItems
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id|unique:inventories',
            'current_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'maximum_stock' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'cost_per_unit' => 'required|numeric|min:0',
            'supplier' => 'required|string|max:255',
            'expiry_date' => 'nullable|date|after:today',
            'status' => 'required|string|in:active,inactive'
        ]);

        $validated['last_restocked_at'] = now();

        Inventory::create($validated);

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Inventory item created successfully.');
    }

    public function show(Inventory $inventory)
    {
        $inventory->load('menuItem');

        // Get stock movement history (you might want to create a separate model for this)
        $stockHistory = collect(); // Placeholder for stock movement history

        return Inertia::render('Admin/InventoryDetail', [
            'inventory' => $inventory,
            'stockHistory' => $stockHistory
        ]);
    }

    public function edit(Inventory $inventory)
    {
        $inventory->load('menuItem');

        return Inertia::render('Admin/EditInventoryItem', [
            'inventory' => $inventory
        ]);
    }

    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'current_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'maximum_stock' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'cost_per_unit' => 'required|numeric|min:0',
            'supplier' => 'required|string|max:255',
            'expiry_date' => 'nullable|date|after:today',
            'status' => 'required|string|in:active,inactive'
        ]);

        $inventory->update($validated);

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Inventory item updated successfully.');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Inventory item deleted successfully.');
    }

    public function updateStock(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer',
            'type' => 'required|string|in:add,subtract,set',
            'notes' => 'nullable|string'
        ]);

        $oldStock = $inventory->current_stock;

        switch ($validated['type']) {
            case 'add':
                $inventory->current_stock += $validated['quantity'];
                $inventory->last_restocked_at = now();
                break;
            case 'subtract':
                $inventory->current_stock = max(0, $inventory->current_stock - $validated['quantity']);
                break;
            case 'set':
                $inventory->current_stock = $validated['quantity'];
                break;
        }

        $inventory->save();

        // Log stock movement (you might want to create a separate model for this)
        // StockMovement::create([
        //     'inventory_id' => $inventory->id,
        //     'old_stock' => $oldStock,
        //     'new_stock' => $inventory->current_stock,
        //     'quantity_changed' => $validated['quantity'],
        //     'type' => $validated['type'],
        //     'notes' => $validated['notes'],
        //     'user_id' => auth()->id()
        // ]);

        return redirect()->back()
            ->with('success', 'Stock updated successfully.');
    }

    public function bulkUpdateStock(Request $request)
    {
        $validated = $request->validate([
            'inventory_ids' => 'required|array',
            'inventory_ids.*' => 'exists:inventories,id',
            'quantity' => 'required|integer',
            'type' => 'required|string|in:add,subtract'
        ]);

        $inventories = Inventory::whereIn('id', $validated['inventory_ids']);

        foreach ($inventories->get() as $inventory) {
            if ($validated['type'] === 'add') {
                $inventory->current_stock += $validated['quantity'];
                $inventory->last_restocked_at = now();
            } else {
                $inventory->current_stock = max(0, $inventory->current_stock - $validated['quantity']);
            }
            $inventory->save();
        }

        return redirect()->back()
            ->with('success', count($validated['inventory_ids']) . ' inventory items updated successfully.');
    }

    public function lowStockAlert()
    {
        $lowStockItems = Inventory::lowStock()
            ->with('menuItem')
            ->orderBy('current_stock', 'asc')
            ->get();

        return Inertia::render('Admin/LowStockAlert', [
            'lowStockItems' => $lowStockItems
        ]);
    }

    public function expiringItems()
    {
        $expiringItems = Inventory::expiringSoon()
            ->with('menuItem')
            ->orderBy('expiry_date', 'asc')
            ->get();

        return Inertia::render('Admin/ExpiringItems', [
            'expiringItems' => $expiringItems
        ]);
    }
}
