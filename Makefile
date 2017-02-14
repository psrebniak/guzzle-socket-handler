start: stop-server
	php -S localhost:8080 tests/server.php &
	socat unix-listen:./socat.sock,reuseaddr,fork tcp-connect:127.0.0.1:8080 &

stop: stop-server stop-socket

stop-server:
	@PID_SERVER=$(shell ps axo pid,command \
	  | grep 'tests/server.php' \
	  | grep -v grep \
	  | cut -f 1 -d " "\
	) && [ -n "$$PID_SERVER" ] && kill $$PID_SERVER || true

stop-socket:
	@PID_SOCKET=$(shell ps axo pid,command \
	  | grep './socat.sock' \
	  | grep -v grep \
	  | cut -f 1 -d " "\
	) && [ -n "$$PID_SOCKET" ] && kill $$PID_SOCKET || true
	@rm -f socat.sock

test: start
	vendor/bin/phpunit --bootstrap tests/bootstrap.php  tests/Tests/
	$(MAKE) stop
