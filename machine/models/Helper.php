<?php 
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */

namespace APIShift\Models;

class Helper {
    /**
     * This is a smart array key mapper that maps the whole keys as a tree
     * It avoids recursion by storing the positions on the tree when working on each child
     * @param   $arr        The array to map
     */
    public static function smartArrayKeys(array $arr) {
        // Array of current parent-child sizes and algorithmic position
        $sizes = [ count($arr) - 1 ];
        $counters = [ 0 ];
        $result = array_keys($arr);

        while(isset($counters[0]) && $counters[0] <= $sizes[0])
        {
            // Determine current counter
            $current_counter = count($counters) - 1;
            $name = [$counters[0]];
            // Remove counter if range done and come back to previous position
            if($counters[$current_counter] >= $sizes[$current_counter])
            {
                unset($sizes[$current_counter]);
                unset($counters[$current_counter]);
                continue;
            }

            // Create key string

            // Add to result

            // If array then extend into child

            $counters[$current_counter]++;
        }
    }
}
?>