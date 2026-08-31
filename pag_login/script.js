document.addEventListener('DOMContentLoaded', function () {
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#password');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function () {
            // Alterna o tipo do input entre password e text
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Altera o ícone do olho aberto / fechado
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }

    // --- Checkbox "Sou funcionário": só ajusta rótulo/placeholder/máscara.
    // A decisão real de qual login usar é feita no PHP a partir do checkbox marcado ou não. ---
    const chkFuncionario = document.querySelector('#chkFuncionario');
    const identificadorInput = document.querySelector('#identificador');
    const labelIdentificador = document.querySelector('#labelIdentificador');
    const iconIdentificador = document.querySelector('#iconIdentificador');

    function aplicarMascaraCpf() {
        let valor = identificadorInput.value.replace(/\D/g, '').slice(0, 11);
        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        identificadorInput.value = valor;
    }

    function atualizarCampoIdentificador() {
        if (!chkFuncionario || !identificadorInput || !labelIdentificador) return;

        if (chkFuncionario.checked) {
            labelIdentificador.textContent = 'CPF';
            identificadorInput.placeholder = 'Ex: 000.000.000-00';
            identificadorInput.setAttribute('inputmode', 'numeric');
            if (iconIdentificador) {
                iconIdentificador.classList.remove('fa-envelope');
                iconIdentificador.classList.add('fa-id-card');
            }
            identificadorInput.addEventListener('input', aplicarMascaraCpf);
        } else {
            labelIdentificador.textContent = 'E-mail';
            identificadorInput.placeholder = 'Ex: gestor@restcontrol.com';
            identificadorInput.removeAttribute('inputmode');
            if (iconIdentificador) {
                iconIdentificador.classList.remove('fa-id-card');
                iconIdentificador.classList.add('fa-envelope');
            }
            identificadorInput.removeEventListener('input', aplicarMascaraCpf);
        }
    }

    if (chkFuncionario) {
        chkFuncionario.addEventListener('change', atualizarCampoIdentificador);
    }
});
