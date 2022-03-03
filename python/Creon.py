# import  sys , os
import ctypes
import win32com.client
import time
from datetime import datetime
import pprint

import math
import pandas

from python import Exception




if __name__ == '__main__' :
    raise Exception.Error(__file__+'은 단독으로 사용할 수 없습니다')

class connect:
    def __init__(self):
        if not ctypes.windll.shell32.IsUserAnAdmin():
            raise Exception.Error('관리자 권한 아님')
        self._connect = win32com.client.Dispatch('CpUtil.CpCybos')

    @property
    def status(self) -> bool:
        '''
        연결상태
        :return: bool
        '''
        return self._connect.IsConnect == 0


'''
주식 정보 얻기, 매수, 매도 등
'''
class stock(connect):
    _object = {}
    _trade = None
    _stock = None
    _connect = None

    '''
        오브젝트 초기화, 세팅
    '''
    def __init__(self):
        super(stock, self).__init__()

        self._trade = win32com.client.Dispatch('CpTrade.CpTdUtil')
        self._stock = win32com.client.Dispatch('CpUtil.CpCodeMgr')

        self.tradeInit()
        #if self.status != True :
        #    raise Exception.Error('연결안됨')

    '''
        트레이드 오브젝트 초기화
        거래마다 초기화 해줘야함
    '''
    def tradeInit(self):
        if self._trade.TradeInit(0) != 0:
            raise Exception.Error('트레이드 초기화 실패')


    def list(self):
        '''
            종목코드 리스트
        '''
        res = {}
        # 거래소
        codeList = self._stock.GetStockListByMarket(1)
        for i, code in enumerate(codeList):
            res[code] = self._stock.CodeToName(code)

        # 코스닥
        codeList = self._stock.GetStockListByMarket(2)
        for i, code in enumerate(codeList):
            res[code] = self._stock.CodeToName(code)

        return res



    '''
        가격 이동 평균
        price moving average
        
        수정 필요
        NaN 너무 많이 나오는듯
        
        일단 틱 빼고 가능
        
        1분봉 ma5 ma20 1개씩 밀림
    '''
    def MA(self,code,q,window,**kwargs):
        ohlc = self.OHLC(code, q, **kwargs)

        close = {'종가':{}}
        for date in ohlc:
            for c in ohlc[date]:

                # 분봉 한개씩 밀림 @@@@@@@@
                t = str(c['시간'] - 1)
                t = '0' + t if len(t) == 3 else t
                if t != '0':
                    close['종가'][str(date) + t] = c['종가']
                else:
                    close['종가'][str(date)] = c['종가']

        # 순서안바뀌어야함
        data = pandas.DataFrame.from_dict(close).rolling(window=window).mean().to_dict()
        res = {'종가':{}}
        for i in data['종가']:
            if data['종가'][i] != data['종가'][i]:
                continue
            res['종가'][i] = data['종가'][i]
        return res

    def finance(self, code):
        '''

        :param string|list code: 종목코드
        :return:
        '''
        if type(code) == str:
            code = [code]


        global col
        col = {
            0: '종목코드',
            20: '총주식수',
            67: 'PER', # 주가수익비율
            70: 'EPS', # 주당 순이익
            71: '자본금',
            74: '배당수익률',
            75: '부채비율',
            76: '유보율',
            77: '자기자본이익률',
            78: '매출액증가율',
            79: '경상이익증가율',
            80: '순이익증가율',
            #82: 'VR',
            86: '매출액',
            87: '경상이익',
            88: '당기순이익',
            89: 'BPS', # 주당 순자산
            90: '영업이익증가율',
            91: '영업이익',
            92: '매출액영업이익률',
            93: '매출액경상이익률',
            95: '결산년월',
            96: '분기BPS',
            98: '분기영업이익증가율',
            99: '분기경상이익증가율',
            100: '분기순이익증가율',
            101: '분기매출액',
            102: '분기영업이익',
            103: '분기경상이익',
            104: '분기당기순이익',
            105: '분기매출액영업이익률',
            106: '분기매출액경상이익률',
            107: '분기ROE', # 자기자본이익률
            109: '분기유보율',
            110: '분기부채비율',
            111: '최근분기년월'
        }

        o = win32com.client.Dispatch("cpsysdib.MarketEye")
        o.SetInputValue(0, list(col.keys()))  # A:시장전체 선택
        o.SetInputValue(1, code)  # 종목 string | list
        o.BlockRequest()

        numField = o.GetHeaderValue(0)  # 필드수
        numData = o.GetHeaderValue(2)  # 데이터수(종목수)

        data = []
        for ixRow in range(numData):
            tempdata = []
            for ixCol in range(numField):
                tempdata.append(o.GetDataValue(ixCol, ixRow))
            data.append(tempdata)
        res = {}
        for i in data:
            res[i[0]] = dict(zip(list(col.values()),i))


        return res
    def industry(self):
        li = self._stock.GetIndustryList()
        c = 0
        for i in li:
            c += len(list(self._stock.GetGroupCodeList(i)))

        exit()




    def OHLC(self, code, q, **kwargs):
        '''
            API DOC
            https://money2.daishin.com/e5/mboard/ptype_basic/HTS_Plus_Helper/DW_Basic_Read_Page.aspx?boardseq=284&seq=102&page=1&searchString=CpSysDib.StockChart&p=8839&v=8642&m=9508

            주식차트의 봉으로 표현하는거
            O(open), H(high), L(low), C(close)

            code string 종목코드
            q int 수량
        '''
        if not 'ptype' in kwargs:
            kwargs['ptype'] = 'D'


        o = win32com.client.Dispatch('CpSysDib.StockChart')
        o.SetInputValue(0, code)  # 종목코드
        o.SetInputValue(1, ord('1'))  # 1:기간, 2:개수

        # 기간으로 요청일때
        o.SetInputValue(2, kwargs['end']) # 요청 종료일 YYYYMMDD
        o.SetInputValue(3, kwargs['start']) # 요청 시작일
        
        
        o.SetInputValue(4, q)  # 요청개수
        o.SetInputValue(5, [0, 1, 2, 3, 4, 5])  # 0:날짜, 1:시간, 2~5:OHLC
        o.SetInputValue(6, ord(kwargs['ptype']))  # D: 일, W: 주, M: 월, m: 분, T: 틱
        o.SetInputValue(9, ord('1'))  # 0:무수정주가, 1:수정주가
        o.BlockRequest()

        count = o.GetHeaderValue(3)  # 3:수신개수
        
        # 위 필드에 따라서 바뀌는듯
        global col
        col = {
            1: '시간',
            2: '시가',
            3: '고가',
            4: '저가',
            5: '종가'
        }
        res = {}
        for i in range(count):
            date = str(o.GetDataValue(0, i))
            if not date in res:
                res[date] = []
            row = {}
            for ii in col:
                row[col[ii]] = o.GetDataValue( ii , i)
            res[date].append(row)
        return res

    def info(self, code:str):
        '''
        주식 종목의 현재 정보 얻기

        :param code: 종목 코드
        :return:
        '''
        # 종목 현재가격 얻기
        o = win32com.client.Dispatch('DsCbo1.StockMst')
        o.SetInputValue(0, code)
        o.BlockRequest()

        # 상태
        stat = o.GetDibStatus()
        if stat != 0:
            return {
                'result': 'error',
                'code': stat,
                'message': o.GetDibMsg1()
            }

        global col
        col = {
            0: '종목코드',
            1: '종목명',
            4: '시간',
            11: '현재가', # 지금데이터라 현재가 장 끝날때 가격
            12: '전일대비', # 어제 종가와 현재가 금액차이
            13: '시가', # 오늘 첫 거래 가격
            14: '고가', # 오늘중 최고 가격
            15: '저가',
            16: '매도호가',
            17: '매수호가',
            18: '거래량',
            19: '거래대금',
            55: '예상체결가'
        }

        res = {}
        for i in col:
            res[col[i]] = o.GetHeaderValue(i)

        return res

    @staticmethod
    def price_format(price) -> int:
        '''
        호가 금액범위에 따라 단위 맞추기
        :param price: 호가
        :return: int
        '''
        price = int(price)
        if price == 0:
            return False
        elif price < 1000:
            return price
        elif 1000 <= price and price < 5000:
            q = 5
            rest = price % q
        elif 5000 <= price and price < 10000:
            q = 10
            rest = price % q
        elif 10000 <= price and price < 50000:
            q = 50
            rest = price % q
        elif 50000 <= price and price < 100000:
            q = 100
            rest = price % q
        # 아래는 코스닥, 코스피 다름 / 코스닥기준
        elif 100000 <= price and price < 500000:
            q = 100
            rest = price % q
        else:
            q = 100
            rest = price % q
        return price + (q - rest) if rest > (math.ceil(q / 2)) else price - rest


    def rankTrade(self, **kwargs):
        '''
        거래량 또는 거래대금 순위
        :param kwargs:
            type string V | A
                거래량상위 | 거래대금상위
                default V
            limit int 순위 개수 default 20
        :return:
        '''
        type = 'V'
        limit = 20
        if 'type' in kwargs:
            type = kwargs['type']
        if 'limit' in kwargs:
            limit = kwargs['limit']

        o = win32com.client.Dispatch('CpSysDib.CpSvr7049')
        '''
            0 - (string)시장 구분 : "1":거래소, "2":코스닥, "4":전체(거래소+코스닥)    
            1 - (string) 선택 구분 : "V":거래량상위, "A":거래대금상위
            2 - (string) 관리 구분 :  "Y'', "N"
            3 - (string) 우선주 구분 :  "Y'', "N"
        '''
        o.SetInputValue(0, '2')
        o.SetInputValue(1, type)
        o.BlockRequest()

        global col
        col = {
            0: '순위',
            1: '종목코드',
            2: '종목명',
            3: '현재가',
            4: '전일대비',
            5: '전일대비율',
            6: '거래량',
            7: '거래대금'
        }

        res = {}
        # 정보 없을때 빈칸 또는 0
        for i in range(limit):
            code = o.GetDataValue(1, i)
            if code == '':
                continue
            if not i in res:
                res[i] = {}
            for ii in col:
                res[i][col[ii]] = o.GetDataValue(ii, i)
        return res



'''
내 증권 계좌 정보
'''
class account(stock):
    _account = None
    _balance = None
    def __init__(self):
        super(account, self).__init__()
        self.tradeInit()
        self._account = self._trade.AccountNumber[0]
        self._balance = win32com.client.Dispatch('CpTrade.CpTd6033')
        self._account_flag = self._trade.GoodsList(self._account, -1)  # -1:전체, 1:주식, 2:선물/옵션

    '''
        증거금 100% 주문 가능 금액을 반환한다.
        
        장 종료시 0 뜨는듯
        
        return int 증거금
    '''
    @property
    def cash(self):
        o = win32com.client.Dispatch('CpTrade.CpTdNew5331A')
        self.tradeInit()
        o.SetInputValue(0, self._account)  # 계좌번호
        o.SetInputValue(1, self._account_flag[0])  # 상품구분 - 주식 상품 중 첫번째
        o.BlockRequest()
        return o.GetHeaderValue(9)  # 증거금 100% 주문 가능 금액


    '''
        내가 가진 주식 보여주기
        매도 예약 되어있으면 0나옴
        return {
            code : { 주식, 코드, 수 }
        }
    '''
    @property
    def stock(self):
        self.tradeInit()
        self._balance.SetInputValue(0, self._account)  # 계좌번호
        self._balance.SetInputValue(1, self._account_flag[0])  # 상품구분 - 주식 상품 중 첫번째
        self._balance.SetInputValue(2, 50)  # 요청 건수(최대 50)
        self._balance.BlockRequest()

        res = {}
        global col
        col = {
            0: '종목명',
            #3: '결제잔고수량',
            #4: '결제장부단가',
            #5: '전일체결수량',
            #6: '금일체결수량',
            #7: '체결잔고수량',
            9: '평가금액', # 천원미만 내림
            10: '평가손익', # 천원미만 내림
            11: '수익률',
            12: '종목코드',
            15: '매도가능수량',
            #16: '만기일',
            18: '손익단가'
        }
        for i in range(self._balance.GetHeaderValue(7)):
            code = self._balance.GetDataValue(12, i)
            if not code in res:
                res[code] = {}
            for ii in col:
                res[code][col[ii]] = self._balance.GetDataValue(ii, i)
        return res


'''
트레이드 ㄱㄱㄱ
API doc
http://cybosplus.github.io/cptrade_new_rtf_1_/cptd0311_.htm
'''
class trade(account):
    _order = None
    def __init__(self):
        super(trade, self).__init__()
        # 일반거래
        self._order = win32com.client.Dispatch('CpTrade.CpTd0311')
        # 예약매수
        self._reserv = win32com.client.Dispatch("CpTrade.CpTdNew9061")
        # 예약 매도
        self._cancle = win32com.client.Dispatch("CpTrade.CpTdNew9064")
        # 예약 결과
        self._result = win32com.client.Dispatch("CpTrade.CpTd9065")

    def _send_reserv(self, code:str , q:int , price:int , type:str ):
        '''
        http://cybosplus.github.io/cptrade_new_rtf_1_/cptdnew9061.htm
        장외에 밤, 새벽에 할 수 있는거

        :param code: str 종목코드
        :param q: int 수량
        :param price: int 가격
        :param type: str 매도 | 매수 : 1|2
        :return: dict
        '''
        self._reserv.SetInputValue(0, self._account) # 계좌
        self._reserv.SetInputValue(1, self._account_flag[0]) #상품관리구분코드
        self._reserv.SetInputValue(2, type) # 주문종류
        self._reserv.SetInputValue(3, code) # 종목코드
        self._reserv.SetInputValue(4, q) # 수량
        self._reserv.SetInputValue(5, "01")  # 주문호가 구분 01: 보통
        self._reserv.SetInputValue(6, price) # 가격
        res = self._reserv.BlockRequest()
        if res == 4:
            remain_time = self._connect.LimitRequestRemainTime
            print('주의: 연속 주문 제한에 걸림. 대기 시간:', remain_time / 1000)
            time.sleep(remain_time / 1000)

        if self._reserv.GetDibStatus() != 0:
            print("통신상태", self._reserv.GetDibStatus(), self._reserv.GetDibMsg1())
            return False

        global col
        col = {
            0: '예약번호',
            #1: '계좌명',
            #2: '상품관리구분내용',
            3: '매매구분내용',
            4: '종목명',
            5: '주문수량',
            6: '주문호가구분내용',
            7: '주문가격',
            #8: '현금신용대용구분내용',
            #9: '대출일자',
            #10: '신용거래구분내용'
        }

        res = {}
        for i in col:
            res[col[i]] = self._reserv.GetHeaderValue(i)
        return res
    def buy_reserv(self, code:str , q:int , price:int ):
        return self._send_reserv(code , q , price, '2')

    def list_reserv(self):
        '''
        예약 주문 목록
        취소면 취소 예정이면 예정 다보여줌
        :return:
        '''
        self._result.SetInputValue(0, self._account)
        self._result.SetInputValue(1, self._account_flag[0])
        self._result.SetInputValue(2, 20)
        self._result.BlockRequest()

        if self._result.GetDibStatus() != 0:
            print("통신상태", self._result.GetDibStatus(), self._result.GetDibMsg1())
            return False

        global col
        col = {
            #0: '시장구분',
            1: '주문구분', # 매수 매도
            2: '종목코드',
            3: '주문수량',
            4: '주문호가구분',
            #5: '주문입력매체코드내용' # 영업점, 플러스 등등
            6: '예약번호',
            #7: '신용구분내용',
            8: '종목명',
            9: '주문단가',
            #10: '대출일',
            #11: '주문번호',
            12: '처리구분내용',
            13: '거부코드',
            14: '거부내용'
        }

        res = {}

        # 수신개수
        count = self._result.GetHeaderValue(4)
        if count == 0:
            return res

        for i in range(count):
            if not i in res:
                res[i] = {}
            for ii in col:
                res[i][col[ii]] = self._result.GetDataValue(ii, i)
        return res

    def sell_reserv(self, code:str , q:int , price:int ):
        '''
        예약 판매
        :param code: str 종목코드
        :param q: int 수량
        :param price: int 가격
        :return: dict 결과값
        '''
        return self._send_reserv(code, q, price, '1')

    def cancel_reserv_all(self) -> bool:
        '''
        예약 전체취소
        :return:
        '''
        li = self.list_reserv()
        res = 0
        for i in li:
            if li[i]['처리구분내용'] == '주문취소':
                continue
            time.sleep(1)
            if self.cancel_reserv(li[i]['예약번호'],li[i]['종목코드']) == False:
                res += 1
        return res == 0
    def cancel_reserv(self, num:int , code:str ) -> bool:
        '''
        예약 주문 취소

        :param num: 예약번호
        :param code: 종목코드
        :return:
        '''
        self._cancle.SetInputValue(0, num)
        self._cancle.SetInputValue(1, self._account)
        self._cancle.SetInputValue(2, self._account_flag[0])
        self._cancle.SetInputValue(3, code)
        self._cancle.BlockRequest()

        if self._cancle.GetDibStatus() != 0:
            print("통신상태", self._cancle.GetDibStatus(), self._cancle.GetDibMsg1())
            return False
        return True

    def _send(self, **kwargs):
        '''

        :param kwargs div : str : 1 | 2  : 매도 | 매수:
            code : str : 종목코드
            q : int : 수량
            price : int : 가격
            type : str : 01 02 03 ... 13 | 보통(지정가), .... 최우선
        :return:
        '''
        self._order.SetInputValue(0, kwargs['div'])  # 1: 매도, 2: 매수
        self._order.SetInputValue(1, self._account)  # 계좌번호
        self._order.SetInputValue(2, self._account_flag[0])  # 상품구분 - 주식 상품 중 첫번째
        self._order.SetInputValue(3, kwargs['code'])  # 종목코드
        self._order.SetInputValue(4, kwargs['q'])  # 매수할 수량
        if kwargs['price'] != 0:
            self._order.SetInputValue(5, kwargs['price'])  # 매수할 가격
        self._order.SetInputValue(7, "0")  # 주문조건 0:기본, 1:IOC, 2:FOK
        self._order.SetInputValue(8, kwargs['type'])
        res = self._order.BlockRequest()
        try:
            print(self._order.GetDibStatus())
            print(self._order.GetDibMsg1())
        except:
            print('오더는 getdibstatus 없는가?')
        if res == 4:
            remain_time = self._connect.LimitRequestRemainTime
            print('주의: 연속 주문 제한에 걸림. 대기 시간:', remain_time / 1000)
            time.sleep(remain_time / 1000)

        return res
    def buy(self, code:str, q:int, price:int = 0, type = "01"):
        '''
        
        :param code: str 종목코드
        :param q: int 수량
        :param price: int 가격
        :param type: str 주문호가 구분 ( 01: 보통가, ... 12:최유리, 13:최우선 )
        :return: 
        '''
        self._send(
            div = '2',
            code = code,
            q = q,
            price = price,
            type = type
        )

        global col
        col = {
            3: '종목코드',
            4: '주문수량',
            5: '주문단가',
            10: '종목명',
            13: '주문호가구분코드'
        }
        res = {}
        for i in col:
            resp = self._order.getHeaderValue(i)
            #if i == 4 and resp == 0:
            #    print('통신실패')
            #    return False
            res[col[i]] = resp
        return res


    def sell_all(self):
        stock = self.stock
        for code in stock:
            time.sleep(1)
            self.sell(
                code ,
                stock[code]['매도가능수량'],
            )
    '''
    판매
    code string 종목코드 또는 all default all 
    '''
    def sell(self, code , q:int , price:int = 0 , type = "01"):
        self._send(
            div='1',
            code=code,
            q=q,
            price=price,
            type=type
        )

        global col
        col = {
            3: '종목코드',
            4: '주문수량',
            5: '주문단가',
            10: '종목명',
            13: '주문호가구분코드'
        }
        res = {}
        for i in col:
            resp = self._order.getHeaderValue(i)
            if i == 4 and resp == 0:
                return False
            res[col[i]] = resp
        return res


########################################################################################################################

# CpEvent: 실시간 이벤트 수신 클래스
# CpEvent: 실시간 이벤트 수신 클래스
class CpEvent:
    def set_params(self, client, name, parent):
        self.client = client  # CP 실시간 통신 object
        self.name = name  # 서비스가 다른 이벤트를 구분하기 위한 이름
        self.parent = parent  # callback 을 위해 보관

        # 데이터 변환용
        self.concdic = {"1": "체결", "2": "확인", "3": "거부", "4": "접수"}
        self.buyselldic = {"1": "매도", "2": "매수"}
        print(self.concdic)
        print(self.buyselldic)

    # PLUS 로 부터 실제로 시세를 수신 받는 이벤트 핸들러
    def OnReceived(self):
        print('On Receiveed',self.name)
        if self.name == "stockcur":
            # 현재가 체결 데이터 실시간 업데이트
            exFlag = self.client.GetHeaderValue(19)  # 예상체결 플래그
            cprice = self.client.GetHeaderValue(13)  # 현재가
            # 장중이 아니면 처리 안함.
            if exFlag != ord('2'):
                return

            # 현재가 업데이트
            self.parent.sprice.cur = cprice
            print("PB > 현재가 업데이트 : ", cprice)

            # 현재가 변경  call back 함수 호출
            self.parent.monitorPriceChange()

            return

        elif self.name == "stockbid":
            # 현재가 10차 호가 데이터 실시간 업데이트
            dataindex = [3, 4, 7, 8, 11, 12, 15, 16, 19, 20, 27, 28, 31, 32, 35, 36, 39, 40, 43, 44]
            obi = 0
            for i in range(10):
                self.parent.sprice.offer[i] = self.client.GetHeaderValue(dataindex[obi])
                self.parent.sprice.bid[i] = self.client.GetHeaderValue(dataindex[obi + 1])
                obi += 2

            # for debug
            for i in range(10):
                print("PB > 10차 호가 : ", i + 1, "차 매도/매수 호가: ", self.parent.sprice.offer[i], self.parent.sprice.bid[i])
            return True

            # 10차 호가 변경 call back 함수 호출
            self.parent.monitorPriceChange()

            return

        elif self.name == "conclution":
            # 주문 체결 실시간 업데이트
            conflag = self.client.GetHeaderValue(14)  # 체결 플래그
            ordernum = self.client.GetHeaderValue(5)  # 주문번호
            amount = self.client.GetHeaderValue(3)  # 체결 수량
            price = self.client.GetHeaderValue(4)  # 가격
            code = self.client.GetHeaderValue(9)  # 종목코드
            bs = self.client.GetHeaderValue(12)  # 매수/매도 구분
            balace = self.client.GetHeaderValue(23)  # 체결 후 잔고 수량

            conflags = ""
            if conflag in self.concdic:
                conflags = self.concdic.get(conflag)
                print(conflags)

            bss = ""
            if (bs in self.buyselldic):
                bss = self.buyselldic.get(bs)

            print(conflags, bss, code, "주문번호:", ordernum)
            # call back 함수 호출해서 orderMain 에서 후속 처리 하게 한다.
            self.parent.monitorOrderStatus(code, ordernum, conflags, price, amount, balace)
            return

# Hoga : 주식 현재가 및 10차 호가 조회
class Hoga:
    def __init__(self):
        self.objCpCybos = win32com.client.Dispatch("CpUtil.CpCybos")
        bConnect = self.objCpCybos.IsConnect
        if (bConnect == 0):
            print("PLUS가 정상적으로 연결되지 않음. ")
            return
        self.objStockMst = win32com.client.Dispatch("DsCbo1.StockMst")
        self.objStockjpbid = win32com.client.Dispatch("DsCbo1.StockJpBid2")

        return

    def Request(self, code):
        # 현재가 통신
        self.objStockMst.SetInputValue(0, code)
        self.objStockMst.BlockRequest()

        # 10차 호가 통신
        self.objStockjpbid.SetInputValue(0, code)
        self.objStockjpbid.BlockRequest()

        if self.objStockMst.GetDibStatus() != 0:
            print('통신에러 :', self.objStockMst.GetDibMsg1())
            return False
        if self.objStockjpbid.GetDibStatus() != 0:

            print('통신에러 :',  self.objStockjpbid.GetDibMsg1())
            return False

        res = {
            'close': self.objStockMst.GetHeaderValue(11),
            'sell': [],
            'buy': []
        }
        # 10차호가
        for i in range(10):
            # 매도호가
            res['sell'].append(self.objStockjpbid.GetDataValue(0, i))
            # 매수호가
            res['buy'].append(self.objStockjpbid.GetDataValue(1, i))
        return res

