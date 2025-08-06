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
        // Extract video ID from TikTok URL
        $videoId = $this->extractTikTokVideoId($url);
        
        if (!$videoId) {
            throw new Exception('Invalid TikTok URL');
        }
        
        // Use TikTok API or web scraping to get video info
        $videoInfo = $this->getTikTokVideoInfo($videoId);
        
        return [
            'success' => true,
            'message' => 'Video found',
            'downloadUrl' => $videoInfo['download_url'],
            'title' => $videoInfo['title'],
            'thumbnail' => $videoInfo['thumbnail']
        ];
    }
    
    private function downloadInstagram($url) {
        // Extract media ID from Instagram URL
        $mediaId = $this->extractInstagramMediaId($url);
        
        if (!$mediaId) {
            throw new Exception('Invalid Instagram URL');
        }
        
        // Use Instagram API or web scraping to get video info
        $videoInfo = $this->getInstagramVideoInfo($mediaId);
        
        return [
            'success' => true,
            'message' => 'Video found',
            'downloadUrl' => $videoInfo['download_url'],
            'title' => $videoInfo['title'],
            'thumbnail' => $videoInfo['thumbnail']
        ];
    }
    
    private function downloadFacebook($url) {
        // Extract video ID from Facebook URL
        $videoId = $this->extractFacebookVideoId($url);
        
        if (!$videoId) {
            throw new Exception('Invalid Facebook URL');
        }
        
        // Use Facebook API or web scraping to get video info
        $videoInfo = $this->getFacebookVideoInfo($videoId);
        
        return [
            'success' => true,
            'message' => 'Video found',
            'downloadUrl' => $videoInfo['download_url'],
            'title' => $videoInfo['title'],
            'thumbnail' => $videoInfo['thumbnail']
        ];
    }
    
    private function downloadTwitter($url) {
        // Extract tweet ID from Twitter URL
        $tweetId = $this->extractTwitterTweetId($url);
        
        if (!$tweetId) {
            throw new Exception('Invalid Twitter URL');
        }
        
        // Use Twitter API or web scraping to get video info
        $videoInfo = $this->getTwitterVideoInfo($tweetId);
        
        return [
            'success' => true,
            'message' => 'Video found',
            'downloadUrl' => $videoInfo['download_url'],
            'title' => $videoInfo['title'],
            'thumbnail' => $videoInfo['thumbnail']
        ];
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
    
    private function getTikTokVideoInfo($videoId) {
        // This is a simplified implementation
        // In production, you would need to implement proper TikTok API integration
        // or use a third-party service
        
        // For demo purposes, return mock data
        return [
            'download_url' => "https://example.com/tiktok/video_{$videoId}.mp4",
            'title' => "TikTok Video {$videoId}",
            'thumbnail' => "https://example.com/tiktok/thumb_{$videoId}.jpg"
        ];
    }
    
    private function getInstagramVideoInfo($mediaId) {
        // This is a simplified implementation
        // In production, you would need to implement proper Instagram API integration
        
        return [
            'download_url' => "https://example.com/instagram/video_{$mediaId}.mp4",
            'title' => "Instagram Video {$mediaId}",
            'thumbnail' => "https://example.com/instagram/thumb_{$mediaId}.jpg"
        ];
    }
    
    private function getFacebookVideoInfo($videoId) {
        // This is a simplified implementation
        // In production, you would need to implement proper Facebook API integration
        
        return [
            'download_url' => "https://example.com/facebook/video_{$videoId}.mp4",
            'title' => "Facebook Video {$videoId}",
            'thumbnail' => "https://example.com/facebook/thumb_{$videoId}.jpg"
        ];
    }
    
    private function getTwitterVideoInfo($tweetId) {
        // This is a simplified implementation
        // In production, you would need to implement proper Twitter API integration
        
        return [
            'download_url' => "https://example.com/twitter/video_{$tweetId}.mp4",
            'title' => "Twitter Video {$tweetId}",
            'thumbnail' => "https://example.com/twitter/thumb_{$tweetId}.jpg"
        ];
    }
    
    private function makeRequest($url, $headers = []) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP request failed with code: {$httpCode}");
        }
        
        return $response;
    }
}
?>