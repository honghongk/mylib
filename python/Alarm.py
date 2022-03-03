from datetime import datetime
import requests

from python import Exception


if __name__ == '__main__' :
    raise Exception.Error(__file__+'은 단독으로 사용할 수 없습니다')

class slacker:
    def __init__(self,config):
        self._config = config
        self._queue = []
    def message(self,msg):
        msg = datetime.now().strftime('[%m/%d %H:%M:%S] ') + "\n" + msg
        return requests.post(
            self._config['url'],
             headers={
                 "Authorization": "Bearer " + self._config['token']
             },
             data={
                 "channel": self._config['channel'],
                 "text": msg
             }
         )
    def queue(self,msg):
        self._queue.insert(0,msg)

    def queue_send(self):
        while True:
            if len(self._queue) <= 0:
                break
            self.message(self._queue.pop())

'''
class Telegram:
    def __init__(self,config):
        self._config = config
    def message(self,msg):
        msg = datetime.now().strftime('[%m/%d %H:%M:%S] ') + "\n" + msg
        return requests.post(
            self._config['url'],
            headers={
                "Authorization": "Bearer " + self._config['token']
            },
            data={
                "channel": self._config['channel'],
                "text": msg
            }
        )
'''
