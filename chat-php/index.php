<?php
//receber os dados do formulario de login
$dados= filter_input_array(INPUT_POST, FILTER_DEFAULT);

//cair no if caso o usuario tenha clicado no botão de acessar do fomulario de login
    if (!empty($dados['acessar']))
    {
        //var_dump($dados)--teste
        $_SESSION['usuario'] = $dados['usuario'];

        header("Location: chat.php");
    };
?>