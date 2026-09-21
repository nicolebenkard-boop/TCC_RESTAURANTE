function copiarLink() {
    const campo = document.querySelector('#linkCardapio');
    const msg = document.querySelector('#msgCopiado');
    if (!campo) return;

    campo.select();
    campo.setSelectionRange(0, 99999);

    navigator.clipboard.writeText(campo.value).then(function () {
        if (msg) {
            msg.classList.add('show');
            setTimeout(() => msg.classList.remove('show'), 2000);
        }
    }).catch(function () {
        // Navegadores mais antigos: o texto já ficou selecionado para Ctrl+C manual
    });
}
