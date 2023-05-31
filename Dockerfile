#Lambda base image Amazon linux
FROM public.ecr.aws/lambda/provided
# Set desired PHP Version
ARG php_version="8.2.2"
RUN yum clean all && \
    yum install -y autoconf \
                bison \
                bzip2-devel \
                gcc \
                gcc-c++ \
                git \
                gzip \
                libcurl-devel \
                libxml2-devel \
                libzip-devel \
                make \
                openssl-devel \
                tar \
                re2c \ 
                sqlite-devel \
                zip \
                unzip \
                php-zip 

# Download the PHP source, compile, and install both PHP and Composer
RUN curl -sL https://github.com/php/php-src/archive/refs/tags/php-${php_version}.tar.gz | tar -xvz && \
    cd php-src-php-${php_version} && \
    ./buildconf --force && \
    ./configure --with-openssl \ 
        --with-onig=/usr/lib64 \ 
        --with-curl \ 
        --with-zlib \ 
        --without-pear \
        --with-libzip \ 
        --enable-bcmath \ 
        --enable-zip \ 
        --with-bz2 \ 
        --with-mysqli && \
    make -j 5 && \
    make install

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer


COPY runtime/bootstrap /var/runtime/
COPY src/ /var/task/




# # # Layer 1: PHP Binaries
WORKDIR /var/task/
RUN composer install --no-interaction -vvv


CMD [ "index" ]