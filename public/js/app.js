document.addEventListener('DOMContentLoaded', function () {
    var sections = Array.from(document.querySelectorAll('.main-content > .section'));
    var navigationItems = Array.from(document.querySelectorAll('.side-nav > li'));
    var sectionData = document.getElementById('section-data');
    var currentSection = 0;
    var scrollLocked = false;

    function activateSection(nextSection) {
        if (sections.length === 0) {
            return;
        }

        var boundedSection = Math.max(0, Math.min(nextSection, sections.length - 1));
        var previousSection = currentSection;
        currentSection = boundedSection;

        navigationItems.forEach(function (item, index) {
            item.classList.toggle('is-active', index === currentSection);
            item.setAttribute('aria-current', index === currentSection ? 'page' : 'false');
        });

        sections.forEach(function (section, index) {
            section.classList.toggle('section--is-active', index === currentSection);
            section.setAttribute('aria-hidden', index === currentSection ? 'false' : 'true');

            Array.from(section.children).forEach(function (child) {
                child.classList.remove('section--next', 'section--prev');
            });
        });

        if (previousSection !== currentSection && sections[previousSection]) {
            Array.from(sections[previousSection].children).forEach(function (child) {
                child.classList.add(currentSection > previousSection ? 'section--next' : 'section--prev');
            });
        }

        if (currentSection === 3) {
            animateProgressBars();
        }
    }

    function moveSection(direction) {
        var nextSection = currentSection + direction;

        if (nextSection >= sections.length) {
            nextSection = 0;
        } else if (nextSection < 0) {
            nextSection = sections.length - 1;
        }

        activateSection(nextSection);
    }

    navigationItems.forEach(function (item, index) {
        item.addEventListener('click', function () {
            activateSection(index);
        });
        item.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                activateSection(index);
            }
        });
    });

    var contactButton = document.querySelector('.meContacter');
    if (contactButton) {
        contactButton.addEventListener('click', function () {
            activateSection(sections.length - 1);
        });
    }

    var homeLink = document.querySelector('.accueil');
    if (homeLink) {
        homeLink.addEventListener('click', function (event) {
            if (window.location.pathname === homeLink.pathname) {
                event.preventDefault();
                activateSection(0);
            }
        });
    }

    document.addEventListener('wheel', function (event) {
        if (document.querySelector('[role="dialog"].visible') || Math.abs(event.deltaY) < 35) {
            return;
        }

        event.preventDefault();

        if (scrollLocked) {
            return;
        }

        scrollLocked = true;
        moveSection(event.deltaY > 0 ? 1 : -1);
        window.setTimeout(function () {
            scrollLocked = false;
        }, 650);
    }, {passive: false});

    document.addEventListener('keydown', function (event) {
        if (document.querySelector('[role="dialog"].visible')) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            moveSection(1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            moveSection(-1);
        }
    });

    var viewport = document.getElementById('viewport');
    var touchStartY = null;

    if (viewport) {
        viewport.addEventListener('touchstart', function (event) {
            touchStartY = event.touches[0].clientY;
        }, {passive: true});
        viewport.addEventListener('touchend', function (event) {
            if (null === touchStartY || document.querySelector('[role="dialog"].visible')) {
                return;
            }

            var difference = touchStartY - event.changedTouches[0].clientY;
            touchStartY = null;

            if (Math.abs(difference) > 55) {
                moveSection(difference > 0 ? 1 : -1);
            }
        }, {passive: true});
    }

    var projectItems = Array.from(document.querySelectorAll('.slider > .slider--item'));
    var projectIndex = 0;

    function updateProjectSlider() {
        var total = projectItems.length;
        projectItems.forEach(function (item) {
            item.classList.remove('slider--item-left', 'slider--item-center', 'slider--item-right');
        });

        if (total === 0) {
            return;
        }

        projectItems[projectIndex].classList.add('slider--item-center');

        if (total > 1) {
            projectItems[(projectIndex + 1) % total].classList.add('slider--item-right');
        }

        if (total > 2) {
            projectItems[(projectIndex - 1 + total) % total].classList.add('slider--item-left');
        }
    }

    document.querySelector('.slider--prev')?.addEventListener('click', function () {
        projectIndex = (projectIndex - 1 + projectItems.length) % projectItems.length;
        updateProjectSlider();
    });
    document.querySelector('.slider--next')?.addEventListener('click', function () {
        projectIndex = (projectIndex + 1) % projectItems.length;
        updateProjectSlider();
    });
    updateProjectSlider();

    var careerSlider = document.querySelector('[data-career-slider]');
    if (careerSlider) {
        var careerTrack = careerSlider.querySelector('.swiper-wrapper');
        careerSlider.querySelector('.swiper-button-prev')?.addEventListener('click', function () {
            careerTrack.scrollBy({left: -careerSlider.clientWidth * 0.75, behavior: 'smooth'});
        });
        careerSlider.querySelector('.swiper-button-next')?.addEventListener('click', function () {
            careerTrack.scrollBy({left: careerSlider.clientWidth * 0.75, behavior: 'smooth'});
        });
    }

    function animateProgressBars() {
        document.querySelectorAll('.progress').forEach(function (bar) {
            if (bar.dataset.animated === 'true') {
                return;
            }

            var target = Math.max(0, Math.min(parseInt(bar.dataset.value || '0', 10), 100));
            var startedAt = null;
            bar.dataset.animated = 'true';
            bar.style.backgroundColor = bar.dataset.color || '#0f33ff';
            bar.style.width = target + '%';

            function updateCounter(timestamp) {
                startedAt = startedAt || timestamp;
                var progress = Math.min((timestamp - startedAt) / 1600, 1);
                bar.textContent = Math.round(target * progress) + '%';

                if (progress < 1) {
                    window.requestAnimationFrame(updateCounter);
                }
            }

            window.requestAnimationFrame(updateCounter);
        });
    }

    var dialogs = Array.from(document.querySelectorAll('[role="dialog"]'));
    var lastFocusedElement = null;

    function focusableElements(dialog) {
        return Array.from(dialog.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'));
    }

    function openDialog(dialog, trigger) {
        if (!dialog) {
            return;
        }

        lastFocusedElement = trigger || document.activeElement;
        dialog.hidden = false;
        window.requestAnimationFrame(function () {
            dialog.classList.add('visible');
            dialog.querySelector('.popup-content')?.focus();
        });
        document.body.classList.add('popup-open');
        document.body.classList.toggle('contact-popup-open', dialog.classList.contains('popupcontact'));
    }

    function closeDialog(dialog) {
        if (!dialog) {
            return;
        }

        dialog.classList.remove('visible');
        window.setTimeout(function () {
            dialog.hidden = true;
        }, 220);
        document.body.classList.remove('popup-open', 'contact-popup-open');

        if (lastFocusedElement instanceof HTMLElement) {
            lastFocusedElement.focus();
        }
    }

    document.querySelectorAll('.open-popup').forEach(function (trigger) {
        trigger.addEventListener('click', function (event) {
            var selector = trigger.getAttribute('href');
            if (!selector || !selector.startsWith('#')) {
                return;
            }

            event.preventDefault();
            openDialog(document.querySelector(selector), trigger);
        });
    });

    dialogs.forEach(function (dialog) {
        dialog.querySelector('.close')?.addEventListener('click', function () {
            closeDialog(dialog);
        });
        dialog.addEventListener('click', function (event) {
            if (event.target === dialog) {
                closeDialog(dialog);
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        var activeDialog = document.querySelector('[role="dialog"].visible');
        if (!activeDialog) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeDialog(activeDialog);
            return;
        }

        if (event.key === 'Tab') {
            var focusable = focusableElements(activeDialog);
            if (focusable.length === 0) {
                event.preventDefault();
                return;
            }

            var first = focusable[0];
            var last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    var requestedSection = sectionData ? parseInt(sectionData.dataset.section || '0', 10) : 0;
    activateSection(Number.isNaN(requestedSection) ? 0 : requestedSection);

    if (sectionData?.dataset.openContact === 'true') {
        openDialog(document.getElementById('contact-form'));
    }
});
