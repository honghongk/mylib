
/**
 * 문자 거꾸로 뒤집기
 */
String.prototype.reverse = function(){
    return this.split('').reverse().join('');
};


/**
 * 텍스트 복사
 * 실제 드래그 되는 element에다 입력하고 복사하고 없애야함
 * @fix 모달 떠있으면 수정해야함
 */
String.prototype.copy = function(){
    let element = document.createElement('input');
    element.value = this;
    document.body.appendChild(element);
    element.select();
    document.execCommand("Copy");
    element.remove();
};


/**
 * element로 변경하기
 */
String.prototype.toElement = function(){

    // 임시 상위 태그
    let arr = ['tr','tbody','table','body'];
    for(let t of arr)
    {
        t = document.createElement(t);
        t.innerHTML = this.trim();

        // 하위태그 만들어졌다면 리턴
        let res = t.childNodes;
        if ( res.length > 0 )
            return res;
    }
};

/**
 * https://jamssoft.tistory.com/274
 * @fix 완벽하지 않고 unescape가 없어질수도 있으니 수정
 * 무조건 33%만 커지고 아스키 코드 범위내 문자로 
 */
String.prototype.base64_encode = function(){
    return window.btoa(unescape(encodeURIComponent( this )));
}

/**
 * https://jamssoft.tistory.com/274
 */
String.prototype.base64_decode = function(){
    return decodeURIComponent(escape(window.atob( this )));
}
