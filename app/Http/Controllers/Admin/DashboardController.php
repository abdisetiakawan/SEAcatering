<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\Inventory;
use App\Models\Review;
use App\Models\Payment;
use App\Models\Delivery;
use App\Models\Driver;
use App\Models\MealPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Cache dashboard data for 5 minutes to improve performance
        $cacheKey = 'admin_dashboard_' . auth()->id();

        $dashboardData = Cache::remember($cacheKey, 300, function () {
            return $this->getDashboardData();
        });

        return Inertia::render('Admin/Dashboard', $dashboardData);
    }

    private function getDashboardData()
    {
        // Get current date ranges
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisWeek = Carbon::now()->startOfWeek();
        $lastWeek = Carbon::now()->subWeek()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        $last30Days = Carbon::now()->subDays(30);
        $last7Days = Carbon::now()->subDays(7);

        return [
            'stats' => $this->calculateStats($today, $yesterday, $thisMonth, $lastMonth, $lastMonthEnd, $thisWeek, $lastWeek),
            'revenueChart' => $this->getRevenueChartData($last30Days),
            'subscriptionGrowth' => $this->getSubscriptionGrowthData($last30Days),
            'recentOrders' => $this->getRecentOrders(),
            'lowStockItems' => $this->getLowStockItems(),
            'deliverySchedule' => $this->getTodayDeliverySchedule($today),
            'performance' => $this->getPerformanceMetrics($last30Days),
            'recentActivity' => $this->getRecentActivity(),
            'quickStats' => $this->getQuickStats($today, $thisWeek, $thisMonth),
            'topMenuItems' => $this->getTopMenuItems($last7Days),
            'customerSatisfactionTrend' => $this->getCustomerSatisfactionTrend($last30Days),
        ];
    }

    private function calculateStats($today, $yesterday, $thisMonth, $lastMonth, $lastMonthEnd, $thisWeek, $lastWeek)
    {
        // Total Revenue (This Month vs Last Month)
        $thisMonthRevenue = Payment::where('status', 'completed')
            ->where('payment_date', '>=', $thisMonth)
            ->sum('amount');

        $lastMonthRevenue = Payment::where('status', 'completed')
            ->whereBetween('payment_date', [$lastMonth, $lastMonthEnd])
            ->sum('amount');

        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        // Today's Revenue
        $todayRevenue = Payment::where('status', 'completed')
            ->whereDate('payment_date', $today)
            ->sum('amount');

        $yesterdayRevenue = Payment::where('status', 'completed')
            ->whereDate('payment_date', $yesterday)
            ->sum('amount');

        // Active Subscriptions
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        $pausedSubscriptions = Subscription::where('status', 'paused')->count();
        $cancelledThisWeek = Subscription::where('status', 'cancelled')
            ->where('updated_at', '>=', $thisWeek)
            ->count();

        $newSubscriptionsThisWeek = Subscription::where('created_at', '>=', $thisWeek)->count();
        $newSubscriptionsLastWeek = Subscription::whereBetween('created_at', [$lastWeek, $thisWeek])->count();

        // Today's Orders
        $todayOrders = Order::whereDate('created_at', $today)->count();
        $yesterdayOrders = Order::whereDate('created_at', $yesterday)->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $preparingOrders = Order::where('status', 'preparing')->count();
        $readyOrders = Order::where('status', 'ready')->count();
        $outForDeliveryOrders = Order::where('status', 'out_for_delivery')->count();

        // Customer Satisfaction
        $averageRating = Review::where('is_published', true)
            ->where('created_at', '>=', $thisMonth)
            ->avg('rating') ?? 0;

        $totalReviews = Review::where('is_published', true)
            ->where('created_at', '>=', $thisMonth)
            ->count();

        $lastMonthRating = Review::where('is_published', true)
            ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])
            ->avg('rating') ?? 0;

        $ratingImprovement = $lastMonthRating > 0
            ? round((($averageRating - $lastMonthRating) / $lastMonthRating) * 100, 1)
            : 0;

        return [
            'totalRevenue' => $thisMonthRevenue,
            'revenueGrowth' => $revenueGrowth,
            'todayRevenue' => $todayRevenue,
            'yesterdayRevenue' => $yesterdayRevenue,
            'activeSubscriptions' => $activeSubscriptions,
            'pausedSubscriptions' => $pausedSubscriptions,
            'newSubscriptions' => $newSubscriptionsThisWeek,
            'cancelledSubscriptions' => $cancelledThisWeek,
            'subscriptionGrowth' => $newSubscriptionsLastWeek > 0
                ? round((($newSubscriptionsThisWeek - $newSubscriptionsLastWeek) / $newSubscriptionsLastWeek) * 100, 1)
                : 0,
            'todayOrders' => $todayOrders,
            'yesterdayOrders' => $yesterdayOrders,
            'pendingOrders' => $pendingOrders,
            'preparingOrders' => $preparingOrders,
            'readyOrders' => $readyOrders,
            'outForDeliveryOrders' => $outForDeliveryOrders,
            'averageRating' => round($averageRating, 1),
            'totalReviews' => $totalReviews,
            'ratingImprovement' => $ratingImprovement,
        ];
    }

    private function getRevenueChartData($startDate)
    {
        $revenueData = Payment::where('status', 'completed')
            ->where('payment_date', '>=', $startDate)
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartData = [];
        $currentDate = $startDate->copy();
        $endDate = Carbon::now();

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $revenue = $revenueData->firstWhere('date', $dateStr);

            $chartData[] = [
                'date' => $currentDate->format('M d'),
                'revenue' => $revenue ? $revenue->total : 0,
                'formatted_date' => $currentDate->format('d M Y'),
            ];

            $currentDate->addDay();
        }

        return $chartData;
    }

    private function getSubscriptionGrowthData($startDate)
    {
        $subscriptionData = Subscription::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartData = [];
        $currentDate = $startDate->copy();
        $endDate = Carbon::now();
        $cumulativeCount = Subscription::where('created_at', '<', $startDate)->count();

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $newSubs = $subscriptionData->firstWhere('date', $dateStr);
            $dailyCount = $newSubs ? $newSubs->count : 0;
            $cumulativeCount += $dailyCount;

            $chartData[] = [
                'date' => $currentDate->format('M d'),
                'new_subscriptions' => $dailyCount,
                'total_subscriptions' => $cumulativeCount,
                'formatted_date' => $currentDate->format('d M Y'),
            ];

            $currentDate->addDay();
        }

        return $chartData;
    }

    private function getRecentOrders()
    {
        return Order::with(['subscription.user', 'subscription.mealPlan'])
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer' => $order->subscription->user->name,
                    'customer_email' => $order->subscription->user->email,
                    'plan' => $order->subscription->mealPlan->name,
                    'delivery_date' => $order->delivery_date->format('d M Y'),
                    'delivery_time' => $order->delivery_time_slot,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at->diffForHumans(),
                ];
            });
    }

    private function getLowStockItems()
    {
        return Inventory::whereRaw('current_stock <= minimum_stock * 1.2') // Alert when stock is 20% above minimum
            ->orderByRaw('(current_stock::float / NULLIF(minimum_stock, 0)) ASC')
            ->take(6)
            ->get()
            ->map(function ($item) {
                $percentage = $item->minimum_stock > 0
                    ? ($item->current_stock / $item->minimum_stock) * 100
                    : 0;

                $status = 'good';
                if ($percentage <= 50) {
                    $status = 'critical';
                } elseif ($percentage <= 100) {
                    $status = 'low';
                } elseif ($percentage <= 120) {
                    $status = 'warning';
                }

                return [
                    'id' => $item->id,
                    'name' => $item->menuItem->name ?? 'Unknown',
                    'current' => $item->current_stock,
                    'minimum' => $item->minimum_stock,
                    'unit' => $item->unit,
                    'status' => $status,
                    'percentage' => round($percentage, 1),
                    'supplier' => $item->supplier,
                    'cost_per_unit' => $item->cost_per_unit,
                ];
            });
    }

    private function getTodayDeliverySchedule($today)
    {
        $timeSlots = [
            '07:00-09:00' => 'Breakfast',
            '12:00-14:00' => 'Lunch',
            '18:00-20:00' => 'Dinner'
        ];

        $schedule = [];

        foreach ($timeSlots as $slot => $period) {
            $orders = Order::with('delivery.driver')
                ->whereDate('delivery_date', $today)
                ->where('delivery_time_slot', $slot)
                ->get();

            $totalOrders = $orders->count();
            $completedOrders = $orders->where('status', 'delivered')->count();
            $inTransitOrders = $orders->where('status', 'out_for_delivery')->count();
            $readyOrders = $orders->where('status', 'ready')->count();
            $preparingOrders = $orders->where('status', 'preparing')->count();

            $driversAssigned = $orders->filter(function ($order) {
                return $order->delivery && $order->delivery->driver_id;
            })->count();

            // Determine status
            $status = 'on_schedule';
            $now = Carbon::now();
            $slotEnd = Carbon::createFromFormat('H:i', explode('-', $slot)[1]);

            if ($completedOrders === $totalOrders && $totalOrders > 0) {
                $status = 'completed';
            } elseif ($now > $slotEnd && $completedOrders < $totalOrders) {
                $status = 'delayed';
            } elseif ($preparingOrders > 0 && $now > $slotEnd->copy()->subHour()) {
                $status = 'behind_schedule';
            }

            $schedule[] = [
                'time' => $slot,
                'period' => $period,
                'total_orders' => $totalOrders,
                'completed' => $completedOrders,
                'in_transit' => $inTransitOrders,
                'ready' => $readyOrders,
                'preparing' => $preparingOrders,
                'drivers_assigned' => $driversAssigned,
                'drivers_needed' => max(0, $totalOrders - $driversAssigned),
                'status' => $status,
                'completion_rate' => $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0,
            ];
        }

        return $schedule;
    }

    private function getPerformanceMetrics($startDate)
    {
        // Order Fulfillment Rate
        $totalOrders = Order::where('created_at', '>=', $startDate)->count();
        $fulfilledOrders = Order::where('created_at', '>=', $startDate)
            ->where('status', 'delivered')
            ->count();
        $fulfillmentRate = $totalOrders > 0 ? round(($fulfilledOrders / $totalOrders) * 100, 1) : 0;

        // On-Time Delivery Rate
        $deliveredOrders = Order::with('delivery')
            ->where('created_at', '>=', $startDate)
            ->where('status', 'delivered')
            ->get();

        $onTimeDeliveries = $deliveredOrders->filter(function ($order) {
            if (!$order->delivery || !$order->delivery->delivered_at || !$order->delivery->estimated_delivery) {
                return false;
            }
            return $order->delivery->delivered_at <= $order->delivery->estimated_delivery;
        })->count();

        $onTimeDelivery = $deliveredOrders->count() > 0
            ? round(($onTimeDeliveries / $deliveredOrders->count()) * 100, 1)
            : 0;

        // Customer Satisfaction
        $recentReviews = Review::where('created_at', '>=', $startDate)
            ->where('is_published', true)
            ->get();

        $satisfiedReviews = $recentReviews->where('rating', '>=', 4)->count();
        $customerSatisfaction = $recentReviews->count() > 0
            ? round(($satisfiedReviews / $recentReviews->count()) * 100, 1)
            : 0;

        // Kitchen Efficiency
        $ordersInKitchen = Order::where('created_at', '>=', $startDate)
            ->whereIn('status', ['confirmed', 'preparing'])
            ->get();

        $avgPreparationTime = $ordersInKitchen->filter(function ($order) {
            return $order->status === 'ready' || $order->status === 'delivered';
        })->avg(function ($order) {
            $confirmed = $order->updated_at; // Simplified - should track status changes
            $ready = $order->updated_at; // Should be when status changed to 'ready'
            return $confirmed->diffInMinutes($ready);
        });

        $targetPreparationTime = 45; // minutes
        $kitchenEfficiency = $avgPreparationTime > 0
            ? round(min(100, ($targetPreparationTime / $avgPreparationTime) * 100), 1)
            : 100;

        // Revenue per Order
        $totalRevenue = Payment::where('status', 'completed')
            ->where('payment_date', '>=', $startDate)
            ->sum('amount');
        $revenuePerOrder = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 0) : 0;

        return [
            'fulfillmentRate' => $fulfillmentRate,
            'onTimeDelivery' => $onTimeDelivery,
            'customerSatisfaction' => $customerSatisfaction,
            'kitchenEfficiency' => $kitchenEfficiency,
            'revenuePerOrder' => $revenuePerOrder,
            'avgPreparationTime' => round($avgPreparationTime ?? 0, 1),
            'targetPreparationTime' => $targetPreparationTime,
        ];
    }

    private function getRecentActivity()
    {
        $activities = collect();

        // Recent orders (last 6 hours)
        $recentOrders = Order::with('subscription.user')
            ->where('created_at', '>=', Carbon::now()->subHours(6))
            ->latest()
            ->take(3)
            ->get();

        foreach ($recentOrders as $order) {
            $activities->push([
                'id' => 'order_' . $order->id,
                'type' => 'order',
                'title' => 'New Order Received',
                'description' => "Order #{$order->order_number} from {$order->subscription->user->name}",
                'amount' => $order->total_amount,
                'time' => $order->created_at->diffForHumans(),
                'timestamp' => $order->created_at,
                'link' => route('admin.orders.show', $order->id),
            ]);
        }

        // Recent user registrations (last 24 hours)
        $recentUsers = User::where('role', 'customer')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->latest()
            ->take(2)
            ->get();

        foreach ($recentUsers as $user) {
            $activities->push([
                'id' => 'user_' . $user->id,
                'type' => 'user',
                'title' => 'New User Registration',
                'description' => "{$user->name} joined SEA Catering",
                'time' => $user->created_at->diffForHumans(),
                'timestamp' => $user->created_at,
                'link' => route('admin.users.show', $user->id),
            ]);
        }

        // Recent payments (last 12 hours)
        $recentPayments = Payment::with('subscription.user')
            ->where('status', 'completed')
            ->where('payment_date', '>=', Carbon::now()->subHours(12))
            ->latest('payment_date')
            ->take(3)
            ->get();

        foreach ($recentPayments as $payment) {
            $activities->push([
                'id' => 'payment_' . $payment->id,
                'type' => 'payment',
                'title' => 'Payment Received',
                'description' => "Payment of Rp " . number_format($payment->amount, 0, ',', '.') . " from {$payment->subscription->user->name}",
                'amount' => $payment->amount,
                'time' => $payment->payment_date->diffForHumans(),
                'timestamp' => $payment->payment_date,
            ]);
        }

        // Recent reviews (last 24 hours)
        $recentReviews = Review::with('user')
            ->where('is_published', true)
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->latest()
            ->take(2)
            ->get();

        foreach ($recentReviews as $review) {
            $activities->push([
                'id' => 'review_' . $review->id,
                'type' => 'review',
                'title' => 'New Review Posted',
                'description' => "{$review->user->name} gave {$review->rating} stars: \"" . Str::limit($review->comment, 50) . "\"",
                'rating' => $review->rating,
                'time' => $review->created_at->diffForHumans(),
                'timestamp' => $review->created_at,
            ]);
        }

        // Recent deliveries completed (last 6 hours)
        $recentDeliveries = Delivery::with('order.subscription.user')
            ->where('status', 'delivered')
            ->where('delivered_at', '>=', Carbon::now()->subHours(6))
            ->latest('delivered_at')
            ->take(2)
            ->get();

        foreach ($recentDeliveries as $delivery) {
            $activities->push([
                'id' => 'delivery_' . $delivery->id,
                'type' => 'delivery',
                'title' => 'Order Delivered',
                'description' => "Order #{$delivery->order->order_number} delivered to {$delivery->order->subscription->user->name}",
                'time' => $delivery->delivered_at->diffForHumans(),
                'timestamp' => $delivery->delivered_at,
            ]);
        }

        return $activities->sortByDesc('timestamp')->take(12)->values();
    }

    private function getQuickStats($today, $thisWeek, $thisMonth)
    {
        return [
            'total_customers' => User::where('role', 'customer')->count(),
            'total_menu_items' => MenuItem::where('is_available', true)->count(),
            'total_meal_plans' => MealPlan::where('is_active', true)->count(),
            'active_drivers' => Driver::where('availability', 'available')->count(),
            'pending_deliveries' => Delivery::where('status', 'assigned')->count(),
            'low_stock_count' => Inventory::whereRaw('current_stock <= minimum_stock')->count(),
            'this_week_orders' => Order::where('created_at', '>=', $thisWeek)->count(),
            'this_month_revenue' => Payment::where('status', 'completed')
                ->where('payment_date', '>=', $thisMonth)
                ->sum('amount'),
        ];
    }

    private function getTopMenuItems($startDate)
    {
        return DB::table('order_items')
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', $startDate)
            ->select(
                'menu_items.name',
                'menu_items.category',
                'menu_items.price',
                DB::raw('SUM(order_items.quantity) as total_ordered'),
                DB::raw('SUM(order_items.total_price) as total_revenue')
            )
            ->groupBy('menu_items.id', 'menu_items.name', 'menu_items.category', 'menu_items.price')
            ->orderByDesc('total_ordered')
            ->take(5)
            ->get();
    }

    private function getCustomerSatisfactionTrend($startDate)
    {
        return Review::where('created_at', '>=', $startDate)
            ->where('is_published', true)
            ->selectRaw('DATE(created_at) as date, AVG(rating) as avg_rating, COUNT(*) as review_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('M d'),
                    'rating' => round($item->avg_rating, 1),
                    'count' => $item->review_count,
                ];
            });
    }

    public function refreshStats()
    {
        // Clear cache and return fresh data
        Cache::forget('admin_dashboard_' . auth()->id());

        return response()->json([
            'message' => 'Dashboard data refreshed',
            'timestamp' => now()->toISOString(),
        ]);
    }

    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'revenue');
        $period = $request->get('period', '30');

        $startDate = Carbon::now()->subDays($period);

        switch ($type) {
            case 'revenue':
                return response()->json($this->getRevenueChartData($startDate));
            case 'subscriptions':
                return response()->json($this->getSubscriptionGrowthData($startDate));
            case 'satisfaction':
                return response()->json($this->getCustomerSatisfactionTrend($startDate));
            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }
}
