#!/bin/bash

# https://stackoverflow.com/questions/31712266/how-to-clean-up-docker-overlay-directory

# 꺼진 컨테이너, none 상태 지우기
docker rm -v $(docker ps -a -q -f status=exited)
docker rmi -f  $(docker images -f "dangling=true" -q)
docker volume ls -qf dangling=true | xargs -r docker volume rm
