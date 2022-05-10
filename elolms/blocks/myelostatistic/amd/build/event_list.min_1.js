define(["jquery","core/notification","core/templates","core/custom_interaction_events","block_myelostatistic/calendar_events_repository"],function(a,b,c,d,e){var f=86400,g={EMPTY_MESSAGE:'[data-region="empty-message"]',ROOT:'[data-region="event-list-container"]',EVENT_LIST:'[data-region="event-list"]',EVENT_LIST_CONTENT:'[data-region="event-list-content"]',EVENT_LIST_GROUP_CONTAINER:'[data-region="event-list-group-container"]',LOADING_ICON_CONTAINER:'[data-region="loading-icon-container"]',VIEW_MORE_BUTTON:'[data-action="view-more"]'},h={EVENT_LIST_ITEMS:"block_myelostatistic/event-list-items",COURSE_EVENT_LIST_ITEMS:"block_myelostatistic/course-event-list-items"},i=function(a){a.attr("data-loaded-all",!0)},j=function(a){return!!a.attr("data-loaded-all")},k=function(a){var b=a.find(g.LOADING_ICON_CONTAINER),c=a.find(g.VIEW_MORE_BUTTON);a.addClass("loading"),b.removeClass("hidden"),c.prop("disabled",!0)},l=function(a){var b=a.find(g.LOADING_ICON_CONTAINER),c=a.find(g.VIEW_MORE_BUTTON);a.removeClass("loading"),b.addClass("hidden"),j(a)||c.prop("disabled",!1)},m=function(a){return a.hasClass("loading")},n=function(a){a.attr("data-has-events",!0)},o=function(a){return!!a.attr("data-has-events")},p=function(a,b){b?n(a):o(a)||q(a)},q=function(a){a.find(g.EVENT_LIST_CONTENT).addClass("hidden"),a.find(g.EMPTY_MESSAGE).removeClass("hidden")},r=function(a,b,d){return a.removeClass("hidden"),c.render(d,{events:b}).done(function(b,d){c.appendNodeContents(a.find(g.EVENT_LIST),b,d)})},s=function(a,b){var c=b.timesort||0;return c-a},t=function(a,b,c){var d=a.attr("data-midnight"),e=+c.attr("data-start-day")*f,g=+c.attr("data-end-day")*f,h=s(d,b);return""===c.attr("data-end-day")?e<=h:e<=h&&h<g},u=function(b,c){return function(d){return t(b,d,a(c))}},v=function(b,c){var d=0,e=h.EVENT_LIST_ITEMS;return b.attr("data-course-id")&&(e=h.COURSE_EVENT_LIST_ITEMS),a.when.apply(a,a.map(b.find(g.EVENT_LIST_GROUP_CONTAINER),function(f){var g=c.filter(u(b,f));return g.length?(d+=g.length,r(a(f),g,e)):null})).then(function(){return d})},w=function(c,d){c=a(c);var g=+c.attr("data-limit"),h=+c.attr("data-course-id"),j=c.attr("data-last-id"),n=c.attr("data-midnight"),o=n-14*f;if(m(c))return a.Deferred().resolve();if(k(c),"undefined"==typeof d){var q={starttime:o,limit:g};j&&(q.aftereventid=j),h?(q.courseid=h,d=e.queryByCourse(q)):d=e.queryByTime(q)}return d.then(function(a){if(!a.events.length)return i(c),0;var b=a.events;return c.attr("data-last-id",b[b.length-1].id),b.length<g&&i(c),v(c,b).then(function(a){return a<b.length&&i(c),b.length})}).then(function(a){return p(c,a)}).fail(b.exception).always(function(){l(c)})},x=function(a){d.define(a,[d.events.activate]),a.on(d.events.activate,g.VIEW_MORE_BUTTON,function(){w(a)})};return{init:function(b){b=a(b),w(b),x(b)},registerEventListeners:x,load:w,rootSelector:g.ROOT}});