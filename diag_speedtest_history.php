<?php
header("Content-Type: application/json");

$log = "/var/log/speedtest.log";
$out = [];

if (file_exists($log)) {
    $lines = file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $p = str_getcsv($line);
        if (count($p) < 11) continue;

        $out[] = [
            "timestamp" => $p[0],
            "ping_ms" => $p[1],
            "jitter_ms" => $p[2],
            "download" => $p[3],
            "upload" => $p[4],
            "server_name" => $p[5],
            "server_location" => $p[6],
            "client_ip" => $p[7],
            "client_isp" => $p[8],
            "result_id" => $p[9],
            "server_id" => $p[10]
        ];
    }
}

echo json_encode($out);
