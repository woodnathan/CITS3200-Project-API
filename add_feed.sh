#!/bin/sh
curl -X POST --data-binary @- --header 'X-Mother-Username: p028' --header 'X-Mother-Password: student' --header 'Content-Type: application/json' -sS 'http://hhlrg.woodnathan.com/milk/api/api.php?_action=add_feeds' <<JSON
{
  "feeds" :
  [
    {
      "type" : "Breastfeed",
      "side" : "Right",
      "comment" : "spilled a little",
      "before" : {
        "date" : "2015-01-01T07:30:00Z",
        "weight" : 400
      },
      "after" : {
        "date" : "2015-01-01T08:00:00Z",
        "weight" : 450
      }
    },
    {
      "type" : "Expressed",
      "side" : "Left",
      "before" : {
        "date" : "2015-01-01T08:01:00Z",
        "weight" : 10.0
      },
      "after" : {
        "date" : "2015-01-01T08:12:00Z",
        "weight" : 460.0
      }
    },
    {
      "type" : "Supplementary",
      "subtype" : "Formula",
      "before" : {
        "date" : "2015-01-01T13:10:00Z",
        "weight" : 470.0
      },
      "after" : {
        "date" : "2015-01-01T13:19:00Z",
        "weight" : 485.0
      }
    }
  ]
}
JSON
