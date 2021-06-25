<?php 
/*
Plugin Name: Demo
Author: Izhar
Description: Interview Test
*/

function vehicle_setup_post_type() {
    $args = array(
        'public'    => true,
        'label'     => __( 'Vehicles', 'textdomain' ),
        'menu_icon' => 'dashicons-clipboard',
    );
    register_post_type( 'vehicle', $args );
}
add_action( 'init', 'vehicle_setup_post_type' );

function booking_listing_setup_post_type() {
    $args = array(
        'public'    => true,
        'label'     => __( 'Bookings', 'textdomain' ),
        'menu_icon' => 'dashicons-media-document',
    );
    register_post_type( 'bookings', $args );
}
add_action( 'init', 'booking_listing_setup_post_type' );

function create_vehicle_tax() {
    register_taxonomy( 'vehicle-type', 'vehicle', array(
        'label'        => __( 'Vehicle Type', 'textdomain' ),
        'rewrite'      => array( 'slug' => 'vehicle-type' ),
        'hierarchical' => true,
    ) );
}
add_action( 'init', 'create_vehicle_tax', 0 );

function price_add_custom_meta_box_2() {
   add_meta_box(
       'custom_meta_box',       // $id
       'Price Box',             // $title
       'show_custom_meta_box',  // $callback
       'vehicle',               // $page
       'normal',                // $context
       'high'                   // $priority
   );
}
add_action('add_meta_boxes', 'price_add_custom_meta_box_2');

//showing custom form fields
function show_custom_meta_box($post) {
    global $post;
    wp_nonce_field( basename( __FILE__ ), 'item_price_nonce' );
    
	$value = get_post_meta($post->ID, 'item_price', true);
	?>

    <input type="number" name="item_price" value="<?php echo $value ?>">
    <?php
}

//now we are saving the data
function price_save_meta_fields( $post_id ) {

	if (!isset($_POST['item_price_nonce']) || !wp_verify_nonce($_POST['item_price_nonce'], basename(__FILE__)))
	  return 'nonce not verified';

	if ( wp_is_post_autosave( $post_id ) )
	  return 'autosave';

	if ( wp_is_post_revision( $post_id ) )
	  return 'revision';

	if ( 'vehicle' == $_POST['post_type'] ) {
	  if ( ! current_user_can( 'edit_page', $post_id ) )
		  return 'cannot edit page';
	  } elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		  return 'cannot edit post';
	}

	$item_price = $_POST['item_price'];
	update_post_meta($post_id, 'item_price', $item_price);

}
add_action( 'save_post', 'price_save_meta_fields' );
add_action( 'new_to_publish', 'price_save_meta_fields' );

function booking_form(){
	
	if(isset($_POST['submit'])){
		$first_name = $_POST['first_name'];
		$last_name = $_POST['last_name'];
		$email_id = $_POST['email_id'];
		$phone = $_POST['phone'];
		$vehicletype = $_POST['vehicle_type'];
		$term = get_term_by('term_id', $vehicletype, 'vehicle-type');
		$vehicle_type = $term->name;
		$vehiclename = $_POST['vehicle_name'];
		$vehiclenamep = get_post($vehiclename);
		$vehicle_name = $vehiclenamep->post_title;
		$vehicle_price = $_POST['vehicle_price'];
		$vehicle_message = $_POST['vehicle_message'];
		
		$title = $first_name.' '.$last_name;
		
		$vehicle_post = array(
			'post_title'    => $title,
			'post_content'  => $vehicle_message,
			'post_type'     => 'bookings',
			'post_status'   => 'publish',
		);
		$vehicle_post_id = wp_insert_post( $vehicle_post );
		if($vehicle_post_id) {
			update_post_meta($vehicle_post_id, 'first_name', $first_name);
			update_post_meta($vehicle_post_id, 'last_name', $last_name);
			update_post_meta($vehicle_post_id, 'email_id', $email_id);
			update_post_meta($vehicle_post_id, 'phone', $phone);
			update_post_meta($vehicle_post_id, 'vehicle_type', $vehicle_type);
			update_post_meta($vehicle_post_id, 'vehicle', $vehicle_name);
			update_post_meta($vehicle_post_id, 'vehicle_price', $vehicle_price);
			update_post_meta($vehicle_post_id, 'vehicle_message', $vehicle_message);
			update_post_meta($vehicle_post_id, 'vehicle_status', $vehicle_status);
			
			
			$admin_email = get_option('admin_email');
			if($admin_email) {
				$subject = 'Booking submitted successfully';
				$admin_message = 'Hi Admin '.$vehicle_name. ' booking is submiited by '.$first_name.' '.$last_name;
				wp_mail($admin_email, $subject, $admin_message);
			} 
			if($email_id) {
				$subject = 'Booking submitted successfully';
				$umessage = 'Hi '.$first_name.' '.$last_name.' Your booking is pending';
				wp_mail($email_id, $subject, $umessage);
			}
			
			$message = array('msg'=>'Bokking Submitted Successfully', 'status'=>'success');
		} else {
			$message = array('msg'=>'Bokking Not Submitted', 'status'=>'error');
		}
	}
	
?>	
<style>
.booking_for_users input[type="submit"]{ border:1px solid blue; background:blue; color:#fff; padding:10px 25px; font-size:16px; text-transform:uppercase}
.booking_for_users .form_group{ margin-bottom:15px}
.booking_for_users input[type="text"], .booking_for_users input[type="number"], .booking_for_users input[type="email"], .booking_for_users select{ width:100%; height:40px; padding-left:15px; border:1px solid #ccc}
.booking_for_users textarea{ border:1px solid #ccc; height:100px; width:100%; padding-left:15px}
.booking_for_users .success{ border:1px solid green; background:green; color:#fff; padding:10px; margin-bottom:30px}
.booking_for_users .error{ border:1px solid red; background:red; color:#fff; padding:10px; margin-bottom:30px}
</style>
	<div class="booking_for_users form" style="margin-top:30px">
		<div class="<?php echo $message['status'] ?>"><?php echo $message['msg'] ?></div>
		<form class="booking_form" method="post" action="">
			<div class="form_group">
				<div class="form_lablel">Firt Name</div>
				<div class="form_input"><input type="text" name="first_name" required></div>
			</div>
			<div class="form_group">
				<div class="form_lablel">Last Name</div>
				<div class="form_input"><input type="text" name="last_name" required></div>
			</div>
			<div class="form_group">
				<div class="form_lablel">Email</div>
				<div class="form_input"><input type="email" name="email_id" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" required></div>
			</div>
			<div class="form_group">
				<div class="form_lablel">Phone</div>
				<div class="form_input"><input type="number" name="phone" required></div>
			</div>
			<div class="form_group">
				<div class="form_lablel">Vehicle Type</div>
				<div class="form_input">
					<select name="vehicle_type" id="vehicle_type">
						<option value="">Vehicle Type</option>
						<?php 
						$terms = get_terms('vehicle-type', array('hide_empty'=>false));
						foreach($terms as $term){ ?>
						<option value="<?php echo $term->term_id ?>"><?php echo $term->name ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="form_group">
				<div class="form_lablel">Vehicles</div>
				<div class="form_input">
					<select name="vehicle_name" id="vehicle_name">
						<option value="">Vehicles</option>
					</select>
				</div>
			</div>
			<div class="form_group">
				<div class="form_lablel">Vehicle Price</div>
				<div class="form_input"><input type="number" id="vehicle_price" name="vehicle_price" value="0.00"></div>
			</div>
			<div class="form_group">
				<div class="form_lablel">Message</div>
				<div class="form_input"><textarea name="vehicle_message"></textarea></div>
			</div>
			<div class="form_group">
				<div class="form_submit"><input type="submit" name="submit" value="Booking"></div>
			</div>
		</form>
	</div>

<script>

function vehicle(){
	jQuery(document).on("change", '#vehicle_name', function(event) { 
		var vehicle_id = jQuery(this).val();
		jQuery.ajax({
			url: '<?php echo admin_url("admin-ajax.php") ?>',
			type: 'post',
			data: { action: 'getPriceByVehicleID', vehicle_id: vehicle_id},
			success: function(resp){
				//alert(resp);
				jQuery('#vehicle_price').val(resp)
			}
		}) 
	})
} 
jQuery(document).ready(function($){
	vehicle();
});
jQuery(document).ready(function($){
    jQuery('#vehicle_type').change(function(){
		var type_id = jQuery(this).val();
		jQuery.ajax({
			url: '<?php echo admin_url("admin-ajax.php") ?>',
			type: 'post',
			data: { action: 'getVehicleByTypeID', type_id: type_id},
			success: function(resp){
				jQuery('#vehicle_name').html(resp)
			}
		})
	})
});


</script>	
	
<?php
}
add_shortcode('booking_form', 'booking_form');

function getPriceByVehicleID()
{
	$vehicle_id = $_POST['vehicle_id'];
	echo get_post_meta($vehicle_id, 'item_price', true);
	die;
}
add_action('wp_ajax_getPriceByVehicleID', 'getPriceByVehicleID');
add_action('wp_ajax_nopriv_getPriceByVehicleID', 'getPriceByVehicleID');

function getVehicleByTypeID()
{
	$type_id = $_POST['type_id'];
	
	$args = array(
		'post_type' => 'vehicle',
		'posts_per_page' => -1,
		'tax_query' => array(
			array(
            'taxonomy' => 'vehicle-type',
            'field'    => 'term_id',
            'terms'    => $type_id
			)
		)
	);
	
	query_posts($args); ?>
	<option value="">Vehicles</option>
	<?php 
	while(have_posts()) : the_post()
	?>
		<option value="<?php echo get_the_ID(); ?>"><?php echo the_title() ?></option>
	<?php 
	endwhile;
	
	wp_die();
}
add_action('wp_ajax_getVehicleByTypeID', 'getVehicleByTypeID');
add_action('wp_ajax_nopriv_getVehicleByTypeID', 'getVehicleByTypeID');

function booking_admin_menu(){
    add_menu_page( 
        __( 'Booking Settings', 'demo' ),
        'Booking Settings',
        'manage_options',
        'booking-settings',
        'booking_settings_page',
		'dashicons-welcome-widgets-menus',
        28
    ); 
}
add_action( 'admin_menu', 'booking_admin_menu' );

function booking_settings_page(){
?>	

<h1>Booking Settings</h1>
<p style="margin-top:30px; font-size:18px">Booking Form Shortcode : <strong style="margin-top:30px; font-size:22px"><?php echo "[booking_form]"; ?></strong></p>
<?php 
}


function bookings_fields_add_custom_meta_box_2() {
   add_meta_box(
       'custom_meta_box_bookings',       	// $id
       'Customer Details',             			// $title
       'show_custom_meta_box_bookings',  	// $callback
       'bookings',               			// $page
       'normal',                			// $context
       'high'                   			// $priority
   );
}
add_action('add_meta_boxes', 'bookings_fields_add_custom_meta_box_2');

//showing custom form fields
function show_custom_meta_box_bookings($post) {
    global $post;
    wp_nonce_field( basename( __FILE__ ), 'bookings_fields_nonce' );
    
	$first_name = get_post_meta($post->ID, 'first_name', true);
	$last_name = get_post_meta($post->ID, 'last_name', true);
	$email_id = get_post_meta($post->ID, 'email_id', true);
	$phone = get_post_meta($post->ID, 'phone', true);
	$vehicle_type = get_post_meta($post->ID, 'vehicle_type', true);
	$vehicle = get_post_meta($post->ID, 'vehicle', true);
	$vehicle_price = get_post_meta($post->ID, 'vehicle_price', true);
	$vehicle_message = get_post_meta($post->ID, 'vehicle_message', true);
	$vehicle_status = get_post_meta($post->ID, 'vehicle_status', true);
	
	?>
<style>
.group label {
    display: block;
    margin-bottom: 5px;
    font-size: 16px;
}
.group {
    margin: 15px 0;
}
.group input, .group select {
    width: 100%;
}
.group textarea {
    width: 100%;
    height: 100px;
}
</style>

	<div class="group">
		<label>First Name</label>
		<input type="text" name="first_name" value="<?php echo $first_name ?>">
	</div>
	<div class="group">
		<label>Last Name</label>
		<input type="text" name="last_name" value="<?php echo $last_name ?>">
	</div>
	<div class="group">
		<label>Email</label>
		<input type="text" name="email_id" value="<?php echo $email_id ?>">
	</div>
	<div class="group">
		<label>Phone</label>
		<input type="number" name="phone" value="<?php echo $phone ?>">
	</div>
	<div class="group">
		<label>Vehicle Type</label>
		<input type="text" name="vehicle_type" value="<?php echo $vehicle_type ?>">
	</div>
	<div class="group">
		<label>Vehicle</label>
		<input type="text" name="vehicle" value="<?php echo $vehicle ?>">
	</div>
	<div class="group">
		<label>Vehicle Price</label>
		<input type="number" name="vehicle_price" value="<?php echo $vehicle_price ?>">
	</div>
	<div class="group">
		<label>Message</label>
		<textarea name="vehicle_message"><?php echo $vehicle_message ?></textarea>
	</div>
	<div class="group">
		<label>Status</label>
		<select name="vehicle_status">
			<option value="" <?php if($vehicle_status == ''){ echo 'selected="selected"'; } ?>>Vehicle Status</option>
			<option value="0" <?php if($vehicle_status == 0){ echo 'selected="selected"'; } ?>>Pending</option>
			<option value="1" <?php if($vehicle_status == 1){ echo 'selected="selected"'; } ?>>Approved</option>
			<option value="2" <?php if($vehicle_status == 2){ echo 'selected="selected"'; } ?>>Reject</option>
			<option value="3" <?php if($vehicle_status == 3){ echo 'selected="selected"'; } ?>>On The Way</option>
			<option value="4" <?php if($vehicle_status == 4){ echo 'selected="selected"'; } ?>>Complete</option>
		</select>
	</div>
    <?php
}

//now we are saving the data
function bookings_fields_save_meta_fields( $post_id ) {

	if (!isset($_POST['bookings_fields_nonce']) || !wp_verify_nonce($_POST['bookings_fields_nonce'], basename(__FILE__)))
	  return 'nonce not verified';

	if ( wp_is_post_autosave( $post_id ) )
	  return 'autosave';

	if ( wp_is_post_revision( $post_id ) )
	  return 'revision';

	if ( 'vehicle' == $_POST['post_type'] ) {
	  if ( ! current_user_can( 'edit_page', $post_id ) )
		  return 'cannot edit page';
	  } elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		  return 'cannot edit post';
	}

	$first_name = $_POST['first_name'];
	$last_name = $_POST['last_name'];
	$email_id = $_POST['email_id'];
	$phone = $_POST['phone'];
	$vehicle_type = $_POST['vehicle_type'];
	$vehicle = $_POST['vehicle'];
	$vehicle_price = $_POST['vehicle_price'];
	$vehicle_message = $_POST['vehicle_message'];
	$vehicle_status = $_POST['vehicle_status'];
	
	update_post_meta($post_id, 'first_name', $first_name);
	update_post_meta($post_id, 'last_name', $last_name);
	update_post_meta($post_id, 'email_id', $email_id);
	update_post_meta($post_id, 'phone', $phone);
	update_post_meta($post_id, 'vehicle_type', $vehicle_type);
	update_post_meta($post_id, 'vehicle', $vehicle);
	update_post_meta($post_id, 'vehicle_price', $vehicle_price);
	update_post_meta($post_id, 'vehicle_message', $vehicle_message);
	update_post_meta($post_id, 'vehicle_status', $vehicle_status);
	
	if($vehicle_status == 0){
		$message = 'Yor Booking is Pending';
	} else if($vehicle_status == 1){
		$message = 'Yor Booking is Approved';
	} else if($vehicle_status == 2){
		$message = 'Yor Booking is Rejected';
	} else if($vehicle_status == 2){
		$message = 'Yor Booking is on the way';
	} else {
		$message = 'Yor Booking is Complete';
	}
	wp_mail($email_id, 'Status Update', $message);
}
add_action( 'save_post', 'bookings_fields_save_meta_fields' );
add_action( 'new_to_publish', 'bookings_fields_save_meta_fields' );