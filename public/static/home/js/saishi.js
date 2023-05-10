var mySwiper = new Swiper ('.swiper', {
    // loop: true,
    // autoplay : true,//自动切换
    slidesPerView: 4,
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },

})
var swiper = new Swiper(".mySwiper", {
  autoplay : true,
  loop: true,
  spaceBetween: 30,
  effect: "fade",
  navigation: {
  //   nextEl: ".swiper-button-next",
  //   prevEl: ".swiper-button-prev",
  },
  pagination: {
    el: ".swiper-pagination",
    clickable: true,
  },
});


var rmhfLi = document.getElementsByClassName('rmhfLi');
rmhfLi[0].children[0].style.color='#FF008C'
rmhfLi[1].children[0].style.color='#FC7F00'
rmhfLi[2].children[0].style.color='#FBB21A'