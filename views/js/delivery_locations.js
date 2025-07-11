/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@expedierpascher.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade your module to newer
 * versions in the future.
 *
 * @author    MyFlyingBox <tech@myflyingbox.com>
 * @copyright 2017 MyFlyingBox
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @version   1.0
 *
 */

var lce_locations = null;
var infowindow = null;
var service_code = '';
var gmap = null;
var geocoder = null;
var markers = [];
var locations_data = [];

var chronodata=new Array();
var relay_map=null; // our map
var infowindow=null; // currently displayed infowindow
var map_markers=new Array();


function toggle_map_display(e)
{
    var selected_carrier_id = $('form#js-delivery input[type="radio"]:checked').val().split(',')[0];
    // Checking if we have a carrier that supports relay delivery
    if (carrier_ids.indexOf(selected_carrier_id) >= 0)
    {
        if(typeof e != "undefined") {
            e.stopPropagation();
        }
        $('#relay_container').show();
        load_locations(selected_carrier_id);
        jQuery('#map-canvas').delegate('.lce-select-location','click', select_location);
        $.scrollTo($('#relay_container'), 800);
        return false;
    } else {
      // Not a relay service, we hide the container
      $('#relay_container').hide();
    }
};

function load_locations(carrier_id) {
	// Prestashop 1.7 compatibility. For some reason the ajax URL is escaped despite smarty filter to unescape it
	ajax_url_mfb = ajax_url_mfb.replace(/&amp;/g, '&');
	
  jQuery.ajax({
    url: ajax_url_mfb,
	type: 'POST',
	dataType: 'json',
	cache: false,
    timeout:  15000,
	data: {
		'action': 'get_relay',
		'cart_id': cart_id,
		'carrier_id': carrier_id,
		'postal_code': customer_postal_code,
		'address': customer_address_street,
		'city': customer_city,
		'country': customer_country
	},
    error:    error_loading_locations,
    success:  show_locations,
    complete: function() {
      // td.removeClass( 'processing' ).unblock();
    }
  });
}


/*
 * Initialize the google map for a new display
 */
function init_gmap() {
	jQuery('#relay_container').css('display','block');
	var options = {
		zoom: 11,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	gmap = new google.maps.Map(document.getElementById("map-canvas"), options);
	geocoder = new google.maps.Geocoder();
	infowindow = new google.maps.InfoWindow();
}

/*
 * Close and clear the google map
 */
function close_gmap() {
	jQuery('#relay_container').css('display','none');
	jQuery('#map-canvas').html('');
	for (var i = 0; i < markers.length; i++) {
		markers[i].setMap(null);
	}
	markers = [];
	locations_data = [];
}

function update_zoom_gmap() {

	if (lce_locations.length == 0 ||  (lce_locations.length != 0 && markers.length < lce_locations.length))
	{
		return;
	}
	var bounds = new google.maps.LatLngBounds();

	for(var i = 0;i<markers.length;i++) {
		if (typeof markers[i] != 'undefined')
			bounds.extend(markers[i].getPosition());
	}
	gmap.setCenter(bounds.getCenter());
	gmap.fitBounds(bounds);
	gmap.setZoom(gmap.getZoom()-1);
	if(gmap.getZoom()> 15){
		gmap.setZoom(15);
	}
}

/*
 * Now that we have all the parcel points, we display them
 */
function show_locations(data) {
	lce_locations = data;

	init_gmap();

	// add parcel point markers
	for (i in lce_locations){
		loc = lce_locations[i];
		(function(i) {
			var name = loc.company;
			var address = loc.street;
			var city = loc.city;
			var postal_code = loc.postal_code;
			info ='<b>'+name+'</b><br/>'+
						'<span class="lce-select-location" data="'+i+'">'+'Sélectionner ce point relais'+'</span><br/>'+
						'<span>'+address+', '+postal_code+' '+city+'</span><br/>'+
						'<div class="lce-opening-hours"><table>';
			var opening_hours = loc.opening_hours;

            for (j in opening_hours){
                day = opening_hours[j];
                    info += '<tr>';
                    info += '<td><b>'+day.day+'</b> : </td>';
                    info += '<td>';
                    info += day.hours ;
                    info += '</td></tr>';
            }
            info += '</table></div>';

			locations_data[i] = info;

			if(geocoder)
			{
				geocoder.geocode({ 'address': address + ', ' + postal_code + ' ' + city }, function(results, status) {
					if(status == google.maps.GeocoderStatus.OK)
					{
						if (i == 0) {
							gmap.setCenter(results[0].geometry.location);
						}
						var marker = new google.maps.Marker({
							map: gmap,
							position: results[0].geometry.location,
							title : name
						});
						marker.set("content", locations_data[i]);
						google.maps.event.addListener(marker,"click",function() {
							infowindow.close();
							infowindow.setContent(this.get("content"));
							infowindow.open(gmap,marker);
						});
						markers[i] = marker;
						update_zoom_gmap();
					}
				});
			}
		})(i);
	}

	// remove info if we click on the map
	google.maps.event.addListener(gmap,"click",function() {
		infowindow.close();
	});
}

function select_location(source) {
		// Prestashop 1.7 compatibility. For some reason the ajax URL is escaped despite smarty filter to unescape it
		ajax_url_mfb = ajax_url_mfb.replace(/&amp;/g, '&');
  	var loc = lce_locations[jQuery(source.target).attr('data')];
  	var relay_description = loc.company + '<br/> ' + loc.street + ' - ' + loc.city;
  	$.ajax({
		url: ajax_url_mfb,
		type: 'POST',
		dataType: 'text',
		cache: false,
    	timeout:  15000,
		data: {
			'action': 'save_relay',
			'relay_code': loc.code,
			'cart_id' : cart_id
		},
      	success: function(data) {
        	jQuery('#input_selected_relay').html('<input type="hidden" name="selected_relay_code" value="'+loc.code+'"/>');
        	jQuery('#selected_relay_description').html(relay_description);
        	infowindow.close();
      	}
  	});
}

function error_loading_locations(jqXHR, textStatus, errorThrown ) {
	alert('Unable to load delivery locations'+' : '+errorThrown);
}
