$(function () {
    if (localStorage.getItem('theme')) {
        $("body").attr('style', localStorage.getItem('theme'))
    }

    //获取默认配色
    let color = `--main-color:${webcolor};${torgb(webcolor)}`;
    if (localStorage.getItem('theme') != color) {
        $("body").attr('style', color)
        localStorage.setItem('theme', color)
    }

    if (document.body.scrollHeight < (window.innerHeight || document.documentElement.clientHeight)) {
        $("#bottom").addClass("hold");
    }

    //给未正确加载的图片加上默认图片
    $("img").each(function (i, item) {
        if (!$(item).attr('src')) {
            $(item).attr('src', '/static/home/images/noimage.png')
        }
        $(item).error(function () {
            $(this).attr('src', '/static/home/images/noimage.png')
        })
    })

    // document.addEventListener("error", function (e) {
    //     var elem = e.target;
    //     console.log(elem.naturalWidth)
    // }, true);


    let prefix;
    let curname = $(".category a.cur").text();
    let cururl = location.pathname;
    if (curname == "全部") {
        prefix = cururl.indexOf('zuqiu') > 0 ? '足球' : cururl.indexOf('lanqiu') > 0 ? '篮球' : '';
    } else if (curname == "") {
        prefix = "相关";
    } else {
        prefix = curname;
    }
    $(".prefix").each(function (i, item) {
        let otitle = $(item).text()
        $(item).text(prefix + otitle)
    })

    let titledom = $(".main-model .name span");
    if (titledom.height() > 40) {
        titledom.css({
            "font-size": "20px"
        })
    }

    $(".nodata:not(.sp)").each(function (i, item) {
        if ($(item).children().length == 0) {
            let txt = $(item).parents('.model').find('.tit').text();
            let dom = txt ? `<p class=nodata>近期暂无${txt}</p>` : '<p class=nodata>暂无相关数据</p>';
            $(item).append(dom)
        }
    })

    //16进制颜色转rgb
    function torgb(str) {
        var reg = /^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/
        if (!reg.test(str)) {
            return;
        }
        let newStr = (str.toLowerCase()).replace(/\#/g, '')
        let len = newStr.length;
        if (len == 3) {
            let t = ''
            for (var i = 0; i < len; i++) {
                t += newStr.slice(i, i + 1).concat(newStr.slice(i, i + 1))
            }
            newStr = t
        }
        let name = ['--main-r', '', '--main-g', '', '--main-b'];
        let rgb = '';
        for (var i = 0; i < 6; i = i + 2) {
            let s = newStr.slice(i, i + 2)
            rgb += `${name[i]}:${parseInt("0x" + s)};`;
        }
        return rgb;
    }

    $(".moredata").click(function () {
        console.log(123)
    })

    $(window).scroll(function () {
        if ($(document).scrollTop() > 400) {
            $(".gotop").fadeIn(500);
        } else {
            $(".gotop").fadeOut(500);
        }
    });
    $(".gotop").click(function () {
        $("body,html").animate({
            scrollTop: 0
        }, 500);
    });
})