<?php

namespace App\Services;

use App\Models\Job;
use App\Models\Setting;
use App\Models\TechMeasure;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class EstimatePdfService
{
    /**
     * Generate estimate PDF and merge with the tech measure PDF.
     * Returns the path to the merged PDF in public storage.
     */
    public function generateAndMerge(Job $job): ?string
    {
        // Find the linked tech measure
        $techMeasure = TechMeasure::with('items')->where('job_id', $job->id)->first();
        if (!$techMeasure || !$techMeasure->job_data) {
            return null;
        }

        $jobData = json_decode($techMeasure->job_data, true);
        $lineItems = $jobData['line_items'] ?? [];
        $measurementPrices = $jobData['measurement_prices'] ?? [];
        $originalPdfPath = $jobData['pdf_path'] ?? null;

        // Load settings
        $settings = Setting::pluck('value', 'key')->toArray();
        $defaults = [
            'company_name'    => 'VIP Windows Inc.',
            'company_phone'   => '(562) 368-0313',
            'company_address' => '4231 Liberty Blvd.',
            'company_city'    => 'South Gate',
            'company_state'   => 'CA',
            'company_zip'     => '90280',
            'license_number'  => '',
            'sales_tax_rate'  => '10.75',
            'cc_fee_visa'     => '2',
            'cc_fee_amex'     => '2.5',
            'estimate_terms'  => 'Due on receipt',
            'estimate_footer' => 'If the above prices, specifications and conditions are satisfactory and hereby accepted, the company requires signatures when orders are placed. By signing, customer has agreed Not to cancel the order or put a stop payment on orders that have been paid by Visa, M/C, check and/or cash. Estimate valid only 30 days.',
        ];
        $settings = array_merge($defaults, $settings);

        // Build measurement items for the estimate
        $measureItems = [];
        $taxableSubtotal = 0;

        foreach ($techMeasure->items as $item) {
            $mPrice = collect($measurementPrices)->firstWhere('item_id', $item->id);
            $price = $mPrice ? ($mPrice['price'] ?? 0) : 0;
            $qty = $item->qty ?: 1;
            $unitPrice = $qty > 0 ? $price / $qty : $price;

            // Determine type based on description/dimensions
            $desc = $item->description ?? '';
            $type = 'Windows';
            if (stripos($desc, 'door') !== false || stripos($desc, 'slider') !== false || stripos($desc, 'SL') !== false) {
                $type = 'Sliding Door';
            }

            // Build description: size + config
            $sizeDesc = '';
            if ($item->width && $item->height) {
                $sizeDesc = $item->width . 'x' . $item->height;
            }
            if ($desc) {
                $sizeDesc .= ($sizeDesc ? ' ' : '') . $desc;
            }
            if ($item->notes) {
                $sizeDesc .= ' ' . $item->notes;
            }

            $measureItems[] = [
                'type'       => $type,
                'description'=> trim($sizeDesc) ?: 'Window/Door',
                'qty'        => $qty,
                'unit_price' => $unitPrice,
                'total'      => $price,
                'taxable'    => true,
            ];

            $taxableSubtotal += $price;
        }

        // Build service line items
        $serviceItems = [];
        $nonTaxableSubtotal = 0;
        foreach ($lineItems as $li) {
            $serviceItems[] = [
                'type'        => $li['service_name'] ?? 'Service',
                'description' => ($li['service_name'] ?? 'Installation') . ' charge',
                'qty'         => $li['qty'] ?? 1,
                'unit_price'  => $li['unit_price'] ?? 0,
                'total'       => $li['total'] ?? 0,
            ];
            $nonTaxableSubtotal += $li['total'] ?? 0;
        }

        // Calculate totals
        $subtotal = $taxableSubtotal + $nonTaxableSubtotal;
        $taxRate = (float) ($settings['sales_tax_rate'] ?? 10.75);
        $taxAmount = round($taxableSubtotal * ($taxRate / 100), 2);
        $total = $subtotal + $taxAmount;

        // Generate estimate number from job number
        $estimateNumber = str_replace('JOB-', '', $job->job_number);

        // Installation description
        $installDescription = 'Installation of Elite Vinyl Windows Retrofit Dual Glazed White Exterior/ White Interior Vinyl Windows, Lowe Glass & Argon Gas Filled. Full Lifetime Warranty.';

        // Render the estimate blade to PDF
        $pdf = Pdf::loadView('pdf.estimate', [
            'job'                => $job,
            'settings'           => $settings,
            'measureItems'       => $measureItems,
            'serviceItems'       => $serviceItems,
            'installDescription' => $installDescription,
            'subtotal'           => $subtotal,
            'taxRate'            => $taxRate,
            'taxAmount'          => $taxAmount,
            'total'              => $total,
            'estimateDate'       => now()->format('n/j/Y'),
            'estimateNumber'     => $estimateNumber,
        ])->setPaper('letter');

        // Save the estimate PDF temporarily
        $estimatePath = 'tech-measures/' . $techMeasure->id . '/estimate-' . time() . '.pdf';
        Storage::disk('public')->put($estimatePath, $pdf->output());

        // Merge: original tech measure PDF + estimate page
        if ($originalPdfPath && Storage::disk('public')->exists($originalPdfPath)) {
            $mergedPath = $this->mergePdfs(
                Storage::disk('public')->path($originalPdfPath),
                Storage::disk('public')->path($estimatePath),
                $techMeasure->id
            );

            // Clean up temp estimate
            Storage::disk('public')->delete($estimatePath);

            // Update the job_data with the merged PDF path
            $jobData['pdf_path'] = $mergedPath;
            $techMeasure->update(['job_data' => json_encode($jobData)]);

            return $mergedPath;
        }

        // No original PDF — just use the estimate
        $jobData['pdf_path'] = $estimatePath;
        $techMeasure->update(['job_data' => json_encode($jobData)]);

        return $estimatePath;
    }

    /**
     * Merge two PDFs using FPDI (pure PHP, no system dependencies).
     */
    private function mergePdfs(string $pdf1Path, string $pdf2Path, int $measureId): string
    {
        $mergedRelPath = 'tech-measures/' . $measureId . '/estimate-merged-' . time() . '.pdf';
        $mergedFullPath = Storage::disk('public')->path($mergedRelPath);

        // Ensure directory exists
        $dir = dirname($mergedFullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        try {
            $fpdi = new \setasign\Fpdi\Fpdi();

            // Add pages from PDF 1 (tech measure)
            $pageCount1 = $fpdi->setSourceFile($pdf1Path);
            for ($i = 1; $i <= $pageCount1; $i++) {
                $template = $fpdi->importPage($i);
                $size = $fpdi->getTemplateSize($template);
                $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $fpdi->useTemplate($template);
            }

            // Add pages from PDF 2 (estimate)
            $pageCount2 = $fpdi->setSourceFile($pdf2Path);
            for ($i = 1; $i <= $pageCount2; $i++) {
                $template = $fpdi->importPage($i);
                $size = $fpdi->getTemplateSize($template);
                $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $fpdi->useTemplate($template);
            }

            $fpdi->Output('F', $mergedFullPath);
            return $mergedRelPath;
        } catch (\Exception $e) {
            \Log::warning("FPDI merge failed: " . $e->getMessage());

            // Fallback: just use the estimate PDF on its own
            copy($pdf2Path, $mergedFullPath);
            return $mergedRelPath;
        }
    }
}
