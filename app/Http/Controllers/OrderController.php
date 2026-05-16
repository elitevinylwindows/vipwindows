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

    public function show(Request $request, $id)
    {
        $order = InstallationOrder::with('quoteItems')->findOrFail($id);
        $technicians = VipUser::where('role', 'technician')->orWhere('role', 'admin')->orderBy('name')->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'order' => [
                    'id'              => $order->id,
                    'customer_name'   => $order->customer_name,
                    'customer_email'  => $order->customer_email,
                    'customer_phone'  => $order->customer_phone,
                    'install_address' => $order->install_address,
                    'install_city'    => $order->install_city,
                    'install_state'   => $order->install_state,
                    'install_zip'     => $order->install_zip,
                    'status'          => $order->status,
                    'scheduled_date'  => $order->scheduled_date?->format('M d, Y'),
                    'scheduled_slot'  => $order->scheduled_slot,
                    'notes'           => $order->notes,
                    'technician_id'   => $order->technician_id,
                    'technician_name' => $technicians->firstWhere('id', $order->technician_id)?->name,
                    'created_at'      => $order->created_at?->format('M d, Y'),
                    'completed_at'    => $order->completed_at?->format('M d, Y'),
                ],
                'items' => $order->quoteItems->map(fn($i) => [
                    'id'          => $i->id,
                    'description' => $i->description,
                    'series_type' => $i->series_type,
                    'width'       => $i->width,
                    'height'      => $i->height,
                    'qty'         => $i->qty,
                ]),
                'technicians' => $technicians->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
            ]);
        }

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

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Order status updated.');
    }

    public function assignTechnician(Request $request, $id)
    {
        $order = InstallationOrder::findOrFail($id);
        $validated = $request->validate(['technician_id' => 'required|integer']);

        $order->update(['technician_id' => $validated['technician_id']]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Technician assigned.');
    }
}
