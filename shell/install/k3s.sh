#!/bin/sh

 
 yum update -y
 yum install -y epel-release
# https://www.linkedin.com/pulse/install-kubernetes-using-k3s-centos7-prayag-sangode

# centos 7 기준
curl -sfL https://get.k3s.io | sh -

