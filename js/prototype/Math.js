'use strict';

/**
 * 최대공약수 구하기
 * @param arguments int
 * @returns int 최대공약수
 */
Math.gcd = function ()
{
    // 최소 빼고 next에 넣고 모든 나머지 구해서 넘기기
    let min , max , rem ;
    let next = new Array () ;
    let check = new Array () ;
    for ( let[ k , v ] of Object.entries(arguments) )
    {
            v = Math.abs ( Number ( v ) ) ;

            if ( isNaN ( v ) || v == 0  )
                    return false ;

            if ( typeof ( min ) == 'undefined' || typeof ( max ) == 'undefined' )
                    min = max = v ;
            if ( v < min )
                    min = v ;

            check.push(v);
    }

    // 순서 거꾸로 큰 수 부터
    check.sort(function(a,b){return b-a;});;
    check.forEach(function(v,k){
            rem = v % min ;
            if ( rem > 0 )
                    next.push ( v % min ) ;
    });
    next.push(min);

    if ( typeof ( min ) == 'undefined' )
            return false ;
    if ( next.length == 0 )
            return false ;
    if ( next.length == 1 )
            return next.pop();

    return Math.gcd.apply( this , next ) ;
}

/**
 * 비율 구하기
 * @param arguments int
 * @returns array
 */
Math.ratio = function(){
    let args = Array.from(arguments);
    let div = Math.gcd.apply( this , args ) ;
    if ( ! div )
            return false ;

    args.forEach(function(v,k){
            args[k] = v / div ;
    });
    return args ;
};
