// RESTCONTROL - Efeitos Suaves de Animação ao Rolar
document.addEventListener("DOMContentLoaded", function () {
    console.log("RESTCONTROL carregado e pronto.");

    const observerOptions = {
        threshold: 0.1
    };

    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = "1";
                entry.target.style.transform = "translateY(0)";
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    const elementsToAnimate = document.querySelectorAll('.full-banner-img, .cta-container, .text-block');
    
    elementsToAnimate.forEach(el => {
        el.style.opacity = "0";
        el.style.transform = "translateY(25px)";
        el.style.transition = "all 0.8s ease-out";
        revealObserver.observe(el);
    });
});