
/**
 * 문자 거꾸로 뒤집기
 */
String.prototype.reverse = function(){
    return this.split('').reverse().join('');
};


/**
 * 텍스트 복사
 * 실제 드래그 되는 element에다 입력하고 복사하고 없애야함
 * 모달 떠있으면 수정해야함
 */
String.prototype.copy = function(){
    let element = document.createElement('input');
    element.value = this;
    document.body.appendChild(element);
    element.select();
    document.execCommand("Copy");
    element.remove();
};
