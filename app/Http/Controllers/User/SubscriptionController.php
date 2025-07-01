<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\MealPlan;
use App\Models\UserAddress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::with(['mealPlan', 'deliveryAddress', 'latestOrder', 'payments'])
            ->where('user_id', auth()->id());

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by plan type
        if ($request->filled('plan_type')) {
            $query->whereHas('mealPlan', function ($q) use ($request) {
                $q->where('type', $request->plan_type);
            });
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('mealPlan', function ($mealPlanQuery) use ($search) {
                    $mealPlanQuery->where('name', 'like', "%{$search}%");
                })->orWhere('subscription_number', 'like', "%{$search}%");
            });
        }

        $subscriptions = $query->latest()->paginate(10)->withQueryString();

        // Transform data to ensure delivery_address is always available
        $subscriptions->getCollection()->transform(function ($subscription) {
            // Ensure delivery_address is loaded and available
            if (!$subscription->deliveryAddress) {
                // If no delivery address, create a placeholder
                $subscription->delivery_address = (object) [
                    'id' => null,
                    'address_line_1' => 'Address not available',
                    'city' => 'N/A',
                    'province' => 'N/A',
                    'state' => 'N/A'
                ];
            } else {
                $subscription->delivery_address = $subscription->deliveryAddress;
            }

            // Ensure all required fields are available
            $subscription->price_per_meal = $subscription->price_per_meal;
            $subscription->delivery_frequency = $subscription->frequency;

            return $subscription;
        });

        // Calculate statistics
        $stats = [
            'active_subscriptions' => Subscription::where('user_id', auth()->id())
                ->where('status', 'active')->count(),
            'paused_subscriptions' => Subscription::where('user_id', auth()->id())
                ->where('status', 'paused')->count(),
            'total_deliveries' => DB::table('orders')
                ->join('subscriptions', 'orders.subscription_id', '=', 'subscriptions.id')
                ->where('subscriptions.user_id', auth()->id())
                ->where('orders.status', 'delivered')
                ->count(),
            'monthly_savings' => Subscription::where('user_id', auth()->id())
                ->where('status', 'active')
                ->sum('discount_amount'),
        ];

        $subscriptionStatuses = [
            'pending' => 'Pending',
            'active' => 'Active',
            'paused' => 'Paused',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired'
        ];

        return Inertia::render('User/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'subscriptionStatuses' => $subscriptionStatuses,
            'stats' => $stats,
            'filters' => $request->only(['status', 'plan_type', 'search'])
        ]);
    }

    public function show(Subscription $subscription)
    {
        // Ensure user can only view their own subscriptions
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        $subscription->load([
            'mealPlan.menuItems',
            'deliveryAddress',
            'orders' => function ($query) {
                $query->latest()->limit(10);
            },
            'payments'
        ]);

        // Ensure delivery_address is available
        if (!$subscription->deliveryAddress) {
            $subscription->delivery_address = (object) [
                'id' => null,
                'address_line_1' => 'Address not available',
                'city' => 'N/A',
                'province' => 'N/A',
                'state' => 'N/A'
            ];
        } else {
            $subscription->delivery_address = $subscription->deliveryAddress;
        }

        return Inertia::render('User/Subscriptions/Show', [
            'subscription' => [
                'id' => $subscription->id,
                'subscription_number' => $subscription->subscription_number,
                'start_date' => $subscription->start_date->format('Y-m-d'),
                'end_date' => $subscription->end_date->format('Y-m-d'),
                'status' => $subscription->status,
                'frequency' => $subscription->frequency,
                'meals_per_day' => $subscription->meals_per_day,
                'delivery_days' => $subscription->delivery_days,
                'delivery_time_preference' => $subscription->delivery_time_preference,
                'total_price' => $subscription->total_price,
                'discount_amount' => $subscription->discount_amount,
                'special_requirements' => $subscription->special_requirements,
                'next_delivery_date' => $subscription->next_delivery_date?->format('Y-m-d H:i:s'),
                'auto_renew' => $subscription->auto_renew,
                'can_be_paused' => $subscription->status === 'active',
                'can_be_resumed' => $subscription->status === 'paused',
                'can_be_cancelled' => in_array($subscription->status, ['active', 'paused']),
                'can_pay' => $subscription->orders()->where('payment_status', 'unpaid')->exists(),
                'created_at' => $subscription->created_at->format('Y-m-d H:i:s'),
                'meal_plan' => [
                    'id' => $subscription->mealPlan->id,
                    'name' => $subscription->mealPlan->name,
                    'description' => $subscription->mealPlan->description,
                    'price_per_meal' => $subscription->mealPlan->price_per_meal,
                    'plan_type' => $subscription->mealPlan->plan_type,
                    'image' => $subscription->mealPlan->image,
                ],
                'delivery_address' => $subscription->delivery_address,
                'orders' => $subscription->orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'delivery_date' => $order->delivery_date?->format('Y-m-d'),
                        'status' => $order->status,
                        'payment_status' => $order->payment_status,
                        'total_amount' => $order->total_amount,
                        'can_pay' => $order->can_pay, // Menggunakan accessor dari model Order
                    ];
                }),
                'payments' => $subscription->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'payment_number' => $payment->payment_number,
                        'amount' => $payment->amount,
                        'status' => $payment->status,
                        'payment_method' => $payment->payment_method,
                        'payment_date' => $payment->payment_date?->format('Y-m-d H:i:s'),
                    ];
                }),
            ]
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'meal_plan_id' => 'required|exists:meal_plans,id',
            'user_address_id' => 'required|exists:user_addresses,id',
            'start_date' => 'required|date|after_or_equal:today',
            'frequency' => 'required|in:daily,weekly,monthly',
            'delivery_days' => 'required|array|min:1',
            'delivery_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'delivery_time_preference' => 'required|string',
            'duration_weeks' => 'required|integer|min:1|max:52',
            'meals_per_day' => 'required|integer|min:1|max:5',
            'payment_method' => 'required|in:credit_card,bank_transfer,e_wallet,cash',
            'special_requirements' => 'nullable|string|max:1000'
        ]);

        // Verify meal plan is active
        $mealPlan = MealPlan::where('id', $request->meal_plan_id)
            ->where('is_active', true)
            ->firstOrFail();

        // Verify address belongs to user
        $address = UserAddress::where('id', $request->user_address_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Check if user already has active subscription for this meal plan
        $existingSubscription = Subscription::where('user_id', auth()->id())
            ->where('meal_plan_id', $request->meal_plan_id)
            ->where('status', 'active')
            ->first();

        if ($existingSubscription) {
            return back()->withErrors(['message' => 'You already have an active subscription for this meal plan.']);
        }

        DB::beginTransaction();
        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = $startDate->copy()->addWeeks($request->duration_weeks);

            // Calculate pricing
            $basePrice = $mealPlan->price_per_meal;
            $deliveriesPerWeek = count($request->delivery_days);
            $mealsPerDay = $request->meals_per_day;
            $totalWeeks = $request->duration_weeks;

            $mealsPerWeek = $deliveriesPerWeek * $mealsPerDay;
            $totalMeals = $mealsPerWeek * $totalWeeks;
            $subtotal = $basePrice * $totalMeals;

            // Apply discounts based on duration
            $discountRate = 0;
            if ($totalWeeks >= 52) {
                $discountRate = 0.15; // 15% discount for 1 year
            } elseif ($totalWeeks >= 12) {
                $discountRate = 0.10; // 10% discount for 3 months
            } elseif ($totalWeeks >= 4) {
                $discountRate = 0.05; // 5% discount for 1 month
            }

            $discountAmount = $subtotal * $discountRate;
            $finalPrice = $subtotal - $discountAmount;

            // Create subscription with pending status
            $subscription = Subscription::create([
                'user_id' => auth()->id(),
                'meal_plan_id' => $request->meal_plan_id,
                'user_address_id' => $request->user_address_id,
                'subscription_number' => 'SUB-' . strtoupper(Str::random(8)),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'frequency' => $request->frequency,
                'delivery_days' => $request->delivery_days,
                'delivery_time_preference' => $request->delivery_time_preference,
                'meals_per_day' => $mealsPerDay,
                'total_price' => $finalPrice,
                'discount_amount' => $discountAmount,
                'status' => 'pending', // Changed from 'active' to 'pending'
                'next_delivery_date' => $this->calculateNextDeliveryDate($startDate, $request->delivery_days),
                'auto_renew' => true,
                'special_requirements' => $request->special_requirements
            ]);

            // Create payment record with pending status
            $payment = Payment::create([
                'subscription_id' => $subscription->id,
                'payment_number' => 'PAY-' . strtoupper(Str::random(8)),
                'amount' => $finalPrice,
                'payment_method' => $request->payment_method,
                'status' => 'pending', // Payment is pending
            ]);

            // Don't generate orders yet - wait for payment completion

            DB::commit();

            // Redirect to payment page instead of subscription show
            return redirect()->route('user.subscriptions.payment', $subscription)
                ->with('success', 'Subscription created successfully! Please complete payment to activate.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['message' => 'Failed to create subscription: ' . $e->getMessage()]);
        }
    }

    public function paymentPage(Subscription $subscription)
    {
        // Ensure user can only pay for their own subscriptions
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if subscription is pending and payment is unpaid
        if ($subscription->status !== 'pending') {
            return redirect()->route('user.subscriptions.show', $subscription)
                ->with('error', 'This subscription cannot be paid or is already active');
        }

        $subscription->load([
            'mealPlan',
            'deliveryAddress',
            'payment'
        ]);

        return Inertia::render('User/Subscriptions/Payment', [
            'subscription' => [
                'id' => $subscription->id,
                'subscription_number' => $subscription->subscription_number,
                'start_date' => $subscription->start_date->format('Y-m-d'),
                'end_date' => $subscription->end_date->format('Y-m-d'),
                'frequency' => $subscription->frequency,
                'delivery_days' => $subscription->delivery_days,
                'meals_per_day' => $subscription->meals_per_day,
                'total_price' => $subscription->total_price,
                'discount_amount' => $subscription->discount_amount,
                'status' => $subscription->status,
                'meal_plan' => [
                    'id' => $subscription->mealPlan->id,
                    'name' => $subscription->mealPlan->name,
                    'description' => $subscription->mealPlan->description,
                    'price_per_meal' => $subscription->mealPlan->price_per_meal,
                ],
                'delivery_address' => $subscription->deliveryAddress ? [
                    'id' => $subscription->deliveryAddress->id,
                    'recipient_name' => $subscription->deliveryAddress->recipient_name,
                    'phone_number' => $subscription->deliveryAddress->phone_number,
                    'full_address' => $subscription->deliveryAddress->full_address,
                    'city' => $subscription->deliveryAddress->city,
                    'province' => $subscription->deliveryAddress->province,
                    'postal_code' => $subscription->deliveryAddress->postal_code,
                ] : null,
                'payment' => [
                    'id' => $subscription->payment->id,
                    'payment_number' => $subscription->payment->payment_number,
                    'amount' => $subscription->payment->amount,
                    'status' => $subscription->payment->status,
                    'payment_method' => $subscription->payment->payment_method,
                ]
            ]
        ]);
    }

    public function processPayment(Request $request, Subscription $subscription)
    {
        // Ensure user can only pay for their own subscriptions
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if subscription is pending and payment is unpaid
        if ($subscription->status !== 'pending' || !$subscription->payment || $subscription->payment->status !== 'pending') {
            return redirect()->route('user.subscriptions.show', $subscription)
                ->with('error', 'This subscription cannot be paid or is already active');
        }

        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $paymentMethod = $subscription->payment->payment_method;
            $transactionId = '';

            // Simulate payment method integration (same as OrderController)
            switch ($paymentMethod) {
                case 'cash':
                    $transactionId = 'CASH-' . strtoupper(uniqid());
                    $gatewayResponse = [
                        'message' => 'Cash payment will be collected on delivery',
                        'status' => 'success'
                    ];
                    break;

                case 'bank_transfer':
                    $transactionId = 'BT-' . strtoupper(uniqid());
                    $gatewayResponse = [
                        'virtual_account' => '1234567890123456',
                        'bank_name' => 'Bank Mandiri',
                        'account_name' => 'SEA Catering',
                        'expired_at' => now()->addHours(24)->toISOString()
                    ];
                    break;

                case 'credit_card':
                    $transactionId = 'CC-' . strtoupper(uniqid());
                    $gatewayResponse = [
                        'message' => 'Credit card payment successful',
                        'status' => 'success'
                    ];
                    break;

                case 'e_wallet':
                    $transactionId = 'EW-' . strtoupper(uniqid());
                    $gatewayResponse = [
                        'qr_code' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
                        'deep_link' => 'gojek://pay?amount=' . $subscription->payment->amount,
                        'expired_at' => now()->addMinutes(15)->toISOString()
                    ];
                    break;

                default:
                    throw new \Exception('Payment method not recognized');
            }

            // Update payment record
            $subscription->payment->update([
                'transaction_id' => $transactionId,
                'gateway' => $paymentMethod,
                'gateway_response' => $gatewayResponse,
                'status' => 'completed',
                'paid_at' => now(),
                'payment_date' => now(),
                'notes' => $request->notes,
            ]);

            // Update subscription status to active
            $subscription->update([
                'status' => 'active',
            ]);

            // Now generate orders for the subscription
            $this->generateSubscriptionOrders($subscription);

            DB::commit();

            return redirect()->route('user.subscriptions.show', $subscription)
                ->with('success', 'Payment successful! Your subscription is now active.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Payment processing failed: ' . $e->getMessage());
        }
    }

    private function generateSubscriptionOrders(Subscription $subscription)
    {
        $currentDate = Carbon::parse($subscription->start_date);
        $endDate = Carbon::parse($subscription->end_date);
        $deliveryDays = $subscription->delivery_days;
        $dayMap = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 0
        ];

        // Delete existing future orders for this subscription
        Order::where('subscription_id', $subscription->id)
            ->where('delivery_date', '>', now())
            ->where('status', 'pending')
            ->delete();

        while ($currentDate->lte($endDate)) {
            foreach ($deliveryDays as $day) {
                $deliveryDate = $currentDate->copy()->next($dayMap[$day]);

                if ($deliveryDate->lte($endDate) && $deliveryDate->gte(now()->startOfDay())) {
                    // Calculate order totals
                    $subtotal = $subscription->mealPlan->price_per_meal * $subscription->meals_per_day;
                    $taxAmount = $subtotal * 0.1; // 10% tax
                    $deliveryFee = 15000; // Fixed delivery fee
                    $totalAmount = $subtotal + $taxAmount + $deliveryFee;

                    // Create order
                    $order = Order::create([
                        'user_id' => $subscription->user_id,
                        'subscription_id' => $subscription->id,
                        'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                        'order_type' => 'subscription',
                        'delivery_address_id' => $subscription->user_address_id,
                        'delivery_date' => $deliveryDate,
                        'delivery_time_slot' => $subscription->delivery_time_preference,
                        'subtotal' => $subtotal,
                        'tax_amount' => $taxAmount,
                        'delivery_fee' => $deliveryFee,
                        'total_amount' => $totalAmount,
                        'status' => 'pending',
                        'payment_status' => 'paid', // Subscription orders are pre-paid
                        'special_instructions' => $subscription->special_requirements
                    ]);

                    // Create order items (get random menu items from the meal plan)
                    $menuItems = $subscription->mealPlan->menuItems()
                        ->where('is_available', true)
                        ->inRandomOrder()
                        ->limit($subscription->meals_per_day)
                        ->get();

                    foreach ($menuItems as $menuItem) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'menu_item_id' => $menuItem->id,
                            'quantity' => 1,
                            'unit_price' => $subscription->mealPlan->price_per_meal,
                            'total_price' => $subscription->mealPlan->price_per_meal
                        ]);
                    }

                    // Create payment record for each order (already paid via subscription)
                    Payment::create([
                        'order_id' => $order->id,
                        'subscription_id' => $subscription->id,
                        'payment_number' => 'PAY-' . strtoupper(Str::random(8)),
                        'amount' => $totalAmount,
                        'payment_method' => $subscription->payment->payment_method,
                        'status' => 'completed',
                        'paid_at' => now(),
                        'payment_date' => now(),
                        'notes' => 'Paid via subscription payment',
                    ]);
                }
            }

            $currentDate->addWeek();
        }
    }

    public function update(Request $request, Subscription $subscription)
    {
        // Ensure user can only update their own subscriptions
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'frequency' => 'required|in:daily,weekly,monthly',
            'delivery_days' => 'required|array|min:1',
            'delivery_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'delivery_time_preference' => 'required|string',
            'user_address_id' => 'required|exists:user_addresses,id',
            'meals_per_day' => 'required|integer|min:1|max:5'
        ]);

        // Verify address belongs to user
        $address = UserAddress::where('id', $request->user_address_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $subscription->update([
            'frequency' => $request->frequency,
            'delivery_days' => $request->delivery_days,
            'delivery_time_preference' => $request->delivery_time_preference,
            'user_address_id' => $request->user_address_id,
            'meals_per_day' => $request->meals_per_day,
            'next_delivery_date' => $this->calculateNextDeliveryDate(now(), $request->delivery_days)
        ]);

        return back()->with('success', 'Subscription updated successfully!');
    }

    public function pause(Subscription $subscription)
    {
        // Ensure user can only pause their own subscriptions
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        if ($subscription->status !== 'active') {
            return back()->withErrors(['message' => 'Only active subscriptions can be paused.']);
        }

        $subscription->update([
            'status' => 'paused'
        ]);

        return back()->with('success', 'Subscription paused successfully!');
    }

    public function resume(Subscription $subscription)
    {
        // Ensure user can only resume their own subscriptions
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        if ($subscription->status !== 'paused') {
            return back()->withErrors(['message' => 'Only paused subscriptions can be resumed.']);
        }

        // Calculate new next delivery date
        $nextDeliveryDate = $this->calculateNextDeliveryDate(now(), $subscription->delivery_days);

        $subscription->update([
            'status' => 'active',
            'next_delivery_date' => $nextDeliveryDate
        ]);

        // Regenerate orders for resumed subscription
        $this->generateSubscriptionOrders($subscription);

        return back()->with('success', 'Subscription resumed successfully!');
    }

    public function cancel(Subscription $subscription)
    {
        // Ensure user can only cancel their own subscriptions
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($subscription->status, ['pending', 'paused', 'active'])) {
            return back()->withErrors(['message' => 'This subscription cannot be cancelled.']);
        }

        $subscription->update([
            'status' => 'cancelled'
        ]);

        return back()->with('success', 'Subscription cancelled successfully!');
    }

    private function calculateNextDeliveryDate($startDate, $deliveryDays)
    {
        $date = \Carbon\Carbon::parse($startDate);
        $dayMap = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 0
        ];

        // Find the next delivery day
        $currentDayOfWeek = $date->dayOfWeek;
        $nextDeliveryDay = null;

        foreach ($deliveryDays as $day) {
            $dayNumber = $dayMap[$day];
            if ($dayNumber >= $currentDayOfWeek) {
                $nextDeliveryDay = $dayNumber;
                break;
            }
        }

        // If no day found this week, use first day of next week
        if ($nextDeliveryDay === null) {
            $nextDeliveryDay = $dayMap[$deliveryDays[0]];
            $date->addWeek();
        }

        return $date->next($nextDeliveryDay);
    }
}
