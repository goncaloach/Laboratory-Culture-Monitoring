@echo off
start "Sensor Data Server 1" /MIN mongod --config .\server1\server1.conf --bind_ip 127.0.0.1,192.168.1.238,172.25.60.36,10.62.13.43
start "Sensor Data Server 2" /MIN mongod --config .\server2\server2.conf --bind_ip 127.0.0.1,192.168.1.238,172.25.60.36,10.62.13.43
start "Sensor Data Server 3" /MIN mongod --config .\server3\server3.conf --bind_ip 127.0.0.1,192.168.1.238,172.25.60.36,10.62.13.43
