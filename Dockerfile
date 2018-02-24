FROM wumvi/php.base
MAINTAINER Vitaliy Kozlenko <vk@wumvi.com>

ENV GOROOT="/usr/local/go"
ENV PATH="$GOPATH/bin:$GOROOT/bin:$PATH"

ADD cmd/  /
ADD cnv/  /cnv/

RUN DEBIAN_FRONTEND=noninteractive && \
	apt-get -qq update && \
	apt-get --no-install-recommends -qq -y install gnupg sox wget curl lsb-release imagemagick cmake ca-certificates libjpeg-dev libpng-dev libtiff-dev libgif-dev git autoconf automake libtool  m4 nasm pkg-config build-essential unzip zip && \
	curl -sL https://deb.nodesource.com/setup_9.x | bash - && \
    apt-get --no-install-recommends -qq -y install nodejs && \
	mkdir -p /soft/ && \
	# WebP File
	cd /soft/ && \
	wget https://storage.googleapis.com/downloads.webmproject.org/releases/webp/libwebp-0.6.0.tar.gz -O libwebp.tar.gz && \
	tar -zxf libwebp.tar.gz && \
	cd libwebp-0.6.0 && \
	./configure --prefix=/usr/ && \
	make && \
	make install && \
	# MozJpeg
	cd /soft/ && \
	ldconfig /usr/lib && \
	wget https://github.com/mozilla/mozjpeg/archive/master.zip -O mozjpeg.zip  && \
	unzip mozjpeg.zip && \
	cd mozjpeg-master && \
	autoreconf -fiv && \
	mkdir build && cd build && \
	sh ../configure --prefix=/usr/ --disable-shared --enable-static && \
	make install && \
	# Svg preview
	cd /soft/ && \
	wget https://storage.googleapis.com/golang/go1.8.3.linux-amd64.tar.gz && \
	tar -xvf go1.8.3.linux-amd64.tar.gz && \
	mv go /usr/local && \
	go get -u github.com/fogleman/primitive && \
	# ln -s /root/go/bin/primitive /usr/local/bin/primitive && \
	mv /root/go/bin/primitive /usr/sbin/ && \
	npm install -g sqip && \
	#
	cd /cnv/ && \
	composer install --no-interaction --no-dev --no-progress --no-suggest --optimize-autoloader --prefer-dist --ignore-platform-reqs --no-plugins && \
	#
	chmod +x /*.sh && \
	#
	apt-get remove -y wget lsb-release cmake libjpeg-dev libpng-dev unzip libtiff-dev libgif-dev autoconf automake libtool m4 nasm pkg-config build-essential && \
	apt-get -y autoremove && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* && \
    rm -rf /soft/ && \
	#
	echo 'done'

WORKDIR /data/
