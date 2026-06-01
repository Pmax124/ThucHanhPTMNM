<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class GoogleClient {
    private $client;
    
    public function __construct() {
        $this->client = new Google_Client();
        
        // ⚠️ NHỚ THAY BẰNG CLIENT ID & SECRET MỚI
        $this->client->setClientId('178485761338-samgcrfunaeveggash8g3317s3ntsm58.apps.googleusercontent.com');
        $this->client->setClientSecret('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'); // ← Thay secret mới vào đây
        
        // ✅ Tự động detect redirect URI
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $redirectUri = "$protocol://$host/account/google-callback";
        $this->client->setRedirectUri($redirectUri);
        
        $this->client->addScope('email');
        $this->client->addScope('profile');
        
        // ✅ QUAN TRỌNG: Thêm 2 dòng này để hiện form chọn tài khoản
        $this->client->setPrompt('select_account');
        $this->client->setAccessType('offline');
    }
    
    public function getClient() {
        return $this->client;
    }
    
    public function getAuthUrl() {
        return $this->client->createAuthUrl();
    }
    
    public function verifyCode($code) {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        if (!isset($token['error'])) {
            $this->client->setAccessToken($token);
            return $token;
        }
        return false;
    }
    
    public function getUserInfo() {
        $oauth2 = new Google_Service_Oauth2($this->client);
        return $oauth2->userinfo->get();
    }
}
?>