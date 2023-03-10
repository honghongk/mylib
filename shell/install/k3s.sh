#!/bin/sh


# centos 7 기준
 
 yum update -y
 yum install -y epel-release
# https://www.linkedin.com/pulse/install-kubernetes-using-k3s-centos7-prayag-sangode

curl -sfL https://get.k3s.io | sh -

# 도커 설치
yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
yum -y install docker-ce
