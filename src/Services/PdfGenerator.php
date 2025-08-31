<?php
namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

class PdfGenerator
{
    private Dompdf $dompdf;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        // Crée Dompdf directement, pas besoin d'injecter Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Garamond'); // correction de la faute
        $options->setIsRemoteEnabled(true); // si tu as des images externes

        $this->dompdf = new Dompdf($options);
        $this->logger = $logger;
    }

    public function generatePdf(string $html, string $filename = 'document.pdf', bool $download = true): Response
    {
        try {
            $this->dompdf->loadHtml($html);
            $this->dompdf->setPaper('A4', 'portrait');
            $this->dompdf->render();
            $output = $this->dompdf->output();

            return new Response(
                $output,
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => ($download ? 'attachment' : 'inline') . '; filename="' . $filename . '"'
                ]
            );
        } catch (\Exception $e) {
            $this->logger->error('Error generating PDF: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return new Response('Impossible to generate PDF', 500);
        }
    }
}
