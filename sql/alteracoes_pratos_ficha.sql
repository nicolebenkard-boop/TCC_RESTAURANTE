-- ============================================================
-- RESTCONTROL - Alterações necessárias no banco para as telas
-- de Pratos (com imagem) e Ficha Técnica (composição + receita)
-- ============================================================
-- Execute este script no phpMyAdmin: banco "restaurante" -> aba SQL
-- -> cole tudo abaixo -> Executar.
-- Pode rodar tudo de uma vez, os comandos são independentes.

-- 1. Adiciona campo de imagem e de modo de preparo (receita) no prato.
--    O modo de preparo passa a ficar UMA vez por prato (não por ingrediente).
ALTER TABLE tb_pratos
  ADD COLUMN imagem VARCHAR(255) DEFAULT NULL AFTER nome_prato,
  ADD COLUMN modo_preparo TEXT DEFAULT NULL AFTER preco_venda;

-- 2. A coluna modo_preparo que já existia em tb_item_ficha_tecnica era
--    obrigatória (NOT NULL) e ficava duplicada por ingrediente, o que não
--    faz sentido. Ela deixa de ser usada (o modo de preparo agora mora em
--    tb_pratos), então precisa parar de ser obrigatória:
ALTER TABLE tb_item_ficha_tecnica
  MODIFY COLUMN modo_preparo VARCHAR(600) NULL DEFAULT NULL;

-- 3. Adiciona a unidade de medida de cada ingrediente na ficha técnica
--    (g, kg, ml, l, unidade...), para ficar tipo "300 g de feijão".
ALTER TABLE tb_item_ficha_tecnica
  ADD COLUMN unidade VARCHAR(10) NOT NULL DEFAULT 'g' AFTER quantidade_necessaria;

-- 4. Faz com que, se um prato for excluído, os itens da ficha técnica
--    dele sejam excluídos automaticamente junto (evita lixo no banco).
ALTER TABLE tb_item_ficha_tecnica
  ADD CONSTRAINT fk_ficha_prato_cascade
  FOREIGN KEY (id_prato) REFERENCES tb_pratos (id_prato) ON DELETE CASCADE;
