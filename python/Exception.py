class Error:
    def __init__(Exception, msg):
        exit('에러 : ' + msg)


if __name__ == '__main__' :
    raise Error(__file__ + '은 단독으로 사용할 수 없습니다')