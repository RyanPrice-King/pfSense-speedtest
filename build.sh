#!/bin/sh
set -euo pipefail

PKGNAME="pfsense-pkg-speedtest"
VERSION="1.0.0"
PKGDIRNAME="speedtest"   # nested directory under work/stage/usr/local/pkg/<PKGDIRNAME>
WORKDIR="work"
STAGEDIR="${WORKDIR}/stage"
MANIFESTDIR="${WORKDIR}/${PKGNAME}"
MANIFEST="${MANIFESTDIR}/+MANIFEST"
MAINTAINER="Ryan Price-King <you@example.com>"
PREFIX="/"

# Clean previous build
rm -rf "${WORKDIR}"
mkdir -p "${MANIFESTDIR}"

# Create the nested staging tree pfSense/pkg expects
mkdir -p "${STAGEDIR}/usr/local/pkg/${PKGDIRNAME}/usr/local/www"
mkdir -p "${STAGEDIR}/usr/local/pkg/${PKGDIRNAME}/usr/local/www/css"
mkdir -p "${STAGEDIR}/usr/local/pkg/${PKGDIRNAME}/usr/local/pkg"

# Copy your files into the nested tree (adjust source paths if needed)
cp -f diag_speedtest.php "${STAGEDIR}/usr/local/pkg/${PKGDIRNAME}/usr/local/www/" || true
cp -f diag_speedtest_run.php "${STAGEDIR}/usr/local/pkg/${PKGDIRNAME}/usr/local/www/" || true
cp -f diag_speedtest_servers.php "${STAGEDIR}/usr/local/pkg/${PKGDIRNAME}/usr/local/www/" || true
cp -f diag_speedtest_cities.php "${STAGEDIR}/usr/local/pkg/${PKGDIRNAME}/usr/local/www/" || true
cp -f diag_speedtest_history.php "${STAGEDIR}/usr/local/pkg/${PKGDIRNAME}/usr/local/www/" || true
cp -f graph_speedtest.php "${STAGEDIR}/usr/local/pkg/${PKGDIRNAME}/usr/local/www/" || true
cp -f css/speedtest.css "${STAGEDIR}/usr/local/pkg/${PKGDIRNAME}/usr/local/www/css/" || true
cp -f speedtest.xml "${STAGEDIR}/usr/local/pkg/${PKGDIRNAME}/usr/local/pkg/" || true
cp -f info.xml "${STAGEDIR}/usr/local/pkg/${PKGDIRNAME}/usr/local/pkg/" || true
cp -f pkg-install "${STAGEDIR}/usr/local/pkg/${PKGDIRNAME}/usr/local/pkg/" || true
cp -f pkg-deinstall "${STAGEDIR}/usr/local/pkg/${PKGDIRNAME}/usr/local/pkg/" || true

# Ensure files exist for easier debugging (prints missing files)
echo "Staged files:"
find "${STAGEDIR}" -type f | sed 's/^/  /' || true

# Write pure UCL manifest expected by pfSense pkg
cat > "${MANIFEST}" <<'EOF'
name: "pfsense-pkg-speedtest"
version: "1.0.0"
origin: "net/pfsense-pkg-speedtest"
maintainer: "${MAINTAINER}"
comment: "Speedtest diagnostics package using speedtest-go"
arch: "FreeBSD:15:amd64"
prefix: "${PREFIX}"
desc: "Modern Speedtest Diagnostics using speedtest-go with charts and various advanced controls."
files: {
    usr/local/www/diag_speedtest.php: "",
    usr/local/www/diag_speedtest_run.php: "",
    usr/local/www/diag_speedtest_servers.php: "",
    usr/local/www/diag_speedtest_cities.php: "",
    usr/local/www/diag_speedtest_history.php: "",
    usr/local/www/graph_speedtest.php: "",
    usr/local/www/css/speedtest.css: "",
    usr/local/pkg/speedtest.xml: "",
    usr/local/pkg/pkg-install: "",
    usr/local/pkg/pkg-deinstall: ""
}
EOF

# Ensure newline at EOF
echo >> "${MANIFEST}"

# Build the package
pkg create -m "${MANIFESTDIR}" -r "${STAGEDIR}" -o .

echo "Build complete. Output should be: ${PKGNAME}-${VERSION}.txz"
