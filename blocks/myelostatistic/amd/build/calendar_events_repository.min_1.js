define(["jquery","core/ajax","core/notification"],function(a,b,c){var d=20,e=function(a){a.hasOwnProperty("limit")||(a.limit=d),a.limitnum=a.limit,delete a.limit,a.hasOwnProperty("starttime")&&(a.timesortfrom=a.starttime,delete a.starttime),a.hasOwnProperty("endtime")&&(a.timesortto=a.endtime,delete a.endtime);var e={methodname:"core_calendar_get_action_events_by_course",args:a},f=b.call([e])[0];return f.fail(c.exception),f},f=function(a){a.hasOwnProperty("limit")||(a.limit=10),a.limitnum=a.limit,delete a.limit,a.hasOwnProperty("starttime")&&(a.timesortfrom=a.starttime,delete a.starttime),a.hasOwnProperty("endtime")&&(a.timesortto=a.endtime,delete a.endtime);var d={methodname:"core_calendar_get_action_events_by_courses",args:a},e=b.call([d])[0];return e.fail(c.exception),e},g=function(a){a.hasOwnProperty("limit")||(a.limit=d),a.limitnum=a.limit,delete a.limit,a.hasOwnProperty("starttime")&&(a.timesortfrom=a.starttime,delete a.starttime),a.hasOwnProperty("endtime")&&(a.timesortto=a.endtime,delete a.endtime);var e={methodname:"core_calendar_get_action_events_by_timesort",args:a},f=b.call([e])[0];return f.fail(c.exception),f};return{queryByTime:g,queryByCourse:e,queryByCourses:f}});