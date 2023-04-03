<?php

class Message
{
    protected $conn;

    // Constructor
    public function __construct($db)
    {
        $this->conn = $db->getConnection();
    }

    // Enviar mensaje
    public function sendMsg($id_sender, $id_recipient, $content, $attached_path, $date_time)
    {
        $stmt = $this->conn->prepare("INSERT INTO Msgs (id_sender, id_recipient, content, attached_path, date_time) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(array($id_sender, $id_recipient, $content, $attached_path, $date_time));
        $mensaje_id = $this->conn->lastInsertId();

        return $mensaje_id;
    }

    // Obtener los mensajes y los archivos de la conversación
    public function getMsgs($id_user1, $id_user2)
    {
        $stmt = $this->conn->prepare("SELECT m.*, u1.username AS sender_username, u2.username AS recipient_username 
                                        FROM Msgs m 
                                        JOIN Users u1 ON m.id_sender = u1.id 
                                        JOIN Users u2 ON m.id_recipient = u2.id
                                        WHERE (m.id_sender = ? AND m.id_recipient = ?)
                                        OR (m.id_sender = ? AND m.id_recipient = ?)
                                        ORDER BY m.date_time DESC");
        $stmt->execute(array($id_user1, $id_user2, $id_user2, $id_user1));
        $messages = array();
        while ($row = $stmt->fetch()) {
            $message = array(
                'id_message' => $row['id'],
                'sender' => array(
                    'id' => $row['id_sender'],
                    'username' => $row['sender_username']
                ),
                'recipient' => array(
                    'id' => $row['id_recipient'],
                    'username' => $row['recipient_username']
                ),
                'content' => $row['content'],
                'attached_path' => $row['attached_path'],
                'date_time' => $row['date_time']
            );
            $messages[] = $message;
        }

        return $messages;
    }

    // Borrar mensaje
    public function delete($id_message, $id_user)
    {
        $stmt = $this->conn->prepare("DELETE FROM Msgs WHERE id = ? AND id_sender = ?");
        $stmt->execute(array($id_message, $id_user));

        // Verificar si se ha borrado algún registro
        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

}

?>