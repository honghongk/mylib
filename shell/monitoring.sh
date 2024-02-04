#!/bin/bash


# 상태 모니터링
# 로드에버리지, 좀비 프로세스, 용량, 메모리, 디스크 io

# iostat
# https://blog.naver.com/hanajava/221397600043

# 경로 세팅
__FILE__=`realpath $0`
__DIR__=`dirname $__FILE__`
__LIB__=${__DIR__}/lib

# 사용할 함수 로드
source ${__LIB__}/array.sh
source ${__LIB__}/time.sh
source ${__LIB__}/sys.sh


# 중복 실행 막기
run=`ps -ef | grep $__FILE__ | grep -v grep | wc -l`
if [[ $run -gt 1 ]]; then
    ps -ef | grep $__FILE__ | grep -v grep
    ps -ef | grep $__FILE__ | grep -v grep | wc -l
    exit
fi


# 로드에버리지 70% 넘으면 알림
std_load=70

# 디스크 사용량 70% 넘으면 알림
std_disk=70

# 메모리 사용량 70% 넘으면 알림
std_mem=70

# fin_wait 10개 넘으면 알림
std_fin_wait=10

# 프로세스당 사용량 10% 넘으면 알림
std_ps_cpu=10
std_ps_mem=10

# iostat *_await 10넘으면 알림
std_iostat=10


# ps 값과 비율 맞추기
std_ps_cpu=`echo "$std_ps_cpu * 10" | bc`
std_ps_mem=`echo "$std_ps_mem * 10" | bc`

while true; do

    # 0.1초 마다
    sleep 1

    # ----------------------------------------------------------------------
    # 로드 에버리지 계산
    div=`count_cpu`
    load=(`load_average`);

    # 로드 에버리지 * 100 / cpu
    rate=`echo "${load[0]} * 100 / $div > $std_load" | bc`
    if [[ $rate -eq 1 ]]; then
        echo loadavg warning
        echo ${load[0]}
        echo ${load[3]}
    fi

    # ----------------------------------------------------------------------
    # 좀비 프로세스 확인
    if [[ `zombie | wc -l` -ne 0 ]]; then
        echo zombie warning
        zombie_kill
    fi

    # ----------------------------------------------------------------------
    # 개별 프로세스

    ps -aux --sort -pcpu | awk '{print $3 * 10, $4 * 10}' | while read ps; do
        ps=($ps)
        if [[ ${ps[0]} -ge $std_ps_cpu ]]; then
            echo over ps cpu
        fi
        if [[ ${ps[1]} -ge $std_ps_mem ]]; then
            echo over ps mem
        fi
    done

    # ----------------------------------------------------------------------

    # 네트워크 
    fin=`netstat -atup | grep FIN_WAIT | wc -l`
    if [[ $fin -ge $std_fin_wait ]]; then
        echo over net fin
        netstat -atup | grep FIN_WAIT
    fi

    # ----------------------------------------------------------------------
    # 메모리 사용량 확인 total, available
    # free -bw | grep ^Mem | awk ' { print $2 , $4 , $8 }'
    mem=(`free -bw | grep ^Mem | awk ' { print $2 , $8 }'`)

    # 사용가능한 비율
    per=`echo "${mem[1]} * 100 / ${mem[0]}" | bc`

    # 사용 가능한 비율 30 아래면 알림
    mem_std=`echo "100 - $std_mem" | bc`
    if [[ $mem_std -ge $per ]]; then
        echo over mem
    fi

    # ----------------------------------------------------------------------

    # 확인 대상 디스크
    disk=`active_disk`
    for d in $disk; do

        # 디스크 사용량 % 추출
        vol=`df -h | grep ^$d[[:space:]] | awk '{print int ( $5 ) }'`
        i=`df -i | grep ^$d[[:space:]] | awk '{print int ( $5 ) }'`

        # io 데이터 추출
        #  r/s     rMB/s   rrqm/s  %rrqm r_await rareq-sz     w/s     wMB/s   wrqm/s  %wrqm w_await wareq-sz     d/s     dMB/s   drqm/s  %drqm d_await dareq-sz     f/s f_await  aqu-sz  %util
        dev=`echo $d | cut -d/ -f3`
        # dev r_await w_await f_await d_await
        stat=`iostat -dxm -p $dev | grep $dev | awk '{print $6, $12, $18, $21}'`

        for s in $stat; do
            # 소수점 버리고 확인
            s=`echo "4.21 / 1" | bc`
            if [[ $s -ge $std_iostat ]]; then
                echo over io await
            fi
        done


        # 사용량 없으면 스킵
        if [[ $vol -eq '' || $i -eq '' ]]; then
            echo continue $d
            continue
        fi

        # 디스크 용량 기준 이상이면 알림
        if [[ $vol -ge $std_disk ]]; then
            echo over vol
        fi

        # inode 수 기준 이상이면 알림
        if [[ $i -ge $std_disk ]]; then
            echo over inode
        fi
    done
done
