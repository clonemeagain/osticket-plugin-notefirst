<?php
require_once (INCLUDE_DIR . 'class.plugin.php');
require_once ('config.php');

/**
 * Ensure that "Post Internal Note" reply tab is activated on page load when viewing tickets.
 * For whomever you specify in the admin options.
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

        // Probably more efficient to see if we can even use it first, then build stuff
        // Also see if it's been enabled for Staff/Agents
        if (AttachmentPreviewPlugin::isTicketsView() && in_array($this->getConfig()->get('note-first-enabled'), array(
            'staff',
            'all'
        ))) {

            // We could seriously try and recreate the HTML in DOM objects, inject them into the page..
            // HOWEVER: The tickets.js onload function will simply override anything we write.. I know. I tried.
            // SO, the simplest way, is to simply emulate a "click" on the note tab.. at least we don't need translations!

            // We want to make a script element
            $dom = new DOMDocument();
            $script = $dom->createElement('script');
            $script->setAttribute('type', 'text/javascript');

            // Write our script.. if it was more complicated, we would put it in an external file and pull it in.
            // If we had this hosted on our server in another place, we wouldn't need this, just set:
            // $script->setAttribute('src', 'http://server/path/file.js');
            // That would entail setting up a Dispatch listener effectively.. which is frustrating to deal with.
            $script->nodeValue = <<<SCRIPT
// http://github.com/clonemeagain/osticket-plugin-notefirst
(function($){
          $(document).on('ready pjax:success',function(){
                // Set the default response to "Internal Note", unless there is a reply hash in the URL.
                if(!location.hash){
                    $('#post-note-tab').click(); // Upgrade to 1.10
                    // Note: The "action of clicking" causes the warning message to disappear.. we want it back?
                    //$("#msg_error, #msg_notice, #msg_warning").fadeIn();
                }
          });
})(jQuery);
SCRIPT;

            // Let's build the required signal structure.
            // We want to inject our script on tickets pages..
            /**
             * Based on: attachment_preview exposed functionality
             *
             * $structure = array(
             *  (object)[
             * 'element' => $element, // The DOMElement to replace/inject etc.
             * 'locator' => 'tag', // EG: tag/id/xpath
             * 'replace_found' => FALSE, // default value, only really have to include if you want to replace it
             * 'expression' => 'body' // which tag/id/xpath etc. eg: 'body', 'head', when locator=> 'id' you can use any html id attribute. (without # like jQuery).
             * ],
             * ... Additional Objects if required, all structures for matching regex get loaded if regex matches path
             * )
             */

            // Let's build the required signal structure, containing both DOM manipulations.
            // We want this script at the bottom of the "<body>", the default method is appendChild, and specifying "tag" will find it by tag.
            // Luckily there are never more than one <body> element's in an HTML page.. that could get weird.
            // Regex is which pages to operate on: in this case, tickets pages.
            $signal_structure = array(
                (object) [
                    'locator' => 'tag',
                    'expression' => 'body',
                    'element' => $script
                ]
            )
            ;

            // Connect to the attachment_previews plugin and send the structures. :-)
            Signal::send('attachments.wrapper', $this, $signal_structure);
        }
    }

    /**
     * Required stub.
     *
     * {@inheritDoc}
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
