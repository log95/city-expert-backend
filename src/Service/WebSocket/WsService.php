<?php

declare(strict_types=1);

namespace App\Service\WebSocket;

use App\Entity\User;
use Psr\Log\LoggerInterface;

class WsService
{
    private string $wsHost;
    private int $wsPort;
    private int $wsTimeoutMs;

    private LoggerInterface $logger;

    public function __construct(string $wsHost, int $wsPort, int $wsTimeoutMs, LoggerInterface $logger)
    {
        $this->wsHost = $wsHost;
        $this->wsPort = $wsPort;
        $this->logger = $logger;
        $this->wsTimeoutMs = $wsTimeoutMs;
    }

    public function sendMessage(User $user, array $data): bool
    {
        try {
            $context = new \ZMQContext();

            $socket = $context->getSocket(\ZMQ::SOCKET_REQ);

            $socket->setSockOpt(\ZMQ::SOCKOPT_RCVTIMEO, $this->wsTimeoutMs);

            $socket->connect($this->getWsDsn());

            $data['USER_ID'] = $user->getId();

            $socket->send(json_encode($data));

            $result = $socket->recv();

            // Timeout
            if (!$result) {
                return false;
            }

            $result = json_decode($result, true);

            switch ($result['TYPE']) {
                case 'SUCCESS':
                    return true;

                case 'USER_IS_OFFLINE':
                    return false;

                case 'ERROR':
                    throw new \LogicException('WsService send message error: ' . $result['MESSAGE']);

                default:
                    throw new \LogicException('Undefined ws result type.');
            }
        } catch (\ZMQSocketException $e) {
            $this->logger->error($e->getMessage());

            return false;
        }
    }

    private function getWsDsn(): string
    {
        return sprintf('tcp://%s:%s', $this->wsHost, $this->wsPort);
    }
}
