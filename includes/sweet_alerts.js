function showSuccess(message, callback = null) {
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: message,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    }).then(() => {
        if (callback && typeof callback === 'function') {
            callback();
        }
    });
}

function showError(message, callback = null) {
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: message,
        confirmButtonColor: '#4CAF50'
    }).then(() => {
        if (callback && typeof callback === 'function') {
            callback();
        }
    });
}

function showWarning(message, callback = null) {
    Swal.fire({
        icon: 'warning',
        title: 'Warning!',
        text: message,
        confirmButtonColor: '#4CAF50'
    }).then(() => {
        if (callback && typeof callback === 'function') {
            callback();
        }
    });
}

function showConfirm(title, text, confirmButtonText = 'Yes, proceed', callback = null) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4CAF50',
        cancelButtonColor: '#d33',
        confirmButtonText: confirmButtonText,
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed && callback && typeof callback === 'function') {
            callback();
        }
    });
}

function confirmFormSubmit(formId, title = 'Are you sure?', text = 'This action cannot be undone.') {
    const form = document.getElementById(formId);
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            showConfirm(title, text, 'Yes, submit', () => {
                form.submit();
            });
        });
    }
}

function confirmDelete(formId, itemName = 'this item') {
    const form = document.getElementById(formId);
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            showConfirm(
                'Delete Confirmation',
                `Are you sure you want to delete ${itemName}? This action cannot be undone.`,
                'Yes, delete it',
                () => {
                    form.submit();
                }
            );
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const successMessage = urlParams.get('success');
    const errorMessage = urlParams.get('error');
    const warningMessage = urlParams.get('warning');

    if (successMessage) {
        showSuccess(decodeURIComponent(successMessage));
    }
    if (errorMessage) {
        showError(decodeURIComponent(errorMessage));
    }
    if (warningMessage) {
        showWarning(decodeURIComponent(warningMessage));
    }
}); 