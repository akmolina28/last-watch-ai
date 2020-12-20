# Last Watch AI - REST API

Last Watch is an API-first application. Anything that is managed with the web interface can also be managed with the web API as documented here.

This documentation might not be completely up to date. You can always reverse engineer the API by using the inspector on your browser to see what the web inteface is doing. Also, you can refer to the [API Routes](https://github.com/akmolina28/last-watch-ai/blob/master/src/routes/api.php) to see how the API is defined in the code.

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

`/api/profiles/<profile_id>/automation` | `PUT` | Subscribe/unsubscribe a Detection Profile to an Automation

`/api/profiles/<profile_id>` | `DELETE` | Delete a Detection Profile
