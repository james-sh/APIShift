<?php
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */

namespace APIShift\Core;

class Status {
    // Hardcoded statuses
    public const ERROR = 0;
    public const SUCCESS = 1;
    // Error cods for installation
    public const NOT_INSTALLED = 2;
    public const DB_CONNECTION_FAILED = 3;
    public const INVALID_CONFIG_FILE = 4;
    private static $output = [ 'status' => Status::SUCCESS, 'data' => "Status haven't changed" ];

    /**
     * Return a status & data to the client & exit
     */
    public static function message($status_code = Status::SUCCESS, $data = null, $message_and_exit = true)
    {
        // Update status
        self::$output['status'] = $status_code;
        // Assign message if present
        if($data !== null && $data !== "") self::$output['data'] = $data;
        // Assign default message if exists
        else if($data === null && session_status() != PHP_SESSION_NONE && isset($_SESSION["StatusCollection"][$status_code]))
            $data = $_SESSION["StatusCollection"][$status_code]["default_message"];
        // Assign empty message if non can be applied
        else self::$output['data'] = "";

        // Send data to UI
        if($message_and_exit) self::respond();
    }

    /**
     * Post the message that was constructed
     */
    public static function respond() {
        echo json_encode(self::$output);
        exit();
    }

    /**
     * Get current status
     */
    public static function getStatus() { return self::$output['status']; }
}

?>