<?php

namespace App\Helper;

class BrandingHelper
{
    /**
     * Get the company branding for a quote.
     * VIP Windows simplified version -- always returns VIP Windows branding.
     */
    public static function getQuoteBranding($quote = null)
    {
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
