#! /bin/bash
echo "Reloading..."
pid=`pidof wmi_http_server`
echo "PID $pid"
kill -USR1 `pidof wmi_http_server`
kill  `pidof wmi_http_server`
echo "Reloaded"

# 分割日志
time=$(date +%Y-%m-%d)
mv data/log/app.log data/log/app-${time}.log
kill -SIGRTMIN `pidof wmi_http_server`