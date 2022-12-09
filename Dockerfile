FROM centos:7
ENV LANG ja_JP.UTF-8
RUN set -x && \
    yum -y update && \
    yum clean all && \
    yum reinstall -y glibc-common && \
    yum clean all && \
    localedef -f UTF-8 -i ja_JP ja_JP.UTF-8 && \
    sed -i 's/^LANG="[^"]*"$/LANG="ja_JP.UTF-8"/' /etc/locale.conf && \
    sed -i -e '/override_install_langs/s/$/,ja_JP.utf8/g' /etc/yum.conf && \
    rm -f /etc/localtime && ln -s /usr/share/zoneinfo/Asia/Tokyo /etc/localtime && \
    yum -y install epel-release && \
    yum -y install vim lsof tcpdump tmux wget curl zip unzip gzip
RUN yum -y install http://rpms.famillecollet.com/enterprise/remi-release-7.rpm && \
    yum -y install --enablerepo=remi,remi-php73 php php-mbstring php-xml php-xmlrpc php-gd php-pdo php-pecl-mcrypt php-mysqlnd php-pecl-mysql httpd
COPY httpd.conf /etc/httpd/conf/httpd.conf
RUN cd /tmp && \
    mkdir CakePHP && \
    cd CakePHP && \
    wget https://github.com/cakephp/cakephp/archive/2.10.19.tar.gz && \
    tar zxf 2.10.19.tar.gz && \
    mv cakephp-2.10.19 cake && \
    mv cake /var/www/ && \
    rm -f 2.10.19.tar.gz
RUN cd /tmp && \
    mkdir iroha && \
    cd iroha && \
    wget https://github.com/insyo/irohaboard/archive/refs/heads/master.zip && \
    unzip master.zip && \
    cd irohaboard-master && \
    cp -pr ./* /var/www/html && \
    cp -pr .htaccess /var/www/html/ && \
    cd .. && \
    rm -rf irohaboard-0.10.9.1.2 v0.10.9.1.2.tar.gz && \
    chown -R apache: /var/www/ && \
    cd /var/www/html/Config/ && \
    sed -i -e "s/'password' => '',/'password' => 'P@ssword+1',/" database.php && \
    rm -rf /var/cache/yum/* && \
    yum clean all
COPY database.php /var/www/html/Config
