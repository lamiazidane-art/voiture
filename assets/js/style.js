


document.addEventListener('DOMContentLoaded', () => {
    const navbar = document.querySelector('.navbar');
    const menuButton = document.querySelector('[data-menu-toggle]');
    const navLinks = document.getElementById('navLinks');
    const connexionLinks = document.querySelector('.connexion');

    if (navbar && document.body.classList.contains('home')) {
        const updateNavbarState = () => {
            navbar.classList.toggle('navbar-scrolled', window.scrollY > 20);
        };

        updateNavbarState();
        window.addEventListener('scroll', updateNavbarState, { passive: true });
    }

    if (menuButton && navLinks) {
        const closeMenu = () => {
            navLinks.classList.remove('show');
            if (connexionLinks) {
                connexionLinks.classList.remove('show');
            }
            menuButton.setAttribute('aria-expanded', 'false');
        };

        const toggleMenu = () => {
            const isOpen = navLinks.classList.toggle('show');
            if (connexionLinks) {
                connexionLinks.classList.toggle('show', isOpen);
            }
            menuButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        };

        menuButton.addEventListener('click', toggleMenu);
        navLinks.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    closeMenu();
                }
            });
        });

        if (connexionLinks) {
            connexionLinks.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 768) {
                        closeMenu();
                    }
                });
            });
        }

        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                closeMenu();
            }
        }, { passive: true });
    }

    document.querySelectorAll('a[href^="#"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const targetId = link.getAttribute('href');
            if (!targetId || targetId === '#') {
                return;
            }

            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                event.preventDefault();
                targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        window.addEventListener('scroll', () => {
            heroSection.style.backgroundPositionY = `${window.pageYOffset * 0.5}px`;
        }, { passive: true });
    }

    const statNumbers = document.querySelectorAll('.stat-number');
    if (statNumbers.length && 'IntersectionObserver' in window) {
        const animateNumber = (element) => {
            const target = Number.parseInt(element.dataset.target || '0', 10);
            const duration = 1600;
            const startTime = performance.now();

            const tick = (now) => {
                const progress = Math.min((now - startTime) / duration, 1);
                element.textContent = Math.floor(progress * target);

                if (progress < 1) {
                    requestAnimationFrame(tick);
                } else {
                    element.textContent = String(target);
                }
            };

            requestAnimationFrame(tick);
        };

        const numbersObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animateNumber(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        });

        statNumbers.forEach((stat) => numbersObserver.observe(stat));
    }

    const cards = document.querySelectorAll('.card, .deals-card');
    if (cards.length && 'IntersectionObserver' in window) {
        const cardsObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        cards.forEach((card) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            cardsObserver.observe(card);
        });
    }

    const createVehiclePhotoModal = () => {
        let modal = document.getElementById('global-vehicle-photo-modal');
        if (modal) {
            return modal;
        }

        modal = document.createElement('div');
        modal.id = 'global-vehicle-photo-modal';
        modal.className = 'vehicle-photo-modal';
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = `
            <div class="vehicle-photo-modal__content">
                <button type="button" class="vehicle-photo-modal__close" id="global-vehicle-photo-close" aria-label="Fermer la photo">&times;</button>
                <img class="vehicle-photo-modal__img" id="global-vehicle-photo-modal-img" alt="" src="" />
                <p class="vehicle-photo-modal__caption"></p>
            </div>
        `;
        document.body.appendChild(modal);
        return modal;
    };

    const openVehiclePhotoModal = (img) => {
        const modal = createVehiclePhotoModal();
        const modalImg = modal.querySelector('#global-vehicle-photo-modal-img');
        const caption = modal.querySelector('.vehicle-photo-modal__caption');

        modalImg.src = img.dataset.zoomSrc || img.src;
        modalImg.alt = img.dataset.zoomAlt || img.alt || '';
        caption.textContent = img.dataset.zoomCaption || img.alt || '';

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    };

    const closeVehiclePhotoModal = () => {
        const modal = document.getElementById('global-vehicle-photo-modal');
        if (!modal) {
            return;
        }
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    const vehicleZoomImages = document.querySelectorAll('img[data-zoomable="true"], .vehicle-image');
    vehicleZoomImages.forEach((img) => {
        img.style.cursor = 'pointer';
        img.addEventListener('click', () => openVehiclePhotoModal(img));
    });

    document.addEventListener('click', (event) => {
        const modal = document.getElementById('global-vehicle-photo-modal');
        if (!modal) {
            return;
        }

        if (event.target.closest('#global-vehicle-photo-close')) {
            closeVehiclePhotoModal();
            return;
        }

        if (event.target === modal) {
            closeVehiclePhotoModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeVehiclePhotoModal();
        }
    });
});

const menuBtn = document.getElementById("menuBtn");
const mobileMenu = document.getElementById("mobileMenu");

// Ouvrir / fermer menu
if (menuBtn && mobileMenu) {
    menuBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        mobileMenu.classList.toggle("active");
    });

    // clic sur lien => fermer menu
    mobileMenu.querySelectorAll("a").forEach(link => {
        link.addEventListener("click", () => {
            mobileMenu.classList.remove("active");
        });
    });

    // clic dehors => fermer menu
    document.addEventListener("click", (e) => {
        if (!mobileMenu.contains(e.target) && !menuBtn.contains(e.target)) {
            mobileMenu.classList.remove("active");
        }
    });

    // ESC clavier => fermer
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            mobileMenu.classList.remove("active");
        }
    });
}