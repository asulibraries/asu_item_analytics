ASU Item Analytics
==================

Provides an integration with Google Analytics to display a "download" count based on a configured event name associated with the item being displayed.

Requires configuration (`/admin/config/system/asu-item-analytics-settings`):
- *Credentials File Path*: key file with Google credentials allowing us to access the Analytics API. See the [Google Cloud Console](https://console.cloud.google.com/apis/dashboard) to add the Analytics API, create a Service Account, and download the credentials file to the server. (Obviously, store the file in a protected place outside of web root.) Add the service account as a viewer to the analytics account you will be querying data for.
- *Property ID*: The analytics property ID for the property we are querying. (*Not the Tag.*)
- *Event Name*: The name of the event we are requesting (our site is using a custom "resource_engagement" event).

The module includes a block for displaying the download count for the current page. We are using a twig tweak call in our templates to display the block rather than using the block placement configuration.

To reduce page-load time, the block uses JavaScript to call a provided JSON endpoint to get monthly totals since the beginning of 2024. We simply total those and display it. Other visualizations or endpoints could be future enhancements.

## Matomo Legacy Branch

This branch is exploring an alternative model, closer to the previous one. In this case, rather than pulling live data, we pull from the API on a regular basis to update a download count table. This is the old model used for Matomo. The primary rationale for this is that the Google Analytics doesn't allow us to migrate in old item count data. So, if we want to preserve existing data, we need to reuse the existing model.

The [existing query](src/Controller/Controller.php#L68-L128) could be re-written to get counts by path:

```php
$request->setDimensions([new Dimension(['name' => 'pagePath'])]);
$request->setDimensionFilter(new FilterExpression([ 'filter' => new Filter(['field_name' => 'eventName', 'in_list_filter' => new InListFilter(['values'=>['resource_engagement']])])]));
```

We can also adjust the DateRange to be from the day of the last run through 'yesterday' to ensure we don't double-count events.

### Database Structure

We can re-purpose a lot of the code in [asu_collection_extras](https://github.com/asulibraries/islandora-repo/tree/develop/web/modules/custom/asu_collection_extras); but we are using it differently, so we won't just tie into the exsiting ones.

```php
$schema = [
    'item_analytic_counts' => [
      'description' => 'Stores the item event counts by period.',
      'fields' => [
        'iid' => [
          'description' => "The item's id this record affects.",
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ],
        'type' => [
          'description' => 'Item type. (E.g. node, taxonomy_term, or media.)',
          'type' => 'varchar_ascii',
          'length' => DRUPAL_EXTENSION_NAME_MAX_LENGTH,
          'not null' => TRUE,
          'default' => '',
        ],
        'event' => [
          'description' => 'Event name we are tracking.',
          'type' => 'varchar_ascii',
          'length' => DRUPAL_EXTENSION_NAME_MAX_LENGTH,
          'not null' => TRUE,
          'default' => '',
        ],
        'period' => [
          'description' => 'Period for the event count in the format "YYYY-mm".',
          'type' => 'varchar_ascii',
          'length' => 7,
          'not null' => TRUE,
          'default' => '',
        ],
        'count' => [
          'description' => 'Event count for the item.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
        ],
      ],
      'primary key' => ['iid', 'type', 'event', 'period'],
      'indexes' => ['item' => ['iid','type']],
    ],
];
```

With this structure we can build a monthly download graph; although for now we will simply sum the counts for display. This also means updates simply updates for a given period and can avoid double-counting during updates.
