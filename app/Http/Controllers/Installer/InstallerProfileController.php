<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InstallerProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('installer.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $section = $request->input('_section', 'personal');

        if ($section === 'pricing') {
            $validated = $request->validate([
                'price_markup_pct'  => 'required|numeric|min:0|max:500',
                'price_markup_flat' => 'required|numeric|min:0',
            ]);
            $user->update($validated);
            return redirect()->route('installer.profile')->with('success', 'Pricing markup updated.');
        }

        if ($section === 'security') {
            $validated = $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $user->update(['password' => Hash::make($validated['password'])]);
            return redirect()->route('installer.profile')->with('success', 'Password updated.');
        }

        if ($section === 'company') {
            $validated = $request->validate([
                'company_name'    => 'nullable|string|max:255',
                'company_phone'   => 'nullable|string|max:50',
                'company_fax'     => 'nullable|string|max:50',
                'company_email'   => 'nullable|email|max:255',
                'company_website' => 'nullable|string|max:255',
                'company_address' => 'nullable|string|max:500',
                'company_city'    => 'nullable|string|max:100',
                'company_state'   => 'nullable|string|max:50',
                'company_zip'     => 'nullable|string|max:20',
            ]);
            $user->update($validated);
            return redirect()->route('installer.profile')->with('success', 'Company info updated.');
        }

        // Default: personal section
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255|unique:vip_users,email,' . $user->id,
            'phone'           => 'nullable|string|max:50',
            'address'         => 'nullable|string|max:255',
            'city'            => 'nullable|string|max:100',
            'state'           => 'nullable|string|max:50',
            'zip'             => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return redirect()->route('installer.profile')
            ->with('success', 'Profile updated successfully.');
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo_type' => 'required|in:dark,light',
            'company_logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $user = Auth::user();
        $logoType = $request->input('logo_type'); // 'dark' or 'light'
        $column = 'company_logo_' . $logoType;

        // Delete old logo if exists
        if ($user->$column) {
            $oldPath = public_path('uploads/installer-logos/' . $user->$column);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $file = $request->file('company_logo');
        $filename = 'logo_' . $logoType . '_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Ensure directory exists
        $uploadPath = public_path('uploads/installer-logos');
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $file->move($uploadPath, $filename);

        $user->update([$column => $filename]);

        $label = $logoType === 'light' ? 'Light' : 'Dark';
        return redirect()->route('installer.profile')
            ->with('success', "$label logo updated successfully.");
    }
}
