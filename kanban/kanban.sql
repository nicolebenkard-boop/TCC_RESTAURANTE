-- BANCO: restaurante
-- Se ainda não criou a tabela, execute apenas o bloco CREATE.
CREATE TABLE IF NOT EXISTS tb_kanban_tarefas (
    id_tarefa INT NOT NULL AUTO_INCREMENT,
    id_gestor INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    status ENUM('ideias','a_fazer','em_progresso','em_revisao','concluida') NOT NULL DEFAULT 'ideias',
    prioridade ENUM('baixa','media','alta') NOT NULL DEFAULT 'media',
    data_limite DATE NULL,
    data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_tarefa),
    KEY idx_kanban_gestor_status (id_gestor, status),
    CONSTRAINT fk_kanban_gestor FOREIGN KEY (id_gestor)
        REFERENCES tb_gestor(id_gestor) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- IMPORTANTE: se você já criou a tabela da versão anterior,
-- execute este bloco UMA VEZ para adaptar os status antigos ao novo layout.
UPDATE tb_kanban_tarefas SET status = 'a_fazer' WHERE status = 'pendente';
UPDATE tb_kanban_tarefas SET status = 'em_progresso' WHERE status = 'em_andamento';
ALTER TABLE tb_kanban_tarefas
MODIFY status ENUM('ideias','a_fazer','em_progresso','em_revisao','concluida') NOT NULL DEFAULT 'ideias';
