#!/usr/bin/env bash

HTTP_PORT=8080

SCRIPT=$(readlink -f "$0")
SCRIPTPATH=$(dirname "$SCRIPT")

MOUNTS="-v $SCRIPTPATH/www:/var/www -v $SCRIPTPATH/videos:/var/videos -p $HTTP_PORT:80 -p 1935:1935"

mkdir -p videos

if [ "$1" = "start" ]; then
	sudo docker run $MOUNTS popcorn
elif [ "$1" = "shell" ]; then
	sudo docker run $MOUNTS -i -t --entrypoint="/bin/bash" popcorn -i
elif [ "$1" = "build" ]; then
	sudo docker build -t="popcorn" .
#elif [ "$1" = "clean" ]; then
#	docker stop $(docker ps -a -q)
#	docker rm $(docker ps -a -q)
#	docker rmi $(docker images -a -q)
else
	echo "Missing arg"
fi
