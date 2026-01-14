/**
 * Main application JavaScript
 * Features: Mobile menu, AJAX forms, validation, filters, gallery
 */

// Global app object

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modules
    App.init();
});

/**
 * Main App Module
 */
const App = {
    init() {
        this.setupMobileMenu();
        this.setupLeadForms();
        this.setupFilterForms();
        this.setupGallery();
        this.setupCSRFToken();
    },

    /**
     * Setup CSRF token for AJAX requests
     */
    setupCSRFToken() {
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (tokenMeta) {
            const token = tokenMeta.getAttribute('content');
            
            // Store globally for fetch requests
            this.csrfToken = token;
            
            // Add to all forms if not present
            document.querySelectorAll('form').forEach(form => {
                if (!form.querySelector('input[name="_token"]')) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = '_token';
                    input.value = token;
                    form.appendChild(input);
                }
            });
        }
    },

    /**
     * Mobile menu functionality
     */
    setupMobileMenu() {
        const hamburger = document.querySelector('.hamburger');
        const navMenu = document.querySelector('.nav-menu');

        if (hamburger && navMenu) {
            hamburger.addEventListener('click', () => {
                navMenu.classList.toggle('active');
                hamburger.classList.toggle('active');
            });

            // Close menu when clicking on a link
            navMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    navMenu.classList.remove('active');
                    hamburger.classList.remove('active');
                });
            });

            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                    navMenu.classList.remove('active');
                    hamburger.classList.remove('active');
                }
            });
        }
    },

    /**
     * Setup all lead forms for AJAX submission
     */
    setupLeadForms() {
        document.querySelectorAll('.lead-form').forEach(form => {
            this.setupLeadForm(form);
        });
    },

    /**
     * Setup individual lead form
     */
    setupLeadForm(form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Validate form
            if (!this.validateForm(form)) {
                return;
            }

            // Get form data
            const formData = new FormData(form);
            const messageContainer = form.querySelector('.form-message');

            // Show loading state
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Отправка...';

            try {
                // Get CSRF token
                const token = document.querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') || this.csrfToken;

                // Submit form via AJAX
                const response = await fetch('/leads', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json();

                // Show response message
                if (data.success) {
                    this.showMessage(messageContainer, data.message, 'success');
                    form.reset();
                } else {
                    const errorMessage = this.formatErrors(data.errors);
                    this.showMessage(messageContainer, errorMessage, 'error');
                }

                // Reset button
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;

            } catch (error) {
                this.showMessage(
                    messageContainer, 
                    'Произошла ошибка. Попробуйте позже.', 
                    'error'
                );
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            }
        });
    },

    /**
     * Client-side form validation
     */
    validateForm(form) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        const errors = [];

        requiredFields.forEach(field => {
            // Reset error styling
            field.classList.remove('error');
            
            // Check if field is empty
            if (!field.value.trim()) {
                field.classList.add('error');
                errors.push(`${field.labels[0]?.textContent || field.name} - обязательное поле`);
                isValid = false;
            }

            // Email validation
            if (field.type === 'email' && field.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value)) {
                    field.classList.add('error');
                    errors.push('Введите корректный email');
                    isValid = false;
                }
            }

            // Phone validation (basic)
            if (field.type === 'tel' && field.value) {
                const phoneRegex = /^[\+]?[\d\s\-\(\)]+$/;
                if (!phoneRegex.test(field.value)) {
                    field.classList.add('error');
                    errors.push('Введите корректный телефон');
                    isValid = false;
                }
            }
        });

        // Show validation errors if any
        if (!isValid) {
            const messageContainer = form.querySelector('.form-message');
            this.showMessage(messageContainer, errors.join('<br>'), 'error');
        }

        return isValid;
    },

    /**
     * Show message in container
     */
    showMessage(container, message, type = 'info') {
        if (!container) return;

        // Clear existing messages
        container.innerHTML = '';

        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = message;

        container.appendChild(alert);

        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        // Scroll to message
        container.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'nearest' 
        });
    },

    /**
     * Format errors for display
     */
    formatErrors(errors) {
        if (typeof errors === 'string') {
            return errors;
        }

        if (Array.isArray(errors)) {
            return errors.join('<br>');
        }

        if (typeof errors === 'object') {
            return Object.values(errors).flat().join('<br>');
        }

        return 'Произошла ошибка валидации';
    },

    /**
     * Setup filter forms
     */
    setupFilterForms() {
        document.querySelectorAll('.filters-form').forEach(form => {
            // Auto-submit on change (optional)
            const autoSubmit = form.dataset.autoSubmit === 'true';
            
            if (autoSubmit) {
                form.addEventListener('change', () => {
                    form.submit();
                });
            }

            // Handle range inputs
            this.setupRangeInputs(form);
        });
    },

    /**
     * Setup range input validation
     */
    setupRangeInputs(form) {
        const minInputs = form.querySelectorAll('input[name$="_min"]');
        
        minInputs.forEach(minInput => {
            const fieldName = minInput.name.replace('_min', '');
            const maxInput = form.querySelector(`input[name="${fieldName}_max"]`);
            
            if (maxInput) {
                minInput.addEventListener('change', () => {
                    if (parseFloat(minInput.value) > parseFloat(maxInput.value)) {
                        maxInput.value = minInput.value;
                    }
                });

                maxInput.addEventListener('change', () => {
                    if (parseFloat(maxInput.value) < parseFloat(minInput.value)) {
                        minInput.value = maxInput.value;
                    }
                });
            }
        });
    },

    /**
     * Setup image gallery
     */
    setupGallery() {
        document.querySelectorAll('.image-gallery').forEach(gallery => {
            const thumbs = gallery.querySelectorAll('.gallery-thumb img');
            const mainImage = document.getElementById('main-image');
            
            if (mainImage) {
                thumbs.forEach(thumb => {
                    thumb.addEventListener('click', () => {
                        const newSrc = thumb.src;
                        const tempSrc = mainImage.src;
                        
                        // Smooth transition effect
                        mainImage.style.opacity = '0.7';
                        mainImage.src = newSrc;
                        
                        setTimeout(() => {
                            mainImage.style.opacity = '1';
                        }, 50);
                    });
                });
            }

            // Keyboard navigation
            gallery.addEventListener('keydown', (e) => {
                const currentThumb = document.activeElement;
                const thumbsArray = Array.from(thumbs);
                const currentIndex = thumbsArray.indexOf(currentThumb);
                
                if (e.key === 'ArrowRight' && currentIndex < thumbs.length - 1) {
                    thumbs[currentIndex + 1].focus();
                    e.preventDefault();
                } else if (e.key === 'ArrowLeft' && currentIndex > 0) {
                    thumbs[currentIndex - 1].focus();
                    e.preventDefault();
                } else if (e.key === 'Enter' && currentThumb) {
                    currentThumb.click();
                    e.preventDefault();
                }
            });
        });
    },

    /**
     * Utility: Debounce function
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// Utility functions for global use

/**
 * Show toast notification
 */
function showToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    // Add styles
    Object.assign(toast.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '12px 24px',
        borderRadius: '8px',
        color: 'white',
        fontWeight: '500',
        zIndex: '10000',
        opacity: '0',
        transform: 'translateY(-20px)',
        transition: 'all 0.3s ease',
        maxWidth: '90vw',
        wordWrap: 'break-word'
    });

    // Type-specific styling
    switch(type) {
        case 'success':
            toast.style.backgroundColor = '#10b981';
            break;
        case 'error':
            toast.style.backgroundColor = '#ef4444';
            break;
        case 'warning':
            toast.style.backgroundColor = '#f59e0b';
            break;
        default:
            toast.style.backgroundColor = '#2563eb';
    }

    document.body.appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 10);

    // Animate out and remove
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, duration);
}

/**
 * Format number with spaces (e.g., 1000000 -> 1 000 000)
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

/**
 * Smooth scroll to element
 */
function scrollToElement(selector, offset = 0) {
    const element = document.querySelector(selector);
    if (element) {
        const elementPosition = element.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - offset;

        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { App, showToast, formatNumber, scrollToElement };
}