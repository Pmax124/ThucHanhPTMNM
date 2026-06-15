/**
 * Cấu hình jQuery AJAX tự động gửi token
 */
$(document).ajaxSend(function(event, xhr, settings) {
    const token = localStorage.getItem('token');
    if (token) {
        xhr.setRequestHeader('Authorization', 'Bearer ' + token);
    }
});

// Xử lý khi token hết hạn (401)
$(document).ajaxError(function(event, xhr, settings) {
    if (xhr.status === 401) {
        alert('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = 'login.php';
    } else if (xhr.status === 403) {
        const msg = xhr.responseJSON?.message || 'Bạn không có quyền thực hiện thao tác này';
        alert('❌ ' + msg);
    }
});