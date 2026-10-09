import { gsap } from 'gsap';

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (! reduceMotion) {
    gsap.from('[data-reveal]', {
        autoAlpha: 0,
        duration: 0.7,
        ease: 'power2.out',
        stagger: 0.08,
        y: 18,
    });

    gsap.to('[data-float-card]', {
        duration: 2.6,
        ease: 'sine.inOut',
        repeat: -1,
        y: -8,
        yoyo: true,
    });
}