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

    /**
     * This is the proposed bootstrap function, we'll not need attachment_preview to get this to work
     * if https://github.com/osTicket/osTicket/pull/2907 get's merged.
     */
    function new_bootstrap()
    {
        global $ost;
        
        // As the script only works when there is something to click on, we can safely inject it as a header in
        // all renderable pages.
        $ost->addExtraHeader($this->getScript());
    }

    /**
     * Runs on every page load, keep small!
     *
     * {@inheritdoc}
     * @see Plugin::bootstrap()
     */
    function bootstrap()
    {
        if (! class_exists('AttachmentPreviewPlugin')) {
            error_log("Unable to use NoteFirstPlugin without this https://github.com/clonemeagain/attachment_preview ");
        } elseif (AttachmentPreviewPlugin::isTicketsView()) {
            $this->sendScript();
        }
    }

    /**
     * Uses the Attachments Preview plugin API to send the javascript as a DOMElement
     * TODO: When the new_bootstrap method works, can remove this method, and remove the $raw filter on getScript.
     */
    private function sendScript()
    {
        // We are, let's build a javascript DOMElement to send:
        $dom = new DOMDocument();
        $script = $dom->createElement('script');
        $script->setAttribute('type', 'text/javascript');
        $script->setAttribute('name', 'Plugin: NoteFirst');
        $script->setAttribute('plugin', 'https://github.com/clonemeagain/osticket-plugin-notefirst');
        $script->nodeValue = $this->getScript(true);
        
        // Build the structure that the Attachment Preview plugin expects:
        $signal_structure = array(
            (object) array(
                'locator' => 'tag', // References an HTML tag, in this case <body>
                'expression' => 'body', // Append to the end of the body (persists through pjax loads of the container)
                'element' => $script // The DOMElement
            )
        );
        
        // Connect to the attachment_previews API wrapper and send the structure:
        Signal::send('attachments.wrapper', $this, $signal_structure);
    }

    /**
     * Contains the actual JavaScript that does the work of the plugin
     * Could be made external, however external dependencies should be avoided where possible.
     *
     * "reply hash" means: #reply etc in the URL.
     *
     * @return string
     */
    private function getScript($raw = FALSE)
    {
        $script = <<<SCRIPT

(function($){
    $(document).on("ready pjax:success",function(){
      // Set the default response to "Internal Note", unless there is a reply hash in the URL.
            if(!location.hash){
                // Test for osTicket 1.10:
                if($('#post-note-tab').length){
                    $('#post-note-tab').click();
                // 1.9 or less:
                }else if($('#note_tab').length){
                    $('#note_tab').click();
                    // The "action of clicking" causes warning messages to disappear.. this keeps them around.
                    $("#msg_error, #msg_notice, #msg_warning").fadeIn(); 
                }
            }
    });
})(jQuery);
SCRIPT;
        if ($raw)
            return $script;
        
        return '<script name="Plugin: NoteFirst" plugin="https://github.com/clonemeagain/osticket-plugin-notefirst">' . $script . '</script>';
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
