<?php

namespace UMLPoll;

class Database {

    private static $_HOST, $_USER, $_PASSWORD, $_DBNAME;
    private static $_MYSQLI;

    public static function connect($host, $user, $password, $dbname) {
        \UMLPoll\Database::$_HOST = $host;
        \UMLPoll\Database::$_USER = $user;
        \UMLPoll\Database::$_PASSWORD = $password;
        \UMLPoll\Database::$_DBNAME = $dbname;

        \UMLPoll\Database::$_MYSQLI = new \mysqli(\UMLPoll\Database::$_HOST, \UMLPoll\Database::$_USER, \UMLPoll\Database::$_PASSWORD, \UMLPoll\Database::$_DBNAME);

        if (\UMLPoll\Database::$_MYSQLI->connect_errno) {
            echo "Errno: " . \UMLPoll\Database::$_MYSQLI->connect_errno . "\n";
            echo "Error: " . \UMLPoll\Database::$_MYSQLI->connect_error . "\n";
            exit;
        }
    }

    public static function close() {
        \UMLPoll\Database::$_MYSQLI->close();
    }

    public static function setdb($dbstring) {
        \UMLPoll\Database::$_MYSQLI->mysqli_select_db($dbstring);
    }

    public static function query($sql) {
        return \UMLPoll\Database::$_MYSQLI->query($sql);
    }

    public static function prepare($statement) {
        return \UMLPoll\Database::$_MYSQLI->prepare($statement);
    }

    public static function error() {
        return \UMLPoll\Database::$_MYSQLI->error;
    }

    public static function charset($charset) {
        mysqli_set_charset(\UMLPoll\Database::$_MYSQLI, $charset);
    }

}
