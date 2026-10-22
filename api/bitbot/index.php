<?php
/**
 * BitBot Native Standalone PHP Proxy
 * Proxies chat requests securely to the youami.ai backend service.
 * Keeps BITBOT_API_KEY private on the server (never exposed to browser client).
 */

declare(strict_types=1);

// Prevent PHP runtime notices/deprecations from corrupting JSON output
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../../includes/db.php';

$rawUri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($rawUri, PHP_URL_PATH) ?: '';

// 1. Endpoint: /widget-config
if (str_ends_with($path, 'widget-config') || isset($_GET['config'])) {
    echo json_encode([
        'position'        => 'bottom-right',
        'primaryColor'    => '#0073aa',
        'welcomeMessage'  => 'Hi! How can I help you navigate our website today?',
        'languageCode'    => 'en',
        'widgetTitle'     => 'Chat with us',
        'placeholderText' => 'Type your message...',
        'accessPaused'    => false,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. Beacons: /visitor-heartbeat, /visitor-left, /citation-click
if (preg_match('#/(visitor-heartbeat|visitor-left|citation-click)$#', $path)) {
    echo json_encode(['success' => true]);
    exit;
}

// 3. Chat Endpoint: POST /chat (or direct POST to /api/bitbot/)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

// Rate Limiting: Max 25 chat messages per minute per IP session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$now = time();
$recent = array_filter(
    $_SESSION['bitbot_rate_limit'] ?? [],
    fn($t) => is_int($t) && $t > ($now - 60)
);
if (count($recent) >= 25) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error'   => 'You are sending messages too quickly. Please wait a moment before trying again.',
    ]);
    exit;
}
$recent[] = $now;
$_SESSION['bitbot_rate_limit'] = $recent;

// Validate incoming JSON payload
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!is_array($data) || empty(trim((string)($data['message'] ?? '')))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Message is required.']);
    exit;
}

$userMessage = trim((string)$data['message']);
if (mb_strlen($userMessage) > 1000) {
    $userMessage = mb_substr($userMessage, 0, 1000);
}

// Sanitize conversation history
$sanitizedHistory = [];
if (!empty($data['history']) && is_array($data['history'])) {
    foreach ($data['history'] as $item) {
        if (isset($item['role'], $item['content'])) {
            $role = (string)$item['role'];
            if (in_array($role, ['user', 'assistant'], true)) {
                $sanitizedHistory[] = [
                    'role'    => $role,
                    'content' => mb_substr((string)$item['content'], 0, 1500),
                ];
            }
        }
    }
}
// Keep max recent 20 messages for context
if (count($sanitizedHistory) > 20) {
    $sanitizedHistory = array_slice($sanitizedHistory, -20);
}

$sessionId       = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)($data['sessionId'] ?? ''));
$visitorId       = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)($data['visitorId'] ?? ''));
$visitorTimezone = preg_replace('/[^a-zA-Z0-9_\-\/]/', '', (string)($data['visitorTimezone'] ?? 'Asia/Kuala_Lumpur'));
$siteLanguage    = preg_replace('/[^a-zA-Z\-]/', '', (string)($data['siteLanguage'] ?? 'en'));
$visitorIp       = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// Read secret BITBOT_API_KEY from server environment (.env)
$apiKey = getenv('BITBOT_API_KEY');
$apiKey = ($apiKey !== false) ? trim($apiKey) : '';

// Graceful fallback if API key is not yet configured or placeholder
if ($apiKey === '' || str_starts_with($apiKey, 'your_') || str_starts_with($apiKey, 'bb_placeholder')) {
    echo json_encode([
        'success' => true,
        'data'    => [
            'message'         => "Hello! Welcome to Mowinton Consulting. Our AI assistant is currently connecting to our knowledgebase. You can reach our consulting team directly via email at info@mowinton.com, phone/WhatsApp at +6010 8282 060, or by submitting our contact form.",
            'citations'       => [],
            'suggest_contact' => true,
            'lead_captured'   => false,
        ],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Forward to youami.ai Cloud AI RPC
$payload = [
    '_tag'            => 'Chat',
    'apiKey'          => $apiKey,
    'message'         => $userMessage,
    'history'         => $sanitizedHistory,
    'sessionId'       => $sessionId,
    'visitorIp'       => $visitorIp,
    'siteLanguage'    => $siteLanguage ?: 'en',
    'visitorId'       => $visitorId,
    'visitorTimezone' => $visitorTimezone ?: 'Asia/Kuala_Lumpur',
];

$curlOptions = [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'User-Agent: Mowinton-BitBot-Proxy/2.1.1',
    ],
    CURLOPT_TIMEOUT        => 35,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true,
];

// If local CA bundle exists (Windows development), use it to verify TLS certificates
$localCa = __DIR__ . '/../../includes/cacert.pem';
if (file_exists($localCa)) {
    $curlOptions[CURLOPT_CAINFO] = $localCa;
}

$ch = curl_init('https://www.youami.ai/api/rpc');
curl_setopt_array($ch, $curlOptions);

$responseBody = curl_exec($ch);
$httpStatus   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError    = curl_error($ch);
unset($ch); // Replaces deprecated curl_close() in PHP 8.5+

if ($responseBody === false || $httpStatus >= 500) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'error'   => 'The AI assistant is temporarily unreachable. Please try again or reach us at info@mowinton.com.',
    ]);
    exit;
}

$decoded = json_decode((string)$responseBody, true);

if (isset($decoded['_tag']) && $decoded['_tag'] === 'Failure') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => $decoded['error'] ?? 'The AI service returned an error.',
    ]);
    exit;
}

// Extract message and citations from youami.ai response
$replyMessage = $decoded['value']['message'] ?? $decoded['data']['message'] ?? ($decoded['message'] ?? 'Thank you for your message.');
$citations    = $decoded['value']['citations'] ?? $decoded['data']['citations'] ?? [];
$suggest      = $decoded['value']['suggestContact'] ?? $decoded['data']['suggestContact'] ?? false;
$leadCaptured = $decoded['value']['leadCaptured'] ?? $decoded['data']['leadCaptured'] ?? false;

echo json_encode([
    'success' => true,
    'data'    => [
        'message'         => $replyMessage,
        'citations'       => $citations,
        'suggest_contact' => (bool)$suggest,
        'lead_captured'   => (bool)$leadCaptured,
    ],
], JSON_UNESCAPED_UNICODE);
