// tested with Node 22.5.1 and NPM 10.8.2
/**
 * External dependencies
 */
const gulp = require("gulp");
const { src, dest, watch, series, parallel } = gulp;

const { exec } = require("child_process");
const fs = require("fs");
const del = require("del");
const path = require("path");

const sass = require("gulp-sass")(require("sass"));
const jsonCssVar = require("gulp-json-to-css-variables");
const concat = require("gulp-concat");

const cleanCss = require("gulp-clean-css");
const criticalCss = require("penthouse");
const postcss = require("gulp-postcss");
const autoprefixer = require("autoprefixer");

const sharp = require("sharp");
const scriptMinify = require("gulp-minify");

const templatePath = ".";

function checkDirectoryExists(filePath) {
    const dirname = path.dirname(filePath);
    if (fs.existsSync(dirname)) {
        return true;
    }
    fs.mkdirSync(dirname, { recursive: true });
    console.log(`Created directory: ${dirname}`);
    return true;
}

/**
 * Generates the styles for the project.
 */
exports.styles = function () {
    return src([`${templatePath}/assets/src/sass/*.scss`])
        .pipe(sass().on("error", sass.logError))
        .pipe(postcss([autoprefixer()]))
        .pipe(
            cleanCss({
                compatibility: "*",
                level: {
                    1: {
                        specialComments: 0,
                    },
                },
            })
        )
        .pipe(dest(`${templatePath}/assets/dist/css`));
};

/**
 * Generates a list of image files and performs various optimization operations on them.
 */
exports.images = function (callback) {
    const imageSrc = `${templatePath}/assets/src/images/**/*.{jpg,jpeg,png,webp}`;
    const imageDist = `${templatePath}/assets/dist/images`;

    return src(imageSrc)
        .on("data", (file) => {
            const extension = path.extname(file.path).toLowerCase();
            let pipeline = sharp(file.path);

            switch (extension) {
                case ".jpg":
                case ".jpeg":
                    pipeline = pipeline.jpeg({
                        mozjpeg: true,
                        quality: 40,
                    });
                    break;

                case ".png":
                    pipeline = pipeline.png({
                        compressionLevel: 9,
                        adaptiveFiltering: true,
                        quality: 60,
                        palette: true,
                    });
                    break;

                case ".webp":
                    pipeline = pipeline.webp({
                        quality: 40,
                    });
                    break;

                default:
                    console.warn(`Unsupported file type: ${file.path}`);
                    resolve();
                    return;
            }

            const relativePath = path.relative(file.base, file.path);
            const outputFilePath = path.join(imageDist, relativePath);

            checkDirectoryExists(outputFilePath);

            pipeline
                .toFile(outputFilePath)
                .then(() => {
                    console.log("Processed image:", file.path);
                })
                .catch((err) => {
                    console.error(
                        "Error during image processing:",
                        err.message
                    );
                });
        })
        .on("error", (err) => {
            console.error("Error during image processing:", err.message);
            callback(err);
        })
        .on("end", () => {
            console.log("Images processed successfully.");
            callback();
        });
};

/**
 * Concatenates and minifies JavaScript files.
 */
exports.scripts = function () {
    const scriptSrc = `${templatePath}/assets/src/scripts`;
    const scriptDist = `${templatePath}/assets/dist/scripts`;

    if (!fs.existsSync(scriptSrc)) {
        console.warn("No scripts found to process.");
        return Promise.resolve();
    }

    if (!fs.existsSync(scriptDist)) {
        fs.mkdirSync(scriptDist, { recursive: true });
        console.log(`Created directory: ${scriptDist}`);
    }

    const minified = src(`${scriptSrc}/*.min.js`)
        .pipe(dest(scriptDist))
        .on("data", (file) => {
            console.log("Processed minified file:", file.path);
        });

    const toMinify = src([`${scriptSrc}/*.js`, `!${scriptSrc}/*.min.js`])
        .pipe(
            scriptMinify({
                ext: { min: ".min.js" },
                noSource: true,
            })
        )
        .pipe(dest(scriptDist));

    return Promise.all(
        [minified, toMinify].map(
            (stream) =>
                new Promise((resolve, reject) => {
                    stream.on("end", resolve).on("error", reject);
                })
        )
    );
};

/**
 * Copies the specified files and directories to the destination directory.
 */
exports.copy = function () {
    return src([
        templatePath + "/assets/src/**/*",
        "!" + templatePath + "/assets/src/images{,/**}",
        templatePath + "/assets/src/images/**/*.{svg,gif}",
        "!" + templatePath + "/assets/src/scripts{,/**}",
        "!" + templatePath + "/assets/src/sass{,/**}",
    ])
        .pipe(
            dest((file) => {
                if (file.dirname.includes("vendor")) {
                    return templatePath + "/assets/dist/vendor";
                } else if (file.dirname.includes("images")) {
                    return templatePath + "/assets/dist/images";
                }
                return templatePath + "/assets/dist";
            })
        )
        .on("data", (file) => {
            console.log("Copied file:", file.path);
        });
};

/**
 * Runs Composer install/update in the root directory.
 */
exports.runComposer = function (done) {
    if (
        !fs.existsSync("vendor") ||
        fs.statSync("composer.lock").mtime > fs.statSync("vendor").mtime
    ) {
        exec("composer install", (err, stdout, stderr) => {
            console.log(stdout);
            console.error(stderr);
            done(err);
        });
    } else {
        console.log("Vendor is up to date.");
        done();
    }
};

/**
 * Copies composer vendor files from root to template directory.
 */
exports.copyVendor = function () {
    return src("./vendor/**/*")
        .pipe(dest(`${templatePath}/vendor`))
        .on("data", (file) => console.log("Copied file:", file.path));
};

/**
 * Removes all existing files in the dist folder.
 */
exports.clean = function () {
    return del([templatePath + "/assets/dist"]);
};

/**
 * Watches for changes in the specified files and triggers the corresponding tasks.
 */
exports.watchForChanges = function () {
    watch(templatePath + "/assets/src/sass/**/*.scss", exports.styles);
    watch(templatePath + "/assets/src/scripts/**/*.js", exports.scripts);
    watch(
        templatePath + "/assets/src/images/**/*.{jpg,jpeg,png,svg,gif}",
        exports.images
    );
};

// Runs the build task of the project.
exports.build = series(
    exports.clean,
    exports.runComposer,
    parallel(exports.styles, exports.images, exports.scripts, exports.copy)
);

// Runs the dev task of the project, which does the build task and watches for changes.
exports.dev = series(exports.build, exports.watchForChanges);

// Default run is the dev task if nothing is specified.
exports.default = exports.dev;
