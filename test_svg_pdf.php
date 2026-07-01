<?php
require __DIR__.'/vendor/autoload.php';
$html = '<!DOCTYPE html><html><body><h1>Test SVG</h1>
<svg width="100" height="100"><circle cx="50" cy="50" r="40" stroke="green" stroke-width="4" fill="yellow" /></svg>
</body></html>';
$dompdf = new \Barryvdh\DomPDF\Facade\Pdf();
$pdf = $dompdf->loadHTML($html);
file_put_contents('test_svg.pdf', $pdf->output());
echo "PDF SVG Generated. File size: " . filesize('test_svg.pdf') . "\n";
