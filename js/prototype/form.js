'use strict';

/**
 * 폼 fetch 전송
 * @fix 데이터 타입에 따라 응답 처리 변경
 * @param {success: function, error: function, ...} fetch옵션
 */
HTMLFormElement.prototype.fetch =
HTMLFormElement.prototype.ajax = function(option){

    let url = this.getAttribute('action');
    let method = this.getAttribute('method')?.toUpperCase() ?? 'GET';


    // 옵션 기본값
    let def = {
        data: new FormData(this),

        header: {
            'Accept': 'application/json',
            'Referrer-Policy': 'strict-origin'
        },
        // 외부 요청 여부, 서버의 헤더 설정이 더 상위임
        cors: false,

        // xhr 은 지원 안하는거같기도 ??????
        // fetch 에서  manual, *follow, error
        // redirect: 'follow',
        timeout: 0,
        cache: false,
        async: true,
        // mode: 'same-origin',
        success: console.log,
        error: console.log,
    };

    // 파라미터와 옵션 기본값 합치기
    // @fix 헤더 내부도 재귀적으로 합치기?
    option = [def, this.ajax.option, option].reduce((res,cur)=>{
        return Object.assign(res,cur);
    });

    // url에 파라미터 놓기
    if ( ['GET','HEAD'].indexOf(method) >= 0 )
        url += '?'+option.data.serialize();

    // 전송 세팅
    let xhr = new XMLHttpRequest();
    xhr.withCredentials = option.cors;

    xhr.open(method, url, option.async, option.user, option.password);
    for(let k in option.header)
        xhr.setRequestHeader(k,option.header[k]);


    // 여기서 리다이렉트 ???
    // xhr.onreadystatechange = function(e){
    //     console.log(this.readyState)
    //     console.log(this.getAllResponseHeaders())
    //     // 로드 끝
    //     if ( this.readyState !== 4 )
    //     {
    //         return;
    //     }
    //     if ( 300 <= this.status || this.status < 400 )
    //     {
    //         console.log(this.status)
    //     }
    // };
    xhr.onload = function(e){

        // 데이터
        let res = this.responseText;

        // response 데이터 알맞게 변환
        if ( option.header?.Accept?.split(';').indexOf('application/json') >= 0 )
        {
            try {
                res = JSON.parse(this.responseText);
            } catch (error) {
                return option.error(res,this,error);
            }
        }

        // 코드별 처리
        // 성공
        if (this.status >= 200 && this.status < 300)
        {
            return option.success(res);
        }
        // redirect 여기서 안됨
        // else if ( xhr.status >= 300 && xhr.status < 400 )
        // {
        //     console.log('redirect 처리 ㄱㄱ');
        // }
        // 에러
        else
            return option.error(res,this);
    }

    // 에러
    xhr.onerror = option.error;
    xhr.ontimeout = option.error;
    xhr.onabort = option.abort;


    // 응답받을때
    // xhr.onloadstart = option.progress;
    xhr.onprogress = option.progress;
    // xhr.onloadend = option.progress;

    // 보낼때 abort, error, load, loadend, loadstart, progress, timeout
    xhr.upload.onerror = option.error;
    xhr.upload.ontimeout = option.error;
    xhr.upload.onabort = option.abort;
    xhr.upload.onprogress = option.progress;
    xhr.upload.onloadstart = option.progress;
    xhr.upload.onloadend = option.progress;

    // GET/HEAD 
    if ( ['GET','HEAD'].indexOf(method) < 0 )
        xhr.send(option.data);
    else
        xhr.send();
};



/**
 * 폼데이터를 Blob 타입으로 리턴
 * @returns {Array<Blob>}
 */
FormData.prototype.blob = function(){
    let res = [];
    for (let row of this)
    {
        let [k,v] = row;

        // 오브젝트는 그냥 놓기
        if ( ['string','number'].indexOf(typeof v) >= 0 )
            res.push(new Blob([v],{type: 'plain/text'}))
        else if ( v instanceof Blob )
            res.push(v)
        else
            res.push(new Blob([JSON.stringify(v),null,2]),{type: 'application/json'});
    }
    // return new Blob(res);
    return res;
};
HTMLFormElement.prototype.blob = function(){
    let data = new FormData(this);
    return data.blob();
};


/**
 * 폼데이터를 HTTP query 형식으로 변경
 * 숫자, 문자만 가능
 * @return {string}
 */
FormData.prototype.serialize = function(){
    let res = '';
    for (let row of this)
    {
        let [k,v] = row;
        if ( ['string', 'number'].indexOf(typeof v) < 0 )
            continue;
        res += `${k}=${v}&`;
    }

    return res.slice(0,-1);
}
HTMLFormElement.prototype.serialize = function(){
    let data = new FormData(this);
    return data.serialize();
    
};


/**
 * jquery 함수와 같은형식으로
 * 숫자, 문자만 가능
 * @return {Array}
 */
FormData.prototype.serializeArray = function(){
    let res = [];
    for (let row of this)
    {
        let [k,v] = row;
        if ( ['string', 'number'].indexOf(typeof v) < 0 )
            continue;
        res.push({
            name: k,
            value: v
        });
    }
    return res;
};
HTMLFormElement.prototype.serializeArray = function(){
    let data = new FormData(this);
    return data.serializeArray();
};


/**
 * http 쿼리 스트링을 오브젝트로 가져오기
 * @returns {object} 다차원 오브젝트
 */
HTMLFormElement.prototype.query = function(){
    let url = new URL(this.action);
    let data = url.search.slice(1);
    return data.length === 0 ? {} : unserialize(data);
};


/**
 * https://stackoverflow.com/questions/1184624/convert-form-data-to-javascript-object-with-jquery
 * HTTP query string 으로 되어있는것을 다차원 오브젝트로 구조화 한다
 * @fix Object.assign이 재귀적으로 안돼서 jquery 함수썻는데 바닐라로 따로 만들기
 * @fix 파일 오브젝트를 제대로 오브젝트로 세팅되도록 수정하기
 * @returns {object} 다차원 오브젝트
 */
function unserialize(str){

    let data = str.split('&').map(v=>v.split('='));
    
     let res = {},
        patterns = {
            "validate": /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
            "key":      /[a-zA-Z0-9_]+|(?=\[\])/g,
            "push":     /^$/,
            "fixed":    /^\d+$/,
            "named":    /^[a-zA-Z0-9_]+$/
        };

    // 키 세팅
    let set = function(o, k, v){
        o[k] = v;
        return o;
    }

    // []로 끝나면 push
    let push = function(k){
        if ( ! push[k] )
            push[k] = 0;
        return push[k] ++ ;
    }
    

    // 폼데이터
    for (let row of data)
    {
        let [k,v] = row;
        // 유효성 검사
        if ( ! patterns.validate.test(k) )
            throw '데이터키 invalid `' + str + '`';

        // 키 분할
        let match = k.match(patterns.key),
            key,
            merge = v,
            reverse_key = k;

        // 뒤에서부터 뽑고 입력
        while ( (key = match.pop()) !== undefined )
        {
            reverse_key = reverse_key.replace(new RegExp('\\[' + key + '\\]$'),'');

            if ( key.match(patterns.push) )
                merge = set([], push(reverse_key), merge);
            else if ( key.match(patterns.fixed) )
                merge = set({}, key, merge);
            else if ( key.match(patterns.named) )
                merge = set({}, key, merge);

            // 루프 끝났으면 적용
            if ( match.length == 0)
                res = $.extend(true,res,merge);
                // res = Object.assign(res,merge);
        }
    }
    return res;
}


/**
 * 폼 데이터를 다차원 오브젝트 구조화 한다
 * @returns {object} 다차원 오브젝트
 */
HTMLFormElement.prototype.object = function(){
    return unserialize(this.serialize());
};
