<?php

include_once "updateController.php";

// Define a custom error handler
function customErrorHandler($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    // Convert error to an exception
    throw new ErrorException($message, 0, $severity, $file, $line);
}

// Set the custom error handler
set_error_handler("customErrorHandler");

header("content-type: application/json");
$action = $_GET["action"];
$controller = new Controller();
call_user_func(array($controller,$action));

// a decorator controller so that handling exceptions is easier
class Controller
{
    private $updateController;

    public function __construct()
    {
        $this->updateController = new UpdateController();
    }

    public function __call($name,$arguments)
    {
        try
        {
            return call_user_func([$this->updateController,$name]);
        }
        catch(Exception $ex)
        {
            http_response_code(500);

            $response = [
                'error' => true,
                'message' => $ex->getMessage(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine()
            ];
            echo json_encode($response);

            exit;
        }
    }
}