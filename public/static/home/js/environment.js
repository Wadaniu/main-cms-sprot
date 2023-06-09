$(function () {
	$(window).scroll(function() {
		if ($(document).scrollTop() > 400) {
			$(".gotop").fadeIn(500);
		} else {
			$(".gotop").fadeOut(500);
		}
	});
	$(".gotop").click(function() {
		$("body,html").animate({
			scrollTop: 0
		}, 500);
	});
	
    if (localStorage.getItem('theme')) {
        $("body").attr('style', localStorage.getItem('theme'))
    }
	
    //获取默认配色
    $.get('/api/FootballCompetitionCount/info', res => {
        //let color = `--main-color:${res.data.primary_color};${torgb(res.data.primary_color)}`;
		let color = `--main-color:#3f86c4;${torgb('#3f86c4')}`;
        if (localStorage.getItem('theme') != color) {
            $("body").attr('style', color)
            localStorage.setItem('theme', color)
        }
        $(".type-name").text(res.data.short_name_zh)
    })

    //给未正确加载的图片加上默认图片
    $("img").each(function (i, item) {
        $(item).error(function () {
            $(this).attr('src', '/static/home/images/noimage.png')
        })
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
})