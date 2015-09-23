#!/bin/sh
curl -X POST --data-binary @- --header 'X-Mother-Username: p028' --header 'X-Mother-Password: student' --header 'Content-Type: application/json' -sS 'http://hhlrg.woodnathan.com/milk/api/api.php?_action=edit_feeds' <<JSON
{
  "feeds" :
  [
    {
      "before" : {
        "SID" : 5,
        "weight" : "650"
      },
      "after" : {
        "SID" : 6,
        "date" : "2015-01-01T13:21:00Z"
      },
      "type" : "S",
      "side" : "U",
      "subtype" : "F",
      "comment" : ""
    }
  ]
}
JSON
