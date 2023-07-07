const gulp = require('gulp');
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
const gulpIf = require('gulp-if');

gulp.task('minify-js', function() {
  return gulp.src('admin/js/*.js')
    .pipe(gulpIf(file => !file.basename.endsWith('.min.js'), uglify()))
    .pipe(gulpIf(file => !file.basename.endsWith('.min.js'), rename({ suffix: '.min' })))
    .pipe(gulp.dest('admin/js'));
});

gulp.task('default', gulp.series('minify-js'));