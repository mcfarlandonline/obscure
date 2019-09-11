<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Obscure
{

	var $return_data = "";

	// --------------------------------------------------------------------

	/**
	* Memberlist
	*
	* This function obscures the string between the plugin template tags.
	*
	* @access	public
	* @return	string
	*/

	function Obscure()
	{
		$this->EE =& get_instance(); 
		$this->EE->load->helper('form_helper');
		$this->EE->load->helper('typography_helper');
		
		$data = $this->EE->TMPL->tagdata;

		// Look for EE tags that may need to be parsed before obscuring
		foreach($this->EE->TMPL->var_single as $key => $val)
		{
			if (strncmp($key, "name", 4) == 0)
			{
				$name = ($this->EE->session->userdata['screen_name'] != '') ? $this->EE->session->userdata['screen_name'] : $this->EE->session->userdata['username'];
                $name = ( ! isset($_POST['name'])) ? $name : $_POST['name'];

				$data = $this->EE->TMPL->swap_var_single('name', form_prep($name), $data);
			}
			elseif (strncmp($key, "email", 5) == 0)
			{
				$email = ( ! isset($_POST['email'])) ? $this->EE->session->userdata['email'] : $_POST['email'];

				$data = $this->EE->TMPL->swap_var_single('email', form_prep($email), $data);
			}
			elseif (strncmp($key, "url", 3) == 0)
			{
				$url = ( ! isset($_POST['url'])) ? $this->EE->session->userdata['url'] : $_POST['url'];

				if($url == '')
				{
					$url = 'http://';
				}

				$data = $this->EE->TMPL->swap_var_single('url', form_prep($url), $data);
			}
			elseif (strncmp($key, "comment", 7) == 0)
			{
				$comment = ( ! isset($_POST['comment'])) ? '' : $_POST['comment'];

				$data = $this->EE->TMPL->swap_var_single('comment', form_prep($comment), $data);
			}
			elseif (strncmp($key, "save_info", 9) == 0)
			{
				$save_info = ( ! isset($_POST['save_info'])) ? '' : $_POST['save_info'];

                $notify = ( ! isset($this->EE->session->userdata['notify_by_default'])) ? $this->EE->input->cookie('save_info') : $this->EE->session->userdata['notify_by_default'];

                $checked   = ( ! isset($_POST['PRV'])) ? $notify : $save_info;

				$data = $this->EE->TMPL->swap_var_single('save_info', ($checked == 'yes') ? "checked=\"checked\"" : '', $data);
			}
			elseif (strncmp($key, "notify_me", 9) == 0)
			{
				$checked = '';

            	if ( ! isset($_POST['PRV']))
            	{
					if ($this->EE->input->cookie('notify_me'))
					{
						$checked = $this->EE->input->cookie('notify_me');
					}

					if (isset($this->EE->session->userdata['notify_by_default']))
					{
						$checked = ($this->EE->session->userdata['notify_by_default'] == 'y') ? 'yes' : '';
					}					
				}

				if (isset($_POST['notify_me']))
				{
					$checked = $_POST['notify_me'];
				}

				$data = $this->EE->TMPL->swap_var_single('notify_me', ($checked == 'yes') ? "checked=\"checked\"" : '', $data);
			}
			else
			{
				$data = $this->EE->TMPL->parse_globals($data);
			}
		}

		// Get rid of the entitiy encoding that EE adds in
		$new_data = entity_decode($data);
		
		// Build array of data
		$new_data_array = array();

		for ($i = 0; $i < strlen($data); $i++)
        {
			$character = substr($new_data, $i, 1);

			if($character == '/' || $character == '<' || $character == '>' || $character == '"' || $character == '=')
			{
				$character = $character;
			}
			elseif($character == '\'')
			{
				$character = '\\\'';
			}
			elseif($character == ";")
			{
				$character = '';
			}
			else
			{
				$character = " ".ord($character);
			}

  			$new_data_array[] .= $character;
        }

		$new_data_array = array_reverse($new_data_array);

		// Build javascript array and code that will decode obscured data back into the page
		ob_start();

?><script type="text/javascript">
//<![CDATA[
var d = new Array();
<?php

    $i = 0;
    foreach ($new_data_array as $value)
    {
?>d[<?php print $i++; ?>]='<?php print $value; ?>';<?php
    }
?>

for (var i = d.length-1; i >= 0; i = i - 1)
{ 
	if (d[i].substring(0, 1) == ' ')
	{
		var code = unescape(d[i].substring(1));
		document.write(String.fromCharCode(code));
	}
	else
	{
		document.write(unescape(d[i]));
	}
}
//]]>
</script><?php

		$buffer = ob_get_contents();
        ob_end_clean(); 
		
		$this->return_data = $buffer;
	}

	// --------------------------------------------------------------------

	/**
	* Usage
	*
	* This function describes how the plugin is used.
	*
	* @access	public
	* @return	string
	*/
	
	function usage()
	{
		
ob_start(); 
?>
Put stuff between the tags like this:

{exp:obscure}
Something you want obscured.
{/exp:obscure}

It should be noted that some conditionals and global variables seem to be rendered AFTER plugins get parsed.  That means that some variables and conditionals will not function properly inside of the tags as the plugin will have obscured the tags before EE has had a chance to parse them.  As this plugin was developed as a way to obscure forms initially it will properly parse the entry comment form variables before obscuring them. With other form related variables your milage may vary.

I'm happy to add support for variables you might need.  So let me know if you have a problem with one specifically.
<?php
	
		$buffer = ob_get_contents();

		ob_end_clean(); 

		return $buffer;
	}
	// End

}
/* End of file pi.obscure.php */ 
/* Location: ./system/expressionengine/third_party/obscure/pi.obscure.php */