<?php

namespace Api\Websocket;
use Exception;
use Ratchet\ConnectionInterface;
Use Ratchet\Websocket\MessageComponentInterface;

class Sistemachat implements MessageComponentInterface
{
    protected $usuarios;

        public function __construct() {
            //INICIANDO O OBJETO QUE VAI ARMAZENAR OS USUARIOS CONECTADOS
            $this-> $usuarios= NEW \SplObjectStorage;
        }

        public function Onopen( ConnectionInterface $conn)
        {
            //adicionando o usuario na lista
            $this-> Usuarios->attach($conn);

            echo "Nova conexão: {$conn -> resourceId}\n\n ";
        }
        //enviar as mensagens para os usuarios conectados
        public function OnMessage(ConnectionInterface $from, $msg)
        {
            //percorre a lista de usuarios conectados
            foreach($this -> $usuarios as $usuarios)
            {
                if($from !== $usuarios )
                {
                    //envia a mensagem para os usuarios
                    $usuarios -> send($msg);
                }
              
            }
            echo " Usuario {$from-> resourceId}\n\n";
        }
        //desconecta o usuario do Websocket
        public function OnClose( ConnectionInterface $conn)
        {
            //encerra com a conexão e retira o usuario da lista
            $this-> usuarios->detach($conn);

            echo "Usuario: {$conn -> resourceId} desconectou. \n\n ";
        }
         //caso de algum erro no Websocket
        public function OnError( ConnectionInterface $conn, Exception $e)
        {
            $conn -> Close();
            echo"Ocorreu um erro: {$e->GetMessage()}\n\n";
        }
    }
   