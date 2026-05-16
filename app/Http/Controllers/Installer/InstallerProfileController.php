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

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255|unique:vip_users,email,' . $user->id,
            'phone'           => 'nullable|string|max:50',
            'address'         => 'nullable|string|max:255',
            'city'            => 'nullable|string|max:100',
            'state'           => 'nullable|string|max:50',
            'zip'             => 'nullable|string|max:20',
            'password'        => 'nullable|string|min:8|confirmed',
            'company_name'    => 'nullable|string|max:255',
            'company_phone'   => 'nullable|string|max:50',
            'company_email'   => 'nullable|email|max:255',
            'company_website' => 'nullable|string|max:255',
        ]);

        // Remove password if not provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('installer.profile')
            ->with('success', 'Profile updated successfully.');
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'company_logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $user = Auth::user();

        // Delete old logo if exists
        if ($user->company_logo) {
            $oldPath = public_path('uploads/installer-logos/' . $user->company_logo);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $file = $request->file('company_logo');
        $filename = 'logo_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Ensure directory exists
        $uploadPath = public_path('uploads/installer-logos');
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $file->move($uploadPath, $filename);

        $user->update(['company_logo' => $filename]);

        return redirect()->route('installer.profile')
            ->with('success', 'Company logo updated successfully.');
    }
}
