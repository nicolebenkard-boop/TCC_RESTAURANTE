<?php
session_start();

// O projeto pode ter sessões diferentes para gestor e funcionário.
// Aqui encerramos somente a sessão do funcionário.
unset(
    $_SESSION['funcionario_id'],
    $_SESSION['funcionario_nome'],
    $_SESSION['funcionario_gestor_id'],
    $_SESSION['funcionario_primeiro_login']
);

header('Location: ../pag_login/index.php');
exit();
?>
