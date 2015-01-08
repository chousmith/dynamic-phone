<?php

add_action( 'admin_menu', 'dynamic_phone_admin_menu' );

//create admin menu items
function dynamic_phone_admin_menu() {
	add_menu_page('Dynamic Phone', 'Dynamic Phone', 'manage_options', 'dynamic-phone', 'dynamic_phone_shortcode');
	add_submenu_page( 'dynamic-phone', 'Shortcode', 'Shortcode', 'manage_options', 'dynamic-phone', 'dynamic_phone_shortcode' );
	add_submenu_page( 'dynamic-phone', 'Triggers', 'Triggers', 'manage_options', 'dynamic-phone-triggers', 'dynamic_phone_triggers' );
	add_submenu_page( 'dynamic-phone', 'Numbers', 'Numbers', 'manage_options', 'dynamic-phone-numbers', 'dynamic_phone_numbers' );
}


function dynamic_phone_admin_scripts() {

    wp_register_script( 'jquery_watermark', DYNAMIC_PHONE_PLUGIN_URL . 'includes/jquery.watermark.min.js', array('jquery'). '1.3.3' );
    wp_register_script( 'dynamic_phone_admin_js', DYNAMIC_PHONE_PLUGIN_URL . 'includes/admin.js', array('jquery', 'jquery_watermark'). '1.0' );
    wp_register_style( 'dynamic_phone_admin_css', DYNAMIC_PHONE_PLUGIN_URL . 'includes/admin.css', array(). '1.0' );

    wp_enqueue_script( 'jquery_watermark' );
    wp_enqueue_script( 'dynamic_phone_admin_js' );
    wp_enqueue_style( 'dynamic_phone_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'dynamic_phone_admin_scripts' );



//----------------------------------------------------
//
//	Functions to build out admin pages
//

// set new options on save
if($_POST['dynamic_phone']['settings_submit'] == '1') {

	$a = get_option( 'dynamic_phone', array() );

	if ( $_POST['dynamic_phone']['shortcode'] ) {
		foreach ( $_POST['dynamic_phone']['shortcode'] as $k => $v ) {
			$a['shortcode'][ $k ] = $v;
		}
	}

	if ( $_POST['dynamic_phone']['triggers'] ) {
		foreach ( $_POST['dynamic_phone']['triggers'] as $k => $v ) {
			$a['triggers'][ $k ] = $v;
		}
	}

	if ( $_POST['dynamic_phone']['numbers'] ) {
		foreach ( $_POST['dynamic_phone']['numbers'] as $k => $v ) {
			$a['numbers'][ $k ] = $v;
		}
	}

	update_option( 'dynamic_phone', $a );

	$result = get_option( 'dynamic_phone', array() );

	echo '<div id="message" class="updated fade"><p><strong>' . __('Settings saved.') . '</strong></p><pre style="display:none;">';
	print_r($result);
	echo '</pre></div>';

}
$dynamic_phone = get_option('dynamic_phone');


// wrap pages in generic form
function dynamic_phone_admin_form_wrapper( $begin = true ) {
	if ( $begin ) { ?>
		<form name="dynamic_phone_admin" class="dynamic_phone_admin" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="dynamic_phone[settings_submit]" value="1" />
	<?php }
	else { ?>
		<p class="submit"><input type="submit" name="Submit" value="Submit" /></p>
		</form>
	<?php }
}

// SHORTCODE page
function dynamic_phone_shortcode() { ?>
	<?php global $dynamic_phone; ?>
	<div class="wrap">
		<?php echo "<h2>" . __( 'Dynamic Phone: Shortcodes' ) . "</h2>"; ?>
		<?php dynamic_phone_admin_form_wrapper(); ?>
			<section>
				<p>Place the following shortcode wherever you would like dynamic phone number replacement to occur.<br />
					Then use the dynamic phone numbers and triggers to control options.</p>

				<h3>Default Shortcode</h3>

				<dl>
					<dt><pre><code>[phone_num]</code></pre></dt>
					<dd>Inserts formatted dynamic phone number with HTML5 "tel" link wrapper.</dd>
				</dl>

				<hr />

				<h3>Options</h3>

				<dl>
					<dt><pre><code>[phone_num number="8885551212" link=true format="standard"]</code></pre></dt>
					<dd>Additional display options available:
						<ul>
							<li><b>number</b> (7 to 11 digits) : override dynamic phone number generation</li>
							<li><b>link</b> ( true or false ) : false will disable <em>&lt;a href="tel:"&gt;</em> wrapped element</li>
							<li><b>format</b> : Specify output format of phone number</li>
							<li>
								<ul style="text-indent: 20px;">
									<li>standard = (888) 555-1212</li>
									<li>decimal = 888.555.1212</li>
									<li>dash = 888-555-1212</li>
									<li>space = 888 555 1212</li>
									<li>numeric = 8885551212</li>
								</ul>
							</li>
						</ul>
					</dd>
				</dl>

				<hr />

				<h3>Allow Shortcode in Widgets</h3>

				<p><input type="checkbox" name="dynamic_phone[shortcode][allow_in_widget]" value="yes" <?php if ( $dynamic_phone['shortcode']['allow_in_widget'] == 'yes' ) { echo 'checked="checked"'; } ?> />
					&nbsp;&nbsp;By default WordPress does not allow shortcodes in widget area. Check here to turn on this feature.</p>
				
			</section>
		<?php dynamic_phone_admin_form_wrapper( false ); ?>
	</div>
	<?php
}

// TRIGGERS page
function dynamic_phone_triggers() { ?>
	<?php global $dynamic_phone; ?>
	<div class="wrap">
	    <?php echo "<h2>" . __( 'Dynamic Phone: Triggers' ) . "</h2>"; ?>
	    <?php dynamic_phone_admin_form_wrapper(); ?>
			<section>
				<h3>Usage</h3>

				<p>The use of dynamic phone numbers may be controlled by URL query strings or form field POST data.</p>
				<p><ul>
					<li>
						<dl>
							<dt><strong>URL query string example:</strong></dt>
							<dd>
								<ul>
									<li><code><?php echo get_bloginfo('url'); ?>/index.php?<?php echo $dynamic_phone['triggers']['query_string_name'] ? $dynamic_phone['triggers']['query_string_name'] : 'phid'; ?>=3&amp;etc=true</code></li>
									<li>where <code><?php echo $dynamic_phone['triggers']['query_string_name'] ? $dynamic_phone['triggers']['query_string_name'] : 'phid'; ?></code> is the query name, and <code>3</code> is the query value.</li>
								</ul>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt><strong>Form field post example:</strong></dt>
							<dd>
								<ul>
									<li><code>&lt;input type="hidden" name="<?php echo $dynamic_phone['triggers']['form_field_name'] ? $dynamic_phone['triggers']['form_field_name'] : 'phoneid'; ?>" value="xyz" /&gt;</code></li>
									<li>where <code><?php echo $dynamic_phone['triggers']['form_field_name'] ? $dynamic_phone['triggers']['form_field_name'] : 'phoneid'; ?></code> is the POST name, and <code>xyz</code> is the form field value.</li>
								</ul>
							</dd>
						</dl>
					</li>
				</ul></p>
				
				<hr />

				<h3>Dynamic Triggers</h3>

				<p>This plugin uses cookies, query strings, and form post data to set and retrieve dynamic phone numbers. Set the names of the various trigger options here.</p>
				<p>Precedence is set in the following order:<br />
					<ol>
						<li>Cookie</li>
						<li>URL Query String</li>
						<li>Form Post Field</li>
					</ol>
				</p>
				<p>&nbsp;</p>

				<label for="dynamic_phone[triggers][query_string_name]">URL Query Name<br /><small>Add this query string and associated value(s) to your Adwords ads (for example)</small></label>
				<input type="text" name="dynamic_phone[triggers][query_string_name]" value="<?php echo $dynamic_phone['triggers']['query_string_name']; ?>"/><br />
				<br />
				<label for="dynamic_phone[triggers][form_field_name]">Form POST Name<br /><small>Use this form field name in contact / conversion forms</small></label>
				<input type="text" name="dynamic_phone[triggers][form_field_name]" value="<?php echo $dynamic_phone['triggers']['form_field_name']; ?>"/><br />
				<br />
				<label for="dynamic_phone[triggers][cookie_name]">Cookie Name<br /><small>This cookie will contain dynamic phone number reference value. In addition, this is the name of the cookie that will be set to store dynamic number value</small></label>
				<input type="text" name="dynamic_phone[triggers][cookie_name]" value="<?php echo $dynamic_phone['triggers']['cookie_name']; ?>"/><br />
				<br />
				<label for="dynamic_phone[triggers][cookie_lifetime]">Cookie Lifetime<br /><small>The time (in days) that this cookie will store the phone number reference value. Default is 7 days.</small></label>
				<input type="text" name="dynamic_phone[triggers][cookie_lifetime]" value="<?php echo $dynamic_phone['triggers']['cookie_lifetime']; ?>"/><br />
				<br />

			</section>
	    <?php dynamic_phone_admin_form_wrapper( false ); ?>
	</div>
	<?php
}

// NUMBERS page
function dynamic_phone_numbers() { ?>
	<?php global $dynamic_phone; ?>
	<div class="wrap">
	    <?php echo "<h2>" . __( 'Dynamic Phone: Phone Numbers' ) . "</h2>"; ?>
	    <?php dynamic_phone_admin_form_wrapper(); ?>
			<section>

				<h3>Default Phone Number</h3>

				<label for="dynamic_phone[numbers][default_phone]">Default Phone Number</label>
				<input type="text" name="dynamic_phone[numbers][default_phone]" value="<?php echo $dynamic_phone['numbers']['default_phone']; ?>" p-label="<?php echo $dynamic_phone['numbers']['default_phone'] ? $dynamic_phone['numbers']['default_phone'] : '888-555-0000'; ?>"/><br />

				<hr />

				<h3>Dynamic Numbers and Trigger Values</h3>

				<?php $i = 1; ?>

				<table id="dynamic_phone_table" style="text-align: center;">
					<tbody>
						<tr>
							<th><small>URL Value</small></th>
							<th><small>Form Value</small></th>
							<th><small>Cookie Value</small></th>
							<th><small>Display Phone Number</small></th>
							<th></th>
						</tr>
						<tr>
							<td><input type="text" size="8" name="dynamic_phone[numbers][query_string_value][0]" value="<?php echo $dynamic_phone['numbers']['query_string_value'][0]; ?>"/></td>
							<td><input type="text" size="8" name="dynamic_phone[numbers][form_field_value][0]" value="<?php echo $dynamic_phone['numbers']['form_field_value'][0]; ?>"/></td>
							<td><input type="text" size="8" name="dynamic_phone[numbers][cookie_value][0]" value="<?php echo $dynamic_phone['numbers']['cookie_value'][0]; ?>"/></td>
							<td><input type="tel" size="20" name="dynamic_phone[numbers][dynamic_phone_number][0]" value="<?php echo $dynamic_phone['numbers']['dynamic_phone_number'][0]; ?>"/></td>
							<td><a class="phone-row-more" rel="0" style="cursor: pointer;">+ add row</a></td>
						</tr>

						<?php while ( $i < count( $dynamic_phone['numbers']['dynamic_phone_number'] ) ) { ?>
						<tr>
							<td><input type="text" size="8" name="dynamic_phone[numbers][query_string_value][<?php echo $i; ?>]" value="<?php echo $dynamic_phone['numbers']['query_string_value'][ $i ]; ?>"/></td>
							<td><input type="text" size="8" name="dynamic_phone[numbers][form_field_value][<?php echo $i; ?>]" value="<?php echo $dynamic_phone['numbers']['form_field_value'][ $i ]; ?>"/></td>
							<td><input type="text" size="8" name="dynamic_phone[numbers][cookie_value][<?php echo $i; ?>]" value="<?php echo $dynamic_phone['numbers']['cookie_value'][ $i ]; ?>"/></td>
							<td><input type="tel" size="20" name="dynamic_phone[numbers][dynamic_phone_number][<?php echo $i; ?>]" value="<?php echo $dynamic_phone['numbers']['dynamic_phone_number'][ $i ]; ?>"/></td>
							<td><a class="phone-row-more" rel="<?php echo $i; ?>" style="cursor: pointer;">+ add row</a></td>
						</tr>
						<?php $i++; } ?>

					</tbody>
				</table>
			</section>
	    <?php dynamic_phone_admin_form_wrapper( false ); ?>
	</div>
	<?php
}








?>