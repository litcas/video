#!/bin/sh
PATH=/usr/local/php/bin:/opt/someApp/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
# 1.执行 php 命令不需要到thinkphp项目的目录下 2.index.php为入口文件 3.第三个参数为需要执行方法的路由
# 当天
cd /data/www/video/
#a=`ps -ef | grep pushData |grep name= | grep -v grep | awk '{print $2}'`
#if [ ! -n "$a" ]; then
##   echo 1
#  #空的
#  php think pushData name=i
#else
##  echo 1
#  # 不是空的 先不杀死
#  ps -ef | grep pushData |grep name=i | grep -v grep | awk '{print $2}' | xargs kill -9
#fi