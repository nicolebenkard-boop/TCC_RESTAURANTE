document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('#formTrocarSenha');
    const novaSenha = document.querySelector('#nova_senha');
    const confirmaSenha = document.querySelector('#confirma_senha');

    if (form && novaSenha && confirmaSenha) {
        form.addEventListener('submit', function (event) {
            if (novaSenha.value !== confirmaSenha.value) {
                event.preventDefault();
                alert('A confirmação de senha não coincide com a nova senha.');
                confirmaSenha.focus();
            }
        });
    }

    const profileButton = document.querySelector('#profileButton');
    const profileMenu = document.querySelector('#profileMenu');

    if (profileButton && profileMenu) {
        profileButton.addEventListener('click', function (event) {
            event.stopPropagation();
            const aberto = profileMenu.classList.toggle('open');
            profileButton.setAttribute('aria-expanded', aberto ? 'true' : 'false');
        });

        document.addEventListener('click', function (event) {
            if (!profileMenu.contains(event.target) && !profileButton.contains(event.target)) {
                profileMenu.classList.remove('open');
                profileButton.setAttribute('aria-expanded', 'false');
            }
        });
    }
});
