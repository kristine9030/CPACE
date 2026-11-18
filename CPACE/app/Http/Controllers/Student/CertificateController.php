<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\CertificateService;
use Dompdf\Dompdf;
use Dompdf\Options as DompdfOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Certificate of Completion, issued once a student has submitted the
 * current mock exam for all 6 subjects. See CertificateService for the
 * eligibility rule.
 *
 * Rendered on top of the Chair-approved template image (public/images/
 * certificate-of-completion.png) with Montserrat embedded as base64 so
 * dompdf never has to fetch anything remotely, same convention as
 * StudentManagementController::exportPdf.
 */
class CertificateController extends Controller
{
    /** Name box is ~48.6% of the page wide; past this many letters the base size starts clipping. */
    private const NAME_FONT_SIZE = 27;
    private const NAME_SHRINK_AT = 22;
    private const NAME_MIN_FONT_SIZE = 16;

    public function __construct(private CertificateService $certificates) {}

    public function download()
    {
        $student = Auth::user();
        abort_unless($student->isStudent(), 403);

        $status = $this->certificates->status($student->id);
        abort_unless($status['eligible'], 403, 'Finish all 6 mock exams to unlock your certificate.');

        $fullNameUpper = Str::upper($student->name);

        $html = view('student.certificate-pdf', [
            'templateImage' => $this->fileAsBase64(public_path('images/certificate-of-completion.png')),
            'boldFont' => $this->fileAsBase64(public_path('fonts/montserrat/Montserrat-Bold.ttf')),
            'regularFont' => $this->fileAsBase64(public_path('fonts/montserrat/Montserrat-Regular.ttf')),
            'fullNameUpper' => $fullNameUpper,
            'nameFontSize' => $this->nameFontSize($fullNameUpper),
            'earnedAtLabel' => $status['earned_at']?->format('F j, Y') ?? now()->format('F j, Y'),
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

    private function fileAsBase64(string $path): ?string
    {
        return is_file($path) ? base64_encode(file_get_contents($path)) : null;
    }

    /** Scales the name down as it gets longer so it never overruns the template's name box. */
    private function nameFontSize(string $fullNameUpper): int
    {
        $len = mb_strlen($fullNameUpper);
        if ($len <= self::NAME_SHRINK_AT) {
            return self::NAME_FONT_SIZE;
        }

        $over = $len - self::NAME_SHRINK_AT;

        return max(self::NAME_MIN_FONT_SIZE, self::NAME_FONT_SIZE - $over);
    }
}
