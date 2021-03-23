# Drift Local plugin
Site administrators have the option to receive notifications
about important news and updates for their site directly from the
Drift communications channel. News, updates, and information appear to
site administrators in the Drift Widget.

![local_drift](https://help.openlms.net/wp-content/uploads/2020/05/driftnotificationwidget-1.png)

Site administrators can unsubscribe from this communication 
channel using the Drift subscriptions link on their profile page.

This plugin was contributed by the Open LMS Product Development team. Open LMS is an education technology company
dedicated to bringing excellent online teaching to institutions across the globe.  We serve colleges and universities,
schools and organizations by supporting the software that educators use to manage and deliver instructional content to
learners in virtual classrooms.

## Installation
Extract the contents of the plugin into _/wwwroot/local_ then visit `admin/upgrade.php` or use the CLI script to upgrade your site.

### Configuration
You can go to _/local/drift/view.php?controller=subscription&action=view_ to
manage the Drift subscription

Through _/admin/settings.php?section=local_drift_ you can manage the Drift conneciton.

## License
Copyright (c) 2021 Open LMS (https://www.openlms.net)

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <http://www.gnu.org/licenses/>.
