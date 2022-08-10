

# -------------- 삭제
podman pod stop test-apm
podman rm $(podman ps -aq)
podman pod rm test-apm
podman volume rm test-apm-volume


# -------------- 생성
podman pod create --name test-apm --publish 1234:80/TCP


# 어차피 절대경로로 해서 디렉토리 적당히 맞추면 필요없음
# persistent로 하려면 마운트 필수
podman volume create test-apm-volume


# 볼륨마운트 절대경로로 합쳐야함
vpath=`podman volume inspect test-apm-volume|grep Mountpoint |cut -d'"' -f4`

# php-fpm 으로 코드 넘기는게 아니라서 경로 공유되어야 함
podman run -v $vpath:/var/www/html --detach --pod test-apm --name php-fpm localhost/php
podman run -v $vpath:/var/www/html --detach --pod test-apm --name apache localhost/web
podman run --detach --pod test-apm --name mariadb localhost/db
