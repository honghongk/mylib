'use strict';

/**
 * 큰 수 부터 나열 sort 거꾸로
 * @returns {Array}
 */
Array.prototype.descend = function(){
    return this.sort(function(a,b){return b-a;});
};

/**
 * https://stackoverflow.com/questions/1187518/how-to-get-the-difference-between-two-arrays-in-javascript
 * 공통된 요소만 반환한다
 * @returns {Array}
 */
Array.prototype.intersection = function(){
    let res = this;
    Object.entries(arguments).forEach(arr => {
        res = arr[1].filter(x => res.includes(x));
    });
    return res;
};

/**
 * https://stackoverflow.com/questions/1187518/how-to-get-the-difference-between-two-arrays-in-javascript
 * 자신만 가지고있는 요소를 반환한다
 * @returns {Array}
 */
Array.prototype.diff = function(){
    let res = this;
    Object.entries(arguments).forEach(arr => {
        res = res.filter(x => !arr[1].includes(x));
    });
    return res;
};

/**
 * https://stackoverflow.com/questions/1187518/how-to-get-the-difference-between-two-arrays-in-javascript
 * 공통된것 이외 모든 요소를 합쳐서 반환한다
 * @returns {Array}
 */
Array.prototype.symmetric = function(){
    let res = this;
    Object.entries(arguments).forEach(arr => {
        res = res.filter(
            x => !arr[1].includes(x)
        ).concat(
            arr[1].filter(
                x => !res.includes(x)
            )
        );        
    });
    return res;
};



let a = [1,25,22,54,54,73,66,44,42,31,3,4];
let b = [53,46,5,3,4,67,5,4,45,3,4,44,99];
let c = [1,3,5,4];

console.log(a.descend(b,c))
console.log(a.diff(b,c))
console.log(a.intersection(b,c))
console.log('---------------------------------------------')
console.log(a.symmetric(b,c))