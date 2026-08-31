-- Execute este script no phpMyAdmin (banco "restaurante") antes de usar o login de funcionário.
-- Adiciona a coluna de senha do funcionário. Enquanto estiver NULL, o sistema entende
-- que o funcionário ainda não trocou a senha provisória (login com "senha123" e é
-- obrigado a cadastrar uma senha nova no primeiro acesso).

ALTER TABLE `tb_funcionarios`
  ADD COLUMN `senha` VARCHAR(255) NULL DEFAULT NULL AFTER `desempenho`;
