start: stop
	php -S localhost:8081 tests/server.php &
	sleep 1
	socat unix-listen:./socat.sock,reuseaddr,fork tcp-connect:127.0.0.1:8081 &
	sleep 1

stop: stop-server stop-socket

stop-server:
	@PID_SERVER=$$(ps axo pid,command \
	  | grep 'tests/server.php' \
	  | grep -v grep \
	  | awk '{print $$1}' \
	) && [ -n "$$PID_SERVER" ] && kill $$PID_SERVER || true

stop-socket:
	@PID_SOCKET=$$(ps axo pid,command \
	  | grep './socat.sock' \
	  | grep -v grep \
	  | awk '{print $$1}' \
	) && [ -n "$$PID_SOCKET" ] && kill $$PID_SOCKET || true
	@rm -f socat.sock

test: start
	vendor/bin/phpunit --bootstrap tests/bootstrap.php  tests/Tests/
	$(MAKE) stop
