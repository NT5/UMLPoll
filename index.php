<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

\UMLPoll\Database::connect($DB_SERVER, $DB_USER, $DB_PASS, $DB_DATABASE);
\UMLPoll\Database::charset("utf8");

$loader = new Twig_Loader_Filesystem(__DIR__ . '/res/twig/templates');

$twig = new Twig_Environment($loader, array(
    'cache' => __DIR__ . '/compilation_cache',
    'output_encoding' => 'UTF-8',
    'auto_reload' => true
        )
);

$twig_vars = array(
    'page_title' => 'UML Poll',
    'script_dir' => ""
);

switch (filter_input(INPUT_GET, 'p')) {
    case 'newpoll':
        $template_file = "newpoll/polldone.twig";

        $get_id = filter_input(INPUT_GET, 'id');
        $submit_token = filter_input(INPUT_POST, 'submit');

        if ($get_id !== NULL && $submit_token !== NULL) {
            $_exists_poll = \UMLPoll\Poll\Poll::existsPoll($get_id);
            if ($_exists_poll === TRUE) {
                $_is_valid_token = \UMLPoll\Poll\Token::isValid($get_id, $submit_token);
                if ($_is_valid_token === TRUE) {
                    \UMLPoll\Poll\Token::delete($get_id, $submit_token);
                    $post_vars = filter_input_array(INPUT_POST);
                    $valid_values = [];
                    foreach ($post_vars as $key => $value) {
                        if (preg_match("/^q_(\d+)$/", $key, $result) === 1) {
                            $valid_values[$result[1]] = $value;
                        }
                    }
                    if (count($valid_values) > 0) {
                        $twig_vars['poll_id'] = $get_id;
                        \UMLPoll\Poll\Poll::insertResponses($get_id, $valid_values);
                    } else {
                        $twig_vars['poll_error'] = 4; // No hay datos a ingresar
                    }
                } else {
                    $twig_vars['poll_error'] = 3; // El token es invalido
                }
            } else {
                $twig_vars['poll_error'] = 2; // No existe la encuesta
            }
        } else {
            $twig_vars['poll_error'] = 1; // Link invalido
        }
        break;
    case 'results':
        $template_file = "results/results.twig";
        $poll_array = \UMLPoll\Poll\Poll::get((filter_input(INPUT_GET, 'id') !== NULL ? filter_input(INPUT_GET, 'id') : 1));
        break;
    case 'poll':
        $template_file = "poll/poll.twig";

        $poll_array = \UMLPoll\Poll\Poll::get((filter_input(INPUT_GET, 'id') !== NULL ? filter_input(INPUT_GET, 'id') : 1));
        $poll_token = \UMLPoll\Poll\Token::generate();

        \UMLPoll\Poll\Token::insert(filter_input(INPUT_GET, 'id'), $poll_token);
        $twig_vars['poll_token'] = $poll_token;
        break;
    default:
        $template_file = "home.twig";
        break;
}

$twig_vars['poll_array'] = (isset($poll_array) ? $poll_array : NULL);

echo $twig->render($template_file, $twig_vars);

\UMLPoll\Database::close();
