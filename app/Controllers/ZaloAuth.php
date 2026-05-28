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

    public function authorize(): ResponseInterface
    {
        $this->requireAuth();

        $appId         = env('ZALO_APP_ID', '');
        $redirectUri   = base_url('zalo/callback');
        $codeChallenge = $this->generateCodeChallenge();

        // Encode verifier in state (no session needed — survives container restarts)
        $state = rtrim(strtr(base64_encode(json_encode([
            'v' => $codeChallenge['verifier'],
            'r' => bin2hex(random_bytes(8)),
        ])), '+/', '-_'), '=');

        $params = http_build_query([
            'app_id'         => $appId,
            'redirect_uri'   => $redirectUri,
            'code_challenge' => $codeChallenge['challenge'],
            'state'          => $state,
        ]);

        return redirect()->to(self::OAUTH_BASE . '/permission?' . $params);
    }

    public function callback(): string
    {
        $this->requireAuth();

        $code  = $this->request->getGet('code');
        $oaId  = $this->request->getGet('oa_id');
        $state = $this->request->getGet('state');
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

        // Recover code_verifier from state parameter
        $codeVerifier = '';
        if ($state) {
            $decoded = json_decode(base64_decode(strtr($state, '-_', '+/')), true);
            $codeVerifier = $decoded['v'] ?? '';
        }

        $result = $this->exchangeCodeForToken($code, $codeVerifier);

        if (isset($result['access_token'])) {
            $this->settingModel->saveSetting('zalo_access_token',    $result['access_token']);
            $this->settingModel->saveSetting('zalo_refresh_token',   $result['refresh_token'] ?? '');
            $this->settingModel->saveSetting('zalo_token_expires_at',
                date('Y-m-d H:i:s', time() + ($result['expires_in'] ?? 3600)));
            $this->settingModel->saveSetting('zalo_oa_id', $oaId ?? env('ZALO_OA_ID', ''));

            return view('admin/oauth_result', [
                'title'        => 'Xác thực Zalo',
                'success'      => true,
                'message'      => 'Lấy Access Token thành công!',
                'access_token' => substr($result['access_token'], 0, 20) . '...',
                'expires_in'   => $result['expires_in'] ?? 3600,
            ]);
        }

        return view('admin/oauth_result', [
            'title'   => 'Xác thực Zalo',
            'success' => false,
            'message' => 'Lỗi đổi token: ' . json_encode($result),
        ]);
    }

    private function generateCodeChallenge(): array
    {
        $verifier  = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
        return ['verifier' => $verifier, 'challenge' => $challenge];
    }

    private function exchangeCodeForToken(string $code, string $codeVerifier): array
    {
        $url  = self::OAUTH_BASE . '/oa/access_token';
        $data = [
            'code'          => $code,
            'app_id'        => env('ZALO_APP_ID', ''),
            'grant_type'    => 'authorization_code',
            'code_verifier' => $codeVerifier,
            'redirect_uri'  => base_url('zalo/callback'),
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
                'secret_key: ' . env('ZALO_APP_SECRET', ''),
            ],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }
}
