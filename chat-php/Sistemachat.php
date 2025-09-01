<?php

namespace App\Websocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * Sistema de chat privado via Ratchet
 * 
 * - Usuários são autenticados via token Laravel
 * - Cada usuário pode ter múltiplas abas
 * - Mensagens são enviadas para ID fixo do banco
 */
class Sistemachat implements MessageComponentInterface
{
    // Armazena todas as conexões ativas por userId
    protected $usuarios = []; // userId => [ConnectionInterface, ...]

    // URL Laravel para validar token
    protected $laravelUrl = "http://localhost:8000/api/validate-token";

    public function onOpen(ConnectionInterface $conn) {
        // Marca que o usuário ainda não está autenticado
        $conn->authenticated = false;
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if(!$data) return;

        // Se ainda não autenticado, o primeiro pacote deve ser o token
        if(empty($from->authenticated) && $data['type'] === 'auth'){
            $token = $data['token'];

            // Valida token via Laravel e obtém userId
            $userId = $this->validarTokenLaravel($token);

            if(!$userId){
                $from->send(json_encode([
                    "system"=>true,
                    "message"=>"Token inválido!"
                ]));
                $from->close();
                return;
            }

            // Associa conexão ao userId
            $from->userId = $userId;
            $from->authenticated = true;
            $this->usuarios[$userId][] = $from;

            $from->send(json_encode([
                "system"=>true,
                "message"=>"Autenticado com sucesso!"
            ]));

            return;
        }

        // Mensagens normais só podem ser enviadas após autenticação
        if(empty($from->authenticated)) return;

        if($data['type'] === 'message'){
            $to = $data['to'] ?? null;
            $message = $data['message'] ?? '';

            // Envia para todas as conexões do usuário destino
            if($to && isset($this->usuarios[$to])){
                foreach($this->usuarios[$to] as $destConn){
                    $destConn->send(json_encode([
                        "from"=>$from->userId,
                        "message"=>$message
                    ]));
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn){
        // Remove conexão do usuário
        if(!empty($conn->userId) && isset($this->usuarios[$conn->userId])){
            $this->usuarios[$conn->userId] = array_filter(
                $this->usuarios[$conn->userId],
                fn($c) => $c !== $conn
            );

            if(empty($this->usuarios[$conn->userId])){
                unset($this->usuarios[$conn->userId]);
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e){
        $conn->close();
    }

    /**
     * Valida token via Laravel
     * Retorna userId ou null
     */
    protected function validarTokenLaravel($token){
        $ch = curl_init($this->laravelUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['token'=>$token]));
        $resp = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($resp, true);
        return $data['userId'] ?? null;
    }
}
