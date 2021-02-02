<?php

namespace UMLPoll\Poll;

class Token {

    public static function generate() {
        return md5(uniqid(rand(), TRUE));
    }

    public static function isValid($poll_id, $token) {
        $exists = FALSE;
        $stmt = \UMLPoll\Database::prepare("SELECT Token FROM Poll_Tokens WHERE Id_Poll = ? AND Token = ?");
        $stmt->bind_param('is', $poll_id, $token);
        $stmt->execute();
        $stmt->store_result();
        $exists = ($stmt->num_rows > 0 ? TRUE : FALSE);
        $stmt->free_result();
        $stmt->close();
        return $exists;
    }

    public static function insert($poll_id, $token) {
        $stmt = \UMLPoll\Database::prepare("INSERT INTO Poll_Tokens (Id_Poll, Token) VALUES(?, ?)");
        $stmt->bind_param('is', $poll_id, $token);
        $stmt->execute();
        $stmt->close();
    }

    public static function delete($poll_id, $token) {
        $stmt = \UMLPoll\Database::prepare("DELETE FROM Poll_Tokens WHERE Id_Poll = ? AND Token = ?");
        $stmt->bind_param('is', $poll_id, $token);
        $stmt->execute();
        $stmt->close();
    }

}
