'use strict';


var config = require('./config');

var eslint = {
  options: {
    format: 'stylish',
    overrideConfigFile: '.eslintrc.json'
  },

  build: {
    src: [
      'Gruntfile.js',
      'grunt/**/*.js',
      config.src + '/htdocs/**/*.js'
    ]
  }
};


module.exports = eslint;
