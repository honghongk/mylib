# 포드안에 컨테이너 넣으면서 만들기
> podman run -dt --pod new:nginx -p 1234:80 quay.io/libpod/alpine_nginx:latest

> podman run -dt --pod new:podset -p 1234:80 registry.centos.org/centos/httpd-24-centos7

> podman build -t apm centos7-apm

> podman run --log-level=debug -it -p 81:80 localhost/apm


# 빌드 다시하면서 남는 찌꺼기 삭제
> podman rmi $(podman images -f "dangling=true" -q)


# 쿠버네티스 yaml 파일로 만들기
> podman generate kube 컨테이너_아이디

# 디버스 서비스 파일로 만들기
> podman generate systemd 컨테이너_아이디


# 포드
https://atl.kr/dokuwiki/doku.php/podman_%EB%A1%9C%EC%BB%AC_%EC%BB%A8%ED%85%8C%EC%9D%B4%EB%84%88_%EB%9F%B0%ED%83%80%EC%9E%84%EC%97%90%EC%84%9C_%ED%8F%AC%EB%93%9C_%EB%B0%8F_%EC%BB%A8%ED%85%8C%EC%9D%B4%EB%84%88_%EA%B4%80%EB%A6%AC



같은 포드에 있으면 netstat에 나오는거 공유됨



# 포드에 여러 컨테이너 적용

podman pod create \
  --name my-pod \
  --publish 8080:80/TCP \
  --publish 8113:113/TCP

# Create a first container inside the pod
podman run --detach \
  --pod my-pod \
  --name cont1-name \
  --env MY_VAR="my val" \
  nginxdemos/hello

# Create a second container inside the pod
podman run --detach \
  --pod my-pod \
  --name cont2-name \
  --env MY_VAR="my val" \
  greboid/nullidentd

# Check by
$ podman container ls; podman pod ls