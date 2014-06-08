<?php

function ois_render_feedburner($skin_id, $options, $template)
{
	$fields = array(
		'action' => 'http://feedburner.google.com/fb/a/mailverify',
		'service' => 'feedburner',
		'name_name' => 'fname',
		'email_name' => 'email',
		'skin_id' => $skin_id
	);

	if (!empty($options['feedburner_id']))
		{ // for the Feedburner ID.
		$feedburner_id = trim($options['feedburner_id']);
	} // if
	else
	{
		$feedburner_id = '';
	} // else

	$fields['open_form_extras'] = "target=\"popupwindow\" onsubmit=\"window.open('http://feedburner.google.com/fb/a/mailverify?uri=$feedburner_id', 'popupwindow', 'scrollbars=yes,width=550,height=520');return true;\"";

	/* HIDDEN FIELDS */
	if (!empty($options['hidden_fields']))
	{
		$hidden = $options['hidden_fields'];
	} // if
	else
	{
		$hidden = array();
	} // else
	$hidden['loc'] = 'en_US';
	$hidden['uri'] = $feedburner_id;

	// Make replacements.
	$template = ois_render_common_fields($fields, $template);
	$template = ois_render_hidden($hidden, $template);

	return $template;
} // ois_render_feedburner()

function ois_render_aweber($skin_id, $options, $template)
{
	if (!empty($options['aweber_id']))
		{ // for the Aweber ID.
		$aweber_id = trim($options['aweber_id']);
	} // if
	else
	{
		$aweber_id = '';
	} // else

	$fields = array(
		'action' => 'http://www.aweber.com/scripts/addlead.pl',
		'service' => 'aweber',
		'name_name' => 'name',
		'email_name' => 'email',
		'skin_id' => $skin_id,
		'open_form_extras' => '', // none, but must still replace {{open_form_extras}}
	);

	/* HIDDEN FIELDS */
	if (!empty($options['hidden_fields']))
	{
		$hidden = $options['hidden_fields'];
	} // if
	else
	{
		$hidden = array();
	} // else
	$hidden['listname'] = $aweber_id;
	$hidden['meta_message'] = "1";
	if (isset($options['redirect_url']))
	{
		$hidden['redirect'] = $options['redirect_url'];
	} // if

	// Make replacements.
	$template = ois_render_common_fields($fields, $template);
	$template = ois_render_hidden($hidden, $template);

	return $template;

} // ois_render_aweber ()

function ois_render_mailchimp($skin_id, $options, $template)
{
	$fields = array(
		'action' => $options['mailchimp_action'],
		'service' => 'mailchimp',
		'name_name' => 'FNAME',
		'email_name' => 'EMAIL',
		'skin_id' => $skin_id,
		'open_form_extras' => '', // none, but must still replace {{open_form_extras}}
	);

	/* HIDDEN FIELDS */
	if (!empty($options['hidden_fields']))
	{
		$hidden = $options['hidden_fields'];
	} // if
	else
	{
		$hidden = array();
	} // else

	// Make replacements.
	$template = ois_render_common_fields($fields, $template);
	$template = ois_render_hidden($hidden, $template);

	return $template;

} // ois_render_mailchimp ()


function ois_render_getresponse($skin_id, $options, $template)
{
	$fields = array(
		'action' => 'https://app.getresponse.com/add_contact_webform.html',
		'service' => 'getResponse',
		'name_name' => 'name',
		'email_name' => 'email',
		'skin_id' => $skin_id,
		'open_form_extras' => '',
	);

	$getresponse_id = $options['getResponse_id'];

	/* HIDDEN FIELDS */
	if (!empty($options['hidden_fields']))
	{
		$hidden = $options['hidden_fields'];
	} // if
	else
	{
		$hidden = array();
	} // else
	$hidden['webform_id'] = $getresponse_id;

	// Make replacements.
	$template = ois_render_common_fields($fields, $template);
	$template = ois_render_hidden($hidden, $template);

	return $template;

} // ois_render_icontact ()

function ois_render_infusionsoft($skin_id, $options, $template)
{
	$infusionsoft_action = $options['infusionSoft_action'];

	$fields = array(
		'action' => $infusionsoft_action,
		'service' => 'infusionSoft',
		'name_name' => 'inf_field_FirstName',
		'email_name' => 'inf_field_Email',
		'skin_id' => $skin_id,
		'open_form_extras' => '',
	);

	$infusionsoft_id = $options['infusionSoft_id'];

	/* HIDDEN FIELDS */
	if (!empty($options['hidden_fields']))
	{
		$hidden = $options['hidden_fields'];
	} // if
	else
	{
		$hidden = array();
	} // else
	$hidden['inf_form_xid'] = $infusionsoft_id;
	//$hidden['inf_form_name'] = addslashes( htmlentities( $options['infusionSoft_name'] ) );
	$hidden['infusionsoft_version'] = '1.22.10.32';

	if (isset($options['redirect_url']))
	{
		$hidden['confirmation_url'] = $options['redirect_url'];
	} // if

	// Make replacements.
	$template = ois_render_common_fields($fields, $template);
	$template = ois_render_hidden($hidden, $template);
	return $template;

} // ois_render_infusionsoft ()

function ois_render_custom ($skin_id, $options, $template)
{

	$fields = array(
		'action' => $options['custom_action'],
		'service' => 'custom',
		'name_name' => $options['custom_name'],
		'email_name' => $options['custom_email'],
		'skin_id' => $skin_id,
		'open_form_extras' => '',
	);

	/* HIDDEN FIELDS */
	if (!empty($options['hidden_fields']))
	{
		$hidden = $options['hidden_fields'];
	} // if
	else
	{
		$hidden = array();
	} // else

	// Make replacements.
	$template = ois_render_common_fields($fields, $template);
	$template = ois_render_hidden($hidden, $template);
	return $template;
} // ois_render_custom

function ois_render_other ($skin_id, $options, $template)
{

	/* HIDDEN FIELDS */
	if (!empty($options['hidden_fields']))
	{
		$hidden = $options['hidden_fields'];
	} // if
	else
	{
		$hidden = array();
	} // else

	$template = ois_render_hidden($hidden, $template);
	
	return $template;
}

function ois_render_common_fields($fields, $template)
{
	foreach ($fields as $field_name=>$field_value)
	{
		$template = str_replace('{{' . $field_name . '}}', $field_value, $template);
	} // foreach

	return $template;
} // ois_render_common_fields($fields)

/*
	Preconditions:
		. $hidden is an array in the form, ('name' => 'value'), ...
	Postconditions:
		. Will add hidden fields (<input type="hidden" ...) to the template,
		before the end of the form (</form>)
*/
function ois_render_hidden($hidden, $template)
{
	$hidden_string = ''; // contains a string of all hidden fields
	if (!empty($hidden))
	{
		foreach ($hidden as $name=>$value)
		{
			if (trim($name) != '')
			{
				$hidden_string .= "<input type='hidden' name='$name' value='$value'/>\n";
			}
		} // foreach
	} // if

	return str_replace('</form>', $hidden_string . '</form>', $template);
} // ois_render_hidden
?>