/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/facade.js/facade.js":
/*!******************************************!*\
  !*** ./node_modules/facade.js/facade.js ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/*!
 * Facade.js v0.3.0-beta
 * https://github.com/facadejs/facade.js
 *
 * Copyright (c) 2014 Scott Doxey
 * Released under the MIT license
 */

(function (window, document, undefined) {

    'use strict';

    var _requestAnimationFrame,
        _cancelAnimationFrame,
        _context,
        _contextProperties = [ 'fillStyle', 'font', 'globalAlpha', 'globalCompositeOperation', 'lineCap', 'lineJoin', 'lineWidth', 'miterLimit', 'shadowBlur', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY', 'strokeStyle', 'textAlign', 'textBaseline' ],
        _TO_RADIANS = Math.PI / 180,
        _OPERATOR_TEST = new RegExp('^([-+])=');

    if (String(typeof window) !== 'undefined') {

        _context = document.createElement('canvas').getContext('2d');

        /*!
         * requestAnimationFrame Support
         */

        ['webkit', 'moz'].forEach(function (key) {
            _requestAnimationFrame = _requestAnimationFrame || window.requestAnimationFrame || window[key + 'RequestAnimationFrame'] || null;
            _cancelAnimationFrame = _cancelAnimationFrame || window.cancelAnimationFrame || window[key + 'CancelAnimationFrame'] || null;
        });

    }

    /**
     * Creates a new Facade.js object with either a preexisting canvas tag or a unique name, width, and height.
     *
     *     var stage = new Facade(document.querySelector('canvas'));
     *     var stage = new Facade('stage', 500, 300);
     *
     * @property {Object} canvas Reference to the canvas element.
     * @property {Object} context Reference to the <a href="https://developer.mozilla.org/en-US/docs/Web/API/CanvasRenderingContext2D" target="_blank">CanvasRenderingContext2D</a> object.
     * @property {Integer} dt Current time in milliseconds since last canvas draw.
     * @property {Integer} fps Current frames per second.
     * @property {Integer} ftime Time of last canvas draw.
     * @param {Object|String} [canvas] Reference to an HTML canvas element or a unique name.
     * @param {Integer} [width] Width of the canvas.
     * @param {Integer} [height] Height of the canvas.
     * @return {Object} New Facade.js object.
     * @public
     */

    function Facade(canvas, width, height) {

        if (!(this instanceof Facade)) {

            return new Facade(canvas, width, height);

        }

        this.dt = null;
        this.fps = null;
        this.ftime = null;

        this._callback = null;

        this._requestAnimation = null;

        this._width = null;
        this._height = null;

        if (canvas && typeof canvas === 'object' && canvas.nodeType === 1) {

            this.canvas = canvas;

        } else {

            this.canvas = document.createElement('canvas');

            if (typeof canvas === 'string') {

                this.canvas.setAttribute('id', canvas);

            }

        }

        if (width) {

            this.width(width);

        } else if (this.canvas.hasAttribute('width')) {

            this._width = parseInt(this.canvas.getAttribute('width'), 10);

        } else {

            this.width(this.canvas.clientWidth);

        }

        if (height) {

            this.height(height);

        } else if (this.canvas.hasAttribute('height')) {

            this._height = parseInt(this.canvas.getAttribute('height'), 10);

        } else {

            this.height(this.canvas.clientHeight);

        }

        try {

            this.context = this.canvas.getContext('2d');

        } catch (e) {

            console.error('Object passed to Facade.js was not a valid canvas element.');

        }

    }

    /**
     * Draws a Facade.js entity (or multiple entities) to the stage.
     *
     *     stage.addToStage(circle);
     *     stage.addToStage(circle, { x: 100, y: 100 });
     *
     * @param {Object|Array} obj Facade.js entity or an array of entities.
     * @param {Object} options Temporary options for rendering a Facade.js entity (or multiple entities).
     * @return {Object} Facade.js object.
     * @public
     */

    Facade.prototype.addToStage = function (obj, options) {

        var i,
            length;

        if (obj instanceof Facade.Entity) {

            obj.draw(this, options);

        } else if (Array.isArray(obj)) {

            for (i = 0, length = obj.length; i < length; i += 1) {

                this.addToStage(obj[i], options);

            }

        } else {

            console.error('Object passed to Facade.addToStage is not a valid Facade.js entity.');

        }

        return this;

    };

    /**
     * Clears the canvas.
     *
     *      stage.clear();
     *
     * @return {Object} Facade.js object.
     * @public
     */

    Facade.prototype.clear = function () {

        this.context.clearRect(0, 0, this.width(), this.height());

        return this;

    };

    /**
     * Sets a callback function to run in a loop using <a href="https://developer.mozilla.org/en-US/docs/Web/API/window.requestAnimationFrame" target="_blank">requestAnimationFrame</a> or available polyfill.
     *
     *     stage.draw(function () {
     *
     *         this.clear();
     *
     *         this.addToStage(circle, { x: 100, y: 100 });
     *
     *     });
     *
     * @param {Function} callback Function callback.
     * @return {Object} Facade.js object.
     * @public
     */

    Facade.prototype.draw = function (callback) {

        if (typeof callback === 'function') {

            this._callback = callback;

            this.start();

        } else {

            console.error('Parameter passed to Facade.draw is not a valid function.');

        }

        return this;

    };

    /**
     * Exports a base64 encoded representation of the current rendered canvas.
     *
     *     console.log(stage.exportBase64('image/png', 100));
     *
     * @param {String} [type] Image format: <code>image/png</code> (Default), <code>image/jpeg</code>, <code>image/webp</code> (Google Chrome Only)
     * @param {Integer} [quality] Number between 0 and 100.
     * @return {String} Base64 encoded string.
     * @public
     */

    Facade.prototype.exportBase64 = function (type, quality) {

        if (!type) {

            type = 'image/png';

        }

        if (typeof quality === 'number') {

            quality = quality / 100;

        } else {

            quality = 1;

        }

        return this.canvas.toDataURL(type, quality);

    };

    /**
     * Gets and sets the canvas height.
     *
     *     console.log(stage.height()); // 300
     *     console.log(stage.height(600)); // 600
     *
     * @param {Integer} [height] Height in pixels.
     * @return {Integer} Height in pixels.
     * @public
     */

    Facade.prototype.height = function (height) {

        if (height) {

            this._height = parseInt(height, 10);

            if (this.canvas.hasAttribute('data-resized-for-hdpi')) {

                this.resizeForHDPI();

            } else {

                this.canvas.setAttribute('height', this._height);

            }

        }

        return this._height;

    };

    /**
     * Applies key-value pairs to appropriate <a href="https://developer.mozilla.org/en-US/docs/Web/API/CanvasRenderingContext2D" target="_blank">CanvasRenderingContext2D</a> properties and methods.
     *
     *     stage.renderWithContext({ fillStyle: '#f00', globalAlpha: 0.5, fillRect: [ 0, 0, 100, 100 ]});
     *
     * @param {Object} options Object containing context property and/or method names with corresponding values.
     * @param {Function} [callback] Function to be called when context options have been rendered to the canvas.
     * @return {void}
     * @public
     */

    Facade.prototype.renderWithContext = function (options, callback) {

        var keys = Object.keys(options),
            i,
            length;

        this.context.save();

        for (i = 0, length = keys.length; i < length; i += 1) {

            if (_contextProperties.indexOf(keys[i]) !== -1) {

                this.context[keys[i]] = options[keys[i]];

            } else if (Array.isArray(options[keys[i]]) && typeof this.context[keys[i]] === 'function') {

                this.context[keys[i]].apply(this.context, options[keys[i]]);

            }

        }

        if (callback) {

            callback.call(null, this);

        }

        this.context.restore();

    };

    /**
     * Resizes the canvas width and height to be multiplied by the pixel ratio of the device to allow for sub-pixel aliasing. Canvas tag maintains original width and height through CSS. Must be called before creating/adding any Facade entities as scaling is applied to the canvas context.
     *
     *     stage.resizeForHDPI();
     *     stage.resizeForHDPI(2);
     *
     * @param {Integer} [ratio] Ratio to scale the canvas.
     * @return {Object} Facade.js object.
     * @public
     */

    Facade.prototype.resizeForHDPI = function (ratio) {

        if (ratio === undefined) {

            ratio = window.devicePixelRatio;

        } else {

            ratio = parseFloat(ratio);

        }

        if (ratio > 1) {

            this.canvas.setAttribute('style', 'width: ' + this.width() + 'px; height: ' + this.height() + 'px;');

            this.canvas.setAttribute('width', this.width() * ratio);
            this.canvas.setAttribute('height', this.height() * ratio);

            this.context.scale(ratio, ratio);

            this.canvas.setAttribute('data-resized-for-hdpi', true);

        }

        return this;

    };

    /**
     * Starts the callback supplied in <code>Facade.draw</code>.
     *
     *     stage.start();
     *
     * @return {Object} Facade.js object.
     * @public
     */

    Facade.prototype.start = function () {

        this._requestAnimation = _requestAnimationFrame(this._animate.bind(this));

        return this;

    };

    /**
     * Stops the callback supplied in <code>Facade.draw</code>.
     *
     *     stage.stop();
     *
     * @return {Object} Facade.js object.
     * @public
     */

    Facade.prototype.stop = function () {

        this.dt = null;
        this.fps = null;
        this.ftime = null;

        _cancelAnimationFrame(this._requestAnimation);

        this._requestAnimation = null;

        return this;

    };

    /**
     * Gets and sets the canvas width.
     *
     *     console.log(stage.width()); // 400
     *     console.log(stage.width(800)); // 800
     *
     * @param {Integer} [width] Width in pixels.
     * @return {Integer} Width in pixels.
     * @public
     */

    Facade.prototype.width = function (width) {

        if (width) {

            this._width = parseInt(width, 10);

            if (this.canvas.hasAttribute('data-resized-for-hdpi')) {

                this.resizeForHDPI();

            } else {

                this.canvas.setAttribute('width', this._width);

            }

        }

        return this._width;

    };

    /**
     * Method called by <a href="https://developer.mozilla.org/en-US/docs/Web/API/window.requestAnimationFrame" target="_blank">requestAnimationFrame</a>. Sets <code>Facade.dt</code>, <code>Facade.fps</code> and  <code>Facade.ftime</code>.
     *
     *     this._requestAnimation = _requestAnimationFrame(this._animate.bind(this));
     *
     * @param {Integer} time <a href="https://developer.mozilla.org/en-US/docs/Web/API/DOMTimeStamp" target="_blank">DOMTimeStamp</a> or <a href="https://developer.mozilla.org/en-US/docs/Web/API/DOMHighResTimeStamp" target="_blank">DOMHighResTimeStamp</a> (Google Chrome Only)
     * @return {Object} Facade.js object.
     * @private
     */

    Facade.prototype._animate = function (time) {

        if (typeof this._callback === 'function') {

            if (this.ftime) {

                this.dt = time - this.ftime;

                this.fps = (1000 / this.dt).toFixed(2);

            }

            this.ftime = time;

            this._requestAnimation = _requestAnimationFrame(this._animate.bind(this));

            this.context.save();

            this._callback();

            this.context.restore();

        } else {

            this.stop();

        }

        return this;

    };

    /**
     * The constructor for all Facade.js shape, image and text objects.
     *
     * @return {Object} New Facade.Entity object.
     * @public
     */

    Facade.Entity = function (options) {

        if (!(this instanceof Facade.Entity)) {

            return new Facade.Entity();

        }

        this._options = this._defaultOptions();
        this._metrics = this._defaultMetrics();

        this.setOptions(options);

    };

    /**
     * Returns a default set of options common to all Facade.js entities.
     *
     *     console.log(Facade.Entity.prototype._defaultOptions());
     *     console.log(Facade.Entity.prototype._defaultOptions({ lineWidth: 0 }));
     *
     * @param {Object} updated Additional options as key-value pairs.
     * @return {Object} Default set of options.
     * @private
     */

    Facade.Entity.prototype._defaultOptions = function (updated) {

        var options,
            keys,
            i,
            length;

        options = {
            x: 0,
            y: 0,
            anchor: 'top/left',
            rotate: 0,
            scale: 1
        };

        if (typeof updated === 'object') {

            keys = Object.keys(updated);

            for (i = 0, length = keys.length; i < length; i += 1) {

                options[keys[i]] = updated[keys[i]];

            }

        }

        return options;

    };

    /**
     * Returns a default set of metrics common to all Facade.js entities.
     *
     *     console.log(Facade.Entity.prototype._defaultMetrics());
     *     console.log(Facade.Entity.prototype._defaultMetrics({ scale: null }));
     *
     * @param {Object} updated Additional metrics as key-value pairs.
     * @return {Object} Default set of metrics.
     * @private
     */

    Facade.Entity.prototype._defaultMetrics = function (updated) {

        var metrics,
            keys,
            i,
            length;

        metrics = {
            x: null,
            y: null,
            width: null,
            height: null
        };

        if (typeof updated === 'object') {

            keys = Object.keys(updated);

            for (i = 0, length = keys.length; i < length; i += 1) {

                metrics[keys[i]] = updated[keys[i]];

            }

        }

        return metrics;

    };

    /**
     * Returns an array of the x and y anchor positions based on given options and metrics.
     *
     *     console.log(rect._getAnchorPoint(options, metrics));
     *
     * @param {Object} options Facade.Entity options.
     * @param {Object} metrics Facade.Entity metrics.
     * @return {Array} Array with the x and y anchor positions.
     * @private
     */

    Facade.Entity.prototype._getAnchorPoint = function (options, metrics) {

        var pos = [0, 0],
            strokeWidthOffset;

        if (options.anchor.match(/center$/)) {

            pos[0] = -metrics.width / 2;

        } else if (options.anchor.match(/right$/)) {

            pos[0] = -metrics.width;

        }

        if (options.anchor.match(/^center/)) {

            pos[1] = -metrics.height / 2;

        } else if (options.anchor.match(/^bottom/)) {

            pos[1] = -metrics.height;

        }

        if (this instanceof Facade.Polygon) {

            strokeWidthOffset = this._getStrokeWidthOffset(options);

            pos[0] = pos[0] + strokeWidthOffset;
            pos[1] = pos[1] + strokeWidthOffset;

        }

        return pos;

    };

    /**
     * Returns an integer for the stroke width offset. Used to calculate metrics.
     *
     *     console.log(rect._getStrokeWidthOffset(options));
     *
     * @param {Object} options Facade.Entity options.
     * @return {Integer} Integer representing the stroke width offset.
     * @private
     */

    Facade.Entity.prototype._getStrokeWidthOffset = function (options) {

        var strokeWidthOffset = 0;

        if (options.lineWidth !== undefined) {

            strokeWidthOffset = options.lineWidth / 2;

        }

        return strokeWidthOffset;

    };

    /**
     * Applies transforms (translate, rotate and scale) to an entity.
     *
     *     console.log(rect._applyTransforms(context, options, metrics));
     *
     * @param {Object} context Reference to the <a href="https://developer.mozilla.org/en-US/docs/Web/API/CanvasRenderingContext2D" target="_blank">CanvasRenderingContext2D</a> object.
     * @param {Object} options Facade.Entity options.
     * @param {Object} metrics Facade.Entity metrics.
     * @return {void}
     * @private
     */

    Facade.Entity.prototype._applyTransforms = function (context, options, metrics) {

        var anchor = this._getAnchorPoint(options, {
            x: metrics.x,
            y: metrics.y,
            width: metrics.width / options.scale,
            height: metrics.height / options.scale
        });

        context.translate.apply(context, anchor);

        if (options.rotate) {

            context.translate(-anchor[0], -anchor[1]);
            context.rotate(options.rotate * _TO_RADIANS);
            context.translate(anchor[0], anchor[1]);

        }

        if (options.scale !== 1) {

            context.translate(-anchor[0], -anchor[1]);
            context.scale(options.scale, options.scale);
            context.translate(anchor[0], anchor[1]);

        }

    };

    /**
     * Retrieves the value of a given option. Only retrieves options set when creating a new Facade.js entity or using <a href="#facade.entity.prototype.setoptions"><code>setOptions</code></a> not through temporary options set when using <a href="#facade.addtostage"><code>Facade.addToStage</code></a>.
     *
     *     console.log(text.getOption('value'));
     *
     * @param {String} key The name of the option.
     * @return {Object|Function|String|Integer} Value of the option requested.
     * @public
     */

    Facade.Entity.prototype.getOption = function (key) {

        if (this._options[key] !== undefined) {

            return this._options[key];

        }

        return undefined;

    };

    /**
     * Retrieves the value of all options. Only retrieves options set when creating a new Facade.js entity or using <a href="#facade.entity.prototype.setoptions"><code>setOptions</code></a> not through temporary options set when using <a href="#facade.addtostage"><code>Facade.addToStage</code></a>.
     *
     *     console.log(text.getAllOptions());
     *
     * @return {Object} Object containing all options.
     * @public
     */

    Facade.Entity.prototype.getAllOptions = function () {

        var options = {},
            keys = Object.keys(this._options),
            i,
            length;

        for (i = 0, length = keys.length; i < length; i += 1) {

            options[keys[i]] = this._options[keys[i]];

        }

        return options;

    };

    /**
     * Sets an option for a given entity.
     *
     *     console.log(text._setOption('value', 'Hello world!'));
     *
     * @param {String} key The option to update.
     * @param {Object|Function|String|Integer} value The new value of the specified option.
     * @param {Boolean} test Flag to determine if options are to be persisted in the entity or just returned.
     * @return {Object|Function|String|Integer} Returns value of the updated option.
     * @private
     */

    Facade.Entity.prototype._setOption = function (key, value, test) {

        var results;

        if (this._options[key] !== undefined) {

            if (typeof this._options[key] === 'number') {

                if (typeof value === 'string') {

                    results = value.match(_OPERATOR_TEST);

                    if (results) {

                        value = parseFloat(value.replace(_OPERATOR_TEST, ''));

                        if (results[1] === '+') {

                            value = this._options[key] + value;

                        } else if (results[1] === '-') {

                            value = this._options[key] - value;

                        }

                    } else {

                        value = parseFloat(value);

                    }

                }

                if (isNaN(value)) {

                    value = this._options[key];

                    console.error('The value for ' + key + ' was not a valid number.');

                }

            }

            if (String(typeof this._options[key]) === String(typeof value)) {

                if (!test) {

                    this._options[key] = value;

                }

            } else {

                console.error('The value for ' + key + ' (' + value + ') was a ' + String(typeof value) + ' not a ' + String(typeof this._options[key]) + '.');

            }

            return value;

        }

        return undefined;

    };

    /**
     * Sets a group of options as key-value pairs to an entity.
     *
     *     console.log(text.setOptions({ fontFamily: 'Georgia', fontSize: 20 }));
     *
     * @param {Object} [updated] The options to update. Does not need to be entire set of options.
     * @param {Boolean} [test] Flag to determine if options are to be persisted in the entity or just returned.
     * @return {Object} Updated options.
     * @public
     */

    Facade.Entity.prototype.setOptions = function (updated, test) {

        var options = this.getAllOptions(),
            keys,
            i,
            length;

        if (updated) {

            keys = Object.keys(updated);

            for (i = 0, length = keys.length; i < length; i += 1) {

                if (options[keys[i]] !== undefined) {

                    options[keys[i]] = this._setOption(keys[i], updated[keys[i]], test);

                }

            }

            if (!test) {

                this._setMetrics();

            }

        }

        return options;

    };

    /**
     * Retrieves the value of a given metric. Only retrieves metrics set when creating a new Facade.js entity or using <a href="#facade.entity.prototype.setoptions"><code>setOptions</code></a> not through temporary options set when using <a href="#facade.addtostage"><code>Facade.addToStage</code></a>.
     *
     *     console.log(text.getMetric('width'));
     *
     * @param {String} key The name of the metric.
     * @return {Integer} Value of the metric requested.
     * @public
     */

    Facade.Entity.prototype.getMetric = function (key) {

        if (this._metrics[key] !== undefined) {

            return this._metrics[key];

        }

        return undefined;

    };

    /**
     * Retrieves the value of all metrics. Only retrieves metrics set when creating a new Facade.js entity or using <a href="#facade.entity.prototype.setoptions"><code>setOptions</code></a> not through temporary options set when using <a href="#facade.addtostage"><code>Facade.addToStage</code></a>.
     *
     *     console.log(text.getAllMetrics());
     *
     * @return {Object} Object containing all metrics.
     * @public
     */

    Facade.Entity.prototype.getAllMetrics = function () {

        var metrics = {},
            keys = Object.keys(this._metrics),
            i,
            length;

        for (i = 0, length = keys.length; i < length; i += 1) {

            metrics[keys[i]] = this._metrics[keys[i]];

        }

        return metrics;

    };

    /**
     * Renders an entity to a canvas.
     *
     *     entity.draw(stage);
     *     entity.draw(stage, { x: 100, y: 100 });
     *
     * @param {Object} facade Facade.js object.
     * @param {Object} updated Temporary options for rendering a Facade.js entity.
     * @return {void}
     * @public
     */

    Facade.Entity.prototype.draw = function (facade, updated) {

        var options = this.setOptions(updated, true),
            metrics;

        if (typeof this._configOptions === 'function') {

            options = this._configOptions(options);

        }

        metrics = updated ? this._setMetrics(options) : this.getAllMetrics();

        if (typeof this._draw === 'function') {

            facade.renderWithContext(options, this._draw.bind(this, facade, options, metrics));

        }

    };

    /**
     * Create a polygon object. Inherits all methods from <b>Facade.Entity</b>.
     *
     *     var polygon = new Facade.Polygon({
     *         x: 0,
     *         y: 0,
     *         points: [ [100, 0], [200, 100], [100, 200], [0, 100] ],
     *         lineWidth: 10,
     *         strokeStyle: '#333E4B',
     *         fillStyle: '#1C73A8',
     *         anchor: 'top/left'
     *     });
     *
     * @param {Object} [options] Options to create the polygon with.
     * @param {Integer} [options.x] X coordinate to position the polygon. <i>Default:</i> 0
     * @param {Integer} [options.y] Y coordinate to position the polygon. <i>Default:</i> 0
     * @param {String} [options.anchor] Position to anchor the polygon. <i>Default:</i> "top/left"<br><ul><li>top/left</li><li>top/center</li><li>top/right</li><li>center/left</li><li>center</li><li>center/right</li><li>bottom/left</li><li>bottom/center</li><li>bottom/right</li></ul>
     * @param {Integer} [options.rotate] Degrees to rotate the polygon. <i>Default:</i> 0
     * @param {Integer} [options.scale] A float representing the scale of a polygon. <i>Default:</i> 1
     * @param {Integer} [options.opacity] Opacity of the polygon. Integer between 0 and 100. <i>Default:</i> 100
     * @param {Array} [options.points] Multi-dimensional array of points used to render a polygon. Point arrays with 2 values is rendered as a line, 5 values is rendered as an arc and 6 values is rendered as a bezier curve.
     * @param {String} [options.fillStyle] Fill color for the polygon. Can be a text representation of a color, HEX, RGB(a), HSL(a). <i>Default:</i> "#000"<br><ul><li>HTML Colors: red, green, blue, etc.</li><li>HEX: #f00, #ff0000</li><li>RGB(a): rgb(255, 0, 0), rgba(0, 255, 0, 0.5)</li><li>HSL(a): hsl(100, 100%, 50%), hsla(100, 100%, 50%, 0.5)</li></ul>
     * @param {String} [options.strokeStyle] Color of a polygon's stroke. Can be a text representation of a color, HEX, RGB(a), HSL(a). <i>Default:</i> "#000"<br><ul><li>HTML Colors: red, green, blue, etc.</li><li>HEX: #f00, #ff0000</li><li>RGB(a): rgb(255, 0, 0), rgba(0, 255, 0, 0.5)</li><li>HSL(a): hsl(100, 100%, 50%), hsla(100, 100%, 50%, 0.5)</li></ul>
     * @param {Integer} [options.lineWidth] Width of the stroke. <i>Default:</i> 0
     * @param {String} [options.lineCap] The style of line cap. <i>Default:</i> "butt"<br><ul><li>butt</li><li>round</li><li>square</li></ul>
     * @param {String} [options.lineJoin] The style of line join. <i>Default:</i> "miter"<br><ul><li>miter</li><li>round</li><li>bevel</li></ul>
     * @param {Boolean} [options.closePath] Boolean to determine if the polygon should be self closing or not. <i>Default:</i> true
     * @return {Object} New Facade.Polygon object.
     * @public
     */

    Facade.Polygon = function (options) {

        if (!(this instanceof Facade.Polygon)) {

            return new Facade.Polygon(options);

        }

        this._options = this._defaultOptions();
        this._metrics = this._defaultMetrics();

        this.setOptions(options);

    };

    /*!
     * Extend from Facade.Entity
     */

    Facade.Polygon.prototype = Object.create(Facade.Entity.prototype);
    Facade.Polygon.constructor = Facade.Entity;

    /**
     * Returns a default set of options common to all Facade.js polygon entities.
     *
     *     console.log(Facade.Polygon.prototype._defaultOptions());
     *
     * @param {Object} updated Additional options as key-value pairs.
     * @return {Object} Default set of options.
     * @private
     */

    Facade.Polygon.prototype._defaultOptions = function (updated) {

        var options,
            keys,
            i,
            length;

        options = Facade.Entity.prototype._defaultOptions({
            opacity: 100,
            points: [],
            fillStyle: '#000',
            strokeStyle: '',
            lineWidth: 0,
            lineCap: 'butt',
            lineJoin: 'miter',
            closePath: true
        });

        if (updated) {

            keys = Object.keys(updated);

            for (i = 0, length = keys.length; i < length; i += 1) {

                options[keys[i]] = updated[keys[i]];

            }

        }

        return options;

    };

    /**
     * Renders a polygon entity to a canvas.
     *
     *     polygon._draw(facade, options, metrics);
     *
     * @param {Object} facade Facade.js object.
     * @param {Object} options Options used to render the polygon.
     * @param {Object} metrics Metrics used to render the polygon.
     * @return {void}
     * @private
     */

    Facade.Polygon.prototype._draw = function (facade, options, metrics) {

        var context = facade.context,
            i,
            length;

        this._applyTransforms(context, options, metrics);

        if (options.points.length) {

            context.beginPath();

            for (i = 0, length = options.points.length; i < length; i += 1) {

                if (options.points[i].length === 6) {

                    context.bezierCurveTo.apply(context, options.points[i]);

                } else if (options.points[i].length === 5) {

                    context.arc.apply(context, options.points[i]);

                } else if (options.points[i].length === 2) {

                    context.lineTo.apply(context, options.points[i]);

                }

            }

            if (options.closePath) {

                context.closePath();

            } else {

                context.moveTo.apply(context, options.points[length - 1]);

            }

            if (options.fillStyle) {

                context.fill();

            }

            if (options.lineWidth > 0) {

                context.stroke();

            }

        }

    };

    /**
     * Custom configuration for options specific to a polygon entity.
     *
     *     console.log(polygon._configOptions(options));
     *
     * @param {Object} options Complete set of polygon specific options.
     * @return {Object} Converted options.
     * @private
     */

    Facade.Polygon.prototype._configOptions = function (options) {

        options.translate = [ options.x, options.y ];
        options.globalAlpha = options.opacity / 100;

        return options;

    };

    /**
     * Set metrics based on the polygon's options.
     *
     *     console.log(polygon._setMetrics());
     *     console.log(polygon._setMetrics(options));
     *
     * @param {Object} [updated] Custom options used to render the polygon.
     * @return {Object} Object with metrics as key-value pairs.
     * @private
     */

    Facade.Polygon.prototype._setMetrics = function (updated) {

        var metrics = this._defaultMetrics(),
            options = updated || this.getAllOptions(),
            bounds = { top: null, right: null, bottom: null, left: null },
            point,
            i,
            length,
            anchor,
            strokeWidthOffset = this._getStrokeWidthOffset(options);

        if (typeof this._configOptions === 'function') {

            options = this._configOptions(options);

        }

        for (i = 0, length = options.points.length; i < length; i += 1) {

            if (options.points[i].length === 2) { // Rect

                point = { x: options.points[i][0], y: options.points[i][1] };

            } else if (options.points[i].length === 5) { // Circle

                metrics.width = options.points[i][2] * 2;
                metrics.height = options.points[i][2] * 2;

                point = {
                    x: options.points[i][0] - options.points[i][2],
                    y: options.points[i][1] - options.points[i][2]
                };

            }

            if (point.x < bounds.left || bounds.left === null) {

                bounds.left = point.x;

            }

            if (point.y < bounds.top || bounds.top === null) {

                bounds.top = point.y;

            }

            if (point.x > bounds.right || bounds.right === null) {

                bounds.right = point.x;

            }

            if (point.y > bounds.bottom || bounds.bottom === null) {

                bounds.bottom = point.y;

            }

        }

        metrics.x = options.x + bounds.left;
        metrics.y = options.y + bounds.top;

        if (metrics.width === null && metrics.height === null) {

            metrics.width = bounds.right - bounds.left;
            metrics.height = bounds.bottom - bounds.top;

        }

        metrics.width = (metrics.width + strokeWidthOffset * 2) * options.scale;
        metrics.height = (metrics.height + strokeWidthOffset * 2) * options.scale;

        anchor = this._getAnchorPoint(options, metrics);

        metrics.x = metrics.x + anchor[0] - strokeWidthOffset;
        metrics.y = metrics.y + anchor[1] - strokeWidthOffset;

        if (this instanceof Facade.Circle) {

            metrics.x = metrics.x + options.radius;
            metrics.y = metrics.y + options.radius;

        }

        if (!updated) {

            this._metrics = metrics;

        }

        return metrics;

    };

    /**
     * Create a circle object. Inherits all methods from <b>Facade.Polygon</b>.
     *
     *     var circle = new Facade.Circle({
     *         x: 0,
     *         y: 0,
     *         radius: 100,
     *         lineWidth: 10,
     *         strokeStyle: '#333E4B',
     *         fillStyle: '#1C73A8',
     *         anchor: 'top/left'
     *     });
     *
     * @param {Object} [options] Options to create the circle with.
     * @param {Integer} [options.x] X coordinate to position the circle. <i>Default:</i> 0
     * @param {Integer} [options.y] Y coordinate to position the circle. <i>Default:</i> 0
     * @param {String} [options.anchor] Position to anchor the circle. <i>Default:</i> "top/left"<br><ul><li>top/left</li><li>top/center</li><li>top/right</li><li>center/left</li><li>center</li><li>center/right</li><li>bottom/left</li><li>bottom/center</li><li>bottom/right</li></ul>
     * @param {Integer} [options.rotate] Degrees to rotate the circle. <i>Default:</i> 0
     * @param {Integer} [options.scale] A float representing the scale of a circle. <i>Default:</i> 1
     * @param {Integer} [options.opacity] Opacity of the circle. Integer between 0 and 100. <i>Default:</i> 100
     * @param {String} [options.fillStyle] Fill color for the circle. Can be a text representation of a color, HEX, RGB(a), HSL(a). <i>Default:</i> "#000"<br><ul><li>HTML Colors: red, green, blue, etc.</li><li>HEX: #f00, #ff0000</li><li>RGB(a): rgb(255, 0, 0), rgba(0, 255, 0, 0.5)</li><li>HSL(a): hsl(100, 100%, 50%), hsla(100, 100%, 50%, 0.5)</li></ul>
     * @param {String} [options.strokeStyle] Color of a circle's stroke. Can be a text representation of a color, HEX, RGB(a), HSL(a). <i>Default:</i> "#000"<br><ul><li>HTML Colors: red, green, blue, etc.</li><li>HEX: #f00, #ff0000</li><li>RGB(a): rgb(255, 0, 0), rgba(0, 255, 0, 0.5)</li><li>HSL(a): hsl(100, 100%, 50%), hsla(100, 100%, 50%, 0.5)</li></ul>
     * @param {Integer} [options.lineWidth] Width of the stroke. <i>Default:</i> 0
     * @param {String} [options.lineCap] The style of line cap. <i>Default:</i> "butt"<br><ul><li>butt</li><li>round</li><li>square</li></ul>
     * @param {String} [options.lineJoin] The style of line join. <i>Default:</i> "miter"<br><ul><li>miter</li><li>round</li><li>bevel</li></ul>
     * @param {Integer} [options.radius] Radius of the circle. <i>Default:</i> 0
     * @param {Integer} [options.start] Degree at which the circle begins. <i>Default:</i> 0
     * @param {Integer} [options.end] Degree at which the circle ends. <i>Default:</i> 360
     * @param {Boolean} [options.counterclockwise] Boolean determining if the circle will be drawn in a counter clockwise direction. <i>Default:</i> false
     * @return {Object} New Facade.Circle object.
     * @public
     */

    Facade.Circle = function (options) {

        if (!(this instanceof Facade.Circle)) {

            return new Facade.Circle(options);

        }

        this._options = this._defaultOptions({
            radius: 0,
            begin: 0,
            end: 360,
            counterclockwise: false
        });
        this._metrics = this._defaultMetrics();

        this.setOptions(options);

    };

    /*!
     * Extend from Facade.Polygon
     */

    Facade.Circle.prototype = Object.create(Facade.Polygon.prototype);
    Facade.Circle.constructor = Facade.Polygon;

    /**
     * Custom configuration for options specific to a circle entity.
     *
     *     console.log(circle._configOptions(options));
     *
     * @param {Object} options Complete set of circle specific options.
     * @return {Object} Converted options.
     * @private
     */

    Facade.Circle.prototype._configOptions = function (options) {

        options.translate = [ options.x, options.y ];
        options.globalAlpha = options.opacity / 100;

        if (options.counterclockwise) {

            options.points = [ [ 0, 0, options.radius, options.end * _TO_RADIANS, options.begin * _TO_RADIANS ] ];

        } else {

            options.points = [ [ 0, 0, options.radius, options.begin * _TO_RADIANS, options.end * _TO_RADIANS ] ];

        }

        return options;

    };

    /**
     * Returns an array of the x and y anchor positions based on given options and metrics.
     *
     *     console.log(circle._getAnchorPoint(options, metrics));
     *
     * @param {Object} options Facade.Circle options.
     * @param {Object} metrics Facade.Circle metrics.
     * @return {Array} Array with the x and y anchor positions.
     * @private
     */

    Facade.Circle.prototype._getAnchorPoint = function (options, metrics) {

        var pos = Facade.Polygon.prototype._getAnchorPoint.call(this, options, metrics);

        pos[0] = pos[0] + options.radius;
        pos[1] = pos[1] + options.radius;

        return pos;

    };

    /**
     * Set metrics based on the circle's options.
     *
     *     console.log(circle._setMetrics());
     *     console.log(circle._setMetrics(options));
     *
     * @param {Object} [updated] Custom options used to render the circle.
     * @return {Object} Object with metrics as key-value pairs.
     * @private
     */

    Facade.Circle.prototype._setMetrics = function (updated) {

        var metrics = Facade.Polygon.prototype._setMetrics.call(this, updated),
            options = updated || this.getAllOptions();

        metrics.x = metrics.x - options.radius;
        metrics.y = metrics.y - options.radius;

        if (!updated) {

            this._metrics = metrics;

        }

        return metrics;

    };

    /**
     * Create a line object. Inherits all methods from <b>Facade.Polygon</b>.
     *
     *     var line = new Facade.Line({
     *         x: 0,
     *         y: 0,
     *         x1: 0,
     *         x2: 200,
     *         lineWidth: 10,
     *         strokeStyle: '#333E4B',
     *         anchor: 'top/left'
     *     });
     *
     * @param {Object} [options] Options to create the line with.
     * @param {Integer} [options.x] X coordinate to position the line. <i>Default:</i> 0
     * @param {Integer} [options.y] Y coordinate to position the line. <i>Default:</i> 0
     * @param {String} [options.anchor] Position to anchor the line. <i>Default:</i> "top/left"<br><ul><li>top/left</li><li>top/center</li><li>top/right</li><li>center/left</li><li>center</li><li>center/right</li><li>bottom/left</li><li>bottom/center</li><li>bottom/right</li></ul>
     * @param {Integer} [options.rotate] Degrees to rotate the line. <i>Default:</i> 0
     * @param {Integer} [options.scale] A float representing the scale of a line. <i>Default:</i> 1
     * @param {Integer} [options.opacity] Opacity of the line. Integer between 0 and 100. <i>Default:</i> 100
     * @param {String} [options.strokeStyle] Color of a line. Can be a text representation of a color, HEX, RGB(a), HSL(a). <i>Default:</i> "#000"<br><ul><li>HTML Colors: red, green, blue, etc.</li><li>HEX: #f00, #ff0000</li><li>RGB(a): rgb(255, 0, 0), rgba(0, 255, 0, 0.5)</li><li>HSL(a): hsl(100, 100%, 50%), hsla(100, 100%, 50%, 0.5)</li></ul>
     * @param {Integer} [options.lineWidth] Width of the stroke. <i>Default:</i> 0
     * @param {String} [options.lineCap] The style of line cap. <i>Default:</i> "butt"<br><ul><li>butt</li><li>round</li><li>square</li></ul>
     * @param {Integer} [options.x1] X coordinate where line begins. <i>Default:</i> 0
     * @param {Integer} [options.y1] Y coordinate where line begins. <i>Default:</i> 0
     * @param {Integer} [options.x2] X coordinate where line ends. <i>Default:</i> 0
     * @param {Integer} [options.y2] Y coordinate where line ends. <i>Default:</i> 0
     * @return {Object} New Facade.Line object.
     * @public
     */

    Facade.Line = function (options) {

        if (!(this instanceof Facade.Line)) {

            return new Facade.Line(options);

        }

        this._options = this._defaultOptions({
            x1: 0,
            y1: 0,
            x2: 0,
            y2: 0,
            lineWidth: 1
        });
        this._metrics = this._defaultMetrics();

        this.setOptions(options);

    };

    /*!
     * Extend from Facade.Polygon
     */

    Facade.Line.prototype = Object.create(Facade.Polygon.prototype);
    Facade.Line.constructor = Facade.Polygon;

    /**
     * Custom configuration for options specific to a line entity.
     *
     *     console.log(line._configOptions(options));
     *
     * @param {Object} options Complete set of line specific options.
     * @return {Object} Converted options.
     * @private
     */

    Facade.Line.prototype._configOptions = function (options) {

        options.translate = [ options.x, options.y ];
        options.globalAlpha = options.opacity / 100;
        options.closePath = false;

        options.points = [ [ options.x1, options.y1 ], [ options.x2, options.y2 ] ];

        return options;

    };

    /**
     * Returns an array of the x and y anchor positions based on given options and metrics.
     *
     *     console.log(line._getAnchorPoint(options, metrics));
     *
     * @param {Object} options Facade.Line options.
     * @param {Object} metrics Facade.Line metrics.
     * @return {Array} Array with the x and y anchor positions.
     * @private
     */

    Facade.Line.prototype._getAnchorPoint = function (options, metrics) {

        var pos = [0, 0];

        if (options.anchor.match(/center$/)) {

            pos[0] = -(metrics.width / 2 - options.lineWidth / 2);

        } else if (options.anchor.match(/right$/)) {

            pos[0] = -(metrics.width - options.lineWidth);

        }

        if (options.anchor.match(/^center/)) {

            pos[1] = -(metrics.height / 2 - options.lineWidth / 2);

        } else if (options.anchor.match(/^bottom/)) {

            pos[1] = -(metrics.height - options.lineWidth);

        }

        return pos;

    };

    /**
     * Create a rectangle object. Inherits all methods from <b>Facade.Polygon</b>.
     *
     *     var rect = new Facade.Rect({
     *         x: 0,
     *         y: 0,
     *         width: 200,
     *         height: 200,
     *         lineWidth: 10,
     *         strokeStyle: '#333E4B',
     *         fillStyle: '#1C73A8',
     *         anchor: 'top/left'
     *     });
     *
     * @param {Object} [options] Options to create the rectangle with.
     * @param {Integer} [options.x] X coordinate to position the rectangle. <i>Default:</i> 0
     * @param {Integer} [options.y] Y coordinate to position the rectangle. <i>Default:</i> 0
     * @param {String} [options.anchor] Position to anchor the rectangle. <i>Default:</i> "top/left"<br><ul><li>top/left</li><li>top/center</li><li>top/right</li><li>center/left</li><li>center</li><li>center/right</li><li>bottom/left</li><li>bottom/center</li><li>bottom/right</li></ul>
     * @param {Integer} [options.rotate] Degrees to rotate the rectangle. <i>Default:</i> 0
     * @param {Integer} [options.scale] A float representing the scale of a rectangle. <i>Default:</i> 1
     * @param {Integer} [options.opacity] Opacity of the rectangle. Integer between 0 and 100. <i>Default:</i> 100
     * @param {String} [options.fillStyle] Fill color for the rectangle. Can be a text representation of a color, HEX, RGB(a), HSL(a). <i>Default:</i> "#000"<br><ul><li>HTML Colors: red, green, blue, etc.</li><li>HEX: #f00, #ff0000</li><li>RGB(a): rgb(255, 0, 0), rgba(0, 255, 0, 0.5)</li><li>HSL(a): hsl(100, 100%, 50%), hsla(100, 100%, 50%, 0.5)</li></ul>
     * @param {String} [options.strokeStyle] Color of a rectangle's stroke. Can be a text representation of a color, HEX, RGB(a), HSL(a). <i>Default:</i> "#000"<br><ul><li>HTML Colors: red, green, blue, etc.</li><li>HEX: #f00, #ff0000</li><li>RGB(a): rgb(255, 0, 0), rgba(0, 255, 0, 0.5)</li><li>HSL(a): hsl(100, 100%, 50%), hsla(100, 100%, 50%, 0.5)</li></ul>
     * @param {Integer} [options.lineWidth] Width of the stroke. <i>Default:</i> 0
     * @param {String} [options.lineJoin] The style of rectangle join. <i>Default:</i> "miter"<br><ul><li>miter</li><li>round</li><li>bevel</li></ul>
     * @param {Integer} [options.width] Width of the rectangle. <i>Default:</i> 0
     * @param {Integer} [options.height] Height of the rectangle. <i>Default:</i> 0
     * @return {Object} New Facade.Rect object.
     * @public
     */

    Facade.Rect = function (options) {

        if (!(this instanceof Facade.Rect)) {

            return new Facade.Rect(options);

        }

        this._options = this._defaultOptions({
            width: 0,
            height: 0
        });
        this._metrics = this._defaultMetrics();

        this.setOptions(options);

    };

    /*!
     * Extend from Facade.Polygon
     */

    Facade.Rect.prototype = Object.create(Facade.Polygon.prototype);
    Facade.Rect.constructor = Facade.Polygon;

    /**
     * Custom configuration for options specific to a rectangle entity.
     *
     *     console.log(rect._configOptions(options));
     *
     * @param {Object} options Complete set of rectangle specific options.
     * @return {Object} Converted options.
     * @private
     */

    Facade.Rect.prototype._configOptions = function (options) {

        options.translate = [ options.x, options.y ];
        options.globalAlpha = options.opacity / 100;

        options.points = [ [ 0, 0 ], [ options.width, 0 ], [ options.width, options.height ], [ 0, options.height ] ];

        return options;

    };

    /**
     * Create an image object. Inherits all methods from <b>Facade.Entity</b>.
     *
     *     var image = new Facade.Image('images/sprite.png', {
     *         x: 0,
     *         y: 0,
     *         width: 100,
     *         height: 200,
     *         anchor: 'top/left'
     *     });
     *
     * @param {Object|String} source Local image file or reference to an HTML image element.
     * @param {Object} [options] Options to create the image with.
     * @param {Integer} [options.x] X coordinate to position an image. <i>Default:</i> 0
     * @param {Integer} [options.y] Y coordinate to position an image. <i>Default:</i> 0
     * @param {String} [options.anchor] Position to anchor the image. <i>Default:</i> "top/left"<br><ul><li>top/left</li><li>top/center</li><li>top/right</li><li>center/left</li><li>center</li><li>center/right</li><li>bottom/left</li><li>bottom/center</li><li>bottom/right</li></ul>
     * @param {Integer} [options.rotate] Degrees to rotate the image. <i>Default:</i> 0
     * @param {Integer} [options.scale] A float representing the scale of an image. <i>Default:</i> 1
     * @param {Integer} [options.width] Width of the image. <i>Default:</i> 0
     * @param {Integer} [options.height] Height of the image. <i>Default:</i> 0
     * @param {Integer} [options.tileX] Number of times to tile the image horizontally. <i>Default:</i> 1
     * @param {Integer} [options.tileY] Number of times to tile the image vertically. <i>Default:</i> 1
     * @param {Integer} [options.offsetX] Starting X coordinate within the image. <i>Default:</i> 0
     * @param {Integer} [options.offsetY] Starting Y coordinate within the image. <i>Default:</i> 0
     * @param {Array} [options.frames] Array of frame numbers (integers starting at 0) for sprite animation. <i>Default:</i> [0]
     * @param {Integer} [options.speed] Speed of sprite animation. <i>Default:</i> 120
     * @param {Boolean} [options.loop] Determines if the animation should loop. <i>Default:</i> true
     * @param {Function} [options.callback] Function called for every frame of a sprite animation. <i>Default:</i> `function (frame) { };`
     * @property {Object} image Reference to the image element.
     * @property {Boolean} animating Boolean state of the animation.
     * @property {Integer} currentFrame Current frame of animation.
     * @return {Object} New Facade.Image object.
     * @public
     */

    Facade.Image = function (img, options) {

        if (!(this instanceof Facade.Image)) {

            return new Facade.Image(img, options);

        }

        this._options = this._defaultOptions({
            width: 0,
            height: 0,
            tileX: 1,
            tileY: 1,
            offsetX: 0,
            offsetY: 0,
            frames: [0],
            speed: 120,
            loop: true,
            callback: function () { return undefined; }
        });
        this._metrics = this._defaultMetrics();

        this.animating = false;
        this.currentFrame = 0;

        this.setOptions(options);

        this.load(img);

    };

    /*!
     * Extend from Facade.Entity
     */

    Facade.Image.prototype = Object.create(Facade.Entity.prototype);
    Facade.Image.constructor = Facade.Entity;

    /**
     * Loads either a reference to an image tag or an image URL into a Facade.Image entity.
     *
     *     console.log(image.load(document.querySelector('img')));
     *     console.log(image.load('images/sprite.png'));
     *
     * @param {Object|String} source A reference to an image tag or an image URL.
     * @return {void}
     * @public
     */

    Facade.Image.prototype.load = function (source) {

        if (typeof source === 'object' && source.nodeType === 1) {

            this.image = source;

        } else {

            this.image = document.createElement('img');
            this.image.setAttribute('src', source);

        }

        if (this.image.complete) {

            this._setMetrics();

        } else {

            this.image.addEventListener('load', this._setMetrics.bind(this, null));

        }

    };

    /**
     * Starts an image sprite animation.
     *
     *  image.play();
     *
     * @return {Object} Facade.js image object.
     * @public
     */

    Facade.Image.prototype.play = function () {

        this.animating = true;

        return this;

    };

    /**
     * Pauses an image sprite animation.
     *
     *  image.pause();
     *
     * @return {Object} Facade.js image object.
     * @public
     */

    Facade.Image.prototype.pause = function () {

        this.animating = false;

        return this;

    };

    /**
     * Resets an image sprite animation to the first frame.
     *
     *  image.reset();
     *
     * @return {Object} Facade.js image object.
     * @public
     */

    Facade.Image.prototype.reset = function () {

        this.currentFrame = 0;

        return this;

    };

    /**
     * Stops and resets an image sprite animation.
     *
     *  image.stop();
     *
     * @return {Object} Facade.js image object.
     * @public
     */

    Facade.Image.prototype.stop = function () {

        this.currentFrame = 0;

        this.animating = false;

        return this;

    };

    /**
     * Custom configuration for options specific to a image entity.
     *
     *     console.log(image._configOptions(options));
     *
     * @param {Object} options Complete set of image specific options.
     * @return {Object} Converted options.
     * @private
     */

    Facade.Image.prototype._configOptions = function (options) {

        options.translate = [ options.x, options.y ];

        if (this.image && this.image.complete) {

            if (!options.width) {

                options.width = this.image.width;

            }

            if (!options.height) {

                options.height = this.image.height;

            }

        }

        return options;

    };

    /**
     * Set metrics based on the image's options.
     *
     *     console.log(image._setMetrics());
     *     console.log(image._setMetrics(updated));
     *
     * @param {Object} [updated] Custom options used to render the image.
     * @return {Object} Object with metrics as key-value pairs.
     * @private
     */

    Facade.Image.prototype._setMetrics = function (updated) {

        var metrics = this._defaultMetrics(),
            options = updated || this.getAllOptions(),
            anchor;

        if (typeof this._configOptions === 'function') {

            options = this._configOptions(options);

        }

        metrics.width = options.width * options.tileX * options.scale;
        metrics.height = options.height * options.tileY * options.scale;

        anchor = this._getAnchorPoint(options, metrics);

        metrics.x = options.x + anchor[0];
        metrics.y = options.y + anchor[1];

        if (!updated) {

            this._metrics = metrics;

        }

        return metrics;

    };

    /**
     * Renders an image entity to a canvas.
     *
     *     image._draw(facade, options, metrics);
     *
     * @param {Object} facade Facade.js object.
     * @param {Object} options Options used to render the image.
     * @param {Object} metrics Metrics used to render the image.
     * @return {void}
     * @private
     */

    Facade.Image.prototype._draw = function (facade, options, metrics) {

        var context = facade.context,
            currentOffsetX = options.offsetX,
            currentOffsetY = options.offsetY,
            currentWidth = options.width,
            currentHeight = options.height,
            originalWidth = this.image.width,
            originalHeight = this.image.height,
            x,
            y;

        if (this.image.complete) {

            this._applyTransforms(context, options, metrics);

            if (options.frames.length) {

                currentOffsetX += options.frames[this.currentFrame] * options.width;

                if (currentOffsetX + options.width > originalWidth) {

                    currentOffsetY += Math.floor(currentOffsetX / originalWidth) * options.height;

                    currentOffsetX = currentOffsetX % originalWidth;

                }

            }

            if (currentOffsetX + currentWidth > originalWidth) {

                currentWidth = currentOffsetX + currentWidth - originalWidth;

            }

            if (currentOffsetY + currentHeight > originalHeight) {

                currentHeight = currentOffsetY + currentHeight - originalHeight;

            }

            for (x = 0; x < options.tileX; x += 1) {

                for (y = 0; y < options.tileY; y += 1) {

                    context.drawImage(
                        this.image,
                        currentOffsetX,
                        currentOffsetY,
                        currentWidth,
                        currentHeight,
                        options.width * x,
                        options.height * y,
                        currentWidth,
                        currentHeight
                    );

                }

            }

            if (this.animating) {

                if (!this.ftime || facade.ftime - this.ftime >= options.speed) {

                    if (this.ftime) {

                        this.currentFrame += 1;

                    }

                    this.ftime = facade.ftime;

                    if (this.currentFrame >= options.frames.length) {

                        if (options.loop) {

                            this.currentFrame = 0;

                        } else {

                            this.currentFrame = options.frames.length - 1;

                            this.isAnimating = false;

                        }

                    }

                    if (typeof options.callback === 'function') {

                        options.callback.call(this, options.frames[this.currentFrame]);

                    }

                }

            }

        }

    };

    /**
     * Create a text object. Inherits all methods from <b>Facade.Entity</b>.
     *
     *     var text = new Facade.Text('Hello World!', {
     *         x: 0,
     *         y: 0,
     *         fontFamily: 'Helvetica',
     *         fontSize: 40,
     *         fillStyle: '#333',
     *         anchor: 'top/left'
     *     });
     *
     * @param {Object} [value] Value of the text object.
     * @param {Object} [options] Options to create the text entity with.
     * @param {Integer} [options.x] X coordinate to position a text object. <i>Default:</i> 0
     * @param {Integer} [options.y] Y coordinate to position a text object. <i>Default:</i> 0
     * @param {String} [options.anchor] Position to anchor the text object. <i>Default:</i> "top/left"<br><ul><li>top/left</li><li>top/center</li><li>top/right</li><li>center/left</li><li>center</li><li>center/right</li><li>bottom/left</li><li>bottom/center</li><li>bottom/right</li></ul>
     * @param {Integer} [options.rotate] Degrees to rotate the text object. <i>Default:</i> 0
     * @param {Integer} [options.scale] A float representing the scale of a text object. <i>Default:</i> 1
     * @param {Integer} [options.opacity] Opacity of the text object. Integer between 0 and 100. <i>Default:</i> 100
     * @param {Integer} [options.width] Max width of the text object. Will cause text to wrap onto a new line if necessary. No wrapping will occur if the value is set to 0. <i>Default:</i> 0
     * @param {String} [options.fontFamily] Sets the font family of the text. Only one font can be specified at a time. <i>Default:</i> "Arial"
     * @param {String} [options.fontStyle] Font style of the text. <i>Default:</i> "normal"<br><ul><li>normal</li><li>bold</li><li>italic</li></ul>
     * @param {Integer} [options.fontSize] Font size in pixels. <i>Default:</i> 30
     * @param {String} [options.lineHeight] Line height of the text. <i>Default:</i> 1
     * @param {String} [options.textAlignment] Horizontal alignment of the text. <i>Default:</i> "left"<br><ul><li>left</li><li>center</li><li>right</li></ul>
     * @param {String} [options.textBaseline] Baseline to set the vertical alignment of the text drawn. <i>Default:</i> "top"<br><ul><li>top</li><li>hanging</li><li>middle</li><li>alphabetic</li><li>ideographic</li><li>bottom</li></ul>
     * @param {String} [options.fillStyle] Fill color for the text object. Can be a text representation of a color, HEX, RGB(a), HSL(a). <i>Default:</i> "#000"<br><ul><li>HTML Colors: red, green, blue, etc.</li><li>HEX: #f00, #ff0000</li><li>RGB(a): rgb(255, 0, 0), rgba(0, 255, 0, 0.5)</li><li>HSL(a): hsl(100, 100%, 50%), hsla(100, 100%, 50%, 0.5)</li></ul>
     * @param {String} [options.strokeStyle] Color of a text object's stroke. Can be a text representation of a color, HEX, RGB(a), HSL(a). <i>Default:</i> "#000"<br><ul><li>HTML Colors: red, green, blue, etc.</li><li>HEX: #f00, #ff0000</li><li>RGB(a): rgb(255, 0, 0), rgba(0, 255, 0, 0.5)</li><li>HSL(a): hsl(100, 100%, 50%), hsla(100, 100%, 50%, 0.5)</li></ul>
     * @param {Integer} [options.lineWidth] Width of the stroke. <i>Default:</i> 0
     * @property {String} value Current value of the text object.
     * @return {Object} New Facade.Text object.
     * @public
     */

    Facade.Text = function (value, options) {

        if (!(this instanceof Facade.Text)) {

            return new Facade.Text(value, options);

        }

        this._options = this._defaultOptions({
            opacity: 100,
            width: 0,
            fontFamily: 'Arial',
            fontStyle: 'normal',
            fontSize: 16,
            lineHeight: 1,
            textAlignment: 'left',
            textBaseline: 'top',
            fillStyle: '#000',
            strokeStyle: '#000',
            lineWidth: 0
        });
        this._metrics = this._defaultMetrics();

        this._maxLineWidth = 0;

        this._lines = [];

        this.setOptions(options);

        if (value !== undefined) {

            this.setText(value);

        }

    };

    /*!
     * Extend from Facade.Entity
     */

    Facade.Text.prototype = Object.create(Facade.Entity.prototype);
    Facade.Text.constructor = Facade.Entity;

    /**
     * Sets the text entities value.
     *
     *     console.log(text.setText('Lorem ipsum dolor sit amet'));
     *
     * @param {String} value The new value of the text entity.
     * @return {Array} An array of lines and the position to render using <a href="https://developer.mozilla.org/en-US/docs/Drawing_text_using_a_canvas#fillText()">fillText()</a> and <a href="https://developer.mozilla.org/en-US/docs/Drawing_text_using_a_canvas#strokeText()">strokeText()</a>.
     * @public
     */

    Facade.Text.prototype.setText = function (value) {

        var options = this.getAllOptions(),
            words = [],
            currentWord = null,
            currentLine = '',
            currentLineWidth = 0,
            i,
            length;

        this.value = value;

        this._maxLineWidth = options.width;

        this._lines = [];

        if (value) {

            words = String(value).match(/\n|[\S]+ ?/g);

        }

        if (typeof this._configOptions === 'function') {

            options = this._configOptions(options);

        }

        _context.save();

        _context.font = options.font;

        while (words.length) {

            currentWord = words.shift();
            currentLineWidth = _context.measureText(currentLine + currentWord.replace(/\s$/, '')).width;

            if ((options.width > 0 && currentLineWidth > options.width) || currentWord.match(/\n/)) {

                this._lines.push([currentLine.replace(/\s$/, ''), 0, this._lines.length * (options.fontSize * options.lineHeight)]);

                currentLine = currentWord.replace(/\n/, '');

            } else {

                currentLine = currentLine + currentWord;

                if (currentLineWidth > this._maxLineWidth) {

                    this._maxLineWidth = currentLineWidth;

                }

            }

        }

        this._lines.push([currentLine.replace(/\s$/, ''), 0, this._lines.length * (options.fontSize * options.lineHeight)]);

        for (i = 0, length = this._lines.length; i < length; i += 1) {

            currentLineWidth = _context.measureText(this._lines[i][0]).width;

            if (options.textAlignment === 'center') {

                this._lines[i][1] = (this._maxLineWidth - currentLineWidth) / 2;

            } else if (options.textAlignment === 'right') {

                this._lines[i][1] = this._maxLineWidth - currentLineWidth;

            }

        }

        _context.restore();

        this._setMetrics();

        return this._lines;

    };

    /**
     * Renders a text entity to a canvas.
     *
     *     text._draw(facade, options, metrics);
     *
     * @param {Object} facade Facade.js object.
     * @param {Object} options Options used to render the text entity.
     * @param {Object} metrics Metrics used to render the text entity.
     * @return {void}
     * @private
     */

    Facade.Text.prototype._draw = function (facade, options, metrics) {

        var context = facade.context,
            i,
            length;

        this._applyTransforms(context, options, metrics);

        for (i = 0, length = this._lines.length; i < length; i += 1) {

            if (options.fillStyle) {

                context.fillText.apply(context, this._lines[i]);

            }

            if (options.lineWidth) {

                context.strokeText.apply(context, this._lines[i]);

            }

        }

    };

    /**
     * Custom configuration for options specific to a text entity.
     *
     *     console.log(text._configOptions(options));
     *
     * @param {Object} options Complete set of text specific options.
     * @return {Object} Converted options.
     * @private
     */

    Facade.Text.prototype._configOptions = function (options) {

        options.translate = [ options.x, options.y ];
        options.globalAlpha = options.opacity / 100;
        options.font = options.fontStyle + ' ' + parseInt(options.fontSize, 10) + 'px ' + options.fontFamily;

        if (options.width === 0) {

            options.width = this._maxLineWidth;

        }

        return options;

    };

    /**
     * Set metrics based on the text's options.
     *
     *     console.log(text._setMetrics());
     *     console.log(text._setMetrics(updated));
     *
     * @param {Object} [updated] Custom options used to render the text entity.
     * @return {Object} Object with metrics as key-value pairs.
     * @private
     */

    Facade.Text.prototype._setMetrics = function (updated) {

        var metrics = this._defaultMetrics(),
            options = updated || this.getAllOptions(),
            anchor;

        if (typeof this._configOptions === 'function') {

            options = this._configOptions(options);

        }

        if (this._lines) {

            metrics.width = options.width * options.scale;
            metrics.height = this._lines.length * (options.fontSize * options.lineHeight) * options.scale;

        }

        anchor = this._getAnchorPoint(options, metrics);

        metrics.x = options.x + anchor[0];
        metrics.y = options.y + anchor[1];

        if (!updated) {

            this._metrics = metrics;

        }

        return metrics;

    };

    /**
     * Create a group object. Inherits all methods from <b>Facade.Entity</b>.
     *
     *     var group = new Facade.Group({ x: 100, y: 100 });
     *
     *     group.addToGroup(polygon);
     *     group.addToGroup(circle);
     *     group.addToGroup(line);
     *     group.addToGroup(rect);
     *
     * @param {Object} [options] Options to create the group with.
     * @param {Integer} [options.x] X coordinate to position a group. <i>Default:</i> 0
     * @param {Integer} [options.y] Y coordinate to position a group. <i>Default:</i> 0
     * @param {String} [options.anchor] Position to anchor the group. <i>Default:</i> "top/left"<br><ul><li>top/left</li><li>top/center</li><li>top/right</li><li>center/left</li><li>center</li><li>center/right</li><li>bottom/left</li><li>bottom/center</li><li>bottom/right</li></ul>
     * @param {Integer} [options.rotate] Degrees to rotate the group. <i>Default:</i> 0
     * @param {Integer} [options.scale] A float representing the scale of a group. <i>Default:</i> 1
     * @return {Object} New Facade.Group object.
     * @public
     */

    Facade.Group = function (options) {

        if (!(this instanceof Facade.Group)) {

            return new Facade.Group(options);

        }

        this._options = this._defaultOptions();
        this._metrics = this._defaultMetrics();

        this._objects = [];

        this.setOptions(options);

    };

    /*!
     * Extend from Facade.Entity
     */

    Facade.Group.prototype = Object.create(Facade.Entity.prototype);
    Facade.Group.constructor = Facade.Entity;

    /**
     * Renders a group of entities to a canvas.
     *
     *     group._draw(stage, options, metrics);
     *
     * @param {Object} facade Facade.js object.
     * @param {Object} options Options used to render the group.
     * @param {Object} metrics Metrics used to render the group.
     * @return {void}
     * @private
     */

    Facade.Group.prototype._draw = function (facade, options, metrics) {

        var context = facade.context,
            i,
            length;

        this._applyTransforms(context, options, metrics);

        for (i = 0, length = this._objects.length; i < length; i += 1) {

            facade.addToStage(this._objects[i]);

        }

    };

    /**
     * Custom configuration for options specific to a group entity.
     *
     *     console.log(group._configOptions(options));
     *
     * @param {Object} options Complete set of group specific options.
     * @return {Object} Converted options.
     * @private
     */

    Facade.Group.prototype._configOptions = function (options) {

        options.translate = [ options.x, options.y ];

        return options;

    };

    /**
     * Adds a Facade.js entity to a group.
     *
     *     group.addToGroup(circle);
     *
     * @param {Object|Array} obj Facade.js entity or an array of entities.
     * @return {void}
     * @public
     */

    Facade.Group.prototype.addToGroup = function (obj) {

        var i,
            length;

        if (obj instanceof Facade.Entity) {

            if (!this.hasEntity(obj)) {

                this._objects.push(obj);

                this._setMetrics();

            }

        } else if (Array.isArray(obj)) {

            for (i = 0, length = obj.length; i < length; i += 1) {

                this.addToGroup(obj[i]);

            }

        } else {

            console.error('Object passed to Facade.addToStage is not a valid Facade.js entity.');

        }

    };

    /**
     * Tests the existence of an entity within a group.
     *
     *     group.addToGroup(circle);
     *
     * @param {Object} obj Facade.js entity.
     * @return {Boolean} Boolean result of the test.
     * @public
     */

    Facade.Group.prototype.hasEntity = function (obj) {

        return this._objects.indexOf(obj) !== -1;

    };

    /**
     * Removes a Facade.js entity from a group.
     *
     *     group.removeFromGroup(circle);
     *
     * @param {Object} obj Facade.js entity.
     * @return {void}
     * @public
     */

    Facade.Group.prototype.removeFromGroup = function (obj) {

        if (obj instanceof Facade.Entity) {

            if (this.hasEntity(obj)) {

                this._objects.splice(this._objects.indexOf(obj), 1);

                this._setMetrics();

            }

        }

    };

    /**
     * Set metrics based on the groups's entities and options.
     *
     *     console.log(group._setMetrics());
     *     console.log(group._setMetrics(updated));
     *
     * @param {Object} [updated] Custom options used to render the group.
     * @return {Object} Object with metrics as key-value pairs.
     * @private
     */

    Facade.Group.prototype._setMetrics = function (updated) {

        var metrics = this._defaultMetrics(),
            options = updated || this.getAllOptions(),
            bounds = { top: null, right: null, bottom: null, left: null },
            i,
            length,
            anchor,
            obj_metrics;

        for (i = 0, length = this._objects.length; i < length; i += 1) {

            obj_metrics = this._objects[i].getAllMetrics();

            if (obj_metrics.x < bounds.left || bounds.left === null) {

                bounds.left = obj_metrics.x;

            }

            if (obj_metrics.y < bounds.top || bounds.top === null) {

                bounds.top = obj_metrics.y;

            }

            if (obj_metrics.x + obj_metrics.width > bounds.right || bounds.right === null) {

                bounds.right = obj_metrics.x + obj_metrics.width;

            }

            if (obj_metrics.y + obj_metrics.height > bounds.bottom || bounds.bottom === null) {

                bounds.bottom = obj_metrics.y + obj_metrics.height;

            }

        }

        metrics.x = options.x + bounds.left;
        metrics.y = options.y + bounds.top;

        metrics.width = (bounds.right - bounds.left) * options.scale;
        metrics.height = (bounds.bottom - bounds.top) * options.scale;

        anchor = this._getAnchorPoint(options, metrics);

        metrics.x = options.x + anchor[0];
        metrics.y = options.y + anchor[1];

        if (!updated) {

            this._metrics = metrics;

        }

        return metrics;

    };

    /*!
     * AMD Support
     */

    if ( true && __webpack_require__(/*! !webpack amd options */ "./node_modules/webpack/buildin/amd-options.js") !== undefined) {

        !(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_RESULT__ = (function () { return Facade; }).apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));

    } else if ( true && module.exports !== undefined) {

        module.exports = Facade;

    } else {

        window.Facade = Facade;

    }

}(window, document));


/***/ }),

/***/ "./node_modules/webpack/buildin/amd-options.js":
/*!****************************************!*\
  !*** (webpack)/buildin/amd-options.js ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/* WEBPACK VAR INJECTION */(function(__webpack_amd_options__) {/* globals __webpack_amd_options__ */
module.exports = __webpack_amd_options__;

/* WEBPACK VAR INJECTION */}.call(this, {}))

/***/ }),

/***/ "./resources/js/event.js":
/*!*******************************!*\
  !*** ./resources/js/event.js ***!
  \*******************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

var Facade = __webpack_require__(/*! facade.js */ "./node_modules/facade.js/facade.js");

function draw(image_name, predictions, mask_name) {
  var stage = new Facade(document.querySelector('#event-snapshot')),
      image = new Facade.Image('/storage/' + image_name, {
    x: stage.width() / 2,
    y: stage.height() / 2,
    height: 480,
    width: 640,
    anchor: 'center'
  });
  var mask = null;

  if (typeof mask_name !== 'undefined') {
    mask = new Facade.Image('/storage/masks/' + mask_name, {
      x: stage.width() / 2,
      y: stage.height() / 2,
      height: 480,
      width: 640,
      anchor: 'center'
    });
  }

  var rects = [];
  predictions.forEach(function (prediction) {
    rects.push(new Facade.Rect({
      x: prediction.x_min,
      y: prediction.y_min,
      width: prediction.x_max - prediction.x_min,
      height: prediction.y_max - prediction.y_min,
      lineWidth: 4,
      strokeStyle: 'red',
      fillStyle: 'rgba(0, 0, 0, 0)'
    }));
  }); // stage.resizeForHDPI();
  // stage.context.webkitImageSmoothingEnabled = false;

  stage.draw(function () {
    this.clear();
    this.addToStage(image);
    if (mask) this.addToStage(mask);

    for (var i = 0; i < rects.length; i++) {
      this.addToStage(rects[i]);
    }
  });
}

if ((typeof predictions === "undefined" ? "undefined" : _typeof(predictions)) !== undefined) {
  draw(file_name, predictions);
  var elements = document.getElementsByClassName('prediction');

  var renderPrediction = function renderPrediction() {
    var selectedPredictions = [JSON.parse(this.getAttribute("data-prediction"))];
    console.log(selectedPredictions);
    draw(file_name, selectedPredictions);
  };

  for (var i = 0; i < elements.length; i++) {
    elements[i].addEventListener('click', renderPrediction, false);
  }
}

/***/ }),

/***/ 1:
/*!*************************************!*\
  !*** multi ./resources/js/event.js ***!
  \*************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /var/www/app/resources/js/event.js */"./resources/js/event.js");


/***/ })

/******/ });