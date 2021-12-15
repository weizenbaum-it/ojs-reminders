# Extended Review Reminders
A plugin for [OJS 3.3.0+](https://github.com/pkp/ojs), which allows users to configure multiple email reminders for reviews.

Reminders can be configured to be send at specific points in time before or after the response or review deadline respectively. Reminders can also be configured to use different built-in or custom email templates.

## Usage
Download the current release [here] and install it by logging in as an admin and going to **Settings > Website > Plugins**, selecting **Upload A New Plugin** and then selecting the downloaded .tar.gz file.

## Using development source
Clone the repository into the following directory relative to the root of your OJS installation:

    /plugins/generic/reviewReminders

Install dependencies via [NPM](https://www.npmjs.com/) and build:

    npm install
    npm run build

After building, follow the steps to [upgrade the OJS Database](https://openjournalsystems.com/ojs-3-user-guide/upgrading/) before enabling the plugin.

## License
This plugin is licensed under the GNU General Public License v2.
