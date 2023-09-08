/**
 * 캔바스 크기를 조절하고 원래 안에 있던 이미지 다시 놓기
 * @see {} 그려져 있던 이미지는 크기를 조절 하지 않아서 잘리거나 여백이 생김
 * @param {float} w 
 * @param {float} h 
 * @returns {HTMLCanvasElement}
 */
HTMLCanvasElement.prototype.size = function(w, h){
    this.ctx ??= this.getContext('2d');
    // let ctx = this.ctx;
    w ??= this.offsetWidth;
    h ??= this.offsetHeight;

    // let img = this.image()
    // let img = ctx.getImageData(0, 0, w, h);
    let img = ctx.getImageData(0, 0, this.width, this.height);
    
    this.setAttribute('width',w);
    this.setAttribute('height',h);

    ctx.putImageData(img,0,0);
    return this;
}


/**
 * 캔바스 크기를 조절하고 원래 안에 있던 이미지 비율 맞춰서 늘리고 다시 놓기
 * @see {} 그려져 있던 이미지는 크기를 조절 하지 않아서 잘리거나 여백이 생김
 * @param {float} w 
 * @param {float} h 
 * @returns {HTMLCanvasElement}
 */
HTMLCanvasElement.prototype.rsize = function(w, h){
    this.ctx ??= this.getContext('2d');
    // let ctx = this.ctx;
    w ??= this.offsetWidth;
    h ??= this.offsetHeight;

    
    // let img = ctx.getImageData(0, 0, w, h);
    let img = ctx.getImageData(0, 0, this.width, this.height);

    let ratio = Math.min(w / img.width, h / img.height);
    
    this.setAttribute('width',w);
    this.setAttribute('height',h);

    ctx.drawImage(img, 0, 0, img.width, img.height, 0, 0, img.width*ratio, img.height*ratio )
    // ctx.putImageData(img,0,0);
    return this;
}

/**
 * 내용을 비운다
 * @returns {HTMLCanvasElement}
 */
HTMLCanvasElement.prototype.clear = function(){
    this.ctx ??= this.getContext('2d');
    this.ctx.clearRect(0, 0, this.offsetWidth, this.offsetHeight);
    return this;
}


/**
 * 마우스로 그리는 이벤트 바인드 하기 ??
 * 이미지 데이터 그려놓기 ???
 */
// HTMLCanvasElement.prototype.draw = function(){
    
// }

/**
 * 그릴 색 선택
 * @param {String} color 색상코드, 단어
 * @returns {HTMLCanvasElement}
 */
HTMLCanvasElement.prototype.color = function(color){
    this.ctx ??= this.getContext('2d');
    // if ( ! color )
    //     color = this.pos.color;
    this.ctx.strokeStyle = color;
    return this;
}

/**
 * 캔바스 안에 이미지 데이터를 리턴한다
 * @returns {ImageData}
 */
HTMLCanvasElement.prototype.image = function(){
    this.ctx ??= this.getContext('2d');
    return this.ctx.getImageData(0, 0, this.offsetWidth, this.offsetHeight);
}


/**
 * 캔바스 안에 이미지 테두리만 따서 리턴한다
 * @see opencv.js 가 필요
 * @returns {ImageData}
 */
HTMLCanvasElement.prototype.canny = function(){

    // 임시 캔바스
    let canvas = document.createElement('canvas');
    let image = cv.imread(this); // canvas에서 이미지 데이터 읽어오기
    let gray = new cv.Mat();

    // BGR 색상 공간에서 그레이스케일로 변환
    cv.cvtColor(image, gray, cv.COLOR_BGR2GRAY);

    // 가우시안 블러 적용
    cv.GaussianBlur(gray, gray, new cv.Size(5, 5), 0);

    // bitwise_not 연산을 적용하여 이미지 반전
    cv.bitwise_not(gray, gray);

    // Canny 에지 검출 수행
    let edge = new cv.Mat();
    cv.Canny(gray, edge, 50, 100);

    // 에지를 반전
    cv.bitwise_not(edge, edge);
    
    // edge Mat 객체를 사용하여 필요한 작업 수행
    cv.imshow(canvas,edge);
    let res = canvas.image();
    canvas.remove();
    return res;
}

/**
 * 영상 이미지 주변배경 제거
 * @todo 미완성
 * @returns {ImageData}
 */
HTMLCanvasElement.prototype.mog = function(){

    // 임시 캔바스
    let canvas = document.createElement('canvas');

    let image = cv.imread(this);
    let fgmask = new cv.Mat(image.height, image.width, cv.CV_8UC1)
    let img = new cv.BackgroundSubtractorMOG2(500, 16, true);
    // // cv.threshold(image, gray, 127, 255, cv.THRESH_BINARY)
    img.apply(image, fgmask);

    // 이미지 데이터만 빼고 리턴
    cv.imshow(canvas, fgmask);
    let res = canvas.image();
    canvas.remove();
    return res;
}


/**
 * drop 이벤트를 세팅한다
 * @returns {HTMLCanvasElement}
 */
HTMLCanvasElement.prototype.drop = function(){

    this.ctx ??= canvas.getContext('2d');

    // 파일 드래그 드랍
    this.addEventListener('dragover',function(e){
        e.preventDefault();
        e.stopPropagation();
    });
    this.addEventListener('drop',async function(e){

        e.preventDefault();
        e.stopPropagation();

        // 파일을 img 태그에 놓고
        let file = e.dataTransfer.files[0];
        let base = await file.base64();
        let img = `<img src="${base}">`.toElement()[0];
        img.onload = function(){
            
            this.ctx.drawImage(img,0,0);
            img.remove();
        }
    });
    return this;
}

/**
 * 업로드 파일을 선택하게 하고 canvas에 그린다
 * @returns {HTMLCanvasElement}
 */
HTMLCanvasElement.prototype.upload = function(){

    let canvas = this;
    this.ctx ??= canvas.getContext('2d');
    
    // 안보이는 input태그 생성
    let tmp = document.createElement('table');
    tmp.innerHTML = '<input type="file" accept="image/*" style="display: none;">';
    let input = tmp.children[0];


    // input 업로드시 이미지 그리기
    input.addEventListener('change',async function(e){
        let file = this.files[0];
        let base = await file.base64();
        let img = `<img src="${base}">`.toElement()[0];
        img.onload = function(){
            let ctx = canvas.getContext('2d');
            ctx.drawImage(img,0,0);
            img.remove();
        }
    })
    input.click();

    return this;
}







// let paint = {
//     pos: {
//         drawable: false,
//         color: 'black',
//         x: -1,
//         y: -1,
//     },
//     /**
//      * 이벤트 걸기
//      * @param {canvas} 캔바스 태그
//      */
//     bind: function(canvas){

//         this.element = canvas;
//         // ctx 세팅
//         this.ctx = canvas.getContext('2d');

        
//         // let rect = canvas.getBoundingClientRect();
        
//         // 이벤트 세팅
//         let self = this;

//         // attr 세팅해주기
//         self.size(canvas);
//         this.clear(canvas);

//         document.querySelector('#clear').addEventListener('click',function(){
//             self.clear(canvas);
//         });

//         window.addEventListener('resize',function(){
//             return self.size(canvas);
//         });
        
//         canvas.addEventListener('mousedown', function(e){
//             return self.draw.start(e,self);
//         });
//         canvas.addEventListener('mousemove', function(e){
//             return self.draw.render(e,self);
//         });
//         canvas.addEventListener('mouseup', function(e){
//             return self.end(e,self);
//         });
//         canvas.addEventListener('mouseout', function(e){
//             return self.end(e,self);
//         });
//         canvas.addEventListener('touchstart', function(e){
//             return self.touch.start(e,self);
//         });
//         canvas.addEventListener('touchmove', function(e){
//             // console.log(e)
//             e.preventDefault();
//             return self.touch.render(e,self);
//         });
//         canvas.addEventListener('touchend', function(e){
//             return self.end(e,self);
//         });
//     },

//     /**
//      * attr 사이즈 조절해주기
//      * @fix static 한거와 인스턴스 느낌으로 수정하기
//      */
//     size: function(canvas){
//         let ctx = canvas.getContext('2d');
//         // let ctx = this.ctx;

//         let img = ctx.getImageData(0, 0, canvas.offsetWidth, canvas.offsetHeight);
        
//         canvas.setAttribute('width',canvas.offsetWidth);
//         canvas.setAttribute('height',canvas.offsetHeight);

//         ctx.putImageData(img,0,0);

//     },
//     draw:{
//         start: function(e,self){
//             let [x,y] = [ e.offsetX, e.offsetY ] ;
//             self.pos.drawable = true;
//             self.ctx.beginPath();
//             self.pos.x = x;
//             self.pos.y = y;
//             self.ctx.moveTo(x, y);
//         },
//         render: function(e,self){

//             if ( ! self.pos.drawable )
//                 return;

//             let [x, y] = [ e.offsetX, e.offsetY ] ;
//             self.ctx.lineTo(x, y);
//             self.pos.x = x;
//             self.pos.y = y;
            
//             self.ctx.stroke();
//         }
//     },
//     touch:{
//         start: function(e,self){
//             let [x, y] = [e.touches[0].pageX, e.touches[0].pageY - self.ctx.canvas.offsetTop];
//             self.pos.drawable = true;
//             self.ctx.beginPath();
//             self.pos.x = x;
//             self.pos.y = y;
//             self.ctx.moveTo(x, y);
//         },
//         render: function(e,self){
//             if ( ! self.pos.drawable )
//                 return;

//             for (let t of e.touches) {
//                 let [x, y] = [t.pageX, t.pageY - self.ctx.canvas.offsetTop];
//                 self.ctx.lineTo(x, y);
//                 self.pos.x = x;
//                 self.pos.y = y;
//                 self.ctx.stroke();
//             }
//         }
//     },
//     end: function(e,self){
//         self.pos.drawable = false;
//         self.pos.x = -1;
//         self.pos.y = -1;
//     },

   
    
// };

