/**
 * 날짜 포맷 맞추기
 * php date 처럼 사용하게끔
 * @param string|int|Date 날짜
 * @param string 포맷
 */
function date_format(date = new Date(), format = 'Y-m-d H:i:s')
{
    // 숫자일때
    if ( ! isNaN ( date ) )
        date = new Date(Number(date));
    else if (typeof date == 'string')
        date = new Date(date);

    // 파싱 불가 에러
    if ( date == 'Invalid Date' )
        throw date;

    let formating = {
        y : String(date.getFullYear()).substr(2,2),
        Y : date.getFullYear(),
        m : date.getMonth() + 1,
        d : date.getDate(),
        H : date.getHours(),
        i : date.getMinutes(),
        s : date.getSeconds(),
        w : function(){
            let day = ['일','월','화','수','목','금','토'];
            return day[date.getDay()];
        },

        // 영어로는 am pm 보내야함
        A : function(date){
            let h = date.getHours()
            if ( h < 12 )
                return '오전';
            return '오후';
        },
    }

    for (const key in formating)
    {
        let v = '';
        if ( formating[key] instanceof Function)
            v = formating[key](date);
        else
            v = formating[key];
        if ( v < 10 )
            v = "0" + String(v)
        format = format.replace(key, v);
    }
    return format;

}

/**
 * @see 타임존, 로케일 안맞으면 오차 있음
 * @see 포맷 현재 d만 가능
 * 시작, 끝 시간 차이 구하고 f 단위만큼 배열에 반환
 * 파라미터에 속하는 날짜도 포함
 * start, end 둘다 있는데 작은쪽이 시작, 큰쪽이 끝
 * @param string|int|Date 날짜
 * @param string|int|Date 날짜
 * @param string 포맷
 */
function date_range(start,end,f = 'd')
{
    if ( ! isNaN ( start ) )
        start = new Date(Number(start));
    else if (typeof start == 'string')
        start = new Date(start);

    if ( ! isNaN ( end ) )
        end = new Date(Number(end));
    else if (typeof end == 'string')
        end = new Date(end);

    start = Math.min(start.valueOf(),end.valueOf());
    end = Math.max(start.valueOf(),end.valueOf());

    let diff = end - start;

    // 포맷
    let t = {
        // 하루 밀리초
        d: 86400000
    };

    if ( ! t[f] )
        throw '없는 포맷'
    f = t[f];

    diff = diff / f;

    let res = [];
    for ( let i = diff + 1 ; i > 0; i -- )
        res.push(new Date(start + (i * f)))

    return res;
}
