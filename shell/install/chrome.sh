#!/bin/sh


#######  centos 7  ######

# 레포 안될때
# wget https://dl.google.com/linux/direct/google-chrome-stable_current_x86_64.rpm

# 브라우저 설치
cat << EOF > /etc/yum.repos.d/google-chrome.repo
[google-chrome]
name=google-chrome
baseurl=http://dl.google.com/linux/chrome/rpm/stable/$basearch
enabled=1
gpgcheck=1
gpgkey=https://dl.google.com/linux/linux_signing_key.pub
EOF

yum install google-chrome-stable -y



version=`google-chrome-stable --version |cut -d' ' -f3`



# 브라우저와 버전 맞아야함
wget https://chromedriver.storage.googleapis.com/$version/chromedriver_linux64.zip

unzip chromedriver_linux64.zip

rm chromedriver_linux64.zip

mv chromedriver /usr/local/bin
