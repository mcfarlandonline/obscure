<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Obscure
{

    var $return_data = "";

    // --------------------------------------------------------------------

    /**
    * Memberlist
    *
    * This function obscures the string between the plugin template tags.
    *
    * @access   public
    * @return   string
    */

    public function __construct()
    {

        ee()->load->helper('form_helper');
        ee()->load->helper('typography_helper');

        $data = ee()->TMPL->tagdata;

        // Look for EE tags that may need to be parsed before obscuring
        foreach (ee()->TMPL->var_single as $key => $val) {
            if (strncmp($key, "name", 4) == 0) {
                $name = (ee()->session->userdata['screen_name'] != '') ? ee()->session->userdata['screen_name'] : ee()->session->userdata['username'];
                $name = ( ! isset($_POST['name'])) ? $name : $_POST['name'];

                $data = ee()->TMPL->swap_var_single('name', form_prep($name), $data);
            } elseif (strncmp($key, "email", 5) == 0) {
                $email = ( ! isset($_POST['email'])) ? ee()->session->userdata['email'] : $_POST['email'];

                $data = ee()->TMPL->swap_var_single('email', form_prep($email), $data);
            } elseif (strncmp($key, "url", 3) == 0) {
                $url = ( ! isset($_POST['url'])) ? ee()->session->userdata['url'] : $_POST['url'];

                if ($url == '') {
                    $url = 'http://';
                }

                $data = ee()->TMPL->swap_var_single('url', form_prep($url), $data);
            } elseif (strncmp($key, "comment", 7) == 0) {
                $comment = ( ! isset($_POST['comment'])) ? '' : $_POST['comment'];

                $data = ee()->TMPL->swap_var_single('comment', form_prep($comment), $data);
            } elseif (strncmp($key, "save_info", 9) == 0) {
                $save_info = ( ! isset($_POST['save_info'])) ? '' : $_POST['save_info'];

                $notify = ( ! isset(ee()->session->userdata['notify_by_default'])) ? ee()->input->cookie('save_info') : ee()->session->userdata['notify_by_default'];

                $checked   = ( ! isset($_POST['PRV'])) ? $notify : $save_info;

                $data = ee()->TMPL->swap_var_single('save_info', ($checked == 'yes') ? "checked=\"checked\"" : '', $data);
            } elseif (strncmp($key, "notify_me", 9) == 0) {
                $checked = '';

                if (! isset($_POST['PRV'])) {
                    if (ee()->input->cookie('notify_me')) {
                        $checked = ee()->input->cookie('notify_me');
                    }

                    if (isset(ee()->session->userdata['notify_by_default'])) {
                        $checked = (ee()->session->userdata['notify_by_default'] == 'y') ? 'yes' : '';
                    }
                }

                if (isset($_POST['notify_me'])) {
                    $checked = $_POST['notify_me'];
                }

                $data = ee()->TMPL->swap_var_single('notify_me', ($checked == 'yes') ? "checked=\"checked\"" : '', $data);
            } else {
                $data = ee()->TMPL->parse_globals($data);
            }
        }

        // Get rid of the entitiy encoding that EE adds in
        $new_data = entity_decode($data);

        // Build array of data
        $new_data_array = array();

        for ($i = 0; $i < strlen($data); $i++) {
            $character = substr($new_data, $i, 1);

            if ($character == '/' || $character == '<' || $character == '>' || $character == '"' || $character == '=') {
                $character = $character;
            } elseif ($character == '\'') {
                $character = '\\\'';
            } elseif ($character == ";") {
                $character = '';
            } else {
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
foreach ($new_data_array as $value) {
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
    * @access   public
    * @return   string
    */

    public static function usage()
    {



        return '';
    }
    // End
}
/* End of file pi.obscure.php */
/* Location: ./system/expressionengine/third_party/obscure/pi.obscure.php */
