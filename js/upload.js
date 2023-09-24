/**
 * 초기화
 * @param {String|Object|Array<Object>} target selector, DOM 오브젝트 등
 */
function Upload(target)
{
    // 허용할 파일 input accept와 같게
    this._accept = [];

    // 적용 element
    if ( typeof target == 'string')
        this.target = document.querySelectorAll(target);
    else if ( ! target.length )
        this.target = [target];
}

/**
 * 초기화
 * @param {String|Object|Array<Object>} target selector, DOM 오브젝트 등
 * @returns 
 */
Upload.target = function(target){
    return new Upload(target);
};


/**
 * 허용할 파일 설정
 * @param {*} accept
 * @returns 
 */
Upload.prototype.accept = function(accept){
    // 데이터 얻기
    if ( ! accept )
        return this._accept.join(', ');

    // 허용할 파일 세팅
    if ( typeof accept == 'string' )
        accept = accept.split(/\s+|,/);
    this._accept = accept;
    return this;
};


/**
 * 드랍된 객체 순회
 * @see {} await 때문에 루프끊김, promise resolve도 루프끊김, 상위에서 루프 돌아야함
 * @param {*} i 엔트리
 * @param {*} func 콜백함수
 */
Upload.recursive = async function( i , func ) {
    if ( func != null && func != undefined && ! ( func instanceof Function ) )
        throw 'func는 함수거나 null, undefined 여야함';
    let res = [];
    for ( let ii of i )
    {
        // 엔트리로 일괄처리
        let entry = ii.webkitGetAsEntry
            ? ii.webkitGetAsEntry()
            : ii ;

        // 파일이 맞으면
        if ( entry.isFile )
        {
            let file = await (()=>{
                return new Promise((resolve, reject) => {
                    entry.file(async (file)=>{
                        file = new File(
                            [file.slice(0, file.size, file.type)] ,
                            entry.fullPath ,
                            {type: file.type}
                        );
                        resolve(file);
                    });
                })
            })();

            await res.push(file)
            if ( func )
                func ( file );
        }
        // 디렉토리면
        else if ( entry.isDirectory )
        {
            // 크롬 100개 제한 있어서 재귀
            let reader = entry.createReader();
            let getEntry = async ()=>{
                let res = [];
                let files = await (()=>{
                    return new Promise((resolve, reject) => {
                        reader.readEntries(async li=>{
                            if ( li.length > 0 )
                            {
                                // 재귀 + 결과합치기
                                let et = await getEntry();
                                let r = await Upload.recursive(li, func);
                                et.push(...r)
                                resolve(et);
                            }
                            else
                                resolve([]);
                        },err=>{
                            console.log('err',err);
                            reject(err);
                        });
                    })
                })();
                res.push(... files);
                return res;
            };
            let all = await getEntry();
            res.push(...all)
        }
    }
    return res;
};


/**
 * 클릭 이벤트를 추가하고 업로드 폴더, 파일 데이터 가져옴
 * @see {} input file 기본 이벤트 특성상 폴더는 못함
 * @param {*} func 
 * @returns {Upload}
 */
Upload.prototype.click = function(func){

    // 허용할 파일
    let accept = this.accept();
    // 담을 데이터
    let arr = [];
    for (let t of this.target)
    {
        t.addEventListener('click',function(e){
            // if ( ! this.isSameNode(e.target) )
            //     return;
            let input = document.createElement('input'); 
            input.setAttribute('type','file');
            input.setAttribute('multiple', true);
            input.setAttribute('accept', accept);

            input.addEventListener('change',function(e){
                for (let f of this.files)
                    arr.push(f);
                // 콜백 실행
                if ( func )
                    func(arr);
                input.remove();
            });
            input.click();
        });
        
    }
    return this;

}

/**
 * 드롭 이벤트를 걸고 드롭된 파일, 폴더 재귀적으로 다 빼오기
 * @see {} 빈 폴더는 안가져옴
 * @param {Function} func 콜백함수
 * @returns {Upload}
 */
Upload.prototype.drop = function(func){

    // 허용할 파일 타입, 비어있으면 모두 허용
    let accept = this.accept();
    accept = accept.length > 0 ? this.accept().split(',').map( v => v.trim() ) : [];

    for (let t of this.target)
    {
        t.addEventListener('dragover',e=>{
            e.preventDefault();
        });
        // 드롭이벤트
        t.addEventListener('drop',async e=>{

            e.preventDefault();

            // 파일 모아서 그룹으로
            let file = [];

            for (let i of e.dataTransfer.items)
                file.push(Upload.recursive([i]));

            file = Promise.all(file);

            file.then(res=>{
                // 콜백 실행
                if ( func )
                    func(res);
            });
        });
    }
    return this;
}
