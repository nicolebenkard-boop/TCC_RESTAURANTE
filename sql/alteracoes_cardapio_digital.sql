-- ============================================================
-- RESTCONTROL - Alterações para o Cardápio Digital do Cliente
-- (tela_clientes), Mesas e Histórico de Pedidos do gestor
-- ============================================================
-- Execute este script no phpMyAdmin: banco "restaurante" -> aba SQL
-- -> cole tudo abaixo -> Executar.

-- 1. Tabela de mesas do restaurante (cada gestor cadastra as suas)
CREATE TABLE IF NOT EXISTS `tb_mesas` (
  `id_mesa` int(11) NOT NULL AUTO_INCREMENT,
  `id_gestor` int(11) NOT NULL,
  `numero_mesa` int(11) NOT NULL,
  `status` enum('ativa','inativa') NOT NULL DEFAULT 'inativa',
  PRIMARY KEY (`id_mesa`),
  UNIQUE KEY `uq_gestor_mesa` (`id_gestor`, `numero_mesa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Token único do gestor, usado no link/QR Code do cardápio digital
--    (evita que um cliente acesse os pratos de outro restaurante).
ALTER TABLE tb_gestor
  ADD COLUMN token_cardapio VARCHAR(32) DEFAULT NULL;

-- 3. tb_pedidos passa a representar a COMANDA de uma mesa: da abertura
--    (quando o cliente entra pelo cardápio digital) até o pagamento
--    (quando o gestor dá baixa manualmente).
ALTER TABLE tb_pedidos
  ADD COLUMN id_mesa INT(11) NULL AFTER id_gestor,
  ADD COLUMN nome_cliente VARCHAR(150) NULL AFTER id_mesa,
  ADD COLUMN cpf_cliente VARCHAR(14) NULL AFTER nome_cliente,
  ADD COLUMN telefone_cliente VARCHAR(20) NULL AFTER cpf_cliente,
  ADD COLUMN status ENUM('aberto','fechado') NOT NULL DEFAULT 'aberto' AFTER valor_total,
  ADD COLUMN data_abertura DATETIME NULL AFTER status,
  ADD COLUMN data_fechamento DATETIME NULL AFTER data_abertura;

-- Permite valores com centavos (ex: 49,90)
ALTER TABLE tb_pedidos
  MODIFY COLUMN valor_total DECIMAL(10,2) NOT NULL DEFAULT 0;

ALTER TABLE tb_pratos
  MODIFY COLUMN preco_venda DECIMAL(10,2) NOT NULL;

-- 4. tb_itens_pedido passa a guardar uma "foto" do nome/preço do prato no
--    momento do pedido, para o histórico não mudar se o prato for
--    renomeado/excluído depois, além do horário de cada item.
ALTER TABLE tb_itens_pedido
  ADD COLUMN nome_prato VARCHAR(255) NULL AFTER id_prato,
  ADD COLUMN preco_unitario DECIMAL(10,2) NULL AFTER nome_prato,
  ADD COLUMN data_hora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER quantidade_vendida;
