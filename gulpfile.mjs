/**
 * External dependencies
 */
import { exec } from 'child_process';
import fs from 'fs';
import path from 'path';
import del from 'del';
import { src, dest, watch, series, parallel } from 'gulp';
import gulpSass from 'gulp-sass';
import * as dartSass from 'sass';
import rename from 'gulp-rename';
import terser from 'gulp-terser';
import cleanCss from 'gulp-clean-css';
import postcss from 'gulp-postcss';
import { generate } from 'critical';
import autoprefixer from 'autoprefixer';
import sharp from 'sharp';

/**
 * Internal dependencies
 */
const sass = gulpSass( dartSass );
const templatePath = '.';

/**
 * Check if directory exists, create if not
 *
 * @param {string} filePath path to check
 * @return {boolean} true if exists or created
 */
export function checkDirectoryExists( filePath ) {
    const dirname = path.dirname( filePath );
    if ( fs.existsSync( dirname ) ) {
        return true;
    }
    fs.mkdirSync( dirname, { recursive: true } );
    console.log( `Created directory: ${ dirname }` );
    return true;
}

/**
 * Generates the styles for the project.
 *
 * @return {Object} gulp stream
 */
export async function styles() {
    const sassSrc = `${ templatePath }/assets/src/sass`;
    const cssDist = `${ templatePath }/assets/dist/css`;

    if ( ! fs.existsSync( sassSrc ) ) {
        console.warn( 'No styles found to process.' );
        return Promise.resolve();
    }

    if ( ! fs.existsSync( cssDist ) ) {
        fs.mkdirSync( cssDist, { recursive: true } );
        console.log( `Created directory: ${ cssDist }` );
    }

    // process already minified files (including subfolders)
    const minified = src( `${ sassSrc }/**/*.css` )
        .pipe( dest( cssDist ) )
        .on( 'data', ( file ) => {
            console.log( 'Processed minified file:', file.path );
        } );

    // process files that need minification (including subfolders)
    const toMinify = src( [ `${ templatePath }/assets/src/sass/*.scss` ] )
        .pipe( sass().on( 'error', sass.logError ) )
        .pipe( postcss( [ autoprefixer() ] ) )
        .pipe(
            cleanCss( {
                compatibility: '*',
                level: {
                    1: {
                        specialComments: 0,
                    },
                },
            } )
        )
        .pipe( dest( cssDist ) );

    return Promise.all(
        [ minified, toMinify ].map(
            ( stream ) =>
                new Promise( ( resolve, reject ) => {
                    stream.on( 'end', resolve ).on( 'error', reject );
                } )
        )
    );
}

/**
 * Generates a list of image files and performs various optimization operations on them.
 *
 * @param {Function} callback gulp callback
 * @return {Object} gulp stream
 */
export async function images( callback ) {
    const imageSrc = `${ templatePath }/assets/src/images`;
    const imageDist = `${ templatePath }/assets/dist/images`;
    const imageFiles = `${ imageSrc }/**/*.{jpg,jpeg,png,webp}`;

    return src( imageFiles )
        .on( 'data', ( file ) => {
            const extension = path.extname( file.path ).toLowerCase();
            let pipeline = sharp( file.path );

            switch ( extension ) {
                case '.jpg':
                case '.jpeg':
                    pipeline = pipeline.jpeg( {
                        mozjpeg: true,
                        quality: 40,
                    } );
                    break;

                case '.png':
                    pipeline = pipeline.png( {
                        compressionLevel: 9,
                        adaptiveFiltering: true,
                        quality: 60,
                        palette: true,
                    } );
                    break;

                case '.webp':
                    pipeline = pipeline.webp( {
                        quality: 40,
                    } );
                    break;

                default:
                    console.warn( `Unsupported file type: ${ file.path }` );
                    return;
            }

            const relativePath = path.relative( file.base, file.path );
            const outputFilePath = path.join( imageDist, relativePath );

            checkDirectoryExists( outputFilePath );

            pipeline
                .toFile( outputFilePath )
                .then( () => {
                    console.log( 'Processed image:', file.path );
                } )
                .catch( ( err ) => {
                    console.error(
                        'Error during image processing:',
                        err.message
                    );
                } );
        } )
        .on( 'error', ( err ) => {
            console.error( 'Error during image processing:', err.message );
            callback( err );
        } )
        .on( 'end', () => {
            console.log( 'Images processed successfully.' );
            callback();
        } );
}

/**
 * Concatenates and minifies JavaScript files.
 * Processes files in subfolders while maintaining directory structure.
 *
 * @return {Promise} promise that resolves when all scripts are processed
 */
export async function scripts() {
    const scriptSrc = `${ templatePath }/assets/src/scripts`;
    const scriptDist = `${ templatePath }/assets/dist/scripts`;

    if ( ! fs.existsSync( scriptSrc ) ) {
        console.warn( 'no scripts found to process.' );
        return Promise.resolve();
    }

    if ( ! fs.existsSync( scriptDist ) ) {
        fs.mkdirSync( scriptDist, { recursive: true } );
        console.log( `created directory: ${ scriptDist }` );
    }

    // process already minified files (including subfolders)
    const minified = src( `${ scriptSrc }/**/*.min.js` )
        .pipe( dest( scriptDist ) )
        .on( 'data', ( file ) => {
            console.log( 'processed minified file:', file.path );
        } );

    // process files that need minification (including subfolders)
    const toMinify = src( [ `${ scriptSrc }/**/*.js`, `!${ scriptSrc }/**/*.min.js` ] )
        .pipe(
            terser( {
                format: {
                    comments: false,
                },
                compress: {
                    drop_console: false,
                    passes: 1,
                },
                mangle: {
                    reserved: [ '$', 'jQuery', 'window', 'document' ],
                },
            } )
        )
        .on( 'error', function( err ) {
            console.error( 'terser error:', err.message );
            console.error( 'file:', err.filename || 'unknown' );
            this.emit( 'end' );
        } )
        .pipe(
            rename( ( path ) => {
                path.extname = '.min.js';
            } )
        )
        .pipe( dest( scriptDist ) );

    return Promise.all(
        [ minified, toMinify ].map(
            ( stream ) =>
                new Promise( ( resolve, reject ) => {
                    stream.on( 'end', resolve ).on( 'error', reject );
                } )
        )
    );
}

/**
 * Generates a Sass variable file with colors from a JSON file.
 */
export const generateColorsVarSass = () => {
    return src("./includes/colors.json")
        .pipe(jsonCssVar())
        .pipe(concat("_colors.scss"))
        .pipe(dest("assets/src/sass/configs"));
}

/**
 * Generates a SASS file with colors based on a JSON file.
 *
 * @param {Function} callback - Optional callback function to be called after the SASS file is generated.
 * @return {void}
 */
export const generateColorsSass = (callback) => {
    const colorsFilePath = "./includes/colors.json";
    const sassFilePath = "./assets/src/sass/configs/_dynamic_colors.scss";

    try {
        const colors = JSON.parse(fs.readFileSync(colorsFilePath, "utf8"));

        let sassOutput = ".wp-block-post-content, .widget_block {";

        for (const [colorName] of Object.entries(colors)) {
            const formattedColorName = colorName.replace(/--/g, "-");

            sassOutput += `
                .has-${formattedColorName}-color {
                    color: var(--${colorName}) !important;
                }

                .has-${formattedColorName}-background-color {
                    background-color: var(--${colorName}) !important;
                }

                .is-style-outline {
                    .wp-block-button__link {
                        &.has-${formattedColorName}-color {
                            color: var(--${colorName}) !important;
                            border-color: var(--${colorName}) !important;
                        }
                    }
                }
                    `;
        }

        sassOutput += "}";

        fs.writeFileSync(sassFilePath, sassOutput);

        if (callback) {
            callback(null, true);
        }
    } catch (error) {
        if (callback) {
            callback(error);
        }
    }
}

/**
 * Generates critical inline CSS for a list of URLs.
 *
 * @return {Promise} A promise that resolves when all critical inline CSS files are generated.
 */
export const criticalInlineCss = () => {
    const criticalUrls = JSON.parse(
        fs.readFileSync("./assets/src/criticalcss-pagelist.json", "utf-8")
    );

    const promises = criticalUrls.urls.map((item) =>
        generate({
            src: item.link,
            base: "./assets/dist/css/",
            css: ["./assets/dist/css/frontpage-main.css"],
            width: 1680,
            height: 953,
            target: {
                css: "./assets/dist/css/" + item.name + "-inline.css",
            },
            inline: false,
            request: {
                https: {
                    rejectUnauthorized: false,
                },
            },
            penthouse: {
                propertiesToRemove: ["@font-face", /url\(/],
                puppeteer: {
                    ignoreHTTPSErrors: true,
                },
            },
        })
            .catch((err) => {
                console.error(`Error generating critical CSS for ${item.link}:`, err);
            })
    );

    return Promise.all(promises);
}

/**
 * Copies the specified files and directories to the destination directory.
 *
 * @return {Object} gulp stream
 */
export function copy() {
    return src(
        [
            templatePath + '/assets/src/**/*',
            '!' + templatePath + '/assets/src/images/**/*.{jpg,jpeg,png,webp}',
            '!' + templatePath + '/assets/src/scripts{,/**}',
            '!' + templatePath + '/assets/src/sass{,/**}',
        ],
        { base: templatePath + '/assets/src' }
    )
        .pipe( dest( templatePath + '/assets/dist' ) )
        .on( 'data', ( file ) => {
            console.log( 'Copied file:', file.path );
        } );
}

/**
 * Runs Composer install/update in the root directory.
 *
 * @param {Function} done gulp callback
 * @return {void}
 */
export function runComposer( done ) {
    if (
        ! fs.existsSync( 'vendor' ) ||
        fs.statSync( 'composer.lock' ).mtime > fs.statSync( 'vendor' ).mtime
    ) {
        exec( 'composer install', ( err, stdout, stderr ) => {
            console.log( stdout );
            console.error( stderr );
            done( err );
        } );
    } else {
        console.log( 'Vendor is up to date.' );
        done();
    }
}

/**
 * Removes all existing files in the dist folder.
 *
 * @return {Promise} promise that resolves when files are deleted
 */
export function clean() {
    return del( [ templatePath + '/assets/dist' ] );
}

/**
 * Creates the dist directory structure
 *
 * @return {Promise} promise that resolves when directories are created
 */
export async function createDistDirs() {
    const dirs = [
        `${ templatePath }/assets/dist`,
        `${ templatePath }/assets/dist/css`,
        `${ templatePath }/assets/dist/scripts`,
        `${ templatePath }/assets/dist/images`,
    ];

    try {
        for ( const dir of dirs ) {
            if ( ! fs.existsSync( dir ) ) {
                fs.mkdirSync( dir, { recursive: true } );
                console.log( `Created directory: ${ dir }` );
            }
        }
    } catch ( error ) {
        console.error( `Error creating directories: ${ error.message }` );
        throw error;
    }
}

/**
 * Watches for changes in the specified files and triggers the corresponding tasks.
 *
 * @return {void}
 */
export function watchForChanges() {
    const sassPath = `${ templatePath }/assets/src/sass/**/*.scss`;
    const scriptsPath = `${ templatePath }/assets/src/scripts/**/*.js`;
    const imagesPath = `${ templatePath }/assets/src/images/**/*.{jpg,jpeg,png,svg,gif}`;

    // only watch directories that exist
    if ( fs.existsSync( `${ templatePath }/assets/src/sass` ) ) {
        watch( sassPath, styles );
        console.log( 'Watching SASS files...' );
    }

    if ( fs.existsSync( `${ templatePath }/assets/src/scripts` ) ) {
        watch( scriptsPath, scripts );
        console.log( 'Watching script files...' );
    }

    if ( fs.existsSync( `${ templatePath }/assets/src/images` ) ) {
        watch( imagesPath, images );
        console.log( 'Watching image files...' );
    }

    console.log( 'File watching started. Press Ctrl+C to stop.' );
}

/**
 * Build tasks dynamically based on what directories exist
 *
 * @return {Array} array of gulp tasks
 */
export function getBuildTasks() {
    const tasks = [ clean, createDistDirs, runComposer ];
    const parallelTasks = [];

    // only add tasks for directories that exist
    if ( fs.existsSync( `${ templatePath }/assets/src/sass` ) ) {
        parallelTasks.push( styles );
    } else {
        console.warn( 'No sass directory found. Skipping styles task.' );
    }

    if ( fs.existsSync( `${ templatePath }/assets/src/images` ) ) {
        parallelTasks.push( images );
    } else {
        console.warn( 'No images directory found. Skipping images task.' );
    }

    if ( fs.existsSync( `${ templatePath }/assets/src/scripts` ) ) {
        parallelTasks.push( scripts );
    } else {
        console.warn( 'No scripts directory found. Skipping scripts task.' );
    }

    if ( fs.existsSync( `${ templatePath }/assets/src` ) ) {
        parallelTasks.push( copy );
    } else {
        console.warn( 'No src directory found. Skipping copy task.' );
    }

    // add parallel tasks if any exist
    if ( parallelTasks.length > 0 ) {
        tasks.push( parallel( ...parallelTasks ) );
    }

    return tasks;
}

// runs the build task of the project.
export const build = series( ...getBuildTasks() );

// runs the dev task of the project, which does the build task and watches for changes.
export const dev = series( build, watchForChanges );

// runs critical css generation
export const buildInline = criticalInlineCss;

// default run is the dev task if nothing is specified.
export default dev;
