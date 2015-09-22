# Human Lactation Research Group Mobile Application API

This document outlines the public facing operation of the API.

## Basics

All requests to the API must be made using HTTP POST, an error will be returned if any other HTTP method is used.

API actions are handled via the api.php page with the action specified in the query portion of the URL under the parameter '_action'.

The API will always respond with a JSON payload and a Content-Type of 'application/json'. The API ignores the occurence of an Accept header in the request, however this may be caught by the web server, as such it is recommended to send an Accept header with a value of 'application/json'.

Parameters sent to the action must be encoded using JSON with a Content-Type of 'application/json'.

All dates must conform to the ISO 8601 date formatting statement. The timezone for the date string must be UTC.

Example HTTP Request:

```
POST /api.php?_action=user_info
Accept: application/json
X-Mother-Username: username
X-Mother-Password: password
```

## Authentication

Two custom headers are used to authenticate API requests: `X-Mother-Username` and `X-Mother-Password`. Both of these must be supplied with each request, failing to supply either will result in an API error.

## Actions

- [Authenticate](#authenticate)
- [User Info](#user-info)
- [Add Feeds](#add-feeds)
- [Edit Feeds](#edit-feeds)
- [Get Feeds](#get-feeds)

### Authenticate

The `authenticate` action has been deprecated. Use the `user_info` action with the API authentication method instead.

### User Info
**Action Name:** `user_info`  
**Request Parameters:** None  
**Example Response:**

```
{
  "user": {
    "collecting_samples": true
  }
}
```

### Add Feeds
**Action Name:** `add_feeds`  
**Note:** Although creating multiple feeds in one request is possible it is not recommended.  
**Request Parameters:**

```
{
  "feeds": [
    {
      "type": "Breastfeed",
      "side": "Right",
      "comment": "spilled a little",
      "before": {
        "date": "2015-01-01T07:30:00Z",
        "weight": 400
      },
      "after": {
        "date": "2015-01-01T08:00:00Z",
        "weight": 450
      }
    },
    {
      "type": "Expressed",
      "side": "Left",
      "before": {
        "date": "2015-01-01T08:01:00Z",
        "weight": 10
      },
      "after": {
        "date": "2015-01-01T08:12:00Z",
        "weight": 460
      }
    },
    {
      "type": "Supplementary",
      "subtype": "Formula",
      "before": {
        "date": "2015-01-01T13:10:00Z",
        "weight": 470
      },
      "after": {
        "date": "2015-01-01T13:19:00Z",
        "weight": 485
      }
    }
  ]
}
```

**Example Response:**

```
{
  "errors": []
}
```

### Edit Feeds
**Not implemented yet**

### Get Feeds
**Action Name:** `get_feeds`  
**Request Parameters:** None  
**Example Response:**

```
{
  "feeds": [
    {
      "SNO": 1,
      "before": {
        "SID": 1,
        "date": "2015-01-01T07:30:00Z",
        "weight": "400.00"
      },
      "after": {
        "SID": 2,
        "date": "2015-01-01T08:00:00Z",
        "weight": "450.00"
      },
      "comment": "spilled a little",
      "type": "B",
      "subtype": "U",
      "side": "R"
    },
    {
      "SNO": 2,
      "before": {
        "SID": 3,
        "date": "2015-01-01T08:01:00Z",
        "weight": "10.00"
      },
      "after": {
        "SID": 4,
        "date": "2015-01-01T08:12:00Z",
        "weight": "460.00"
      },
      "comment": "",
      "type": "E",
      "subtype": "U",
      "side": "L"
    },
    {
      "SNO": 0,
      "before": {
        "SID": 5,
        "date": "2015-01-01T13:10:00Z",
        "weight": "470.00"
      },
      "after": {
        "SID": 6,
        "date": "2015-01-01T13:19:00Z",
        "weight": "485.00"
      },
      "comment": "",
      "type": "S",
      "subtype": "F",
      "side": "U"
    }
  ]
}
```

## Errors

If an API error occurs the response will follow this format:

```
{
  "error": {
    "code": 000,
    "message": "Description of error"
  }
}
```

| Code | Error |
| :--: | ----- |
| 100  | A method other than POST was used in the HTTP request. |
| 101  | The URL is missing the '_action' query parameter. |
| 103  | The value for the action parameter provided to the API is not recognised. |
| 104  | The `X-Mother-Username` request header was not present. |
| 105  | The `X-Mother-Passowrd` request header was not present. |
| 106  | The value of the Content-Type header of the request was not `application/json`. |
| 107  | The structure of the JSON request body posted to the action is invalid. |
| 200  | The username provided via the `X-Mother-Username` header was not found. |
| 201  | The password provided via the `X-Mother-Password` header does not match the username provided. |
| 300  | The value provided for the feed type is invalid. |
| 301  | A value for the side must be provided for the provided feed type. |
| 302  | A value for the side must be provided for the feed type of 'Expression'. |
| 303  | A value for the feed subtype must be provided for the feed type of 'Supplementary'. |
| 304  | The date format provided for the before date is invalid. |
| 305  | The date format provided for the after date is invalid. |
| 306  | The date value pair provided is invalid. The after date must occur after the before date. |
| 400  | An error occurred whilst attempting to save the feed to the database. |
| 401  | An error occurred whilst attempting to fetch the feeds from the database. |