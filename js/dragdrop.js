class dragdrop
{
    constructor()
    {
        this.node;
    }

    
    /**
     * @param { selector:string, callback: function}
     */
    set drag ( o )
    {
        // 이벤트 중복 때문에 전체에 걸기
        document.addEventListener('mousedown',e=>{
            let node = document.querySelectorAll(o.selector);

            // 드래그 노드 찾기
            this.node = e.target.closest(o.selector);
            node.forEach(v=>{

                // this.node 수정해야함
                v.setAttribute('draggable',true);
                // drop으로 데이터 전송
                // console.log(e.originalEvent.dataTransfer.setData('k','test'));
                if ( e.target.querySelectorAll(o.selector).length == 0 )
                    return;
                if ( o.callback )
                    o.callback(e);
            });
        });
    }
    
    set drop ( o )
    {
        // 이벤트 중복 방지
        document.addEventListener('mousedown',e=>{

            // 범위내에서 노드 빼고 이벤트 논 해야됨
            // v.style.pointerEvents = 'none';
            let node = document.querySelectorAll(o.selector);
            node.forEach(v=>{
                v.setAttribute('droppable',true);
                // console.log(v)
            });
            // throw 'asdf';
        });

        document.addEventListener('dragenter',e=>{
            if ( e.target.closest(o.selector) == null )
                return;
            o.callback(e,e.target.closest(o.selector),this.node);
        });

        document.addEventListener('dragover',e=>{
            // 필수, 기본이벤트 막아야 드롭됨
            e.preventDefault();
            if ( e.target.closest(o.selector) == null )
                return;
            o.callback(e,e.target.closest(o.selector),this.node);
        });

        document.addEventListener('dragleave',e=>{
            if ( e.target.closest(o.selector) == null )
                return;
            o.callback(e,e.target.closest(o.selector),this.node);
        });

        document.addEventListener('drop',e=>{
            if ( e.target.closest(o.selector) == null )
                return;
            o.callback(e,e.target.closest(o.selector),this.node);
        });
    }
}
