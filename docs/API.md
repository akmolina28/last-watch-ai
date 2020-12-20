# Last Watch AI - REST API

Last Watch is an API-first application. Anything that is managed with the web interface can also be managed with the web API as documented here.

This documentation might not be completely up to date. You can always reverse engineer the API by using the inspector on your browser to see what the web inteface is doing. Also, you can refer to the [API Routes](https://github.com/akmolina28/last-watch-ai/blob/master/src/routes/api.php) and [API Tests](https://github.com/akmolina28/last-watch-ai/blob/master/src/tests/Feature/ApiTest.php) to see how the API is defined in the code.

## Example

Here is a basic example showing how to get the latest relevant detection events for a given profile.

#### HTTP Request

```
curl -i GET "http://<SERVER_IP>:<WEB_PORT>/api/events?page=1&relevant&profileId=1"
```

#### HTTP Response

```
{
  "data": [
    {
      "id": 331465,
      "image_file_name": "events\/annke101sd.20201219_171959023.jpg",
      "image_dimensions": "640x480",
      "occurred_at": "2020-12-19 23:19:59",
      "detection_profiles_count": 3
    },
    {
      "id": 331457,
      "image_file_name": "events\/annke101sd.20201219_171926090.jpg",
      "image_dimensions": "640x480",
      "occurred_at": "2020-12-19 23:19:26",
      "detection_profiles_count": 3
    }
    ...
  ]
}
```

## API Endpoints

### Detection Profiles

`/api/profiles` | `GET` | Get Detection Profiles

`/api/profiles/<profile_id>` | `GET` | Get single Detection Profile details

`/api/profiles` | `POST` | Create a new Detection Profile

`/api/profiles/<profile_id>` | `PATCH` | Update a Detection Profile

`/api/profiles/<profile_id>/status` | `GET` | Get the on/off status of a Detection Profile

`/api/profiles/<profile_id>/status` | `PUT` | Turn on/off a Detection Profile

`/api/profiles/<profile_id>/automations` | `GET` | Get the Automations to which a Detection Profile is subscribed

`/api/profiles/<profile_id>/automations` | `PUT` | Subscribe/unsubscribe a Detection Profile to an Automation

`/api/profiles/<profile_id>` | `DELETE` | Delete a Detection Profile

### Detection Events

`/api/events?<option_1>&<option_2>&...` | `GET` | Get list of Detection Events

Options:

* `page=<page_number>` | Page number starting with 1
* `relevant` | Only include events with a relevant detection
* `profileId=<profile_id>` | Only include events which matched the given profile

`/api/events/latest` | `GET` | Get the latest Detection Event

`/api/events/<event_id>` | `GET` | Get the details of a given Detection Event

`/api/events/<event_id>/next` | `GET` | Get the next event (chronologically) after a given Detection Event

`/api/events/<event_id>/previous` | `GET` | Get the previous event (chronologically) before a given Detection Event

### Automations

`/api/automations/telegram` | `GET` | Get all Telegram automation configs

`/api/automations/telegram` | `POST` | Create a new Telegram automation config

`/api/automations/webRequest` | `GET` | Get all Web Request automation configs

`/api/automations/webRequest` | `POST` | Create a new Web Request automation config

`/api/automations/folderCopy` | `GET` | Get all Folder Copy automation configs

`/api/automations/folderCopy` | `POST` | Create a new Folder Copy automation config

`/api/automations/smbCifsCopy` | `GET` | Get all SMB/CIFS Copy automation configs

`/api/automations/smbCifsCopy` | `POST` | Create a new SMB/CIFS Copy automation config

### General

`/api/alive` | `GET` | Check if Last Watch is up and running

`/api/statistics` | `GET` | Get the home page statistics

`/api/objectClasses` | `GET` | Get full list of objects which can be profiled

`/api/errors?page=<page_number>` | `GET` | Get latest automation errors starting with page 1

`/api/deepstackLogs?page=<page_number>` | `GET` | Get latest logs from the Deepstack API starting with page 1


