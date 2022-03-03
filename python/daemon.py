import os
import sys
import time
import datetime
import re
import signal
import subprocess


# 아래는 subprocess 기반인듯
# 비동기
# import asyncio
# 멀티프로세싱
# import multiprocessing as mp



class schedule:
    def __init__(self, config):

        # 루프에서 적용
        if config['run']['interval'] <= 0:
            config['run']['interval'] = 1
        self._run = config['run']

        if not 'schedule' in config:
            raise Exception('실행할 것이 없음')

        self._schedule = {}
        self._process = {}

        for i in config['schedule']:
            self._insert_schedule(i, config['schedule'][i])

        # 시그널 설정
        schedule.signal()

    def __del__(self):
        for p in self._process:
            self._process[p]['proc'].terminate()
            if self._process[p]['proc'].poll() != None:
                del self._process[p]
            else:
                print('종료 제대로 안된 하위 프로세스 있을수있음')

    @staticmethod
    def signal_handler(signum,frame):
        '''
        시그널 핸들러
        :param signum: int 시그널 숫자
        :param frame: 백트레이스의 마지막으로 실행했던 코드 ????
        :return:
        '''
        global col
        col = {
            int(signal.SIGINT): 'Ctrl + C 키보드 인터럽트',
            int(signal.SIGBREAK): 'Ctrl + BREAK',
            int(signal.SIGTERM): '중지 신호 받음',
            #int(signal.SIGPIPE): '파이프 끊어짐', #문제생기면 먼저 끊어짐
            #int(signal.SIGHUP): '제어 프로세스/터미널 끊어짐'
        }
        msg = '시그널 받음' + "\n" + 'signum : ' + str(signum) + "\n"
        msg += '시그널 값 표시 : ' + col[signum] + "\n" \
            if signum in col else\
            '모르는 시그널 값 : ' + signum + "\n"

        raise Exception(msg)

    @staticmethod
    def signal():
        '''
        https://docs.python.org/ko/3.9/library/signal.html
        :return: None
        '''
        for x in dir(signal):
            if not x.startswith("SIG"):
                continue
            try:
                signum = getattr(signal, x)
                signal.signal(signum, schedule.signal_handler)
            except:
                # signal 등록에 실패하는 경우 표시
                #print('Skipping the signal : %s' % x)
                continue

    def _insert_schedule(self, key:str, schedule: dict):
        '''
        스케줄 확인 후 기본값입력, 코드로 치환
        :param schedule: dict 시간단위별 범위, 실행할 값 default self._default['time']
        :return:
        '''
        # 필수요소 확인
        if not 'process' in schedule:
            raise Exception('에러 서브프로세스 없음')

        # 기본값 세팅
        default = self._default['time']

        for d in default:
            if not d in schedule:
                schedule[d] = default[d]

        # 문자별로 요일 코드로 치환
        w = self._weekday
        for i in schedule['weekday']:
            schedule['weekday'][schedule['weekday'].index(i)] = \
                ''.join([str(w.index(s)) if s in w else s for s in i])
        # 범위구분패턴
        global pattern
        pattern = '~|-'

        for d in default:
            pop = []
            for i in schedule[d]:
                # 정규식은 str만 가능해서 형변환
                r = str(i)
                f = re.findall(pattern, r)
                if len(f) == 0:
                    continue
                # 기호대로 나눠서 리스트로 만들고 숫자로 형변환
                delimiter = '|'.join(f)
                r = list(filter(lambda x: x != '', re.split(delimiter, str(r))))
                r = [int(s.strip()) for s in r]
                # 마지막 수 포함을 위해 + 1
                r = range(r.pop(0), r.pop(0) + 1)
                r = [s for s in r]
                schedule[d].extend(r)
                pop.append(i)

            # 변환에 사용한 값은 제거
            for p in pop:
                schedule[d].pop(schedule[d].index(p))

            # 중목제거, 문자열 숫자로 변환, 정렬
            #print(schedule[d])
            schedule[d] = list(map(int,set(schedule[d])))
            schedule[d].sort()

        try:
            pythonpath = os.environ['PYTHONPATH']
        except:
            pythonpath = os.path.dirname(sys.argv[0])
            os.environ['PYTHONPATH'] = pythonpath
        if 'file' in schedule['process']:
            schedule['process']['file'] = os.path.join(
                pythonpath, schedule['process']['file']
            )

        # 뭔가 큰 기간별로 나눠서 저장하면 좋을듯 한데
        # 나중에
        self._schedule[key] = schedule


    @property
    def _default(self):
        return {
            'time':{
                'weekday': ['월 ~ 일'],
                'year': ['2020 ~ ' + str(datetime.datetime.now().year)],
                'month': ['1 ~ 12'],
                'day': ['1 ~ 31'],
                'hour': ['0 ~ 24'],
                'minute': ['0 ~ 59'],
                'second': ['0 ~ 59'],
            },
            'act':{
                'file': '',
                'function': ''
            }
        }


    @property
    def _weekday(self):
        return [
            '월',
            '화',
            '수',
            '목',
            '금',
            '토',
            '일'
        ]


    def run(self):
        '''
        설정대로 루프 실행
        :return: None
        '''
        default_time = list(self._default['time'].keys())
        default_act = list(self._default['act'].keys())
        schedule = self._schedule

        while True:
            # 루프 인터벌
            time.sleep(self._run['interval'])

            # 현재시간
            now = datetime.datetime.now()
            # 실행할 조건으로 거르기
            check = {}
            for k in schedule:
                check[k] = []
                for d in default_time:
                    check_time = getattr(now,d)
                    # 요일은 함수실행으로 얻어와야함
                    if d == 'weekday':
                        check_time = check_time()
                    # 조건 안맞을때
                    if check_time not in schedule[k][d]:
                        # 조건 안맞는데 실행되어있을때
                        if k in self._process:
                            # self._process 에서 찾아서 킬싸인 보내기
                            self._process[k]['proc'].terminate() # None
                            if self._process[k]['proc'].poll() != None:
                                self._process[k]['proc'].wait()
                                del self._process[k]
                        check[k].append(False)
                        break
                    check[k].append(True)

            # False 없는거 담기
            act = {}
            for i in check:
                if False in check[i]:
                    continue
                if not i in act:
                    act[i] = {}
                act[i]['process'] = schedule[i]['process']
                if 'dependency' in schedule[i]:
                    act[i]['dependency'] = schedule[i]['dependency']

            for a in act:
                # 의존성 확인
                if 'dependency' in act[a]:
                    dp = []
                    for p in act[a]['dependency']:
                        if not p in list(self._process.keys()):
                            dp.append(False)
                            break
                    if False in dp:
                        break

                for d in default_act:
                    if d not in act[a]['process']:
                        continue

                    # 이미 들어있으면 실행 스킵
                    if a in self._process:
                        # 종료된거 있으면 비우기
                        if self._process[a]['proc'].poll() != None:
                            print('종료된 프로세스 재시작')
                            wcheck = self._process[a]['proc'].wait()
                            del self._process[a]
                        continue
                    # 서브 프로세스 실행
                    proc = self.execute(d,act[a]['process'][d])
                    # 응답 받거나 하면 다음거 안함
                    self._process[a] = {
                        'proc':proc,
                        'pid':  proc.pid, #pid 값이 tasklist와 안맞음 32비트라서??
                        #'status': proc.poll(),
                        #'out': out,
                        #'error': err,
                        #'code': proc.wait()
                    }

    def execute(self, type:str, value:str):
        '''
            타입별로 다른 실행
            :param type: str
            :param value: str
            :return: 실행결과
        '''
        if type == 'file':
            return subprocess.Popen(
                ['python', value],
                shell=True, # 쉘 실행이면?? 필수
                universal_newlines=True, # 출력 깔끔하게
                env=os.environ.copy(), # 현재 환경변수대로 필수
                encoding='utf8', # 인코딩 필수?

                # 이거 없으면 죽이는 시그널 못받음
                # 이거 있으면 출력 안나옴
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                stdin=subprocess.PIPE
            )
        elif type == 'function':
            return getattr(sys.modules[__name__], value)
        else:
            raise Exception('모르는 프로세스타입')