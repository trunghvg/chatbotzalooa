<?php

namespace App\Libraries;

use App\Models\SettingModel;

/**
 * Thu vien tuong tac voi Zalo Official Account API v3
 *
 * Tai lieu: https://developers.zalo.me/docs/official-account
 */
class ZaloOA
{
    private const API_BASE   = 'https://openapi.zalo.me';
    private const OAUTH_BASE = 'https://oauth.zaloapp.com/v4';

    private string $appId;
    private string $appSecret;
    private string $oaId;
    private string $accessToken  = '';
    private string $refreshToken = '';
    private bool   $tokensLoaded = false;

    private SettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();

        $this->appId     = env('ZALO_APP_ID', '');
        $this->appSecret = env('ZALO_APP_SECRET', '');
        $this->oaId      = env('ZALO_OA_ID', '');
        // Tokens are loaded lazily on first API call to avoid DB query on every request
    }

    private function ensureTokensLoaded(): void
    {
        if ($this->tokensLoaded) {
            return;
        }
        $this->accessToken  = $this->settingModel->get('zalo_access_token')
                              ?? env('ZALO_ACCESS_TOKEN', '');
        $this->refreshToken = $this->settingModel->get('zalo_refresh_token')
                              ?? env('ZALO_REFRESH_TOKEN', '');
        $this->tokensLoaded = true;
    }

    // ----------------------------------------------------------------
    // GUI TIN NHAN
    // ----------------------------------------------------------------

    /**
     * Gui tin nhan van ban den nguoi dung Zalo
     */
    public function sendTextMessage(string $userId, string $text): array
    {
        $payload = [
            'recipient' => ['user_id' => $userId],
            'message'   => ['text'    => $text],
        ];

        return $this->post('/v3.0/oa/message/cs', $payload);
    }

    /**
     * Gui tin nhan co hinh anh (banner)
     */
    public function sendImageMessage(string $userId, string $imageUrl, string $caption = ''): array
    {
        $payload = [
            'recipient' => ['user_id' => $userId],
            'message'   => [
                'attachment' => [
                    'type'    => 'template',
                    'payload' => [
                        'template_type' => 'media',
                        'elements'      => [[
                            'media_type'  => 'image',
                            'url'         => $imageUrl,
                            'caption'     => $caption,
                        ]],
                    ],
                ],
            ],
        ];

        return $this->post('/v3.0/oa/message/cs', $payload);
    }

    /**
     * Gui tin nhan co nut bam (button)
     */
    public function sendButtonMessage(string $userId, string $text, array $buttons): array
    {
        $payload = [
            'recipient' => ['user_id' => $userId],
            'message'   => [
                'attachment' => [
                    'type'    => 'template',
                    'payload' => [
                        'template_type' => 'button',
                        'text'          => $text,
                        'buttons'       => $buttons,
                    ],
                ],
            ],
        ];

        return $this->post('/v3.0/oa/message/cs', $payload);
    }

    // ----------------------------------------------------------------
    // THONG TIN NGUOI DUNG
    // ----------------------------------------------------------------

    /**
     * Lay thong tin profile cua nguoi dung Zalo
     */
    public function getUserProfile(string $userId): array
    {
        return $this->get('/v3.0/oa/user/detail', ['user_id' => $userId]);
    }

    // ----------------------------------------------------------------
    // TOKEN MANAGEMENT
    // ----------------------------------------------------------------

    /**
     * Lam moi Access Token bang Refresh Token
     * Zalo Access Token het han sau 1 gio, Refresh Token sau 3 thang
     */
    public function refreshAccessToken(): array
    {
        $this->ensureTokensLoaded();
        $url  = self::OAUTH_BASE . '/oa/access_token';
        $data = [
            'refresh_token' => $this->refreshToken,
            'app_id'        => $this->appId,
            'grant_type'    => 'refresh_token',
            'secret_key'    => $this->appSecret,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', '[ZaloOA] Refresh token curl error: ' . $error);
            return ['error' => $error];
        }

        $result = json_decode($response, true) ?? [];

        if (isset($result['access_token'])) {
            $this->accessToken  = $result['access_token'];
            $this->refreshToken = $result['refresh_token'] ?? $this->refreshToken;

            // Luu vao DB de su dung sau
            $this->settingModel->saveSetting('zalo_access_token', $this->accessToken);
            $this->settingModel->saveSetting('zalo_refresh_token', $this->refreshToken);
            $this->settingModel->saveSetting('zalo_token_expires_at',
                date('Y-m-d H:i:s', time() + ($result['expires_in'] ?? 3600)));

            log_message('info', '[ZaloOA] Access token refreshed successfully.');
        } else {
            log_message('error', '[ZaloOA] Refresh token failed: ' . $response);
        }

        return $result;
    }

    /**
     * Xac minh chu ky webhook tu Zalo (HMAC-SHA256)
     */
    public function verifyWebhook(string $rawBody, string $signature): bool
    {
        $expectedSig = hash_hmac('sha256', $rawBody, $this->appSecret);
        return hash_equals($expectedSig, $signature);
    }

    // ----------------------------------------------------------------
    // INTERNAL HTTP HELPERS
    // ----------------------------------------------------------------

    private function post(string $endpoint, array $data): array
    {
        return $this->request('POST', $endpoint, $data);
    }

    private function get(string $endpoint, array $params = []): array
    {
        $this->ensureTokensLoaded();
        $url = self::API_BASE . $endpoint;
        if ($params) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'access_token: ' . $this->accessToken,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', "[ZaloOA] GET $endpoint curl error: $error");
            return ['error' => $error];
        }

        $result = json_decode($response, true) ?? [];

        // Token het han - tu dong refresh va thu lai
        if (($result['error'] ?? null) === -216) {
            log_message('warning', '[ZaloOA] Token expired, refreshing...');
            $refreshResult = $this->refreshAccessToken();
            if (isset($refreshResult['access_token'])) {
                return $this->get($endpoint, $params);
            }
        }

        return $result;
    }

    private function request(string $method, string $endpoint, array $data = []): array
    {
        $this->ensureTokensLoaded();
        $url = self::API_BASE . $endpoint;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'access_token: ' . $this->accessToken,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', "[ZaloOA] $method $endpoint curl error: $error");
            return ['error' => $error];
        }

        $result = json_decode($response, true) ?? [];

        // Token het han - tu dong refresh va thu lai
        if (($result['error'] ?? null) === -216) {
            log_message('warning', '[ZaloOA] Token expired, refreshing...');
            $refreshResult = $this->refreshAccessToken();
            if (isset($refreshResult['access_token'])) {
                return $this->request($method, $endpoint, $data);
            }
        }

        if (isset($result['error']) && $result['error'] !== 0) {
            log_message('error', "[ZaloOA] API error on $endpoint: " . json_encode($result));
        }

        return $result;
    }

    public function getAccessToken(): string
    {
        $this->ensureTokensLoaded();
        return $this->accessToken;
    }
}
