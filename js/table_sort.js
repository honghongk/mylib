/**
 * 함수로 만드니 구린데
 * 사용 table_sort('th');
 * @param {string} 셀렉터 table구조에 맞는 th만 가능
 * @param {string} 이벤트 실행시 태그에 속성이 추가됨
 */
function table_sort(selector,attr = 'order')
{
    // 정렬타입에 따른 실행
    let type = {
        'asc': function(res){
            res.sort(function(k,v){
                let x = k[1].toLowerCase();
                let y = v[1].toLowerCase();
                if (x < y) return -1;
                if (x > y) return 1;
                return 0;
            });
            return res;
        },
        'desc': function(res){
            res.sort(function(k,v){
                let x = k[1].toLowerCase();
                let y = v[1].toLowerCase();
                if (x < y) return 1;
                if (x > y) return -1;
                return 0;
            });
            return res;
        }
    };

    // 이벤트 추가
    let t = document.querySelectorAll(selector);
    for (let n of t)
        n.addEventListener('click',function(){
        
    
            // 적용범위 찾기
            let th = this;
            let tr = th.closest('tr').querySelectorAll('th');
            
    
            // 정렬타입 찾고 세팅
            let o = th.getAttribute(attr);
            let check = Object.keys(type);
            o = !o ? check[0] : check[check.indexOf(o) + 1] ?? check[0];
            th.setAttribute(attr , o)
    
            // 세로 몇번째 인지 찾기
            let i = 0;
            for (const r of tr)
            {
                if( this.isSameNode(r) )
                    break;
                i ++;
            }
    
            // 데이터 부분 찾기
            let tbody = th.closest('table').querySelector('tbody');
            tr = tbody.querySelectorAll('tr');
    
            // 기준열 데이터들 담기
            let arr = {};
            let n = 0;
            for (const r of tr) {
                let td = $(r).find('td');
                let tt = td[i];
                arr[n] = tt.innerHTML;
                n ++;
            }
            let res = [];
            for (const r  in arr)
                res.push([r, arr[r]]);
    
    
            // 타입에 따라 정렬하기
            if ( ! type[o] )
                throw '정렬 타입이 없음';
    
            // 데이터 정렬하고 적용
            res = type[o](res);
            for (const i of res)
                tbody.append(tr[i[0]]);
        });
}
