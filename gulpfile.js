"use strict";

const autoprefixer = require("gulp-autoprefixer");
const csso = require("gulp-csso");
const del = require("del");
const gulp = require("gulp");
const htmlmin = require("gulp-htmlmin");
const runSequence = require("run-sequence");
const sass = require("gulp-sass");
const uglify = require("gulp-uglify");
const less = require("gulp-less");

const AUTOPREFIXER_BROWSERS = [
    "ie >= 10",
    "ie_mob >= 10",
    "ff >= 30",
    "chrome >= 34",
    "safari >= 7",
    "opera >= 23",
    "ios >= 7",
    "android >= 4.4",
    "bb >= 10"
];

gulp.task("styles", function () {
    return gulp.src("./assets/css/style.css")
        .pipe(less({
            paths: [
                ".",
                "./node_modules/bootstrap-less"
            ]
        }))
        .pipe(autoprefixer({browsers: AUTOPREFIXER_BROWSERS}))
        .pipe(csso())
        .pipe(gulp.dest("./webroot/css"))
});