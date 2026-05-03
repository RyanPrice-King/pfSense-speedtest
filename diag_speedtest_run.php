<?php
require_once("guiconfig.inc");
require_once("functions.inc");
require_once("filter.inc");

csrf_check();

header("Content-Type: application/json");

// Decode payload
$req = json_decode($_POST['payload'] ?? "{}", true);
if (!is_array($req)) {
    echo json_encode(["error" => "Invalid payload"]);
    exit;
}

// Check binary
$bin = "/usr/local/bin/speedtest-go";
if (!file_exists($bin) || !is_executable($bin)) {
    echo json_encode(["error" => "speedtest-go not found or not executable at {$bin}"]);
    exit;
}

// Base command
$cmd = escapeshellarg($bin) . " --json";

// MULTI-SERVER MODE
if (!empty($req["multi"]) && $req["multi"] === "1") {
    // Multi mode cannot use --server
    $cmd .= " --multi";
} else {
    // SINGLE SERVER MODE
    if (!empty($req["server"]) && ctype_digit($req["server"])) {
        $cmd .= " --server " . escapeshellarg($req["server"]);
    }
}

// Map simple flags
$map = [
    "ping_mode"  => "--ping-mode",
    "thread"     => "--thread",
    "custom_url" => "--custom-url",
    "location"   => "--location",
    "city"       => "--city",
    "proxy"      => "--proxy",
    "source"     => "--source",
    "ua"         => "--ua"
];

foreach ($map as $key => $flag) {
    if (!empty($req[$key])) {
        $cmd .= " {$flag} " . escapeshellarg($req[$key]);
    }
}

// Boolean flags
$bools = [
    "multi"      => "--multi",
    "saving"     => "--saving-mode",
    "no_download"=> "--no-download",
    "no_upload"  => "--no-upload",
    "debug"      => "--debug",
    "dns_bind"   => "--dns-bind-source"
];

foreach ($bools as $key => $flag) {
    if (!empty($req[$key])) {
        $cmd .= " {$flag}";
    }
}

// Server selection
if (!empty($req["server"])) {
    if (ctype_digit($req["server"])) {
        $cmd .= " --server " . escapeshellarg($req["server"]);
    } else {
        $cmd .= " --search " . escapeshellarg($req["server"]);
    }
}

// Execute
exec("$cmd 2>&1", $out, $rc);

if ($rc !== 0) {
    echo json_encode(["error" => "speedtest-go failed", "cmd" => $cmd, "output" => $out]);
    exit;
}

$json = json_decode(implode("\n", $out), true);
if (!$json) {
    echo json_encode(["error" => "Invalid JSON from speedtest-go", "raw" => $out]);
    exit;
}

// Extract server (first entry)
$server = $json['servers'][0] ?? [];

// Convert microseconds → milliseconds
$latency_ms = isset($server['latency']) ? round($server['latency'] / 1e6, 2) : null;
$jitter_ms  = isset($server['jitter'])  ? round($server['jitter']  / 1e6, 2) : null;

// Backend conversion for speed results
$unit = $req["unit"] ?? "Mbps";

switch ($unit) {
    case "MB/s":
        $download = isset($server['dl_speed']) ? round($server['dl_speed'] / 1e6, 2) : null;
        $upload   = isset($server['ul_speed']) ? round($server['ul_speed'] / 1e6, 2) : null;
        break;

    case "Mibps":
        $download = isset($server['dl_speed']) ? round(($server['dl_speed'] * 8) / (1024*1024), 2) : null;
        $upload   = isset($server['ul_speed']) ? round(($server['ul_speed'] * 8) / (1024*1024), 2) : null;
        break;

    case "MiB/s":
        $download = isset($server['dl_speed']) ? round($server['dl_speed'] / (1024*1024), 2) : null;
        $upload   = isset($server['ul_speed']) ? round($server['ul_speed'] / (1024*1024), 2) : null;
        break;

    default: // Mbps
        $download = isset($server['dl_speed']) ? round(($server['dl_speed'] * 8) / 1e6, 2) : null;
        $upload   = isset($server['ul_speed']) ? round(($server['ul_speed'] * 8) / 1e6, 2) : null;
        break;
}

$download_mbps = isset($server['dl_speed']) ? round(($server['dl_speed'] * 8) / 1e6, 2) : null;
$upload_mbps   = isset($server['ul_speed']) ? round(($server['ul_speed'] * 8) / 1e6, 2) : null;

// Build result
$result = [
    "timestamp"       => $json['timestamp'] ?? date('c'),

    "ping_ms"         => $latency_ms,
    "jitter_ms"       => $jitter_ms,

    "download"   => $download,
    "upload"     => $upload,
    "unit"       => $unit,

    "download_mbps"   => $download_mbps,
    "upload_mbps"     => $upload_mbps,

    "server_name"     => $server['name'] ?? '',
    "server_location" => trim(($server['sponsor'] ?? '') . ", " . ($server['country'] ?? '')),
    "server_id"       => $server['id'] ?? '',

    "client_ip"       => $json['user_info']['IP'] ?? '',
    "client_isp"      => $json['user_info']['Isp'] ?? '',
    "client_lat"      => $json['user_info']['Lat'] ?? '',
    "client_lon"      => $json['user_info']['Lon'] ?? '',

    "result_id"       => $server['host'] ?? ''
];

file_put_contents("/var/run/speedtest.json", json_encode($result, JSON_PRETTY_PRINT));

// RRD update
$rrdfile = "/var/db/rrd/speedtest.rrd";

if (!file_exists($rrdfile)) {
    $step = 300;
    $cmd_create = sprintf(
        'rrdtool create %s --step %d ' .
        'DS:down:GAUGE:600:0:U ' .
        'DS:up:GAUGE:600:0:U ' .
        'DS:ping:GAUGE:600:0:U ' .
        'RRA:AVERAGE:0.5:1:288 ' .
        'RRA:AVERAGE:0.5:12:744 ' .
        'RRA:AVERAGE:0.5:288:365',
        escapeshellarg($rrdfile),
        $step
    );
    @exec($cmd_create);
}

$down = is_numeric($result['download']) ? $result['download'] : 'U';
$up   = is_numeric($result['upload'])   ? $result['upload']   : 'U';
$ping = is_numeric($result['ping_ms'])       ? $result['ping_ms']       : 'U';

$cmd_update = sprintf(
    'rrdtool update %s N:%s:%s:%s',
    escapeshellarg($rrdfile),
    $down,
    $up,
    $ping
);
@exec($cmd_update);

// CSV log
$logfile = "/var/log/speedtest.log";

$fields = [
    $result['timestamp'],
    $result['ping_ms'],
    $result['jitter_ms'],
    $result['download'],
    $result['upload'],
    $result['server_name'],
    $result['server_location'],
    $result['client_ip'],
    $result['client_isp'],
    $result['result_id'],
    $result['server_id'] ?? ''   // include server_id if present
];

$fp = fopen($logfile, "a");
fputcsv($fp, $fields);
fclose($fp);
echo json_encode($result);
exit;
