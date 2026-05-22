<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::where('is_admin', false)->count(),
            'orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'revenue' => Order::revenueEligible()
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->sum(DB::raw('order_items.price * order_items.quantity')),
            'products' => Product::count(),
            'unread_messages' => ContactMessage::where('is_processed', false)->count(),
        ];

        $recentOrders = Order::with(['user', 'items'])
            ->latest()
            ->limit(8)
            ->get();

        $ordersByStatus = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', compact('stats', 'recentOrders', 'ordersByStatus'));
    }
}
