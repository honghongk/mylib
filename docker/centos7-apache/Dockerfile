FROM registry.centos.org/centos/centos

# 기본설치
RUN yum update -y
RUN yum install -y vim git wget gzip net-tools



RUN yum install -y httpd

COPY httpd.conf /etc/httpd/conf/httpd.conf

EXPOSE 80

ENTRYPOINT httpd -D FOREGROUND