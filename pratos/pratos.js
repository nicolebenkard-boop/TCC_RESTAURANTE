// pratos.js - Gerenciamento do Modal de Cadastro/Edição de Pratos

document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("pratoModal");
    const openBtn = document.getElementById("openModalBtn");
    const closeBtn = document.getElementById("closeModalBtn");
    const form = document.getElementById("formPrato");
    const modalTitulo = document.getElementById("modalTitulo");
    const inputId = document.getElementById("id_prato");
    const inputNome = document.getElementById("nome_prato");
    const inputPreco = document.getElementById("preco_venda");
    const inputImagem = document.getElementById("imagem");
    const previewImagem = document.getElementById("previewImagem");
    const dicaEdicao = document.getElementById("dicaEdicao");

    function abrirModalNovo() {
        form.reset();
        inputId.value = "";
        modalTitulo.textContent = "Cadastrar Prato";
        previewImagem.style.display = "none";
        dicaEdicao.style.display = "none";
        modal.style.display = "flex";
    }

    function abrirModalEdicao(botao) {
        form.reset();
        inputId.value = botao.dataset.id;
        inputNome.value = botao.dataset.nome;
        inputPreco.value = botao.dataset.preco;
        modalTitulo.textContent = "Editar Prato";
        previewImagem.style.display = "none";
        dicaEdicao.style.display = "block";
        modal.style.display = "flex";
    }

    if (openBtn) {
        openBtn.addEventListener("click", abrirModalNovo);
    }

    // Abre o modal já preenchido ao clicar em "Editar" em qualquer card
    document.querySelectorAll(".btn-editar").forEach(function (botao) {
        botao.addEventListener("click", function () {
            abrirModalEdicao(botao);
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            modal.style.display = "none";
        });
    }

    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    // Pré-visualização da imagem escolhida antes de enviar
    if (inputImagem) {
        inputImagem.addEventListener("change", function () {
            const arquivo = this.files[0];
            if (!arquivo) {
                previewImagem.style.display = "none";
                return;
            }
            const leitor = new FileReader();
            leitor.onload = function (e) {
                previewImagem.src = e.target.result;
                previewImagem.style.display = "block";
            };
            leitor.readAsDataURL(arquivo);
        });
    }
});
