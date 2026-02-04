/* =============================================
   MARVELL RENTAL - JAVASCRIPT FUNCTIONALITY
   ============================================= */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize all components
    initNavbar();
    initCarousel();
    initMobileMenu();
    initModals();
    initSidebar();
    initAlerts();
});

/* =============================================
   NAVBAR
   ============================================= */
function initNavbar() {
    const navbar = document.querySelector('.navbar');

    if (navbar) {
        // Navbar scroll effect
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // Mobile menu toggle
    const toggle = document.querySelector('.navbar-toggle');
    const menu = document.querySelector('.navbar-menu');

    if (toggle && menu) {
        toggle.addEventListener('click', function () {
            toggle.classList.toggle('active');
            menu.classList.toggle('active');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function (e) {
            if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                toggle.classList.remove('active');
                menu.classList.remove('active');
            }
        });

        // Close menu when clicking on menu item
        menu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function () {
                toggle.classList.remove('active');
                menu.classList.remove('active');
            });
        });
    }
}

/* =============================================
   CAROUSEL
   ============================================= */
function initCarousel() {
    const carousels = document.querySelectorAll('.vehicle-carousel');

    carousels.forEach(carousel => {
        const track = carousel.querySelector('.vehicle-track');
        const prevBtn = carousel.querySelector('.carousel-prev');
        const nextBtn = carousel.querySelector('.carousel-next');

        if (!track) return;

        const cardWidth = 310; // card width + gap
        let scrollPosition = 0;

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                scrollPosition = Math.max(scrollPosition - cardWidth, 0);
                track.scrollTo({
                    left: scrollPosition,
                    behavior: 'smooth'
                });
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                const maxScroll = track.scrollWidth - track.clientWidth;
                scrollPosition = Math.min(scrollPosition + cardWidth, maxScroll);
                track.scrollTo({
                    left: scrollPosition,
                    behavior: 'smooth'
                });
            });
        }

        // Touch/swipe support for mobile
        let isDown = false;
        let startX;
        let scrollLeft;

        track.addEventListener('mousedown', (e) => {
            isDown = true;
            track.style.cursor = 'grabbing';
            startX = e.pageX - track.offsetLeft;
            scrollLeft = track.scrollLeft;
        });

        track.addEventListener('mouseleave', () => {
            isDown = false;
            track.style.cursor = 'grab';
        });

        track.addEventListener('mouseup', () => {
            isDown = false;
            track.style.cursor = 'grab';
        });

        track.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - track.offsetLeft;
            const walk = (x - startX) * 2;
            track.scrollLeft = scrollLeft - walk;
        });

        // Touch events
        track.addEventListener('touchstart', (e) => {
            startX = e.touches[0].pageX - track.offsetLeft;
            scrollLeft = track.scrollLeft;
        });

        track.addEventListener('touchmove', (e) => {
            const x = e.touches[0].pageX - track.offsetLeft;
            const walk = (x - startX) * 2;
            track.scrollLeft = scrollLeft - walk;
        });
    });
}

/* =============================================
   MOBILE SIDEBAR MENU
   ============================================= */
function initMobileMenu() {
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    if (mobileBtn && sidebar) {
        mobileBtn.addEventListener('click', function () {
            sidebar.classList.toggle('active');
            if (overlay) overlay.classList.toggle('active');
        });

        if (overlay) {
            overlay.addEventListener('click', function () {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        }
    }
}

/* =============================================
   SIDEBAR
   ============================================= */
function initSidebar() {
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a');

    sidebarLinks.forEach(link => {
        link.addEventListener('click', function () {
            // Remove active class from all links
            sidebarLinks.forEach(l => l.classList.remove('active'));
            // Add active class to clicked link
            this.classList.add('active');
        });
    });
}

/* =============================================
   MODALS
   ============================================= */
function initModals() {
    // Open modal
    document.querySelectorAll('[data-modal]').forEach(trigger => {
        trigger.addEventListener('click', function () {
            const modalId = this.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Close modal
    document.querySelectorAll('.modal-close, .modal-overlay').forEach(elem => {
        elem.addEventListener('click', function (e) {
            if (e.target === this) {
                const modal = this.closest('.modal-overlay');
                if (modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
        });
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
    });
}

/* =============================================
   ALERTS
   ============================================= */
function initAlerts() {
    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}

/* =============================================
   FORM VALIDATION
   ============================================= */
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('[required]');

    inputs.forEach(input => {
        removeError(input);

        if (!input.value.trim()) {
            showError(input, 'Field ini wajib diisi');
            isValid = false;
        } else if (input.type === 'email' && !isValidEmail(input.value)) {
            showError(input, 'Format email tidak valid');
            isValid = false;
        }
    });

    return isValid;
}

function showError(input, message) {
    input.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    errorDiv.textContent = message;
    errorDiv.style.color = '#FF5252';
    errorDiv.style.fontSize = '0.85rem';
    errorDiv.style.marginTop = '5px';
    input.parentNode.appendChild(errorDiv);
}

function removeError(input) {
    input.classList.remove('error');
    const error = input.parentNode.querySelector('.form-error');
    if (error) error.remove();
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/* =============================================
   UTILITY FUNCTIONS
   ============================================= */

// Format currency to Rupiah
function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
}

// Format date to Indonesian format
function formatDate(dateString) {
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

// Print function
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    ${getStylesForPrint()}
                </style>
            </head>
            <body>
                ${element.innerHTML}
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
}

function getStylesForPrint() {
    return `
        .nota { max-width: 400px; margin: 0 auto; padding: 20px; }
        .nota-header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px dashed #ddd; }
        .nota-logo { font-size: 1.5rem; font-weight: bold; color: #C9A100; }
        .nota-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .nota-label { color: #666; }
        .nota-value { font-weight: 600; }
        .nota-total { background: #f9f9f9; padding: 15px; margin-top: 15px; border-radius: 5px; }
        .nota-footer { text-align: center; margin-top: 20px; padding-top: 10px; border-top: 2px dashed #ddd; color: #666; font-size: 0.9rem; }
    `;
}

// Confirm delete
function confirmDelete(message = 'Apakah Anda yakin ingin menghapus data ini?') {
    return confirm(message);
}

// Show loading
function showLoading(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner"></span> Loading...';
    button.disabled = true;
    return originalText;
}

// Hide loading
function hideLoading(button, originalText) {
    button.innerHTML = originalText;
    button.disabled = false;
}

// Calculate rental days
function calculateDays(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays || 1;
}

// Calculate total price
function calculateTotal(pricePerDay, days) {
    return pricePerDay * days;
}

/* =============================================
   DATE INPUT HELPERS
   ============================================= */
function setMinDate(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        const today = new Date().toISOString().split('T')[0];
        input.setAttribute('min', today);
    }
}

function updateEndDateMin(startInputId, endInputId) {
    const startInput = document.getElementById(startInputId);
    const endInput = document.getElementById(endInputId);

    if (startInput && endInput) {
        startInput.addEventListener('change', function () {
            endInput.setAttribute('min', this.value);
            if (endInput.value && endInput.value < this.value) {
                endInput.value = this.value;
            }
            updateRentalSummary();
        });

        endInput.addEventListener('change', updateRentalSummary);
    }
}

function updateRentalSummary() {
    const startInput = document.getElementById('tanggal_pinjam');
    const endInput = document.getElementById('tanggal_kembali');
    const priceElement = document.getElementById('harga_per_hari');
    const daysElement = document.getElementById('total_hari');
    const totalElement = document.getElementById('total_harga');

    if (startInput && endInput && startInput.value && endInput.value && priceElement && daysElement && totalElement) {
        const days = calculateDays(startInput.value, endInput.value);
        const price = parseInt(priceElement.value) || 0;
        const total = calculateTotal(price, days);

        daysElement.textContent = days + ' hari';
        totalElement.textContent = formatRupiah(total);
    }
}

/* =============================================
   SMOOTH SCROLL
   ============================================= */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

/* =============================================
   BACK BUTTON PREVENTION (for logged in pages)
   ============================================= */
function preventBackButton() {
    history.pushState(null, null, location.href);
    window.onpopstate = function () {
        history.go(1);
    };
}

// Call this on dashboard pages
if (document.querySelector('.dashboard')) {
    preventBackButton();
}

/* =============================================
   MOTOR CATEGORY FILTER (Landing Page)
   ============================================= */
function filterMotor(category) {
    const cards = document.querySelectorAll('.vehicle-card');
    const buttons = document.querySelectorAll('.category-filter');

    // Update active button
    buttons.forEach(btn => {
        btn.style.background = 'rgba(0,0,0,0.2)';
        btn.style.color = '#000';
    });
    event.target.closest('.category-filter').style.background = '#000';
    event.target.closest('.category-filter').style.color = '#FFD700';

    // Filter cards
    cards.forEach(card => {
        const cardCategory = card.querySelector('.vehicle-card-type')?.textContent.trim();
        if (category === '' || cardCategory === category) {
            card.style.display = '';
            card.style.animation = 'fadeIn 0.3s ease';
        } else {
            card.style.display = 'none';
        }
    });
}
