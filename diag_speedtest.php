<?php
require_once("guiconfig.inc");
require_once("functions.inc");
require_once("filter.inc");

$pgtitle = array(gettext("Diagnostics"), gettext("Speedtest"));
include("head.inc");
?>

<link rel="stylesheet" href="/css/speedtest.css?v=1">
<script src="/js/chart.umd.min.js"></script>
<script src="/js/chartjs-chart-gauge.min.js"></script>

<form method="post" id="csrf-form">
</form>

<div class="speedtest-page">

  <!-- Header -->
  <div class="speedtest-header">
    <div>
      <div class="speedtest-title">Speedtest</div>
      <div class="speedtest-subtitle">
        Modern diagnostics powered by speedtest-go with full advanced controls.
      </div>
    </div>
  </div>

  <!-- Control Panel -->
  <div class="speedtest-controls">

    <!-- Server + Load button -->
    <div class="st-control-group">
      <label class="st-label">Server</label>
      <select id="st-server" class="st-select">
        <option value="">Auto-select</option>
      </select>
      <small class="helper-text">Click “Load servers” to populate.</small>
    </div>

    <div class="st-control-group">
      <label class="st-label">&nbsp;</label>
      <button id="st-load-servers" class="st-btn-primary st-glow-soft" style="padding:4px 10px;font-size:11px;">
        Load servers
      </button>
    </div>

    <!-- Custom URL -->
    <div class="st-control-group">
      <label class="st-label">Custom URL</label>
      <input id="st-custom-url" class="st-input" placeholder="http(s)://...">
    </div>

    <!-- Location -->
    <div class="st-control-group">
      <label class="st-label">Location (lat,lon)</label>
      <input id="st-location" class="st-input" placeholder="51.50,-0.12">
    </div>

    <!-- City + Load button -->
    <div class="st-control-group">
      <label class="st-label">City</label>
      <select id="st-city" class="st-select">
        <option value="">None</option>
      </select>
      <small class="helper-text">Click “Load cities” to populate.</small>
    </div>

    <div class="st-control-group">
      <label class="st-label">&nbsp;</label>
      <button id="st-load-cities" class="st-btn-primary st-glow-soft" style="padding:4px 10px;font-size:11px;">
        Load cities
      </button>
    </div>

    <!-- Proxy -->
    <div class="st-control-group">
      <label class="st-label">Proxy</label>
      <input id="st-proxy" class="st-input" placeholder="http://user:pass@host:port">
    </div>

    <!-- Source Interface -->
    <div class="st-control-group">
      <label class="st-label">Source Interface</label>
      <input id="st-source" class="st-input" placeholder="e.g. igb0">
    </div>

    <!-- Ping Mode -->
    <div class="st-control-group">
      <label class="st-label">Ping Mode</label>
      <select id="st-ping-mode" class="st-select">
        <option value="http">HTTP</option>
        <option value="icmp">ICMP</option>
        <option value="tcp">TCP</option>
      </select>
    </div>

    <!-- Unit -->
    <div class="st-control-group">
      <label class="st-label">Unit</label>
      <select id="st-unit" class="st-select">
        <option value="Mbps">Mbps</option>
        <option value="MB/s">MB/s</option>
        <option value="Mibps">Mibps</option>
        <option value="MiB/s">MiB/s</option>
      </select>
    </div>

    <!-- Threads -->
    <div class="st-control-group">
      <label class="st-label">Threads</label>
      <input id="st-thread" class="st-input" type="number" min="1" max="64" value="8">
    </div>

    <!-- User-Agent -->
    <div class="st-control-group">
      <label class="st-label">User-Agent</label>
      <input id="st-ua" class="st-input" placeholder="Custom UA">
    </div>

    <!-- Toggles -->
    <div class="st-control-group">
      <label class="st-label">Options</label>
      <div class="st-toggle">
        <label><input type="checkbox" id="st-multi"> Multi-server</label><br>
        <label><input type="checkbox" id="st-saving"> Saving mode</label><br>
        <label><input type="checkbox" id="st-no-download"> No download</label><br>
        <label><input type="checkbox" id="st-no-upload"> No upload</label><br>
        <label><input type="checkbox" id="st-debug"> Debug</label><br>
        <label><input type="checkbox" id="st-dns-bind"> DNS bind source</label>
      </div>
    </div>

    <!-- Run button -->
    <div class="st-control-group">
      <label class="st-label">&nbsp;</label>
      <button id="st-run" class="st-start-btn st-glow-soft">Run Speedtest</button>
    </div>

  </div>

  <!-- Status -->
  <div class="speedtest-status" id="st-status">
    Ready. Configure options and click Run Speedtest.
  </div>

<!-- TOP ROW: Download, Upload, Ping, Jitter -->
<div class="st-tiles">

    <div class="st-tile st-glow-soft" id="tile-download">
      <div class="st-tile-inner">
        <div class="st-tile-label">Download</div>
        <div class="st-tile-value">
            <span id="st-download">--</span>
            <span id="st-download-unit" class="st-tile-unit"></span>
        </div>
        <div class="st-tile-sub">Throughput</div>
      </div>
    </div>

    <div class="st-tile st-glow-soft" id="tile-upload">
      <div class="st-tile-inner">
        <div class="st-tile-label">Upload</div>
        <div class="st-tile-value">
            <span id="st-upload">--</span>
            <span id="st-upload-unit" class="st-tile-unit"></span>
        </div>
        <div class="st-tile-sub">Throughput</div>
      </div>
    </div>

    <div class="st-tile st-glow-soft" id="tile-ping">
      <div class="st-tile-inner">
        <div class="st-tile-label">Ping</div>
        <div class="st-tile-value">
            <span id="st-ping">--</span><span class="st-tile-unit">ms</span>
        </div>
        <div class="st-tile-sub">Latency</div>
      </div>
    </div>

    <div class="st-tile st-glow-soft" id="tile-jitter">
      <div class="st-tile-inner">
        <div class="st-tile-label">Jitter</div>
        <div class="st-tile-value">
            <span id="st-jitter">--</span><span class="st-tile-unit">ms</span>
        </div>
        <div class="st-tile-sub">Variation</div>
      </div>
    </div>

</div> <!-- END TOP ROW -->



<!-- BOTTOM ROW: Client, Server, Result ID -->
<div class="st-tiles bottom-row">

    <div class="st-tile st-glow-soft">
      <div class="st-tile-inner">
        <div class="st-tile-label">Client</div>
        <div class="st-tile-value" style="font-size:16px;">
            <span id="st-client-ip">--</span>
        </div>
        <div class="st-tile-sub">
            <span id="st-client-isp">--</span>
        </div>
      </div>
    </div>

    <div class="st-tile st-glow-soft">
      <div class="st-tile-inner">
        <div class="st-tile-label">Server</div>
        <div class="st-tile-value" style="font-size:16px;">
            <span id="st-server-name">--</span>
        </div>
        <div class="st-tile-sub">
            <span id="st-server-location">--</span>
        </div>
      </div>
    </div>

    <div class="st-tile result-wide st-glow-soft">
      <div class="st-tile-inner">
        <div class="st-tile-label">Result ID</div>
        <div class="st-tile-value" style="font-size:16px;">
            <span id="st-result-id">--</span>
        </div>
        <div class="st-tile-sub">
            <span id="st-timestamp">--</span>
        </div>
      </div>
    </div>

</div> <!-- END BOTTOM ROW -->

  <!-- History -->
  <div class="st-history st-glow-soft">
    <div class="st-history-title">Recent Results</div>
    <div class="st-table-wrapper">
      <table class="st-table" id="st-history-table">
        <thead>
          <tr>
            <th>Time</th>
            <th>Ping</th>
            <th>Jitter</th>
            <th>Down</th>
            <th>Up</th>
            <th>Server</th>
            <th>Location</th>
            <th>IP</th>
          </tr>
        </thead>
        <tbody id="st-history-body"></tbody>
      </table>
    </div>
  </div>

  <!-- Charts -->
  <div class="st-history st-glow-soft" style="margin-top:16px;">
  <div class="st-history-title">Throughput Charts</div>

  <div class="st-gauge-grid">

      <!-- Download -->
      <div class="st-gauge-cell">
          <div class="st-gauge-title">Download Speed</div>
          <div class="st-gauge-wrapper">
              <canvas id="st-chart-download"></canvas>
              <div class="st-gauge-glass"></div>
              <div class="st-gauge-minmax">
                  <span class="min">0</span>
                  <span class="max">1200</span>
              </div>
          </div>
          <div class="st-gauge-value" id="val-download">0</div>
          <div class="st-gauge-desc">Measured in Mbps (Megabits per second)</div>
      </div>

      <!-- Upload -->
      <div class="st-gauge-cell">
          <div class="st-gauge-title">Upload Speed</div>
          <div class="st-gauge-wrapper">
              <canvas id="st-chart-upload"></canvas>
              <div class="st-gauge-glass"></div>
              <div class="st-gauge-minmax">
                  <span class="min">0</span>
                  <span class="max">120</span>
              </div>
          </div>
          <div class="st-gauge-value" id="val-upload">0</div>
          <div class="st-gauge-desc">Measured in Mbps (Megabits per second)</div>
      </div>

      <!-- Ping -->
      <div class="st-gauge-cell">
          <div class="st-gauge-title">Ping</div>
          <div class="st-gauge-wrapper">
              <canvas id="st-chart-ping"></canvas>
              <div class="st-gauge-glass"></div>
              <div class="st-gauge-minmax">
                  <span class="min">0</span>
                  <span class="max">60</span>
              </div>
          </div>
          <div class="st-gauge-value" id="val-ping">0</div>
          <div class="st-gauge-desc">Round‑trip latency in milliseconds</div>
      </div>

      <!-- Jitter -->
      <div class="st-gauge-cell">
          <div class="st-gauge-title">Jitter</div>
          <div class="st-gauge-wrapper">
              <canvas id="st-chart-jitter"></canvas>
              <div class="st-gauge-glass"></div>
              <div class="st-gauge-minmax">
                  <span class="min">0</span>
                  <span class="max">10</span>
              </div>
          </div>
          <div class="st-gauge-value" id="val-jitter">0</div>
          <div class="st-gauge-desc">Variation in latency (ms)</div>
      </div>

    <!-- AVERAGE GAUGES -->
    <div class="st-gauge-cell">
    <div class="st-gauge-title">Avg Download</div>
    <div class="st-gauge-wrapper">
        <canvas id="st-chart-avg-download"></canvas>
        <div class="st-gauge-glass"></div>
        <div class="st-gauge-minmax">
            <span class="min">0</span>
            <span class="max">1200</span>
        </div>
    </div>
    <div class="st-gauge-value" id="val-avg-download">0</div>
    <div class="st-gauge-desc">10‑Test Average (Mbps)</div>
    </div>

    <div class="st-gauge-cell">
    <div class="st-gauge-title">Avg Upload</div>
    <div class="st-gauge-wrapper">
        <canvas id="st-chart-avg-upload"></canvas>
        <div class="st-gauge-glass"></div>
        <div class="st-gauge-minmax">
            <span class="min">0</span>
            <span class="max">120</span>
        </div>
    </div>
    <div class="st-gauge-value" id="val-avg-upload">0</div>
    <div class="st-gauge-desc">10‑Test Average (Mbps)</div>
    </div>

    <div class="st-gauge-cell">
    <div class="st-gauge-title">Avg Ping</div>
    <div class="st-gauge-wrapper">
        <canvas id="st-chart-avg-ping"></canvas>
        <div class="st-gauge-glass"></div>
        <div class="st-gauge-minmax">
            <span class="min">0</span>
            <span class="max">60</span>
        </div>
    </div>
    <div class="st-gauge-value" id="val-avg-ping">0</div>
    <div class="st-gauge-desc">10‑Test Average (ms)</div>
    </div>

    <div class="st-gauge-cell">
    <div class="st-gauge-title">Avg Jitter</div>
    <div class="st-gauge-wrapper">
        <canvas id="st-chart-avg-jitter"></canvas>
        <div class="st-gauge-glass"></div>
        <div class="st-gauge-minmax">
            <span class="min">0</span>
            <span class="max">10</span>
        </div>
    </div>
    <div class="st-gauge-value" id="val-avg-jitter">0</div>
    <div class="st-gauge-desc">10‑Test Average (ms)</div>
    </div>    
</div>

<?php include("foot.inc"); ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
let avgDL = 0, avgUL = 0, avgPing = 0, avgJitter = 0;

function computeHistoryAverages(history) {
    // Take last 10 entries
    const last10 = history.slice(-10);

    avgDL = last10.reduce((a, b) => a + Number(b.download || 0), 0) / last10.length;
    avgUL = last10.reduce((a, b) => a + Number(b.upload || 0), 0) / last10.length;
    avgPing = last10.reduce((a, b) => a + Number(b.ping_ms || 0), 0) / last10.length;
    avgJitter = last10.reduce((a, b) => a + Number(b.jitter_ms || 0), 0) / last10.length;
}

function loadHistory() {
    fetch('diag_speedtest_history.php')
        .then(r => r.json())
        .then(list => {
            if (!Array.isArray(list) || list.length === 0) return;

            const latest = list[list.length - 1];

            updateRecentResults(latest);
            updateHistoryTable(list);
            computeHistoryAverages(list)
            updateThroughputCharts(latest);
        })
        .catch(err => console.error("History load failed:", err));
}

function updateRecentResults(data) {
    const tbody = document.getElementById('st-recent-body');
    if (!tbody) return;

    tbody.innerHTML = `
        <tr>
            <td>${data.timestamp}</td>
            <td>${data.ping_ms}</td>
            <td>${data.jitter_ms}</td>
            <td>${data.download}</td>
            <td>${data.upload}</td>
            <td>${data.server_name}</td>
            <td>${data.server_location}</td>
            <td>${data.client_ip}</td>
        </tr>
    `;
}

// ------------------------------
// Initialize Throughput Charts
// ------------------------------

// ------------------------------
// Colour Threshold Helper
// ------------------------------
function gaugeColors(value, max) {
    const pct = value / max;

    if (pct < 0.33) return ['#ff4d4d55', '#ff4d4dAA', '#ff4d4dFF'];   // red
    if (pct < 0.66) return ['#ffcc0055', '#ffcc00AA', '#ffcc00FF'];   // yellow
    return ['#00ff8855', '#00ff88AA', '#00ff88FF'];                   // green
}

function gaugeColorsLatency(value, max) {
    const pct = value / max;

    if (pct < 0.33) return ['#00ff8855', '#00ff88AA', '#00ff88FF'];   // green (good)
    if (pct < 0.66) return ['#ffcc0055', '#ffcc00AA', '#ffcc00FF'];   // yellow
    return ['#ff4d4d55', '#ff4d4dAA', '#ff4d4dFF'];                   // red (bad)
}

function gaugeGradient(ctx, value, max) {
    const pct = value / max;

    const gradient = ctx.createLinearGradient(0, 0, 300, 0);

    if (pct < 0.33) {
        gradient.addColorStop(0, "#ff4d4d55");
        gradient.addColorStop(1, "#ff4d4dFF");
    } else if (pct < 0.66) {
        gradient.addColorStop(0, "#ffcc0055");
        gradient.addColorStop(1, "#ffcc00FF");
    } else {
        gradient.addColorStop(0, "#00ff8855");
        gradient.addColorStop(1, "#00ff88FF");
    }

    return gradient;
}

function gaugeGradientLatency(ctx, value, max) {
    const pct = value / max;
    const gradient = ctx.createLinearGradient(0, 0, 300, 0);

    if (pct < 0.33) {
        gradient.addColorStop(0, "#00ff8855");
        gradient.addColorStop(1, "#00ff88FF");
    } else if (pct < 0.66) {
        gradient.addColorStop(0, "#ffcc0055");
        gradient.addColorStop(1, "#ffcc00FF");
    } else {
        gradient.addColorStop(0, "#ff4d4d55");
        gradient.addColorStop(1, "#ff4d4dFF");
    }

    return gradient;
}

function animateTickGlow(chart, value, max) {
    const pct = value / max;
    const tickIndex = Math.floor(pct * chart.options.ticks.count);

    chart.options.ticks.lineColor = (i => {
        if (i === tickIndex) return "#00ff88FF";
        return "#ffffff33";
    });

    chart.update();
}

function autoScale(chart, value) {
    const max = chart.data.datasets[0].data.reduce((a,b) => a+b, 0);
    if (value > max * 0.95) {
        const newMax = Math.ceil(value * 1.2);
        const step = newMax / 3;
        chart.data.datasets[0].data = [step, step*2, newMax];
    }
}

// ------------------------------
// Shared Gauge Options
// ------------------------------
const gaugeBaseOptions = {
    type: 'gauge',
    options: {
        responsive: true,
        maintainAspectRatio: false,

        rotation: -90,
        circumference: 180,

        animation: {
            duration: 1200,
            easing: 'easeOutCubic',
            animateRotate: true
        },

        needle: {
            radius: "8%",
            width: "4%",
            length: "80%",
            color: "#ffffff",
            borderColor: "#00000055",
            borderWidth: 1,
            shadowColor: "rgba(0,0,0,0.4)",
            shadowBlur: 8
        },

        valueLabel: {
            display: false,
            formatter: v => v.toFixed(0),
            color: "#fff",
            font: { size: 18, weight: 'bold' },
            backgroundColor: "rgba(0,0,0,0.4)",
            borderRadius: 6,
            padding: 6
        },

        ticks: {
            display: true,
            lineWidth: 2,
            lineColor: "#ffffff33",
            count: 30
        }
    }
};

// ------------------------------
// Gradient Colour Helper
// ------------------------------
function gaugeGradient(ctx, value, max) {
    const pct = value / max;
    const g = ctx.createLinearGradient(0, 0, 300, 0);

    if (pct < 0.33) {
        g.addColorStop(0, "#ff4d4d55");
        g.addColorStop(1, "#ff4d4dFF");
    } else if (pct < 0.66) {
        g.addColorStop(0, "#ffcc0055");
        g.addColorStop(1, "#ffcc00FF");
    } else {
        g.addColorStop(0, "#00ff8855");
        g.addColorStop(1, "#00ff88FF");
    }

    return g;
}

function colourForSpeed(v, max) {
    const pct = v / max;
    if (pct < 0.33) return "#ff4d4d";   // red
    if (pct < 0.66) return "#ffcc00";   // yellow
    return "#00ff88";                   // green
}

// ------------------------------
// Gauge Creation
// ------------------------------
let downloadChart, avgDownloadChart, uploadChart, avgUploadChart, pingChart, avgPingChart, jitterChart, avgJitterChart;

// Download
{
    const c = document.getElementById('st-chart-download');
    if (c) {
        const ctx = c.getContext('2d');
        downloadChart = new Chart(ctx, {
            ...gaugeBaseOptions,
            data: {
                datasets: [{
                    value: 0,
                    data: [300, 800, 1200],
                    backgroundColor: gaugeGradient(ctx, 0, 1200),
                    borderWidth: 0
                }]
            }
        });
    }
}

// avgDownload
{
    const c = document.getElementById('st-chart-avg-download');
    if (c) {
        const ctx = c.getContext('2d');
        avgDownloadChart = new Chart(ctx, {
            ...gaugeBaseOptions,
            data: {
                datasets: [{
                    value: 0,
                    data: [300, 800, 1200],
                    backgroundColor: gaugeGradient(ctx, 0, 1200),
                    borderWidth: 0
                }]
            }
        });
    }
}

// Upload
{
    const c = document.getElementById('st-chart-upload');
    if (c) {
        const ctx = c.getContext('2d');
        uploadChart = new Chart(ctx, {
            ...gaugeBaseOptions,
            data: {
                datasets: [{
                    value: 0,
                    data: [30, 40, 120],
                    backgroundColor: gaugeGradient(ctx, 0, 120),
                    borderWidth: 0
                }]
            }
        });
    }
}

// avgUpload
{
    const c = document.getElementById('st-chart-avg-upload');
    if (c) {
        const ctx = c.getContext('2d');
        avgUploadChart = new Chart(ctx, {
            ...gaugeBaseOptions,
            data: {
                datasets: [{
                    value: 0,
                    data: [30, 40, 120],
                    backgroundColor: gaugeGradient(ctx, 0, 120),
                    borderWidth: 0
                }]
            }
        });
    }
}

// Ping
{
    const c = document.getElementById('st-chart-ping');
    if (c) {
        const ctx = c.getContext('2d');
        pingChart = new Chart(ctx, {
            ...gaugeBaseOptions,
            data: {
                datasets: [{
                    value: 0,
                    data: [20, 40, 60],
                    backgroundColor: gaugeGradient(ctx, 0, 60),
                    borderWidth: 0
                }]
            },
            options: {
                ...gaugeBaseOptions.options,
                valueLabel: {
                    display: false,
                    formatter: v => v.toFixed(1) + " ms"
                }
            }
        });
    }
}

// avgPing
{
    const c = document.getElementById('st-chart-avg-ping');
    if (c) {
        const ctx = c.getContext('2d');
        avgPingChart = new Chart(ctx, {
            ...gaugeBaseOptions,
            data: {
                datasets: [{
                    value: 0,
                    data: [20, 40, 60],
                    backgroundColor: gaugeGradient(ctx, 0, 60),
                    borderWidth: 0
                }]
            },
            options: {
                ...gaugeBaseOptions.options,
                valueLabel: {
                    display: false,
                    formatter: v => v.toFixed(1) + " ms"
                }
            }
        });
    }
}

// Jitter
{
    const c = document.getElementById('st-chart-jitter');
    if (c) {
        const ctx = c.getContext('2d');
        jitterChart = new Chart(ctx, {
            ...gaugeBaseOptions,
            data: {
                datasets: [{
                    value: 0,
                    data: [1, 5, 10],
                    backgroundColor: gaugeGradient(ctx, 0, 10),
                    borderWidth: 0
                }]
            },
            options: {
                ...gaugeBaseOptions.options,
                valueLabel: {
                    display: false,
                    formatter: v => v.toFixed(1) + " ms"
                }
            }
        });
    }
}

// avgJitter
{
    const c = document.getElementById('st-chart-avg-jitter');
    if (c) {
        const ctx = c.getContext('2d');
        avgJitterChart = new Chart(ctx, {
            ...gaugeBaseOptions,
            data: {
                datasets: [{
                    value: 0,
                    data: [1, 5, 10],
                    backgroundColor: gaugeGradient(ctx, 0, 10),
                    borderWidth: 0
                }]
            },
            options: {
                ...gaugeBaseOptions.options,
                valueLabel: {
                    display: false,
                    formatter: v => v.toFixed(1) + " ms"
                }
            }
        });
    }
}

// ------------------------------
// Update Function
// ------------------------------

function updateGauge(chart, value, max) {
    if (!chart) return;

    // Clamp value
    const v = Math.min(value, max);

    // Update gauge value
    chart.data.datasets[0].value = v;

    // Update domain (required for chartjs-chart-gauge)
    chart.options.domain = [0, max];

    chart.update();
}

function updateThroughputCharts(data) {
    if (!data) return;

    const dl = Number(data.download) || 0;
    const ul = Number(data.upload) || 0;
    const ping = Number(data.ping_ms) || 0;
    const jitter = Number(data.jitter_ms) || 0;

    if (downloadChart) {
        const ctx = downloadChart.ctx;
        downloadChart.data.datasets[0].value = dl;
        downloadChart.data.datasets[0].backgroundColor = gaugeGradient(ctx, dl, 1200);
        downloadChart.update();
    }

    if (avgDownloadChart) {
        const ctx = avgDownloadChart.ctx;
        avgDownloadChart.data.datasets[0].value = avgDL;
        avgDownloadChart.data.datasets[0].backgroundColor = gaugeGradient(ctx, avgDL, 1200);
        avgDownloadChart.update();
    }

    if (uploadChart) {
        const ctx = uploadChart.ctx;
        uploadChart.data.datasets[0].value = ul;
        uploadChart.data.datasets[0].backgroundColor = gaugeGradient(ctx, ul, 120);
        uploadChart.update();
    }

    if (avgUploadChart) {
        const ctx = avgUploadChart.ctx;
        avgUploadChart.data.datasets[0].value = avgUL;
        avgUploadChart.data.datasets[0].backgroundColor = gaugeGradient(ctx, avgUL, 120);
        avgUploadChart.update();
    }

    if (pingChart) {
        const ctx = pingChart.ctx;
        pingChart.data.datasets[0].value = ping;
        pingChart.data.datasets[0].backgroundColor = gaugeGradientLatency(pingChart.ctx, ping, 60);
        pingChart.update();
    }

    if (avgPingChart) {
        const ctx = avgPingChart.ctx;
        avgPingChart.data.datasets[0].value = avgPing;
        avgPingChart.data.datasets[0].backgroundColor = gaugeGradientLatency(avgPingChart.ctx, avgPing, 60);
        avgPingChart.update();
    }

    if (jitterChart) {
        const ctx = jitterChart.ctx;
        jitterChart.data.datasets[0].value = jitter;
        jitterChart.data.datasets[0].backgroundColor = gaugeGradientLatency(jitterChart.ctx, jitter, 10);
        jitterChart.update();
    }

    if (avgJitterChart) {
        const ctx = avgJitterChart.ctx;
        avgJitterChart.data.datasets[0].value = avgJitter;
        avgJitterChart.data.datasets[0].backgroundColor = gaugeGradientLatency(avgJitterChart.ctx, avgJitter, 10);
        avgJitterChart.update();
    }

    document.getElementById("val-download").textContent = dl + " Mbps";
    document.getElementById("val-upload").textContent = ul + " Mbps";
    document.getElementById("val-ping").textContent = ping.toFixed(1) + " ms";
    document.getElementById("val-jitter").textContent = jitter.toFixed(1) + " ms";
    document.getElementById("val-ping").style.color = gaugeColorsLatency(ping, 60)[2];
    document.getElementById("val-jitter").style.color = gaugeColorsLatency(jitter, 30)[2];

    document.getElementById("val-avg-download").textContent = avgDL.toFixed(1) + " Mbps";
    document.getElementById("val-avg-upload").textContent = avgUL.toFixed(1) + " Mbps";
    document.getElementById("val-avg-ping").textContent = avgPing.toFixed(1) + " ms";
    document.getElementById("val-avg-jitter").textContent = avgJitter.toFixed(1) + " ms";

    updateGauge(downloadChart, dl, 1200);
    updateGauge(uploadChart, ul, 120);
    updateGauge(pingChart, ping, 60);
    updateGauge(jitterChart, jitter, 10);
    updateGauge(avgDownloadChart, avgDL, 1200);
    updateGauge(avgUploadChart, avgUL, 120);
    updateGauge(avgPingChart, avgPing, 60);
    updateGauge(avgJitterChart, avgJitter, 10);
}

function updateHistoryTable(list) {
    const tbody = document.getElementById('st-history-body');
    if (!tbody || !Array.isArray(list)) return;

    tbody.innerHTML = "";

    list.slice().reverse().forEach(row => {
        const tr = document.createElement('tr');

        tr.innerHTML = `
            <td>${row.timestamp || "--"}</td>
            <td>${row.ping_ms ?? "--"}</td>
            <td>${row.jitter_ms ?? "--"}</td>
            <td>${row.download ?? "--"}</td>
            <td>${row.upload ?? "--"}</td>
            <td>${row.server_name || "--"}</td>
            <td>${row.server_location || "--"}</td>
            <td>${row.client_ip || "--"}</td>
        `;

        tbody.appendChild(tr);
    });
}

// Load history immediately on page load
loadHistory();

// Refresh every 5 seconds
setInterval(loadHistory, 5000);

function distanceKm(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI/180;
    const dLon = (lon2 - lon1) * Math.PI/180;

    const a =
        Math.sin(dLat/2)**2 +
        Math.cos(lat1 * Math.PI/180) *
        Math.cos(lat2 * Math.PI/180) *
        Math.sin(dLon/2)**2;

    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

function getBrowserLocation() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            return reject("Geolocation not supported");
        }

        navigator.geolocation.getCurrentPosition(
            pos => {
                const lat = pos.coords.latitude.toFixed(6);
                const lon = pos.coords.longitude.toFixed(6);
                resolve(`${lat}, ${lon}`);
            },
            err => reject(err.message),
            { enableHighAccuracy: true, timeout: 5000 }
        );
    });
}

function sortCitiesByDistance(coords) {
    const [lat1, lon1] = coords.split(',').map(Number);

    cities.forEach(c => {
        const [lat2, lon2] = c.coords.split(',').map(Number);
        c.distance = distanceKm(lat1, lon1, lat2, lon2);
    });

    cities.sort((a, b) => a.distance - b.distance);
}

function rebuildCityDropdown() {
    const citySelect = document.getElementById('st-city');
    citySelect.innerHTML = '';

    cities.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.label;
        opt.textContent = `${c.label} (${c.country.toUpperCase()}) — ${c.distance.toFixed(1)} km`;
        citySelect.appendChild(opt);
    });
}

async function loadCities() {
    const locInput = document.getElementById('st-location');
    let loc = locInput.value;

    // Try browser geolocation
    try {
        const detected = await getBrowserLocation();
        loc = detected;
        locInput.value = detected;
    } catch (e) {
        console.log("Geolocation failed:", e);
        // fallback: use existing LOCATION field
    }

    // Fetch cities from backend
    fetch('diag_speedtest_cities.php')
        .then(r => r.json())
        .then(list => {
            cities = list;
            sortCitiesByDistance(loc);
            rebuildCityDropdown();
        });
}

  // ------------------------------
// Load Servers
// ------------------------------
const loadServersBtn = document.getElementById('st-load-servers');
const serverSelect = document.getElementById('st-server');

if (loadServersBtn) {
  loadServersBtn.addEventListener('click', () => {

    const tokenEl = document.querySelector('input[name="__csrf_magic"]');
    if (!tokenEl || !tokenEl.value) {
      console.error("CSRF token missing");
      return;
    }
    const token = tokenEl.value;

    loadServersBtn.disabled = true;
    loadServersBtn.textContent = 'Loading…';

    fetch(`diag_speedtest_servers.php?__csrf_magic=${encodeURIComponent(token)}`)
      .then(r => r.json())
      .then(list => {
        serverSelect.innerHTML = '<option value="">Auto-select</option>';
        if (Array.isArray(list)) {
          list.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = `${s.latency}ms - ${s.name}`;
            serverSelect.appendChild(opt);
          });
        }
        loadServersBtn.textContent = 'Load servers';
        loadServersBtn.disabled = false;
      })
      .catch(err => {
        console.error(err);
        loadServersBtn.textContent = 'Load servers';
        loadServersBtn.disabled = false;
      });
  });
}

// ------------------------------
// Load Cities
// ------------------------------
const loadCitiesBtn = document.getElementById('st-load-cities');
const citySelect = document.getElementById('st-city');

if (loadCitiesBtn) {
  loadCitiesBtn.addEventListener('click', loadCities);
}

  const runBtn = document.getElementById('st-run');
  const statusEl = document.getElementById('st-status');

  function setStatus(msg) {
    statusEl.textContent = msg;
  }

  function setTileActive(active) {
    const tiles = {
      ping: document.getElementById('tile-ping'),
      jitter: document.getElementById('tile-jitter'),
      download: document.getElementById('tile-download'),
      upload: document.getElementById('tile-upload')
    };
    Object.values(tiles).forEach(t => {
      if (!t) return;
      t.classList.toggle('active', active);
    });
  }

  runBtn.addEventListener("click", () => {

    // 1. Get CSRF token safely
    const tokenEl = document.querySelector('input[name="__csrf_magic"]');
    if (!tokenEl || !tokenEl.value) {
      console.error("CSRF token missing");
      setStatus("Error: CSRF token missing");
      return;
    }
    const token = tokenEl.value;

    // 2. Build payload BEFORE using it
    const payload = {
      server: document.getElementById('st-server').value,
      custom_url: document.getElementById('st-custom-url').value,
      location: document.getElementById('st-location').value,
      city: document.getElementById('st-city').value,
      proxy: document.getElementById('st-proxy').value,
      source: document.getElementById('st-source').value,
      unit: document.getElementById('st-unit').value,
      ping_mode: document.getElementById('st-ping-mode').value,
      thread: document.getElementById('st-thread').value,
      ua: document.getElementById('st-ua').value,
      multi: document.getElementById('st-multi').checked,
      saving: document.getElementById('st-saving').checked,
      no_download: document.getElementById('st-no-download').checked,
      no_upload: document.getElementById('st-no-upload').checked,
      debug: document.getElementById('st-debug').checked,
      dns_bind: document.getElementById('st-dns-bind').checked
    };

    // 3. Build POST body correctly
    const body = new URLSearchParams({
      '__csrf_magic': token,
      'payload': JSON.stringify(payload)
    });

    runBtn.disabled = true;
    setTileActive(true);
    setStatus("Running speedtest-go…");

    fetch("/diag_speedtest_run.php", {
      method: "POST",
      body,
      credentials: "same-origin"
    })
    .then(r => r.json())
    .then(data => {

      runBtn.disabled = false;
      setTileActive(false);

      if (data.error) {
        setStatus("Error: " + data.error);
        return;
      }

      // update UI...
      document.getElementById('st-ping').textContent = data.ping_ms ?? "--";
      document.getElementById('st-jitter').textContent = data.jitter_ms ?? "--";
      document.getElementById('st-download').textContent = data.download ?? "--";
      document.getElementById('st-download-unit').textContent = data.unit ?? "--";
      document.getElementById('st-upload').textContent = data.upload ?? "--";
      document.getElementById('st-upload-unit').textContent = data.unit ?? "--";
      document.getElementById('st-server-name').textContent = data.server_name || "--";
      document.getElementById('st-server-location').textContent = data.server_location || "--";
      document.getElementById('st-client-ip').textContent = data.client_ip || "--";
      document.getElementById('st-client-isp').textContent = data.client_isp || "--";
      document.getElementById('st-result-id').textContent = data.result_id || "--";
      document.getElementById('st-timestamp').textContent = data.timestamp || "--";

      setStatus("Speedtest completed successfully.");
    })
    .catch(err => {
      runBtn.disabled = false;
      setTileActive(false);
      setStatus("Error: " + err);
    });

  });

});
</script>
