#!/bin/sh

INPUT="$1"
SS=0
if [ $# -eq 2 ]
    then
        SS=$2
fi

cd $(dirname $0)
killall ffmpeg
ffmpeg -re -ss $SS -i "$INPUT" -vcodec libx264 -preset superfast -b:v 2000k -vf "scale=1280:trunc(ow/a/2)*2" -acodec libfaac -aq 80 -ar 22050 -f flv rtmp://127.0.0.1/flvplayback/popcorn > /dev/null 2> /dev/null &
