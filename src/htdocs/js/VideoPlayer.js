/* global jwplayer */
'use strict';


var _COUNT = 0;


jwplayer.key = 'zBOH983t+dtdhriK4drdRPIStHMX02RUk0paAg==';


/**
 * Create a jwplayer video instance using the given <video> el's attributes.
 *
 * @param options {Object}
 *   {
 *     el: {Element}
 *     elPlaylist: {Element} optional
 *   }
 */
var VideoPlayer = function (options) {
  var _this,
      _initialize,

      _el,
      _elPlaylist,

      _getItems,
      _getOptions,
      _getPlaylist,
      _initPlayer;


  _this = {};

  _initialize = function (options) {
    _COUNT ++;
    _el = options.el || document.createElement('video');
    _elPlaylist = options.elPlaylist;

    _initPlayer();
  };

  /**
   * Get the items from the playlist <dl> and associate each <dt> with all of
   * its <dd>'s.
   *
   * @return items {Array}
   */
  _getItems = function () {
    var children,
        item,
        items,
        type;

    children = Array.from(_elPlaylist.children);
    items = [];

    children.forEach(child => {
      type = child.nodeName;

      if (type === 'DT') { // create new item
        item = {
          dt: child,
          dds: []
        };

        items.push(item);
      } else if (type === 'DD') { // add to existing item
        if (item === null) {
          throw new Error('Found DD before DT');
        }

        item.dds.push(child);
      } else { // unexpected type
        throw new Error(`Found unexpected element "${type}"; expected DT or DD`);
      }
    });

    return items;
  };

  /**
   * Get the dynamic jwplayer options from the embedded HTML Elements.
   *
   * @return options {Object}
   */
  _getOptions = function () {
    var file,
        livestream,
        options,
        parts,
        track;

    file = _el.getAttribute('src');
    livestream = false;
    track = _el.querySelector('track');

    // Check if video is a live stream. RTMP live streaming is supported using
    // this syntax in the <video> 'src' attr: {stream}?streamer={application}
    if (_el.getAttribute('src').search(/streamer=/) !== -1) {
      parts = _el.getAttribute('src').split('?streamer=');
      file = parts[1] + '/' + parts[0];
      livestream = true;
    }

    options = Object.assign({}, {
      autostart: _el.hasAttribute('autoplay'),
      controls: _el.hasAttribute('controls'),
      file: file,
      id: _el.getAttribute('id') || 'jwplayer' + _COUNT,
      image: _el.getAttribute('poster') || '',
      livestream: livestream
    });

    // NOTE: only 1 playlist per page is supported
    if (_elPlaylist) {
      options.playlist = _getPlaylist();
    }

    // TODO: capture multiple track elems (for multi-language support)
    if (track) {
      options.tracks = [{
        default: track.hasAttribute('default'),
        file: track.getAttribute('src'),
        kind: track.getAttribute('kind'),
        label: track.getAttribute('label')
      }];
    }

    return options;
  };

  /**
   * Get the playlist for jwplayer from the embedded playlist <dl> in the HTML.
   *
   * @return playlist {Array}
   */
  _getPlaylist = function () {
    var description,
        items,
        image,
        playlist,
        playlistItem,
        title,
        track,
        video;

    items = _getItems();
    playlist = [];

    items.forEach(item => {
      description = null;
      image = null;
      title = item.dt.textContent;
      track = null;
      video = item.dt.querySelector('a').getAttribute('href');

      // Get optional description, poster img, closed captions
      item.dds.forEach(dd => {
        if (dd.classList.contains('description')) {
          description = dd.textContent;
        } else if (dd.classList.contains('image')) {
          image = dd.querySelector('img').getAttribute('src');
        } else if (dd.classList.contains('cc')) {
          track = dd.querySelector('a').getAttribute('href');
        }
      });

      playlistItem = {
        description: description,
        image: image,
        sources: [{
          file: video
        }],
        title: title,
        tracks: [{
          file: track
        }]
      };

      playlist.push(playlistItem);
    });

    return playlist;
  };

  /**
   * Instantiate jwplayer.
   */
  _initPlayer = function () {
    var options = Object.assign({}, _getOptions(), {
      aspectratio: '16:9',
      captions: {
        backgroundOpacity: 60,
        fontSize: 12
      },
      skin: {
        name: 'five'
      },
      width: '100%'
    });

    if (options.file) {
      _el.setAttribute('id', options.id); // jwplayer requires an id on el

      // Instantiate jwplayer and log errors
      jwplayer(options.id).setup(options).on('setupError', e => {
        console.log('hi', e);
      });

      if (_elPlaylist) {
        _elPlaylist.parentNode.removeChild(_elPlaylist); // included in player
      }
    }
  };


  _initialize(options);
  options = null;
  return _this;
};


module.exports = VideoPlayer;
