<?php

namespace App\Http\Controllers;

use App\Models\SentEmail;
use App\Models\VipUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    /**
     * Compose redirects to sent page (compose is now a modal there).
     */
    public function compose(Request $request)
    {
        return redirect()->route('admin.email.sent', ['to' => $request->input('to')]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'to'      => 'required|email',
            'subject' => 'required|string|max:200',
            'body'    => 'required|string|max:10000',
            'cc'      => 'nullable|string|max:500',
        ]);

        try {
            $ccAddresses = [];
            if (!empty($validated['cc'])) {
                $ccAddresses = array_map('trim', explode(',', $validated['cc']));
            }

            Mail::html($validated['body'], function ($message) use ($validated, $ccAddresses) {
                $message->to($validated['to'])
                        ->subject($validated['subject']);

                if (!empty($ccAddresses)) {
                    $message->cc($ccAddresses);
                }
            });

            // Log the sent email
            SentEmail::create([
                'to'      => $validated['to'],
                'cc'      => $validated['cc'],
                'subject' => $validated['subject'],
                'body'    => $validated['body'],
                'sent_by' => Auth::id(),
            ]);

            return redirect()->route('admin.email.sent')->with('success', 'Email sent successfully to ' . $validated['to']);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    public function sent(Request $request)
    {
        $emails = SentEmail::orderByDesc('created_at')->paginate(20);
        $customers = VipUser::where('role', 'customer')->orderBy('name')->get();
        return view('email.sent', compact('emails', 'customers'));
    }
}
