#!/bin/sh
echo '{ "feeds" : [ { "type" : "B", "side" : "R", "comment" : "spilled a little", "before" : { "date" : "2015-01-01T07:30:00Z", "weight" : 400 }, "after" : { "date" : "2015-01-01T08:00:00Z", "weight" : 450 } } ] }' | curl -X POST --data-binary @- --header 'X-Mother-Username: p028' --header 'X-Mother-Password: student' --header 'Content-Type: application/json' -sS 'http://hhlrg.woodnathan.com/milk/api/api.php?_action=add_feeds'
