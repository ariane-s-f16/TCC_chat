<?php
//LÓGICA
//IoServer (loop principal)
 //└── HttpServer (handshake HTTP)
 //└── WsServer (protocolo WebSocket)
   //   └── Sistemachat (sua lógica: onOpen, onMessage, onClose, onError)
use Ratchet\Server\IoServer;
require __DIR__ . "/vendor/autoload.php";

//IoServer::factory(..., 8080)
//Cria de fato o servidor na porta 8080.
//Ele escuta todas as conexões e mantém o loop de eventos ativo, encaminhando cada mensagem para a lógica do seu chat (Sistemachat)
$server = IoServer:: factory(
    new HttpServer(
        //HttpServer(...)
        //HttpServer é uma camada intermediária que permite que o WebSocket funcione sobre HTTP, porque o WebSocket começa como um pedido HTTP de handshake.
        //Então o HttpServer cuida do handshake e entrega a conexão para o WsServer.
            WsServer(
                new Sistemachat()
                //WsServer(...)
                //WsServer envolve sua classe Sistemachat e transforma a lógica do chat em um servidor compatível com WebSockets.
                //Sem isso, o Ratchet não saberia como “conversar” usando o protocolo WebSocket.
            )
        ),
    8080
);
$server->run();
//$server->run();
//Coloca o servidor para rodar infinitamente.
//O script fica ativo, esperando clientes se conectarem e processando mensagens em tempo real.


    

