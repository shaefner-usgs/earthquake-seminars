(function() {

	var map, markers, cluster, lookup, max = 0;

	$(document).ready(function() {
		var interval = 2 * 60 * 1000;

		initHandlers();
		//initPrintHandler();
		initMap();
		loadUsers();
		setInterval(loadUsers, interval);
	});

	/**
	 * Initialize event handlers
	 */

	function initHandlers() {
		$('.expand').click(function(e) {
			e.preventDefault();

			$('body').toggleClass('expanded');
			// use jQuery to show / hide table cells because 'display' property is not affected
			// by css animations (want to delay hiding the content until animation is finsihed)
			if ($('body').hasClass('expanded')) {
				$('.expand').attr('title', 'Show map');
				$('.showifexpanded').show();
			} else {
				$('.expand').attr('title', 'Expand table');
				$('.showifexpanded').delay(250).fadeOut(0); // use fadeOut b/c jQuery's .hide doesn't implement delays
			}

			// reset map so that it displays correctly after changing its container size
			map.invalidateSize();
		});

		$('#details').on('click', 'tr', function() {
			return false; // TODO: fix leaflet markercluster render bugs so this works reliably; deactivate for now
			var id = $(this).attr('id'),
				key = lookup[id];

			// if record is not plotted on map, key will not be set
			if (key !== undefined) {
				console.log(key);
				cluster.zoomToShowLayer(markers[key], function() {
					console.log('made it');
					markers[key].openPopup();
				});
			}
		});
	}

	/**
	 * TODO: unfinished - tyring to reformat the map to fit all markers on *printed* page
	 */

	function initPrintHandler() {
		function resetMap() {
			map.invalidateSize();
			map.fitBounds(cluster.getBounds());
		}
		// Safari and Chrome
		if (window.matchMedia) {
			var mediaQueryList = window.matchMedia('print');
			mediaQueryList.addListener(function(mql) {
				if (mql.matches) {
					resetMap();
				} else {
					resetMap();
				}
			});
		}
		// Firefox and IE
		window.onbeforeprint = resetMap;
		window.onafterprint = resetMap;
	}

	/**
	 * Create Leaflet map
	 */

	function initMap() {
		var greyscale_base, greyscale_ref, greyscale, terrain;

		map = L.map('map', {
			center: [37.5, -122],
			zoom: 9,
			minZoom: 2,
			maxZoom: 15,
			scrollWheelZoom: false
		});

		// base layers
		greyscale_base = L.tileLayer('http://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
			attribution: '&copy;2014 Esri, DeLorme, HERE'
		});
		greyscale_ref = L.tileLayer('http://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Reference/MapServer/tile/{z}/{y}/{x}');
		greyscale = L.layerGroup([greyscale_base, greyscale_ref]);

		terrain = L.tileLayer('http://services.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
			attribution: 'Esri, HERE, DeLorme, TomTom, USGS, NGA, USDA, EPA, NPS'
		}).addTo(map);

		L.control.scale().addTo(map);
	}

	/**
	 * Get online viewers from json feed
	 */

	function loadUsers() {
		$.getJSON('users.json.php', function(data) {
		//$.getJSON('users.json', function(data) {
			if (data) {
				update(data);
			}
		}).fail(function() {
			showError('', 'Cannot load data feed of viewers');
		});
	}

	/**
	 * Wrapper for updates to page when info from feed is reloaded
	 * @param {object}	data	json feed
	 */

	function update(data) {

		// if streaming server is running - update time, status, table, and map
		if (data.metadata.status === 'active') {
			var current = parseInt(data.metadata.current, 10);

			// track maximum number of viewers
			if (current > max) {
				max = current;
			}
			data.metadata.max = max;

			updateTime();
			updateStatus(data.metadata);
			updateTable(data.features);
			updateMap(data.features);

			// alert user that feed was updated
			$('#alerts').removeClass('error').html('<p>Webcast viewers updated</p>').css('display', 'block');
			$('#alerts').delay(1500).fadeOut();
		}
		// if it's not running - show error, but don't "wipe out" cached data
		else {
			showError('Offline', 'Streaming server is not running');
		}
	}

	function showError(heading, error) {
		var has_cached_data = $('#details table').html().length;

		// only change status message if no cached data present; alert user if data is "old"
		if (has_cached_data) {
			error += ' (showing cached data)';
		}
		else {
			$('#status h2').html(heading);
		}
		$('#alerts').addClass('error').html('<p>' + error + '</p>');
	}

	function updateTime() {
		var date = new Date(),
			hours = date.getHours(),
			minutes = date.getMinutes(),
			period, time;

		if (hours < 12) {
			period = 'AM';
		} else {
			period = 'PM';
		}

		if (hours === 0) {
			hours = 12;
		} else if (hours < 10) {
			hours = '0' + hours;
		} else if (hours > 12) {
			hours -= 12;
		}

		if (minutes < 10) {
			minutes = '0' + minutes;
		}

		time = ['at ', hours, ':', minutes, ' ', period].join('');
		$('#title time').text(time);
	}

	/**
	 * Update status message above table
	 * @param {object}	metadata	json feed info
	 */

	function updateStatus(metadata) {
		var current = metadata.current,
			total = metadata.total,
			max = metadata.max,
			status = [current, ' watching (', max, ' max<span class="hide"> / ', total, ' total</span>)'].join('');

		$('#status h2').html(status);
	}

	/**
	 * Update table data
	 * @param {object}	features	json user details
	 */

	function updateTable(features) {
		var rows = '',
			header = '<tr><th>Name</th><th>IP Address</th><th>Duration</th><th class="showifexpanded">Viewing From</th><th class="showifexpanded">Flash Version</th></tr>';

		$.each(features, function(i, feature) {
			var user = feature.properties,
				minutes = Math.round(user.duration / 60),
				server = user.referrer.split(/\/+/)[1],
				row_html = ['<tr class="', user.type, '" id="', feature.id, '">',
					'<td>', user.name, '</td>',
					'<td>', user.ip, '</td>',
					'<td>', minutes, ' min</td>',
					'<td class="showifexpanded" title="', user.referrer, '">', server, '</td>',
					'<td class="showifexpanded">', user.version.ucFirst(), '</td></tr>'
				].join('');

			rows += row_html;
		});

		$('#details table').html(header + rows);
		$('.expand').removeClass('hide');
		if ($('body').hasClass('expanded')) {
			// using jquery instead of css to toggle display (so we can set a delay on hiding), so we need to explicitly call 'show' here
			$('.showifexpanded').show();
		}
	}

	/**
	 * Update points on map
	 * @param {object}	features	json user details
	 */

	function updateMap(features) {
		var markerOptions = {
			radius: 10,
			fillColor: "#f5b231",
			color: "#e2d3a9",
			weight: 2,
			opacity: 0.6,
			fillOpacity: 0.9
		};

		/* Markers' array keys must be sequential, starting at 0, for Leaflet to plot them.
			 Since records in the table are only plotted if a lat / lng value is present (obviously),
			 we need a lookup obj to map the viewer's id in the table to the markers on the map */
		markers = [];
		lookup = {};

		if (map.hasLayer(cluster)) {
			cluster.clearLayers();
		} else {
			cluster = new L.MarkerClusterGroup({
				showCoverageOnHover: false,
				maxClusterRadius: 1,
				spiderfyOnMaxZoom: true,
				spiderfyDistanceMultiplier: 2
			});
			map.addLayer(cluster);
		}

		L.geoJson(features, {
			pointToLayer: function (feature, latlng) {
				var marker = L.circleMarker(latlng, markerOptions);

				lookup[feature.id] = markers.length;
				markers.push(marker);
				return marker;
			},
			onEachFeature: function(feature, layer) {
				var user = feature.properties,
					minutes = Math.round(user.duration / 60),
					popup_html = ['<h1 class="', user.type, '">', user.name, '</h1><table>',
						'<tr><th>IP Address</th><td>', user.ip, '</td></tr>',
						'<tr><th>Duration</th><td>', minutes, ' minutes</td></tr>',
						'<tr><th>Started</th><td>', user.start, '</td></tr>',
						'<tr class="referrer"><th>Referrer</th><td>', user.referrer, '</td></tr>',
						'<tr class="flash"><th>Flash</th><td>', user.version.ucFirst(), '</td></tr></table>'
					].join(''),
					popup = L.popup({maxWidth: 480}).setContent(popup_html);

				layer.bindPopup(popup);
			}
		});

		cluster.addLayers(markers);
		if ($('#extent').prop('checked')) {
			map.fitBounds(cluster.getBounds());
			if (map.getZoom() > 11) {
				map.setZoom(11); // don't zoom in "too far"
			}
		}
	}

	/**
	 * Extend javascript String class w/ ucFirst method
	 * Returns a string with only the first letter uppercased
	 */

	String.prototype.ucFirst = function () {
		return this.substr(0, 1).toUpperCase() + this.substr(1).toLowerCase();
	};

})();

	/*
			livestream: {
				streamer: 'rtmp://video2.wr.usgs.gov/live',
				file: 'mplive',
				message: 'Checking for Menlo Park broadcast...',
				interval: 60
			}
	*/