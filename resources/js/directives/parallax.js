import { gsap, ScrollTrigger } from '../lib/smooth-scroll';

/**
 * v-parallax — geser elemen mengikuti scroll untuk efek kedalaman.
 *
 * <div v-parallax />                                     default: naik 80px
 * <div v-parallax="{ speed: -0.4 }" />                    arah berlawanan
 * <div v-parallax="{ y: 140, scale: 1.08, rotate: 6 }" /> atur detail
 *
 * `scrub: true` membuat animasi terikat langsung ke posisi scroll,
 * sehingga tetap presisi walau scroll di-interpolasi Lenis.
 */
export const parallax = {
    mounted(el, binding) {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const options = typeof binding.value === 'number' ? { speed: binding.value } : binding.value ?? {};

        const {
            speed = 1,
            y = 80,
            x = 0,
            scale = 1,
            rotate = 0,
            start = 'top bottom',
            end = 'bottom top',
        } = options;

        const tween = gsap.fromTo(
            el,
            { yPercent: 0, xPercent: 0 },
            {
                y: -y * speed,
                x: x * speed,
                scale,
                rotate,
                ease: 'none',
                scrollTrigger: {
                    trigger: el,
                    start,
                    end,
                    scrub: true,
                    invalidateOnRefresh: true,
                },
            },
        );

        el._parallaxTween = tween;
    },

    unmounted(el) {
        el._parallaxTween?.scrollTrigger?.kill();
        el._parallaxTween?.kill();
        delete el._parallaxTween;
    },
};

export default {
    install(app) {
        app.directive('parallax', parallax);
    },
};

export { ScrollTrigger };
