'use strict';

/**
 * querySelectorAll 했을때 나오는 객체
 * 마지막 노드를 반환
 * @returns 
 */
NodeList.prototype.pop = function(){
    return [].slice.call(this).pop();
};