# Popcorn - A video stream/sync service

FROM ubuntu:14.04
MAINTAINER Chaz Schlarp <schlarpc@gmail.com>

# Install prerequisite packages

RUN echo "deb http://archive.ubuntu.com/ubuntu trusty main restricted universe multiverse" > /etc/apt/sources.list
RUN apt-get update && \
    apt-get install -f -y \
        apache2 \
        php5 \
        php5-curl \
        libapache2-mod-php5 \
        wget \
        git \
        subversion \
        nasm \
        build-essential \
        pkg-config \
        libfaac-dev \
        libmp3lame-dev \
        libtheora-dev \
        libvorbis-dev \
        libopencore-amrnb-dev \
        libopencore-amrwb-dev \
        libgsm1-dev \
        zlib1g-dev \
        libgpac-dev \
        supervisor \
        psmisc

# Compile dependencies for ffmpeg

# ... yasm

RUN wget -O /tmp/yasm.tar.gz http://www.tortall.net/projects/yasm/releases/yasm-1.2.0.tar.gz 
RUN mkdir /tmp/yasm && \
    tar xfz /tmp/yasm.tar.gz -C /tmp/yasm --strip-components 1
RUN cd /tmp/yasm && \
    ./configure --prefix=/usr/local && \
    make && \
    make install

# ... x264

RUN git clone git://git.videolan.org/x264.git /tmp/x264
RUN cd /tmp/x264 && \
    ./configure --prefix=/usr/local --enable-shared && \
    make && \
    make install

# ... xvidcore

RUN wget -O /tmp/xvidcore.tar.gz http://downloads.xvid.org/downloads/xvidcore-1.3.3.tar.gz 
RUN mkdir /tmp/xvidcore && \
    tar xfz /tmp/xvidcore.tar.gz -C /tmp/xvidcore --strip-components 1
RUN cd /tmp/xvidcore/build/generic && \
    ./configure --prefix=/usr/local && \
    make && \
    make install

# And finally ffmpeg itself

RUN git clone git://source.ffmpeg.org/ffmpeg.git /tmp/ffmpeg
RUN cd /tmp/ffmpeg && \
    ./configure --prefix=/usr/local --enable-gpl --enable-version3 --enable-nonfree --enable-shared --enable-libopencore-amrnb --enable-libopencore-amrwb --enable-libfaac --enable-libgsm --enable-libmp3lame --enable-libtheora --enable-libvorbis --enable-libx264 --enable-libxvid && \
    make && \
    make install && \
    ldconfig

# Download and extract the rtmp server

RUN wget -O /tmp/rtmpd.tar.gz http://www.rtmpd.com/assets/binaries/784/crtmpserver-1.1_beta-x86_64-Ubuntu_12.04.tar.gz
RUN mkdir /opt/rtmpd && \
    tar xfz /tmp/rtmpd.tar.gz -C /opt/rtmpd --strip-components 1
ENV PATH /opt/rtmpd:$PATH

# Install youtube-dl

RUN mkdir /opt/youtube-dl && \
    wget --no-check-certificate -O /opt/youtube-dl/youtube-dl https://yt-dl.org/downloads/2014.05.05/youtube-dl && \
    chmod a+x /opt/youtube-dl/youtube-dl
ENV PATH /opt/youtube-dl:$PATH

# Cleanup

RUN rm -rf /tmp/*

# Add config files

ADD build/popcorn.lua /opt/rtmpd/configs/popcorn.lua
ADD build/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose necessary ports and run via supervisord

EXPOSE 80 1935
ENTRYPOINT ["/usr/bin/supervisord"]

