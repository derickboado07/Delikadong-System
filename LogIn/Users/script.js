document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function () {
        document.querySelector('.overlay').classList.add('active');
        document.querySelector('.login-container').classList.add('active');
        document.querySelector('.quotes').classList.add('active');
    }, 200);

    setTimeout(function () {
        document.body.classList.add('loaded');
    }, 20);

    const passwordInput = document.getElementById('password');
    const strengthMessage = document.getElementById('strengthMessage');

    passwordInput.addEventListener('input', function () {
        const password = passwordInput.value;
        let strength = 'Weak';
        let color = 'red';

        if (password.length === 0) {
            strengthMessage.textContent = '';
            return;
        }

        const hasUpper = /[A-Z]/.test(password);
        const hasLower = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSymbol = /[^a-zA-Z0-9]/.test(password);

        if (password.length < 8) {
            strength = 'Weak';
            color = 'red';
        } else if (
            hasUpper &&
            hasLower &&
            (hasNumber || hasSymbol)
        ) {
            strength = 'Medium';
            color = 'orange';
        }

        if (
            hasUpper &&
            hasLower &&
            hasNumber &&
            hasSymbol &&
            password.length >= 8
        ) {
            strength = 'Strong';
            color = 'green';
        }

        strengthMessage.textContent = `strength: ${strength}`;
        strengthMessage.style.color = color;
    });
});