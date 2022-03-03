import pprint
import json

from python import mysocket

pp = pprint.PrettyPrinter(indent=4)

request = mysocket.request('localhost', 12345)
res = request.get(json.dumps({
    'instance': 'math',
    'method': 'round',
    'args': [123.2345, 2]
}))
print('--------------------------------------------')
print('요청 응답받음')
pp.pprint(res)
print('--------------------------------------------')