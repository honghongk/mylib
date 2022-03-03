import socket
import time


class common:
    '''
    소켓 클라이언트와 서버 공통 메서드
    '''
    def __init__(self):
        self._socket = self.create()

    def __del__(self):
        self.close()

    @staticmethod
    def create():
        '''
        소켓 생성
        윈도우는 소켓을 file로 다룰수 없어서 무조껀 TCP생성인거로
        :return: socket
        '''
        return socket.socket(socket.AF_INET, socket.SOCK_STREAM)

    # 에러핸들링 병신같은데
    def error(self):
        return socket.error
    
    def close(self):
        '''
        소켓 해제
        :return:
        '''
        return self._socket.close()

    def receive(self,sock:socket):
        size = 1024
        res = ''
        received = 0
        while True:
            b = sock.recv(size)
            received += len(b)
            if b == b'':
                raise RuntimeError("socket connection broken")
            res += b.decode('utf8')
            if size > len(b) or received < size:
                break
        return res
    def send(self, sock:socket, data: str):
        return sock.sendall(data.encode('utf8'))

class client(common):
    def connect(self,host:str,port:int) -> None:
        '''
        :param host: str 연결할 서버
        :param port: int 포트
        :return: None
        '''
        if self._socket.fileno() == -1:
            self._socket = self.create()
        return self._socket.connect((host,port))

    def send(self, data: str):
        return super(client, self).send(self._socket,data)
    def receive(self):
        return super(client,self).receive(self._socket)
class request(client):
    def __init__(self,host,port):
        self._host = host
        self._port = port
        super(request, self).__init__()
    def get(self,data:str):
        '''
        요청 보내고 받기 닫기 한세트로
        :param data: str 보낼 데이터
        :return: str 받은 데이터
        '''
        retry = 2
        for i in range(retry):
            try:
                self.connect(self._host,self._port)
                self.send(data)
                r = self.receive()
                self.close()
                break
            except:
                print('리퀘스트 재시도')
                time.sleep(1)
                continue
        return r





'''
https://docs.python.org/3/library/socket.html#socket-objects
'''
class server(common):
    def __init__(self,config):
        '''
        서버 소켓 세팅
        :param config: dict 소켓 설정 값
            {
                timeout: int
                block: bool
                host: str
                port: int
            }
        '''
        super(server, self).__init__()

        self._socket.settimeout(config['timeout'])
        self._socket.setblocking(config['block'])
        self._socket.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
        #self._socket.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEPORT, 1)
        self._socket.bind((config['host'],config['port']))
        self._socket.listen()

    def accept(self):
        '''

        :return: tuple 연결된 클라이언트 소켓과 정보
            ( client_socket , ( ip, port ) )
        '''
        try:
            return self._socket.accept()
        except:
            return False

    def response(self,sock:socket,data:str):
        self.send(sock,data)
        sock.close()