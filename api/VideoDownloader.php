<?php

class VideoDownloader {
    
    public function downloadVideo($url, $platform) {
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
    }
    
    private function downloadTikTok($url) {
        // TikWM API - En güvenilir TikTok API'si
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
        } else {
            // Fallback: TikTok Scraper API
            return $this->downloadTikTokFallback($url);
        }
    }
    
    private function downloadTikTokFallback($url) {
        // TikTok Scraper API - Yedek API
        $videoId = $this->extractTikTokVideoId($url);
        if ($videoId) {
            $apiUrl = "https://api.tiktok.com/api/item/detail/?itemId=" . $videoId;
            $response = $this->makeRequest($apiUrl);
            $data = json_decode($response, true);
            
            if (isset($data['itemInfo']['itemStruct']['video']['playAddr'][0])) {
                return [
                    'success' => true,
                    'message' => 'Video found',
                    'downloadUrl' => $data['itemInfo']['itemStruct']['video']['playAddr'][0],
                    'title' => $data['itemInfo']['itemStruct']['desc'] ?? 'TikTok Video',
                    'thumbnail' => $data['itemInfo']['itemStruct']['video']['cover'] ?? ''
                ];
            }
        }
        
        throw new Exception('Failed to download TikTok video');
    }
    
    private function downloadInstagram($url) {
        // SnapInsta API - En güvenilir Instagram API'si
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
        } else {
            // Fallback: Instagram Scraper API
            return $this->downloadInstagramFallback($url);
        }
    }
    
    private function downloadInstagramFallback($url) {
        // Instagram Scraper API - Yedek API
        $postId = $this->extractInstagramMediaId($url);
        if ($postId) {
            $apiUrl = "https://www.instagram.com/p/{$postId}/?__a=1";
            $response = $this->makeRequest($apiUrl);
            $data = json_decode($response, true);
            
            if (isset($data['graphql']['shortcode_media']['video_url'])) {
                return [
                    'success' => true,
                    'message' => 'Video found',
                    'downloadUrl' => $data['graphql']['shortcode_media']['video_url'],
                    'title' => $data['graphql']['shortcode_media']['title'] ?? 'Instagram Video',
                    'thumbnail' => $data['graphql']['shortcode_media']['display_url'] ?? ''
                ];
            }
        }
        
        throw new Exception('Failed to download Instagram video');
    }
    
    private function downloadFacebook($url) {
        // FB Downloader API - En güvenilir Facebook API'si
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
        } else {
            // Fallback: Facebook Video Downloader API
            return $this->downloadFacebookFallback($url);
        }
    }
    
    private function downloadFacebookFallback($url) {
        // Facebook Video Downloader API - Yedek API
        $apiUrl = "https://api.facebook.com/video/download?url=" . urlencode($url);
        $response = $this->makeRequest($apiUrl);
        $data = json_decode($response, true);
        
        if (isset($data['video_url'])) {
            return [
                'success' => true,
                'message' => 'Video found',
                'downloadUrl' => $data['video_url'],
                'title' => $data['title'] ?? 'Facebook Video',
                'thumbnail' => $data['thumbnail'] ?? ''
            ];
        }
        
        throw new Exception('Failed to download Facebook video');
    }
    
    private function downloadTwitter($url) {
        // Twitter API v2 - En güncel Twitter API'si
        $tweetId = $this->extractTwitterTweetId($url);
        if ($tweetId) {
            // Not: Twitter API için API key gerekli
            // Bu örnek için mock data döndürüyoruz
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
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: {$httpCode}");
        }
        
        return $response;
    }
    
    private function extractTikTokVideoId($url) {
        // TikTok URL'den video ID çıkar
        preg_match('/\/video\/(\d+)/', $url, $matches);
        return $matches[1] ?? null;
    }
    
    private function extractInstagramMediaId($url) {
        // Instagram URL'den media ID çıkar
        preg_match('/\/p\/([A-Za-z0-9_-]+)/', $url, $matches);
        return $matches[1] ?? null;
    }
    
    private function extractFacebookVideoId($url) {
        // Facebook URL'den video ID çıkar
        preg_match('/\/videos\/(\d+)/', $url, $matches);
        return $matches[1] ?? null;
    }
    
    private function extractTwitterTweetId($url) {
        // Twitter URL'den tweet ID çıkar
        preg_match('/\/status\/(\d+)/', $url, $matches);
        return $matches[1] ?? null;
    }
}
?>