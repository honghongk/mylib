
<?php


/**
 * 날짜/시간 관련 함수 모음
 */
class Time
{
    static protected $yoil = ['일','월','화','수','목','금','토'];
    /**
     * @fix 일단 일 단위로 했고 년/월 단위 할때 뒤에 날짜 어찌할지
     * 날짜/시간 범위 얻기
     * @param int|string 시작날짜
     * @param int|string 종료날짜
     * @param string 리턴 포맷
     * @param string 갭 단위
     * @return array 날짜범위
     */
    static function range($start, $end, $format = 'Y-m-d H:i:s' , $unit = 'd')
    {
        static $u;
        $u = [
            // 윤년
            'Y' => function($s, $e) use ( $format ) {
                $s = explode('-', date('Y', $s));
                $e = explode('-', date('Y', $e));

                $y = range($s[0],$e[0]);

                // 뒤에 날짜 임의로 붙여야 값 안틀어짐
                $res = [];
                foreach ($y as $v)
                    $res[] = date($format,strtotime($v.'-01-01'));
                return $res;
            },
            // 매달 다름
            'm' => function($s, $e) use ( $format ) {
                $s = explode('-', date('Y-m', $s));
                $e = explode('-', date('Y-m', $e));

                $y = range($s[0],$e[0]);
                $m = range($s[1],$e[1]);

                $res = [];
                foreach ($y as $v)
                    foreach ($m as $vv)
                        $res[] = date($format,strtotime($v.'-'.$vv));
                return $res;
            },
            'd' => 86400,
            'H' => 3600,
            'i' => 60,
            's' => 1,
        ];

        if ( ! is_numeric ( $start ) )
            $start = strtotime($start) ;
        if ( ! is_numeric ( $end ) )
            $end = strtotime($end) ;


        $f = $u[$unit];
        if ( is_int ( $f ) )
        {
            // 나눠 떨어지게 만들기 소수점 버림
            if ( ($end - $start) % $f > 0 )
                $end = $start + ( $f * intval(($end - $start) / $f) );

            $range = array_map(function($v) use ($format){
                return date($format, $v);
            }, range($start, $end, $f) );
        }
        elseif ( is_callable($f) )
            $range = $f($start,$end);

        return $range;
    }


    /**
     * 주말인지 확인
     * @param int|string 날짜
     * @return boolean
     */
    static function isWeekend($date)
    {
        if ( ! is_numeric ( $date ) )
            $date = strtotime($date) ;
        $w = date('l', $date );
        return $w == 'Sunday' || $w == 'Saturday';
    }


    /**
     * 특정 요일인지 확인
     * @param int|string 날짜
     * @param int|string 요일
     * @return boolean
     */
    static function isDay($date, $d)
    {
        if ( ! is_numeric ( $date ) )
            $date = strtotime ( $date ) ;
        
        // 영어단어로 체크
        if( ! is_numeric( $d ) )
            return date('l', $date ) == $d;
        else
        {
            // 숫자로 체크
            if ( in_array ( $d, range ( 0 , 6 ) ) )
                throw new Exception("0 ~ 6 숫자를 입력하세요", 1);
            
            $w = date('w', $date );
            return $w == $d;
        } 
    }


    /**
     * 월 끝날 얻기
     * @param int|string 날짜
     * @return string 날짜 포맷 
     */
    static function endOfMonth($date, $format = 'Y-m-d')
    {
        if ( ! is_numeric ( $date ) )
            $date = strtotime($date) ;
        return date ( $format , strtotime ( date('Y-m-t', $date ) ) ) ;
    }


    /**
     * 시간 내 랜덤
     * 시간만 있으면 오늘의 Y-m-d 빼야함
     * @param int|string 시작시간
     * @param int|string 종료시간
     * @param string 리턴 포맷
     * @return string
     */
    static function randomTime($start = '0:0:0', $end = '23:59:59', $format = 'H:i:s')
    {
        if ( ! is_numeric ( $start ) )
            $start = strtotime($start) ;
        if ( ! is_numeric ( $end ) )
            $end = strtotime($end) ;

        $t = strtotime(date('Y-m-d'));

        // 숫자 범위 랜덤을 위해 시간 부분만
        $start = $start - $t;
        $end = $end - $t;

        // date 함수로 만들려면 Y-m-d의 숫자 더하기
        return date($format,$t + rand($start,$end));
    }


    /**
     * 날짜 범위 주별
     * php 기본은 월 ~ 일 묶음
     * $yoil 순으로 묶기때문에 일 ~ 월
     * @param int|string 시작날짜
     * @param int|string 종료날짜
     * @param string 리턴 포맷
     * @param string 갭 단위
     * @return array 주별 날짜범위
     */
    static function rangeWeek($start, $end, $format = 'Y-m-d H:i:s' , $unit = 'd')
    {
        // 범위얻기
        $range = self::range($start,$end,'Y-m-d H:i:s',$unit);
        $yoil = self::$yoil;

        $res = [];
        // 첫날 요일 찾기
        $index = intval(date('w', strtotime ( $start ) ));
        if ( $yoil[$index] != current($yoil) )
        {
            while( $r = next($yoil) )
                if ($yoil[$index] == $r )
                    break;
        }
        // 아래 루프 next도는거 보정
        prev($yoil);

        // 데이터 채우기
        // 주 index
        $i = 0;
        foreach ($range as $r)
        {
            // 요일 얻기
            $w = next($yoil);

            // 요일 다 돌았으면 다음주차
            if ( $w === false )
            {
                $w = reset($yoil);
                $i ++;
            }

            // 주차 => 요일 => 날짜
            $res[$i][$w] = date ( $format , strtotime ( $r ) );
        }

        return $res;
    }
    
    
    /**
     * 특정 시간의 주 전체 얻기
     * @param null|string|int 기준날짜
     * @param string 날짜 포맷
     * @return array<string> 한주
     */
    static function getWeek($date = NULL, $format = 'Y-m-d H:i:s')
    {
        // 숫자로 바꿔주기
        if ( is_null ( $date ) )
            $date = time();
        else if ( ! is_numeric ( $date ) )
            $date = strtotime($date) ;

        // 요일 찾기
        $day = date('w',$date);

        // 요일이라 하루 기준 시간 고정 계산
        $start = (0 - $day) * 86400;
        $end = (6 - $day) * 86400;

        $start_date = $date + $start;
        $end_date = $date + $end;

        return self::range($start_date, $end_date, $format);
    }


}
