
/**
 * https://stackoverflow.com/questions/1184624/convert-form-data-to-javascript-object-with-jquery
 * 폼 데이터를 다차원 오브젝트 구조화 한다
 * @returns {object} 다차원 오브젝트
 */
HTMLFormElement.prototype.object = function(){
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
    let data = new FormData(this);
    for (let row of data)
    {
        let [k,v] = row;
        // 유효성 검사
        if ( ! patterns.validate.test(k) )
            throw '데이터키 invalid';

        // 키 분할
        let match = k.match(patterns.key),
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
                res = Object.assign(res,merge);
        }
    }
    return res;
};
