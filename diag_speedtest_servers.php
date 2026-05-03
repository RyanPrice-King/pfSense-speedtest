<?php
header("Content-Type: application/json");

$bin = "/usr/local/bin/speedtest-go";
if (!file_exists($bin) || !is_executable($bin)) {
    echo json_encode(["error" => "speedtest-go not found"]);
    exit;
}

$cmd = escapeshellarg($bin) . " --list";
exec("$cmd 2>&1", $out, $rc);
if ($rc !== 0) {
    echo json_encode(["error" => "speedtest-go --list failed", "output" => $out]);
    exit;
}

/*
 * speedtest-go --list output is text; we’ll parse lines loosely.
 * Adjust parsing if your format differs.
 */
$servers = [];
foreach ($out as $line) {
    $line = trim($line);
    if ($line === '') {
        continue;
    }

    // Match: [ID]   DISTANCE   LATENCY   NAME
    if (preg_match('/^\[(\d+)\]\s+([\d\.]+km)\s+(\d+ms)\s+(.+)$/', $line, $m)) {
        $id       = $m[1];
        $distance = $m[2];
        $latency  = $m[3];
        $name     = $m[4];

        // Convert "27ms" → 27
        $lat = (float) str_replace('ms', '', $latency);

        $servers[] = [
            "id"       => $id,
            "name"     => $name,
            "latency"  => $lat,
            "distance" => $distance
        ];
    }
}

// Sort by lowest latency
usort($servers, function($a, $b) {
    return $a["latency"] <=> $b["latency"];
});

echo json_encode($servers);
