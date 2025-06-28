<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\MealPlan;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::with(['mealPlan', 'deliveryAddress'])
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
                });
            });
        }

        $subscriptions = $query->latest()->paginate(10)->withQueryString();

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
            }
        ]);

        return Inertia::render('User/Subscriptions/Show', [
            'subscription' => $subscription
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'meal_plan_id' => 'required|exists:meal_plans,id',
            'delivery_address_id' => 'required|exists:user_addresses,id',
            'start_date' => 'required|date|after_or_equal:today',
            'delivery_frequency' => 'required|in:daily,weekly,monthly',
            'delivery_days' => 'required|array|min:1',
            'delivery_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'preferred_delivery_time' => 'required|string',
            'duration_months' => 'required|integer|min:1|max:12'
        ]);

        // Verify meal plan is active
        $mealPlan = MealPlan::where('id', $request->meal_plan_id)
            ->where('is_active', true)
            ->firstOrFail();

        // Verify address belongs to user
        $address = UserAddress::where('id', $request->delivery_address_id)
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
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = $startDate->copy()->addMonths($request->duration_months);

            // Calculate pricing based on frequency and duration
            $basePrice = $mealPlan->price_per_meal;
            $deliveriesPerWeek = count($request->delivery_days);

            $weeklyPrice = $basePrice * $deliveriesPerWeek;
            $monthlyPrice = $weeklyPrice * 4; // Approximate 4 weeks per month
            $totalPrice = $monthlyPrice * $request->duration_months;

            // Apply discounts for longer subscriptions
            $discountRate = 0;
            if ($request->duration_months >= 6) {
                $discountRate = 0.1; // 10% discount for 6+ months
            } elseif ($request->duration_months >= 3) {
                $discountRate = 0.05; // 5% discount for 3+ months
            }

            $discountAmount = $totalPrice * $discountRate;
            $finalPrice = $totalPrice - $discountAmount;

            $subscription = Subscription::create([
                'user_id' => auth()->id(),
                'meal_plan_id' => $request->meal_plan_id,
                'delivery_address_id' => $request->delivery_address_id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'delivery_frequency' => $request->delivery_frequency,
                'delivery_days' => $request->delivery_days,
                'preferred_delivery_time' => $request->preferred_delivery_time,
                'price_per_meal' => $basePrice,
                'total_price' => $finalPrice,
                'discount_amount' => $discountAmount,
                'status' => 'active',
                'next_delivery_date' => $this->calculateNextDeliveryDate($startDate, $request->delivery_days)
            ]);

            DB::commit();

            return redirect()->route('subscriptions.show', $subscription)
                ->with('success', 'Subscription created successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['message' => 'Failed to create subscription. Please try again.']);
        }
    }

    public function update(Request $request, Subscription $subscription)
    {
        // Ensure user can only update their own subscriptions
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'delivery_frequency' => 'required|in:daily,weekly,monthly',
            'delivery_days' => 'required|array|min:1',
            'delivery_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'preferred_delivery_time' => 'required|string',
            'delivery_address_id' => 'required|exists:user_addresses,id',
        ]);

        // Verify address belongs to user
        $address = UserAddress::where('id', $request->delivery_address_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $subscription->update([
            'delivery_frequency' => $request->delivery_frequency,
            'delivery_days' => $request->delivery_days,
            'preferred_delivery_time' => $request->preferred_delivery_time,
            'delivery_address_id' => $request->delivery_address_id,
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
            'status' => 'paused',
            'paused_at' => now()
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
            'paused_at' => null,
            'next_delivery_date' => $nextDeliveryDate
        ]);

        return back()->with('success', 'Subscription resumed successfully!');
    }

    public function cancel(Subscription $subscription)
    {
        // Ensure user can only cancel their own subscriptions
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($subscription->status, ['active', 'paused'])) {
            return back()->withErrors(['message' => 'This subscription cannot be cancelled.']);
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now()
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
