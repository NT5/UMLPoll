<?php

namespace UMLPoll\Poll;

class Poll {

    public static function insertResponses($poll_id, $response) {
        $stmt = \UMLPoll\Database::prepare("INSERT INTO Poll_Responses (Id_Poll, Id_Response) SELECT ?, MAX(Id_Response) + 1 FROM Poll_Responses WHERE Id_Poll = ?");
        $stmt->bind_param('ii', $poll_id, $poll_id);
        $stmt->execute();
        $stmt->close();

        $response_id = Poll::getResponseCount($poll_id)[1];

        $stmt = \UMLPoll\Database::prepare("INSERT INTO Poll_Answers (Id_Poll, Id_Response, Id_Question, Response) VALUES(?, ?, ? ,?)");
        $stmt->bind_param("iiii", $poll_id, $response_id, $id_question, $id_response);

        \UMLPoll\Database::query("START TRANSACTION");
        foreach ($response as $id_question => $id_response) {
            $stmt->execute();
        }
        $stmt->close();
        \UMLPoll\Database::query("COMMIT");
    }

    public static function existsPoll($poll_id) {
        $exists = FALSE;
        if (is_numeric($poll_id)) {
            $stmt = \UMLPoll\Database::prepare("SELECT Id FROM Polls WHERE Id = ?");
            $stmt->bind_param('i', $poll_id);
            $stmt->execute();
            $stmt->store_result();
            $exists = ($stmt->num_rows > 0 ? TRUE : FALSE);
            $stmt->free_result();
            $stmt->close();
        }
        return $exists;
    }

    public static function get($poll_id) {
        $_poll_body = array();
        $stmt = \UMLPoll\Database::prepare("SELECT Goal, Create_by, Description, Create_at FROM Polls WHERE Id = ?");
        $stmt->bind_param('i', $poll_id);
        $stmt->execute();
        $stmt->bind_result($poll_goal, $poll_createby, $poll_description, $poll_createat);
        $stmt->store_result();
        while ($stmt->fetch()) {
            $_poll_body["id"] = $poll_id;
            $_poll_body["goal"] = $poll_goal;
            $_poll_body["replied"] = Poll::getResponseCount($poll_id)[0];
            $_poll_body["description"] = $poll_description;
            $_poll_body["createby"] = $poll_createby;
            $_poll_body["createat"] = $poll_createat;
            $_poll_body["questions"] = Poll::getQuestions($poll_id);
        }
        $stmt->free_result();
        $stmt->close();

        return (count($_poll_body) === 0 ? NULL : $_poll_body);
    }

    public static function getQuestions($poll_id) {
        $questions = array();
        $stmt = \UMLPoll\Database::prepare("SELECT Id_Question, Body FROM Poll_Questions WHERE Id_Poll = ?");
        $stmt->bind_param('i', $poll_id);
        $stmt->execute();
        $stmt->bind_result($Id_Question, $Question_Body);
        $stmt->store_result();
        while ($stmt->fetch()) {
            array_push($questions, array(
                "id" => $Id_Question,
                "body" => $Question_Body,
                "options" => Poll::getQuestionsOptions($poll_id, $Id_Question)
            ));
        }
        $stmt->free_result();
        $stmt->close();

        return $questions;
    }

    public static function getQuestionsOptions($poll_id, $id_Question) {
        $options = array();
        $stmt = \UMLPoll\Database::prepare("SELECT Id_Option, Value FROM Poll_Questions_Options WHERE Id_Poll = ? AND Id_Question = ?");
        $stmt->bind_param('ii', $poll_id, $id_Question);
        $stmt->execute();
        $stmt->bind_result($Id_Option, $Value);
        $stmt->store_result();
        while ($stmt->fetch()) {
            array_push($options, array(
                "id" => $Id_Option,
                "value" => $Value,
                "votes" => Poll::getVotesCount($poll_id, $id_Question, $Id_Option)
            ));
        }
        $stmt->free_result();
        $stmt->close();

        return $options;
    }

    public static function getVotesCount($poll_id, $id_Question, $Id_Option) {
        $count = 0;
        $stmt = \UMLPoll\Database::prepare("SELECT COUNT(*) FROM Poll_Answers WHERE Id_Poll = ? AND Id_Question = ? AND Response = ?");
        $stmt->bind_param('iii', $poll_id, $id_Question, $Id_Option);
        $stmt->execute();
        $stmt->bind_result($_count);
        $stmt->store_result();
        while ($stmt->fetch()) {
            $count = $_count;
        }
        $stmt->free_result();
        $stmt->close();

        return $count;
    }

    public static function getResponseCount($id_poll) {
        $stmt = \UMLPoll\Database::prepare("SELECT COUNT(*), MAX(Id_Response) FROM Poll_Responses WHERE Id_Poll = ?");
        $stmt->bind_param('i', $id_poll);
        $stmt->execute();
        $stmt->bind_result($_count, $_max);
        $stmt->store_result();
        $stmt->fetch();
        $stmt->free_result();
        $stmt->close();

        return array($_count, $_max);
    }

}
