<?php

namespace App\Controllers;

use App\Models\SettingModel;
use CodeIgniter\HTTP\ResponseInterface;

class ZaloAuth extends BaseController
{
    private const OAUTH_BASE = 'https://oauth.zaloapp.com/v4';

    private SettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    /**
     * Buoc 1: Redirect den Zalo de cap quyen OA
     * Khong dung PKCE (Zalo OA API khong ho tro code_challenge)
     */
    public function authorize(): ResponseInterface
    {
        $this->requireAuth();

        $appId       = env('ZALO_APP_ID', '');
        $redirectUri = base_url('zalo/callback');
        $state       = bin2hex(random_bytes(16));

        // Luu state vao session de xac minh CSRF khi callback
        session()->set('zalo_oauth_state', $state);

        $params = http_build_query([
            'app_id'       => $appId,
            'redirect_uri' => $redirectUri,
            'state'        => $state,
        ]);

        return redirect()->to(self::OAUTH_BASE . '/permission?' . $params);
    }

    /**
     * Buoc 2: Nhan code tu Zalo, doi lay OA Access Token
     */
    public function callback(): string
    {
        $this->requireAuth();

        $code  = $this->request->getGet('code');
        $oaId  = $this->request->getGet('oa_id');
        $error = $this->request->getGet('error');

        if ($error) {
            return view('admin/oauth_result', [
                'title'   => 'Xác thực Zalo',
                'success' => false,
                'message' => 'Zalo từ chối cấp quyền: ' . $error,
            ]);
        }

        if (!$code) {
            return view('admin/oauth_result', [
                'title'   => 'Xác thực Zalo',
                'success' => false,
                'message' => 'Không nhận được authorization code từ Zalo.',
            ]);
        }

        $result = $this->exchangeCodeForToken($code);

        if (isset($result['access_token'])) {
            $this->settingModel->saveSetting('zalo_access_token',    $result['access_token']);
            $this->settingModel->saveSetting('zalo_refresh_token',   $result['refresh_token'] ?? '');
            $this->settingModel->saveSetting('zalo_token_expires_at',
                date('Y-m-d H:i:s', time() + ($result['expires_in'] ?? 3600)));
            $this->settingModel->saveSetting('zalo_oa_id', $oaId ?? env('ZALO_OA_ID', ''));

            return view('admin/oauth_result', [
                'title'        => 'Xác thực Zalo',
                'success'      => true,
                'message'      => 'Lấy Access Token thành công! Bot Zalo đã sẵn sàng.',
                'access_token' => substr($result['access_token'], 0, 20) . '...',
                'expires_in'   => $result['expires_in'] ?? 3600,
            ]);
        }

        // Debug: show full request details to diagnose -14005
        $debugInfo = [
            'app_id'       => env('ZALO_APP_ID', '(not set)'),
            'redirect_uri' => base_url('zalo/callback'),
            'code_prefix'  => substr($code, 0, 20) . '...',
            'oa_id'        => $oaId,
            'zalo_response'=> $result,
        ];

        return view('admin/oauth_result', [
            'title'   => 'Xác thực Zalo',
            'success' => false,
            'message' => 'Lỗi đổi token: <pre>' . json_encode($debugInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>',
        ]);
    }

    /**
     * Doi authorization code lay OA Access Token
     * API: POST /v4/oa/access_token
     * Header: secret_key
     * Body: app_id, code, grant_type
     */
    private function exchangeCodeForToken(string $code): array
    {
        $appId       = env('ZALO_APP_ID', '');
        $appSecret   = env('ZALO_APP_SECRET', '');
        $redirectUri = base_url('zalo/callback');

        // Try OA access token endpoint first
        $url  = self::OAUTH_BASE . '/oa/access_token';
        $body = http_build_query([
            'app_id'       => $appId,
            'code'         => $code,
            'grant_type'   => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ]);

        log_message('info', "[ZaloAuth] Trying OA token exchange app_id=$appId redirect_uri=$redirectUri");

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
                'secret_key: ' . $appSecret,
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true) ?? [];
        log_message('info', "[ZaloAuth] OA token response HTTP $httpCode: $response");

        // If OA endpoint fails, try social login endpoint (user access token)
        if (!isset($result['access_token'])) {
            $url2  = self::OAUTH_BASE . '/access_token';
            $body2 = http_build_query([
                'app_id'       => $appId,
                'code'         => $code,
                'grant_type'   => 'authorization_code',
                'redirect_uri' => $redirectUri,
            ]);

            $ch2 = curl_init($url2);
            curl_setopt_array($ch2, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $body2,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'secret_key: ' . $appSecret,
                ],
            ]);
            $response2 = curl_exec($ch2);
            $httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
            curl_close($ch2);

            $result2 = json_decode($response2, true) ?? [];
            log_message('info', "[ZaloAuth] Social token response HTTP $httpCode2: $response2");

            // Return combined debug info
            return [
                'oa_endpoint'     => $result,
                'social_endpoint' => $result2,
                'access_token'    => $result2['access_token'] ?? null,
                'refresh_token'   => $result2['refresh_token'] ?? null,
                'expires_in'      => $result2['expires_in'] ?? null,
            ];
        }

        return $result;
    }
}
