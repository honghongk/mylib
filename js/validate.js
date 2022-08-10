/**
코드를 좀더 분리해야 멋진 라이브러리가 될듯


throttle 아직 코드 없음
*/

'use strict';
class Validate
{
    /**
     * 해당 element의 dataset에 추가
     * @param string selector 
     * @param string uri 
     * @param function 메세지 있을때 출력
     * @param function 에러 핸들러
     */
    set(selector,uri,display,error)
    {
        let form = document.querySelectorAll(selector);
        form.forEach((v)=>{

            let send = JSON.stringify({
                action: v.action,
                method: v.getAttribute('method')
            });

            // 포커스 할때 한번만 로딩
            // 나중에
            // v.addEventListener('onfocus',function(){
                // 규칙 가져오기
                fetch(uri,{
                    method: 'post',
                    mode: 'cors', // no-cors, *cors, same-origin
                    cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
                    credentials: 'same-origin', // include, *same-origin, omit
                    headers: {
                        'Content-Type': 'application/json',
                    // 'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    redirect: 'follow', // manual, *follow, error
                    referrerPolicy: 'no-referrer',
                    body: send
                }).then(function(res){
                    // form에 규칙 세팅
                    // default 있으면 폼 채우기
                    res.json().then(function(data){
                        if ( data.message )
                            if ( display )
                                display( data.message, send );
                            else
                                alert(data.message+' '+send);

                        Object.entries(data).forEach(vv=>{
                            v.dataset[vv[0]] = JSON.stringify(vv[1]);

                            // 기본값 세팅
                            vv.recursive(function(kkk,vvv){
                                if ( typeof vvv == 'object' && Object.keys(vvv).indexOf('default') >= 0 )
                                {
                                    let node = v.querySelectorAll('[name='+kkk+']')
                                    node.forEach(function(n){
                                        if ( n.tagName == 'INPUT' )
                                        {
                                            if ( n.value.length == 0 )
                                                n.value = vvv.default;
                                        }
                                        else if ( n.tagName == 'SELECT')
                                        {
                                            n.querySelectorAll('option[value='+vvv.default+']')[0].selected = true;
                                        }
                                        else
                                        {
                                            console.warn('기본값 세팅 해야함');
                                            console.log(n.tagName,vvv.default)
                                        }
                                    });
                                }
                            });

                        });
                    }).catch(function(res){
                        if (error)
                            error(res);
                        else
                            console.log('타입에러',res)
                    });
                }).catch(function(res){
                    if (error)
                        error(res);
                    else
                        console.log('에러',res)
                });
            // },{once:true});
        });
    }

    /**
     * @fix 데이터 포맷 맞추기 php 처럼
     * @param {*} data 
     * @param {*} format
     */
    static date(data,format)
    {

    }

    static minlength(data,min)
    {
        return String(data).length >= min;
    }

    static maxlength(data,max)
    {
        return String(data).length <= max;
    }


    static min(data,min)
    {
        if ( isNaN(min) )
            throw 'min 설정은 숫자만 가능';
        if ( isNaN(data) )
            return false;
        return data >= min;
    }

    static max(data,max)
    {
        if ( isNaN(max) )
            throw 'max 설정은 숫자만 가능';
        if ( isNaN(data) )
            return false;
        return data <= max;
    }

    static regex(data,regex)
    {
        let r = new RegExp(regex);
        return r.test(data);
    }

    static not_regex(data,regex)
    {
        return ! Validate.regex(data,regex);
    }

    static numeric(data)
    {
        return ! isNaN(data);
    }

    static in(data,arr)
    {
        return ! ( arr.indexOf(data) < 0 );
    }

    static notin(data,arr)
    {
        return ( arr.indexOf(data) < 0 );
    }
}




/**
 * @fix 다차원 배열, 파일 등 추가해야함
 * 지금 키 없는 2차 까지만
 * 
 * @param {*} rule 
 * @param {*} message 
 */
HTMLFormElement.prototype.validate = function(rule,message,act){
    if ( typeof rule[this.getAttribute('method')] == 'undefined' )
        throw 'http method 다름';

    // 결과
    let res = false;

    // 에러여부
    let error = false;

    let data = new FormData(this);

    Object.entries(rule).forEach(v=>{
        // 일단 input만 검사
        for ( let vv of data )
        {
            // 에러 있으면 중단
            if ( error )
                break;

            // 다차원 배열 확인
            let match = vv[0].match(/\[(.*?)\]/g);
            if ( match != null )
            {
                if ( match.length > 1 || match[0] != '[]' )
                    throw '다차원 배열 아직 미지원';
                vv[0] = vv[0].substr(0,vv[0].indexOf('[]'));
            }

            if ( typeof v[1][vv[0]] == 'undefined')
                throw '데이터 키가 없습니다';

            // optional 있고 값이 비어있으면 스킵
            if ( Object.values(v[1][vv[0]]).indexOf('optional') >= 0 && vv[1].length == 0 )
                continue;

            Object.entries(v[1][vv[0]]).forEach((vvv)=>{
                // 에러 있으면 중단
                if ( error )
                    return;
                if (vvv[0] == 'call' || typeof Validate[vvv[0]] == 'undefined')
                    return;

                if ( Validate[vvv[0]](vv[1],vvv[1]) === false )
                {
                    // 에러
                    error = true;

                    if (typeof message[v[0]][vv[0]] == 'undefined')
                        return console.error('에러메세지가 없습니다')

                    // @fix 다차원 일때 수정해야함
                    let node = this.querySelectorAll('[name='+vv[0]+']').item(0);

                    // 후처리
                    if ( typeof act != 'undefined' )
                        act(message[v[0]][vv[0]]['message'],node);
                    else
                    {
                        // 메세지
                        alert(message[v[0]][vv[0]]['message']);

                        // 포커스
                        node.focus();
                    }
                }
            });
        }
        res = ! error;
    });
    return res;
};


/**
 * @fix 나중에 수정
 * 실행만 제어할지 메세지까지 출력할지
 * 
 * @param {*} rule 
 * @param {*} message 
 * @returns 
 */
HTMLFormElement.prototype.throttle = function(rule,message){

    return true;
    let data = new FormData(this);

    Object.entries(rule).forEach(v=>{
        // console.log(v[0])
    });
};
