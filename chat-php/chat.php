<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Chat Privado</title>
</head>
<body>
<h2>Chat Privado</h2>




<input type="text" id="mensagem" placeholder="Mensagem">
<input type="text" id="destinatario" placeholder="ID do usuário destino">
<button onclick="enviarMensagem()">Enviar</button>

<div id="mensagens"></div>




<script>
    const token = "SEU_TOKEN_JWT_AQUI"; // Recebido do login Laravel
    const mensagensDiv = document.getElementById("mensagens");

    // Conectar ao servidor Ratchet
    const ws = new WebSocket("ws://localhost:8080");

    ws.onopen = () => {
        // Envia token JWT para autenticar
        ws.send(JSON.stringify({ type:"auth", token: token }));
    };

    ws.onmessage = (msg) => {
        const data = JSON.parse(msg.data);
        if(data.system){
            mensagensDiv.insertAdjacentHTML("beforeend", `<b>SISTEMA:</b> ${data.message}<br>`);
        } else {
            mensagensDiv.insertAdjacentHTML("beforeend", `<b>${data.from}:</b> ${data.message}<br>`);
        }
    };

    function enviarMensagem(){
        const mensagem = document.getElementById("mensagem").value;
        const to = document.getElementById("destinatario").value;

        ws.send(JSON.stringify({ type:"message", to:to, message:mensagem }));
        document.getElementById("mensagem").value = "";
    }
</script>
</body>
</html>
