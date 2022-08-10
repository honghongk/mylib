/**
 테이블하고 html하고 서로 세팅하기 쉽게
 
 
 
 돌아가는 코드 효율은 별로인듯
 */
const tree = (function(){

    function strtohtml(str)
    {
        let tmp = document.createElement('div');

        // tr td 등 안맞는거는 따로 처리해야함
        tmp.insertAdjacentHTML( 'beforeend', str );
        return [].slice.call(tmp.children).pop();
    }


    // 공통
    class common
    {
        // 속성 키워드

        // 공통
        id;
        // html 용
        key = '';
        child;

        // 테이블용
        parent;
        order;

        // 핵심데이터
        object;

        constructor(config)
        {
            for (let k in config)
                if ( this.hasOwnProperty(k) )
                    this[k] = config[k];
        }


        /**
         * @see aa 지금은 테이블만 적용됨
         */
        get json()
        {
            return JSON.stringify(this.object);
        }
    }

    class html extends common
    {
        // 템플릿
        node;
        container;

        constructor ( config )
        {
            super(config);
            for (let k in config)
            {
                if ( ! this.hasOwnProperty(k) )
                    throw 'html 항목 설정 못하는거 : '+k;
                this[k] = config[k];
            }

            this.object = this.fromString(this.container);
        }


        fromString(str)
        {
            return strtohtml(str);
        }

        render(template, data)
        {
            return Mustache.render(template,data);
        }


        // 같은 컨테이너인지 확인
        isSameContainer(n,m)
        {
            // 태그 이름과 n.attributes 비교
            if ( n.tagName != m.tagName )
                return false;

            if ( n.attributes.length != m.attributes.length )
                return false;

            for (let attr of [].slice.call(n.attributes))
            {
                if ( attr.value != m.attributes[attr.name].value )
                    return false;
            }

            return true;
        }

        /**
         * e에서 target 찾기
         * @param {*} e html element
         * @param {*} target int
         * @returns 
         */
        #findTarget(e,target)
        {
            let selector = '['+[this.key,this.id].join('-')+'="' + target + '"]';
            return e.querySelector(selector);
        }


        /**
         * e에서 이미있는 container 찾거나 새로 만들고 e에 추가
         * @param {*} e 
         * @returns 
         */
        #findContainer(e)
        {
            let container = this.fromString(this.container);
            container = this.isSameContainer(container , e.lastElementChild)
                ? e.lastElementChild
                : container;
            e.append(container);
            return container;
        }


        /**
         * 데이터를 html로
         * @param {*} data 
         * @returns 
         */
        nodeFromData(data)
        {
            let attr_id = [this.key,this.id].join('-');
            let node = this.fromString(this.render(this.node,data));
            node.setAttribute(attr_id, data[this.id]);
            return node;
        }

        /**
         * 데이터를 html로
         * 이미 있다면 있는거 반환
         * @param {*} data 
         * @returns 
         */
        fromData(data)
        {
            let attr_id = [this.key,this.id].join('-');
            let selector = '['+attr_id+'="'+data[this.id]+'"'+']';
            let node;
            node = this.object.querySelector(selector);
            if ( ! node )
                node = this.fromString(this.render(this.node,data));
            node.setAttribute(attr_id, data[this.id]);
            return node;
        }


        /**
         * append prepend 공통
         * @param {*} data 
         * @param {*} target 
         * @param {*} method 
         * @returns 
         */
        #addChild ( data, target, method )
        {
            let parent = this.object;
            if ( target )
            {
                parent = this.#findTarget(parent,target);
                if ( ! parent )
                    throw 'html 상위노드 못찾음'
                parent = this.#findContainer(parent);
            }

            let node = this.fromData(data);
            return parent[method](node);
        }


        /**
         * after before 공통
         * @param {*} data 
         * @param {*} target 
         * @param {*} method 
         * @returns 
         */
        #addSibling(data, target, method)
        {
            if ( ! target )
                throw 'target 필수';
            let parent = this.object;
            target = this.#findTarget(parent,target);
            if ( ! target )
                throw '대상 찾지 못함';

            let node = this.fromData(data);
            return target[method](node);
        }

        append(data, target = data[this.parent])
        {
            return this.#addChild(data,target,'append');
        }

        prepend(data, target = data[this.parent])
        {
            return this.#addChild(data,target,'prepend');
        }

        after(data, target)
        {
            return this.#addSibling(data,target,'after');
        }

        before(data, target)
        {
            return this.#addSibling(data,target,'before');
        }


        /**
         * element 새로 만들어서 입력
         * @see {*} 지금 노드에서 온전히 데이터만 바꿀 수 없어서 데이터 온전히 가져와야함
         * @param {*} data 
         * @param {*} target 
         * @returns 
         */
        update ( data, target )
        {
            let n = this.nodeFromData(data);
            let b = this.#findTarget(this.object,target);

            // 자식노드
            let nc = this.#findContainer(n);
            let bc = this.#findContainer(b);
            b.replaceWith(n);
            nc.replaceWith(bc);
        }


        /**
         * 삭제
         * @param {*} id 
         */
        remove(id)
        {
            this.#findTarget(this.object,id).remove();
        }
    }


    /**
     * @fix 이거 안맞는데 일단 사용안해서 나중에
     * 테이블의 object하고 겹침
     */
    // class object extends common
    // {
    //     constructor(config)
    //     {
    //         super(config);
    //         for ( let k in config )
    //         {
    //             if ( ! this.hasOwnProperty ( k ) )
    //                 throw '설정못하는값 : '+k;
    //             this[k] = config[k];
    //         }

    //         // https://stackoverflow.com/questions/2980763/javascript-objects-get-parent
    //         // 상위 찾을라면 미리 세팅해놔야함
    //         this.object = {};
    //     }

        
    //     /**
    //      * @param {*} object 
    //      * @param {*} callback 
    //      * @returns 
    //      */
    //     recursive(object, callback, path = [])
    //     {
    //         if ( Object.keys(object).length == 0 )
    //             return;
    //         for (let k in object)
    //         {
    //             let current = path.concat([k]);
    //             callback(k,object[k],current);

    //             // 루프중 삭제
    //             if ( ! object[k] )
    //                 break;
    //             if ( object[k][this.child] )
    //                 return path.concat(this.recursive(object[k][this.child],callback,current));
    //         }
    //     }


    //     /**
    //      * 노드 추가
    //      * 이미 있다면 기존거 삭제 후 새로 추가
    //      * @param {*} data 
    //      * @param {*} target 
    //      */
    //     append ( data, target = data[this.parent] )
    //     {
    //         let parent = this.object;

    //         // 이미 있는거 찾기
    //         let id = data[this.id];
    //         this.recursive(parent,function(k,v,path){
    //             if ( k == id )
    //             {
    //                 // 경로에 있는거 삭제
    //                 let before = parent;
    //                 for (let i of path)
    //                 {
    //                     if ( path.indexOf(i)+1 == path.length )
    //                     {
    //                         delete before[i];
    //                         break;
    //                     }
    //                     before = before[i]['child']
    //                 }
    //             }
    //         });
            
    //         if ( target )
    //         {
    //             // child 키워드
    //             let ck = this.child;
    //             // 재귀로 parent 찾기
    //             this.recursive(parent,function(k,v){
    //                 if ( k != target )
    //                     return;
    //                 if ( ! v[ck] )
    //                     v[ck] = {};
    //                 parent = v[ck];
    //             });
    //         }
    //         parent[data[this.id]] = data;
    //     }


    //     // order 정해져서 옴 + js 순서 못바꿈
    //     prepend(data, target = data[this.parent])
    //     {
    //         return this.append(data,target)
    //     }

    //     after(data, target)
    //     {
    //         if ( ! target )
    //             throw 'target 필수';
    //         let parent = this.object
    //         let id = data[this.id];

    //         let pk = this.parent;
    //         // 찾은부분의 상위에 append
    //         // parent 세팅은 따로 안했기 때문에 child 얻어놓고 id 일치하는지
    //         this.recursive(parent,function(k,v){
    //             if ( k != target )
    //                 return;
    //             // 최상위
    //             if ( ! v[pk] )
    //                 parent[id] = data;
    //             else
    //             {
    //                 throw '어딘가의 하위일때 찾기 ㄱㄱㄱ'
    //             }
    //         });
    //     }


    //     before(data, target)
    //     {
    //         if ( ! target )
    //             throw 'target 필수';
    //         let parent = this.object
    //         let id = data[this.id];

    //         let pk = this.parent;

    //         // 찾은부분의 상위에 추가
    //         // parent 세팅은 따로 안했기 때문에 child 얻어놓고 id 일치하는지
    //         this.recursive(parent,function(k,v){
    //             if ( k == target )
    //             {
    //                 if ( ! v[pk] )
    //                     parent[id] = data;
    //             }
                
    //         });
    //     }
    // }


    class table extends common
    {
        constructor(config)
        {
            super(config);
            this.object = [];
        }


        /**
         * 추가
         * 이미있는거는 삭제 후 추가
         * @param {*} data 
         * @param {*} target 
         * @returns 
         */
        append(data, parent = data[this.parent])
        {
            // 기존것 삭제
            this.remove(data[this.id]);
            data[this.parent] = parent;
            return this.object.push(data);
        }

        prepend(data, parent = data[this.parent])
        {
            return this.append(data, parent);
        }

        /**
         * 상위 노드 찾고 넘기기
         * @param {*} data 
         * @param {*} target 
         * @returns 
         */
        after(data, target)
        {
            let parent;
            for (const i of this.object)
            {
                if (target != i[this.id])
                    continue;
                parent = i[this.parent]
            }
            return this.append(data,parent);
        }

        before(data, target)
        {
            return this.after(data, target);
        }


        update(data, target)
        {
            let row = {};
            for (const i of this.object)
            {
                if (target == i[this.id])
                {
                    row = i;
                    break;
                }
            }

            for (const k in data)
                row[k] = data[k];
            return row;
        }


        /**
         * 아이디 기준으로 해당노드 포함 하위노드 다 가져오기
         * @param {*} id 
         */
        #getTree(id)
        {
            let o = this.object;
            let res = [];
            for (const i of o)
            {
                if (id == i[this.id])
                    res.push(i);
                else if (id == i[this.parent])
                    res = res.concat(this.#getTree(i[this.id]));
            }
            return res;
        }


        /**
         * 루프중에 삭제하면 삭제한만큼 루프빼먹는거 있어서 걸러낸 다음 비교
         * 삭제
         * @param {*} id 
         */
        remove(id)
        {
            return this.object = this.object.diff(this.#getTree(id));
        }
    
        
    }

    // 동적 생성용 네임스페이스 역할
    let ns = {
        table:table,
        html:html,
        // object:object
    };


    return class tree extends common
    {
        // 오브젝트들
        html;
        object;
        table;


        /**
         * 내부적으로 필요한거 세팅
         */
        constructor ( config )
        {
            // 공통, 클래스별 분리
            let kw = {};
            let o = {};
            for (let k in config)
            {
                let v = config[k];
                if (typeof v != 'object')
                    kw[k] = v;
                else
                    o[k] = v;
            }

            // 지금 클래스에 세팅
            
            super(kw);
            for (let k in ns)
                this[k] = new ns[k](Object.assign({},kw,o[k]));
        }


        /**
         * 노드의 아이디를 추가
         * 데이터 많아지면 거꾸로 루프
         * 테이블형식의 데이터 기준으로
         * @param {*} node
         * @returns 
         */
        getNodeID(node)
        {
            if ( node[this.id] )
                return node[this.id];
            let arr = [0];
            for (let n of this.table.object)
                arr.push(n[this.id]);

            let id = Math.max.apply(null,arr) + 1;
            return id;
        }


        /**
         * 비교해서 order를 추가, 수정
         * 테이블형식의 데이터 기준으로
         * @param {*} target 기준노드 아이디
         * @param {*} type 메서드타입
         */
        getNodeOrder(target, type)
        {
            target = Number(target);
            if ( isNaN(target))
                target = 0;

            let arr = [0];
            if ( type == 'prepend')
                arr = [1];
            let t = this.table.object;

            for (let i of t)
            {
                if ( ! i[this.parent] )
                    i[this.parent] = 0;
                // append, prepend 상위와 비교
                if ( type == 'append' && i[this.parent] == target )
                    arr.push(i[this.order]);
                else if ( type == 'prepend' && i[this.parent] == target)
                {
                    i[this.order]++;
                    arr.push(i[this.order]);
                }
                else if ( type == 'before' && target <= i[this.order] )
                    i[this.order] ++;
                else if ( type == 'after' && target < i[this.order] )
                    i[this.order] ++;
            }

            if (type == 'after')
                return target + 1;
            else if ( type == 'before')
                return target;
            else if ( type == 'append' )
                return Math.max.apply(null,arr) + 1;
            else if ( type == 'prepend' )
                return Math.min.apply(null,arr);
        }


        /**
         * @param {} data kv 데이터
         * @param {} target 노드 아이디
         */
        update ( data, target )
        {
            data = this.table.update(data,target);
            this.html.update(data,target);
        }


        /**
         * 삭제
         * @param {*} id
         */
        remove ( id )
        {
            for (let o of Object.keys(ns))
                this[o].remove(id);
        }


        /**
         * 노드 추가
         * @param {*} data
         * @param {*} parent 상위 아이디
         */
        append ( data, parent = data[this.parent])
        {
            data[this.id] = this.getNodeID(data);
            data[this.order] = this.getNodeOrder(parent, 'append');
            for (let o of Object.keys(ns))
                this[o].append(data, parent);
            return data;
        }
    
        prepend ( data, parent = data[this.parent] )
        {
            data[this.id] = this.getNodeID(data);
            data[this.order] = this.getNodeOrder(parent, 'prepend');
            for (let o of Object.keys(ns))
                this[o].prepend(data, parent);
            return data;
        }
    
        after ( data, target )
        {
            data[this.id] = this.getNodeID(data);
            data[this.order] = this.getNodeOrder(target, 'after');
            for (let o of Object.keys(ns))
                this[o].after(data, target);
            return data;
        }
    
        before ( data, target )
        {
            data[this.id] = this.getNodeID(data);
            data[this.order] = this.getNodeOrder(target, 'before');
            for (let o of Object.keys(ns))
                this[o].before(data, target);
            return data;
        }
    }
})();
