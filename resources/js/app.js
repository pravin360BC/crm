import Alpine from 'alpinejs';
import '@fontsource-variable/manrope';
import '@fontsource-variable/dm-sans';

Alpine.start();

const entrance = document.querySelector('[data-entrance]');
const reveal = document.querySelector('[data-section-reveal]');
const motion = window.matchMedia('(prefers-reduced-motion: no-preference)');

if (motion.matches && (entrance || reveal)) {
    Promise.all([import('gsap'), import('gsap/ScrollTrigger')]).then(([{ gsap }, { ScrollTrigger }]) => {
        gsap.registerPlugin(ScrollTrigger);
        const conditions = gsap.matchMedia();

        conditions.add(motion.media, () => {
            if (entrance) {
                gsap.from(entrance, { y: 8, duration: 0.45, ease: 'power2.out', clearProps: 'transform' });
            }

            if (reveal) {
                gsap.from(reveal, {
                    y: 8,
                    duration: 0.45,
                    ease: 'power2.out',
                    clearProps: 'transform',
                    immediateRender: false,
                    scrollTrigger: { trigger: reveal, start: 'top 92%', once: true },
                });
            }
        });

        window.addEventListener('pagehide', () => conditions.revert(), { once: true });
    }).catch(() => {
        // The overview stays visible if the optional motion chunk fails to load.
    });
}
