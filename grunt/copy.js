'use strict';


var config = require('./config');

var copy = {
  options: {
    mode: true,
    timestamp: true
  },

  build: {
    cwd: config.src,
    dest: config.build + '/' + config.src,
    expand: true,
    filter: 'isFile',
    src: [
      '**/*',
      '!**/*.js',
      '!**/*.scss'
    ]
  },

  dist: {
    cwd: config.build + '/' + config.src,
    dest: config.dist,
    expand: true,
    filter: 'isFile',
    src: [
      '**/*',
      '!**/*.css',
      '!**/*.js',
      '!**/*.map'
    ]
  },

  jwplayer: {
    cwd: config.src + '/lib/jwplayer-7.9.3',
    dest: config.build + '/' + config.src + '/htdocs/lib/jwplayer',
    expand: true,
    src: [
      '**/*'
    ]
  }
};


module.exports = copy;
