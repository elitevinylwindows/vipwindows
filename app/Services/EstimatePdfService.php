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
     * Generate estimate PDF and merge with the uploaded tech measure PDF.
     * The uploaded PDF pages come FIRST, the generated estimate is appended LAST.
     * Returns the path to the final PDF in public storage.
     */
    public function generateAndMerge(Job $job): ?string
    {
        // Find the linked tech measure
        $techMeasure = TechMeasure::with(['items', 'calendarEvent'])->where('job_id', $job->id)->first();
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

            // Determine type based on opening_type or description
            $openingType = $item->opening_type ?? '';
            $desc = $item->description ?? '';
            $type = 'Windows';
            if (stripos($openingType, 'door') !== false || stripos($openingType, 'slider') !== false
                || stripos($desc, 'door') !== false || stripos($desc, 'SL') !== false) {
                $type = 'Sliding Door';
            }

            // Build description: size + unit(config) + notes
            $parts = [];
            if ($item->width && $item->height) {
                $parts[] = $item->width . 'x' . $item->height;
            }
            if ($item->series_type) {
                $parts[] = $item->series_type;
            }
            if ($desc) {
                $parts[] = $desc;
            }
            if ($item->notes) {
                $parts[] = $item->notes;
            }

            $measureItems[] = [
                'type'       => $type,
                'description'=> trim(implode(' ', $parts)) ?: 'Window/Door',
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
                'description' => $li['service_name'] ?? 'Installation',
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

        // Build filename: estimate# - title (e.g. "00009 - Tola.pdf")
        $title = $job->title ?: $job->customer_name ?: 'Estimate';
        $safeTitle = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $title);
        $finalFilename = $estimateNumber . ' - ' . trim($safeTitle);

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

        // Save the estimate PDF
        $estimateRelPath = 'tech-measures/' . $techMeasure->id . '/estimate-' . time() . '.pdf';
        Storage::disk('public')->put($estimateRelPath, $pdf->output());

        // Determine the final output path
        $finalRelPath = 'tech-measures/' . $techMeasure->id . '/' . $finalFilename . '.pdf';

        // Merge: uploaded PDF pages first, then estimate page(s) appended at the end
        if ($originalPdfPath && Storage::disk('public')->exists($originalPdfPath)) {
            $merged = $this->mergePdfs(
                Storage::disk('public')->path($originalPdfPath),
                Storage::disk('public')->path($estimateRelPath),
                Storage::disk('public')->path($finalRelPath),
            );

            if ($merged) {
                // Clean up temp estimate
                Storage::disk('public')->delete($estimateRelPath);

                // Update the job_data with the final PDF path
                $jobData['pdf_path'] = $finalRelPath;
                $techMeasure->update(['job_data' => json_encode($jobData)]);

                return $finalRelPath;
            }

            // Merge failed — fall through to use estimate only
            \Log::warning("PDF merge failed, using estimate-only PDF.");
        }

        // No original PDF or merge failed — rename the estimate to final filename
        $estimateFullPath = Storage::disk('public')->path($estimateRelPath);
        $finalFullPath = Storage::disk('public')->path($finalRelPath);

        // Ensure directory exists
        $dir = dirname($finalFullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        rename($estimateFullPath, $finalFullPath);

        $jobData['pdf_path'] = $finalRelPath;
        $techMeasure->update(['job_data' => json_encode($jobData)]);

        return $finalRelPath;
    }

    /**
     * Merge two PDFs: uploaded doc first, then estimate appended.
     * Tries multiple approaches: pdfunite, gs, FPDI, then raw fallback.
     */
    private function mergePdfs(string $uploadedPdfPath, string $estimatePdfPath, string $outputPath): bool
    {
        // Ensure output directory exists
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Escape paths for shell
        $pdf1 = escapeshellarg($uploadedPdfPath);
        $pdf2 = escapeshellarg($estimatePdfPath);
        $out  = escapeshellarg($outputPath);

        // Method 1: pdfunite (poppler-utils — common on cPanel)
        $check = trim(shell_exec('which pdfunite 2>/dev/null') ?? '');
        if ($check) {
            exec("pdfunite {$pdf1} {$pdf2} {$out} 2>&1", $output, $code);
            if ($code === 0 && file_exists($outputPath) && filesize($outputPath) > 0) {
                return true;
            }
            \Log::warning("pdfunite failed (code {$code}): " . implode("\n", $output));
        }

        // Method 2: Ghostscript (very common on shared hosting)
        $gsPath = trim(shell_exec('which gs 2>/dev/null') ?? '');
        if ($gsPath) {
            $gsCmd = "{$gsPath} -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile={$out} {$pdf1} {$pdf2} 2>&1";
            exec($gsCmd, $output, $code);
            if ($code === 0 && file_exists($outputPath) && filesize($outputPath) > 0) {
                return true;
            }
            \Log::warning("gs merge failed (code {$code}): " . implode("\n", $output));
        }

        // Method 3: FPDI (if the package happens to be installed)
        try {
            if (class_exists('\setasign\Fpdi\Fpdi')) {
                $fpdi = new \setasign\Fpdi\Fpdi();

                // Add uploaded PDF pages first
                $pageCount1 = $fpdi->setSourceFile($uploadedPdfPath);
                for ($i = 1; $i <= $pageCount1; $i++) {
                    $template = $fpdi->importPage($i);
                    $size = $fpdi->getTemplateSize($template);
                    $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $fpdi->useTemplate($template);
                }

                // Add estimate pages last
                $pageCount2 = $fpdi->setSourceFile($estimatePdfPath);
                for ($i = 1; $i <= $pageCount2; $i++) {
                    $template = $fpdi->importPage($i);
                    $size = $fpdi->getTemplateSize($template);
                    $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $fpdi->useTemplate($template);
                }

                $fpdi->Output('F', $outputPath);
                return true;
            }
        } catch (\Throwable $e) {
            \Log::warning("FPDI merge failed: " . $e->getMessage());
        }

        // Method 4: Fallback — just copy the estimate (no merge possible)
        \Log::warning("No PDF merge tool available. Using estimate-only PDF.");
        copy($estimatePdfPath, $outputPath);
        return true;
    }
}
