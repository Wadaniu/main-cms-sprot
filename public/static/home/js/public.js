//右侧内容
var rightList = document.getElementsByClassName('rightList');

//右侧内容
for (let i = 0; i < rightList.length; i++) {
    rightList[i].onmouseover = function () {
        rightList[i].style.background='#EEF3F8'
        // console.log(leftList[i].children[1].innerHTML);
    }
    rightList[i].onmouseout = function () {
        rightList[i].style.background='white';
    }
}