document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            animation: true,
            delay: { show: 100, hide: 50 }
        });
    });

    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                const invalidFields = form.querySelectorAll(':invalid');
                invalidFields.forEach(field => {
                    field.classList.add('is-invalid');
                    field.addEventListener('animationend', () => {
                        field.classList.remove('animate__animated', 'animate__headShake');
                    }, { once: true });
                    
                    field.classList.add('animate__animated', 'animate__headShake');
                });
            }
            form.classList.add('was-validated');
        }, false);
    });

    const flashMessages = document.querySelectorAll('.alert');
    flashMessages.forEach(message => {
        message.classList.add('animate__animated', 'animate__fadeInRight');
        
        setTimeout(() => {
            message.style.transition = 'all 0.5s ease';
            message.classList.remove('animate__fadeInRight');
            message.classList.add('animate__fadeOutRight');
            
            setTimeout(() => {
                message.remove();
                // Adjust layout smoothly after removal
                const parent = message.parentElement;
                if (parent && parent.children.length === 0) {
                    parent.style.transition = 'height 0.3s ease';
                    parent.style.height = '0';
                    setTimeout(() => parent.remove(), 300);
                }
            }, 500);
        }, 5000);
    });

    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('d-none');
            
            if (mobileMenu.classList.contains('d-none')) {
                mobileMenu.style.maxHeight = '0';
            } else {
                mobileMenu.style.maxHeight = mobileMenu.scrollHeight + 'px';
            }
        });
    }
});

function makeAjaxRequest(url, method, data, callback, timeout = 10000) {
    const xhr = new XMLHttpRequest();
    const timeoutId = setTimeout(() => {
        xhr.abort();
        callback({ success: false, message: 'Request timed out' });
    }, timeout);

    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            clearTimeout(timeoutId);
            
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    callback(response);
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                    callback({ 
                        success: false, 
                        message: 'Invalid server response',
                        details: e.message 
                    });
                }
            } else {
                callback({ 
                    success: false, 
                    message: 'Request failed', 
                    status: xhr.status,
                    statusText: xhr.statusText 
                });
            }
        }
    };
    
    xhr.onerror = function() {
        clearTimeout(timeoutId);
        callback({ 
            success: false, 
            message: 'Network error occurred' 
        });
    };
    
    xhr.send(data);
}

function showToast(message, type = 'info', duration = 3000) {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toast = document.createElement('div');
    
    toast.className = `toast-message toast-${type} animate__animated animate__fadeInUp`;
    toast.innerHTML = `
        <div class="toast-icon">
            ${getToastIcon(type)}
        </div>
        <div class="toast-content">${message}</div>
        <button class="toast-close">&times;</button>
    `;
    
    toastContainer.appendChild(toast);
    
    const timer = setTimeout(() => {
        removeToast(toast);
    }, duration);

    toast.querySelector('.toast-close').addEventListener('click', () => {
        clearTimeout(timer);
        removeToast(toast);
    });
    
    return toast;
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

function removeToast(toast) {
    toast.classList.remove('animate__fadeInUp');
    toast.classList.add('animate__fadeOutUp');
    
    toast.addEventListener('animationend', () => {
        toast.remove();
        
        const container = document.getElementById('toast-container');
        if (container && container.children.length === 0) {
            container.remove();
        }
    });
}

function getToastIcon(type) {
    const icons = {
        success: '<i class="bi bi-check-circle-fill"></i>',
        error: '<i class="bi bi-x-circle-fill"></i>',
        warning: '<i class="bi bi-exclamation-triangle-fill"></i>',
        info: '<i class="bi bi-info-circle-fill"></i>'
    };
    return icons[type] || icons.info;
}

// Enhanced quantity controls for cart items
function setupQuantityControls() {
    document.querySelectorAll('.quantity-control').forEach(control => {
        const input = control.querySelector('.quantity-input');
        const minusBtn = control.querySelector('.quantity-decrease');
        const plusBtn = control.querySelector('.quantity-increase');
        
        minusBtn.addEventListener('click', () => {
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                input.dispatchEvent(new Event('change'));
                animateButton(minusBtn);
            }
        });
        
        plusBtn.addEventListener('click', () => {
            if (parseInt(input.value) < 10) {
                input.value = parseInt(input.value) + 1;
                input.dispatchEvent(new Event('change'));
                animateButton(plusBtn);
            }
        });
    });
}

function animateButton(button) {
    button.classList.add('animate__animated', 'animate__pulse');
    button.addEventListener('animationend', () => {
        button.classList.remove('animate__animated', 'animate__pulse');
    }, { once: true });
}