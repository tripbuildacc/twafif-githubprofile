<?php
header("Content-Type: application/json");

// cek parameter
if (!isset($_GET['queryuser']) || trim($_GET['queryuser']) === "") {
    echo json_encode([
        "output" => "fail",
        "content" => [
            "issue" => "query parameter is missing"
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

$query = urlencode($_GET['queryuser']);

// endpoint search GitHub
$url = "https://api.github.com/search/users?q={$query}&per_page=100";

// GitHub butuh user-agent
$opts = [
    "http" => [
        "header" => "User-Agent: PHP\r\n"
    ]
];

$context = stream_context_create($opts);
$response = @file_get_contents($url, false, $context);

// kalau gagal fetch
if ($response === false) {
    echo json_encode([
        "output" => "fail",
        "content" => [
            "issue" => "request failed"
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

$data = json_decode($response, true);

// tidak ada user
if (!isset($data["items"]) || count($data["items"]) === 0) {
    echo json_encode([
        "output" => "fail",
        "content" => [
            "issue" => "user not found"
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

// susun hasil
$results = [];

foreach ($data["items"] as $u) {
    $results[] = [
        "username" => $u["login"],
        "id"       => $u["id"],
        "link"     => $u["html_url"]
    ];
}

// keluarkan output final
echo json_encode([
    "output"  => "work",
    "content" => $results
], JSON_PRETTY_PRINT);