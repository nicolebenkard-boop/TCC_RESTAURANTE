-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 31/08/2026 às 22:09
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `restaurante`
--
CREATE DATABASE IF NOT EXISTS `restaurante` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `restaurante`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_desperdicio`
--

CREATE TABLE `tb_desperdicio` (
  `id_desperdicio` int(11) NOT NULL,
  `id_ingrediente` int(11) NOT NULL,
  `qtd_desperdiçada` int(11) NOT NULL,
  `data_registro` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `motivo` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_estoque`
--

CREATE TABLE `tb_estoque` (
  `id_ingrediente` int(11) NOT NULL,
  `id_gestor` int(11) NOT NULL,
  `nome_ingredientes` varchar(30) NOT NULL,
  `quantidade_atual` int(11) NOT NULL,
  `quantidade_minima` int(11) NOT NULL,
  `quantidade_maxima` int(11) NOT NULL,
  `custo_unitario` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_estoque`
--

INSERT INTO `tb_estoque` (`id_ingrediente`, `id_gestor`, `nome_ingredientes`, `quantidade_atual`, `quantidade_minima`, `quantidade_maxima`, `custo_unitario`) VALUES
(2, 5, 'Arroz', 1000, 100, 1500, 25);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_fornecedores`
--

CREATE TABLE `tb_fornecedores` (
  `id_gestor` int(11) NOT NULL,
  `id_fornecedor` int(11) NOT NULL,
  `nome_fornecedor` text NOT NULL,
  `produto_fornecido` text NOT NULL,
  `qtd_total_produto` int(11) NOT NULL,
  `data_reabastecimento` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_fornecedores`
--

INSERT INTO `tb_fornecedores` (`id_gestor`, `id_fornecedor`, `nome_fornecedor`, `produto_fornecido`, `qtd_total_produto`, `data_reabastecimento`) VALUES
(1, 4, 'Atacadão', 'Arroz', 200, '2026-07-16 03:00:00'),
(1, 5, 'JP story', 'Feijão', 200, '2026-09-25 03:00:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_funcionarios`
--

CREATE TABLE `tb_funcionarios` (
  `id_funcionario` int(11) NOT NULL,
  `id_gestor` int(11) NOT NULL,
  `nome_completo` text NOT NULL,
  `CPF_funcionario` varchar(14) NOT NULL,
  `inicio_trabalho` date NOT NULL,
  `desempenho` varchar(100) NOT NULL,
  `senha` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_funcionarios`
--

INSERT INTO `tb_funcionarios` (`id_funcionario`, `id_gestor`, `nome_completo`, `CPF_funcionario`, `inicio_trabalho`, `desempenho`, `senha`) VALUES
(6, 1, 'Vagner Felipe Batista', '49247588748', '2026-06-14', 'Está em treinamento', NULL),
(7, 1, 'Nicole Nizoli Benkard', '49247588748', '2000-04-15', 'Está em treinamento', NULL),
(8, 5, 'Nicole Nizoli Benkard', '46915485805', '2026-08-31', 'Em treinamento', '$2y$10$p/dLvV5.PhsU43f8eulY8.oUAkzdk11si9agU0QuQrqxloBzCic9.');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_gestor`
--

CREATE TABLE `tb_gestor` (
  `CNPJ` varchar(18) NOT NULL,
  `CPF` varchar(14) NOT NULL,
  `id_gestor` int(11) NOT NULL,
  `nome_gestor` text NOT NULL,
  `e_mail` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `restaurante` varchar(100) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `status` enum('pendente','ativo') DEFAULT 'pendente',
  `data_vencimento` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_gestor`
--

INSERT INTO `tb_gestor` (`CNPJ`, `CPF`, `id_gestor`, `nome_gestor`, `e_mail`, `senha`, `restaurante`, `telefone`, `status`, `data_vencimento`) VALUES
('11111111', '46915485805', 5, 'Nicole Nizoli Benkard', 'nicole.benkard@aluno.senai.br', '$2y$10$mY3ajuJk4sowI7eNdxN5AO2eI188gZJLjPHNWwIEKEejGVwYtp/Z.', 'Nicole Nizoli Benkard', '11111111111', 'ativo', '2026-09-23');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_item_ficha_tecnica`
--

CREATE TABLE `tb_item_ficha_tecnica` (
  `id_item_ficha` int(11) NOT NULL,
  `id_prato` int(11) NOT NULL,
  `id_ingrediente` int(11) NOT NULL,
  `quantidade_necessaria` decimal(10,0) NOT NULL,
  `unidade` varchar(10) NOT NULL DEFAULT 'g',
  `modo_preparo` varchar(600) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_item_ficha_tecnica`
--

INSERT INTO `tb_item_ficha_tecnica` (`id_item_ficha`, `id_prato`, `id_ingrediente`, `quantidade_necessaria`, `unidade`, `modo_preparo`) VALUES
(1, 0, 2, 20, 'g', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_itens_pedido`
--

CREATE TABLE `tb_itens_pedido` (
  `id_itens` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_prato` int(11) NOT NULL,
  `quantidade_vendida` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_kanban_tarefas`
--

CREATE TABLE `tb_kanban_tarefas` (
  `id_tarefa` int(11) NOT NULL,
  `id_gestor` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `responsavel` varchar(150) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `status` enum('ideias','a_fazer','em_progresso','em_revisao','concluida') NOT NULL DEFAULT 'ideias',
  `prioridade` enum('baixa','media','alta','urgente') NOT NULL DEFAULT 'media',
  `data_limite` date DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_kanban_tarefas`
--

INSERT INTO `tb_kanban_tarefas` (`id_tarefa`, `id_gestor`, `titulo`, `descricao`, `responsavel`, `tags`, `status`, `prioridade`, `data_limite`, `data_criacao`, `data_atualizacao`) VALUES
(12, 5, 'organizar os documentos', 'fhgxtgfcyhcjhv', 'ni', 'Precisa fazer hoje', 'ideias', 'baixa', '2026-09-26', '2026-08-25 17:11:04', '2026-08-25 17:11:04');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_layout`
--

CREATE TABLE `tb_layout` (
  `id_layout` int(11) NOT NULL,
  `id_gestor` int(11) NOT NULL,
  `nome_layout` varchar(255) NOT NULL,
  `id_cenario` varchar(50) NOT NULL,
  `largura_m` decimal(6,2) DEFAULT NULL,
  `comprimento_m` decimal(6,2) DEFAULT NULL,
  `dados_layout` longtext DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_pedidos`
--

CREATE TABLE `tb_pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_gestor` int(11) NOT NULL,
  `data_pedido` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `valor_total` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_pratos`
--

CREATE TABLE `tb_pratos` (
  `id_prato` int(11) NOT NULL,
  `id_gestor` int(11) NOT NULL,
  `nome_prato` text NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `preco_venda` decimal(10,0) NOT NULL,
  `modo_preparo` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_pratos`
--

INSERT INTO `tb_pratos` (`id_prato`, `id_gestor`, `nome_prato`, `imagem`, `preco_venda`, `modo_preparo`) VALUES
(0, 5, 'Risoto de alho poró', 'prato_6a8c9434bc521_1787597876.webp', 50, 'modo de fazer:\r\nmisture tudo'),
(1, 1, 'Risoto de alho poro', NULL, 50, NULL),
(2, 1, 'guarana', NULL, 20, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `tb_desperdicio`
--
ALTER TABLE `tb_desperdicio`
  ADD PRIMARY KEY (`id_desperdicio`),
  ADD KEY `fk_desperdicio_ingrediente` (`id_ingrediente`);

--
-- Índices de tabela `tb_estoque`
--
ALTER TABLE `tb_estoque`
  ADD PRIMARY KEY (`id_ingrediente`),
  ADD KEY `fk_estoque_gestor` (`id_gestor`);

--
-- Índices de tabela `tb_fornecedores`
--
ALTER TABLE `tb_fornecedores`
  ADD PRIMARY KEY (`id_fornecedor`),
  ADD KEY `fk_fornecedores_gestor` (`id_gestor`);

--
-- Índices de tabela `tb_funcionarios`
--
ALTER TABLE `tb_funcionarios`
  ADD PRIMARY KEY (`id_funcionario`),
  ADD KEY `fk_funcionarios_gestor` (`id_gestor`);

--
-- Índices de tabela `tb_gestor`
--
ALTER TABLE `tb_gestor`
  ADD PRIMARY KEY (`id_gestor`);

--
-- Índices de tabela `tb_item_ficha_tecnica`
--
ALTER TABLE `tb_item_ficha_tecnica`
  ADD PRIMARY KEY (`id_item_ficha`),
  ADD KEY `fk_ficha_prato` (`id_prato`),
  ADD KEY `fk_ficha_ingrediente` (`id_ingrediente`);

--
-- Índices de tabela `tb_itens_pedido`
--
ALTER TABLE `tb_itens_pedido`
  ADD PRIMARY KEY (`id_itens`),
  ADD KEY `fk_itens_pedido_pedido` (`id_pedido`),
  ADD KEY `fk_itens_pedido_prato` (`id_prato`);

--
-- Índices de tabela `tb_kanban_tarefas`
--
ALTER TABLE `tb_kanban_tarefas`
  ADD PRIMARY KEY (`id_tarefa`),
  ADD KEY `idx_kanban_gestor_status` (`id_gestor`,`status`);

--
-- Índices de tabela `tb_layout`
--
ALTER TABLE `tb_layout`
  ADD PRIMARY KEY (`id_layout`),
  ADD KEY `fk_layout_gestor` (`id_gestor`);

--
-- Índices de tabela `tb_pedidos`
--
ALTER TABLE `tb_pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `fk_pedidos_gestor` (`id_gestor`);

--
-- Índices de tabela `tb_pratos`
--
ALTER TABLE `tb_pratos`
  ADD PRIMARY KEY (`id_prato`),
  ADD KEY `fk_pratos_gestor` (`id_gestor`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `tb_desperdicio`
--
ALTER TABLE `tb_desperdicio`
  MODIFY `id_desperdicio` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_estoque`
--
ALTER TABLE `tb_estoque`
  MODIFY `id_ingrediente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `tb_fornecedores`
--
ALTER TABLE `tb_fornecedores`
  MODIFY `id_fornecedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `tb_funcionarios`
--
ALTER TABLE `tb_funcionarios`
  MODIFY `id_funcionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `tb_gestor`
--
ALTER TABLE `tb_gestor`
  MODIFY `id_gestor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `tb_item_ficha_tecnica`
--
ALTER TABLE `tb_item_ficha_tecnica`
  MODIFY `id_item_ficha` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `tb_itens_pedido`
--
ALTER TABLE `tb_itens_pedido`
  MODIFY `id_itens` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_kanban_tarefas`
--
ALTER TABLE `tb_kanban_tarefas`
  MODIFY `id_tarefa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `tb_layout`
--
ALTER TABLE `tb_layout`
  MODIFY `id_layout` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `tb_pedidos`
--
ALTER TABLE `tb_pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `tb_item_ficha_tecnica`
--
ALTER TABLE `tb_item_ficha_tecnica`
  ADD CONSTRAINT `fk_ficha_prato_cascade` FOREIGN KEY (`id_prato`) REFERENCES `tb_pratos` (`id_prato`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tb_kanban_tarefas`
--
ALTER TABLE `tb_kanban_tarefas`
  ADD CONSTRAINT `fk_kanban_gestor` FOREIGN KEY (`id_gestor`) REFERENCES `tb_gestor` (`id_gestor`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tb_layout`
--
ALTER TABLE `tb_layout`
  ADD CONSTRAINT `fk_layout_gestor` FOREIGN KEY (`id_gestor`) REFERENCES `tb_gestor` (`id_gestor`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
