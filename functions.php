<?php


add_action('admin_enqueue_scripts','wptuts53021_load_admin_script');
function wptuts53021_load_admin_script( $hook ){
    wp_enqueue_script( 
        'wptuts53021_script', //unique handle
        get_template_directory_uri().'/admin.js', //location
        array('jquery')  //dependencies
     );
}


/**
 * Register meta box(es).
 */
function wpdocs_register_meta_boxes() {
  add_meta_box( 'meta-filter-json', __( 'Filter Json', 'textdomain' ), 'wpdocs_my_display_callback', 'page' );
}
add_action( 'add_meta_boxes', 'wpdocs_register_meta_boxes' );
/**
* Meta box display callback.
*
* @param WP_Post $post Current post object.
*/
function wpdocs_my_display_callback( $post ) {
  $outline = '<div>';
  $outline .= '<label for="key_word" style="width:150px; display:inline-block;">'. esc_html__('Key word', 'text-domain') .'</label>';
  $key_word = get_post_meta( $post->ID, 'key_word', true );
  $outline .= '<input type="text" name="key_word" id="key_word" class="key_word" value="'. esc_attr($key_word) .'" style="width:300px;"/>';
  $outline .= '</div>';

  $outline .= '<div>';
  $outline .= '<label for="low_date" style="width:150px; display:inline-block;">'. esc_html__('Low Date', 'text-domain') .'</label>';
  $low_date = get_post_meta( $post->ID, 'low_date', true );
  $outline .= '<input type="text" name="low_date" id="low_date" class="low_date" value="'. esc_attr($low_date) .'" style="width:300px;"/>';
  $outline .= '</div>';

  $outline .= '<div>';
  $outline .= '<label for="high_date" style="width:150px; display:inline-block;">'. esc_html__('High Date', 'text-domain') .'</label>';
  $high_date = get_post_meta( $post->ID, 'high_date', true );
  $outline .= '<input type="text" name="high_date" id="high_date" class="high_date" value="'. esc_attr($high_date) .'" style="width:300px;"/>';

  $outline .= '<div style="border: 1px solid black; width: 100px;text-align: center;" class="filter-json">Filter</div>';

  $outline .= '<div class="list-result"></div>';
  $outline .= '</div>';

  echo $outline;
}


function wpdocs_save_meta_box( $post_id ) {
  update_post_meta( $post_id, "key_word", $_POST['key_word']);
  update_post_meta( $post_id, "low_date", $_POST['low_date']);
  update_post_meta( $post_id, "high_date", $_POST['high_date']);
}

add_action( 'save_post', 'wpdocs_save_meta_box' );



add_action( 'wp_ajax_filter', 'my_ajax_filter_handler' );

function my_ajax_filter_handler() {
  $result = filterJson($_POST['data']['low_date'], $_POST['data']['high_date'], $_POST['data']['key_word']);
  die(json_encode($result));

}




function filterJson($lowRange, $highRange, $key_word){

  $get_data = callAPI('GET', "http://www.mediaprime.it/api.php", false);
  $response = json_decode($get_data, true);
  $errors = $response['response']['errors'];
  
  
  $lowRange = strtotime($lowRange);
  $highRange = strtotime($highRange);
  $key_word = $key_word;
  $resultFilter = array();
  
  if ($err) {
   echo "cURL Error #:" . $err;
  } else {
    foreach ($response as $element){
      $elementDate =  strtotime($element['date_gmt']);
      
      if( $elementDate < $highRange &&  $elementDate > $lowRange ){
        if(empty($key_word)){
          array_push($resultFilter,$element);
          continue;
        }
        if (strpos($element['title']['rendered'], $key_word) === false ) continue;
        array_push($resultFilter,$element);

      }
    }
  }
  return $resultFilter;
  
}





function callAPI($method, $url, $data){
  $curl = curl_init();

  switch ($method){
     case "POST":
        curl_setopt($curl, CURLOPT_POST, 1);
        if ($data)
           curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        break;
     case "PUT":
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        if ($data)
           curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
        break;
     default:
        if ($data)
           $url = sprintf("%s?%s", $url, http_build_query($data));
  }

  // OPTIONS:
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array(
     'Content-Type: application/json',
  ));
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

  // EXECUTE:
  $result = curl_exec($curl);
  if(!$result){die("Connection Failure");}
  curl_close($curl);
  return $result;
}