<?php
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */

namespace APIShift\Core;

class Authorizer {
    private const REQUEST_AS_CODE = 0;
    private const REQUEST_AS_TASK = 2;
    private const REQUEST_INVALID = 3;

    /**
     * Validate the request - check if controller exists as core file, controller or as a task
     * 
     * @param $controller Name of the controller
     * @param $method Name of the method
     * @return int Representing either as controller (REQUEST_AS_CODE), as core file (REQUEST_AS_CORE), as task (REQUEST_AS_TASK) or invalid (REQUEST_INVALID)
     */
    public static function validateRequest($controller, $method) {
        // Check if controller exists
        if(class_exists("APIShift\\Controllers\\" . $controller) && is_callable(["APIShift\\Controllers\\" . $controller, $method]))
        {
            return self::REQUEST_AS_CODE;
        }
        // Check if task exists
        else {
            if(Task::requestAsTaskExists($controller, $method)) return self::REQUEST_AS_TASK;
            return self::REQUEST_INVALID;
        }

    }

    /**
     * Runs a authorization process defined in the system before calling a controller/core/task.
     * Also places a "BeforeRun" and "AfterRun" task triggers that a developer can attach his own tasks to.
     * 
     * @param $controller Name of the controller
     * @param $method Name of the method
     * @return void
     */
    public static function authorizeAndRun($controller, $method, $data = [])
    {
        // Step 1: Find controller & method
        $request_type = Authorizer::validateRequest($controller, $method);
        if($request_type == self::REQUEST_INVALID) Status::message(Status::ERROR, "Invalid Request Form: Please check controller or method name");

        // Step 2: Call authorization tasks
        if(!Task::validateResult(Task::runAuthorizationTasks($controller, $method))) Status::message(Status::ERROR, "You have no authorization to access this request");
        
        // Step 3: Tasks triggered before request
        Task::placeTrigger("BeforeRun");
        
        // Step 4: Run request
        if($request_type == self::REQUEST_AS_CODE) ("APIShift\\Controllers\\" . $controller)::$method($data);
        else if($request_type == self::REQUEST_AS_TASK) Task::runRequestTask($controller, $method, $data);

        // Step 5: Tasks triggered after request
        Task::placeTrigger("AfterRun");
    }

    /**
     * Exit if not in a certain state
     * 
     * @param int $state State ID to check
     * @return void
     */
    public static function authorizeByState($state = null) {
        if($state == null) $state = SessionState::getStateID("ADMIN_STATE"); // Get admin state ID by default
        if(SessionState::getSessionState() != $state) Status::message(Status::ERROR, "Unauthorized request");
    }

    /**
     * Validates if user and password matches the admin state
     * 
     * @param string $user User name to login with
     * @param string $pass Password entered
     * @return bool If the test passed or not
     */
    public static function adminState($user, $pass) {
        $credentials = [];
        DatabaseManager::fetchInto("main", $credentials, "SELECT  password FROM admin_users WHERE username = :username", [ 'username' => $user ]);
        if(!is_array($credentials) || count($credentials) == 0 || !password_verify($pass, $credentials[0]['password'])) return false;
        return true;
    }
}
?>