<?php
/*
Plugin Name: RadiatedTwixel
Plugin URI: http://www.radiatedpixel.com/wordpress/radiatedtwixel
Description: This is a very lightweight free Twitter widget. You can add as many widgets as you like and poll another users timeline on every widget. Sadly Twitter restricts unauthorized requests, so you can only view a random number of tweets, starting with the latest. However, you can limit the maximum number of tweets with this plugin. <strong>Get started now!</strong> Just activate the plugin, go to Appearance &gt; Widgets and drag and drop the widget to one of your sidebars.
Version: 0.2
Author: Radiated Pixel
Author URI: http://www.radiatedpixel.com/
License: GPL2

Copyright 2012  Radiated Pixel  (email : staff@radiatedpixel.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*/

class RadiatedTwixel extends WP_Widget
{
  private $lastCheckTimeOptionName = "radiatedtwixel_lastCheck";
  private $previousTweetsOptionName = "radiatedtwixel_prevTweets";
  function RadiatedTwixel()
  {
    $widget_ops = array('classname' => 'RadiatedTwixel', 'description' => 'Shows the Twitter feed from a given user.' );
    $this->WP_Widget('RadiatedTwixel', 'RadiatedTwixel', $widget_ops);
  }
 
  function form($instance)
  {
    $instance = wp_parse_args( (array) $instance, array( 'title' => 'RadiatedTwixel', 'user' => 'radiatedpixel', 'checktime'=>60, 'numTweets'=>5 ) );
    $title = $instance['title'];
    $user = $instance['user'];
    $checktime = $instance['checktime'];
    $numTweets = $instance['numTweets'];
?>
  <p>
    <label for="<?php echo $this->get_field_id('title'); ?>">
      Title: 
      <input class="widefat" 
        id="<?php echo $this->get_field_id('title'); ?>" 
        name="<?php echo $this->get_field_name('title'); ?>" 
        type="text" 
        value="<?php echo attribute_escape($title); ?>" />
    </label>
    <label for="<?php echo $this->get_field_id('user'); ?>">
      Username: 
      <input class="widefat" 
        id="<?php echo $this->get_field_id('user'); ?>" 
        name="<?php echo $this->get_field_name('user'); ?>" 
        type="text" 
        value="<?php echo attribute_escape($user); ?>" />
    </label>
    <label for="<?php echo $this->get_field_id('numTweets'); ?>">
      Number of tweets to load:
      <input class="widefat" 
        id="<?php echo $this->get_field_id('numTweets'); ?>" 
        name="<?php echo $this->get_field_name('numTweets'); ?>" 
        type="text" 
        value="<?php echo attribute_escape($numTweets); ?>" />
    </label>
    <label for="<?php echo $this->get_field_id('checktime'); ?>">
      Update tweets every [x] seconds: 
      <input class="widefat" 
        id="<?php echo $this->get_field_id('checktime'); ?>" 
        name="<?php echo $this->get_field_name('checktime'); ?>" 
        type="text" 
        value="<?php echo attribute_escape($checktime); ?>" />
    </label>
  </p>
<?php
  }
  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    $instance['user'] = $new_instance['user'];
    $instance['checktime'] = $new_instance['checktime'];
    $instance['numTweets'] = $new_instance['numTweets'];
    add_option($this->previousTweetsOptionName.$instance['user'], array(), "Currently loaded tweets", true);
    add_option($this->lastCheckTimeOptionName.$instance['user'], 0, "Last time tweets were updated", true);
    return $instance;
  }
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);
    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
    if (!empty($title)){
      echo $before_title . $title . $after_title;
    }
    $lastCheckTime = get_option($this->lastCheckTimeOptionName.$instance['user']);

    $curDate = new DateTime();
    $curDate = $curDate->format("U");
    if ($curDate > $lastCheckTime + $instance['checktime']){
      $result = $this->getTwitterFeed($instance);  
      update_option($this->previousTweetsOptionName.$instance['user'], $result);
      update_option($this->lastCheckTimeOptionName.$instance['user'], $curDate);
    }else{
      $result = get_option($this->previousTweetsOptionName.$instance['user']);
    }

    $postdata = json_decode($result);
    echo "<ul>";
    foreach ($postdata as $key=>$val) {
      $text = $this->processTweetLinks($val->text);
      $date = strtotime($val->created_at);
      $dateTime = date_create_from_format("U", $date);
      $time = $dateTime->format(get_option('time_format'));
      $readableDate = $this->makeDateReadable($dateTime);
      $html = "<li class='tweet'>
              <div class='content'>{$text}</div>
              <time class='date'>{$readableDate}</time>
              <time class='time'>{$time}</time>
              </li>";
      echo $html;
    }
    echo "</ul>";
    echo $after_widget;
  }
  function makeDateReadable($time){
        $feedDate = $time;
        $time = $time->format('Y-m-d H:i:s');
        $curDate = new DateTime();
        $curDay = $curDate->format('d');
        $curDate = $curDate->format('Y-m-d');

        $spacePos = strpos($time, " ");
        $date = substr($time, 0, $spacePos);
        $explodedDate = explode("-", $date);
        if ($date === $curDate) return 'today';
        else if (intval($explodedDate[2]) + 1 == intval($curDay)) return 'yesterday';
        return $feedDate->format(get_option('date_format'));
    }
  function processTweetLinks($ret) {
    $ret = preg_replace("#(^|[\n ])@([^ \'\´\`\;\.\?\!\:\"\t\n\r<]*)#ise", "'\\1<a href=\"http://www.twitter.com/\\2\" target=\"blank\">@\\2</a>'", $ret);
    $ret = preg_replace("#(^|[\n ])\#([^ \'\´\`\;\.\?\!\:\"\t\n\r<]*)#ise", "'\\1<a href=\"http://www.twitter.com/search?q=\\2&src=hash\" target=\"blank\" >#\\2</a>'", $ret);
    $ret = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t<]*)#ise", "'\\1<a target=\"blank\" href=\"\\2\" >\\2</a>'", $ret);
    $ret = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r<]*)#ise", "'\\1<a target=\"blank\" href=\"http://\\2\" >\\2</a>'", $ret);
    return $ret;
  }
  function getTwitterFeed($instance){
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 
      "http://api.twitter.com/1/statuses/user_timeline.json?".
        "include_entities=true&".
        "include_rts=true&".
        "exclude_replies=false&".
        "screen_name={$instance['user']}&".
        "count={$instance['numTweets']}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
  }
}
add_action( 'widgets_init', create_function('', 'return register_widget("RadiatedTwixel");') );?>