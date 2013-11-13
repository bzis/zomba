'use strict';
module.exports = function(grunt) {
	grunt.initConfig({
	  html2js: {
	  	module: 'templates',
	    options: {
		    rename: function (modulePath) {
		      return modulePath.replace('../vendor/angular-ui/bootstrap/', '');
		    }
	    },
	    angularUiBootstrapTabs: {
	      src: ['vendor/angular-ui/bootstrap/template/tabs/*.html'],
	      dest: 'tmp/angular-ui-tabs-templates.js'
	    },
	    angularUiBootstrapModal: {
	      src: ['vendor/angular-ui/bootstrap/template/modal/*.html'],
	      dest: 'tmp/angular-ui-modal-templates.js'
	    }
	  },
	});

	grunt.loadNpmTasks('grunt-html2js');
	grunt.loadTasks('tasks');
	grunt.registerTask('default', ['html2js']);
}
