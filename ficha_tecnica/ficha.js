// ficha.js - Filtro de pratos por status da receita (com/sem ficha técnica)

document.addEventListener("DOMContentLoaded", function () {
    const botoesFiltro = document.querySelectorAll(".filtro-btn");
    const cards = document.querySelectorAll(".prato-card[data-status]");

    botoesFiltro.forEach(function (botao) {
        botao.addEventListener("click", function () {
            const filtro = botao.dataset.filtro;

            botoesFiltro.forEach(b => b.classList.remove("ativo"));
            botao.classList.add("ativo");

            cards.forEach(function (card) {
                if (filtro === "todos" || card.dataset.status === filtro) {
                    card.style.display = "block";
                } else {
                    card.style.display = "none";
                }
            });
        });
    });
});
