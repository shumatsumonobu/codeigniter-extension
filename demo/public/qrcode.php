<?php
/**
 * Simple QR Code Generator using Google Charts API alternative
 * Uses a free QR code API service
 */

$data = $_GET['data'] ?? '';

if (empty($data)) {
    http_response_code(400);
    die('Missing data parameter');
}

// Use QR Server API (free, no API key required)
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($data);

// Fetch and serve the QR code
header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400');

$qrImage = file_get_contents($qrUrl);
if ($qrImage === false) {
    // Fallback: return a placeholder
    http_response_code(500);
    die('Failed to generate QR code');
}

echo $qrImage;
