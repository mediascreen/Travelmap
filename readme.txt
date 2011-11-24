=== Travelmap ===
Contributors: mediascreen
Donate Link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=marcus%40mediascreen%2ese&item_name=Travelmap
Tags: travel,map,maps,trip,travel blog,travel plan,Google maps,geocoding,location,round the world trip
Requires at least: 2.7
Tested up to: 3.2.1
Stable tag: 1.5

Generates a map of your travels in any post or page based on a list of places.

== Description ==

Travelmap helps you show your travels on a Google map in any post or page. Add places you have visited or plan to visit to show them connected on a world map.

Add arrival dates to automatically show your current position and where you have been so far. Map markers and city names can be linked to a custom url - for example a blog post, wikipedia entry or Flickr album. Geocoding is done automatically based on city and country - but if you need to you can override with your own coordinates.

The maps and lists can be customized using shortcode attributes. There is a full list of available options under the installation tab.

[See Travelmap in use](http://travelingswede.com/my-travel-map/ "A demo of Travelmap using my travels")  
[The plugin homepage](http://travelingswede.com/travelmap/)  
[Travelmap forum at Uservoice](http://travelmap.uservoice.com)

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place shortcodes in the posts or pages where you want to show the map or list:  
   For the map:  
   `[travelmap-map]`  
   For the list:  
   `[travelmap-list]`
1. Add places you want to show. You will find the plugin options page under settings > Travelmap

= Setting the map size =
The map width is 100% of the containing element. If you want another width you simply style its parent with css. Use the shortcode attribute height to set the height of the map. Default is 300px.  
`[travelmap-map height=500]`  

= Setting the map mode =
Choose between roadmap, satellite, hybrid or terrain. Default is roadmap.  
`[travelmap-map mapmode="hybrid"]`  
[More on Google maps mapmodes](http://code.google.com/apis/maps/documentation/javascript/tutorial.html#MapOptions)

= Showing partial maps/lists =
Your can show only some of your places by using shortcode attributes first and/or last. This is handy if you have done several trips and want to show them in separate posts or pages.  
`[travelmap-map first=5 last=15]`  
This works for the list as well:  
`[travelmap-list first=5 last=15]`

The numbers are the row numbers from the plugin options page. Different maps can have overlapping numbers. If you add places before the row you have used as first attribute somewhere you obviously need to change that attribute.

You can also use negative numbers for both start and end to count backwards from the last entry.  
`[travelmap-map first=-15]`  
will show a map with the last 15 entries

You can also use dates to show partial maps. Use first and last with dates instead:  
`[travelmap-map first=2010-01-01 last=2010-12-31]`

The 'last' parameter is optional in both cases.

= Hiding lines or markers =
It is possible to turn of either markers or lines in the map by setting them to false in the options:  
`[travelmap-map lines=false]`  
`[travelmap-map markers=false]`

= Turning off marker numbering =
You can remove all numbers from markers:  
`[travelmap-map numbers=false]` 

= Reversing order =
The attribute reverse can be used to reverse the order of the map or the list. Note that the reversal is the last thing that's done - so other options are applied before the order is changed.  
`[travelmap-map reverse=true]`  
`[travelmap-list reverse=true]`

= Setting custom date format =
Arrival dates in the list is shown in the format the blog uses as default. To change the date format you can use the attribute "dateformat"  
`[travelmap-list dateformat="F j, Y"]`  
[More on date formats](http://codex.wordpress.org/Formatting_Date_and_Time)

= Questions or suggestions? =
Please use the [Travelmap forum at Uservoice](http://travelmap.uservoice.com)

== Screenshots ==
1. The map with lines and markers active
2. The plugin admin page

== Changelog ==

= 1.5 =
* Added clickable markers - all markers with urls are now clickable
* Added option to choose mapmode (roadmap/satellite/hybrid etc)
* Added check for dates before showing arriving column
* Added option to set custom date format for lists (blog setting format is default)
* Added negative numbers to first/last to make it easier to show the N:th latest destinations
* Added option to reverse order for list and map
* Added option to turn off numbers on the markers
* Added ssl setting for map pages
* Added editable versions of marker files for people who whant to change them
* Refactored to static class structure and added better documentation
* Fixed "disappearing map controls"-bug
* Fixed datepicker bug on new rows
* Fixed uninstall error
* Fixed lat/lng rounding
* Updated included libraries


= 1.4 =
* Fixed a bug that affected the deletion of rows in IE
* Fixed a major geocoding bug that could cause some pretty random geocoding results
* Fixed a bug that could cause extra slashes to appear in city and coutry fields
* Fixed a minor bug that could cause the datepicker widget to disappear
* Added custom markers for 1-99 in tree different colors to replace the standard lettered marker
* Added possibility to split maps/lists based on dates as well as row order
* Added shortcode options for hiding markers or lines
* Added autofocus on first input field when editing or adding row
* Added save on enter/return when editing fields
* Adjusted z-index to ensure that the (black) current marker always never is hidden by other markers

= 1.3.1 =
* Fixed geocoding bug. Changed to using LatLng functions instead of private variables.

= 1.3 =
* Added shortcode options for showing subset of places in maps and lists
* Added row numbering to list in options page
* Improved security checks before saving data

= 1.2 =
* Added jQuery-ui datepicker to date field
* Improved layout stability of admin table
* Changed standard colors for markers and lines

= 1.1 =
* Fixed race condition that sometimes saved before geocoding
* Improved first use, hides public maps/lists if there is no data
* Updated text on options page

= 1.0 =
* Added drag and drop for locations
* Changed server response code to 401 for nonce missmatch