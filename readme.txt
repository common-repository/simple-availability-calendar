=== SA Calendar ===
Contributors: andreyvdenisov
Tags: calendar, availability, schedule, appointment
Requires at least: 4.1
Tested up to: 5.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create and manage schedules for site users, show availability calendars with appointments booking interface

== Description ==

Simple Availability Calendar plugin is designed for running the availability schedules of registered site users and presenting their availability calendars.

The key controls of the plugin are:
- Web control for entering the availability schedule for week days
- Web control for showing availability calendar with appointments booking interface
- Web control for showing extended availability calendar with appointments booking interface
- Web control for user's appointments list

This plugin gives the next main advantages:
- Shortcodes for all web controls with special parameter for selecting the schedule owner – current user, current post author or user id
- Customizable date and time format, first week day – plugin uses WordPress site settings, admin can change it from standard WordPress settings page
- Timezones for users availability schedules – all dates and times will be shown according to the selected timezone
- Tuning the availability schedule with extended calendar – user can enable or disable certain dates and times
- Dashboard pages: admin can edit users schedules and appointments
- Uploading availability schedules for the next dates via Ajax, without page reload
- Reduced Ajax traffic for better performance and faster data upload
- Standard form for booking an appointment with email, name and message fields, and captcha for preventing malicious requestszzz

The core plugin control is the availability schedule. Here user can enter his schedule according to the week days. Also it is possible to switch off the whole schedule and become unavailable. And very useful feature for users from different parts of the world – selecting appropriate timezone (by default: site timezone). Control is shown for the current signed-in site user.
Shortcode:
`[sa_calendar_schedule]`

Availability calendar control is designed for compact presenting of the user available dates and times. Ajax is used for uploading the schedules for next months, so user don't need to wait the page reloading. User can book an appointment by selecting date and time and filling appointment form.
Shortcode:
`[sa_calendar_calendar for_user="current|post_author|UID"]`

Extended availability calendar gives more opportunities for tuning the schedule. Being shown for schedule owner, it allows to manage schedule by enabling or disabling certain dates and times, and indicates the times with booked appointments. When shown for other users, it just presents the same availability schedule in different view. And, of course, user can book an appointment by selecting date and time and filling appointment form.
Shortcode:
`[sa_calendar_extcalendar for_user="current|post_author|UID"]`

User's appointments list presents information about current and previous appointments of current user. There is a button for showing/hiding previous appointments. Also schedule owner can cancel each appointment and delete all information about it from the site database.
Shortcode:
`[sa_calendar_userapps for_user="current|post_author|UID"]`

Simple Availability Calendar allows users to manage their availability schedules and appointments from WordPress dashboard. And super admin can manage all schedules and appointments of the site.

for_user parameter:
- current – control is shown for the current signed-in user
- post_author – control is shown for the current post author inside the WordPress loop
- UID – non-negative integer, control is shown for WordPress user with this ID

== Installation ==

To install the plugin, follow the steps below

1. Upload `simple-availability-calendar` to the `/wp-content/plugins/` directory OR install through admin Plugins page
2. Activate the plugin in 'Plugins' page in WordPress
3. Create pages or widgets with plugin shortcodes

== Frequently Asked Questions ==

= What PHP version should I have for using this plugin? =

You should have PHP Version 5.3 or higher.

== Screenshots ==

1. Availability schedule
2. Availability calendar
3. Extended availability calendar
4. User's appointments

== Changelog ==

= 1.0 =
* Plugin release.

== Upgrade Notice ==

= 1.0 =
First version of the plugin