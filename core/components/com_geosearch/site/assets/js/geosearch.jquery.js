/**
 * @package     hubzero-cms
 * @file        components/com_groups/assets/js/groups.jquery.js
 * @copyright   Copyright 2005-2011 Purdue University. All rights reserved.
 * @license     http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

//-----------------------------------------------------------
//  Ensure we have our namespace
//-----------------------------------------------------------
if (!HUB) {
	var HUB = {};
}

//----------------------------------------------------------
//  Members scripts
//----------------------------------------------------------
if (!jq) {
	var jq = $;
}

//----------------------------------------------------------
// Global-ish variables
// ---------------------------------------------------------

var map;
var bounds;
var infoWindow;
var oms;
var markersCount = 0;
var markerList = []; //keeps track of markers

var membersDisplayed = 0;
var eventsDisplayed  = 0;
var jobsDisplayed    = 0;
var orgsDisplayed    = 0;


HUB.Geosearch = {
	jQuery: jq,

	//initialize: function(latlng,uids,jids,eids,oids)
	initialize: function(latlng)
	{
		var $ = this.jQuery;

		var mapOptions = {
			scrollwheel: false,
			zoom: 2,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};

		map = new google.maps.Map(document.getElementById("map_canvas"),mapOptions);
		//bounds = new google.maps.LatLngBounds();
		infoWindow = new google.maps.InfoWindow();
		oms = new OverlappingMarkerSpiderfier(map, {keepSpiderfied:true});

		// create info windows
		oms.addListener('click', function(marker, event) {

			$.ajax({
				url: "/api/events/" + marker.scope_id,
				data: { nicedate : 1}
			})
			.done(function( data ) {

				var html = '';
				switch (marker.scope)
				{
					case "member":
						break;
					case "event":
						// get the data for the popup view
						var title = data.event.title;
						var content = data.event.content;
						var start =  data.event.publish_up;
						var end = data.event.publish_down;
						var location = data.event.adresse_info;
						var link = "/events/event/" + data.event.id;

						//contruct the popup view
						html += '<div class="event-popup">'; // classes for styling, because CSS
						html += '<h1>'+title+'</h1>';
						html += '<p class="date">'+start+' - '+ end + '</p>';
						html += '<p class="location">'+location+'</p>';
						html += '<p>'+content+'</p>';
						html += '<a href="'+link+'"><span class="link">Event Details</p></span>';
						html += '</div>';
					break;
					case "job":
						break;
					case "org":
					break;
				}
				console.log(data.event);

				infoWindow.setContent(html);
			});

 			 //infoWindow.setContent(marker.html);

			  infoWindow.open(map, marker);
		});

		// get markers
		this.loadMarkers();
	},

	loadMarkers: function()
	{
		var $ = this.jQuery;

		// container for holding desired markers to display
		var resources = [];

		// get the resources desired for display
		$('input:checkbox:checked.resck').each(function(){
			resources.push(this.value);
		});

		//get the markers
		$.post("index.php?option=com_geosearch&task=getmarkers",
		{
			resources: resources
		},
		function(data)
		{
			var markers = $.parseJSON(data);

			// places correct icon type
			// @todo: make dynamic types, icon would be stored in DB
			$.each(markers, function(index, marker)
			{
				switch(marker.scope)
				{
					case "member":
						var icon = "/core/components/com_geosearch/site/assets/img/icn_member.png";
						break;
					case "event":
						var icon = "/core/components/com_geosearch/site/assets/img/icn_event.png";
						break;
					case "job":
						var icon = "/core/components/com_geosearch/site/assets/img/icn_job.png";
						break;
					case "org":
						var icon = "/core/components/com_geosearch/site/assets/img/icn_org.png";
					break;
				}

				//var loc = new map.LatLng(marker.addressLatitude, marker.addressLongitude);
				var mlatlng = new google.maps.LatLng(marker.addressLatitude, marker.addressLongitude);
				var point = new google.maps.Marker({
				position: mlatlng,
				map: map,
				icon:icon,
				scope: marker.scope,
				scope_id: marker.scope_id
				//title: name,
				});

				// this adds the spidifier making clusters of markers easier to read.
				oms.addMarker(point)

				// keep track of markers so we can remove them later.
				markerList.push(point);

			}); // end each()

		});


	},

	displayCheckedMarkers: function()
	{
		// clear map
		$.each(markerList, function(index, marker)
		{
			marker.setMap(null);
		});

		HUB.Geosearch.loadMarkers();
	},

	createMarker: function(mlatlng, uid, type)
	{

	}
};

//-----------------------------------------------------------

jQuery(document).ready(function($){

	var latlng = new google.maps.LatLng(40.397, -86.900);
	//HUB.Geosearch.initialize(latlng,uids,jids,eids,oids);
	HUB.Geosearch.initialize(latlng);

	/*$("#clears").click(function() {
		$("#actags").tokenInput("clear");
		$("input[type=text]").val("");
	});

	*/

	$('.resck').on('click', function(event){
		HUB.Geosearch.displayCheckedMarkers();
	});


});