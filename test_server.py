import pprint
import time
import math
import json

from python import mysocket

pp = pprint.PrettyPrinter(indent=4)

# 소켓 서버 세팅
server = mysocket.server({
    'timeout': 1,
    'block': True,
    'host': 'localhost',
    'port': 12345
})
# 파라미터 확인용
param_type = {
    'instance': str,
    'property': str,
    'method': str,
    'function': str,
    'args': list,
    'kwargs': dict,
}
# 인스턴스 세팅
instance = {'math': math}
while True:
    time.sleep(0.3)

    client = server.accept()
    if client == None:
        print('타임아웃후 다음')
        continue
    r = server.receive(client[0])
    try:
        r = json.loads(r)
    except:
        print('json 디코드 실패')
        server.response(client[0], '디코드실패')
        continue

    '''
        {
            instance: str instance key,
            property: str property name,
            method: str method name,
            function: str function name,
            args: list [] optional
            kwargs: dict {} optional
        }
    '''
    param_check = []
    for k, v in list(param_type.items()):
        if k in r:
            param_check.append(isinstance(r[k], v))
    if False in param_check:
        print(r)
        server.response(client[0], json.dumps('파라미터 타입에러'))
        continue

    # 예외처리 기본값 세팅
    if not 'args' in r:
        r['args'] = []
    if not 'kwargs' in r:
        r['kwargs'] = {}

    print(r)
    try:
        res = None
        if 'instance' in r and 'property' in r:
            res = getattr(instance[r['instance']], r['property'])
        elif 'instance' in r and 'method' in r:
            res = getattr(instance[r['instance']], r['method'])(*r['args'], **r['kwargs'])
        elif 'function' in r:
            res = globals()[r['function']](*r['args'], **r['kwargs'])
        else:
            print('파라미터 처리 에러')
            server.response(client[0], json.dumps('error'))
            continue
    except:
        print('함수 처리 에러')
        server.response(client[0], json.dumps('error'))
        continue

    server.response(client[0], str(json.dumps(res)))
    print('서버 종료')
    exit()