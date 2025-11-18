<?php
// founder.php
header('Content-Type: application/json; charset=utf-8');

// ambil parameter user
$user = isset($_GET['user']) ? trim($_GET['user']) : '';

if ($user === '') {
    echo json_encode([
        'output' => 'fail',
        'content' => [
            'issue' => 'missing user parameter'
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// panggil GitHub API
$apiUrl = "https://api.github.com/users/" . rawurlencode($user);

// gunakan cURL supaya bisa set User-Agent dan timeout
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 8);
// GitHub wajibkan User-Agent
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: Tripweb-Tools/1.0',
    'Accept: application/vnd.github.v3+json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($response === false || $curlErr) {
    echo json_encode([
        'output' => 'fail',
        'content' => [
            'issue' => 'network error',
            'detail' => $curlErr
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($httpCode === 200) {
    $d = json_decode($response, true);

    // safety: pastikan field ada
    $username = isset($d['login']) ? $d['login'] : null;
    $bio = isset($d['bio']) ? $d['bio'] : null;
    $followers = isset($d['followers']) ? $d['followers'] : 0;
    $following = isset($d['following']) ? $d['following'] : 0;
    // sesuai permintaan: key \"public repost\" (menjaga penamaan yang kamu minta)
    $public_repost = isset($d['public_repos']) ? $d['public_repos'] : 0;
    $photo = isset($d['avatar_url']) ? $d['avatar_url'] : null;
    $link = isset($d['html_url']) ? $d['html_url'] : ("https://github.com/" . rawurlencode($user));

    echo json_encode([
        'output' => 'work',
        'content' => [
            'username' => $username,
            'bio' => $bio,
            'follower' => $followers,
            'following' => $following,
            'public repost' => $public_repost,
            'photo profile' => $photo,
            'link' => $link
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// 404 atau error lain dari GitHub
if ($httpCode === 404) {
    echo json_encode([
        'output' => 'fail',
        'content' => [
            'issue' => 'user not found'
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// rate limit atau error lain
echo json_encode([
    'output' => 'fail',
    'content' => [
        'issue' => 'github api error',
        'http_code' => $httpCode,
        'response' => json_decode($response, true)
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);