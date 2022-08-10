FROM registry.centos.org/centos/centos

# 기본설치
RUN yum update -y
RUN yum install vim git wget gzip net-tools -y

# 쉘처럼 띄어쓰기 하면안됨
# 10버전대 상세버전 5는 디폴트
# 빌드시 podman build -t $IMAGE_NAME . --build-arg version=9
ARG version=5

# https://mariadb.com/kb/en/mariadb-package-repository-setup-and-usage/#mariadb-repository
# 레포지토리 추가 10.2 ~ 10.9
# 10.9 버전은 패키지 의존성 에러
RUN curl -LsS https://r.mariadb.com/downloads/mariadb_repo_setup | bash -s -- --mariadb-server-version="mariadb-10.$version"

# 마리아디비 설치
RUN yum install Mariadb-server MariaDB -y

COPY my.cnf /etc/my.cnf

EXPOSE 3306

ENTRYPOINT mysqld --user=root

# RUN으로 하면 에러
# 서버 실행
# 여러줄 하려면 역슬래시 붙여야함 쉼표도 알맞게 붙여야함
# ENTRYPOINT [ \
#     "mysqld", "--no-defaults", "--user=root", \
#     "--max-connections=1000", \
#     "--max-allowed-packet=4M", \
#     "--connect-timeout=5", \
#     "--wait-timeout=10", \
#     "--tcp-keepalive-time=10", \
#     "--default-time-zone=+9:00" \
# ]
# "--default-time-zone='Asia/Seoul'" \
