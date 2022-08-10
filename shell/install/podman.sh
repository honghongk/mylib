#!/bin/sh

yum install -y epel-release

# centos7에서 설치하면 버전 낮음 1.6.4 설치됨
# 2022. 2. 22. — Podman v4.0.0
yum install -y podman


# k3s 설치
# curl -sfL https://get.k3s.io | sh -
