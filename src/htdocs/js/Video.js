/* global jwplayer */
'use strict';


var Util = require('util/Util');


var _COUNT,
    _DEFAULTS;

_COUNT = 0;
_DEFAULTS = {
  key: 'zBOH983t+dtdhriK4drdRPIStHMX02RUk0paAg=='
};


var Video = function (options) {
  var _this,
      _initialize,

      _el,
      _elPlaylist,

      _buildOptions,
      _buildPlaylist,
      _getItems,
      _setupPlayer;


  _this = {};

  _initialize = function (options) {
    var jwplayerOpts;

    options = Util.extend({}, _DEFAULTS, options);
    _el = options.el || document.createElement('video');
    _elPlaylist = options.elPlaylist; // optional, ok if not set

    // v7+ requires a key
    jwplayer.key = options.key;

    _COUNT ++;
    jwplayerOpts = _buildOptions();
    if (jwplayerOpts.file) {
      _setupPlayer(jwplayerOpts);
    }
  };


  /**
   * Create options for jwplayer instance
   *
   * @return {Object}
   *    options in format jwplayer expects
   */
  _buildOptions = function () {
    var application,
        file,
        livestream,
        opts,
        stream,
        track;

    // check if video is a live stream (rtmp live streaming is supported)
    // expects this syntax in <video> src attr: {stream}?streamer={application}
    if (_el.getAttribute('src').search(/streamer=/) !== -1) {
      livestream = true;
      stream = _el.getAttribute('src').split('?streamer=')[0];
      application = _el.getAttribute('src').split('?streamer=')[1];
      file = application + '/' + stream;
    } else {
      livestream = false;
      file = _el.getAttribute('src');
    }

    opts = {
      id: _el.getAttribute('id') || 'jwplayer' + _COUNT,
      livestream: livestream, // whether or not this is a livestream instance
      file: file,
      image: _el.getAttribute('poster') || '',
      controls: _el.hasAttribute('controls'),
      autostart: _el.hasAttribute('autoplay'),
      width: +_el.getAttribute('width') || 600,
      height: +_el.getAttribute('height') || 400
    };

    // NOTE: only 1 playlist per page is supported
    // (and when using a playlist, only 1 video elem per page is supported)
    if (_elPlaylist) {
      opts.playlist = _buildPlaylist();
    }

    // TODO: capture multiple track elems (for multi-language support)
    track = _el.querySelector('track');
    if (track) {
      opts.tracks = [{
        file: track.getAttribute('src')
      }];
    }

    // jwplayer requires an id value on the instantiated elem
    _el.setAttribute('id', opts.id);

    return opts;
  };


  /**
   * Create the playlist array for jwplayer
   * Expects the playlist to be defined using an html definition list, <dl>
   *
   * @return {Array}
   *    playlist in format jwplayer expects
   */
  _buildPlaylist = function () {
    var captions,
        description,
        dd,
        dt,
        i,
        j,
        items,
        image,
        playlist,
        playlist_item,
        title,
        video;

    items = _getItems(_elPlaylist);
    playlist = [];

    for (i = 0; i < items.length; i++) {
      dt = items[i].dt; // <dt> elems (title, video href)
      title = dt.textContent;
      video = dt.querySelector('a').getAttribute('href');
      captions = null;
      description = null;
      image = null;

      for (j = 0; j < items[i].dd.length; j++) {
        dd = items[i].dd[j]; // <dd> elems (description, poster img, captions)
        if (dd.classList.contains('description')) {
          description = dd.textContent;
        } else if (dd.classList.contains('image')) {
          image = dd.querySelector('img').getAttribute('src');
        } else if (dd.classList.contains('captions')) {
          captions = dd.querySelector('a').getAttribute('href');
        }
      }

      playlist_item = {
        description: description,
        image: image,
        sources: [{
          file: video
        }],
        title: title,
        tracks: [{
          file: captions
        }]
      };

      playlist.push(playlist_item);
    }

    // Playlist is embedded in jwplayer instance, so remove from DOM
    _elPlaylist.parentNode.removeChild(_elPlaylist);

    return playlist;
  };


  /**
   * Get items from a definition list and associate each <dt> with its <dd>'s
   *
   * @param dl {DOMElement}
   *    definition list
   *
   * @return {Object}
   *    matching items
   */
  _getItems = function (dl) {
    var child,
        children,
        i,
        type,
        item,
        items;

    items = [];

    children = dl.children;
    for (i = 0; i < children.length; i++) {
      child = children[i];
      type = child.nodeName;
      if (type === 'DT') {
        // create new item
        item = {
          dt: child,
          dd: []
        };
        items.push(item);
      } else if (type === 'DD') {
        // add to existing item
        if (item === null) {
          throw new Error('found DD before DT');
        }
        item.dd.push(child);
      } else {
        throw new Error('found unexpected element "' + type + '",' +
            ' expected DT or DD');
      }
    }

    return items;
  };


  /**
   * Instantiate jwplayer
   *
   * @param opts {Object}
   *    jwplayer options
   */
  _setupPlayer = function (opts) {
    var fixedOpts,
        p,
        player;

    // fixedOpts are applied to all jwplayer instances
    fixedOpts = {
      skin: {
        name: 'five'
      },
      captions: {
        backgroundOpacity: 60,
        fontSize: 12
      },
      ga: {} // Google Analytics
    };

    // merge passed opts with fixed opts
    opts = Util.extend({}, fixedOpts, opts);

    // instantiate player
    player = jwplayer(opts.id);
    player.setup(opts);

    // if player can't be setup in livestream mode, assume no Flash, alert user
    jwplayer().onSetupError(function(/*e*/) {
      if (opts.livestream) {
        p = document.querySelector('#' + opts.id + ' p');
        p.innerHTML = '<a href="https://get.adobe.com/flashplayer/" ' +
          'style="color: #fff">Flash Player</a> is required to view live streams.';
      }
    });

    // repeatedly try to play livestream when server is offline
    jwplayer().onBuffer(function() {
      setTimeout(function(){
        jwplayer().play();
      }, 15000);
    });
  };


  _initialize(options);
  options = null;
  return _this;
};


module.exports = Video;
