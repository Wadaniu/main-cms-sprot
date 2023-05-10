

var btnA = document.getElementsByClassName('leftListA')

// if(btnA){
    for (let k = 0; k < btnA.length; k++) {
        localStorage.removeItem('btnNum')
        btnA[k].onclick=function(){
            localStorage.setItem('btnA','btmRed')
            localStorage.setItem('btnNum',`${k}`)
        }
    }
    var col=localStorage.getItem('btnA')
    var btnNum=localStorage.getItem('btnNum')
    if(btnNum && col){
        setInterval(function(){
            btnA[btnNum].classList.add(col);
        },300)
    }
// }





//热点直播
var rdzbbh = document.getElementsByClassName('rdzbbh')
var rdzbbh3 = document.getElementsByClassName('rdzbbh3')
var rdzbbh2 = document.getElementsByClassName('rdzbbh2')


// if(rdzbbh && rdzbbh3 && rdzbbh2){
//热点直播
var r = 0;//记录哪一个热点直播是显示的
rdzbbh[r].style.display = 'block'
rdzbbh2[r].style.display = 'none'

for (let i = 0; i < rdzbbh3.length; i++) {
    rdzbbh3[i].index = i;
    rdzbbh3[i].onmousemove = function () {
        rdzbbh[r].style.display = 'none'
        rdzbbh2[r].style.display = 'block'
        rdzbbh[this.index].style.display = 'block'
        rdzbbh2[this.index].style.display = 'none'
        r = this.index
    }
}
// }





