document.addEventListener('DOMContentLoaded', function () {
    const cpfInput = document.querySelector('#cpf');
    const telefoneInput = document.querySelector('#telefone');

    if (cpfInput) {
        cpfInput.addEventListener('input', function () {
            let valor = this.value.replace(/\D/g, '').slice(0, 11);
            valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
            valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
            valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            this.value = valor;
        });
    }

    if (telefoneInput) {
        telefoneInput.addEventListener('input', function () {
            let valor = this.value.replace(/\D/g, '').slice(0, 11);
            if (valor.length > 10) {
                valor = valor.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (valor.length > 5) {
                valor = valor.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (valor.length > 2) {
                valor = valor.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            this.value = valor;
        });
    }
});
