<?php
/*
Copyright (c) 2011 Muntasir Mohiuddin<muntasir@bakedproject.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

// WP database information
// connect to mysql database 
$db_name        = "NAME";
$db_host        = "HOST";
$db_user        = "USER";
$db_password    = "PASSWORD";

$url['old'] = "http://www.new-domain.com";
$url['new'] = "http://www.new-domain.com";


$link_id = mysql_connect($db_host, $db_user, $db_password) or die(mysql_error());
mysql_select_db($db_name) or die(mysql_error());

$wp_options_table = "wp_options";

$sql = "SELECT * FROM " . $wp_options_table;

$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result)) {
    foreach($row as $k=>$v) {
        //echo $v . "<br>";
        if(stristr($v, $url['old'])) { // if has old url value
            //echo $k . "=>" . $v . "<br><br>";
            if(is_serialized($v)) { // if data is serialized
                $json_array = unserialize($row['option_value']);//json_decode();
                
                $fixed_json_array = array();
                if(is_array($json_array)) {
                
                    foreach($json_array as $key=>$entry) {
                        $fixed_entry = array();
                        if(is_array($entry)) {
                            
                            foreach($entry as $k1=>$v1){
                                $v1 = str_replace($url['old'], $url['new'], $v1);
                                $fixed_entry[$k1] = $v1;
                            }
                            
                            $fixed_json_array[$key] = $fixed_entry;
                            
                        } else {
                            $fixed_json_array[$key] = $entry;
                        }
                    }
                
                }
                
                echo "<h1>JSON_ARRAY</h1><pre>"; print_r($json_array); echo "</pre>";
                echo "<h1>FIXED_JSON_ARRAY</h1><pre>"; print_r($fixed_json_array); echo "</pre>";
                echo "<br><br>" . serialize($fixed_json_array);
                echo "<h1>******************************************************************************************</h1>";
                $sql = "UPDATE " . $wp_options_table . " SET option_value='" . serialize($fixed_json_array) ."' WHERE option_id='" . $row['option_id'] . "'";
                echo "<br><br>" . $sql . "<br><br>";
                mysql_query($sql);
                
                echo "<h1>updated option_id: " . $row['option_id'] . "</h1><br><br>";
                //_da($row);
            } // end of if data is serialized
        }    
    }
} 


// now update rest of the data
$sql = "UPDATE wp_options SET option_value = replace(option_value, '" . $url['old'] . "', '" . $url['new'] . "')";
mysql_query($sql);

$sql = "UPDATE wp_postmeta SET meta_value = replace(meta_value, '" . $url['old'] . "', '" . $url['new'] . "')";
mysql_query($sql);

$sql = "UPDATE wp_options SET option_value = replace(option_value, '" . $url['old'] . "', '" . $url['new'] . "') WHERE option_name = 'home' OR option_name = 'siteurl'";
mysql_query($sql);

$sql = "UPDATE wp_posts SET guid = replace(guid, '" . $url['old'] . "', '" . $url['new'] . "')";
mysql_query($sql);

$sql = "UPDATE wp_posts SET post_content = replace(post_content, '" . $url['old'] . "', '" . $url['new'] . "')";
mysql_query($sql);

exit();

/**
 * a handy debug function
 */
function _da($array) {
    echo "<pre>";
    print_r($array); 
    echo "</pre>";
}


/**
 * check if data is serialized
 */
function is_serialized( $data ) {
    // if it isn't a string, it isn't serialized
    if ( !is_string( $data ) )
        return false;
    
    $data = trim( $data );
    
    if ( 'N;' == $data )
        return true;
    
    if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
        return false;
    
    switch ( $badions[1] ) {
        case 'a' :
        case 'O' :
        case 's' :
            if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
            return true;
        break;
        
        case 'b' :
        case 'i' :
        case 'd' :
            if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
            return true;
        break;
    }
    
    return false;
}
?>