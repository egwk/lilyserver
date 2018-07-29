FROM php:latest

LABEL maintainer="Peter Erdodi <peter@white-konyvtar.hu>"

WORKDIR /code

EXPOSE 8000

RUN curl  -O http://download.linuxaudio.org/lilypond/binaries/linux-64/lilypond-2.19.80-1.linux-64.sh && \
    chmod +x lilypond-2.19.80-1.linux-64.sh && \
    true | ./lilypond-2.19.80-1.linux-64.sh

RUN apt-get update && \
	apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

CMD [ "php", "-S", "lily:8000", "-t", "public"]
