FROM registry.centos.org/centos/centos

# 기본설치
RUN yum update -y
RUN yum install -y vim git wget gzip net-tools


RUN yum install -y epel-release
# 왠지 따로 안깔아도됨 
# RUN yum install -y yum-config-manager

RUN yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm
RUN yum-config-manager --disable remi-php54
RUN yum-config-manager --enable remi-php72

RUN yum install -y php php-mysqlnd php-pdo php-json php-pdo php-xml php-zip php-fpm


COPY php.ini /etc/php.ini
COPY php-fpm.conf /etc/php-fpm.conf


EXPOSE 9000


ENTRYPOINT php-fpm -R -F -O