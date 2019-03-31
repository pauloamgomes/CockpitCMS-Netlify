# Cockpit CMS Netlify Deploys Addon

This addon provides integration with Netlify deploys mechanism, leveraging the Netlify REST API to retrieve list of latest deploys and build hooks to trigger a new deploy. The addon can be useful when combining Cockpit CMS with an static site generator hosted on Netlify, so changes on contents can be deployed easily in seconds.

## Installation

1. Confirm that you have Cockpit CMS (Next branch) installed and working.
2. Download zip and extract to 'your-cockpit-docroot/addons' (e.g. cockpitcms/addons/Netlify, the addon folder name must be Netlify)
3. Confirm that the Netlify deploys icon appears on the top right modules menu.

## Configuration

1. Ensure that from your Netlify account you have an access token and a build hook url.
2. Edit Cockpit config/config.yaml and add a new entry for netlify like below:

```yaml
netlify:
  build_hook_url: https://api.netlify.com/build_hooks/<hook_id>
  api_url: https://api.netlify.com/api/v1
  site_id: <site-id>
  access_token: <access-token>
  branch: <branch-name>
```

Branch is optional, if provided API will return only deploys for that branch.

### Permissions

There are just two permissions:

- **manage.view** - provides access to the Netlify deploy list
- **manage.deploy** - provides access to trigger a new deploy

## Usage

Having the configuration defined accessing the Netlify deploys page (/netlify/deploys) a list of latest (limited to 50) deploys is displayed:

![Netlify dashboard](https://monosnap.com/image/bN5lqOC4FP1zFV7h278EHdz1MoSzxM.png)

To trigger a new deploy just hist the Deploy button an confirm the action.

## Demo

[![Netlify addon screencast](http://img.youtube.com/vi/duK78Lig2KQ/0.jpg)](http://www.youtube.com/watch?v=duK78Lig2KQ "Netlify addon screencast")

## Copyright and license

Copyright 2018 pauloamgomes under the MIT license.
