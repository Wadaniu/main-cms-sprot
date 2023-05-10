//头部TAB
// var navLists = document.getElementsByClassName('navList');
// //头部TAB点击
// var y = 0;//记录哪一个是显示的
// navLists[y].style.color = '#F21646';
// for (var i = 0; i < navLists.length; i++) {

//   navLists[i].index = i;

//   navLists[i].onclick = function () {
//     navLists[y].style.color = "#F21646";
//     navLists[this.index].style.color = "#313131";

//     y = this.index;
//     console.log(y);
//   }
// }

//左侧TAB



//中间日期切换
var saishiover = document.getElementById('saishiover')
var timenavs = document.getElementsByClassName('timenavs')
var saishi = document.getElementsByClassName('saishi')
var date = new Date()
var res = date.getFullYear()+'-'+Number(date.getMonth()+1)+'-'+date.getDate()
console.log(res);
var x = 0;

for (let i = 0; i < timenavs.length; i++) {
    timenavs[i].index = i;
    timenavs[2].style.background='#EEF3F8'
    timenavs[2].children[0].style.color='#3A84FF'
    timenavs[2].children[1].style.color='#3A84FF'
    timenavs[i].onclick=function(){
        timenavs[x].style.background='#fff'
        timenavs[x].children[0].style.color='#4C4B4A'
        timenavs[x].children[1].style.color='#999999'

    timenavs[this.index].style.background = '#EEF3F8'
    timenavs[this.index].children[0].style.color = '#3A84FF'
    timenavs[this.index].children[1].style.color = '#3A84FF'
    x = this.index

    saishiover.scrollTop = saishi[x].offsetTop - 210
    console.log(saishiover.scrollTop);
  }


  if (timenavs[i].children[0].innerHTML == res) {
    timenavs[i].style.background = '#EEF3F8'
    timenavs[i].children[0].style.color = '#3A84FF'
    timenavs[i].children[1].style.color = '#3A84FF'
    x = i
    saishiover.scrollTop = saishi[x].offsetTop - 210
  }
}
timenavs[2].onclick()