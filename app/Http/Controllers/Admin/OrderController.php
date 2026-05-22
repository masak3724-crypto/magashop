<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($request->filled('paid')) {
            $query->where('paid', $request->boolean('paid'));
        }

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product.category']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:'.implode(',', array_keys(Order::STATUSES)),
            'paid' => 'required|boolean',
        ]);

        if ($data['status'] === 'cancelled') {
            $data['paid'] = false;
        }

        $order->update($data);

        return back()->with('success', 'Заказ обновлён.');
    }
}
