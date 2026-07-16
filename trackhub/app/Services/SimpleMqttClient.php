<?php

namespace App\Services;

use RuntimeException;

class SimpleMqttClient
{
    /** @var resource|null */
    private $socket = null;

    private int $packetId = 1;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $clientId,
        private readonly ?string $username = null,
        private readonly ?string $password = null,
        private readonly int $keepalive = 30,
        private readonly int $timeout = 10,
    ) {
    }

    public function connect(): void
    {
        $errno = 0;
        $errstr = '';
        $this->socket = @stream_socket_client(
            "tcp://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            $this->timeout,
            STREAM_CLIENT_CONNECT
        );

        if (! is_resource($this->socket)) {
            throw new RuntimeException("Unable to connect to MQTT broker: {$errstr} ({$errno})");
        }

        stream_set_timeout($this->socket, $this->timeout);
        stream_set_blocking($this->socket, true);

        $flags = 0x02;
        $payload = $this->string($this->clientId);

        if ($this->username !== null && $this->username !== '') {
            $flags |= 0x80;
            $payload .= $this->string($this->username);
        }

        if ($this->password !== null && $this->password !== '') {
            $flags |= 0x40;
            $payload .= $this->string($this->password);
        }

        $variableHeader = $this->string('MQTT').chr(0x04).chr($flags).pack('n', $this->keepalive);
        $this->write(chr(0x10).$this->remainingLength(strlen($variableHeader.$payload)).$variableHeader.$payload);

        $response = $this->readPacket();
        if (($response['type'] ?? null) !== 0x20 || strlen($response['payload']) < 2 || ord($response['payload'][1]) !== 0) {
            throw new RuntimeException('MQTT CONNACK failed.');
        }
    }

    public function subscribe(string $topic): void
    {
        $packetId = $this->nextPacketId();
        $payload = pack('n', $packetId).$this->string($topic).chr(0x00);
        $this->write(chr(0x82).$this->remainingLength(strlen($payload)).$payload);

        $response = $this->readPacket();
        if (($response['type'] ?? null) !== 0x90) {
            throw new RuntimeException('MQTT SUBACK failed.');
        }
    }

    /**
     * @return array{topic: string, payload: string}|null
     */
    public function readMessage(): ?array
    {
        while (true) {
            $packet = $this->readPacket();
            if ($packet === null) {
                $this->ping();

                continue;
            }

            $packetType = $packet['type'] & 0xF0;

            if ($packetType === 0x30) {
                $payload = $packet['payload'];
                if (strlen($payload) < 2) {
                    return null;
                }

                $topicLength = unpack('n', substr($payload, 0, 2))[1];
                $topic = substr($payload, 2, $topicLength);
                $message = substr($payload, 2 + $topicLength);

                return ['topic' => $topic, 'payload' => $message];
            }

            if ($packetType === 0xD0) {
                continue;
            }
        }
    }

    public function disconnect(): void
    {
        if (! is_resource($this->socket)) {
            return;
        }

        @fwrite($this->socket, chr(0xE0).chr(0x00));
        @fclose($this->socket);
        $this->socket = null;
    }

    /**
     * @return array{type: int, payload: string}|null
     */
    private function readPacket(): ?array
    {
        $first = $this->readBytes(1, true);
        if ($first === null) {
            return null;
        }

        $remainingLength = 0;
        $multiplier = 1;

        do {
            $encoded = ord($this->readBytes(1));
            $remainingLength += ($encoded & 127) * $multiplier;
            $multiplier *= 128;
        } while (($encoded & 128) !== 0);

        return [
            'type' => ord($first),
            'payload' => $remainingLength > 0 ? $this->readBytes($remainingLength) : '',
        ];
    }

    private function ping(): void
    {
        $this->write(chr(0xC0).chr(0x00));
    }

    private function readBytes(int $length, bool $allowTimeout = false): ?string
    {
        if (! is_resource($this->socket)) {
            throw new RuntimeException('MQTT socket is not connected.');
        }

        $buffer = '';
        while (strlen($buffer) < $length) {
            $chunk = fread($this->socket, $length - strlen($buffer));
            if ($chunk === false || $chunk === '') {
                $meta = stream_get_meta_data($this->socket);
                if ($allowTimeout && ($meta['timed_out'] ?? false)) {
                    return null;
                }

                throw new RuntimeException('MQTT connection closed.');
            }

            $buffer .= $chunk;
        }

        return $buffer;
    }

    private function write(string $packet): void
    {
        if (! is_resource($this->socket)) {
            throw new RuntimeException('MQTT socket is not connected.');
        }

        if (fwrite($this->socket, $packet) === false) {
            throw new RuntimeException('Unable to write MQTT packet.');
        }
    }

    private function string(string $value): string
    {
        return pack('n', strlen($value)).$value;
    }

    private function remainingLength(int $length): string
    {
        $encoded = '';

        do {
            $digit = $length % 128;
            $length = intdiv($length, 128);
            if ($length > 0) {
                $digit |= 0x80;
            }

            $encoded .= chr($digit);
        } while ($length > 0);

        return $encoded;
    }

    private function nextPacketId(): int
    {
        $this->packetId++;
        if ($this->packetId > 65535) {
            $this->packetId = 1;
        }

        return $this->packetId;
    }
}
