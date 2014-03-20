# Popcorn

Popcorn is a video streaming and synchronization service. It's similar in
concept to Synchtube, but is designed for local content. Popcorn is built
on top of crtmpserver and ffmpeg, and is deployed using Docker.

## Usage

`popcorn.sh` is a shell script designed to operate the Docker image for
Popcorn. `popcorn.sh build` will populate the Docker image `popcorn`
with the required services. `popcorn.sh start` starts the server instances.
Additionally, `popcorn.sh shell` will drop you into a shell.

Video files should be placed into the `videos` directory.

## Configuration

TODO: document config of popcorn.sh and PHP stuff

