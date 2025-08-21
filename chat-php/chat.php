<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <H2>CHAT </H2>
    <!--receber as mensagens enviadas pelo js-->
    <span Id="mensagem_chat"></span>
    <script>
        const mensagemChat = document.GetElementById("mensagem_chat");

       const ws=  new Websocket('ws//localhost:')

       ws onopen = (e) =>
       {
        console.log("conectado");
       }
       ws onmessage =(mensagemrecebida) =>
       {
       let resultado= JSON.parse(mensagemrecebida.data);

       mensagemChat= insertAdjacentHTML('beforeend', '${resultado.mensagem}<br>');//falta coisa
       }
    </script>
</body>
</html>