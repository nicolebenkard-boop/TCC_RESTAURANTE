// estoque.js - Controle do Módulo de Estoque
document.addEventListener("DOMContentLoaded", () => {
    const formEstoque = document.getElementById("formEstoque");
    const nomeInput = document.getElementById("nome_ingredientes");
    const custoInput = document.getElementById("custo_unitario");

    if (formEstoque) {
        formEstoque.addEventListener("submit", (e) => {
            if (nomeInput.value.trim() === "" || custoInput.value <= 0) {
                e.preventDefault();
                alert("Por favor, preencha o nome do ingrediente e um custo válido.");
            }
        });
    }
});