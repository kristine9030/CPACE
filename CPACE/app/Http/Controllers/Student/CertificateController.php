<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\CertificateService;
use Dompdf\Dompdf;
use Dompdf\Options as DompdfOptions;
use Illuminate\Support\Facades\Auth;

/**
 * Certificate of Completion, issued once a student has submitted the
 * current mock exam for all 6 subjects. See CertificateService for the
 * eligibility rule and a note on why this PDF is a placeholder.
 */
class CertificateController extends Controller
{
    public function __construct(private CertificateService $certificates) {}

    public function download()
    {
        $student = Auth::user();
        abort_unless($student->isStudent(), 403);

        $status = $this->certificates->status($student->id);
        abort_unless($status['eligible'], 403, 'Finish all 6 mock exams to unlock your certificate.');

        $html = view('student.certificate-pdf', [
            'student' => $student,
            'earnedAt' => $status['earned_at'],
            'subjects' => $status['subjects'],
        ])->render();

        $options = new DompdfOptions();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'CPACE-Certificate-of-Completion-' . str_replace(' ', '-', $student->name) . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
