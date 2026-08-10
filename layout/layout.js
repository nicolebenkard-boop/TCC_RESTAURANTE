// layout.js - Renderizador Arquitetônico Dinâmico RestControl v4.0

const layoutsEstrategicos = [
    {
        id: "01",
        nome: "Layout Industrial em Linha (Corredor)",
        descricao: "O layout em linha/corredor apresentado foi validado com sucesso. A disposição sequencial dos módulos impede o cruzamento de fluxos entre insumos crus e pratos finalizados, eliminando riscos de contaminação cruzada e mantendo a distância mínima de circulação de 1,20m nas áreas de passagem técnica conforme exigências da ANVISA (RDC 216).",
        svg: `
            <svg class="svg-engine" viewBox="0 0 700 350" xmlns="http://www.w3.org/2000/svg">
                <rect x="15" y="15" width="670" height="320" class="room-floor" />
                
                <rect x="30" y="40" width="70" height="70" class="object-fridge" rx="4" />
                <text x="65" y="80" class="label-obj">GELADEIRA</text>
                
                <rect x="120" y="40" width="120" height="60" class="object-inox" />
                <text x="180" y="75" class="label-obj">BANCADA</text>
                
                <rect x="260" y="40" width="140" height="60" class="object-inox" />
                <rect x="285" y="50" width="40" height="35" class="object-sink" rx="2" />
                <rect x="345" y="50" width="40" height="35" class="object-sink" rx="2" />
                <text x="330" y="90" class="label-obj">PIA</text>
                
                <rect x="420" y="40" width="130" height="70" class="object-inox" />
                <circle cx="455" cy="65" r="10" class="object-burner" />
                <circle cx="515" cy="65" r="10" class="object-burner" />
                <circle cx="455" cy="95" r="10" class="object-burner" />
                <circle cx="515" cy="95" r="10" class="object-burner" />
                <text x="485" y="125" class="label-obj">FOGÃO</text>

                <rect x="570" y="40" width="90" height="60" class="object-inox" />
                <text x="615" y="75" class="label-obj">BALCÃO</text>

                <rect x="15" y="15" width="670" height="320" class="wall-exterior" />
            </svg>
        `
    },
    {
        id: "02",
        nome: "Layout com Ilha Central de Alta Performance",
        descricao: "Modelo focado em alta gastronomia profissional e cozinhas de grande volume. O bloco quente de cocção fica totalmente centralizado no meio do salão. Isso permite a livre circulação técnica em formato circular (360 graus) e agiliza a comunicação e operação da equipe.",
        svg: `
            <svg class="svg-engine" viewBox="0 0 700 350" xmlns="http://www.w3.org/2000/svg">
                <rect x="15" y="15" width="670" height="320" class="room-floor" />
                
                <rect x="220" y="120" width="260" height="100" class="object-inox" />
                <circle cx="260" cy="150" r="12" class="object-burner" />
                <circle cx="310" cy="150" r="12" class="object-burner" />
                <circle cx="360" cy="150" r="12" class="object-burner" />
                <circle cx="410" cy="150" r="12" class="object-burner" />
                <circle cx="260" cy="190" r="12" class="object-burner" />
                <circle cx="310" cy="190" r="12" class="object-burner" />
                <circle cx="360" cy="190" r="12" class="object-burner" />
                <circle cx="410" cy="190" r="12" class="object-burner" />
                <text x="350" y="240" class="label-obj">FOGÃO INDUSTRIAL (ILHA)</text>

                <rect x="35" y="45" width="70" height="70" class="object-fridge" />
                <text x="70" y="85" class="label-obj">GELADEIRA</text>
                
                <rect x="35" y="140" width="65" height="150" class="object-inox" />
                <text x="67" y="220" class="label-obj">BANCADA</text>

                <rect x="595" y="45" width="75" height="170" class="object-inox" />
                <rect x="605" y="70" width="55" height="40" class="object-sink" rx="2" />
                <text x="632" y="145" class="label-obj">PIA</text>

                <rect x="15" y="15" width="670" height="320" class="wall-exterior" />
            </svg>
        `
    },
    {
        id: "03",
        nome: "Layout Estratégico em Formato 'L'",
        descricao: "Indicado para plantas quadradas. O formato em 'L' divide perfeitamente o ambiente aproveitando os cantos mortos estruturais. Cria um fluxo de trabalho triangular ergonômico ideal: armazenamento, preparação e cozimento ficam dispostos nos vértices.",
        svg: `
            <svg class="svg-engine" viewBox="0 0 700 350" xmlns="http://www.w3.org/2000/svg">
                <rect x="15" y="15" width="670" height="320" class="room-floor" />
                
                <rect x="35" y="35" width="450" height="65" class="object-inox" />
                <rect x="35" y="100" width="75" height="190" class="object-inox" />
                
                <rect x="40" y="110" width="65" height="65" class="object-fridge" />
                <text x="72" y="145" class="label-obj">GELADEIRA</text>

                <rect x="140" y="45" width="55" height="45" class="object-sink" rx="2" />
                <text x="167" y="105" class="label-obj">PIA</text>

                <rect x="230" y="45" width="110" height="45" class="object-inox" />
                <text x="285" y="72" class="label-obj">BANCADA</text>

                <rect x="370" y="45" width="90" height="45" class="object-inox" />
                <circle cx="395" cy="68" r="9" class="object-burner" />
                <circle cx="435" cy="68" r="9" class="object-burner" />
                <text x="415" y="110" class="label-obj">FOGÃO</text>

                <rect x="250" y="240" width="380" height="65" class="object-inox" />
                <text x="440" y="278" class="label-obj">BALCÃO DE EXPEDIÇÃO</text>

                <rect x="15" y="15" width="670" height="320" class="wall-exterior" />
            </svg>
        `
    },
    {
        id: "04",
        nome: "Layout de Duas Linhas Paralelas (Double-Galley)",
        descricao: "Altamente eficiente para cozinhas com alta concentração de cozinheiros. De um lado fica a linha de produção quente e montagem; do outro lado ficam os estoques frios e pias de limpeza. Exige rigor no controle de movimentação para evitar colisões traseiras.",
        svg: `
            <svg class="svg-engine" viewBox="0 0 700 350" xmlns="http://www.w3.org/2000/svg">
                <rect x="15" y="15" width="670" height="320" class="room-floor" />

                <rect x="35" y="35" width="80" height="65" class="object-fridge" />
                <text x="75" y="72" class="label-obj">GELADEIRA</text>

                <rect x="140" y="35" width="220" height="55" class="object-inox" />
                <text x="250" y="67" class="label-obj">BANCADA</text>

                <rect x="390" y="35" width="260" height="55" class="object-inox" />
                <rect x="420" y="42" width="50" height="40" class="object-sink" rx="2" />
                <rect x="550" y="42" width="50" height="40" class="object-sink" rx="2" />
                <text x="505" y="110" class="label-obj">PIA</text>

                <rect x="35" y="250" width="160" height="65" class="object-inox" />
                <circle cx="70" cy="282" r="10" class="object-burner" />
                <circle cx="110" cy="282" r="10" class="object-burner" />
                <circle cx="150" cy="282" r="10" class="object-burner" />
                <text x="115" y="240" class="label-obj">FOGÃO</text>

                <rect x="220" y="250" width="200" height="65" class="object-inox" />
                <text x="320" y="287" class="label-obj">BANCADA</text>

                <rect x="450" y="250" width="200" height="65" class="object-inox" />
                <text x="550" y="287" class="label-obj">BALCÃO</text>

                <rect x="15" y="15" width="670" height="320" class="wall-exterior" />
            </svg>
        `
    },
    {
        id: "05",
        nome: "Layout Completo em Formato 'U'",
        descricao: "Oferece a máxima capacidade de armazenamento e superfície de bancadas possível. Envolve o cozinheiro em três lados, garantindo que tudo esteja ao alcance das mãos. Perfeito para seções específicas de um restaurante, como a área de confeitaria.",
        svg: `
            <svg class="svg-engine" viewBox="0 0 700 350" xmlns="http://www.w3.org/2000/svg">
                <rect x="15" y="15" width="670" height="320" class="room-floor" />

                <rect x="35" y="35" width="630" height="60" class="object-inox" />
                <rect x="35" y="95" width="70" height="200" class="object-inox" />
                <rect x="595" y="95" width="70" height="200" class="object-inox" />

                <rect x="40" y="150" width="60" height="70" class="object-fridge" />
                <text x="70" y="190" class="label-obj">GELADEIRA</text>

                <rect x="75" y="42" width="60" height="45" class="object-sink" rx="2" />
                <text x="105" y="115" class="label-obj">PIA</text>

                <rect x="200" y="42" width="300" height="45" class="object-inox" />
                <text x="350" y="70" class="label-obj">BANCADA</text>

                <rect x="600" y="150" width="60" height="70" class="object-inox" />
                <circle cx="630" cy="170" r="10" class="object-burner" />
                <circle cx="630" cy="200" r="10" class="object-burner" />
                <text x="560" y="190" class="label-obj">FOGÃO</text>

                <rect x="15" y="15" width="670" height="320" class="wall-exterior" />
            </svg>
        `
    }
];

function renderizarLayoutPorID(id) {
    const item = layoutsEstrategicos.find(l => l.id === id);
    if (item) {
        document.getElementById("layout-id-badge").innerText = `ID: ${item.id}`;
        document.getElementById("layout-description").innerText = item.descricao;
        document.getElementById("canvas-container").innerHTML = item.svg;

        // TRAVA O ID DO LAYOUT ATIVO NO INPUT OCULTO DO FORMULÁRIO DE SALVAMENTO
        const inputOculto = document.getElementById("input-layout-id");
        if(inputOculto) {
            inputOculto.value = item.id;
        }
    }
}

document.addEventListener("DOMContentLoaded", () => {
    // Verifica se há alguma ordem de visualização vinda da URL (?ver_id=ARQ-...)
    const urlParams = new URLSearchParams(window.location.search);
    const viewId = urlParams.get('ver_id');

    let indiceAtual = 0;

    if (viewId) {
        const foundIndex = layoutsEstrategicos.findIndex(l => l.id === viewId);
        if (foundIndex !== -1) indiceAtual = foundIndex;
    }

    // Carrega o layout inicial na tela
    renderizarLayoutPorID(layoutsEstrategicos[indiceAtual].id);

    // Configura o clique do botão "Alternar Layout Estratégico"
    const btnAlternar = document.getElementById("btn-alternar");
    if(btnAlternar) {
        btnAlternar.addEventListener("click", () => {
            indiceAtual = (indiceAtual + 1) % layoutsEstrategicos.length;
            renderizarLayoutPorID(layoutsEstrategicos[indiceAtual].id);
        });
    }
});