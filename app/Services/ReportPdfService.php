<?php

namespace App\Services;

use Illuminate\Http\Response;

class ReportPdfService
{
    public function __construct(
        protected ReportGeneratorService $generator
    ) {
    }

    public function render(string $reportType, array $filters): Response
    {
        $filters['report_type'] = $reportType;
        $data = $this->generator->generate($filters);

        $view = 'reports.pdf.layout';

        $filename = $reportType . '-report-' . ($filters['from_date'] ?? 'export') . '.html';

        return response()
            ->view($view, $data)
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    public function generateMilkPdf(array $filters): Response
    {
        return $this->render('milk', $filters);
    }

    public function generateFeedPdf(array $filters): Response
    {
        return $this->render('feed', $filters);
    }

    public function generateExpensePdf(array $filters): Response
    {
        return $this->render('expense', $filters);
    }

    public function generateIncomePdf(array $filters): Response
    {
        return $this->render('income', $filters);
    }

    public function generateHealthPdf(array $filters): Response
    {
        return $this->render('health', $filters);
    }

    public function generateVaccinationPdf(array $filters): Response
    {
        return $this->render('vaccination', $filters);
    }

    public function generateMonthlyPdf(array $filters): Response
    {
        return $this->render('monthly', $filters);
    }

    public function generateYearlyPdf(array $filters): Response
    {
        return $this->render('yearly', $filters);
    }
}
