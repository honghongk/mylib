/**
 * 구글엑셀 매크로에서 쓰는용도나 엑셀관련으로
 */


/**
 * 0: A , 1: B ... 27: AA, 28: AB ...
 * 좌표 65 ~ 90
 * @param int 0 이상 영어좌표 얻기
 */
function getpos(i)
{
  let res ;
  if ( i > 25 )
  {
    let rest = i%26;
    res = getpos(Math.floor(i/26)-1)+getpos(rest);
  }
  else
    res = String.fromCharCode(i + 65);
  return res;
}
