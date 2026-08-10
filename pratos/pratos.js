// pratos.js - Gerenciamento do Modal de Cadastro

document.addEventListener("DOMContentLoaded", function() {
    const modal = document.getElementById("pratoModal");
    const openBtn = document.getElementById("openModalBtn");
    const closeBtn = document.getElementById("closeModalBtn");

    // Abre o modal ao clicar no botão
    if (openBtn) {
        openBtn.addEventListener("click", () => {
            modal.style.display = "flex";
        });
    }

    // Fecha o modal ao clicar no "X"
    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            modal.style.display = "none";
        });
    }

    // Fecha o modal se o usuário clicar fora da caixa central
    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
});