<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'VideoDownloader.php';

$action = $_GET['action'] ?? 'download';

try {
    $downloader = new VideoDownloader();
    
    switch ($action) {
        case 'download':
            $url = $_GET['url'] ?? '';
            $platform = $_GET['platform'] ?? 'tiktok';
            
            if (empty($url)) {
                throw new Exception('URL parameter is required');
            }
            
            $result = $downloader->downloadVideo($url, $platform);
            echo json_encode($result);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'downloadUrl' => '',
        'title' => '',
        'thumbnail' => ''
    ]);
}
?>