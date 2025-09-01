<?php

namespace Api\Websocket;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Sistemachat implements MessageComponentInterface
{
    protected $usuarios;

    public function __construct() {
        $this->usuarios = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->usuarios->attach($conn);
        echo "Nova conexão: {$conn->resourceId}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        // Espera: { "to": 3, "message": "Oi" }
        $to = $data['to'] ?? null;
        $message = $data['message'] ?? '';

        if ($to) {
            foreach ($this->usuarios as $user) {
                if ($user->resourceId == $to) {
                    $user->send(json_encode([
                        "from" => $from->resourceId,
                        "message" => $message
                    ]));
                    break;
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->usuarios->detach($conn);
        echo "Usuário {$conn->resourceId} desconectou\n";
    }

    public function onError(ConnectionInterface $conn, Exception $e) {
        echo "Erro: {$e->getMessage()}\n";
        $conn->close();
    }
}
