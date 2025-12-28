<?php

namespace App\Services;

use TCPDF;
use App\Models\Reservation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ReceiptGenerator
{
    protected $reservation;
    protected $customerData;

    public function __construct(Reservation $reservation, array $customerData = [])
    {
        $this->reservation = $reservation;
        $this->customerData = $customerData;
    }

    public function generate()
    {
        try {
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // Configuration
            $pdf->SetCreator(config('app.name'));
            $pdf->SetAuthor(config('app.name'));
            $pdf->SetTitle('Reçu de Réservation');
            $pdf->SetSubject('Reçu');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->AddPage();

            // Logo (optionnel)
            $this->addHeader($pdf);

            // Titre
            $pdf->SetFont('helvetica', 'B', 20);
            $pdf->SetTextColor(34, 197, 94);
            $pdf->Cell(0, 10, 'REÇU DE RÉSERVATION', 0, 1, 'C');
            $pdf->Ln(5);

            // Ligne de séparation
            $pdf->SetDrawColor(204, 204, 204);
            $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
            $pdf->Ln(10);

            // Informations de réservation
            $this->addReservationDetails($pdf);
            $pdf->Ln(10);

            // Informations du client
            $this->addCustomerDetails($pdf);
            $pdf->Ln(15);

            // Statut
            $this->addStatus($pdf);
            $pdf->Ln(20);

            // Pied de page
            $this->addFooter($pdf);

            // Générer le contenu
            $filename = "receipt-{$this->reservation->code}.pdf";
            $content = $pdf->Output($filename, 'S'); // 'S' pour retourner en string

            // Sauvegarder (optionnel)
            if (config('receipts.save_to_disk', true)) {
                $this->saveToDisk($filename, $content);
            }

            return [
                'filename' => $filename,
                'content' => $content,
                'mime_type' => 'application/pdf'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur génération reçu: ' . $e->getMessage());
            throw new \Exception('Impossible de générer le reçu.');
        }
    }

    private function addHeader($pdf)
    {
        // Ajouter un logo si disponible
        $logoPath = public_path('images/logo.png');
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 15, 10, 30, 0, 'PNG');
            $pdf->SetY(40);
        } else {
            $pdf->SetY(20);
        }
    }

    private function addReservationDetails($pdf)
    {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(100, 8, "Réservation #{$this->reservation->code}", 0, 0, 'L');

        $date = $this->reservation->created_at->format('d/m/Y H:i');
        $pdf->Cell(80, 8, "Date: {$date}", 0, 1, 'R');
        $pdf->Ln(8);

        $pdf->SetFont('helvetica', '', 12);

        // Événement
        $eventName = $this->reservation->event->title ?? 'Événement';
        $pdf->Cell(0, 8, "Événement: {$eventName}", 0, 1, 'L');

        // Type de billet
        if ($this->reservation->ticket_type) {
            $pdf->Cell(0, 8, "Type de billet: {$this->reservation->ticket_type}", 0, 1, 'L');
        }

        // Quantité
        $pdf->Cell(0, 8, "Quantité: {$this->reservation->quantity}", 0, 1, 'L');

        // Prix
        $total = $this->reservation->price * $this->reservation->quantity;
        $pdf->Cell(0, 8, "Prix unitaire: {$this->reservation->price} FCFA", 0, 1, 'L');
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, "Total: {$total} FCFA", 0, 1, 'L');

        // Date de l'événement
        $eventDate = $this->reservation->event_date->format('d/m/Y');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 8, "Date de l'événement: {$eventDate}", 0, 1, 'L');
    }

    private function addCustomerDetails($pdf)
    {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, "Informations du client", 0, 1, 'L');
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 8, "Nom: {$this->customerData['name']}", 0, 1, 'L');
        $pdf->Cell(0, 8, "Email: {$this->customerData['email']}", 0, 1, 'L');
        $pdf->Cell(0, 8, "Téléphone: {$this->customerData['phone']}", 0, 1, 'L');
    }

    private function addStatus($pdf)
    {
        $status = strtoupper($this->reservation->status);
        $colorMap = [
            'CONFIRMED' => [34, 197, 94],   // Vert
            'PENDING' => [251, 191, 36],    // Jaune
            'CANCELLED' => [239, 68, 68],   // Rouge
        ];

        $color = $colorMap[$status] ?? [0, 0, 0];

        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(...$color);
        $pdf->Cell(0, 10, "STATUT: {$status}", 0, 1, 'C');
    }

    private function addFooter($pdf)
    {
        $pdf->SetY(-30);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(102, 102, 102);

        $pdf->Cell(0, 6, 'Merci pour votre réservation!', 0, 1, 'C');
        $pdf->Cell(0, 6, config('app.name') . ' | ' . config('app.url'), 0, 1, 'C');
        $pdf->Cell(0, 6, 'Contact: ' . config('app.contact_email', 'contact@example.com'), 0, 1, 'C');
    }

    private function saveToDisk($filename, $content)
    {
        Storage::disk('receipts')->put($filename, $content);
    }

    public function download()
    {
        $result = $this->generate();

        return response($result['content'])
            ->header('Content-Type', $result['mime_type'])
            ->header('Content-Disposition', "attachment; filename=\"{$result['filename']}\"");
    }

    public function stream()
    {
        $result = $this->generate();

        return response($result['content'])
            ->header('Content-Type', $result['mime_type'])
            ->header('Content-Disposition', "inline; filename=\"{$result['filename']}\"");
    }
}
