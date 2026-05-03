<?php
header("Content-Type: application/json");

$bin = "/usr/local/bin/speedtest-go";
if (!file_exists($bin) || !is_executable($bin)) {
    echo json_encode(["error" => "speedtest-go not found"]);
    exit;
}

$cmd = escapeshellarg($bin) . " --city-list";
exec("$cmd 2>&1", $out, $rc);
if ($rc !== 0) {
    echo json_encode(["error" => "speedtest-go --city-list failed", "output" => $out]);
    exit;
}

/* Assume one city label per line */
$cities = [];
foreach ($out as $line) {
    $line = trim($line);
    if ($line === '') {
        continue;
    }

    // Match only real city label lines: (cc) label [lat,lon]
    if (preg_match('/^\(([a-z]{2})\)\s+(\S+)\s+\[([^\]]+)\]$/i', $line, $m)) {
        $country = $m[1];
        $label   = $m[2];
        $coords  = $m[3];

        $cities[] = [
            "country" => $country,
            "label"   => $label,
            "coords"  => $coords
        ];
    }
}

echo json_encode($cities);
