/**
 * tbody별 컬럼별 숫자 합계
 * @return {Array<Array<Integer>>}
 */
HTMLTableElement.prototype.sum = function(){

    // table의 tbody 선택   
    let tbody = this.querySelectorAll('tbody');

    // 담아둘 값
    let res = [];

    // tbody 안에 tr 전체
    tbody.forEach(tb => {
        let arr = [];
        let tr = tb.querySelectorAll('tr');
        for (const row of tr)
        {
            // tr에 세로 td 전체 합계
            let r = row.querySelectorAll('td')
            for (let i = 0; i < r.length; i++)
            {
                const element = r[i];
                if(arr[i] == undefined) 
                    arr[i] = Number(element.innerHTML)
                else
                    arr[i] += Number(element.innerHTML)
            }
        }
        res.push(arr);
    });

    return res;
}

/**
 * tr 안의 값을 배열로 리턴
 * @returns {Array}
 */
HTMLTableRowElement.prototype.toArray = function(){
    let res = [];
    this.querySelectorAll('td,th').forEach(cell=>{
        res.push(cell.innerText.trim())
    })
    return res;
}

/**
 * tbody안의 값을 Object로 리턴
 * @param {Array} col 
 * @returns {Array<String: String>}
 */
HTMLTableSectionElement.prototype.toObject = function(col){
    if ( this.tagName != 'TBODY' )
        console.warn('tbody 맞춤 함수임')

    // 따로 설정하는 컬럼 없다면 thead
    if ( ! col )
        col = this.closest('table').querySelectorAll('thead')[0].toArray()[0];
    return this.toArray().map(v=>{
        return v.map( ( vv, i)=>{
            let item = {};
            item[col[i]] = vv;
            return item;
        });
    });
};

/**
 * thead tbody tfoot 안의 값을 배열로 리턴
 * @returns {Array<String>}
 */
HTMLTableSectionElement.prototype.toArray = function(){
    let res = [];
    this.querySelectorAll('tr').forEach(tr=>{
        res.push(tr.toArray());
    })
    return res;
}

/**
 * 테이블의 첫번째 thead안의 값을 배열로 리턴
 * @returns {Array<String>}
 */
HTMLTableElement.prototype.column = function(){
    return this.querySelectorAll('thead')[0].toArray()[0];
}

/**
 * tbody안의 값을 행렬 바꿔서 Object로 리턴
 * @param {Array} col 설정할 컬럼
 * @returns {String: Array<String>}
 */
HTMLTableElement.prototype.transposeToObject = function(col){

    // 따로 설정하는 컬럼 없다면 thead
    if ( ! col )
        col = this.column();

    let data = this.querySelectorAll('tbody')[0].toArray();

    let res = {};
    for (let row of data) {
        row.forEach((v,i)=>{
            res[col[i]] ??= [];
            res[col[i]].push(v)
        })
    }
    return res;
};

/**
 * 테이블을 컬럼 기준으로 정렬한다
 * @param {String|Integer} col 설정할 컬럼
 */
HTMLTableElement.prototype.asc =
HTMLTableElement.prototype.sort = function(col){

    if ( typeof col == 'undefined' )
        throw '컬럼 선택해야함 int, colname';

    // 기준 컬럼 찾기
    let column = this.column();
    let index = isNaN ( col ) ? column.indexOf(col) : col;
    if ( index < 0 || column.length < index )
        throw '컬럼이 맞지 않습니다';
    
    // 체크할 데이터 모으기
    let arr = [];
    this.querySelectorAll('tbody tr').forEach(tr=>{
        arr.push(tr.querySelectorAll(`td, th`)[index]);
    });

    // 정렬
    arr.sort(function(k,v){
        let x = k.outerHTML.toLowerCase();
        let y = v.outerHTML.toLowerCase();
        if (x < y)
            return -1;
        if (x > y)
            return 1;
        return 0;
    });

    // 순서 적용
    for (let td of arr)
    {
        let tr = td.closest('tr');
        td.closest('tbody').append(tr);
    }
}

/**
 * 테이블을 컬럼 기준으로 역정렬한다
 * @param {String|Integer} col 설정할 컬럼
 */
HTMLTableElement.prototype.desc =
HTMLTableElement.prototype.rsort = function(col){

    if ( typeof col == 'undefined' )
        throw '컬럼 선택해야함 int, colname';

    // 기준 컬럼 찾기
    let column = this.column();
    let index = isNaN ( col ) ? column.indexOf(col) : col;
    if ( index < 0 || column.length < index )
        throw '컬럼이 맞지 않습니다';

    // 없으면 초기 순서 세팅
    let arr = [];
    this.querySelectorAll('tbody tr').forEach(tr=>{
        arr.push(tr.querySelectorAll(`td, th`)[index]);
    });

    // 정렬
    arr.sort(function(k,v){
        let x = k.outerHTML.toLowerCase();
        let y = v.outerHTML.toLowerCase();

        if (x > y)
            return -1;
        if (x < y)
            return 1;
        return 0;
    });

    // 적용
    for (let td of arr)
    {
        let tr = td.closest('tr');
        td.closest('tbody').append(tr);
    }
}

/**
 * 사용법
 * table_sort('.table-bordered > thead th', "dddd");
 */
function table_sort(selector, prop = 'order' )
{
    // '.table-bordered > thead th'
    $(document).on('click',selector,function(){

        // 정렬 타입
        let type = [
            'asc', 'desc', 'origin'
        ];
        

        // 현재 정렬타입
        let o = this[prop];
        let th = this.closest('tr').querySelectorAll('th');
        let table = this.closest('table');
        let tr = table.querySelectorAll('tbody tr');
        
        // 기준 인덱스
        let i = 0;

        // 처음 정렬타입 없다면 세팅 아니면 다음정렬
        if ( ! o ) 
            this[prop] = o = type[0];
        else 
            this[prop] = o = type[type.indexOf(o) + 1] ?? type[0];

        // 현재 컬럼 인덱스 찾기
        for (const r of th)
        {
            if( this.isSameNode(r) )
                break;
            i ++;
        }

        // 테이블 원래 정렬을 위한 값 세팅
        let order = 0;
        tr.forEach(tr=>{
            if ( tr[prop] == undefined )
                tr[prop] = order ++;
        })

        // 정렬 적용
        if ( o == 'origin')
        {
            Array.from(tr).sort((k,v)=>{
                let x = k[prop];
                let y = v[prop];
                if (x < y)
                    return -1;
                if (x > y)
                    return 1;
                return 0;
            }).forEach(row=>{
                row.closest('tbody').append(row);
            });
        }
        else
            table[o](i);
    });
}
