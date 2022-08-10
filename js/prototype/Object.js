'use strict';

/**
 * js 에서 object 타입은 모든 타입임
 * 
 * Object.defineProperty enumerable false로 for in 에서 안나오도록 충돌 회피
 */


/**
 * for of 루프 가능한지 확인
 * https://stackoverflow.com/questions/18884249/checking-whether-something-is-iterable
 * @returns {boolean}
 */
 Object.defineProperty(Object.prototype, 'isIterable',{
    value: function(){
        // checks for null and undefined
        if (this == null)
            return false;
        return typeof this[Symbol.iterator] === 'function';
    },
    enumerable: false
});

/**
 * encodeURI 재귀
 * @returns {boolean}
 */
 Object.defineProperty(Object.prototype, 'encodeURI',{
    value: function(parent = ''){

        let res = [];
        // for in 가능한지 확인
        if ( typeof this == 'undefined' || this == null || typeof this != 'object')
            return res.join('&');

        
        for (let k in this)
        {
            let v = this[k];
            if ( parent.length > 0 )
                k = parent + '[' + k + ']' ;
            if ( typeof v == 'object')
                res.push(v.encodeURI(k));
            else
                res.push(k+'='+v);
        }
        return res.join('&');
    },
    enumerable: false
});



/**
 * object 클론
 * @returns {}
 */
 Object.defineProperty(Object.prototype, 'clone',{
    value: function(){
        let t = this.constructor.name == 'Array'
            ? [] : {} ;
        return Object.assign(t,Object.create(Object.getPrototypeOf(this)), this)
    },
    enumerable: false
});





/**
 * 재귀
 * @param {function} callback 함수
 * @returns 
 */
Object.defineProperty(Object.prototype, 'recursive',{
    value: function(callback){
        let node = this;
        if (typeof node == 'undefined')
            return;
        Object.entries(node).forEach(function(v){
            callback(v[0],v[1]);
            if ( typeof v[1] == 'object' )
                v[1].recursive(callback);
        });
    },
    enumerable: false
});


/**
 * 재귀 돌면서 풀경로 얻기
 * 위와 비슷함
 * @param {function} callback 아이템마다 콜백 실행
 * @param {array} path 1차원 풀경로 담기
 * @returns {array} 아이템마다 풀경로
 */

Object.defineProperty(Object.prototype,'recursivePath',{
    value: function(callback, path = []){

        let res = [];
    
        Object.entries(this).forEach((v)=>{
            let k = v[0];
            v = v[1];
    
            // 경로 array
            let c = path.concat(k);
            if ( callback )
                callback(k,v,c);
    
            if ( typeof v == 'object' && v != null  )
                Object.assign(res,v.recursivePath(callback,c));
        });
    
        return res;
    },
    enumerable: false
});


/**
 * 오브젝트 완전 같은지 확인
 * @see ? 여러 변수로 테스트 필요
 * @fix element 비교에서 맞지않음
 * 
 * @param {array ...} 배열 또는 변수 여러개
 * @returns {boolean}
 */
Object.defineProperty(Object.prototype,'isSame',{
    value: function(){

        // null도 object
        if ( this == null )
            return false;
    
        // 검사결과
        let res = [];
        
        for (let v of arguments)
        {
            // null 이면 false
            if ( v == null )
                return false;
            // 길이확인
            if ( v.length !== this.length )
                return false;
            
            Object.entries(v).forEach((vv)=>{
                res.push(this[vv[0]] === vv[1]);
            });
        }
        return res.indexOf(false) < 0;
    },
    enumerable: false
});
