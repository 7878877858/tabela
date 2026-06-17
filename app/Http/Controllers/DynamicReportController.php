<?php

namespace App\Http\Controllers;

use App\Models\Buffalo;
use App\Services\ReportGeneratorService;
use App\Services\ReportPdfService;
use Illuminate\Http\Request;

class DynamicReportController extends Controller
{
    public function __construct(
        protected ReportGeneratorService $generator,
        protected ReportPdfService $pdf
    ) {
    }

    public function index()
    {
        $animalTypes = Buffalo::animalTypeOptions();

        return view('reports.generator', compact('animalTypes'));
    }

    public function generate(Request $request)
    {
        $filters = $this->generator->filtersFromRequest($request->all());
        $data = $this->generator->generate($filters);

        return view('reports.dynamic', compact('data', 'filters'));
    }

    public function pdf(Request $request)
    {
        $filters = $this->generator->filtersFromRequest($request->all());
        $type = $filters['report_type'];

        return match ($type) {
            'feed'        => $this->pdf->generateFeedPdf($filters),
            'expense'     => $this->pdf->generateExpensePdf($filters),
            'income'      => $this->pdf->generateIncomePdf($filters),
            'health'      => $this->pdf->generateHealthPdf($filters),
            'vaccination' => $this->pdf->generateVaccinationPdf($filters),
            'combined'    => $this->pdf->generateMonthlyPdf($filters),
            'monthly'     => $this->pdf->generateMonthlyPdf($filters),
            'yearly'      => $this->pdf->generateYearlyPdf($filters),
            default       => $this->pdf->generateMilkPdf($filters),
        };
    }
}
