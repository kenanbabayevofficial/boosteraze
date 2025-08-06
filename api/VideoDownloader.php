<?php

class VideoDownloader {
    
    public function downloadVideo($url, $platform) {
        try {
            switch ($platform) {
                case 'tiktok':
                    return $this->downloadTikTok($url);
                case 'instagram':
                    return $this->downloadInstagram($url);
                case 'facebook':
                    return $this->downloadFacebook($url);
                case 'twitter':
                    return $this->downloadTwitter($url);
                default:
                    throw new Exception('Unsupported platform');
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'downloadUrl' => '',
                'title' => 'Unknown',
                'thumbnail' => ''
            ];
        }
    }
    
    private function downloadTikTok($url) {
        // Method 1: TikWM API (En güvenilir)
        try {
            $apiUrl = "https://www.tikwm.com/api/?url=" . urlencode($url);
            $response = $this->makeRequest($apiUrl);
            $data = json_decode($response, true);
            
            if ($data['code'] === 0) {
                return [
                    'success' => true,
                    'message' => 'Video found',
                    'downloadUrl' => $data['data']['play'],
                    'title' => $data['data']['title'] ?? 'TikTok Video',
                    'thumbnail' => $data['data']['cover'] ?? ''
                ];
            }
        } catch (Exception $e) {
            // Continue to next method
        }
        
        // Method 2: TikTok Web Scraping
        try {
            return $this->downloadTikTokWebScraping($url);
        } catch (Exception $e) {
            // Continue to next method
        }
        
        // Method 3: Alternative API
        try {
            return $this->downloadTikTokAlternative($url);
        } catch (Exception $e) {
            throw new Exception('All TikTok download methods failed: ' . $e->getMessage());
        }
    }
    
    private function downloadTikTokWebScraping($url) {
        // Web scraping method for TikTok
        $html = $this->makeRequest($url, [
            'headers' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1'
            ]
        ]);
        
        // Extract video URL from HTML
        if (preg_match('/"playAddr":"([^"]+)"/', $html, $matches)) {
            $videoUrl = str_replace('\\u002F', '/', $matches[1]);
            return [
                'success' => true,
                'message' => 'Video found via web scraping',
                'downloadUrl' => $videoUrl,
                'title' => 'TikTok Video',
                'thumbnail' => ''
            ];
        }
        
        throw new Exception('Video URL not found in HTML');
    }
    
    private function downloadTikTokAlternative($url) {
        // Alternative TikTok API
        $videoId = $this->extractTikTokVideoId($url);
        if (!$videoId) {
            throw new Exception('Invalid TikTok URL');
        }
        
        // Use TikTok's internal API
        $apiUrl = "https://www.tiktok.com/api/item/detail/?itemId=" . $videoId;
        $response = $this->makeRequest($apiUrl, [
            'headers' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Referer: https://www.tiktok.com/',
                'Accept: application/json'
            ]
        ]);
        
        $data = json_decode($response, true);
        if (isset($data['itemInfo']['itemStruct']['video']['playAddr'][0])) {
            return [
                'success' => true,
                'message' => 'Video found via alternative API',
                'downloadUrl' => $data['itemInfo']['itemStruct']['video']['playAddr'][0],
                'title' => $data['itemInfo']['itemStruct']['desc'] ?? 'TikTok Video',
                'thumbnail' => $data['itemInfo']['itemStruct']['video']['cover'] ?? ''
            ];
        }
        
        throw new Exception('Video not found in alternative API');
    }
    
    private function downloadInstagram($url) {
        // Method 1: SnapInsta API
        try {
            $postData = http_build_query(['q' => $url]);
            $response = $this->makeRequest('https://snapinsta.app/api/ajaxSearch', [
                'method' => 'POST',
                'headers' => ['Content-Type: application/x-www-form-urlencoded'],
                'data' => $postData
            ]);
            
            $data = json_decode($response, true);
            if (isset($data['data'])) {
                return [
                    'success' => true,
                    'message' => 'Video found',
                    'downloadUrl' => $data['data']['url'],
                    'title' => $data['data']['title'] ?? 'Instagram Video',
                    'thumbnail' => $data['data']['thumbnail'] ?? ''
                ];
            }
        } catch (Exception $e) {
            // Continue to next method
        }
        
        // Method 2: Instagram Web Scraping
        try {
            return $this->downloadInstagramWebScraping($url);
        } catch (Exception $e) {
            throw new Exception('All Instagram download methods failed: ' . $e->getMessage());
        }
    }
    
    private function downloadInstagramWebScraping($url) {
        // Web scraping method for Instagram
        $html = $this->makeRequest($url, [
            'headers' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive'
            ]
        ]);
        
        // Extract video URL from HTML
        if (preg_match('/"video_url":"([^"]+)"/', $html, $matches)) {
            $videoUrl = str_replace('\\u002F', '/', $matches[1]);
            return [
                'success' => true,
                'message' => 'Video found via web scraping',
                'downloadUrl' => $videoUrl,
                'title' => 'Instagram Video',
                'thumbnail' => ''
            ];
        }
        
        throw new Exception('Video URL not found in HTML');
    }
    
    private function downloadFacebook($url) {
        // Method 1: FB Downloader API
        try {
            $apiUrl = "https://fbdownloader.net/api/download?url=" . urlencode($url);
            $response = $this->makeRequest($apiUrl);
            $data = json_decode($response, true);
            
            if (isset($data['url'])) {
                return [
                    'success' => true,
                    'message' => 'Video found',
                    'downloadUrl' => $data['url'],
                    'title' => $data['title'] ?? 'Facebook Video',
                    'thumbnail' => $data['thumbnail'] ?? ''
                ];
            }
        } catch (Exception $e) {
            // Continue to next method
        }
        
        // Method 2: Facebook Web Scraping
        try {
            return $this->downloadFacebookWebScraping($url);
        } catch (Exception $e) {
            throw new Exception('All Facebook download methods failed: ' . $e->getMessage());
        }
    }
    
    private function downloadFacebookWebScraping($url) {
        // Web scraping method for Facebook
        $html = $this->makeRequest($url, [
            'headers' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive'
            ]
        ]);
        
        // Extract video URL from HTML
        if (preg_match('/"video_url":"([^"]+)"/', $html, $matches)) {
            $videoUrl = str_replace('\\u002F', '/', $matches[1]);
            return [
                'success' => true,
                'message' => 'Video found via web scraping',
                'downloadUrl' => $videoUrl,
                'title' => 'Facebook Video',
                'thumbnail' => ''
            ];
        }
        
        throw new Exception('Video URL not found in HTML');
    }
    
    private function downloadTwitter($url) {
        // Twitter API v2 (API key gerekli)
        $tweetId = $this->extractTwitterTweetId($url);
        if ($tweetId) {
            // For demo purposes, return mock data
            // In production, you need Twitter API key
            return [
                'success' => true,
                'message' => 'Video found (API key required for real download)',
                'downloadUrl' => 'https://example.com/twitter-video.mp4',
                'title' => 'Twitter Video',
                'thumbnail' => ''
            ];
        }
        
        throw new Exception('Failed to download Twitter video');
    }
    
    private function makeRequest($url, $options = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        
        if (isset($options['method']) && $options['method'] === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['data']);
        }
        
        if (isset($options['headers'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: {$error}");
        }
        
        if ($httpCode === 404) {
            throw new Exception("HTTP 404: API endpoint not found");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: {$httpCode}");
        }
        
        return $response;
    }
    
    private function extractTikTokVideoId($url) {
        // Extract video ID from various TikTok URL formats
        $patterns = [
            '/tiktok\.com\/@[^\/]+\/video\/(\d+)/',
            '/vm\.tiktok\.com\/([a-zA-Z0-9]+)/',
            '/vt\.tiktok\.com\/([a-zA-Z0-9]+)/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    private function extractInstagramMediaId($url) {
        // Extract media ID from Instagram URL
        $pattern = '/instagram\.com\/(?:p|reel|tv)\/([a-zA-Z0-9_-]+)/';
        
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    private function extractFacebookVideoId($url) {
        // Extract video ID from Facebook URL
        $pattern = '/facebook\.com\/[^\/]+\/videos\/(\d+)/';
        
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    private function extractTwitterTweetId($url) {
        // Extract tweet ID from Twitter URL
        $pattern = '/twitter\.com\/[^\/]+\/status\/(\d+)/';
        
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}
?>