<?php
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */

namespace APIShift\Core;

class Task {
    /**
     * Places a trigger and calls all the tasks that are part of the trigger
     * @param string $ref_name Name of the trigger
     * @return void
     */
    public static function placeTrigger($ref_name) {
        // TODO: Call the tasks associated withthe trigger
        // If non are found - then do nothing
    }

    /**
     * Checks if a request exiasts as a task
     * 
     * @param string $controller Controller name
     * @param string $method Method name
     * @return bool TRUE if request exists as a task, FALSE otherwise
     */
    public static function requestAsTaskExists($controller, $method) {
        // TODO: Check if the controller and method have an associated class
        return false;
    }

    /**
     * Run the request task
     * @param string $controller Controller name
     * @param string $method Method name
     */
    public static function runRequestTask($controller, $method, $data = []) {
        if(!self::requestAsTaskExists($controller, $method)) Status::message(Status::ERROR, "Controller or method are not defined");
        // TODO: Run the task associated with the request
    }

    /**
     * Find all authorization tasks related to the current request
     * 
     * @param string $controller Name of the controller to authorize
     * @param string $method Name of the method to authorize
     * @return array Collection of results from running the tasks
     */
    public static function runAuthorizationTasks($controller, $method) {
        // Cannot continue using the DB if system not connected
        if (!Configurations::INSTALLED) return [];

        // Find all tasks associated to request
        $task_collection = [];
        DatabaseManager::fetchInto("main", $task_collection,
            "SELECT task FROM request_authorization WHERE controller = :controller AND (method = :method OR method = '*')",
            array(
                'controller' => $controller,
                'method' => $method
            )
        );
        if(!is_array($task_collection) || count($task_collection) == 0) return [];

        // Filter tasks
        $task_list = [];
        // Construct valid tasks list
        foreach($task_collection as $task) $task_list[] = $task['task'];
        // Run valid tasks
        return Task::run($task_list);
    }

    /**
     * Run a task list and store results
     * 
     * @param array|int $task_list The tasks IDs to run
     * @param array& $params Refernce to the parameters to use for authentication
     * @return array Collection of the results of the tasks
     */
    public static function run($task_list = [], &$params = []) {
        // Add to array if not so that no modification to the code will be added
        if(!is_array($task_list)) $task_list = [$task_list];
        
        $results = []; // results collection
        $processes_to_compile = []; // Processes to compile

        // Load procedure connections from DB
        DatabaseManager::fetchInto("main", $processes_to_compile, 
            "SELECT processes.id AS proc_id, connections.* FROM tasks
                JOIN task_processes ON tasks.id = task_processes.task
                JOIN processes ON processes.id = task_processes.process
                JOIN process_connections ON process_connections.process = processes.id
                JOIN connections ON connections.id = process_connections.connection WHERE tasks.id IN (:task_ids)",
            array("task_ids" => implode(',', $task_list)), 'proc_id', false
        );

        // Run all task processes
        foreach($task_list as $task)
        {
            $results[$task] = [];
            // Loop through connection & compile to reach result
            foreach($processes_to_compile as $process) {
                // Order connections by IDs
                $ordered_connections = [];
                foreach($process as $connection) {
                    $ordered_connections[$connection['id']] = $connection;
                    unset($ordered_connections[$connection['id']]['id']);
                }

                // Compile & store result
                $results[$task][] = Process::compileConnections($ordered_connections, $params);
            }
        }

        return $results;
    }

    /**
     * Check if out of all processes at least one succeeded
     */
    public static function validateResult($task_results = [])
    {
        // If empty return true
        if(count($task_results) == 0) return true;

        // If atleast one process result is true then the results are true
        foreach($task_results as $process_results)
            foreach($process_results as $result)
                if($result != null && $result) return true;

        // If no result is true then return false
        return false;
    }
}
?>