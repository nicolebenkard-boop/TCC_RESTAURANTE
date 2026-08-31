// RESTCONTROL - Lógica da Página de Assinatura
document.addEventListener("DOMContentLoaded", function () {
    const btnCopy = document.getElementById("btn-copy");
    const pixInput = document.getElementById("pix-key");

    if (btnCopy && pixInput) {
        btnCopy.addEventListener("click", function () {
            // Seleciona e copia o texto do campo Pix
            pixInput.select();
            pixInput.setSelectionRange(0, 99999); // Para navegadores móveis

            navigator.clipboard.writeText(pixInput.value)
                .then(() => {
                    // Feedback visual no botão ao copiar
                    const originalText = btnCopy.innerText;
                    btnCopy.innerText = "Copiado! ✓";
                    btnCopy.style.backgroundColor = "#28a745";
                    btnCopy.style.color = "#ffffff";

                    setTimeout(() => {
                        btnCopy.innerText = originalText;
                        btnCopy.style.backgroundColor = "";
                        btnCopy.style.color = "";
                    }, 2500);
                })
                .catch(err => {
                    alert("Erro ao copiar chave. Copie manualmente: " + pixInput.value);
                });
        });
    }
});