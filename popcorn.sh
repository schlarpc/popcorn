#!/usr/bin/env bash

# <config>

HTTP_PORT="8080"
POPCORN_PASSWORD="popcorn"

# </config>

SCRIPT=$(readlink -f "$0")
SCRIPTPATH=$(dirname "$SCRIPT")

RUNPARAMS="-v $SCRIPTPATH/www:/var/www -v $SCRIPTPATH/videos:/var/videos -p $HTTP_PORT:80 -p 1935:1935 -e POPCORN_PASSWORD=$POPCORN_PASSWORD"
echo $RUNPARAMS
mkdir -p videos

if [ "$1" = "start" ]; then
	sudo docker run $RUNPARAMS popcorn
elif [ "$1" = "shell" ]; then
	sudo docker run $RUNPARAMS -i -t --entrypoint="/bin/bash" popcorn -i
elif [ "$1" = "build" ]; then
	sudo docker build -t="popcorn" .
#elif [ "$1" = "clean" ]; then
#	docker stop $(docker ps -a -q)
#	docker rm $(docker ps -a -q)
#	docker rmi $(docker images -a -q)
else
	echo "Missing arg"
fi
