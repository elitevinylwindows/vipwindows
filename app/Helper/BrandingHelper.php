<?php

namespace App\Helper;

use Illuminate\Support\Facades\Auth;

class BrandingHelper
{
    /**
     * Get the company branding for a quote.
     * If the authenticated user is an installer with company info, use their branding.
     * Otherwise, fall back to VIP Windows branding.
     */
    public static function getQuoteBranding($quote = null)
    {
        $user = Auth::guard('vip')->user();

        // If installer with company info, use their branding
        if ($user && $user->role === 'installer' && !empty($user->company_name)) {
            return (object) [
                'company_name' => $user->company_name,
                'address'      => $user->company_address ?? '',
                'city'         => $user->company_city ?? '',
                'state'        => $user->company_state ?? '',
                'zip'          => $user->company_zip ?? '',
                'phone'        => $user->company_phone ?? '',
                'fax'          => '',
                'email'        => $user->email ?? '',
                'website'      => '',
                'logo_path'    => $user->company_logo ? 'uploads/installer-logos/' . $user->company_logo : null,
                'is_dealer'    => false,
            ];
        }

        // Default VIP Windows branding
        return (object) [
            'company_name' => 'VIP Windows',
            'address'      => '',
            'city'         => '',
            'state'        => '',
            'zip'          => '',
            'phone'        => '',
            'fax'          => '',
            'email'        => '',
            'website'      => '',
            'logo_path'    => null,
            'is_dealer'    => false,
        ];
    }
}
