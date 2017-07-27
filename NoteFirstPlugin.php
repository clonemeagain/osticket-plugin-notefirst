<?php
require_once (INCLUDE_DIR . 'class.plugin.php');
require_once ('config.php');

/**
 * Ensure that "Post Internal Note" reply tab is activated on page load when viewing tickets.
 * For whomever you specify in the admin options.
 * 
 * The simplest way, is to simply emulate a "click" on the note tab.. at least we don't need translations!.
 */
class NoteFirstPlugin extends Plugin
{

    var $config_class = 'NoteFirstPluginConfig';

    function bootstrap()
    {
        if (! class_exists('AttachmentPreviewPlugin')) {
            global $ost;
            $ost->logError("Attachment Preview Plugin not enabled.", "To use plugin Note First, you need to enable the Attachment Preview Plugin");
            return;
        }
        
        // Use the AttachmentPreviewPlugin to check if we are viewing a ticket:
        if (AttachmentPreviewPlugin::isTicketsView()) {
            // We are, let's a script element in javascript to do the clicking on the tab for us:
            $dom = new DOMDocument();
            $script = $dom->createElement('script');
            $script->setAttribute('type', 'text/javascript');
            $script->setAttribute('name', 'Plugin: NoteFirst');
            
            // Write our script.. 
            $script->nodeValue = <<<SCRIPT
// Source: http://github.com/clonemeagain/osticket-plugin-notefirst
(function($){
          $(document).on('ready pjax:success',function(){
                // Set the default response to "Internal Note", unless there is a reply hash in the URL.
                if(!location.hash){
                    $('#post-note-tab').click(); // Upgrade to 1.10
                    // 1.9 or less:                    
                    //$('#post_tab').click(); $("#msg_error, #msg_notice, #msg_warning").fadeIn(); // The "action of clicking" causes the warning message to disappear.. this keeps it around.
                }
          });
})(jQuery);
SCRIPT;
            
            // Build the structure to send to the Attachments plugin:
            $signal_structure = array(
                (object) [
                    'locator' => 'tag',
                    'expression' => 'body',
                    'element' => $script
                ]
            );
            
            // Connect to the attachment_previews plugin and send the structure
            Signal::send('attachments.wrapper', $this, $signal_structure);
        }
    }

    /**
     * This is the proposed bootstrap function, we'll not need attachment_preview to get this to work
     * if https://github.com/osTicket/osTicket/pull/2907 get's merged.
     */
    function new_bootstrap()
    {
        /**
         * Even newer method, assuming we can get a signal at the end of Bootstrap:
         * Signal::connect ( 'bootstrap', function ($ost) {
         * $ost->addExtraHeader ( '<script...' );
         * } );
         */
        global $ost;
        $ost->addExtraHeader('
	  <script name="Plugin: NoteFirst">
	  // http://github.com/clonemeagain/osticket-plugin-notefirst
	  (function($){
	   $(document).on("ready pjax:success",function(){
    	  // Set the default response to "Internal Note", unless there is a reply hash in the URL.
    	  if(!location.hash){
             //$("#note_tab").click(); // uncomment this for 1.9 or less and comment the next line.
    	     $("#post-note-tab").click(); // Upgrade to 1.10 
    	  }
        });
	  })(jQuery);
	  </script>');
    }

    /**
     * Required stub.
     *
     * {@inheritdoc}
     *
     * @see Plugin::uninstall()
     */
    function uninstall()
    {
        $errors = array();
        parent::uninstall($errors);
    }

    /**
     * Required stub
     */
    public function getForm()
    {
        return array();
    }
}
