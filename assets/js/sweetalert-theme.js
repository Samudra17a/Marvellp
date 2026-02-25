/**
 * SweetAlert2 Theme Configuration for Marvell Rental
 * Centered notifications with light-red theme matching website design
 */

// Default SweetAlert2 configuration with Marvell Rental theme
const MarvellSwal = Swal.mixin({
    customClass: {
        popup: 'marvell-popup',
        title: 'marvell-title',
        confirmButton: 'marvell-confirm-btn',
        cancelButton: 'marvell-cancel-btn',
        denyButton: 'marvell-deny-btn'
    },
    background: '#FFFFFF',
    color: '#1a1a1a',
    confirmButtonColor: '#BF3131',
    cancelButtonColor: '#E0E0E0',
    denyButtonColor: '#FF5252',
    iconColor: '#BF3131',
    showClass: {
        popup: 'animate__animated animate__fadeInUp animate__faster'
    },
    hideClass: {
        popup: 'animate__animated animate__fadeOutDown animate__faster'
    }
});

// Success notification
function showSuccess(message, title = 'Berhasil!') {
    return MarvellSwal.fire({
        icon: 'success',
        title: title,
        text: message,
        confirmButtonText: 'OK',
        timer: 3000,
        timerProgressBar: true
    });
}

// Error notification
function showError(message, title = 'Error!') {
    return MarvellSwal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonText: 'OK'
    });
}

// Warning notification
function showWarning(message, title = 'Peringatan!') {
    return MarvellSwal.fire({
        icon: 'warning',
        title: title,
        text: message,
        confirmButtonText: 'OK'
    });
}

// Info notification
function showInfo(message, title = 'Informasi') {
    return MarvellSwal.fire({
        icon: 'info',
        title: title,
        text: message,
        confirmButtonText: 'OK'
    });
}

// Confirm dialog
function showConfirm(message, title = 'Konfirmasi', confirmText = 'Ya', cancelText = 'Batal') {
    return MarvellSwal.fire({
        icon: 'question',
        title: title,
        text: message,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        reverseButtons: true
    });
}

// Delete confirmation
function confirmDelete(itemName = 'item ini') {
    return MarvellSwal.fire({
        icon: 'warning',
        title: 'Hapus Data?',
        html: `Apakah Anda yakin ingin menghapus <strong>${itemName}</strong>?<br><small style="color: #FF5252;">Tindakan ini tidak dapat dibatalkan!</small>`,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#FF5252',
        reverseButtons: true
    });
}

// Toast notification (centered, non-blocking)
const MarvellToast = Swal.mixin({
    toast: true,
    position: 'center',
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
    background: '#FFFFFF',
    color: '#1a1a1a',
    iconColor: '#BF3131',
    customClass: {
        popup: 'marvell-toast'
    },
    showClass: {
        popup: 'animate__animated animate__fadeIn animate__faster'
    },
    hideClass: {
        popup: 'animate__animated animate__fadeOut animate__faster'
    },
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

function showToast(message, icon = 'success') {
    return MarvellToast.fire({
        icon: icon,
        title: message
    });
}
