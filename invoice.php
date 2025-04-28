<?php
require_once('vendor/autoload.php'); // Composer for dompdf

use Dompdf\Dompdf;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoiceData = $_POST;
    $currency = $invoiceData['currency'] ? $invoiceData['currency'] : 'PKR';
    $symbols = [
        'USD' => '$',
        'AED' => 'د.إ',
        'PKR' => '₨'
    ];
    $symbol = $symbols[$currency] ? $symbols[$currency] : '₨';
    $logoPath = 'logo.png'; // default logo

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadedPath = 'uploads/' . basename($_FILES['logo']['name']);
        if (!is_dir('uploads')) mkdir('uploads');
        move_uploaded_file($_FILES['logo']['tmp_name'], $uploadedPath);
        $logoPath = $uploadedPath;
    }

    $notes = trim($_POST['notes'] ? $_POST['notes'] : '');

    $html = '<html><head><style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; }
        .logo { max-height: 80px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 10px; text-align: left; }
        .notes { margin-top: 30px; padding: 10px; border: 1px dashed #999; background-color: #f9f9f9; }
        h4 { margin-bottom: 5px; }
    </style></head><body>';

    if (file_exists($logoPath)) {
        $base64Logo = base64_encode(file_get_contents($logoPath));
        $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
        $html .= '<img src="data:image/' . $logoType . ';base64,' . $base64Logo . '" class="logo">';
    }

    $html .= '<h2>Invoice</h2>';
    $html .= '<strong>From:</strong> ' . htmlspecialchars($invoiceData['from']) . '<br>';
    $html .= '<strong>To:</strong> ' . htmlspecialchars($invoiceData['to']) . '<br>';
    $html .= '<strong>Date:</strong> ' . date('d-m-Y') . '<br>';
    $html .= '<strong>Currency:</strong> ' . $currency . '<br>';

    $html .= '<table><thead><tr><th>Item</th><th>Qty</th><th>Rate</th><th>Total</th></tr></thead><tbody>';

    $grandTotal = 0;
    foreach ($invoiceData['items'] as $item) {
        $name = htmlspecialchars($item['name']);
        $qty = (int)$item['qty'];
        $rate = (float)$item['rate'];
        $total = $qty * $rate;
        $grandTotal += $total;
        $html .= "<tr><td>$name</td><td>$qty</td><td>$symbol $rate</td><td>$symbol $total</td></tr>";
    }

    $html .= "<tr><td colspan='3'><strong>Grand Total</strong></td><td><strong>$symbol $grandTotal</strong></td></tr>";
    $html .= '</tbody></table>';

    if (!empty($notes)) {
        $html .= '<div class="notes">';
        $html .= '<h4>Notes</h4>';
        $html .= '<p>' . nl2br(htmlspecialchars($notes)) . '</p>';
        $html .= '</div>';
    }

    $html .= '</body></html>';

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    if (!is_dir('invoices')) mkdir('invoices');
    $filename = 'invoice_' . time() . '.pdf';
    file_put_contents('invoices/' . $filename, $dompdf->output());

    $history = [];
    if (file_exists('invoices/history.json')) {
        $history = json_decode(file_get_contents('invoices/history.json'), true);
    }
    $history[] = [
        'from' => $invoiceData['from'],
        'to' => $invoiceData['to'],
        'date' => date('Y-m-d H:i:s'),
        'filename' => $filename,
        'amount' => $grandTotal,
        'currency' => $currency,
        'notes' => $notes
    ];
    file_put_contents('invoices/history.json', json_encode($history, JSON_PRETTY_PRINT));

    $dompdf->stream($filename, ["Attachment" => false]);
    exit;
}


