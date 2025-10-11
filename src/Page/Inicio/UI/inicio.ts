import { Swiper } from "swiper";
import { Navigation, } from "swiper/modules";

import 'swiper/css/bundle';

const swiper = new Swiper('.swiper', {
    loop: true,
    pagination: {
        el: ".swiper-pagination",
        type: "progressbar",
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    modules: [Navigation],
});