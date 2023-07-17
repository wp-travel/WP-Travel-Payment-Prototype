/* jshint node:true */
module.exports = function(grunt) {
  require('load-grunt-tasks')(grunt); // npm install --save-dev load-grunt-tasks
  /**
   * FIles added to WordPress SVN, don't inlucde 'assets/**' here.
   * @type {Array}
   */
  svn_files_list = [
      'i18n/**',
      'inc/**',
      'loco.xml',
      'wpml-config.xml',
      'readme.txt',
      'freemius/**',
      'license.php',
      'wp-travel-custom-payment.php',
      '!inc/modules/**/Gruntfile.js',
      '!inc/modules/**/package-lock.json',
      '!inc/modules/**/package.json',
      '!inc/modules/**/node_modules/**',
      '!inc/modules/**/README.md',
      '!inc/modules/**/assets/css/sass/**',
      '!inc/modules/**/assets/css/*.map',
      '!inc/modules/**/assets/js/src/**',
      '!inc/modules/**/app/src/**',
      '!inc/modules/**/yarn.lock',
      '!inc/modules/**/yarn-error.log',
      '!inc/modules/**/webpack.config.js',
      '!inc/modules/**/postcss.config.js',
      '!inc/modules/**/package.json',
  ];

  /**
   * Let's add a couple of more files to github.
   * @type {Array}
   */
  git_files_list = svn_files_list.concat([
      'bash',
      '\.editorconfig',
      '\.gitattributes',
      '\.gitignore',
      '\.gitlab-ci.yml',
      '\.jshintrc',
      'Gruntfile.js',
      'package-lock.json',
      'package.json',
      'push_dot_org.sh',
      'yarn.lock',
  ]);

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    // TODO: Not working this task.
    "file-creator": {
        "folder": {
            ".gitattributes": function (fs, fd, done) {
                var glob = grunt.file.glob;
                var _ = grunt.util._;
                fs.writeSync(fd, '# We don\'t want these files in our "plugins.zip", so tell GitHub to ignore them when the user click on Download ZIP' + '\n');
                _.each(git_files_list.diff(svn_files_list), function (filepattern) {
                    glob.sync(filepattern, function (err, files) {
                        _.each(files, function (file) {
                            fs.writeSync(fd, '/' + file + ' export-ignore' + '\n');
                        });
                    });
                });
            }
        }
    },
    // Other options.
    options: {
        text_domain: 'wp-travel-custom-payment'
    },
    // Generate POT files.
    makepot: {
        target: {
            options: {
                type: 'wp-plugin',
                domainPath: 'i18n/languages',
                exclude: ['deploy/.*', 'node_modules/.*', 'build/.*'],
                updateTimestamp: false,
                potHeaders: {
                    'report-msgid-bugs-to': '',
                    'x-poedit-keywordslist': true,
                    'language-team': '',
                    'Language': 'en_US',
                    'X-Poedit-SearchPath-0': '../../<%= pkg.name %>',
                    'plural-forms': 'nplurals=2; plural=(n != 1);',
                    'Last-Translator': 'WEN Solutions <info@wensolutions.com>'
                }
            }
        }
    },
    // Update text domain.
    addtextdomain: {
        options: {
            textdomain: '<%= options.text_domain %>',
            updateDomains: true
        },
        target: {
            files: {
                src: [
                    '*.php',
                    '**/*.php',
                    '!*.json',
                    '!*.zip',
                    '!**/*.json',
                    '!.sass-cache/**',
                    '!core/.sass-cache/**',
                    '!node_modules/**',
                    '!core/node_modules/**',
                    '!deploy/**',
                    '!tests/**',
                    '!inc/modules/**'
                ]
            }
        }
    },
    // Check textdomain errors.
    checktextdomain: {
        options: {
            text_domain: '<%= options.text_domain %>',
            keywords: [
                '__:1,2d',
                '_e:1,2d',
                '_x:1,2c,3d',
                'esc_html__:1,2d',
                'esc_html_e:1,2d',
                'esc_html_x:1,2c,3d',
                'esc_attr__:1,2d',
                'esc_attr_e:1,2d',
                'esc_attr_x:1,2c,3d',
                '_ex:1,2c,3d',
                '_n:1,2,4d',
                '_nx:1,2,4c,5d',
                '_n_noop:1,2,3d',
                '_nx_noop:1,2,3c,4d'
            ]
        },
        files: {
            src: [
                '**/*.php',
                '!node_modules/**',
                '!deploy/**'
            ],
            expand: true
        }
    },
    // Clean the directory.
    clean: {
        deploy: ['build']
    },
    copy: {
        build_it: {
            options: {
                mode: true
            },
            expand: true,
            src: svn_files_list,
            dest: 'build/<%= pkg.name %>/'
        },
        deploy: {
            src: [
                '**',
                '!.*',
                '!*.md',
                '!.*/**',
                '!tmp/**',
                '!Gruntfile.js',
                '!test.php',
                '!package.json',
                '!node_modules/**',
                '!tests/**',
                '!docs/**'
            ],
            dest: 'deploy/<%= pkg.name %>',
            expand: true,
            dot: true
        }
    },
    zip: {
        // 'build/<%= pkg.name %>-<%= pkg.version %>.zip': [svn_files_list]
        'using-delate': {
            cwd: 'build/',
            src: ['build/<%= pkg.name %>/**'],
            dest: 'build/<%= pkg.name %>-<%= pkg.version %>.zip',
            compression: 'DEFLATE'
        }
    },
  });

  grunt.registerTask( 'gitattributes', [ 'file-creator' ] );
  grunt.registerTask( 'assets', [] );
  grunt.registerTask( 'precommit', [ 'checktextdomain' ] );
  grunt.registerTask( 'textdomain', [ 'addtextdomain', 'makepot' ] );

  //   grunt.registerTask( 'pre_release', [ 'assets', 'textdomain' ] );
  // Add Text domain on copied directory.
  grunt.registerTask( 'build', [ 'makepot', 'clean:deploy', 'copy:build_it', 'addtextdomain', 'zip' ] );
};

/**
 * Helper
 */
// from http://stackoverflow.com/a/4026828/1434155
Array.prototype.diff = function (a) {
    return this.filter(function (i) {
        return a.indexOf(i) < 0;
    });
};
