<?php
header('Content-Type: application/json');

function readCSV($file) {
    $data = [];
    if (($handle = fopen($file, 'r')) !== FALSE) {
        $headers = fgetcsv($handle); // Lire les en-têtes
        while (($row = fgetcsv($handle)) !== FALSE) {
            $data[] = array_combine($headers, $row);
        }
        fclose($handle);
    }
    return $data;
}

$response = [
    'financial_results' => [],
    'invoice_forecast' => [],
    'images' => []
];

// 📄 Fichiers CSV dans le même dossier  
$financial_file = 'financial_results.csv';
$invoice_file = 'invoice_forecast.csv';

if (file_exists($financial_file)) {
    $response['financial_results'] = readCSV($financial_file);
}

if (file_exists($invoice_file)) {
    $response['invoice_forecast'] = readCSV($invoice_file);
}

// 🖼️ Images dans le même dossier que api.php
$image_files = [
    'net_ttc_2023_2025_prediction.png',
    'Prévision SARIMA du nombre mensuel de factures.png'
];

foreach ($image_files as $img) {
    if (file_exists($img)) {
        $response['images'][] = $img;
    }
}

echo json_encode($response);
?>
