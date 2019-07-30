#! /bin/bash
echo "Reloading..."
pid=`pidof wmi_http_server`
echo "PID $pid"
kill -USR1 `pidof wmi_http_server`
kill  `pidof wmi_http_server`
echo "Reloaded"
