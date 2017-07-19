# osticket-plugin-notefirst

Ensures that when you view a ticket, the "Post Internal Note" tab is highlighted where possible.

To install, first, install https://github.com/clonemeagain/attachment_preview as this relies on it.
Then download/clone this into your /include/plugins directory as a directory, then visit /scp/plugins.php to enable it.

# Can be made much simpler, if you are willing to change core a touch!

* Edit include/class.osticket.php
* Go right to the bottom to the "start" function, add at the beggining: ```global $ost;```
* Effectively, apply https://github.com/osTicket/osTicket/pull/2907 
* Now we don't need the attachments_preview api! :-)

Then you can replace the entire bootstrap method of this plugin with:

```php
function bootstrap(){
	global $ost;
	if(is_object($ost)){	
		$ost->addHeader('
		// http://github.com/clonemeagain/osticket-plugin-notefirst
		(function($){
		          $(document).on("ready pjax:success",function(){
		                // Set the default response to "Internal Note", unless there is a reply hash in the URL.
		                if(!location.hash){
		                    $("#post-note-tab").click(); // Upgrade to 1.10
		                }
		          });
		})(jQuery);');
	}
}

```

I've subscribed to that pull-request, so I'll update this when/if that get's merged.
