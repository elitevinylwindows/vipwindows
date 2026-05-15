<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\VipUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ConsultationController extends Controller
{
    public function index()
    {
        $consultations = Consultation::orderByDesc('scheduled_at')->paginate(20);
        return view('consultations.index', compact('consultations'));
    }

    public function create()
    {
        $customers = VipUser::where('role', 'customer')->orderBy('name')->get();
        return view('consultations.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name'  => 'required|string|max:150',
            'customer_email' => 'required|email',
            'customer_phone' => 'nullable|string|max:30',
            'scheduled_at'   => 'required|date|after:now',
            'duration'       => 'required|integer|in:15,30,45,60',
            'platform'       => 'required|string|in:zoom,teams,phone',
            'meeting_link'   => 'nullable|url|max:500',
            'notes'          => 'nullable|string|max:2000',
            'address'        => 'nullable|string|max:500',
        ]);

        $consultation = Consultation::create([
            'customer_name'  => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'scheduled_at'   => $validated['scheduled_at'],
            'duration'       => $validated['duration'],
            'platform'       => $validated['platform'],
            'meeting_link'   => $validated['meeting_link'],
            'notes'          => $validated['notes'],
            'address'        => $validated['address'],
            'status'         => 'scheduled',
            'created_by'     => Auth::id(),
        ]);

        // Send confirmation email to customer
        if (!empty($validated['customer_email'])) {
            try {
                $data = $consultation->toArray();
                $data['formatted_date'] = $consultation->scheduled_at->format('l, F j, Y \a\t g:i A');

                Mail::html(
                    view('emails.consultation-confirmation', $data)->render(),
                    function ($message) use ($validated) {
                        $message->to($validated['customer_email'])
                                ->subject('Your Virtual Consultation with VIP Windows');
                    }
                );
            } catch (\Exception $e) {
                // Log but don't fail the creation
            }
        }

        return redirect()->route('admin.consultations.index')
            ->with('success', 'Consultation scheduled for ' . $consultation->scheduled_at->format('M d, Y g:i A'));
    }

    public function update(Request $request, $id)
    {
        $consultation = Consultation::findOrFail($id);

        $validated = $request->validate([
            'status'       => 'required|string|in:scheduled,completed,cancelled,no_show',
            'meeting_link' => 'nullable|url|max:500',
            'notes'        => 'nullable|string|max:2000',
        ]);

        $consultation->update($validated);

        return redirect()->route('admin.consultations.index')
            ->with('success', 'Consultation updated.');
    }

    public function destroy($id)
    {
        Consultation::findOrFail($id)->delete();
        return redirect()->route('admin.consultations.index')
            ->with('success', 'Consultation removed.');
    }

    /**
     * Public — customer requests a consultation from the website.
     */
    public function publicRequest(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:150',
            'email'   => 'required|email',
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'notes'   => 'nullable|string|max:2000',
        ]);

        Consultation::create([
            'customer_name'  => $validated['name'],
            'customer_email' => $validated['email'],
            'customer_phone' => $validated['phone'],
            'address'        => $validated['address'],
            'notes'          => $validated['notes'] ?? 'Requested via website',
            'scheduled_at'   => now()->addDays(3)->setHour(10)->setMinute(0), // placeholder
            'duration'       => 30,
            'platform'       => 'zoom',
            'status'         => 'scheduled',
        ]);

        return redirect()->back()->with('success', 'Thank you! We\'ll contact you shortly to confirm your virtual consultation.');
    }
}
