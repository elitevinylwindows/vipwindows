<?php

namespace App\Http\Controllers;

use App\Models\InstallationOrder;
use App\Models\VipUser;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $query = InstallationOrder::orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20);
        $technicians = VipUser::where('role', 'technician')->orWhere('role', 'admin')->orderBy('name')->get();

        return view('orders.index', compact('orders', 'status', 'technicians'));
    }

    public function show($id)
    {
        $order = InstallationOrder::with('quoteItems')->findOrFail($id);
        $technicians = VipUser::where('role', 'technician')->orWhere('role', 'admin')->orderBy('name')->get();

        return view('orders.show', compact('order', 'technicians'));
    }

    public function updateStatus(Request $request, $id)
    {
        $order = InstallationOrder::findOrFail($id);
        $validated = $request->validate([
            'status'        => 'required|in:pending,scheduled,in_progress,completed,cancelled',
            'technician_id' => 'nullable|integer',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $order->update([
            'status'        => $validated['status'],
            'technician_id' => $validated['technician_id'] ?? $order->technician_id,
            'notes'         => $validated['notes'] ?? $order->notes,
            'completed_at'  => $validated['status'] === 'completed' ? now() : $order->completed_at,
        ]);

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Order status updated.');
    }

    public function assignTechnician(Request $request, $id)
    {
        $order = InstallationOrder::findOrFail($id);
        $validated = $request->validate(['technician_id' => 'required|integer']);

        $order->update(['technician_id' => $validated['technician_id']]);

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Technician assigned.');
    }
}
