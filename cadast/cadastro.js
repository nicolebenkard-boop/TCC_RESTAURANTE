// RESTCONTROL - Validações do Formulário de Cadastro
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("form-cadastro");
    const senha = document.getElementById("senha");
    const confirmaSenha = document.getElementById("confirma_senha");
    const errorMessage = document.getElementById("error-message");

    if (form) {
        form.addEventListener("submit", function (e) {
            // Limpa mensagens de erro anteriores
            errorMessage.style.display = "none";
            errorMessage.innerText = "";

            // Validação de correspondência de senha
            if (senha.value !== confirmaSenha.value) {
                e.preventDefault();
                showError("As senhas digitadas não coincidem. Por favor, verifique.");
                confirmaSenha.focus();
                return;
            }

            // Validação simples do tamanho da senha
            if (senha.value.length < 6) {
                e.preventDefault();
                showError("A senha deve conter no mínimo 6 caracteres.");
                senha.focus();
                return;
            }
        });
    }

    function showError(msg) {
        errorMessage.innerText = msg;
        errorMessage.style.display = "block";
    }
});