#!/bin/bash
composer install
npm install
tsc
lessc assets/less/qpost.less webroot/css/qpost.css
node ./node_modules/gulp/bin/gulp.js styles
