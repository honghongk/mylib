FROM registry.centos.org/centos/centos

# 기본설치
RUN yum update -y
RUN yum install vim git wget gzip net-tools -y

RUN yum install -y epel-release 
RUN yum install https://rpms.remirepo.net/enterprise/remi-release-7.rpm -y
# yum install yum-config-manager
RUN yum-config-manager --disable remi-php54
RUN yum-config-manager --enable remi-php72
RUN yum install php php-mysqlnd php-pdo php-json php-pdo php-xml php-zip httpd -y

# 쉘처럼 띄어쓰기 하면안됨
# 10버전대 상세버전 5는 디폴트
# 빌드시 podman build -t $IMAGE_NAME . --build-arg version=9
# ARG version=5

# https://mariadb.com/kb/en/mariadb-package-repository-setup-and-usage/#mariadb-repository
# 레포지토리 추가 10.2 ~ 10.9
# 10.9 버전은 패키지 의존성 에러
RUN curl -LsS https://r.mariadb.com/downloads/mariadb_repo_setup | bash -s -- --mariadb-server-version="mariadb-10.5"

# 마리아디비 설치
RUN yum install Mariadb-server MariaDB -y

COPY my.cnf /etc/my.cnf
COPY httpd.conf /etc/httpd/conf/httpd.conf

COPY run.sh run.sh
RUN chmod 777 run.sh

EXPOSE 80


# RUN으로 하면 에러
# 서버 실행
# 여러줄 하려면 역슬래시 붙여야함 쉼표도 알맞게 붙여야함
# ENTRYPOINT [ "/bin/bash", "-c", "./run.sh" ]
# CMD [ "/bin/bash", "-c", "./run.sh" ]

# 중단되면 컨테이너 중단됨
ENTRYPOINT /run.sh