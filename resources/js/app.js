import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-menu-toggle]');
    const sidebar = document.querySelector('#sidebar');
    const passwordToggle = document.querySelector('[data-password-toggle]');
    const password = document.querySelector('#password');

    toggle?.addEventListener('click', () => sidebar?.classList.toggle('open'));
    passwordToggle?.addEventListener('click', () => {
        if (!password) return;
        const visible = password.type === 'text';
        password.type = visible ? 'password' : 'text';
        passwordToggle.setAttribute('aria-label', visible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
    });
});
