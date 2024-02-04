#!/bin/bash

###################
# 함수 있는지 확인
###################
function_exists()
{
    if [[ `type $1|grep "is a function"|wc -l` -gt 0 ]];then
        return 0;
    else
        return 1;
    fi
}

###################
# cpu 수 반환
# https://zetawiki.com/wiki/%EB%A6%AC%EB%88%85%EC%8A%A4_CPU_%EA%B0%9C%EC%88%98_%ED%99%95%EC%9D%B8
# count_cpu()
#   - echo: 숫자
###################
count_cpu()
{
    # cpu 코어 전체
    grep -c processor /proc/cpuinfo

    # 물리적 cpu 수
    # grep "physical id" /proc/cpuinfo | sort -u | wc -l
}


###################
# 로드 에버리지 얻기
# 1분 평균, 5분 평균, 15분 평균, 큐 상태, pid
# https://zetawiki.com/wiki/%EB%A1%9C%EB%93%9C_%EC%97%90%EB%B2%84%EB%A6%AC%EC%A7%80
###################
load_average()
{
    cat /proc/loadavg
}

###################
# 좀비프로세스 얻기
# https://zetawiki.com/wiki/%EC%A2%80%EB%B9%84_%ED%94%84%EB%A1%9C%EC%84%B8%EC%8A%A4_%EC%B0%BE%EA%B8%B0,_%EC%A3%BD%EC%9D%B4%EA%B8%B0
###################
zombie()
{
    ps -ef | grep defunct | grep -v grep
}

###################
# 좀비프로세스 죽이기
# https://zetawiki.com/wiki/%EC%A2%80%EB%B9%84_%ED%94%84%EB%A1%9C%EC%84%B8%EC%8A%A4_%EC%B0%BE%EA%B8%B0,_%EC%A3%BD%EC%9D%B4%EA%B8%B0
#
###################
zombie_kill()
{
    zombie | awk '{print $3}' | xargs kill -9
}


###################
# 마운트된 디스크 찾기
# 파일시스템으로 사용하는 것만 추출
###################
active_disk()
{
    cat /proc/mounts | awk '$3~/ext[0-9]|vfat|xfs/ && $2 != "/boot/efi" {print $1}'
}
