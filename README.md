ASU Item Analytics
==================

Provides a download count including data previously gathered by Matomo and new counts from Google Analytics. The Google Analytics counts are based on a configured event name associated with the item being displayed.

Requires configuration (`/admin/config/system/asu-item-analytics-settings`):
- *Credentials File Path*: key file with Google credentials allowing us to access the Analytics API. See the [Google Cloud Console](https://console.cloud.google.com/apis/dashboard) to add the Analytics API, create a Service Account, and download the credentials file to the server. (Obviously, store the file in a protected place outside of web root.) Add the service account as a viewer to the analytics account you will be querying data for.
- *Property ID*: The analytics property ID for the property we are querying. (*Not the Tag.*)
- *Event Name*: The name of the event we are requesting (our site is using a custom "resource_engagement" event).

The module includes a block for displaying the download count for the current page. We are using a twig tweak call in our templates to display the block rather than using the block placement configuration.

## Replacing Existing Analytics

The [asu_collection_extras](https://github.com/asulibraries/islandora-repo/tree/develop/web/modules/custom/asu_collection_extras) module had support for analytics counts but used a different structure and is now deprecated.

## Updating the Counts

Google Analytics are gathered via a Drush command which takes a valid PHP Date Format string, including relative formats (e.g. "2024-03", "2024-03-03", "this month", or "last month"). Analytics are gathered by the month period in which to provided parameter belongs. (E.g. "2024-03-15" will gather statistics from "2024-03-01" to "2024-03-31".) Calling the script for the same period will update the existing record.

```sh
drush --uri https://keep-dev.lib.asu.edu aia-gga "last month"
```
