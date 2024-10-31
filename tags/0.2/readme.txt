=== RadiatedTwixel ===
Contributors: radiatedpixel
Donate link: http://www.radiatedpixel.com
Tags: Twitter, Radiated Pixel
Requires at least: 3.3
Tested up to: 3.5.1
Stable tag: 0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is a very lightweight free Twitter widget by Radiated Pixel.

== Description ==

This is a very lightweight free Twitter widget. You can add as many widgets as you like and poll another users timeline on every widget. Sadly Twitter restricts unauthorized requests, so you can only view a random number of tweets (max. 5), starting with the latest. 

== Installation ==

1. Upload `radiatedtwixel.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Drag and drop the widget from the 'Widgets' menu to your sidebar(s).
4. Enter the Twitter username you want to display tweets from in the widget form.

== Frequently asked questions ==

= Why is it not possible to set an amount of loaded tweets? =

Twitter declines the request for tweets without authentication, so it's only possible to view a random amount of tweets. However, the latest tweet should always be shown.

= Scratch that, it is possible now. Why? =

To get a specific amount of tweets, you have to activate all include-parameters inside the json-request (which is now done by the plugin). So if you want 5 tweets, you'll get them!

= How can I exclude retweets and replies? =

You have to edit the plugin on lines 156 and 157.

= Why arent more tweets loaded if I change the "Number of tweets to load" setting? =

Just wait for the page to make the next query to tTwitter. Alternatively you could set the "Update tweets every [x] seconds" to 0

== Screenshots ==

1. http://www.radiatedpixel.com/downloads/misc/twixelunstyled.png
2. http://www.radiatedpixel.com/downloads/misc/twixelrp.png

== Changelog ==

v0.2 - Introduced a setting to load a specific amount of tweets. 
v0.1 - First release, nothing to say here

== Upgrade notice ==
v0.2 - Since the "count" parameter is very important to us, we needed to activate all the Twitter parameters to get the correct amount of tweets. Therefore you'll see retweets and replies.