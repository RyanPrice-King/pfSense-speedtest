<?php
require_once("guiconfig.inc");
require_once("functions.inc");
require_once("filter.inc");

csrf_check();

$pgtitle = array(gettext("Diagnostics"), gettext("Speedtest Graphs"));
include("head.inc");

$rrd = "/var/db/rrd/speedtest.rrd";
if (!file_exists($rrd)) {
    print_info_box("RRD database not found. Run at least one speedtest first.");
    include("foot.inc");
    exit;
}

/* Time ranges */
$ranges = [
    "1h" => 3600,
    "6h" => 21600,
    "24h" => 86400,
    "1w" => 604800,
    "1m" => 2592000,
    "1y" => 31536000
];

$range = $_GET['range'] ?? "24h";
if (!isset($ranges[$range])) $range = "24h";

$end = time();
$start = $end - $ranges[$range];

function make_graph($title, $ds, $color, $unit, $start, $end, $rrd) {
    $tmp = tempnam("/tmp", "rrd");
    $cmd = sprintf(
        "rrdtool graph %s ".
        "--start %d --end %d ".
        "--title '%s' ".
        "--vertical-label '%s' ".
        "--width 700 --height 200 ".
        "--color BACK#000000 --color CANVAS#0A1228 ".
        "--color FONT#9FB6D8 --color AXIS#4A5A7A ".
        "DEF:val=%s:%s:AVERAGE ".
        "LINE2:val#%s:'%s' ".
        "GPRINT:val:LAST:'Current\\: %%6.2lf %s' ".
        "GPRINT:val:AVERAGE:'Avg\\: %%6.2lf %s' ".
        "GPRINT:val:MAX:'Max\\: %%6.2lf %s' ",
        escapeshellarg($tmp),
        $start,
        $end,
        $title,
        $unit,
        escapeshellarg($rrd),
        $ds,
        $color,
        $title,
        $unit,
        $unit,
        $unit
    );
    exec($cmd, $out, $rc);
    return $tmp;
}

$graph_down = make_graph("Download", "down", "00FFD5", "Mbps", $start, $end, $rrd);
$graph_up   = make_graph("Upload",   "up",   "00B7FF", "Mbps", $start, $end, $rrd);
$graph_ping = make_graph("Ping",     "ping", "FFAA00", "ms",   $start, $end, $rrd);

?>

<link rel="stylesheet" href="/css/speedtest.css?v=1">

<div class="speedtest-page">

  <div class="speedtest-header">
    <div>
      <div class="speedtest-title">Speedtest Graphs</div>
      <div class="speedtest-subtitle">
        Long‑term performance history from RRD.
      </div>
    </div>
  </div>

  <!-- Range selector -->
  <div class="speedtest-controls" style="margin-bottom:20px;">
    <div class="st-control-group">
      <label class="st-label">Range</label>
      <select id="range" class="st-select" onchange="location='?range='+this.value;">
        <?php foreach ($ranges as $k => $v): ?>
          <option value="<?=$k?>" <?=$k==$range?"selected":""?>><?=$k?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <!-- Graphs -->
  <div class="st-history st-glow-soft" style="margin-bottom:20px;">
    <div class="st-history-title">Download</div>
    <img src="data:image/png;base64,<?=base64_encode(file_get_contents($graph_down))?>">
  </div>

  <div class="st-history st-glow-soft" style="margin-bottom:20px;">
    <div class="st-history-title">Upload</div>
    <img src="data:image/png;base64,<?=base64_encode(file_get_contents($graph_up))?>">
  </div>

  <div class="st-history st-glow-soft">
    <div class="st-history-title">Ping</div>
    <img src="data:image/png;base64,<?=base64_encode(file_get_contents($graph_ping))?>">
  </div>

</div>

<?php include("foot.inc"); ?>
