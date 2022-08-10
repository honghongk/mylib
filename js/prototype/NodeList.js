'use strict';

/**
 * querySelectorAll 했을때 나오는 객체
 * 마지막 노드를 반환
 * @returns 
 */
NodeList.prototype.pop = function(){
    return [].slice.call(this).pop();
};


/**
 * 하위 노드리스트 getter
 */
Object.defineProperty(NodeList.prototype,'childNodes',{
    get: function(){
        let res;
        for (let i of this)
        {
            let ii = i.childNodes;
            if ( ! res )
                res = ii;
            else
                Object.assign(res,ii);
        }
        return res;
    },
    enumerable:false
});

/**
 * 노드리스트 안에 있는지 확인
 * @param {*} element 
 */
NodeList.prototype.in = function(element){
    for (let i of this)
    {
        if ( i.isEqualNode(element) )
            return true;
    }
    return false;
};


/**
 * @see {} 싹다 돌기 때문에 렉걸릴 수 있음
 * @fix 공백차이나도 다른취급인듯 공백은 상관없도록
 * 전체 하위 노드에 있는지 확인
 * @param {*} element 
 */
NodeList.prototype.find = function(element){

    let res = false;

    for (let i of this)
    {
        for (let ii of i.childNodes) {

            if ( ii.childNodes.length == 0 )
                continue;
            if ( ii.isEqualNode(element) )
                return true;
            res = ii.childNodes.find(element);
        }
    }

    return res;
};


NodeList.prototype.serialize = function(){

    let res = {};
    for (let i of this)
    {
        if ( ! i.querySelectorAll )
            continue;
        let node = i.querySelectorAll('[name]');
        for (let n of node) {

            // input일때 
            // 나중에 select 등 다른거일때 수정
            let k = n.name
            if ( k.reverse().indexOf('[]'.reverse()) == 0 )
            {
                // [] 붙으면 배열로 처리
                k = k.substr(0, k.length - '[]'.length );
                if ( ! res[k] )
                    res[k] = [];
                res[k].push(n.value);
            }
            else
                res[k] = n.value;
        }
    }
    return res;
};


/**
 * @see {} 싹다 돌기 때문에 오래걸리거나 렉걸릴 수 있음
 * @param {string} selector 쿼리 셀렉터
 * @returns 
 */
NodeList.prototype.querySelectorAll = function(selector){
    let res;
    for (const i of this) {

        let ii = i.querySelectorAll(selector);;
        if ( ! res )
            res = ii;
        else
            Object.assign(res,ii);
    }
    return res;
};

/**
 * 노드 리스트를 복사한다
 * @param {boolean} option 하위노드 복사 여부
 */
NodeList.prototype.cloneNode = function(option = true){
    let res = document.createDocumentFragment();
    for (let i of this)
        res.append(i.cloneNode(option));
    return res.childNodes;
};
