PACKAGE=        speedtest
VERSION=        1.0.0
FILES=          diag_speedtest.php \
                diag_speedtest_run.php \
                diag_speedtest_servers.php \
                diag_speedtest_cities.php \
                diag_speedtest_history.php \
                graph_speedtest.php \
                css/speedtest.css \
                pkg-install \
                pkg-deinstall \
                speedtest.xml

all:
	@echo "Run 'make install', 'make package' or 'make clean'"

install:
	@echo "Installing package..."
	@mkdir -p /usr/local/www/css
	@cp diag_speedtest.php /usr/local/www/
	@cp diag_speedtest_run.php /usr/local/www/
	@cp diag_speedtest_servers.php /usr/local/www/
	@cp diag_speedtest_cities.php /usr/local/www/
	@cp diag_speedtest_history.php /usr/local/www/
	@cp graph_speedtest.php /usr/local/www/
	@cp css/speedtest.css /usr/local/www/css/
	@cp pkg-install /usr/local/pkg/
	@cp pkg-deinstall /usr/local/pkg/
	@cp speedtest.xml /usr/local/pkg/
	@cp info.xml /usr/local/pkg/
	@echo "Done."

package:
	@echo "Creating pfSense package..."
	@mkdir -p work/pkg
	@cp -R * work/pkg/
	@cd work && tar czf pfsense-pkg-${PACKAGE}-${VERSION}.txz pkg
	@echo "Package created: work/pfsense-pkg-${PACKAGE}-${VERSION}.txz"

clean:
	@echo "Cleaning package..."
	@rm -f /usr/local/www/diag_speedtest*
	@rm -f /usr/local/www/graph_speedtest.php
	@rm -f /usr/local/www/css/speedtest.css
	@rm -f /usr/local/pkg/pkg-install
	@rm -f /usr/local/pkg/pkg-deinstall
	@rm -f /usr/local/pkg/speedtest.xml
	@rm -f /usr/local/pkg/info.xml
	@rm -f /tmp/pkg_config.*
	@rm -f /tmp/menu_cache.*
	@touch /tmp/reload_packages
	@touch /tmp/reload_menu
	/etc/rc.php-fpm_restart
	/etc/rc.restart_webgui
	@echo "Done."
